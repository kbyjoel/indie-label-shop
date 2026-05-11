# Cahier de specs — Livraison en points relais

**Projet :** Indie Label Shop  
**Date :** 2026-05-10  
**Statut :** Brouillon

---

## 1. Contexte et objectifs

### 1.1 Situation actuelle

Le système de livraison existant gère :
- Trois calculateurs de frais : `flat_rate`, `per_unit_rate`, `weight_range`
- Des zones géographiques (FR, EU, WORLD) avec la stratégie "zone la plus spécifique"
- Quatre méthodes par défaut : Colissimo FR/EU, International, Digital

L'entité `Shipment` stocke un état (`state`) et un numéro de suivi (`tracking`), mais ne connaît pas de point relais.

### 1.2 Objectif

Permettre à une commande d'être livrée dans un **point relais** (Mondial Relay, Sendcloud, Colissimo Point Retrait…) via un pattern **gateway interchangeable**, sans coupler le cœur du projet à un prestataire spécifique.

### 1.3 Contraintes

- La solution doit être **open source** : aucun SDK propriétaire ne doit être une dépendance obligatoire.
- Chaque gateway est une implémentation optionnelle (package séparé ou classe conditionnelle).
- Le code existant (`ShippingCalculator`, `Shipment`, `ShippingMethod`) ne doit pas être cassé.
- L'expérience checkout doit rester fluide : la sélection du point relais s'insère dans le tunnel existant.

---

## 2. Architecture cible

```
┌─────────────────────────────────────────────────────────────┐
│                     Tunnel de commande                      │
│  Adresse → Livraison → [Point relais?] → Paiement → Merci  │
└─────────────────────────────────────────────────────────────┘
                              │
                              │ méthode de type "relay"
                              ▼
┌───────────────────────────────────────────────┐
│           ShippingGatewayRegistry             │
│  ┌────────────────┐  ┌────────────────────┐   │
│  │ MondialRelay   │  │ SendcloudGateway   │   │
│  │ Gateway        │  │                    │   │
│  └────────────────┘  └────────────────────┘   │
│         ▲ implémentent                         │
│  ShippingGatewayInterface                      │
└───────────────────────────────────────────────┘
                              │
                      PickupPoint VO
                              │
                    Shipment ← pickupPointExternalId
                              │ (après paiement)
                              ▼
               ┌──────────────────────────┐
               │  GenerateShippingLabel   │
               │  Message (Messenger)     │
               └──────────────────────────┘
```

### 2.1 Composants introduits

| Composant | Rôle |
|-----------|------|
| `ShippingGatewayInterface` | Contrat unique pour tous les prestataires |
| `ShippingGatewayRegistry` | Collecte et expose les gateways enregistrés |
| `PickupPoint` (Value Object) | Représentation d'un point relais (pas en DB) |
| `PickupPointSearchRequest` | Critères de recherche (adresse, pays, distance) |
| `GenerateShippingLabelMessage` | Message Messenger pour génération de bordereau |
| `GenerateShippingLabelHandler` | Handler qui appelle la gateway concernée |
| `ShippingMethod::$gatewayCode` | Code de gateway associé à la méthode (nullable) |
| `ShippingMethod::$gatewayConfig` | Configuration gateway par méthode (JSON) |
| `Shipment::$pickupPoint*` | Champs point relais sélectionné |

---

## 3. Modèle de domaine

### 3.1 Extension de `ShippingMethod`

Ajouter deux champs à l'entité existante :

```php
// src/Entity/ShippingMethod.php

#[ORM\Column(type: 'string', length: 64, nullable: true)]
private ?string $gatewayCode = null;
// Exemple : "mondial_relay", "sendcloud", null (livraison à domicile)

#[ORM\Column(type: 'json')]
private array $gatewayConfig = [];
// Clés spécifiques à chaque gateway (cf. §5)
```

**Règle :** si `gatewayCode` est non nul, la méthode est une méthode "point relais" et
le tunnel de commande doit déclencher l'étape de sélection du point.

### 3.2 Extension de `Shipment`

Ajouter les champs de point relais sélectionné :

```php
// src/Entity/Shipment.php

#[ORM\Column(type: 'string', length: 128, nullable: true)]
private ?string $pickupPointExternalId = null;
// ID renvoyé par la gateway (ex: "24R-PMC-59650" pour Mondial Relay)

#[ORM\Column(type: 'string', length: 255, nullable: true)]
private ?string $pickupPointName = null;

#[ORM\Column(type: 'string', length: 255, nullable: true)]
private ?string $pickupPointAddress = null;

#[ORM\Column(type: 'string', length: 10, nullable: true)]
private ?string $pickupPointPostalCode = null;

#[ORM\Column(type: 'string', length: 128, nullable: true)]
private ?string $pickupPointCity = null;

#[ORM\Column(type: 'string', length: 2, nullable: true)]
private ?string $pickupPointCountryCode = null;

#[ORM\Column(type: 'string', length: 512, nullable: true)]
private ?string $labelUrl = null;

#[ORM\Column(type: 'datetime_immutable', nullable: true)]
private ?\DateTimeImmutable $labelGeneratedAt = null;
```

### 3.3 Value Object `PickupPoint`

Non persisté en base. Renvoyé par les gateways à la volée.

```php
// src/Component/Shipping/Gateway/PickupPoint.php

final class PickupPoint
{
    public function __construct(
        public readonly string $externalId,
        public readonly string $name,
        public readonly string $address,
        public readonly string $postalCode,
        public readonly string $city,
        public readonly string $countryCode,
        public readonly float  $latitude,
        public readonly float  $longitude,
        public readonly ?float $distanceKm = null,
        public readonly array  $openingHours = [],  // ['lundi' => '9h-18h', ...]
        public readonly array  $extra = [],          // données libres par gateway
    ) {}
}
```

### 3.4 Value Object `PickupPointSearchRequest`

```php
// src/Component/Shipping/Gateway/PickupPointSearchRequest.php

final class PickupPointSearchRequest
{
    public function __construct(
        public readonly string  $postalCode,
        public readonly string  $city,
        public readonly string  $countryCode,
        public readonly ?string $address     = null,
        public readonly ?float  $latitude    = null,
        public readonly ?float  $longitude   = null,
        public readonly int     $maxResults  = 10,
        public readonly float   $maxDistanceKm = 20.0,
    ) {}
}
```

---

## 4. Interface gateway

```php
// src/Component/Shipping/Gateway/ShippingGatewayInterface.php

namespace App\Component\Shipping\Gateway;

interface ShippingGatewayInterface
{
    /**
     * Identifiant unique de la gateway (utilisé dans ShippingMethod::$gatewayCode).
     * Ex: "mondial_relay", "sendcloud"
     */
    public function getCode(): string;

    /** Nom lisible pour l'admin. */
    public function getName(): string;

    /**
     * Recherche les points relais proches de l'adresse donnée.
     *
     * @param array<string,mixed> $gatewayConfig  Configuration spécifique à la méthode
     * @return PickupPoint[]
     */
    public function searchPickupPoints(
        PickupPointSearchRequest $request,
        array $gatewayConfig = [],
    ): array;

    /**
     * Récupère un point relais précis par son ID externe.
     * Utile pour réafficher le point choisi sur la page récapitulative.
     *
     * @param array<string,mixed> $gatewayConfig
     */
    public function getPickupPoint(
        string $externalId,
        array $gatewayConfig = [],
    ): PickupPoint;

    /**
     * Génère un bordereau d'expédition pour un shipment.
     * Appelé de manière asynchrone via Symfony Messenger.
     *
     * @param array<string,mixed> $gatewayConfig
     */
    public function generateLabel(
        LabelRequest $request,
        array $gatewayConfig = [],
    ): LabelResponse;

    /**
     * Vérifie que la configuration (clés API, etc.) est valide.
     * Utilisé dans l'admin pour feedback immédiat.
     *
     * @param array<string,mixed> $gatewayConfig
     */
    public function validateConfiguration(array $gatewayConfig): bool;
}
```

### 4.1 Value Objects label

```php
// src/Component/Shipping/Gateway/LabelRequest.php
final class LabelRequest
{
    public function __construct(
        public readonly int    $shipmentId,
        public readonly string $pickupPointExternalId,
        // Expéditeur
        public readonly string $senderName,
        public readonly string $senderAddress,
        public readonly string $senderPostalCode,
        public readonly string $senderCity,
        public readonly string $senderCountryCode,
        // Destinataire
        public readonly string $recipientName,
        public readonly string $recipientAddress,
        public readonly string $recipientPostalCode,
        public readonly string $recipientCity,
        public readonly string $recipientCountryCode,
        public readonly string $recipientEmail,
        public readonly string $recipientPhone,
        // Colis
        public readonly float  $weightKg,
        public readonly ?string $reference = null,
    ) {}
}

// src/Component/Shipping/Gateway/LabelResponse.php
final class LabelResponse
{
    public function __construct(
        public readonly string $trackingNumber,
        public readonly string $labelUrl,       // URL publique ou data:application/pdf;base64,...
        public readonly string $labelFormat,    // "pdf", "png", "zpl"
    ) {}
}
```

---

## 5. Implémentations

### 5.1 `MondialRelayGateway`

**Fichier :** `src/Component/Shipping/Gateway/MondialRelayGateway.php`  
**Code :** `mondial_relay`  
**API :** SOAP v5.3 / REST (selon contrat) — `ws.mondialrelay.com`

#### Configuration requise

| Clé `gatewayConfig` | Description |
|---------------------|-------------|
| `login`             | Identifiant marchand Mondial Relay |
| `api_key`           | Clé secrète |
| `company_id`        | Numéro d'enseigne (ex: "BDTEST  ") |
| `country_code`      | Code pays de l'enseigne (ex: "FR") |
| `parcel_type`       | Type de colis (ex: "24R", "24L") |
| `weight_unit`       | "gr" ou "kg" |

> Les clés sensibles (`login`, `api_key`) **ne doivent pas** être stockées dans `ShippingMethod::$gatewayConfig` en clair en base.  
> Voir §8 — Configuration sécurisée.

#### Méthodes API utilisées

| Méthode interface | Appel API Mondial Relay |
|-------------------|------------------------|
| `searchPickupPoints` | `WSI3_PointRelais_Recherche` |
| `getPickupPoint` | `WSI3_PointRelais_Detail` |
| `generateLabel` | `WSI2_CreationEtiquette` |
| `validateConfiguration` | `WSI3_PointRelais_Recherche` avec code postal test |

#### Client HTTP

Utiliser le composant Symfony `HttpClient` (`ScopingHttpClient` pour isoler le baseUri).  
Ne pas introduire de dépendance sur un SDK Mondial Relay tiers.

### 5.2 `SendcloudGateway`

**Fichier :** `src/Component/Shipping/Gateway/SendcloudGateway.php`  
**Code :** `sendcloud`  
**API :** REST v2 — `panel.sendcloud.sc/api/v2/`

#### Configuration requise

| Clé `gatewayConfig` | Description |
|---------------------|-------------|
| `public_key`        | Clé publique API Sendcloud |
| `secret_key`        | Clé secrète API Sendcloud |
| `carrier`           | Transporteur ciblé (ex: "dpd", "bpost", "mondial_relay") |
| `service_point_networks` | Tableau de codes réseau (ex: `["MR"]`) |

#### Méthodes API utilisées

| Méthode interface | Endpoint Sendcloud |
|-------------------|--------------------|
| `searchPickupPoints` | `GET /service-points/` |
| `getPickupPoint` | `GET /service-points/{id}/` |
| `generateLabel` | `POST /parcels/` + `GET /labels/` |
| `validateConfiguration` | `GET /user/` |

### 5.3 `NullShippingGateway` (pour tests)

Implémentation sans appel réseau, renvoie des données statiques.  
Enregistrée uniquement en environnement `test` et `dev`.

```php
class NullShippingGateway implements ShippingGatewayInterface
{
    public function getCode(): string { return 'null'; }
    // ... renvoie des PickupPoint fictifs
}
```

---

## 6. Enregistrement des gateways

### 6.1 Tag Symfony

```php
// Chaque gateway se tague elle-même via attribut PHP
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.shipping_gateway')]
class MondialRelayGateway implements ShippingGatewayInterface { ... }
```

### 6.2 Registry

```php
// src/Component/Shipping/Gateway/ShippingGatewayRegistry.php

final class ShippingGatewayRegistry
{
    /** @param iterable<ShippingGatewayInterface> $gateways */
    public function __construct(
        #[TaggedIterator('app.shipping_gateway')]
        private iterable $gateways,
    ) {}

    public function get(string $code): ShippingGatewayInterface
    {
        foreach ($this->gateways as $gateway) {
            if ($gateway->getCode() === $code) {
                return $gateway;
            }
        }
        throw new \InvalidArgumentException("No shipping gateway with code '$code'.");
    }

    /** @return ShippingGatewayInterface[] */
    public function all(): array
    {
        return iterator_to_array($this->gateways);
    }
}
```

---

## 7. Tunnel de commande (checkout)

### 7.1 Étapes existantes

```
1. front_checkout_address    — adresse livraison + facturation
2. front_checkout_shipment   — sélection méthode de livraison
3. front_checkout_payment    — paiement
4. front_checkout_thank_you  — confirmation
```

### 7.2 Nouvelle étape insérée

```
1. front_checkout_address
2. front_checkout_shipment          — sélection méthode (inchangé)
   ↓ si méthode relay (gatewayCode non nul)
2b. front_checkout_pickup_point     — sélection point relais   ← NOUVEAU
3. front_checkout_payment
4. front_checkout_thank_you
```

L'étape 2b est **conditionnelle** : elle n'apparaît que si la méthode choisie a un `gatewayCode`.

### 7.3 Contrôleur `CheckoutPickupPointController`

**Route :** `GET|POST /checkout/pickup-point` — `front_checkout_pickup_point`

```
GET  → affiche la carte + liste des points relais proches de l'adresse de livraison
POST → enregistre le point choisi sur le Shipment, redirige vers payment
```

**Actions :**

1. Charger le panier courant via `CartContext`.
2. Vérifier que la méthode de livraison est bien de type relay ; sinon rediriger vers `front_checkout_shipment`.
3. Construire un `PickupPointSearchRequest` à partir de l'adresse de livraison du panier.
4. Appeler `ShippingGatewayRegistry::get($gatewayCode)->searchPickupPoints(...)`.
5. Passer les points au template.
6. En POST (sélection confirmée) :
   - Valider que l'`externalId` soumis figure bien dans les résultats (sécurité : ne pas accepter n'importe quel ID).
   - Appeler `gateway->getPickupPoint($externalId)` pour récupérer les détails complets.
   - Hydrater `Shipment` avec les champs `pickupPoint*`.
   - Persister et rediriger.

### 7.4 API AJAX de recherche de points relais

Pour permettre une recherche dynamique (changement de code postal) sans rechargement complet :

**Route :** `GET /checkout/pickup-points/search` — `front_checkout_pickup_points_search`  
**Format :** JSON  
**Paramètres :** `postal_code`, `city`, `country_code`  
**Réponse :**

```json
{
  "points": [
    {
      "externalId": "24R-PMC-59650",
      "name": "Le Relais Express",
      "address": "12 rue de la Paix",
      "postalCode": "59650",
      "city": "Villeneuve-d'Ascq",
      "latitude": 50.6292,
      "longitude": 3.1412,
      "distanceKm": 0.8,
      "openingHours": { "lundi": "9h-19h", ... }
    }
  ]
}
```

### 7.5 Frontend — Sélection point relais

**Template :** `templates/front/checkout/pickup_point.html.twig`

Interface en deux panneaux :
- **Gauche :** liste des points relais (nom, adresse, distance, horaires)
- **Droite :** carte Leaflet.js (tiles OpenStreetMap, aucune clé API requise)

**Stimulus controller :** `pickup_point_controller.js`
- Charge les points via l'API AJAX ci-dessus
- Place des marqueurs sur la carte
- Surligne le point sélectionné dans la liste et sur la carte
- Remplit un `<input type="hidden" name="pickup_point_id">` avant soumission

**Leaflet.js** : ajouté dans `importmap.php` (pas de clé API, OSM gratuit).

### 7.6 Validation checkout

Dans `CheckoutController::shipment()`, après enregistrement de la méthode :  
- Si la nouvelle méthode est de type relay **et** le shipment n'a pas de `pickupPointExternalId` → rediriger vers `front_checkout_pickup_point`.
- Si la méthode change (de relay à domicile) → effacer les champs `pickupPoint*` du Shipment.

---

## 8. Configuration sécurisée des clés API

### 8.1 Principe

Les clés API (credentials) ne sont **jamais** stockées en base de données. Elles transitent par :
- Variables d'environnement (`.env.local`)
- Paramètres Symfony injectés dans les gateways

### 8.2 Exemple pour Mondial Relay

```yaml
# config/services/shipping.yaml
App\Component\Shipping\Gateway\MondialRelayGateway:
    arguments:
        $login:   '%env(MONDIAL_RELAY_LOGIN)%'
        $apiKey:  '%env(MONDIAL_RELAY_API_KEY)%'
```

```
# .env
MONDIAL_RELAY_LOGIN=
MONDIAL_RELAY_API_KEY=
SENDCLOUD_PUBLIC_KEY=
SENDCLOUD_SECRET_KEY=
```

### 8.3 Configuration per-method dans `ShippingMethod::$gatewayConfig`

Ce champ JSON stocke uniquement les paramètres **non sensibles** et spécifiques à la méthode :

```json
{
  "parcel_type": "24R",
  "weight_unit": "kg",
  "company_id": "BDTEST  "
}
```

---

## 9. Interface admin

### 9.1 Formulaire `ShippingMethodType`

Ajouter une section **"Intégration gateway"** :

```
┌─────────────────────────────────────────────────┐
│ Gateway de livraison                            │
│  ● Aucune (livraison à domicile)                │
│  ○ Mondial Relay                                │
│  ○ Sendcloud                                    │
│                                                 │
│ [Visible uniquement si gateway ≠ Aucune]        │
│ Configuration gateway  ──────────────────────── │
│  Type de colis : [24R ▾]                        │
│  Enseigne      : [____________]                 │
│  [Tester la connexion]  → ✓ Connexion OK        │
└─────────────────────────────────────────────────┘
```

Le sélecteur de gateway est un `ChoiceType` généré dynamiquement depuis `ShippingGatewayRegistry::all()`.

La section de configuration conditionnelle est rendue via un `CollectionType` ou un `JsonType` adapté par gateway (chaque gateway expose `getConfigurationFormType(): string`).

### 9.2 Vue admin d'une commande

Dans la vue détail d'une commande (`admin/order/show`), si le shipment a un point relais :

```
Livraison ─────────────────────────────────────────
  Méthode        : Mondial Relay 24R
  Point relais   : Le Relais Express
                   12 rue de la Paix, 59650 Villeneuve-d'Ascq
  Bordereau      : [Télécharger PDF]   [Régénérer]
  Suivi          : 7xxxxxxxxxxxxxxx
```

Bouton "Générer le bordereau" → déclenche manuellement le message Messenger.

### 9.3 Liste des commandes

Ajouter un filtre "Livraison point relais" (booléen) dans le DataTable des commandes.

---

## 10. Génération de bordereaux (Messenger)

### 10.1 Message

```php
// src/Component/Shipping/Message/GenerateShippingLabelMessage.php

final class GenerateShippingLabelMessage
{
    public function __construct(
        public readonly int $shipmentId,
    ) {}
}
```

### 10.2 Handler

```php
// src/Component/Shipping/MessageHandler/GenerateShippingLabelHandler.php

#[AsMessageHandler]
final class GenerateShippingLabelHandler
{
    public function __invoke(GenerateShippingLabelMessage $message): void
    {
        $shipment = $this->shipmentRepository->find($message->shipmentId);
        $method   = $shipment->getMethod();
        $gateway  = $this->registry->get($method->getGatewayCode());

        $request  = $this->buildLabelRequest($shipment);
        $response = $gateway->generateLabel($request, $method->getGatewayConfig());

        $shipment->setTracking($response->trackingNumber);
        $shipment->setLabelUrl($response->labelUrl);
        $shipment->setLabelGeneratedAt(new \DateTimeImmutable());

        $this->em->flush();
    }
}
```

### 10.3 Déclenchement

Le message est dispatchée :
1. **Automatiquement** quand l'état de la commande passe à `fulfilled` (listener sur l'événement Sylius `sylius.order.post_fulfill`).
2. **Manuellement** depuis l'admin (bouton "Générer le bordereau").

### 10.4 Routage Messenger

```yaml
# config/packages/messenger.yaml
framework:
    messenger:
        routing:
            App\Component\Shipping\Message\GenerateShippingLabelMessage: async
```

---

## 11. Migrations Doctrine

Deux migrations nécessaires :

**Migration 1 — Extension `ShippingMethod`**
```sql
ALTER TABLE sylius_shipping_method
    ADD COLUMN gateway_code   VARCHAR(64)  DEFAULT NULL,
    ADD COLUMN gateway_config JSON         NOT NULL DEFAULT '{}';
```

**Migration 2 — Extension `Shipment`**
```sql
ALTER TABLE sylius_shipment
    ADD COLUMN pickup_point_external_id VARCHAR(128) DEFAULT NULL,
    ADD COLUMN pickup_point_name        VARCHAR(255) DEFAULT NULL,
    ADD COLUMN pickup_point_address     VARCHAR(255) DEFAULT NULL,
    ADD COLUMN pickup_point_postal_code VARCHAR(10)  DEFAULT NULL,
    ADD COLUMN pickup_point_city        VARCHAR(128) DEFAULT NULL,
    ADD COLUMN pickup_point_country_code VARCHAR(2)  DEFAULT NULL,
    ADD COLUMN label_url                VARCHAR(512) DEFAULT NULL,
    ADD COLUMN label_generated_at       DATETIME     DEFAULT NULL;
```

---

## 12. Gestion des erreurs

| Situation | Comportement attendu |
|-----------|---------------------|
| Gateway inaccessible (timeout réseau) | Afficher message d'erreur dans le checkout, proposer de réessayer. Ne pas bloquer l'accès aux méthodes domicile. |
| Point relais soumis invalide (ID trafiqué) | Rejeter silencieusement, réafficher la sélection avec message d'erreur. |
| Génération de bordereau échoue | Message Messenger repassé en retry (3 tentatives). Alerte email admin si définitivement échoué. |
| Gateway non configurée (env var manquante) | Exception `\LogicException` au boot si le code gateway est référencé par une méthode active. Sinon, lazy : l'exception ne survient qu'à l'usage. |

---

## 13. Tests

### 13.1 Tests unitaires

| Classe | Ce qui est testé |
|--------|-----------------|
| `ShippingGatewayRegistry` | `get()` lève une exception si code inconnu |
| `MondialRelayGateway` | Transformation des réponses SOAP → `PickupPoint[]` (avec fixtures XML) |
| `SendcloudGateway` | Transformation des réponses JSON → `PickupPoint[]` (avec fixtures JSON) |
| `GenerateShippingLabelHandler` | Hydratation du Shipment après `LabelResponse` |

### 13.2 Tests fonctionnels

- Parcours complet checkout avec méthode relay (mock gateway via `NullShippingGateway`).
- Tentative de soumettre un `pickupPointExternalId` non présent dans les résultats.
- Changement de méthode relay → domicile : effacement des champs `pickupPoint*`.

---

## 14. Plan d'implémentation (phases)

### Phase 1 — Infrastructure (sans appel réseau)

- [ ] `ShippingGatewayInterface` + Value Objects (`PickupPoint`, `PickupPointSearchRequest`, `LabelRequest`, `LabelResponse`)
- [ ] `ShippingGatewayRegistry` avec tag `app.shipping_gateway`
- [ ] `NullShippingGateway`
- [ ] Extension entités (`ShippingMethod::$gatewayCode/Config`, `Shipment::$pickupPoint*`)
- [ ] Migrations Doctrine
- [ ] `CheckoutPickupPointController` + template (données statiques `NullGateway`)
- [ ] `pickup_point_controller.js` + intégration Leaflet

### Phase 2 — Gateway Mondial Relay

- [ ] `MondialRelayGateway` (HttpClient SOAP)
- [ ] Formulaire de configuration admin + test de connexion
- [ ] Fixture `mondial_relay_fr` dans `ShippingFixtures`
- [ ] Tests unitaires avec fixtures XML

### Phase 3 — Gateway Sendcloud (optionnel)

- [ ] `SendcloudGateway` (HttpClient REST)
- [ ] Configuration admin
- [ ] Tests unitaires avec fixtures JSON

### Phase 4 — Génération de bordereaux

- [ ] `GenerateShippingLabelMessage` + Handler
- [ ] Routage Messenger
- [ ] Déclenchement sur `sylius.order.post_fulfill`
- [ ] Bouton admin "Générer bordereau"
- [ ] Affichage PDF dans l'admin commande

### Phase 5 — Polish

- [ ] Gestion des erreurs réseau (retry, logs)
- [ ] Filtres admin (commandes relay)
- [ ] Tests fonctionnels complets
- [ ] Documentation `.env` variables

---

## Annexe A — Arborescence des fichiers cibles

```
src/
└── Component/
    └── Shipping/
        └── Gateway/
            ├── ShippingGatewayInterface.php
            ├── ShippingGatewayRegistry.php
            ├── PickupPoint.php
            ├── PickupPointSearchRequest.php
            ├── LabelRequest.php
            ├── LabelResponse.php
            ├── MondialRelayGateway.php
            ├── SendcloudGateway.php
            └── NullShippingGateway.php
        └── Message/
            └── GenerateShippingLabelMessage.php
        └── MessageHandler/
            └── GenerateShippingLabelHandler.php
src/
└── Controller/
    └── Front/
        └── CheckoutPickupPointController.php
templates/
└── front/
    └── checkout/
        └── pickup_point.html.twig
assets/
└── controllers/
    └── pickup_point_controller.js
config/
└── services/
    └── shipping.yaml
```

---

## Annexe B — Questions ouvertes

1. **Mondial Relay SOAP vs REST :** Mondial Relay propose une API SOAP v5 historique et une API REST (sous convention). Confirmer la version disponible avec les credentials du client.

2. **Leaflet vs widget propriétaire :** Mondial Relay fournit un widget JS officiel (iframe). L'utiliser simplifie l'implémentation mais réduit le contrôle UX. Décision à prendre avec le client.

3. **Géocodage :** La recherche par adresse texte requiert parfois un geocoding préalable (adresse → lat/lng). Évaluer si l'API Mondial Relay l'accepte directement ou s'il faut un service tier (Nominatim/OSM — gratuit).

4. **Multi-gateway par méthode :** Le modèle actuel associe **une** gateway à **une** méthode. Un cas comme "Sendcloud avec DPD" et "Sendcloud avec Bpost" nécessite deux méthodes distinctes — ce qui semble raisonnable.

5. **Stockage du bordereau :** Le `labelUrl` peut pointer vers une URL externe (URL Sendcloud, par ex.) ou un fichier stocké dans Flysystem. À décider selon la durée de rétention souhaitée.

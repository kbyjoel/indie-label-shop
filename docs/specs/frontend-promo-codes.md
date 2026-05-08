# Cahier de spécifications — Promotions (frontend)

## 1. Vue d'ensemble

Le système de promotions couvre deux modes, gérés par le même `PromotionProcessor` Sylius :

| Mode | `couponBased` | Déclenchement |
|------|--------------|---------------|
| **Automatique** | `false` | S'applique dès que les règles sont satisfaites, sans action utilisateur |
| **Coupon** | `true` | Requiert la saisie d'un code coupon par l'acheteur |

Le frontend repose sur les services natifs de Sylius (`PromotionProcessor`, rule checkers, action commands) — les entités héritent déjà de Sylius et les interfaces requises sont satisfaites.

---

## 2. Corrections admin préalables

Avant d'implémenter le frontend, des désalignements existent entre le format de configuration stocké en base et ce qu'attendent les services Sylius.

### 2.1 Fichier : `PromotionController::applyRulesConfiguration()`

| Règle | Actuel (stocké en base) | Sylius attend | Action |
|-------|------------------------|---------------|--------|
| `nth_order` | `['nth_order' => 2]` | `['nth' => 2]` | Changer la clé |
| `contains_product` | `['products' => [1, 3]]` (IDs) | `['product_code' => 'code']` (un seul, par code) | Voir §2.2 |
| `total_of_items_from_taxon` | `['taxon' => 'vinyl', 'WEB' => ['amount' => 5000]]` | `['WEB' => ['taxon' => 'vinyl', 'amount' => 5000]]` | Déplacer `taxon` dans le nœud WEB |

**Correction `nth_order` :**
```php
// avant
'nth_order' => ['nth_order' => (int) $ruleForm->get('count')->getData()],
// après
'nth_order' => ['nth' => (int) $ruleForm->get('count')->getData()],
```

**Correction `total_of_items_from_taxon` :**
```php
// avant
'total_of_items_from_taxon' => ['taxon' => $ruleForm->get('taxonCode')->getData(), 'WEB' => ['amount' => ...]],
// après
'total_of_items_from_taxon' => ['WEB' => ['taxon' => $ruleForm->get('taxonCode')->getData(), 'amount' => (int) $ruleForm->get('taxonAmount')->getData()]],
```

### 2.2 Règle `contains_product` — solution recommandée

`ContainsProductRuleChecker` de Sylius attend un seul produit identifié par son **code** (string), pas ses IDs. Deux options :

**Option A (recommandée) :** Passer à un select unique par code produit dans `PromotionRuleType` :
```php
->add('product', EntityType::class, [
    'class' => Product::class,
    'choice_label' => 'name',
    'choice_value' => 'code',  // stocke le code, pas l'ID
    'mapped' => false,
    'multiple' => false,
])
```
Configuration stockée : `['product_code' => 'mon-album-code']`

**Option B :** Écrire un rule checker custom `ContainsAnyProductRuleChecker` qui gère un tableau de codes — utile si le multi-produit est nécessaire métier.

### 2.3 Fichier : `PromotionController::applyActionsConfiguration()` + `PromotionActionType`

| Action | Problème | Solution |
|--------|---------|---------|
| `item_percentage_discount` | N'existe pas dans Sylius | Supprimer du formulaire, remplacer par `unit_percentage_discount` |
| `item_fixed_discount` | N'existe pas dans Sylius | Supprimer du formulaire, remplacer par `unit_fixed_discount` |
| `unit_percentage_discount` | Config non channel-aware : `['percentage' => 0.10]` | Doit devenir `['WEB' => ['percentage' => 0.10]]` |

**Liste finale des types d'actions dans `PromotionActionType` :**
```php
'choices' => [
    'Remise % sur la commande'    => 'order_percentage_discount',
    'Remise fixe sur la commande' => 'order_fixed_discount',
    'Remise % sur la livraison'   => 'shipping_percentage_discount',
    'Remise fixe par unité'       => 'unit_fixed_discount',
    'Remise % par unité'          => 'unit_percentage_discount',
],
```

**Correction `applyActionsConfiguration()` :**
```php
$configuration = match ($action->getType()) {
    'order_fixed_discount',
    'unit_fixed_discount'          => ['WEB' => ['amount' => (int) $actionForm->get('amount')->getData()]],
    'unit_percentage_discount'     => ['WEB' => ['percentage' => $actionForm->get('percentage')->getData()]],
    'order_percentage_discount',
    'shipping_percentage_discount' => ['percentage' => $actionForm->get('percentage')->getData()],
    default                        => [],
};
```

> **Note migration :** Les promotions existantes en base avec les anciens formats ou types doivent être migrées ou recréées manuellement.

---

## 3. Architecture Sylius à câbler

### 3.1 Services natifs disponibles

```
vendor/sylius/promotion/
├── Processor/PromotionProcessor.php                    # Orchestre tout (coupon + automatique)
├── Checker/Eligibility/
│   ├── CompositePromotionEligibilityChecker.php        # Agrège les checkers
│   ├── PromotionDurationEligibilityChecker.php         # startsAt / endsAt
│   ├── PromotionUsageLimitEligibilityChecker.php       # usageLimit promo
│   ├── PromotionRulesEligibilityChecker.php            # évalue les règles
│   ├── PromotionSubjectCouponEligibilityChecker.php    # coupon requis (passe si non couponBased)
│   ├── PromotionCouponUsageLimitEligibilityChecker.php
│   └── CompositePromotionCouponEligibilityChecker.php
└── Action/PromotionApplicator.php                      # Crée les ajustements

vendor/sylius/core/Promotion/
├── Checker/Rule/
│   ├── CartQuantityRuleChecker.php
│   ├── ItemTotalRuleChecker.php
│   ├── ContainsProductRuleChecker.php
│   ├── CustomerGroupRuleChecker.php
│   ├── NthOrderRuleChecker.php
│   ├── TotalOfItemsFromTaxonRuleChecker.php
│   └── HasTaxonRuleChecker.php
└── Action/
    ├── FixedDiscountPromotionActionCommand.php
    ├── PercentageDiscountPromotionActionCommand.php
    ├── UnitFixedDiscountPromotionActionCommand.php
    ├── UnitPercentageDiscountPromotionActionCommand.php
    └── ShippingPercentageDiscountPromotionActionCommand.php
```

### 3.2 Compatibilité de l'entité `Order`

`Order` étend `Sylius\Component\Core\Model\Order` qui implémente déjà :
- `PromotionSubjectInterface` (requis par `PromotionProcessor`)
- `CountablePromotionSubjectInterface`
- `PromotionCouponAwarePromotionSubjectInterface`
- La relation `promotionCoupon` est déjà mappée

**Aucune modification de l'entité Order n'est nécessaire.**

### 3.3 Câblage des services

La configuration est séparée en deux endroits selon la nature des classes :

- **Classes vendor Sylius** (non modifiables) → `config/services/promotion.yaml`
- **Classes `App\` custom** → attributs PHP directement sur la classe

#### `config/services.yaml` — ajout de l'import

```yaml
imports:
    - { resource: services/promotion.yaml }
```

#### `config/services/promotion.yaml` — services vendor Sylius

```yaml
services:

  # --- Rule checkers (vendor — ne peuvent pas avoir d'attributs PHP) ---
  Sylius\Component\Core\Promotion\Checker\Rule\CartQuantityRuleChecker:
    tags:
      - { name: sylius.promotion_rule_checker, type: cart_quantity }

  Sylius\Component\Core\Promotion\Checker\Rule\ItemTotalRuleChecker:
    tags:
      - { name: sylius.promotion_rule_checker, type: item_total }

  Sylius\Component\Core\Promotion\Checker\Rule\ContainsProductRuleChecker:
    tags:
      - { name: sylius.promotion_rule_checker, type: contains_product }

  Sylius\Component\Core\Promotion\Checker\Rule\CustomerGroupRuleChecker:
    tags:
      - { name: sylius.promotion_rule_checker, type: customer_group }

  Sylius\Component\Core\Promotion\Checker\Rule\NthOrderRuleChecker:
    tags:
      - { name: sylius.promotion_rule_checker, type: nth_order }

  Sylius\Component\Core\Promotion\Checker\Rule\TotalOfItemsFromTaxonRuleChecker:
    tags:
      - { name: sylius.promotion_rule_checker, type: total_of_items_from_taxon }

  # --- Action commands (vendor) ---
  Sylius\Component\Core\Promotion\Action\FixedDiscountPromotionActionCommand:
    tags:
      - { name: sylius.promotion_action, type: order_fixed_discount }

  Sylius\Component\Core\Promotion\Action\PercentageDiscountPromotionActionCommand:
    tags:
      - { name: sylius.promotion_action, type: order_percentage_discount }

  Sylius\Component\Core\Promotion\Action\UnitFixedDiscountPromotionActionCommand:
    tags:
      - { name: sylius.promotion_action, type: unit_fixed_discount }

  Sylius\Component\Core\Promotion\Action\UnitPercentageDiscountPromotionActionCommand:
    tags:
      - { name: sylius.promotion_action, type: unit_percentage_discount }

  Sylius\Component\Core\Promotion\Action\ShippingPercentageDiscountPromotionActionCommand:
    tags:
      - { name: sylius.promotion_action, type: shipping_percentage_discount }

  # --- Registries ---
  app.promotion_rule_checker_registry:
    class: Sylius\Component\Registry\ServiceRegistry
    arguments:
      - 'Sylius\Component\Promotion\Checker\Rule\RuleCheckerInterface'
      - 'promotion rule checker'

  app.promotion_action_registry:
    class: Sylius\Component\Registry\ServiceRegistry
    arguments:
      - 'Sylius\Component\Promotion\Action\PromotionActionCommandInterface'
      - 'promotion action'

  # --- Eligibility checkers ---
  app.promotion_eligibility_checker:
    class: Sylius\Component\Promotion\Checker\Eligibility\CompositePromotionEligibilityChecker
    arguments:
      - - '@app.promotion_duration_eligibility_checker'
        - '@app.promotion_usage_limit_eligibility_checker'
        - '@app.promotion_rules_eligibility_checker'
        - '@app.promotion_subject_coupon_eligibility_checker'

  app.promotion_duration_eligibility_checker:
    class: Sylius\Component\Promotion\Checker\Eligibility\PromotionDurationEligibilityChecker

  app.promotion_usage_limit_eligibility_checker:
    class: Sylius\Component\Promotion\Checker\Eligibility\PromotionUsageLimitEligibilityChecker

  app.promotion_rules_eligibility_checker:
    class: Sylius\Component\Promotion\Checker\Eligibility\PromotionRulesEligibilityChecker
    arguments: ['@app.promotion_rule_checker_registry']

  app.promotion_subject_coupon_eligibility_checker:
    class: Sylius\Component\Promotion\Checker\Eligibility\PromotionSubjectCouponEligibilityChecker

  # --- Coupon eligibility checkers (utilisés uniquement lors de la saisie du coupon) ---
  app.promotion_coupon_eligibility_checker:
    class: Sylius\Component\Promotion\Checker\Eligibility\CompositePromotionCouponEligibilityChecker
    arguments:
      - - '@app.promotion_coupon_usage_limit_eligibility_checker'

  app.promotion_coupon_usage_limit_eligibility_checker:
    class: Sylius\Component\Promotion\Checker\Eligibility\PromotionCouponUsageLimitEligibilityChecker

  # --- Applicator ---
  app.promotion_applicator:
    class: Sylius\Component\Promotion\Action\PromotionApplicator
    arguments: ['@app.promotion_action_registry']

  # --- Processor ---
  app.promotion_processor:
    class: Sylius\Component\Promotion\Processor\PromotionProcessor
    arguments:
      - '@app.pre_qualified_promotions_provider'
      - '@app.promotion_eligibility_checker'
      - '@app.promotion_applicator'

  app.pre_qualified_promotions_provider:
    class: Sylius\Component\Promotion\Provider\PreQualifiedPromotionsProvider
    arguments: ['@doctrine.orm.entity_manager']
```

> **Note :** `PromotionSubjectCouponEligibilityChecker` est inclus dans le composite principal : pour une promotion `couponBased = false`, il retourne `true` sans contrôle ; pour `couponBased = true`, il vérifie que le coupon de la commande correspond à cette promotion. C'est ce mécanisme qui permet au même processor de gérer les deux modes.

> **Note :** Les dépendances de certains checkers (ex. `CustomerGroupRuleChecker`) sont à vérifier dans leurs constructeurs lors de l'implémentation.

#### Classes `App\` custom — attributs PHP

Pour tout rule checker ou action command custom écrit dans `App\`, utiliser les attributs PHP directement sur la classe :

```php
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('sylius.promotion_rule_checker', attributes: ['type' => 'contains_product'])]
class ContainsAnyProductRuleChecker implements RuleCheckerInterface
{
    // ...
}
```

Le controller frontend `PromotionController` est auto-configuré via le scan `App\` — aucune déclaration YAML nécessaire.

---

## 4. Promotions automatiques (`couponBased = false`)

### 4.1 Principe

Une promotion automatique s'applique dès que toutes ses règles sont satisfaites, sans action de l'acheteur. Le `PromotionProcessor` est appelé **à chaque modification du panier** (ajout, suppression, changement de quantité) — c'est la différence principale avec les coupons.

### 4.2 Intégration dans `CartManager`

Le processor doit être appelé systématiquement après tout recalcul, qu'un coupon soit actif ou non :

```php
// CartManager.php — après chaque recalculate()
$this->promotionProcessor->process($this->cart);
```

Le processor :
1. Révoque d'abord tous les ajustements promotion existants
2. Réévalue toutes les promotions actives (coupon + automatiques)
3. Réapplique celles qui sont éligibles

Un coupon présent sur la commande (`order.promotionCoupon`) est pris en compte dans cette passe : `PromotionSubjectCouponEligibilityChecker` valide la promo coupon, et les autres passent car elles ne sont pas `couponBased`.

### 4.3 Ordre d'évaluation et priorité

Le champ `priority` (entier, plus grand = plus prioritaire) détermine l'ordre d'évaluation. Le processor applique les promotions **exclusives en premier** :

1. Si une promotion exclusive (`exclusive = true`) est éligible → elle est appliquée, les autres sont ignorées (qu'elles soient automatiques ou coupon-based)
2. Si aucune exclusive n'est éligible → toutes les promotions non-exclusives éligibles sont appliquées (cumulées)

### 4.4 Affichage dans le panier

Les promotions automatiques s'affichent dans le récapitulatif **sans bouton "Retirer"** — l'acheteur ne peut pas les désactiver manuellement.

```
Sous-total articles     60,00 €
Promo fidélité −10%    − 6,00 €   ← automatique, sans bouton retirer
Code SUMMER20          − 5,00 €   ← coupon, avec bouton [Retirer ×]
Livraison               5,00 €
──────────────────────────────────
Total                  54,00 €
```

En Twig, la distinction se fait via la promotion parente de l'ajustement :

```twig
{% for adjustment in order.getAdjustments('promotion') %}
  <tr>
    <td>{{ adjustment.label }}</td>
    <td>{{ adjustment.amount | format_price }}</td>
    {% if adjustment.originCode == order.promotionCoupon?.promotion?.code %}
      <td><button data-action="coupon#removeCoupon">×</button></td>
    {% else %}
      <td></td>  {# automatique, pas de retrait possible #}
    {% endif %}
  </tr>
{% endfor %}
```

### 4.5 Révocation automatique

Si une modification du panier entraîne que les règles d'une promotion automatique ne sont plus satisfaites, le processor révoque ses ajustements au prochain appel. Aucune action supplémentaire n'est requise — le recalcul affiche simplement le nouveau total sans la remise.

---

## 5. Promotions coupon (`couponBased = true`)

### 5.1 Interface panier

**Emplacement :** `templates/front/cart/index.html.twig`, entre les articles et le récapitulatif.

**État sans coupon :**
```
[ Vous avez un code promo ? ]
[ Code ________________ ] [ Appliquer ]
```

**État avec coupon actif :**
```
Code "SUMMER20" appliqué (−12,00 €)  [Retirer ×]
```

**Comportement :**
- Soumission formulaire ou touche Entrée
- Appel `fetch` vers l'endpoint, pas de rechargement de page
- Mise à jour des totaux via Stimulus
- Message d'erreur inline sous le champ

### 5.2 Endpoints à créer

#### `POST /cart/apply-coupon`

**Requête :**
```json
{ "coupon_code": "SUMMER20" }
```

**Réponse succès (200) :**
```json
{
  "success": true,
  "coupon_code": "SUMMER20",
  "promotion_name": "Soldes été 20%",
  "adjustments": [
    { "label": "Promo fidélité −10%", "amount": -600, "removable": false },
    { "label": "Soldes été 20%", "amount": -1200, "removable": true }
  ],
  "items_total": 6000,
  "promotions_total": -1800,
  "shipping_total": 500,
  "total": 4700
}
```

> La réponse inclut **tous** les ajustements promotion (automatiques + coupon) pour permettre au frontend de rafraîchir l'affichage complet.

**Réponse erreur (422) :**
```json
{
  "success": false,
  "error_code": "PROMOTION_RULES_NOT_MET",
  "message": "Votre panier ne remplit pas les conditions requises pour ce code promo."
}
```

#### `DELETE /cart/remove-coupon`

**Réponse (200) :**
```json
{
  "success": true,
  "adjustments": [
    { "label": "Promo fidélité −10%", "amount": -600, "removable": false }
  ],
  "items_total": 6000,
  "promotions_total": -600,
  "shipping_total": 500,
  "total": 5900
}
```

> Les promotions automatiques toujours éligibles restent appliquées après suppression du coupon.

### 5.3 Logique du `PromotionController` frontend

```php
// src/Controller/Front/PromotionController.php

public function applyCoupon(Request $request): JsonResponse
{
    $order = $this->cartContext->getCart();
    $code  = $request->toArray()['coupon_code'] ?? '';

    // 1. Trouver le coupon
    $coupon = $this->couponRepository->findOneBy(['code' => $code]);
    if (null === $coupon) {
        return $this->json(['success' => false, 'error_code' => 'COUPON_NOT_FOUND',
            'message' => "Ce code promo n'existe pas."], 422);
    }

    // 2. Valider le coupon (expiration, limite d'usage globale)
    if (!$this->couponEligibilityChecker->isEligible($order, $coupon)) {
        return $this->json(['success' => false, 'error_code' => 'COUPON_NOT_ELIGIBLE',
            'message' => "Ce code promo n'est plus valide."], 422);
    }

    // 3. Affecter le coupon et relancer le processor complet
    //    (révoque tout, réévalue automatiques + coupon)
    $order->setPromotionCoupon($coupon);
    $this->promotionProcessor->process($order);

    // 4. Vérifier que la promotion coupon a bien été appliquée
    $couponPromotion = $coupon->getPromotion();
    if (!$order->hasPromotion($couponPromotion)) {
        $order->setPromotionCoupon(null);
        $this->promotionProcessor->process($order); // réapplique les automatiques
        return $this->json(['success' => false, 'error_code' => 'PROMOTION_RULES_NOT_MET',
            'message' => "Votre panier ne remplit pas les conditions requises pour ce code promo."], 422);
    }

    $this->em->flush();

    return $this->json($this->buildCartResponse($order));
}

public function removeCoupon(): JsonResponse
{
    $order = $this->cartContext->getCart();

    // Supprimer le coupon et relancer le processor complet
    // (révoque tout, réévalue uniquement les automatiques)
    $order->setPromotionCoupon(null);
    $this->promotionProcessor->process($order);
    $this->em->flush();

    return $this->json($this->buildCartResponse($order));
}
```

### 5.4 Validation du coupon (Sylius natif)

**`CompositePromotionCouponEligibilityChecker`** (validations propres au coupon avant de passer au processor) :

| Checker Sylius | Vérifie |
|---------------|---------|
| `PromotionCouponUsageLimitEligibilityChecker` | `coupon.usageLimit` non atteinte (`used < usageLimit`) |

**`CompositePromotionEligibilityChecker`** (dans le processor, pour la promotion coupon comme pour les automatiques) :

| Checker Sylius | Vérifie |
|---------------|---------|
| `PromotionDurationEligibilityChecker` | `startsAt` / `endsAt` |
| `PromotionUsageLimitEligibilityChecker` | `promotion.usageLimit` |
| `PromotionRulesEligibilityChecker` | Toutes les règles (ET logique) |
| `PromotionSubjectCouponEligibilityChecker` | Coupon présent sur la commande = coupon de cette promo |

### 5.5 Messages d'erreur exposés à l'utilisateur

| Situation | Message |
|-----------|---------|
| Code inconnu | "Ce code promo n'existe pas." |
| Coupon expiré ou limite globale atteinte | "Ce code promo n'est plus valide." |
| Règles non satisfaites | "Votre panier ne remplit pas les conditions requises pour ce code promo." |
| Client non connecté + règle groupe/nième commande | "Connectez-vous pour utiliser ce code promo." |

> Les checkers Sylius retournent un booléen sans message de raison — les messages sont génériques, sauf si on surcharge les checkers pour lever des exceptions typées.

---

## 6. Affichage des remises (commun aux deux modes)

### 6.1 Page panier

```
Sous-total articles     60,00 €
Promo fidélité −10%    − 6,00 €
Code SUMMER20          −12,00 €  [Retirer ×]
Livraison               5,00 €
──────────────────────────────────
Total                  47,00 €
```

Les lignes proviennent de `order.getAdjustments('promotion')` (ajustements sur `Order`) et de l'agrégat des `order.getAdjustmentsTotalRecursively('promotion')` pour les unités.

### 6.2 Résumé checkout (`_summary.html.twig`)

Même structure, sans le bouton "Retirer" (le coupon ne peut plus être modifié en cours de tunnel).

### 6.3 Page confirmation et email

Idem checkout. La remise apparaît dans le récapitulatif de commande.

---

## 7. Ajustements créés par Sylius

Les action commands Sylius créent des `Adjustment` avec les types suivants :

| Action | Type d'ajustement | Cible |
|--------|------------------|-------|
| `order_fixed_discount` | `ORDER_PROMOTION_ADJUSTMENT` | `Order` |
| `order_percentage_discount` | `ORDER_PROMOTION_ADJUSTMENT` | `Order` |
| `unit_fixed_discount` | `ORDER_UNIT_PROMOTION_ADJUSTMENT` | `OrderItemUnit` |
| `unit_percentage_discount` | `ORDER_UNIT_PROMOTION_ADJUSTMENT` | `OrderItemUnit` |
| `shipping_percentage_discount` | `ORDER_SHIPPING_PROMOTION_ADJUSTMENT` | ajustement shipping |

Pour afficher le total des remises : sommer `ORDER_PROMOTION_ADJUSTMENT` + `ORDER_UNIT_PROMOTION_ADJUSTMENT`.

---

## 8. Stimulus controller frontend

```
assets/controllers/coupon_controller.js
  - Cibles : champ code, bouton appliquer, bouton retirer, zone erreur, zone totaux, liste ajustements
  - applyCoupon()  → POST /cart/apply-coupon  → rafraîchit totaux + liste ajustements
  - removeCoupon() → DELETE /cart/remove-coupon → rafraîchit totaux + liste ajustements
  - showError(message) / clearError()
```

---

## 9. Cas limites

| Scénario | Comportement |
|----------|-------------|
| Article retiré → règle `cart_quantity` non satisfaite | Processor révoque la promo au prochain recalcul (automatique) ou au prochain process (coupon) ; afficher le nouveau total |
| Promotion exclusive + promo automatique | Si l'exclusive est éligible, les autres (y compris automatiques) sont ignorées |
| Promo automatique exclusive + coupon saisi | Le coupon n'est pas appliqué si une automatique exclusive est déjà active |
| Promo coupon exclusive + automatique active | Le coupon exclusif prime ; l'automatique est révoquée |
| Deux promos automatiques non-exclusives | Les deux s'appliquent et se cumulent |
| Remise > total panier | Les action commands plafonnent à 0 (logique Sylius native) |
| Nouveau coupon saisi alors qu'un est actif | Le processor repart de zéro (revert tout) — l'ancien coupon est remplacé |
| Coupon sans `usageLimit` | Aucune vérification de limite globale |
| Promotion sans règles | S'applique à tout panier (`PromotionRulesEligibilityChecker` retourne `true`) |
| Client non connecté + règle `customer_group` ou `nth_order` | Retourner `CUSTOMER_LOGIN_REQUIRED` |

---

## 10. Documentation à rédiger

À l'issue de l'implémentation, créer `docs/frontend/promotions.md` dans le même style que les autres docs du dossier (anglais, orientée développeur). Elle doit couvrir :

- **Architecture** — diagramme des services Sylius câblés (`PromotionProcessor`, eligibility checkers, action commands, registries) et leur rôle
- **Automatic vs coupon-based promotions** — distinction, quand le processor tourne, logique d'exclusivité et de priorité
- **Endpoints** — tableau `POST /cart/apply-coupon` / `DELETE /cart/remove-coupon` avec exemples de requêtes/réponses
- **Stimulus controller** — targets, actions, comportement
- **Adding a new rule or action type** — étapes pour étendre le système (implémenter l'interface, enregistrer via `#[AutoconfigureTag]`, ajouter au formulaire admin, format de configuration JSON)
- **Admin configuration reference** — tableau des types de règles et d'actions avec leurs paramètres de configuration (format stocké en base)
- **Testing in development** — scénarios à tester manuellement (promo auto sans règle, promo avec règle quantité, coupon, exclusivité)

Mettre aussi à jour `docs/frontend/README.md` : ajouter une ligne dans le tableau "Implementation steps" pointant vers `promotions.md`.

---

## 11. Hors périmètre (V1)

- Limite par client `perCustomerUsageLimit` — nécessite un historique d'usage par client, non fourni par Sylius core par défaut
- Génération de coupons en masse depuis le frontend
- Programme de fidélité / points

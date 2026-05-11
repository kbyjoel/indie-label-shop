<?php

declare(strict_types=1);

namespace App\Component\Shipping\Gateway;

use App\Entity\Order;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.shipping_gateway')]
interface ShippingGatewayInterface
{
    public function getCode(): string;

    public function getName(): string;

    /**
     * Recherche les points relais proches de l'adresse donnée.
     * Utilisé par NullShippingGateway et les tests. En production, la recherche
     * est déléguée au widget JS officiel du transporteur.
     *
     * @param array<string,mixed> $gatewayConfig
     * @return PickupPoint[]
     */
    public function searchPickupPoints(PickupPointSearchRequest $request, array $gatewayConfig = []): array;

    /**
     * Récupère un point relais par son ID externe.
     * Appelé côté serveur lors du POST pour valider l'ID soumis par le widget
     * et hydrater le Shipment avec les données canoniques.
     *
     * @param array<string,mixed> $gatewayConfig
     */
    public function getPickupPoint(string $externalId, array $gatewayConfig = []): PickupPoint;

    /**
     * Génère un bordereau d'expédition. Appelé via Symfony Messenger (Phase 4).
     *
     * @param array<string,mixed> $gatewayConfig
     */
    public function generateLabel(LabelRequest $request, array $gatewayConfig = []): LabelResponse;

    /**
     * Vérifie que la configuration est valide (test de connexion admin).
     *
     * @param array<string,mixed> $gatewayConfig
     */
    public function validateConfiguration(array $gatewayConfig): bool;

    /**
     * Nom du Stimulus controller qui initialise le widget JS de sélection.
     * Correspond au nom de fichier assets/controllers/<name>_controller.js
     * converti en identifiant Stimulus (ex: "pickup-point-mondial-relay").
     */
    public function getWidgetControllerName(): string;

    /**
     * Configuration publique transmise au Stimulus controller via data attribute.
     * Ne doit contenir aucune clé secrète (visible dans le HTML rendu).
     *
     * @param array<string,mixed> $gatewayConfig  Champs non sensibles depuis ShippingMethod
     * @return array<string,mixed>
     */
    public function getWidgetConfig(array $gatewayConfig, Order $cart): array;
}

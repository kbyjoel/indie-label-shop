<?php

namespace App\Component\AdminMenu;

use Aropixel\AdminBundle\Component\Menu\Builder\AdminMenuBuilderInterface;
use Aropixel\AdminBundle\Component\Menu\Model\Link;
use Aropixel\AdminBundle\Component\Menu\Model\Menu;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('admin_menu_builder')]
#[AsAlias(id: AdminMenuBuilderInterface::class)]
class CustomAdminMenuBuilder implements AdminMenuBuilderInterface
{
    public function __construct(
        private readonly Security $security,
    ) {
    }

    public function buildMenu(): array
    {
        $additionalMenus = [];
        $additionalMenus[] = $this->buildContentMenu();
        $additionalMenus[] = $this->buildMerchMenu();
        $additionalMenus[] = $this->buildShopMenu();
        $additionalMenus[] = $this->buildSettingsMenu();

        // Menus reserved for ROLE_SUPER_ADMIN
        if ($this->security->isGranted('ROLE_SUPER_ADMIN')) {
            $additionalMenus[] = $this->buildAdminMenu();
        }

        return $additionalMenus;
    }

    private function buildContentMenu(): Menu
    {
        $menu = new Menu('content', 'Catalogue');
        $menu->addItem(new Link('Groupes', 'admin_band_index', [], ['icon' => 'fas fa-newspaper']));
        $menu->addItem(new Link('Albums', 'admin_album_index', [], ['icon' => 'fas fa-compact-disc']));
        $menu->addItem(new Link('Médias', 'admin_media_index', [], ['icon' => 'fas fa-photo-video']));

        return $menu;
    }

    private function buildMerchMenu(): Menu
    {
        $menu = new Menu('content', 'Merchandising');
        $menu->addItem(new Link('Produits', 'admin_product_index', [], ['icon' => 'fas fa-list-ul']));
        $menu->addItem(new Link('Options', 'admin_product_option_index', [], ['icon' => 'fas fa-list-ul']));

        return $menu;
    }

    private function buildSettingsMenu(): Menu
    {
        $menu = new Menu('settings', 'Réglages');
        $menu->addItem(new Link('Boutique', 'admin_settings_index', [], ['icon' => 'fas fa-store']));
        $menu->addItem(new Link('Pays', 'admin_country_index', [], ['icon' => 'fas fa-list-ul']));
        $menu->addItem(new Link('Zones', 'admin_zone_index', [], ['icon' => 'fas fa-list-ul']));
        $menu->addItem(new Link('Catégories de taxes', 'admin_tax_category_index', [], ['icon' => 'fas fa-tags']));
        $menu->addItem(new Link('Taux de taxes', 'admin_tax_rate_index', [], ['icon' => 'fas fa-percentage']));

        return $menu;
    }

    private function buildShopMenu(): Menu
    {
        $menu = new Menu('content', 'Shop');
        $menu->addItem(new Link('Clients', 'admin_customer_index', [], ['icon' => 'fas fa-list-ul']));
        $menu->addItem(new Link('Paiements', 'admin_payment_method_index', [], ['icon' => 'fas fa-credit-card']));
        $menu->addItem(new Link('Livraison', 'admin_shipping_method_index', [], ['icon' => 'fas fa-truck']));
        $menu->addItem(new Link('Promotions', 'admin_promotion_index', [], ['icon' => 'fas fa-percentage']));

        return $menu;
    }

    private function buildAdminMenu(): Menu
    {
        $menu = new Menu('admin', 'Administration');
        $menu->addItem(new Link('Administrateurs', 'aropixel_admin_user_index', [], ['icon' => 'fas fa-users-cog']));

        return $menu;
    }
}

<?php

namespace App\Component\AdminMenu;

use Aropixel\AdminBundle\Component\Menu\Builder\AdminMenuBuilderInterface;
use Aropixel\AdminBundle\Component\Menu\Model\Link;
use Aropixel\AdminBundle\Component\Menu\Model\Menu;
use Aropixel\AdminBundle\Component\Menu\Model\SubMenu;
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
        $menu->addItem(new Link('Médias', 'admin_media_index', [], ['icon' => 'fas fa-photo-video']));
        $menu->addItem(new Link('Options', 'admin_product_option_index', [], ['icon' => 'fas fa-list-ul']));

        return $menu;
    }

    private function buildAdminMenu(): Menu
    {
        $menu = new Menu('admin', 'Administration');
        $menu->addItem(new Link('Administrateurs', 'aropixel_admin_user_index', [], ['icon' => 'fas fa-users-cog']));
        return $menu;
    }
}

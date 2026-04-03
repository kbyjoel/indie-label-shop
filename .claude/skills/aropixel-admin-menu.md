---
name: aropixel-admin-menu
description: >
  Gère le menu de l'administration Aropixel AdminBundle.
  Utilise ce skill quand l'utilisateur veut ajouter une entrée dans le menu admin,
  créer une nouvelle section de menu, ou modifier la navigation de l'interface d'administration.
---

# Skill : Menu Admin AropixelAdminBundle

## Fichier à modifier

Le menu admin du projet est défini dans un seul fichier :

```
application/src/Component/AdminMenu/CustomAdminMenuBuilder.php
```

C'est une classe qui implémente `AdminMenuBuilderInterface` et construit le menu via des méthodes privées par section.

## Structure actuelle du menu

Le menu est organisé en sections (méthodes privées) :
- `buildContentMenu()` → **Catalogue** : Groupes, Albums, Médias
- `buildMerchMenu()` → **Merchandising** : Produits, Options
- `buildShopMenu()` → **Shop** : Clients, Zones
- `buildAdminMenu()` → **Administration** (ROLE_SUPER_ADMIN uniquement) : Administrateurs

## Ajouter un lien dans une section existante

Ajouter un `new Link(...)` dans la méthode correspondante :

```php
$menu->addItem(new Link('Label affiché', 'nom_de_route_index', [], ['icon' => 'fas fa-icon']));
```

Exemple — ajouter "Pays" dans la section Shop :

```php
private function buildShopMenu(): Menu
{
    $menu = new Menu('content', 'Shop');
    $menu->addItem(new Link('Clients', 'admin_customer_index', [], ['icon' => 'fas fa-list-ul']));
    $menu->addItem(new Link('Zones', 'admin_zone_index', [], ['icon' => 'fas fa-list-ul']));
    $menu->addItem(new Link('Pays', 'admin_country_index', [], ['icon' => 'fas fa-flag']));
    return $menu;
}
```

## Créer une nouvelle section de menu

1. Ajouter une méthode privée `buildXxxMenu()` :

```php
private function buildXxxMenu(): Menu
{
    $menu = new Menu('xxx', 'Nom de la section');
    $menu->addItem(new Link('Mon entité', 'admin_monentite_index', [], ['icon' => 'fas fa-list-ul']));
    return $menu;
}
```

2. L'appeler dans `buildMenu()` :

```php
public function buildMenu(): array
{
    $additionalMenus = [];
    $additionalMenus[] = $this->buildContentMenu();
    $additionalMenus[] = $this->buildXxxMenu(); // <-- ajouter ici
    // ...
    return $additionalMenus;
}
```

## Ajouter un sous-menu (SubMenu)

```php
$subMenu = new SubMenu('Sous-section', ['icon' => 'fas fa-folder'], 'id-sous-menu');
$subMenu->addItem(new Link('Item 1', 'admin_item1_index', [], ['icon' => 'fas fa-file']));
$subMenu->addItem(new Link('Item 2', 'admin_item2_index', [], ['icon' => 'fas fa-file']));
$menu->addItem($subMenu);
```

## Modèles disponibles

| Classe | Usage |
|--------|-------|
| `Menu` | Section principale du sidebar — `new Menu(string $id, string $label)` |
| `Link` | Lien simple vers une route — `new Link(string $label, string $routeName, array $routeParams, array $properties)` |
| `SubMenu` | Groupe collapsible — `new SubMenu(string $label, array $properties, string $id)` |

La propriété `icon` attend une classe FontAwesome : `'fas fa-...'`.

## Imports nécessaires

```php
use Aropixel\AdminBundle\Component\Menu\Model\Link;
use Aropixel\AdminBundle\Component\Menu\Model\Menu;
use Aropixel\AdminBundle\Component\Menu\Model\SubMenu; // si besoin
```

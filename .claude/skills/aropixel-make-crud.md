---
name: aropixel-make-crud
description: >
  Génère et complète un CRUD d'administration Symfony avec AropixelAdminBundle.
  Utilise ce skill dès que l'utilisateur demande à créer une section admin, un CRUD admin,
  une interface de gestion pour une entité, ou à "ajouter l'admin" d'une entité.
  Ce skill couvre le workflow complet : commande make:crud, complétion du DataTable,
  template de liste, template de formulaire, ajout dans le menu admin, breadcrumbs et macros. À utiliser aussi quand
  l'utilisateur dit "génère le controller admin", "crée la page de liste admin", ou demande
  à personnaliser les colonnes/champs d'un CRUD déjà généré.
---

# Skill : Générer un CRUD Admin avec AropixelAdminBundle

## Workflow complet

### Étape 1 — Lancer la commande

```bash
php bin/console aropixel:make:crud
```

Le terminal demande :
1. **Entity Class** : ex. `App\Entity\Article`
2. **FormType Class** : ex. `App\Form\ArticleType`

Les fichiers générés :
- `src/Controller/Admin/ArticleController.php`
- `templates/admin/article/index.html.twig`
- `templates/admin/article/form.html.twig`

---

### Étape 2 — Compléter le DataTable dans le Controller

Par défaut, seule la colonne `ID` est générée. **Toujours compléter** avec les colonnes pertinentes de l'entité.

#### Règles de sélection des colonnes

| Type de propriété Doctrine | Colonne DataTable | Exemple |
|---|---|---|
| `string`, `text` | Label simple, `orderBy` sur le champ | `['label' => 'Titre', 'orderBy' => 'title']` |
| `date`, `datetime` | Format `d/m/Y` dans le renderJson | `['label' => 'Date', 'orderBy' => 'createdAt', 'style' => 'width:150px;']` |
| `boolean` | Icône ✓/✗ ou label Oui/Non | `['label' => 'Actif', 'orderBy' => 'active', 'style' => 'width:80px;']` |
| `integer`, `float` | Label simple | `['label' => 'Prix', 'orderBy' => 'price']` |
| Relation ManyToOne | `join()` + alias dans `orderBy` | Voir section Jointures ci-dessous |
| Relation ManyToMany / OneToMany | Ne pas mettre en colonne, ignorer | — |
| `image` (champ fichier) | Macro `media.thumbnail_with_status` | Voir section Image ci-dessous |

#### Colonnes à toujours exclure
- **L'ID** — ne jamais mettre l'ID en colonne du datatable
- Mots de passe, tokens, champs techniques internes
- Collections (ManyToMany, OneToMany)
- Champs `updatedAt` (sauf besoin explicite)

#### Colonne nom/label/titre : toujours cliquable via `_link.html.twig`

Le champ principal de l'entité (nom, titre, label, etc.) doit être rendu via un partial `_link.html.twig` qui le rend cliquable vers la page d'édition. Dans `renderJson` :

```php
$this->renderView('admin/article/_link.html.twig', ['item' => $item]),
```

Créer le fichier `templates/admin/article/_link.html.twig` :

```twig
<a href="{{ path('admin_article_edit', {id: item.id}) }}">{{ item.title }}</a>
```

> Adapter `item.title` au getter du champ principal de l'entité (`item.name`, `item.label`, etc.) et `admin_article_edit` au nom de route de l'entité.

#### Dernière colonne : toujours les actions
```php
['label' => '', 'orderBy' => '', 'class' => 'no-sort']
```

#### Pattern Controller complet

```php
#[Route("/", name: "index", methods: ["GET"])]
public function index(DataTableFactory $dataTableFactory): Response
{
    return $dataTableFactory
        ->create(Article::class)
        ->setColumns([
            ['label' => 'Titre',      'orderBy' => 'title'],           // rendu via _link.html.twig
            ['label' => 'Catégorie',  'orderBy' => 'c.name'],          // jointure
            ['label' => 'Date',       'orderBy' => 'publishedAt', 'style' => 'width:150px;'],
            ['label' => 'Actif',      'orderBy' => 'active',      'style' => 'width:80px;'],
            ['label' => '',           'orderBy' => '',            'class' => 'no-sort'],
        ])
        ->join('category', 'c')          // si relation ManyToOne sur category
        ->searchIn(['title', 'c.name'])
        ->setOrderColumn(0)              // tri par défaut sur le titre
        ->setOrderDirection('asc')
        ->renderJson(fn(Article $item) => [
            $this->renderView('admin/article/_link.html.twig', ['item' => $item]),  // champ principal cliquable
            $item->getCategory()?->getName(),
            $item->getPublishedAt()?->format('d/m/Y') ?? '—',
            $item->isActive() ? 'Oui' : 'Non',
            $this->renderView('admin/article/_actions.html.twig', ['item' => $item]),
        ])
        ->render('admin/article/index.html.twig');
}
```

Fichier `templates/admin/article/_link.html.twig` à créer :

```twig
<a href="{{ path('admin_article_edit', {id: item.id}) }}">{{ item.title }}</a>
```

#### Jointures (relation ManyToOne)
```php
->join('category', 'c')           // propriété sur l'entité, alias
->setColumns([
    ['label' => 'Catégorie', 'orderBy' => 'c.name'],
])
->searchIn(['title', 'c.name'])
```

#### Colonne image
Dans `renderJson`, utiliser le renderView avec la macro image :
```php
$this->renderView('admin/article/_thumbnail.html.twig', ['item' => $item]),
```
```twig
{# _thumbnail.html.twig #}
{% import '@AropixelAdmin/Macro/image.html.twig' as media %}
{{ media.thumbnail_with_status(item, 'image', 'status', path('admin_article_edit', {id: item.id})) }}
```

---

### Étape 3 — Ajouter le lien dans le menu admin

Après avoir créé le CRUD, **toujours ajouter un lien vers la liste** dans `CustomAdminMenuBuilder` :

Fichier : `application/src/Component/AdminMenu/CustomAdminMenuBuilder.php`

Ajouter dans la méthode de section appropriée :

```php
$menu->addItem(new Link('Mon entité', 'admin_monentite_index', [], ['icon' => 'fas fa-list-ul']));
```

Choisir la section la plus pertinente (`buildContentMenu`, `buildMerchMenu`, `buildShopMenu`) ou créer une nouvelle section si l'entité appartient à un domaine distinct.

> Voir la skill `aropixel-admin-menu` pour le détail complet.

---

### Étape 4 — Compléter le template de formulaire `form.html.twig`

Pattern minimal :

```twig
{% extends '@AropixelAdmin/Form/base.html.twig' %}
{% import '@AropixelAdmin/Macro/breadcrumb.html.twig' as nav %}

{% block meta_title %}{% if article.id %}Modifier{% else %}Ajouter{% endif %} un article{% endblock %}
{% block header_title %}{% if article.id %}{{ article.title }}{% else %}Ajouter un article{% endif %}{% endblock %}

{% block header_breadcrumb %}
    {{ nav.breadcrumbs([
        { label: 'text.home', url: url('_admin') },
        { label: 'Articles', url: url('admin_article_index') },
        { label: (article.id ? 'Modifier' : 'Ajouter') ~ ' un article' }
    ]) }}
{% endblock %}

{% block mainPanel %}
    <div class="card card-centered card-centered-large">
        <div class="card-body">
            {{ form_rest(form) }}
        </div>
    </div>
{% endblock %}
```

Pour un formulaire avec **onglets** ou des **collections**, voir la section Form Templates ci-dessous.

---

## Référence : DataTable Component

The `DataTable` component of the AdminBundle simplifies the creation of JSON responses compatible with the [DataTables](https://datatables.net/) jQuery plugin. It provides a Fluent Interface to configure columns, filters, and rendering.

### Basic Usage (Single Action)

The recommended way to use the component is to handle both the HTML page and the JSON data in a single controller action. This avoids duplicating column definitions.

```php
use App\Entity\Event;
use Aropixel\AdminBundle\Component\DataTable\DataTableFactory;

#[Route("/", name: "index", methods: ["GET"])]
public function index(DataTableFactory $dataTableFactory): Response
{
    return $dataTableFactory
        ->create(Event::class)
        ->setColumns([
            ['label' => 'Title', 'orderBy' => 'title'],   // rendu via _link.html.twig
            ['label' => 'Date', 'orderBy' => 'startDate', 'style' => 'width:200px;'],
            ['label' => '', 'orderBy' => '', 'class' => 'no-sort'],
        ])
        ->searchIn(['title'])
        ->renderJson(fn(Event $event) => [
            $this->renderView('admin/event/_link.html.twig', ['item' => $event]),  // champ principal cliquable
            $event->getStartDate()->format('d/m/Y'),
            $this->renderView('admin/event/_actions.html.twig', ['item' => $event]),
        ])
        ->render('admin/event/index.html.twig');
}
```

Fichier `templates/admin/event/_link.html.twig` à créer :

```twig
<a href="{{ path('admin_event_edit', {id: item.id}) }}">{{ item.title }}</a>
```

### Fluent Interface Methods

- `setColumns(array $columns)` — définit toutes les colonnes
- `addColumn(array|DataTableColumn $column)` — ajoute une colonne
- `addColumnsIf(bool $condition, array $columns)` — ajoute des colonnes sous condition
- `join(string $property, string $alias)` — LEFT JOIN automatique
- `searchIn(array $fields)` — active la recherche LIKE
- `setOrderColumn(?int $index)` — colonne de tri par défaut (index 0)
- `setOrderDirection(?string $direction)` — `'asc'` ou `'desc'`
- `filter(callable $filter)` — filtre contextuel via QueryBuilder
- `useRepositoryMethod(string $methodName)` — méthode repo personnalisée (doit retourner un `QueryBuilder`)
- `renderJson(callable $transformer)` — transformateur de données
- `render(string $template, array $parameters = [])` — template HTML

### Mode Classic (données pré-chargées)

```php
return $dataTableFactory
    ->create(Event::class, mode: DataTableInterface::MODE_CLASSIC)
    ->setItems($events)
    ->setColumns([...])
    ->render('admin/event/list.html.twig');
```

Template en mode classic :

```twig
{% extends '@AropixelAdmin/List/datatable.html.twig' %}
{% import '@AropixelAdmin/Macro/actions.html.twig' as list %}

{% block datatable_row %}
    <tr>
        <td>{{ item.title }}</td>
        <td class="text-right">
            {{ list.actions(item, path('admin_event_edit', {id: item.id}), path('admin_event_delete', {id: item.id})) }}
        </td>
    </tr>
{% endblock %}
```

---

## Référence : Form Templates

### Structure complète avec onglets

```twig
{% extends '@AropixelAdmin/Form/base.html.twig' %}
{% import '@AropixelAdmin/Macro/breadcrumb.html.twig' as nav %}
{% import '@AropixelAdmin/Macro/forms.html.twig' as forms %}

{% block meta_title %}{% if artist.id %}Modifier{% else %}Ajouter{% endif %} un artiste{% endblock %}
{% block header_title %}{% if artist.id %}{{ artist.name }}{% else %}Ajouter un artiste{% endif %}{% endblock %}

{% block header_breadcrumb %}
    {{ nav.breadcrumbs([
        { label: 'text.home', url: url('_admin') },
        { label: 'Artistes', url: url('admin_artist_index') },
        { label: (artist.id ? 'Modifier' : 'Ajouter') ~ ' un artiste' }
    ]) }}
{% endblock %}

{% block tabbable %}
    {{ forms.tabs([
        { id: 'panel-tab-general', label: 'Général' },
        { id: 'panel-tab-extra', label: 'Détails' },
    ]) }}
{% endblock %}

{% block mainPanel %}
    <div class="tab-pane active" id="panel-tab-general">
        <div class="card card-centered card-centered-large">
            <div class="card-body">
                {{ form_row(form.name) }}
                {{ form_row(form.description) }}
            </div>
        </div>
    </div>
    <div class="tab-pane" id="panel-tab-extra">
        <div class="card card-centered card-centered-large">
            <div class="card-body">
                {{ form_row(form.image) }}
            </div>
        </div>
    </div>
{% endblock %}
```

### Collections dans un formulaire

```twig
<div class="form-group mt-4">
    <div class="d-flex justify-content-between align-items-center mb-2 w-100">
        <label class="control-label">Items</label>
        <a class="btn btn-primary btn-xs" data-form-collection-add="{{ form.items.vars.id }}">
            <i class="fa fa-plus"></i> Ajouter
        </a>
    </div>
    {{ form_widget(form.items, {'attr': {'class': 'w-100'}}) }}
</div>
```

### Ratios horizontaux disponibles

- `.form-horizontal-20-80`
- `.form-horizontal-30-70`
- `.form-horizontal-33-66`
- `.form-horizontal-40-60`
- `.form-horizontal-50-50`

```php
// Dans le FormType
$builder->add('name', TextType::class, [
    'row_attr' => ['class' => 'form-horizontal-40-60'],
]);
```

---

## Référence : Macros Twig

### Actions (`@AropixelAdmin/Macro/actions.html.twig`)

```twig
{% import '@AropixelAdmin/Macro/actions.html.twig' as list %}
{{ list.actions(item, path('admin_entity_edit', {id: item.id}), path('admin_entity_delete', {id: item.id})) }}
```

| Paramètre | Description |
|---|---|
| `item` | L'entité (pour le token CSRF de suppression) |
| `edit_path` | URL de modification (optionnel) |
| `delete_path` | URL de suppression (optionnel) |
| `delete_confirm_msg` | Message de confirmation personnalisé (optionnel) |

### Breadcrumb (`@AropixelAdmin/Macro/breadcrumb.html.twig`)

```twig
{% import '@AropixelAdmin/Macro/breadcrumb.html.twig' as nav %}
{{ nav.breadcrumbs([
    { label: 'text.home', url: url('_admin') },
    { label: 'Entités', url: url('admin_entity_index') },
    { label: 'Modifier' }
]) }}
```

### Image (`@AropixelAdmin/Macro/image.html.twig`)

```twig
{% import '@AropixelAdmin/Macro/image.html.twig' as media %}
{{ media.thumbnail_with_status(item, 'image', 'status', path('admin_entity_edit', {id: item.id})) }}
```

| Paramètre | Défaut | Description |
|---|---|---|
| `item` | — | L'entité |
| `image_field` | `'image'` | Nom de la propriété image |
| `status_field` | `'status'` | Nom de la propriété statut |
| `edit_path` | — | URL du lien autour de la miniature |
| `filter` | `'admin_thumbnail'` | Filtre LiipImagine |
| `height` | `60` | Hauteur en pixels |

### Tabs (`@AropixelAdmin/Macro/forms.html.twig`)

```twig
{% import '@AropixelAdmin/Macro/forms.html.twig' as forms %}
{% block tabbable %}
    {{ forms.tabs([
        { id: 'panel-tab-general', label: 'Général' },
        { id: 'panel-tab-extra', label: 'Extra' },
    ]) }}
{% endblock %}
```

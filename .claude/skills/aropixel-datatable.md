---
name: aropixel-datatable
description: >
  Modifier, configurer ou déboguer un DataTable dans un projet Symfony utilisant
  AropixelAdminBundle. Utilise ce skill dès que l'utilisateur demande à ajouter ou
  supprimer une colonne, changer le tri par défaut, ajouter un filtre, une jointure,
  une recherche, ou à adapter le comportement d'une liste admin existante.
  À utiliser aussi pour les cas avancés : colonne conditionnelle, méthode repository
  personnalisée, mode classic (données pré-chargées), ou tout problème lié au composant
  DataTableFactory. Couvre le controller PHP et le template Twig associé.
---

# Skill : Configurer le DataTable avec AropixelAdminBundle

## Anatomie d'un DataTable

```php
return $dataTableFactory
    ->create(MyEntity::class)       // (1) Entité cible
    ->join('relation', 'alias')     // (2) Jointures (optionnel)
    ->setColumns([...])             // (3) Colonnes
    ->searchIn(['field', 'alias.field']) // (4) Recherche plein-texte
    ->setOrderColumn(0)             // (5) Tri par défaut : index de colonne
    ->setOrderDirection('desc')     //     direction : 'asc' | 'desc'
    ->filter(fn($qb) => ...)        // (6) Filtre contextuel (optionnel)
    ->renderJson(fn($item) => [...]) // (7) Données JSON par ligne
    ->render('admin/.../index.html.twig'); // (8) Template HTML
```

---

## Colonnes — `setColumns()` / `addColumn()` / `addColumnsIf()`

### Format d'une colonne

```php
[
    'label'   => 'Mon libellé',       // texte de l'en-tête
    'orderBy' => 'myField',           // champ Doctrine pour le tri ('' = pas de tri)
    'class'   => 'no-sort',           // classes CSS sur le <th> (ex: désactiver tri)
    'style'   => 'width:120px;',      // style CSS inline sur le <th>
    'data'    => ['type' => 'date-euro'], // attributs data-* sur le <th>
]
```

### Ajouter / remplacer des colonnes

```php
// Remplace toutes les colonnes d'un coup
->setColumns([
    ['label' => 'Titre',   'orderBy' => 'title'],
    ['label' => 'Date',    'orderBy' => 'createdAt', 'style' => 'width:150px;'],
    ['label' => '',        'orderBy' => '',           'class' => 'no-sort'],
])

// Ajoute une colonne à la liste existante
->addColumn(['label' => 'Statut', 'orderBy' => 'status'])

// Ajoute plusieurs colonnes sous condition
->addColumnsIf($showCategory, [
    ['label' => 'Catégorie', 'orderBy' => 'c.name'],
])
```

### Colonne actions — toujours en dernier
```php
['label' => '', 'orderBy' => '', 'class' => 'no-sort']
```
Et dans `renderJson`, le dernier élément du tableau :
```php
$this->renderView('admin/entity/_actions.html.twig', ['item' => $item])
```

---

## Jointures — `join()`

Pour afficher ou trier sur une propriété d'une entité liée (ManyToOne) :

```php
->join('category', 'c')         // relation 'category' sur l'entité, alias 'c'
->join('author', 'a')           // on peut chaîner plusieurs join()
->setColumns([
    ['label' => 'Catégorie', 'orderBy' => 'c.name'],
    ['label' => 'Auteur',    'orderBy' => 'a.lastName'],
])
->searchIn(['title', 'c.name', 'a.lastName'])
```

> La jointure est un `LEFT JOIN` automatique. L'alias s'utilise partout : `orderBy`, `searchIn`, et dans le `filter()`.

---

## Recherche plein-texte — `searchIn()`

Active les clauses `LIKE` automatiques sur les champs listés :

```php
->searchIn(['title', 'description', 'c.name'])
```

- Fonctionne avec les champs de l'entité et les alias de jointure
- Ne pas inclure les champs `boolean`, `date`, ou les relations complexes

---

## Tri par défaut — `setOrderColumn()` / `setOrderDirection()`

```php
->setOrderColumn(1)          // index de la colonne dans setColumns() (commence à 0)
->setOrderDirection('desc')  // 'asc' ou 'desc'
```

---

## Filtre contextuel — `filter()`

Pour restreindre les données sans changer la requête de base :

```php
->filter(function(QueryBuilder $qb) {
    $qb->andWhere('e.active = :active')
       ->setParameter('active', true);
})

// Avec une variable du controller :
->filter(function(QueryBuilder $qb) use ($currentUser) {
    $qb->andWhere('e.owner = :owner')
       ->setParameter('owner', $currentUser);
})
```

---

## Méthode repository personnalisée — `useRepositoryMethod()`

Quand la logique de requête est trop complexe pour `filter()` :

```php
->useRepositoryMethod('findArchivedWithStats')
```

La méthode dans le repository doit retourner un `QueryBuilder` :

```php
// src/Repository/ArticleRepository.php
public function findArchivedWithStats(): QueryBuilder
{
    return $this->createQueryBuilder('e')
        ->leftJoin('e.stats', 's')
        ->andWhere('e.archived = true')
        ->addSelect('s');
}
```

---

## Mode Classic (données pré-chargées)

Pour les listes sans AJAX, avec des données déjà récupérées :

```php
use Aropixel\AdminBundle\Component\DataTable\DataTableInterface;

$items = $this->repository->findAll();

return $dataTableFactory
    ->create(MyEntity::class, mode: DataTableInterface::MODE_CLASSIC)
    ->setItems($items)
    ->setColumns([
        ['label' => 'Titre', 'orderBy' => 'title'],
        ['label' => '',      'orderBy' => '', 'class' => 'no-sort'],
    ])
    ->render('admin/entity/index.html.twig');
```

Template en mode classic — utiliser le bloc `datatable_row` :

```twig
{% extends '@AropixelAdmin/List/datatable.html.twig' %}
{% import '@AropixelAdmin/Macro/actions.html.twig' as list %}

{% block datatable_row %}
    <tr>
        <td>{{ item.title }}</td>
        <td class="text-right">
            {{ list.actions(item, path('admin_entity_edit', {id: item.id}), path('admin_entity_delete', {id: item.id})) }}
        </td>
    </tr>
{% endblock %}
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

---
name: aropixel-collection
description: >
  Create or configure a collection of forms using CollectionType when AropixelAdminBundle is used.
  This type displays items in a table and uses a Bootstrap offcanvas for editing.
  Focus on using the `columns` option with `field`, `display`, and `render` for customization.
---

# Skill: Configuring CollectionType with AropixelAdminBundle

When working with collections of forms in an AropixelAdminBundle project, you MUST use the bundle's custom `CollectionType` instead of the standard Symfony `CollectionType` or any other collection management, unless explicitly asked otherwise.

The `CollectionType` in AropixelAdminBundle is an enhanced version of the Symfony CollectionType. It displays items in a table and opens a Bootstrap offcanvas for editing each entry, keeping the main form clean.

The layout handles the entire rendering of the collection, including the "Add" button and the list table. There is no need to manually add an "Add" button or a custom template for the list in most cases.

## Basic Usage

```php
use Aropixel\AdminBundle\Form\Type\CollectionType;

$builder->add('variants', CollectionType::class, [
    'entry_type' => VariantType::class,
    'columns' => [
        'Name' => 'name',          // Displays the default form widget for 'name'
        'Price' => 'price',        // Displays the default form widget for 'price'
    ],
    'button_add_label' => 'Add variant',
    'form_title' => 'Edit variant',
]);
```

---

## Columns Configuration — `columns`

The `columns` option is an associative array where the key is the table header label and the value is the configuration.

### Simple field path
```php
'columns' => [
    'Title' => 'title',
    'Category' => 'category.name', // Dot notation for nested fields
]
```

### Advanced field configuration
Each column can be an array with the following keys:
- `field`: (string) The path to the field (e.g., `track.name`).
- `display`: (string) Set to `'label'` to display the field value as plain text (it updates live when edited in the offcanvas).
- `render`: (closure) A function for custom HTML rendering.

```php
'columns' => [
    'Product' => [
        'field' => 'name',
        'display' => 'label', // Live-updating text instead of a widget
    ],
    'Stock' => [
        'field' => 'quantity',
        'render' => function($field, $item) {
            $value = $field->vars['value'];
            $class = $value < 5 ? 'text-danger' : 'text-success';
            return sprintf('<span class="%s">%d units</span>', $class, $value);
        },
    ],
]
```

---

## Custom Rendering — `render`

The `render` closure is the preferred way to customize the table display without creating custom templates.

It receives two parameters (both `Symfony\Component\Form\FormView`):
1. `$field`: The specific field object (matching the `field` option).
2. `$item`: The whole row form object (the collection entry).

### Accessing data
Inside the closure, you can access:
- `$field->vars['value']`: The value of the specific field.
- `$item->vars['data']`: The underlying entity or array for the entire row.

### Example: Displaying a thumbnail
```php
'Image' => [
    'render' => function($field, $item) {
        $entity = $item->vars['data'];
        if ($entity && $entity->getImage()) {
            return sprintf('<img src="/uploads/%s" height="50">', $entity->getImage());
        }
        return '<em>No image</em>';
    },
],
```

---

## Options Reference

| Option | Type | Default | Description |
|---|---|---|---|
| `columns` | `array` | `[]` | Table columns definition (Label => Config). |
| `button_add_label`| `string`| `"Ajouter un élément"` | Label of the "Add" button. |
| `form_title` | `string` | `"Détails de l'élément"` | Title of the edit offcanvas. |
| `sortable` | `bool` | `true` | Enables drag-and-drop sorting. |
| `entry_type` | `string` | — | The underlying FormType for each item (Required). |

> **Note**: The bundle's layout automatically handles the rendering of the collection and the addition of new items. Avoid using `list_template` and `entry_row_template` unless the standard table layout is absolutely impossible to use. Prefer customizing via the `columns` and `render` options.

---

## Doctrine Entity — Required OneToMany Setup

For a collection to persist correctly, the parent entity must declare a `OneToMany` relation with `cascade: ['persist', 'remove']` and `orphanRemoval: true`, and expose `addXxx`/`removeXxx` methods that sync the owning side.

```php
// Parent entity (e.g. Band)
#[ORM\OneToMany(targetEntity: Concert::class, mappedBy: 'band', cascade: ['persist', 'remove'], orphanRemoval: true)]
private Collection $concerts;

public function __construct()
{
    $this->concerts = new ArrayCollection();
}

public function addConcert(Concert $concert): static
{
    if (!$this->concerts->contains($concert)) {
        $this->concerts->add($concert);
        $concert->setBand($this); // sync owning side
    }
    return $this;
}

public function removeConcert(Concert $concert): static
{
    if ($this->concerts->removeElement($concert)) {
        if ($concert->getBand() === $this) {
            $concert->setBand(null);
        }
    }
    return $this;
}
```

```php
// Child entity (e.g. Concert) — owns the FK
#[ORM\ManyToOne(targetEntity: Band::class, inversedBy: 'concerts')]
#[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
private ?Band $band = null;
```

Always add **`'by_reference' => false`** to the `CollectionType` options so Symfony calls `addXxx`/`removeXxx` instead of mutating the collection directly — Doctrine needs these calls to detect changes on the owning side.

---

## Twig — Rendering in a Tabbed Form

The collection renders itself entirely via `form_widget`. In a tabbable admin form (using `forms.tabs`), add a new tab entry and a corresponding `tab-pane`:

```twig
{# In {% block tabbable %} #}
{{ forms.tabs([
    {id: 'panel-tab-general', label: 'Général'},
    {id: 'panel-tab-concerts', label: 'Concerts'},
]) }}

{# In {% block mainPanel %} #}
<div class="tab-pane" id="panel-tab-concerts">
    <div class="card card-centered card-centered-large">
        <div class="card-body">
            {{ form_widget(form.concerts) }}
        </div>
    </div>
</div>
```

Use `form_widget` (not `form_row`) for collections — the bundle renders the full table and "Add" button automatically.

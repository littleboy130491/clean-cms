# Project Documentation

This document provides an overview of key development aspects of this project, including model and migration generation from YAML schemas, Tailwind CSS compilation, and the structure of Filament resources.

## 1. Generating Models from YAML

Eloquent models can be generated automatically from a YAML schema file using the `make:model:from-yaml` Artisan command.

The command reads the model definitions from `schemas/models.yaml` by default, but a different file can be specified. It creates or updates model files in the `app/Models` directory.

**Command Signature:**

```bash
php artisan make:model:from-yaml {yaml_file?} {--model=} {--force}
```

-   `yaml_file`: Optional. The path to the YAML definition file (defaults to `schemas/models.yaml`).
-   `--model`: Optional. Specify a single model name from the YAML file to generate.
-   `--force`: Optional. Overwrite existing model files without prompting.

**Example Usage:**

Generate all models from the default schema file:

```bash
php artisan make:model:from-yaml
```

Generate only the `Post` model:

```bash
php artisan make:model:from-yaml --model=Post
```

Generate all models and overwrite existing files:

```bash
php artisan make:model:from-yaml --force
```

The `CreateModelCommand.php` file (`app/Console/Commands/CreateModelCommand.php`) contains the logic for parsing the YAML and generating the model files, including fields, casts, translatable attributes, relationships, and constants for enums.

## 2. Generating Migrations from YAML

Database migrations can also be generated from the same YAML schema file using the `make:migration:from-yaml` Artisan command.

This command reads the model and relationship definitions from `schemas/models.yaml` (by default) and generates migration files in the `database/migrations` directory. It handles both main model tables and pivot tables for `belongsToMany` relationships.

**Command Signature:**

```bash
php artisan make:migration:from-yaml {yaml_file?} {--model=}
```

-   `yaml_file`: Optional. The path to the YAML definition file (defaults to `schemas/models.yaml`).
-   `--model`: Optional. Specify a single model name from the YAML file to generate the migration for its main table and any associated pivot tables.

**Example Usage:**

Generate migrations for all models and pivot tables from the default schema:

```bash
php artisan make:migration:from-yaml
```

Generate migration only for the `Category` model's main table and its pivot tables:

```bash
php artisan make:migration:from-yaml --model=Category
```

The `CreateMigrationCommand.php` file (`app/Console/Commands/CreateMigrationCommand.php`) contains the logic for parsing the YAML, determining the necessary tables and columns (including foreign keys and constraints), and using Laravel's `MigrationCreator` to generate the migration files.

## 3. Modifying the `schemas/models.yaml` File

The `schemas/models.yaml` file defines the structure of your Eloquent models and their corresponding database tables. You can modify this file to add, remove, or change models, fields, relationships, and traits.

The basic structure is a top-level `models` key, under which each key represents a model name (e.g., `Post`, `Page`, `Category`, `Tag`). Each model definition can have the following keys:

-   `fields`: Defines the columns for the model's main database table. Each field has a name (the key) and properties like `type`, `nullable`, `unique`, `default`, `index`, `unsigned`, `comment`, and `translatable`. For `enum` types, an `enum` array is required.
-   `relationships`: Defines the Eloquent relationships for the model. Each relationship has a name (the method name on the model) and properties like `type` (e.g., `belongsTo`, `hasMany`, `belongsToMany`), `model` (the related model name), and optional keys like `foreign_key`, `related_key`, `onDelete`, `onUpdate`.
-   `traits`: Lists the fully qualified class names of traits to be used by the model (e.g., `Spatie\Translatable\HasTranslations`, `Illuminate\Database\Eloquent\SoftDeletes`).

**Example Snippet from `schemas/models.yaml`:**

```yaml
models:
    Post:
        fields:
            title:
                type: json
                nullable: false
                translatable: true
            slug:
                type: json
                nullable: false
                unique: false
                translatable: true
            # ... other fields
        relationships:
            author:
                type: belongsTo
                model: User
            categories:
                type: belongsToMany
                model: Category
            # ... other relationships
        traits:
            - Spatie\Translatable\HasTranslations
            - Illuminate\Database\Eloquent\SoftDeletes
    # ... other models
```

After modifying the `schemas/models.yaml` file, you should run the `make:model:from-yaml` and `make:migration:from-yaml` commands to regenerate your models and migrations based on the updated schema. Remember to run `php artisan migrate` after generating new migration files.

## 4. Syncing Curator Media

The `SyncCuratorMedia` Artisan command (`app/Console/Commands/SyncCuratorMedia.php`) is used to synchronize files on a specified filesystem disk with the `media` database table.

It provides the following functionality:

-   **Import any new files:** Scans the specified directory for files that do not have a corresponding record in the `media` table and creates new records for them.
-   **Update metadata:** When the `--update` option is passed, it updates metadata (width, height, size, type, ext, exif) for existing records in the `media` table whose files exist on disk.
-   **Prune database rows:** When the `--prune` option is passed, it deletes records from the `media` table whose corresponding files are missing on disk.

**Command Signature:**

```bash
php artisan media:sync {--disk=public} {--dir=media} {--update} {--prune}
```

-   `--disk`: Optional. The filesystem disk to scan (defaults to `public`).
-   `--dir`: Optional. The directory within that disk to scan (defaults to `media`).
-   `--update`: Optional. Update metadata for existing records.
-   `--prune`: Optional. Prune database rows whose files no longer exist.

**How to use**

Import new + update metadata + prune missing

```bash
php artisan media:sync --update --prune
```

Just import new + update metadata

```bash
php artisan media:sync --update
```

Just import new + prune missing

```bash
php artisan media:sync --prune
```

Default (only imports new files)

```bash
php artisan media:sync
```

## 5. Generating Sitemap

The `GenerateSitemap` Artisan command (`app/Console/Commands/GenerateSitemap.php`) is used to generate the sitemap.xml file for the website. It iterates through configured content models, retrieves published records, and adds their localized URLs to the sitemap.

The generated sitemap file is saved to `public/sitemap.xml`.

**Command Signature:**

```bash
php artisan sitemap:generate
```

## 6. Building Tailwind CSS

The project uses Tailwind CSS for styling the Filament admin panel. To compile the CSS after making changes to the Tailwind configuration or source CSS files, use the following command:

```bash
npx tailwindcss@3 --input ./resources/css/filament/admin/theme.css --output ./public/css/filament/admin/theme.css --config ./resources/css/filament/admin/tailwind.config.js --minify
```

This command reads the input CSS file (`./resources/css/filament/admin/theme.css`), processes it using the specified Tailwind configuration (`./resources/css/filament/admin/tailwind.config.js`), and outputs the minified result to `./public/css/filament/admin/theme.css`.

You can add this command to your `package.json` scripts for easier execution (e.g., `npm run build-tailwind`).

## 6. Filament Resource Structure

The Filament admin panel resources in this project follow a hierarchical structure based on base classes to promote code reusability and consistency.

### BaseResource (`app/Filament/Abstracts/BaseResource.php`)

This is the foundational class for most Filament resources in the project. It provides common configurations and methods for forms and tables, including:

-   Handling translatable fields (`title`, `slug`, `content`, `excerpt`, `custom_fields`) using `SolutionForest\FilamentTranslateField\Forms\Component\Translate`.
-   Defining standard form fields like `featured_image` (using `Awcodes\Curator\Components\Forms\CuratorPicker`), `author_id`, `status`, `template`, `featured`, `published_at`, and `menu_order`.
-   Defining standard table columns like `title`, `slug`, `featured`, `status`, `author.name`, `created_at`, `updated_at`, `deleted_at`, and `menu_order`.
-   Including common table filters (`TrashedFilter`).
-   Providing standard table actions (`EditAction`, `DeleteAction`, `ForceDeleteAction`, `RestoreAction`) and bulk actions (`DeleteBulkAction`, `ForceDeleteBulkAction`, `RestoreBulkAction`, and a custom `edit` bulk action).
-   Implementing reordering based on `menu_order`.
-   Handling soft deletes in the Eloquent query.

New resources typically extend this class and override specific methods (like `formContentFields`, `formRelationshipsFields`, `tableColumns`, etc.) to define resource-specific fields, columns, and relationships while inheriting the common functionality.

### BaseContentResource (`app/Filament/Abstracts/BaseContentResource.php`)

This abstract class extends `BaseResource` and is specifically designed for content-based resources (like `Post` and `Page`). It provides default implementations for content-related form fields:

-   `content` (using `RichEditor`)
-   `excerpt` (using `Textarea`)
-   `custom_fields` (using `KeyValue`)

Resources extending `BaseContentResource` inherit these fields and can add their own specific fields and relationships.

### BaseTaxonomyResource (`app/Filament/Abstracts/BaseTaxonomyResource.php`)

This abstract class also extends `BaseResource` but is tailored for taxonomy resources (like `Category` and `Tag`). It overrides several methods from `BaseResource` to remove fields and columns that are not typically relevant to taxonomies:

-   It provides a default `content` field (using `RichEditor`).
-   It explicitly returns empty arrays for `formAuthorRelationshipField`, `formStatusField`, `formFeaturedField`, `formPublishedDateField`, `tableFeaturedColumn`, `tableStatusColumn`, `tableAuthorColumn`, `tablePublishedAtColumn`, and `tableBulkEditAction`.

Resources extending `BaseTaxonomyResource` inherit the base functionality but exclude the content/status/author/featured fields and columns, providing a cleaner base for taxonomy management.

### BaseEditResource (`app/Filament/Abstracts/BaseEditResource.php`)

This abstract class extends Filament's `EditRecord` page class and provides a base for the edit pages of resources. It defines common header actions for edit pages:

-   A save action (`getSaveFormAction`).
-   Delete action (`Actions\DeleteAction`).
-   Restore action (`Actions\RestoreAction`).

Resource edit pages (e.g., `EditPost`, `EditCategory`) extend this class to inherit these standard actions.

By using these base classes, the project maintains a consistent structure across different Filament resources, reduces code duplication, and simplifies the creation of new resources. Developers creating new resources should extend the most appropriate base class (`BaseContentResource` for content types, `BaseTaxonomyResource` for taxonomies, or `BaseResource` for other types) and override methods as needed to define the unique aspects of the resource.

### BaseCreateResource (`app/Filament/Abstracts/BaseCreateResource.php`)

This abstract class extends Filament's `CreateRecord` page class and provides a base for the create pages of resources. It defines common header actions for create pages:

-   A create action (`getCreateFormAction`).

Resource create pages (e.g., `CreatePost`, `CreateCategory`) extend this class to inherit this standard action.

## 7. Template Hierarchy

This project implements a WordPress-like template hierarchy system for Laravel 12, providing a flexible and powerful way to customize the appearance of different content types and pages.

### Overview

The template hierarchy system follows the WordPress pattern of "most specific to most general" template selection. When a page is requested, the system looks for the most specific template first, then falls back to more general templates if the specific one doesn't exist.

All templates are stored in the `resources/views/templates` directory.

### Template Hierarchies

#### Home Page Hierarchy:

1. `templates/singles/home.blade.php`
2. `templates/singles/front-page.blade.php`
3. `templates/home.blade.php`
4. `templates/front-page.blade.php`
5. `templates/singles/default.blade.php`
6. `templates/default.blade.php`

#### Static Page Hierarchy:

1. Custom template specified in content model (`template` field)
2. `templates/singles/{slug}.blade.php` (using default language slug)
3. `templates/singles/page.blade.php`
4. `templates/page.blade.php`
5. `templates/singles/default.blade.php`
6. `templates/default.blade.php`

#### Single Content Hierarchy (Posts, Custom Post Types):

1. Custom template specified in content model (`template` field)
2. `templates/singles/{post_type}-{slug}.blade.php` (using default language slug)
3. `templates/singles/{post_type}.blade.php`
4. `templates/{post_type}.blade.php`
5. `templates/singles/default.blade.php`
6. `templates/default.blade.php`

#### Taxonomy Archive Hierarchy:

1. Custom template specified in content model (`template` field)
2. Check config `cms.content_models` archive_view
3. `templates/archives/{taxonomy}-{slug}.blade.php`
4. `templates/archives/{taxonomy}.blade.php`
5. `templates/{taxonomy}-{slug}.blade.php`
6. `templates/{taxonomy}.blade.php`
7. `templates/archives/archive.blade.php`
8. `templates/archive.blade.php`

### Creating Custom Templates

To create a custom template for a specific content item:

1. Create a new Blade file in the `resources/views/templates` directory following the naming conventions above.
2. Use the appropriate template hierarchy based on the content type.
3. For content-specific templates (like a specific page or post), use the slug in the filename (e.g., `page-about.blade.php`).
4. For content type templates (like all posts or all pages), use the content type in the filename (e.g., `single-post.blade.php`).

You can also specify a custom template directly in the content model by setting the `template` field to the name of the template (without the `.blade.php` extension and without the `templates/` prefix).

### Implementation Details

The template hierarchy system is implemented in the `ContentController` class with a set of template resolver methods:

-   `resolveHomeTemplate()` - For the home page
-   `resolvePageTemplate()` - For static pages
-   `resolveSingleTemplate()` - For single content items (posts, custom post types)
-   `resolveArchiveTemplate()` - For custom post type archives
-   `resolveTaxonomyTemplate()` - For taxonomy archives

These methods use two helper functions:

-   `getContentCustomTemplates()` - Extracts custom template information from content models
-   `findFirstExistingTemplate()` - Checks for template existence and returns the first one found

The system automatically selects the most appropriate template based on the request and content type, providing a flexible way to customize the appearance of different parts of your site.

### Example Usage

To create a custom template for a specific page with the slug "about":

```php
// Create a file at resources/views/templates/page-about.blade.php
<x-layouts.app :title="$content->title ?? 'Default Page'" :body-classes="$bodyClasses">
    <x-partials.header />
    <main>
        <article class="page about-page">
            <header>
                <h1>{{ $content->title ?? 'About Us' }}</h1>
            </header>
            <div class="page-content">
                {!! $content->content ?? 'About page content goes here.' !!}
            </div>

            <!-- Custom sections specific to the About page -->
            <section class="team-section">
                <h2>Our Team</h2>
                <!-- Team content -->
            </section>
        </article>
    </main>
    <x-partials.footer />
</x-layouts.app>
```

This template will be automatically used for the page with the slug "about", while other pages will use more general templates in the hierarchy.

## 8. Dynamic Component Loading

The project includes a `ComponentLoader` Blade component (`app/View/Components/ComponentLoader.php`) that allows for dynamic loading and rendering of components based on data stored in the database.

This component fetches component data from the `components` table using a provided name and passes this data to a corresponding Blade view for rendering.

**Usage:**

To use the `ComponentLoader`, simply include it in your Blade file with the `name` attribute set to the slug of the component you want to load:

```blade
<x-component-loader name="your-component-slug" />
```

**Implementation:**

1.  **Create the Blade View:** Create a new Blade file for your dynamic component in the `resources/views/components/dynamic` directory. This Blade file will receive the component data fetched by the `ComponentLoader`.
2.  **Manage Data in CMS:** The data for the dynamic components is managed through the `ComponentResource` in the CMS. You can create and edit component entries there. The `name` field in the CMS entry **must** match the name used in the `x-component-loader` tag and the Blade file name. The data provided in the CMS entry will be passed to your dynamic Blade views.
3.  **Display Data in the Blade View:** In the Blade view, you can access the data using `$componentData->blocks`. See [`app/Models/Component.php`](app/Models/Component.php) for the `getBlocksAttribute` method implementation.
4.  **Example (using 'slider'):**
    *   In your Blade file where the component will be rendered, use: `<x-component-loader name="slider" />`
    *   Create the component Blade view file: `resources/views/components/dynamic/slider.blade.php`
    ```php
    {{-- resources/views/components/dynamic/slider.blade.php:1 --}}
    @foreach ($componentData->blocks as $block)
        @if ($block['type'] === 'slider')
            <div class="slider-item">
                <h2>{{ $block['data']['heading'] }}</h2>
                <p>{{ $block['data']['description'] }}</p>
                <a class="btn">{{ $block['data']['call-to-action'] }}</a>
                <img src="{{ $block['data']['image_url'] }}" alt="">
            </div>
        @endif
    @endforeach
    ```
    *   In the CMS, create a Component entry with the `name` field set to `slider`.


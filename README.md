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

## 9. Using the Section Field in the Page Model

The `Page` model includes a `section` field, which is cast to an array and can store flexible content blocks. This allows for building dynamic page layouts where different sections of content can be defined and rendered.

The `Page` model also has a `blocks` accessor (`getBlocksAttribute()`) that processes the raw `section` data. This accessor is responsible for injecting additional data, such as `media_url` for image blocks, by looking up related media IDs.

**Structure of `$content->blocks`:**

The `blocks` attribute on a `Page` model (`$content->blocks` in a Blade view) is an array of content blocks. Each block typically has a `type` and `data` key. The `data` key contains the specific fields for that block type.

**Blade Example for Displaying Section Data:**

To display the content from the `section` field in a Blade template, you can iterate over the `$content->blocks` array. You can use conditional statements (e.g., `@if ($block['type'] === 'your_block_type')`) to render different layouts based on the `type` of each block.

Here's an example demonstrating how to display the provided `complete` block data:

```blade
{{-- resources/views/templates/default.blade.php (or any page template) --}}
@if ($content->blocks)
    @foreach ($content->blocks as $block)
        @if ($block['type'] === 'complete')
            <section class="complete-block">
                @if (isset($block['data']['heading']))
                    <h2>{{ $block['data']['heading'] }}</h2>
                @endif
                @if (isset($block['data']['group']))
                    <p>Group: {{ $block['data']['group'] }}</p>
                @endif
                @if (isset($block['data']['description']))
                    <div>{!! $block['data']['description'] !!}</div>
                @endif
                @if (isset($block['data']['media_url']))
                    <img src="{{ $block['data']['media_url'] }}" alt="{{ $block['data']['heading'] ?? 'Image' }}">
                @endif
                @if (isset($block['data']['cta-label']) && isset($block['data']['cta-url']))
                    <a href="{{ $block['data']['cta-url'] }}" class="btn">{{ $block['data']['cta-label'] }}</a>
                @endif
            </section>
        @endif
        {{-- Add more @elseif blocks for other section types as needed --}}
    @endforeach
@endif
```

This example checks for the existence of each data field before attempting to display it, ensuring robustness. The `description` field uses `{!! !!}` to render HTML content safely.


## 10. Advanced Debug Mode

This project includes an advanced debug mode that injects detailed HTML comments into the frontend output, providing comprehensive debugging information. This mode is designed to be active only in development environments, ensuring no impact on production performance or security.

### Information Provided:

When enabled, the debug mode injects comments containing:

*   **Request Details**: Request ID, Timestamp, and current application Environment.
*   **Route Information**: Details about the matched route, including its name, URI, HTTP methods, associated controller, and middleware.
*   **View Information**: The names of the Blade templates rendered and a dump of their associated variables. Sensitive data is redacted, and large data structures are summarized for readability.
*   **Database Queries**: A list of all executed database queries, their bindings, execution time, and connection (if enabled).
*   **Cache Information**: Details on cache hits and misses, including the cache key (if enabled).
*   **Component Information**: Data passed to dynamically loaded components.
*   **Performance Metrics**: Memory usage and total execution time for the request.

### Enabling Debug Mode:

To activate the advanced debug mode, follow these steps:

1.  **Update `.env` file**:
    Set the `CMS_DEBUG_MODE_ENABLED` environment variable to `true`.
    ```dotenv
    CMS_DEBUG_MODE_ENABLED=true
    ```
    Ensure your `APP_ENV` is set to `local` or `development` (or any environment specified in `config/cms.php` under `debug_mode.environments`).
    ```dotenv
    APP_ENV=local
    ```

2.  **Clear Configuration Cache (if necessary)**:
    If you've previously cached your configuration, run the following Artisan command to ensure the new settings are loaded:
    ```bash
    php artisan config:clear
    ```

### Configuration:

The debug mode's behavior can be configured in the `config/cms.php` file under the `debug_mode` array:

```php
// config/cms.php
'debug_mode' => [
    'enabled' => env('CMS_DEBUG_MODE_ENABLED', false), // Master switch for debug mode
    'environments' => ['local', 'development'], // Environments where debug mode is active
    'max_variable_depth' => 3, // Max depth for variable dumping to prevent excessive output
    'max_array_items' => 50, // Max number of items to display for dumped arrays
    'include_queries' => true, // Whether to include database query logs
    'include_cache_info' => true, // Whether to include cache hit/miss information
    'redacted_keys' => ['password', 'token', 'secret', 'key', 'api_key'], // Keys whose values will be redacted
],
```

You can modify these settings to control the verbosity and scope of the debug information.

### Usage:

Once enabled, simply view the source code of any HTML page in your browser (e.g., by right-clicking and selecting "View Page Source" or using your browser's developer tools) to see the injected HTML comments. These comments will provide a detailed breakdown of the request and rendering process.

## 11. Email Notification System for Form Submissions

This system provides automatic email notifications to admins for Laravel Livewire form submissions. It features professional formatting using Markdown mail templates, queue support for better performance, and reply-to functionality. Notification emails include submitter information, message details, technical information (IP, user agent, submission ID), and a direct link to the admin panel.

**Configuration:**
- Set `MAIL_ADMIN_EMAIL` in your `.env` file.
- Ensure proper mail configuration (e.g., `MAIL_MAILER`, `MAIL_FROM_ADDRESS`, `MAILGUN_DOMAIN`, `MAILGUN_SECRET`) in `.env`.

**Technical Implementation:**
- `app/Mail/FormSubmissionNotification.php`: Mailable class for email composition, implements `ShouldQueue`.
- `resources/views/emails/admin/form-submission.blade.php`: Markdown email template.
- `app/Livewire/SubmissionForm.php`: Sends email after successful submission.

**Usage:**
The system works automatically: user submits form -> data saved -> email queued -> admin receives notification. Admins can reply directly to the submitter or view full details in the admin panel.

**Testing:**
- Test email delivery using `php artisan tinker` (`Mail::raw(...)`).
- Test form submission by visiting `/preview/submission-form`, filling it out, and checking admin email.
- Ensure `php artisan queue:work` is running if using queues.

**Troubleshooting:**
- Check `.env` mail configuration and `MAIL_ADMIN_EMAIL`.
- Verify email template path and variable passing.
- Ensure `APP_URL` is set correctly for admin panel links.

**Security & Performance:**
- **Security:** Email validation, CAPTCHA protection, data sanitization, and background processing via queues.
- **Performance:** Queued processing prevents blocking form submission, lightweight templates, and immediate form response.

## 12. Livewire Page Likes Feature

This feature enables users to like/unlike posts with real-time updates and cookie-based tracking to prevent multiple likes from the same user. It is built with Laravel Livewire and uses Tailwind CSS for styling.

**Features:**
- Livewire-powered reactive components.
- Cookie-based tracking (`liked_content_{post_id}` cookie, 1-year expiry).
- Toggle functionality for liking/unliking.
- Real-time updates without page refresh.
- No custom CSS required (uses Tailwind classes).
- Built-in loading states and accessibility features.

**Implementation:**
- **Livewire Component:** `app/Livewire/LikeButton.php`:1 handles state, cookies, database updates, and real-time UI.
- **Model Trait:** `app/Traits/HasPageLikes.php`:1 provides methods (`incrementPageLikes`, `decrementPageLikes`, `setPageLikes`, `resetPageLikes`) and query scopes (`orderByPageLikes`, `mostLiked`, `withMinLikes`).
- **Database:** Requires a migration to initialize `page_likes` field (e.g., `php artisan migrate`).

**Usage:**
Embed the `<livewire:like-button>` component in Blade templates:
```blade
<livewire:like-button :content="$post" :lang="$lang" :content-type="$contentType" />
```
Supports `size` ('sm', 'md', 'lg'), `variant` ('default', 'minimal', 'outline'), and `showCount` properties.

**Extending to Other Models:**
Add the `HasPageLikes` trait to your model and use the component in templates.

**Performance & Security:**
- **Performance:** Leverages Livewire's optimized DOM updates, automatic CSRF, and built-in loading states. Each like/unlike is one database update.
- **Security:** Automatic CSRF protection, Laravel cookie encryption, and trait validation. Rate limiting can be added for abuse prevention.

**Troubleshooting:**
- Verify Livewire installation, trait usage, browser console for errors, and component registration.
- Check browser cookie settings, domain, and expiry for cookie issues.
- Ensure Tailwind CSS is configured correctly for styling issues.

## 13. Page Likes Feature (Non-Livewire)

This feature allows logged-out users to like posts using cookie-based tracking and AJAX for real-time updates.

**Features:**
- Cookie-based tracking (`liked_content_{post_id}` cookie, 1-year expiry).
- Toggle functionality for liking/unliking.
- Real-time updates via AJAX.
- Responsive design, accessibility, and visual feedback (heart animation).

**Implementation:**
- **Model Trait:** `HasPageViews` trait (now includes likes functionality) provides methods (`incrementPageLikes`, `decrementPageLikes`, `setPageLikes`, `resetPageLikes`) and query scopes (`orderByPageLikes`, `mostLiked`, `withMinLikes`).
- **Controller:** `ContentController`'s `toggleLike()` method handles validation, cookie management, database updates, and returns JSON responses.
- **Route:** `POST /{content_type_key}/{content_slug}/like` (`cms.content.like`).
- **Blade Component:** `<x-ui.like-button>` for interactive buttons.
- **Styling:** `resources/css/like-button.css`.
- **JavaScript:** Built-in JS handles clicks, loading states, UI updates, error handling, and CSRF tokens.
- **Database:** Requires a migration to initialize `page_likes` field (e.g., `php artisan migrate`).

**Security & Performance:**
- **Security:** CSRF token validation, Laravel cookie encryption. Rate limiting can be added to the route.
- **Performance:** Single database update per like/unlike, minimal cookie impact, lightweight JSON responses. Caching and queue-based processing are options for high-traffic sites.

**Troubleshooting:**
- Check CSRF token, JavaScript errors, route registration, and trait usage.
- Verify browser cookie settings, domain, and expiry.
- Ensure CSS is loaded and classes are applied correctly.

## 14. Page Views Tracking

This feature automatically counts and displays page views for posts, storing the count in the `custom_fields` JSON column (`page_views` key).

**Features:**
- Automatic view incrementation on post view.
- Stores view count in `custom_fields` JSON column.
- Reusable Blade component (`<x-ui.page-views>`) for display.
- Query scopes (`orderByPageViews`, `mostViewed`, `withMinViews`) for filtering and ordering.
- Extensible to other models using the `HasPageViews` trait.

**Implementation:**
- **Model Trait:** `HasPageViews` trait (e.g., in `Post` model) provides view functionality and a `page_views` accessor.
- **Controller:** `ContentController::singleContent()` automatically increments views for models using the `HasPageViews` trait.
- **Blade Component:** `<x-ui.page-views :count="$post->page_views" />` with format options ('long', 'short', 'number') and icon visibility.
- **Styling:** `resources/css/page-views.css`.
- **Database:** Requires a migration to initialize `page_views` field (e.g., `php artisan migrate`).

**Extending to Other Models:**
Add the `HasPageViews` trait to your model; the controller automatically detects and increments views.

**Performance:**
- Single database update per view increment.
- JSON column is indexed for performance.
- Caching and queue-based processing are options for high-traffic sites.

**Advanced Usage:**
- Customize view tracking logic (e.g., only for authenticated users, unique sessions).
- Integrate with analytics services.

**Troubleshooting:**
- Verify trait usage, `custom_fields` casting, and controller's `incrementPageViews()` call.
- Check component inclusion, CSS loading, and accessor functionality for display issues.
- Consider database indexes, caching, or queues for performance issues.

## 15. Google reCAPTCHA Setup Guide

This guide details setting up Google reCAPTCHA v2 ("I'm not a robot" Checkbox) for Laravel Livewire forms.

**Setup Steps:**
1.  **Create reCAPTCHA Site:** Go to [Google reCAPTCHA Admin Console](https://www.google.com/recaptcha/admin), create a new site (v2 "I'm not a robot" Checkbox), and add your domains (production and development like `localhost`, `127.0.0.1`).
2.  **Get Keys:** Obtain your Site Key (frontend) and Secret Key (backend).
3.  **Configure Laravel:** Add `NOCAPTCHA_SITEKEY` and `NOCAPTCHA_SECRET` to your `.env` file. Test keys are available for development but **must not** be used in production.
4.  **Domain Configuration:** Ensure all relevant domains are registered in the reCAPTCHA admin console.
5.  **Verify Setup:** Clear application cache (`php artisan config:clear`, `php artisan cache:clear`), then test your form to ensure the widget loads, challenges can be completed, and form submission works.

**Troubleshooting:**
- **CAPTCHA Not Loading:** Check browser console, site key, and domain registration.
- **Validation Fails:** Verify secret key, domain match, and avoid test keys in production.
- **HTTPS Issues:** reCAPTCHA may require HTTPS in production.

**Security Best Practices:**
- Never expose secret keys.
- Use environment variables for keys.
- Regularly rotate keys.
- Monitor reCAPTCHA analytics.
- Keep domain list minimal.

**Additional Configuration:**
- Customize widget appearance (`theme`, `size`, `callback`) in Blade views.
- Force specific language using `hl` parameter in script URL.

## 16. Laravel Livewire Submission Form Component

This Livewire component handles form submissions with real-time validation, user-friendly errors, and success notifications. It supports responsive design with Tailwind CSS, flexible JSON data storage in the `submissions` table (including IP/user agent), and single submission protection.

**Key Features:**
- Real-time validation and error messages.
- Animated success messages and loading states.
- Google reCAPTCHA v2 integration (conditional display).
- Multi-language support.
- Graceful fallback for disabled JavaScript.

**Installation & Usage:**
1. Install Livewire and reCAPTCHA packages.
2. Configure reCAPTCHA keys in `.env`.
3. Run migrations to create the `submissions` table.
4. Include `<livewire:submission-form />` in your Blade views.

The component consists of `app/Livewire/SubmissionForm.php` and `resources/views/livewire/submission-form.blade.php`. Validation rules are defined using `#[Validate]` attributes. Customization of styling, rules, fields, and messages is supported. Security features include CSRF protection, input sanitization, IP tracking, and reCAPTCHA. Performance is optimized with debounced real-time validation and efficient updates.

## 17. Scheduled Content Publishing

This project includes a scheduled task to automatically publish content that has a `published_at` date in the future. This ensures that content goes live at the intended time without manual intervention.

**Implementation Details:**

The scheduling is configured in the `routes/console.php` file, which defines console commands and their schedules.

-   **Artisan Command:** The `cms:publish-scheduled` Artisan command is responsible for checking for and publishing content. The logic for this command is located in [`app/Console/Commands/PublishScheduledContent.php`](app/Console/Commands/PublishScheduledContent.php). This command iterates through configured content models, identifies records with a `status` of `Scheduled` and a `published_at` date in the past or present, and updates their `status` to `Published`.
-   **Scheduler Configuration:** The command is scheduled to run every thirty minutes and prevents overlapping executions.

**`routes/console.php` snippet:**

```php
Schedule::command('cms:publish-scheduled')
    ->everyThirtyMinutes()
    ->withoutOverlapping();
```

To ensure the Laravel scheduler runs, you must add the following Cron entry to your server:

```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

## 18. Instagram Feed Integration

This project integrates Instagram feeds using the `Yizack/instagram-feed` package.

### Setup

1.  **Install the package:**
    ```bash
    composer require yizack/instagram-feed
    ```

2.  **Get your Instagram Access Token:**
    Follow the instructions on the `Yizack/instagram-feed` GitHub page to obtain a Meta Developer App and generate an access token:
    [https://github.com/Yizack/instagram-feed?tab=readme-ov-file#meta-developer-app](https://github.com/Yizack/instagram-feed?tab=readme-ov-file#meta-developer-app)

3.  **Store the Access Token:**
    Add your Instagram Access Token to your `.env` file:
    ```dotenv
    INSTAGRAM_ACCESS_TOKEN="YOUR_ACCESS_TOKEN_HERE"
    ```

### Component Location

The Instagram Feed component consists of:

*   **Class:** [`app/View/Components/InstagramFeed.php`](app/View/Components/InstagramFeed.php)
*   **Blade View:** [`resources/views/components/instagram-feed.blade.php`](resources/views/components/instagram-feed.blade.php)

### How to Render

You can render the Instagram Feed component in your Blade templates using the following syntax:

```blade
<x-instagram-feed type="all" :columns="4" />
```

*   `type`: (Optional) Filter feeds by type. Accepted values are `all`, `image`, `video`, or `reel`. Defaults to `all`.
*   `columns`: (Optional) Number of columns for the grid display (1-6). Defaults to `3`.

### Token Refresh

To ensure your Instagram Access Token remains valid, an Artisan command is available to refresh it. This command is automatically triggered monthly.

*   **Artisan Command:** `php artisan instagram:refresh-token`
*   **Automation:** This command is scheduled to run monthly to automatically update the token.


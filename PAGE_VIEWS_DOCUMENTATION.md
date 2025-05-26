# Page Views Tracking Documentation

This documentation explains how to use the page views tracking feature implemented in your CMS.

## Overview

The page views tracking feature automatically counts and displays how many times each post has been viewed. The view count is stored in the `custom_fields` JSON column of the posts table under the `page_views` key.

## Features

- **Automatic Tracking**: Page views are automatically incremented when a post is viewed
- **JSON Storage**: Uses the existing `custom_fields` JSON column
- **Reusable Components**: Includes Blade components for displaying page views
- **Query Scopes**: Provides useful query scopes for filtering and ordering by views
- **Extensible**: Can be easily extended to other content types using the `HasPageViews` trait

## Implementation Details

### Model Changes

The `Post` model has been updated with:

1. **JSON Casting**: The `custom_fields` column is now cast as an array
2. **HasPageViews Trait**: Provides all page view functionality
3. **Automatic Accessor**: `$post->page_views` returns the current view count

### Controller Changes

The `ContentController::singleContent()` method now automatically increments page views for any model that uses the `HasPageViews` trait when they are viewed. This makes the feature extensible to other content types without code changes.

### Available Methods

#### Basic Methods

```php
// Get current page views
$views = $post->page_views;

// Increment page views (usually done automatically)
$post->incrementPageViews();

// Increment by a specific amount
$post->incrementPageViews(5);

// Set page views to a specific number
$post->setPageViews(100);

// Reset page views to zero
$post->resetPageViews();
```

#### Query Scopes

```php
// Get posts ordered by page views (most viewed first)
$popularPosts = Post::orderByPageViews()->get();

// Get posts ordered by page views (least viewed first)
$leastViewed = Post::orderByPageViews('asc')->get();

// Get top 10 most viewed posts
$topPosts = Post::mostViewed(10)->get();

// Get posts with at least 100 views
$popularPosts = Post::withMinViews(100)->get();

// Combine scopes
$recentPopular = Post::where('created_at', '>=', now()->subDays(30))
    ->mostViewed(5)
    ->get();
```

## Display Components

### Page Views Component

Use the `<x-ui.page-views>` component to display page views:

```blade
{{-- Basic usage --}}
<x-ui.page-views :count="$post->page_views" />

{{-- With different formats --}}
<x-ui.page-views :count="$post->page_views" format="long" />    {{-- "1,234 views" --}}
<x-ui.page-views :count="$post->page_views" format="short" />   {{-- "1.2k" --}}
<x-ui.page-views :count="$post->page_views" format="number" />  {{-- "1,234" --}}

{{-- Without icon --}}
<x-ui.page-views :count="$post->page_views" :show-icon="false" />

{{-- With custom CSS class --}}
<x-ui.page-views :count="$post->page_views" class="my-custom-class" />
```

### Template Examples

#### Single Post Template

The `resources/views/templates/singles/post.blade.php` template shows page views in the post meta section:

```blade
<div class="post-meta">
    <time datetime="{{ $content->published_at->toISOString() }}">
        {{ $content->published_at->format('F j, Y') }}
    </time>
    
    <span class="post-author">by {{ $content->author->name }}</span>
    
    <x-ui.page-views :count="$content->page_views" format="long" class="post-views" />
</div>
```

#### Archive Template

The `resources/views/templates/archives/posts.blade.php` template shows page views in post cards:

```blade
<div class="post-card-meta">
    <time datetime="{{ $post->published_at->toISOString() }}">
        {{ $post->published_at->format('M j, Y') }}
    </time>
    
    <x-ui.page-views :count="$post->page_views" format="short" class="post-card-views" />
</div>
```

## Styling

Basic CSS styles are provided in `resources/css/page-views.css`. Include this in your main CSS file or compile it with your build process.

Key CSS classes:
- `.page-views`: Main container
- `.page-views-icon`: The eye icon
- `.page-views-count`: The count text
- `.post-meta`: Post metadata container
- `.post-card-meta`: Post card metadata container

## Database Migration

Run the migration to initialize page views for existing posts:

```bash
php artisan migrate
```

This will set `page_views` to 0 for all existing posts that don't have this field.

## Extending to Other Models

To add page view tracking to other models (like Pages), follow these steps:

1. **Add the trait** to your model:
```php
use App\Traits\HasPageViews;

class Page extends Model
{
    use HasPageViews;
    
    protected $casts = [
        'custom_fields' => 'array',
        // ... other casts
    ];
}
```

2. **The controller automatically detects the trait** - no changes needed! The `ContentController::singleContent()` method automatically checks if a model uses the `HasPageViews` trait and increments views accordingly.

3. **Use in templates**:
```blade
<x-ui.page-views :count="$page->page_views" />
```

## Performance Considerations

- Page view increments use a single database update query
- The JSON column is indexed for better query performance
- Consider implementing caching for high-traffic sites
- For very high traffic, consider using a queue for view increments

## Advanced Usage

### Custom View Tracking Logic

You can customize when views are counted by modifying the controller logic:

```php
// Only count views for authenticated users
if (auth()->check() && in_array(\App\Traits\HasPageViews::class, class_uses_recursive($content))) {
    $content->incrementPageViews();
}

// Only count unique views per session
if (!session()->has("viewed_content_{$content->id}") &&
    in_array(\App\Traits\HasPageViews::class, class_uses_recursive($content))) {
    $content->incrementPageViews();
    session()->put("viewed_content_{$content->id}", true);
}
```

### Analytics Integration

You can integrate with analytics services:

```php
// In your controller after incrementing views
if ($content instanceof \App\Models\Post) {
    $content->incrementPageViews();
    
    // Send to analytics
    Analytics::track('post_viewed', [
        'post_id' => $content->id,
        'post_title' => $content->title,
        'view_count' => $content->page_views,
    ]);
}
```

## Troubleshooting

### Views Not Incrementing

1. Check that the Post model uses the `HasPageViews` trait
2. Verify the `custom_fields` column is cast as 'array'
3. Ensure the controller is calling `incrementPageViews()`

### Display Issues

1. Check that the page views component is properly included
2. Verify the CSS is loaded
3. Ensure the `$post->page_views` accessor is working

### Performance Issues

1. Consider adding database indexes for large datasets
2. Implement caching for frequently accessed posts
3. Use queues for high-traffic scenarios
# Page Likes Feature Documentation

This documentation explains how to use the page likes feature that allows users to like posts with cookie-based tracking to prevent multiple likes from the same user.

## Overview

The page likes feature allows logged-out users to like posts by clicking a like button. The system tracks likes using browser cookies to prevent multiple likes from the same user. Users can click the button again to unlike a post.

## Features

- **Cookie-Based Tracking**: Uses browser cookies to track which posts a user has liked
- **Toggle Functionality**: Users can like and unlike posts by clicking the button
- **Real-Time Updates**: Like counts update immediately via AJAX
- **Responsive Design**: Works on all device sizes
- **Accessibility**: Proper focus states and keyboard navigation
- **Visual Feedback**: Heart animation and color changes when liked

## Implementation Details

### Model Changes

The `HasPageViews` trait now includes page likes functionality:

```php
// Get current likes count
$likes = $post->page_likes;

// Increment likes
$post->incrementPageLikes();

// Decrement likes
$post->decrementPageLikes();

// Set specific likes count
$post->setPageLikes(50);

// Reset likes to zero
$post->resetPageLikes();
```

### Controller Integration

The `ContentController` includes a new `toggleLike()` method that:

1. Validates the content type and slug
2. Checks if the model supports likes (uses `HasPageViews` trait)
3. Reads the user's like status from cookies
4. Toggles the like status and updates the database
5. Sets/removes the cookie accordingly
6. Returns JSON response with updated like count

### Route

A new POST route handles like/unlike requests:

```php
Route::post('/{content_type_key}/{content_slug}/like', [ContentController::class, 'toggleLike'])
    ->name('cms.content.like');
```

## Available Methods

### Basic Methods

```php
// Get current page likes
$likes = $post->page_likes;

// Increment page likes
$post->incrementPageLikes();

// Decrement page likes (won't go below 0)
$post->decrementPageLikes();

// Set page likes to a specific number
$post->setPageLikes(100);

// Reset page likes to zero
$post->resetPageLikes();
```

### Query Scopes

```php
// Get posts ordered by likes (most liked first)
$popularPosts = Post::orderByPageLikes()->get();

// Get posts ordered by likes (least liked first)
$leastLiked = Post::orderByPageLikes('asc')->get();

// Get top 10 most liked posts
$topPosts = Post::mostLiked(10)->get();

// Get posts with at least 50 likes
$popularPosts = Post::withMinLikes(50)->get();

// Combine with other scopes
$recentPopular = Post::where('created_at', '>=', now()->subDays(7))
    ->mostLiked(5)
    ->get();
```

## Display Components

### Like Button Component

Use the `<x-ui.like-button>` component to display interactive like buttons:

```blade
{{-- Basic usage --}}
<x-ui.like-button :content="$post" :lang="$lang" :content-type="$contentType" />

{{-- Different sizes --}}
<x-ui.like-button :content="$post" :lang="$lang" :content-type="$contentType" size="sm" />
<x-ui.like-button :content="$post" :lang="$lang" :content-type="$contentType" size="md" />
<x-ui.like-button :content="$post" :lang="$lang" :content-type="$contentType" size="lg" />

{{-- Different variants --}}
<x-ui.like-button :content="$post" :lang="$lang" :content-type="$contentType" variant="default" />
<x-ui.like-button :content="$post" :lang="$lang" :content-type="$contentType" variant="minimal" />
<x-ui.like-button :content="$post" :lang="$lang" :content-type="$contentType" variant="outline" />

{{-- Hide count --}}
<x-ui.like-button :content="$post" :lang="$lang" :content-type="$contentType" :show-count="false" />

{{-- Custom CSS class --}}
<x-ui.like-button :content="$post" :lang="$lang" :content-type="$contentType" class="my-custom-class" />
```

### Template Examples

#### Single Post Template

The `resources/views/templates/singles/post.blade.php` template shows:

1. **Like count in meta section**:
```blade
<span class="post-likes">
    {{ $content->page_likes }} {{ Str::plural('like', $content->page_likes) }}
</span>
```

2. **Interactive like button**:
```blade
<div class="post-actions">
    <x-ui.like-button 
        :content="$content" 
        :lang="$lang" 
        :content-type="$content_type" 
        class="post-like-button"
    />
</div>
```

#### Archive Template

The `resources/views/templates/archives/posts.blade.php` template shows compact like buttons in post cards:

```blade
<x-ui.like-button 
    :content="$post" 
    :lang="$lang" 
    :content-type="$post_type" 
    size="sm" 
    variant="minimal" 
    class="post-card-like"
/>
```

## Styling

CSS styles are provided in `resources/css/like-button.css`. Key features:

- **Responsive design** with mobile-friendly sizing
- **Dark mode support** with appropriate color schemes
- **Animation effects** including heart beat animation when liked
- **Accessibility features** with proper focus states
- **High contrast mode** support for better accessibility

Key CSS classes:
- `.like-button`: Main button container
- `.like-icon`: The heart icon
- `.like-count`: The count display
- `.like-button.liked`: Liked state styling
- `.post-actions`: Container for post action buttons
- `.post-likes`: Static likes count display

## Cookie Management

The system uses cookies to track user likes:

- **Cookie name**: `liked_content_{post_id}`
- **Cookie value**: `'true'` when liked, `'false'` or expired when not liked
- **Expiry**: 1 year for liked posts
- **Domain**: Same as your application
- **Security**: Uses Laravel's cookie encryption

## JavaScript Functionality

The like button includes built-in JavaScript that:

1. **Handles clicks** on like buttons
2. **Shows loading states** during AJAX requests
3. **Updates UI** based on server response
4. **Provides visual feedback** with animations
5. **Handles errors** gracefully
6. **Manages CSRF tokens** automatically

The JavaScript is automatically included when you use the like button component.

## Database Migration

Run the migration to initialize page likes for existing posts:

```bash
php artisan migrate
```

This will set both `page_views` and `page_likes` to 0 for all existing posts that don't have these fields.

## Security Considerations

### Cookie-Based Tracking

- **Pros**: Works for logged-out users, simple implementation, no database overhead
- **Cons**: Can be cleared by users, not 100% reliable for preventing abuse

### CSRF Protection

All like requests include CSRF token validation to prevent cross-site request forgery attacks.

### Rate Limiting

Consider adding rate limiting to the like endpoint for high-traffic sites:

```php
Route::post('/{content_type_key}/{content_slug}/like', [ContentController::class, 'toggleLike'])
    ->middleware('throttle:60,1') // 60 requests per minute
    ->name('cms.content.like');
```

## Advanced Usage

### Custom Like Tracking Logic

You can customize the like tracking logic in the controller:

```php
// Only allow likes for published posts
if ($content->status !== ContentStatus::Published) {
    return response()->json(['error' => 'Cannot like unpublished content'], 400);
}

// Add IP-based tracking for additional security
$ipAddress = $request->ip();
$ipCookieName = "liked_content_{$content->id}_ip_{$ipAddress}";
// ... additional logic
```

### Analytics Integration

Track like events in your analytics:

```php
// In the toggleLike method after successful like/unlike
Analytics::track($hasLiked ? 'post_liked' : 'post_unliked', [
    'post_id' => $content->id,
    'post_title' => $content->title,
    'likes_count' => $content->fresh()->page_likes,
]);
```

### Custom Styling

Override the default styles by creating your own CSS:

```css
.like-button.my-custom-style {
    background: linear-gradient(45deg, #ff6b6b, #ee5a24);
    border: none;
    color: white;
}

.like-button.my-custom-style.liked {
    background: linear-gradient(45deg, #ff3838, #c44569);
}
```

## Troubleshooting

### Likes Not Working

1. **Check CSRF token**: Ensure the meta tag is present in your layout
2. **Verify JavaScript**: Check browser console for errors
3. **Check routes**: Ensure the like route is properly registered
4. **Verify trait**: Ensure the model uses the `HasPageViews` trait

### Cookie Issues

1. **Check browser settings**: Ensure cookies are enabled
2. **Verify domain**: Ensure cookies are set for the correct domain
3. **Check expiry**: Verify cookie expiration settings

### Styling Issues

1. **Include CSS**: Ensure the like button CSS is loaded
2. **Check conflicts**: Look for CSS conflicts with existing styles
3. **Verify classes**: Ensure proper CSS classes are applied

## Performance Considerations

- **Database queries**: Each like/unlike requires one database update
- **Cookie storage**: Minimal impact on browser storage
- **AJAX requests**: Lightweight JSON responses
- **Caching**: Consider caching popular posts with high like counts

For high-traffic sites, consider:
- Implementing queue-based like processing
- Using Redis for temporary like storage
- Adding database indexes for like-based queries
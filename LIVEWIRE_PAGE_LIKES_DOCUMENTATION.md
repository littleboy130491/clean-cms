# Livewire Page Likes Feature Documentation

This documentation explains how to use the Livewire-based page likes feature that allows users to like posts with cookie-based tracking to prevent multiple likes from the same user.

## Overview

The page likes feature is implemented using Laravel Livewire, providing a reactive and seamless user experience. Users can like and unlike posts by clicking a like button, with real-time updates and cookie-based tracking to prevent multiple likes from the same user.

## Features

- **Livewire-Powered**: Uses Laravel Livewire for reactive components
- **Cookie-Based Tracking**: Uses browser cookies to track which posts a user has liked
- **Toggle Functionality**: Users can like and unlike posts by clicking the button
- **Real-Time Updates**: Like counts update immediately without page refresh
- **No Custom CSS Required**: Uses Tailwind CSS classes for styling
- **Loading States**: Built-in loading indicators during interactions
- **Accessibility**: Proper focus states and keyboard navigation

## Implementation Details

### Livewire Component

The `LikeButton` Livewire component ([`app/Livewire/LikeButton.php`](app/Livewire/LikeButton.php:1)) handles:

1. **State Management**: Tracks like status and count
2. **Cookie Handling**: Reads and sets cookies for like tracking
3. **Database Updates**: Increments/decrements like counts
4. **Real-time Updates**: Updates UI immediately after actions

### Model Integration

The `HasPageLikes` trait ([`app/Traits/HasPageLikes.php`](app/Traits/HasPageLikes.php:1)) provides:

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

## Usage

### Basic Livewire Component Usage

```blade
{{-- Basic usage --}}
<livewire:like-button :content="$post" :lang="$lang" :content-type="$contentType" />

{{-- Different sizes --}}
<livewire:like-button :content="$post" :lang="$lang" :content-type="$contentType" size="sm" />
<livewire:like-button :content="$post" :lang="$lang" :content-type="$contentType" size="md" />
<livewire:like-button :content="$post" :lang="$lang" :content-type="$contentType" size="lg" />

{{-- Different variants --}}
<livewire:like-button :content="$post" :lang="$lang" :content-type="$contentType" variant="default" />
<livewire:like-button :content="$post" :lang="$lang" :content-type="$contentType" variant="minimal" />
<livewire:like-button :content="$post" :lang="$lang" :content-type="$contentType" variant="outline" />

{{-- Hide count --}}
<livewire:like-button :content="$post" :lang="$lang" :content-type="$contentType" :show-count="false" />

{{-- In loops (requires unique key) --}}
@foreach($posts as $post)
    <livewire:like-button 
        :content="$post" 
        :lang="$lang" 
        :content-type="$contentType" 
        :key="'like-button-' . $post->id"
    />
@endforeach
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
    <livewire:like-button :content="$content" :lang="$lang" :content-type="$content_type" />
</div>
```

#### Archive Template

The `resources/views/templates/archives/posts.blade.php` template shows compact like buttons in post cards:

```blade
<livewire:like-button 
    :content="$post" 
    :lang="$lang" 
    :content-type="$post_type" 
    size="sm" 
    variant="minimal"
    :key="'like-button-' . $post->id"
/>
```

## Component Properties

### Required Properties

- `content`: The model instance (must use `HasPageLikes` trait)
- `lang`: Current language code
- `contentType`: Content type key (e.g., 'posts')

### Optional Properties

- `showCount`: Whether to display the like count (default: `true`)
- `size`: Button size - 'sm', 'md', 'lg' (default: 'md')
- `variant`: Button style - 'default', 'minimal', 'outline' (default: 'default')

## Styling

The component uses Tailwind CSS classes for styling:

### Size Classes
- **sm**: `text-sm px-2 py-1`
- **md**: `text-base px-3 py-2`
- **lg**: `text-lg px-4 py-3`

### Variant Classes
- **default**: `bg-gray-100 hover:bg-gray-200 text-gray-700`
- **minimal**: `bg-transparent hover:bg-gray-100 text-gray-600`
- **outline**: `border border-gray-300 bg-white hover:bg-gray-50 text-gray-700`

### State Classes
- **Liked state**: `text-red-600` with filled heart icon
- **Loading state**: `animate-pulse` on the heart icon
- **Disabled state**: Applied during loading

## Cookie Management

The system uses cookies to track user likes:

- **Cookie name**: `liked_content_{post_id}`
- **Cookie value**: `'true'` when liked, `'false'` or expired when not liked
- **Expiry**: 1 year for liked posts
- **Domain**: Same as your application
- **Security**: Uses Laravel's cookie encryption

## Database Migration

Run the migration to initialize page likes for existing posts:

```bash
php artisan migrate
```

This will set both `page_views` and `page_likes` to 0 for all existing posts that don't have these fields.

## Livewire Events

The component dispatches a browser event when likes are toggled:

```javascript
// Listen for like toggle events
document.addEventListener('like-toggled', function(event) {
    console.log('Content ID:', event.detail.contentId);
    console.log('Liked:', event.detail.liked);
    console.log('Likes Count:', event.detail.likesCount);
});
```

## Extending to Other Models

To add page likes to other models (like Pages):

1. **Add the trait** to your model:
```php
use App\Traits\HasPageLikes;

class Page extends Model
{
    use HasPageLikes;
    
    protected $casts = [
        'custom_fields' => 'array',
        // ... other casts
    ];
}
```

2. **Use in templates**:
```blade
<livewire:like-button :content="$page" :lang="$lang" :content-type="'pages'" />
```

## Performance Considerations

### Livewire Benefits
- **Automatic CSRF protection**
- **Built-in loading states**
- **Optimized DOM updates**
- **No custom JavaScript required**

### Database Impact
- Each like/unlike requires one database update
- Consider adding indexes for like-based queries on high-traffic sites

### Cookie Storage
- Minimal impact on browser storage
- Cookies are encrypted by Laravel

## Security Features

### Automatic CSRF Protection
Livewire automatically handles CSRF token validation for all component interactions.

### Cookie Encryption
Laravel automatically encrypts all cookies, including the like tracking cookies.

### Trait Validation
The component validates that the model uses the `HasPageLikes` trait before allowing interactions.

## Troubleshooting

### Likes Not Working

1. **Check Livewire installation**: Ensure Livewire is properly installed and configured
2. **Verify trait**: Ensure the model uses the `HasPageLikes` trait
3. **Check browser console**: Look for JavaScript errors
4. **Verify component registration**: Ensure the Livewire component is properly registered

### Cookie Issues

1. **Check browser settings**: Ensure cookies are enabled
2. **Verify domain**: Ensure cookies are set for the correct domain
3. **Check expiry**: Verify cookie expiration settings

### Styling Issues

1. **Tailwind CSS**: Ensure Tailwind CSS is properly installed and configured
2. **Check classes**: Verify that Tailwind classes are being applied correctly
3. **Purge settings**: Ensure Tailwind isn't purging the component classes

## Advanced Usage

### Custom Event Handling

Listen for like toggle events in your JavaScript:

```javascript
document.addEventListener('like-toggled', function(event) {
    // Custom analytics tracking
    gtag('event', 'like_toggle', {
        'content_id': event.detail.contentId,
        'liked': event.detail.liked,
        'likes_count': event.detail.likesCount
    });
});
```

### Custom Styling

Override the component's styling by extending the Livewire component:

```php
class CustomLikeButton extends LikeButton
{
    public function getVariantClasses()
    {
        return match($this->variant) {
            'custom' => 'bg-blue-500 hover:bg-blue-600 text-white',
            default => parent::getVariantClasses()
        };
    }
}
```

### Rate Limiting

Add rate limiting to prevent abuse:

```php
// In the LikeButton component
use Illuminate\Support\Facades\RateLimiter;

public function toggleLike()
{
    $key = 'like-toggle:' . request()->ip() . ':' . $this->content->id;
    
    if (RateLimiter::tooManyAttempts($key, 10)) {
        $this->addError('rate_limit', 'Too many attempts. Please try again later.');
        return;
    }
    
    RateLimiter::hit($key, 60); // 10 attempts per minute
    
    // ... rest of the toggle logic
}
```

This Livewire implementation provides a clean, reactive solution for page likes without requiring custom CSS or JavaScript, leveraging Laravel's built-in features for security and performance.
<?php

namespace App\Models;

use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Awcodes\Curator\Models\Media;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;
use Afatmustafa\SeoSuite\Models\Traits\InteractsWithSeoSuite;
use App\Enums\ContentStatus;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use App\Models\Comment;
use App\Traits\HasPageViews;

class Post extends Model
{
    use HasFactory, HasTranslations, SoftDeletes, InteractsWithSeoSuite, HasPageViews;


    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'author_id',
        'content',
        'custom_fields',
        'excerpt',
        'featured',
        'featured_image',
        'menu_order',
        'published_at',
        'slug',
        'status',
        'template',
        'title'
    ];


    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'menu_order' => 'integer',
        'featured' => 'boolean',
        'status' => ContentStatus::class,
        'published_at' => 'datetime',
        'custom_fields' => 'array'
    ];


    /**
     * The attributes that are translatable.
     *
     * @var array<int, string>
     */
    public $translatable = [
        'content',
        'custom_fields',
        'excerpt',
        'slug',
        'title'
    ];

    //--------------------------------------------------------------------------
    // Relationships
    //--------------------------------------------------------------------------

    /**
     * Define the featuredImage relationship to Curator Media.
     */
    public function featuredImage(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'featured_image', 'id');
    }

    /**
     * Define the author relationship.
     */
    public function author(): BelongsTo
    {
        // Use the base class name for the ::class constant
        // Add foreign key argument if specified in YAML
        return $this->belongsTo(User::class);
    }

    /**
     * Define the categories relationship.
     */
    public function categories(): BelongsToMany
    {
        // Use the base class name for the ::class constant
        // Add foreign key argument if specified in YAML
        return $this->belongsToMany(Category::class);
    }

    /**
     * Define the tags relationship.
     */
    public function tags(): BelongsToMany
    {
        // Use the base class name for the ::class constant
        // Add foreign key argument if specified in YAML
        return $this->belongsToMany(Tag::class);
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

}

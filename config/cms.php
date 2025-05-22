<?php

return [
    'site_name' => env('APP_NAME', 'Clean CMS'),
    'site_description' => env('CMS_SITE_DESCRIPTION', 'My CMS Description'),
    'site_url' => env('APP_URL', 'http://localhost'),
    'site_email' => env('CMS_SITE_EMAIL'),
    'site_phone' => env('CMS_SITE_PHONE'),
    'site_logo' => env('CMS_SITE_LOGO', 'logo.png'),
    'site_favicon' => env('CMS_SITE_FAVICON', 'favicon.ico'),
    'site_social_media' => [
        'facebook' => env('CMS_FACEBOOK'),
        'twitter' => env('CMS_TWITTER'),
        'instagram' => env('CMS_INSTAGRAM'),
        'linkedin' => env('CMS_LINKEDIN'),
        'youtube' => env('CMS_YOUTUBE'),
        'whatsapp'  => env('CMS_WHATSAPP'),
    ],
    'site_social_media_enabled' => env('CMS_SOCIAL_MEDIA_ENABLED', true),

    'multilanguage_enabled' => env('MULTILANGUAGE_ENABLED', true),

    'default_language' => env('APP_LOCALE', 'en'),

    'language_available' => [
        'en' => 'English',
        'id' => 'Indonesian',
        'zh-cn' => 'Chinese',
        'ko' => 'Korean',
    ],

    'content_models' => [
        'pages' => [
            'model' => App\Models\Page::class,
            'type' => 'content',
            'has_archive' => false,
            'has_single' => true,
            'single_view' => 'templates.singles.page',
        ],
        'posts' => [
            'model' => App\Models\Post::class,
            'type' => 'content',
            'has_archive' => true,
            'has_single' => true,
            'archive_view' => 'templates.archives.post',
            'single_view' => 'templates.singles.post',
            'archive_SEO_title' => 'Archive: Posts',
            'archive_SEO_description' => 'Archive of all posts',

        ],
        'categories' => [
            'model' => App\Models\Category::class,
            'type' => 'taxonomy',
            'has_archive' => true,
            'has_single' => false,
            'archive_view' => 'templates.archives.category',
            'display_content_from' => 'posts', // the relationship name in the model

        ],
        'tags' => [
            'model' => App\Models\Tag::class,
            'type' => 'taxonomy',
            'has_archive' => true,
            'has_single' => false,
            'archive_view' => 'templates.archives.tag',
            'display_content_from' => 'posts', // the relationship name in the model

        ],
    ],

    'static_page_model' => App\Models\Page::class,
    'static_page_slug' => 'pages',
    'front_page_slug' => 'home',

    'pagination_limit' => env('CMS_PAGINATION_LIMIT', 12),
    'commentable_resources' => [
        App\Models\Post::class => App\Filament\Resources\PostResource::class,
        App\Models\Page::class => App\Filament\Resources\PageResource::class,
    ]
];
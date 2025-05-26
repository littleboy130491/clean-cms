<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Post;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Initialize page_views for existing posts that don't have it
        Post::whereNull('custom_fields')
            ->orWhere(function ($query) {
                $query->whereJsonMissing('custom_fields->page_views');
            })
            ->chunk(100, function ($posts) {
                foreach ($posts as $post) {
                    $customFields = $post->custom_fields ?? [];
                    if (!isset($customFields['page_views'])) {
                        $customFields['page_views'] = 0;
                        $post->update(['custom_fields' => $customFields]);
                    }
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove page_views from all posts
        Post::whereJsonContains('custom_fields->page_views', '>=', 0)
            ->chunk(100, function ($posts) {
                foreach ($posts as $post) {
                    $customFields = $post->custom_fields ?? [];
                    unset($customFields['page_views']);
                    $post->update(['custom_fields' => $customFields]);
                }
            });
    }
};
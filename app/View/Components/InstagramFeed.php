<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Yizack\InstagramFeed as IGfeed;
use Illuminate\Support\Facades\Cache;


class InstagramFeed extends Component
{
    public $feeds;
    public $type;
    public $columns;

    /**
     * @param string $type
     * @param int $columns
     */
    public function __construct($type = 'all', $columns = 3)
    {
        $accessToken = config('cms.instagram.access_token');
        $ig = new IGfeed($accessToken);

        $fields = ["id", "media_type", "media_url", "thumbnail_url", "permalink", "timestamp", "caption"];
        $cacheKey = 'instagram_feeds_' . md5(json_encode($fields)) . '_' . $accessToken;

        $feeds = Cache::remember($cacheKey, now()->addMinutes(15), function () use ($ig, $fields) {
            return $ig->getFeed($fields);
        });

        if ($type !== 'all') {
            $mediaType = strtoupper($type);
            $feeds = array_filter($feeds, function ($item) use ($mediaType) {
                if ($mediaType === 'REEL') {
                    return $item['media_type'] === 'VIDEO' && (isset($item['caption']) && str_contains(strtolower($item['caption']), 'reel'));
                }
                return $item['media_type'] === $mediaType;
            });
        }

        $this->feeds = $feeds;
        $this->type = $type;
        $this->columns = (int) $columns;
    }

    public function render()
    {
        return view('components.instagram-feed');
    }
}

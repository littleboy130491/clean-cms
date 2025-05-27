<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Awcodes\Curator\Models\Media;
class Component extends Model
{
    use SoftDeletes;



    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'data',
        'notes',
    ];


    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'data' => 'array',
    ];

    protected $appends = ['blocks'];

    /**
     * Return the raw data blocks, but with image URLs injected.
     *
     * @return array
     */
    public function getBlocksAttribute(): array
    {
        return collect($this->data)->map(function (array $block) {
            // if this block has an "media" key, fetch its URL
            if (isset($block['data']['media_id'])) {
                $media = Media::find($block['data']['media_id']);
                $block['data']['media_url'] = $media?->url;
            }

            return $block;
        })->all();
    }

}

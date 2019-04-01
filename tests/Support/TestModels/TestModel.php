<?php

namespace Okipa\MediaLibraryExtension\Tests\Support\TestModels;

use Okipa\MediaLibraryExtension\HasMedia\HasMediaTrait;

use Illuminate\Database\Eloquent\Model;
use Okipa\MediaLibraryExtension\HasMedia\HasMedia;
use Spatie\MediaLibrary\Models\Media;

class TestModel extends Model implements HasMedia
{
    use HasMediaTrait;

    protected $table = 'test_models';
    protected $guarded = [];
    public $timestamps = false;

    /**
     * Register the conversions that should be performed.
     *
     * @param \Spatie\MediaLibrary\Models\Media $media
     *
     * @return void
     */
    public function registerMediaConversions(Media $media = null)
    {
    }
}

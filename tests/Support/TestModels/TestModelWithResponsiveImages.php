<?php

namespace Okipa\MediaLibraryExtension\Tests\Support\TestModels;

use Spatie\MediaLibrary\Models\Media;

class TestModelWithResponsiveImages extends TestModel
{
    /**
     * Register the conversions that should be performed.
     *
     * @param \Spatie\MediaLibrary\Models\Media $media
     *
     * @return void
     * @throws \Spatie\Image\Exceptions\InvalidManipulation
     */
    public function registerMediaConversions(Media $media = null)
    {
        $this->addMediaConversion('thumb')
            ->withResponsiveImages()
            ->width(50)
            ->nonQueued();
        $this->addMediaConversion('otherImageConversion')
            ->greyscale();
    }
}

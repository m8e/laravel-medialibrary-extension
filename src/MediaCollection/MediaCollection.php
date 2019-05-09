<?php

namespace Okipa\MediaLibraryExtension\MediaCollection;

class MediaCollection extends \Spatie\MediaLibrary\MediaCollection\MediaCollection
{
    /** @var array $acceptsMimeTypes */
    public $acceptsMimeTypes = [];

    /**
     * Set the media collection accepted mime types.
     *
     * @param array $mimeTypes
     *
     * @return \Okipa\MediaLibraryExtension\MediaCollection\MediaCollection
     */
    public function acceptsMimeTypes(array $mimeTypes): MediaCollection
    {
        $this->acceptsMimeTypes = $mimeTypes;

        return $this;
    }
}

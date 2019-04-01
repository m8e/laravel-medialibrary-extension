<?php

namespace Okipa\MediaLibraryExtension\MediaCollection;

class MediaCollection extends \Spatie\MediaLibrary\MediaCollection\MediaCollection
{
    /** @var array */
    public $acceptsMimeTypes = [];

    /**
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

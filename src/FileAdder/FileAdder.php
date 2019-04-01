<?php

namespace Okipa\MediaLibraryExtension\FileAdder;

use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\FileUnacceptableForCollection;
use Spatie\MediaLibrary\File as PendingFile;
use Spatie\MediaLibrary\Models\Media;

class FileAdder extends \Spatie\MediaLibrary\FileAdder\FileAdder
{
    /** @var \Okipa\MediaLibraryExtension\HasMedia\HasMedia $hasMedia */
    protected $subject;

    /**
     * @param \Spatie\MediaLibrary\Models\Media $media
     */
    protected function guardAgainstDisallowedFileAdditions(Media $media)
    {
        $file = PendingFile::createFromMedia($media);
        if ($collection = $this->getMediaCollection($media->collection_name)) {
            $acceptsFileClosure = $collection->acceptsFile;
            $acceptsFile = $acceptsFileClosure($file, $this->subject);
            $acceptsMimeTypes = ! empty($collection->acceptsMimeTypes)
                ? in_array($file->mimeType, $collection->acceptsMimeTypes)
                : true;
            if (! $acceptsFile || ! $acceptsMimeTypes) {
                throw FileUnacceptableForCollection::create($file, $collection, $this->subject);
            }
        }
    }
}

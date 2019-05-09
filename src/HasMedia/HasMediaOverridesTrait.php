<?php

namespace Okipa\MediaLibraryExtension\HasMedia;

use Illuminate\Support\Str;
use Okipa\MediaLibraryExtension\FileAdder\FileAdder;
use Okipa\MediaLibraryExtension\FileAdder\FileAdderFactory;
use Okipa\MediaLibraryExtension\MediaCollection\MediaCollection;
use Spatie\MediaLibrary\Conversion\Conversion;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\InvalidBase64Data;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\UnreachableUrl;
use Spatie\MediaLibrary\Models\Media;

trait HasMediaOverridesTrait
{
    use \Spatie\MediaLibrary\HasMedia\HasMediaTrait;

    /**
     * Add a file to the media library.
     *
     * @param string|\Symfony\Component\HttpFoundation\File\UploadedFile $file
     *
     * @return \Spatie\MediaLibrary\FileAdder\FileAdder
     */
    public function addMedia($file)
    {
        return app(FileAdderFactory::class)->create($this, $file);
    }

    /**
     * Add a file from a request.
     *
     * @param string $key
     *
     * @return \Spatie\MediaLibrary\FileAdder\FileAdder
     */
    public function addMediaFromRequest(string $key)
    {
        return app(FileAdderFactory::class)->createFromRequest($this, $key);
    }

    /**
     * Add multiple files from a request by keys.
     *
     * @param string[] $keys
     *
     * @return \Spatie\MediaLibrary\FileAdder\FileAdder[]
     */
    public function addMultipleMediaFromRequest(array $keys)
    {
        return app(FileAdderFactory::class)->createMultipleFromRequest($this, $keys);
    }

    /**
     * Add all files from a request.
     *
     * @return \Spatie\MediaLibrary\FileAdder\FileAdder[]
     */
    public function addAllMediaFromRequest()
    {
        return app(FileAdderFactory::class)->createAllFromRequest($this);
    }

    /**
     * Add a remote file to the medialibrary.
     *
     * @param string $url
     * @param string|array ...$allowedMimeTypes
     *
     * @return \Spatie\MediaLibrary\FileAdder\FileAdder
     */
    public function addMediaFromUrl(string $url, ...$allowedMimeTypes)
    {
        if (! $stream = @fopen($url, 'r')) {
            throw UnreachableUrl::create($url);
        }
        $temporaryFile = tempnam(sys_get_temp_dir(), 'media-library');
        file_put_contents($temporaryFile, $stream);
        $this->guardAgainstInvalidMimeType($temporaryFile, $allowedMimeTypes);
        $filename = basename(parse_url($url, PHP_URL_PATH));
        $filename = str_replace('%20', ' ', $filename);
        if ($filename === '') {
            $filename = 'file';
        }
        $mediaExtension = explode('/', mime_content_type($temporaryFile));
        if (! Str::contains($filename, '.')) {
            $filename = "{$filename}.{$mediaExtension[1]}";
        }

        return app(FileAdderFactory::class)
            ->create($this, $temporaryFile)
            ->usingName(pathinfo($filename, PATHINFO_FILENAME))
            ->usingFileName($filename);
    }

    /**
     * Add a base64 encoded file to the medialibrary.
     *
     * @param string $base64data
     * @param string|array ...$allowedMimeTypes
     *
     * @return \Okipa\MediaLibraryExtension\FileAdder\FileAdder
     */
    public function addMediaFromBase64(string $base64data, ...$allowedMimeTypes): FileAdder
    {
        // strip out data uri scheme information (see RFC 2397)
        if (strpos($base64data, ';base64') !== false) {
            $base64data = last(explode(';', $base64data));
            $base64data = last(explode(',', $base64data));
        }
        // strict mode filters for non-base64 alphabet characters
        if (base64_decode($base64data, true) === false) {
            throw InvalidBase64Data::create();
        }
        // decoding and then reencoding should not change the data
        if (base64_encode(base64_decode($base64data)) !== $base64data) {
            throw InvalidBase64Data::create();
        }
        $binaryData = base64_decode($base64data);
        // temporarily store the decoded data on the filesystem to be able to pass it to the fileAdder
        $tmpFile = tempnam(sys_get_temp_dir(), 'medialibrary');
        file_put_contents($tmpFile, $binaryData);
        $this->guardAgainstInvalidMimeType($tmpFile, $allowedMimeTypes);
        $file = app(FileAdderFactory::class)->create($this, $tmpFile);

        return $file;
    }

    /**
     * @param string $name
     *
     * @return \Okipa\MediaLibraryExtension\MediaCollection\MediaCollection
     */
    public function addMediaCollection(string $name): MediaCollection
    {
        $mediaCollection = MediaCollection::create($name);
        $this->mediaCollections[] = $mediaCollection;

        return $mediaCollection;
    }

    /**
     * @param \Spatie\MediaLibrary\Models\Media|null $media
     */
    public function registerAllMediaConversions(Media $media = null)
    {
        $this->registerMediaCollections();
        collect($this->mediaCollections)->each(function (MediaCollection $mediaCollection) use ($media) {
            $actualMediaConversions = $this->mediaConversions;
            $this->mediaConversions = [];
            ($mediaCollection->mediaConversionRegistrations)($media);
            $preparedMediaConversions = collect($this->mediaConversions)
                ->each(function (Conversion $conversion) use ($mediaCollection) {
                    $conversion->performOnCollections($mediaCollection->name);
                })
                ->values()
                ->toArray();
            $this->mediaConversions = array_merge($actualMediaConversions, $preparedMediaConversions);
        });
        $this->registerMediaConversions($media);
    }
}

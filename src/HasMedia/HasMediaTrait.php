<?php

namespace Okipa\MediaLibraryExtension\HasMedia;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\Models\Media;
use Spatie\MediaLibrary\Conversion\Conversion;
use Okipa\MediaLibraryExtension\FileAdder\FileAdder;
use Okipa\MediaLibraryExtension\FileAdder\FileAdderFactory;
use Okipa\MediaLibraryExtension\Exceptions\CollectionNotFound;
use Okipa\MediaLibraryExtension\Exceptions\ConversionsNotFound;
use Okipa\MediaLibraryExtension\MediaCollection\MediaCollection;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\UnreachableUrl;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\InvalidBase64Data;

trait HasMediaTrait
{
    use \Spatie\MediaLibrary\HasMedia\HasMediaTrait;

    /**
     * Add a file to the medialibrary.
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

    /**
     * Get the constraints validation string for a media collection.
     *
     * @param string $collectionName
     *
     * @return string
     * @throws \Okipa\MediaLibraryExtension\Exceptions\CollectionNotFound
     * @throws \Okipa\MediaLibraryExtension\Exceptions\ConversionsNotFound
     */
    public function validationConstraints(string $collectionName): string
    {
        $dimensions = $this->dimensionValidationConstraints($collectionName);
        $mimeTypes = $this->mimeTypesValidationConstraints($collectionName);
        $separator = $dimensions && $mimeTypes ? '|' : '';

        return ($dimensions ? $dimensions . $separator : '') . ($mimeTypes);
    }

    /**
     * Get a collection dimension validation constraints string from its name name.
     *
     * @param string $collectionName
     *
     * @return string
     * @throws \Okipa\MediaLibraryExtension\Exceptions\CollectionNotFound
     * @throws \Okipa\MediaLibraryExtension\Exceptions\ConversionsNotFound
     */
    public function dimensionValidationConstraints(string $collectionName): string
    {
        $maxSizes = $this->collectionMaxSizes($collectionName);
        if (empty($maxSizes)) {
            return '';
        }
        $width = $maxSizes['width'] ? 'min_width=' . $maxSizes['width'] : '';
        $height = $maxSizes['height'] ? 'min_height=' . $maxSizes['height'] : '';
        $separator = $width && $height ? ',' : '';

        return $width || $height ? 'dimensions:' . $width . $separator . $height : '';
    }

    /**
     * Get a media collection object from its name.
     *
     * @param string $collectionName
     *
     * @return \Okipa\MediaLibraryExtension\MediaCollection\MediaCollection|null
     */
    public function getCollection(string $collectionName): ?MediaCollection
    {
        $collection = Arr::where($this->mediaCollections, function ($collection) use ($collectionName) {
            return $collection->name === $collectionName;
        });

        return $collection ? head($collection) : null;
    }

    /**
     * Get declared conversions from a media collection name.
     *
     * @param string $collectionName
     *
     * @return array
     */
    public function getConversions(string $collectionName): array
    {
        return Arr::where($this->mediaConversions, function ($conversion) use ($collectionName) {
            return $conversion->shouldBePerformedOn($collectionName);
        });
    }

    /**
     * Check if the given media collection contains images from its declared accepted mime types.
     * It is considered that a collection without declared accepted mime types may contains images.
     *
     * @param \Okipa\MediaLibraryExtension\MediaCollection\MediaCollection $collection
     *
     * @return bool
     */
    public function mayContainsImages(MediaCollection $collection): bool
    {
        return ! $collection->acceptsMimeTypes
            || ! empty(array_filter(
                $collection->acceptsMimeTypes,
                function ($mimeTypes) {
                    return Str::contains($mimeTypes, 'image');
                }
            ));
    }

    /**
     * Get registered collection max width and max height from its name.
     *
     * @param string $collectionName
     *
     * @return array
     * @throws \Okipa\MediaLibraryExtension\Exceptions\CollectionNotFound
     * @throws \Okipa\MediaLibraryExtension\Exceptions\ConversionsNotFound
     */
    public function collectionMaxSizes(string $collectionName): array
    {
        $this->registerAllMediaConversions();
        $collection = $this->getCollection($collectionName);
        if (! $collection) {
            throw CollectionNotFound::notDeclaredInModel($this, $collectionName);
        }
        if (! $this->mayContainsImages($collection)) {
            return [];
        }
        $conversions = $this->getConversions($collectionName);
        if (empty($conversions)) {
            throw ConversionsNotFound::noneDeclaredInModel($this);
        }
        $sizes = [];
        foreach ($conversions as $key => $conversion) {
            $manipulations = head($conversion->getManipulations()->toArray());
            $sizes[$key] = [
                'width'  => Arr::get($manipulations, 'width'),
                'height' => Arr::get($manipulations, 'height'),
            ];
        }

        return $this->getMaxWidthAndMaxHeight($sizes);
    }

    /**
     * Calculate max width and max height from sizes array.
     *
     * @param array $sizes
     *
     * @return array
     */
    protected function getMaxWidthAndMaxHeight(array $sizes): array
    {
        $width = ! empty($sizes) ? max(Arr::pluck($sizes, 'width')) : null;
        $height = ! empty($sizes) ? max(Arr::pluck($sizes, 'height')) : null;

        return compact('width', 'height');
    }

    /**
     * Get a collection mime types constraints validation string from its name.
     *
     * @param string $collectionName
     *
     * @return string
     * @throws \Okipa\MediaLibraryExtension\Exceptions\CollectionNotFound
     */
    public function mimeTypesValidationConstraints(string $collectionName): string
    {
        $this->registerMediaCollections();
        $collection = head(Arr::where($this->mediaCollections, function ($collection) use ($collectionName) {
            return $collection->name === $collectionName;
        }));
        if (! $collection) {
            throw CollectionNotFound::notDeclaredInModel($this, $collectionName);
        }
        $validationString = '';
        if (! empty($collection->acceptsMimeTypes)) {
            $validationString .= 'mimetypes:' . implode(',', $collection->acceptsMimeTypes);
        }

        return $validationString;
    }

    /**
     * Get the constraints legend string for a media collection.
     *
     * @param string $collectionName
     *
     * @return string
     * @throws \Okipa\MediaLibraryExtension\Exceptions\CollectionNotFound
     * @throws \Okipa\MediaLibraryExtension\Exceptions\ConversionsNotFound
     */
    public function constraintsLegend(string $collectionName): string
    {
        $dimensionsLegend = $this->dimensionsLegend($collectionName);
        $mimeTypesLegend = $this->mimeTypesLegend($collectionName);
        $separator = $dimensionsLegend && $mimeTypesLegend ? ' ' : '';

        return ($dimensionsLegend ? $dimensionsLegend . $separator : '') . $mimeTypesLegend;
    }

    /**
     * Get a collection dimensions constraints legend string from its name.
     *
     * @param string $collectionName
     *
     * @return string
     * @throws \Okipa\MediaLibraryExtension\Exceptions\CollectionNotFound
     * @throws \Okipa\MediaLibraryExtension\Exceptions\ConversionsNotFound
     */
    public function dimensionsLegend(string $collectionName): string
    {
        $sizes = $this->collectionMaxSizes($collectionName);
        $width = Arr::get($sizes, 'width');
        $height = Arr::get($sizes, 'height');
        $legend = '';
        if ($width && $height) {
            $legend = __('medialibrary::medialibrary.constraint.dimensions.both', [
                'width'  => $width,
                'height' => $height,
            ]);
        } elseif ($width && ! $height) {
            $legend = __('medialibrary::medialibrary.constraint.dimensions.width', [
                'width' => $width,
            ]);
        } elseif (! $width && $height) {
            $legend = __('medialibrary::medialibrary.constraint.dimensions.height', [
                'height' => $height,
            ]);
        }

        return $legend;
    }

    /**
     * Get a collection mime types constraints legend string from its name.
     *
     * @param string $collectionName
     *
     * @return string
     * @throws \Okipa\MediaLibraryExtension\Exceptions\CollectionNotFound
     */
    public function mimeTypesLegend(string $collectionName): string
    {
        $this->registerMediaCollections();
        $collection = head(Arr::where($this->mediaCollections, function ($collection) use ($collectionName) {
            return $collection->name === $collectionName;
        }));
        if (! $collection) {
            throw CollectionNotFound::notDeclaredInModel($this, $collectionName);
        }
        $legendString = '';
        if (! empty($collection->acceptsMimeTypes)) {
            $legendString .= __('medialibrary::medialibrary.constraint.mimeTypes', [
                'mimetypes' => implode(', ', $collection->acceptsMimeTypes),
            ]);
        }

        return $legendString;
    }
}

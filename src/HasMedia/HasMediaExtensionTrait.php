<?php

namespace Okipa\MediaLibraryExtension\HasMedia;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Okipa\MediaLibraryExtension\Exceptions\CollectionNotFound;
use Okipa\MediaLibraryExtension\Exceptions\ConversionsNotFound;
use Okipa\MediaLibraryExtension\MediaCollection\MediaCollection;

trait HasMediaExtensionTrait
{
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

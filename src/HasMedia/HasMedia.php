<?php

namespace Okipa\MediaLibraryExtension\HasMedia;

use Okipa\MediaLibraryExtension\MediaCollection\MediaCollection;

interface HasMedia extends \Spatie\MediaLibrary\HasMedia\HasMedia
{
    /**
     * Get a collection mime types constraints validation string from its name.
     *
     * @param string $collectionName
     *
     * @return string
     */
    public function mimeTypesValidationConstraints(string $collectionName): string;

    /**
     * Get a collection dimension validation constraints string from its name.
     *
     * @param string $collectionName
     *
     * @return string
     * @throws \Okipa\MediaLibraryExtension\Exceptions\CollectionNotFound
     * @throws \Okipa\MediaLibraryExtension\Exceptions\ConversionsNotFound
     */
    public function dimensionValidationConstraints(string $collectionName): string;

    /**
     * Get registered collection max width and max height from its name.
     *
     * @param string $collectionName
     *
     * @return array
     * @throws \Okipa\MediaLibraryExtension\Exceptions\CollectionNotFound
     * @throws \Okipa\MediaLibraryExtension\Exceptions\ConversionsNotFound
     */
    public function collectionMaxSizes(string $collectionName): array;

    /**
     * Get the constraints validation string for a media collection.
     *
     * @param string $collectionName
     *
     * @return string
     * @throws \Okipa\MediaLibraryExtension\Exceptions\CollectionNotFound
     * @throws \Okipa\MediaLibraryExtension\Exceptions\ConversionsNotFound
     */
    public function validationConstraints(string $collectionName): string;

    /**
     * Get the constraints legend string for a media collection.
     *
     * @param string $collectionName
     *
     * @return string
     */
    public function constraintsLegend(string $collectionName): string;

    /**
     * Get a collection dimensions constraints legend string from its name.
     *
     * @param string $collectionName
     *
     * @return string
     */
    public function dimensionsLegend(string $collectionName): string;

    /**
     * Get a collection mime types constraints legend string from its name.
     *
     * @param string $collectionName
     *
     * @return string
     */
    public function mimeTypesLegend(string $collectionName): string;

    /**
     * Check if the given media collection contains images from its declared accepted mime types.
     * It is considered that a collection without declared accepted mime types may contains images.
     *
     * @param \Okipa\MediaLibraryExtension\MediaCollection\MediaCollection $collection
     *
     * @return bool
     */
    public function mayContainsImages(MediaCollection $collection): bool;

    /**
     * Get declared conversions from a media collection name.
     *
     * @param string $collectionName
     *
     * @return array
     */
    public function getConversions(string $collectionName): array;

    /**
     * Get a media collection object from its name.
     *
     * @param string $collectionName
     *
     * @return \Okipa\MediaLibraryExtension\MediaCollection\MediaCollection|null
     */
    public function getCollection(string $collectionName): ?MediaCollection;
}

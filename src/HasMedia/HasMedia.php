<?php

namespace Okipa\MediaLibraryExtension\HasMedia;

interface HasMedia extends \Spatie\MediaLibrary\HasMedia\HasMedia
{
    /**
     * Get the mime types constraints validation string for a media collection.
     *
     * @param string $collectionName
     *
     * @return string
     */
    public function mimeTypesValidationConstraints(string $collectionName): string;

    /**
     * Get the dimension validation constraints string for a media collection.
     *
     * @param string $collectionName
     *
     * @return string
     * @throws \Okipa\MediaLibraryExtension\Exceptions\CollectionNotFound
     * @throws \Okipa\MediaLibraryExtension\Exceptions\ConversionsNotFound
     */
    public function dimensionValidationConstraints(string $collectionName): string;

    /**
     * Get registered collection max width and max height.
     *
     * @param string $collectionName
     *
     * @return array
     * @throws \Okipa\MediaLibraryExtension\Exceptions\CollectionNotFound
     * @throws \Okipa\MediaLibraryExtension\Exceptions\ConversionsNotFound
     */
    public function collectionMaxSizes(string $collectionName = 'default'): array;

    /**
     * Get the constraints validation string for a media collection.
     *
     * @param string $collectionName
     *
     * @return string
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
     * Get the dimensions constraints legend string for a media collection.
     *
     * @param string $collectionName
     *
     * @return string
     */
    public function collectionDimensionsLegend(string $collectionName): string;

    /**
     * Get the mime types constraints legend string for a media collection.
     *
     * @param string $collectionName
     *
     * @return string
     */
    public function collectionMimeTypesLegend(string $collectionName): string;
}

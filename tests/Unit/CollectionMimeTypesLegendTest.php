<?php

namespace Okipa\MediaLibraryExtension\Tests\Unit\UrlGenerator;

use Okipa\MediaLibraryExtension\Exceptions\CollectionNotFound;
use Okipa\MediaLibraryExtension\Tests\Support\TestModels\TestModelWithGlobalAndCollectionConversions;
use Okipa\MediaLibraryExtension\Tests\Support\TestModels\TestModelWithGlobalConversionOnly;
use Okipa\MediaLibraryExtension\Tests\Support\TestModels\TestModelWithGlobalConversionOnlyWithoutCollection;
use Okipa\MediaLibraryExtension\Tests\TestCase;

class CollectionMimeTypesLegendTest extends TestCase
{
    /**
     * @test
     */
    public function itThrowsExceptionWhenItIsCalledWithNonExistentCollection()
    {
        $this->expectException(CollectionNotFound::class);
        $this->expectExceptionMessage('No collection `logo` declared in the Okipa\MediaLibraryExtension\Tests\Support'
            . '\TestModels\TestModelWithGlobalConversionOnlyWithoutCollection-model');
        (new TestModelWithGlobalConversionOnlyWithoutCollection)->collectionMimeTypesLegend('logo');
    }

    /**
     * @test
     */
    public function itReturnsNoMimeTypesLegendWhenNoneDeclared()
    {
        $dimensionsLegendString = (new TestModelWithGlobalConversionOnly)
            ->collectionMimeTypesLegend('logo');
        $this->assertEquals('', $dimensionsLegendString);
    }

    /**
     * @test
     */
    public function itReturnsMimeTypesLegendWhenAreDeclared()
    {
        $dimensionsLegendString = (new TestModelWithGlobalAndCollectionConversions)
            ->collectionMimeTypesLegend('logo');
        $this->assertEquals(__('medialibrary::medialibrary.constraint.mimeTypes', [
            'mimetypes' => 'image/jpeg, image/png',
        ]), $dimensionsLegendString);
    }
}

<?php

namespace Okipa\MediaLibraryExtension\Tests\Unit\UrlGenerator;

use Okipa\MediaLibraryExtension\Exceptions\CollectionNotFound;
use Okipa\MediaLibraryExtension\Exceptions\ConversionsNotFound;
use Okipa\MediaLibraryExtension\Tests\Support\TestModels\TestModelWithCollectionWithoutConversions;
use Okipa\MediaLibraryExtension\Tests\Support\TestModels\TestModelWithGlobalConversionOnly;
use Okipa\MediaLibraryExtension\Tests\Support\TestModels\TestModelWithGlobalConversionOnlyWithoutCollection;
use Okipa\MediaLibraryExtension\Tests\Support\TestModels\TestModelWithGlobalConversionWithNoSize;
use Okipa\MediaLibraryExtension\Tests\Support\TestModels\TestModelWithGlobalConversionWithNoSizeAndNoMimeTypes;
use Okipa\MediaLibraryExtension\Tests\TestCase;

class CollectionLegendTest extends TestCase
{
    /**
     * @test
     */
    public function itThrowsExceptionWhenItIsCalledWithNonExistentCollection()
    {
        $this->expectException(CollectionNotFound::class);
        $this->expectExceptionMessage('No collection `logo` declared in the Okipa\MediaLibraryExtension\Tests\Support'
            . '\TestModels\TestModelWithGlobalConversionOnlyWithoutCollection-model');
        (new TestModelWithGlobalConversionOnlyWithoutCollection)->constraintsLegend('logo');
    }

    /**
     * @test
     */
    public function itThrowsExceptionWhenItIsCalledWithNonExistentConversions()
    {
        $this->expectException(ConversionsNotFound::class);
        $this->expectExceptionMessage('No conversion declared in the Okipa\MedialibraryExtension\Tests'
            . '\Support\TestModels\TestModelWithCollectionWithoutConversions-model');
        (new TestModelWithCollectionWithoutConversions)->constraintsLegend('logo');
    }

    /**
     * @test
     */
    public function itReturnsNoLegendWhenNoConstraintIsDeclared()
    {
        $legendString = (new TestModelWithGlobalConversionWithNoSizeAndNoMimeTypes)
            ->constraintsLegend('logo');
        $this->assertEquals('', $legendString);
    }

    /**
     * @test
     */
    public function itReturnsOnlyDimensionLegendWhenOnlyDimensionsDeclared()
    {
        $legendString = (new TestModelWithGlobalConversionOnly)->constraintsLegend('logo');
        $this->assertEquals(__('medialibrary::medialibrary.constraint.dimensions.both', [
            'width'  => 60,
            'height' => 20,
        ]), $legendString);
    }

    /**
     * @test
     */
    public function itReturnsOnlyMimeTypesLegendWhenOnlyMimeTypesDeclared()
    {
        $legendString = (new TestModelWithGlobalConversionWithNoSize)->constraintsLegend('logo');
        $this->assertEquals(__('medialibrary::medialibrary.constraint.mimeTypes', [
            'mimetypes' => 'image/jpeg, image/png',
        ]), $legendString);
    }
}

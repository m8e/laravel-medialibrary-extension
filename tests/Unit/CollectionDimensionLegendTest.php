<?php

namespace Okipa\MediaLibraryExtension\Tests\Unit\UrlGenerator;

use Okipa\MediaLibraryExtension\Exceptions\CollectionNotFound;
use Okipa\MediaLibraryExtension\Exceptions\ConversionsNotFound;
use Okipa\MediaLibraryExtension\Tests\Support\TestModels\TestModelWithCollectionWithoutConversions;
use Okipa\MediaLibraryExtension\Tests\Support\TestModels\TestModelWithGlobalAndCollectionConversions;
use Okipa\MediaLibraryExtension\Tests\Support\TestModels\TestModelWithGlobalConversionOnlyWithoutCollection;
use Okipa\MediaLibraryExtension\Tests\Support\TestModels\TestModelWithGlobalConversionWithNoSize;
use Okipa\MediaLibraryExtension\Tests\Support\TestModels\TestModelWithGlobalConversionWithOnlyHeight;
use Okipa\MediaLibraryExtension\Tests\Support\TestModels\TestModelWithGlobalConversionWithOnlyWidth;
use Okipa\MediaLibraryExtension\Tests\TestCase;

class CollectionDimensionLegendTest extends TestCase
{
    /**
     * @test
     */
    public function itThrowsExceptionWhenItIsCalledWithNonExistentCollection()
    {
        $this->expectException(CollectionNotFound::class);
        $this->expectExceptionMessage('No collection `logo` declared in the Okipa\MediaLibraryExtension\Tests\Support'
            . '\TestModels\TestModelWithGlobalConversionOnlyWithoutCollection-model');
        (new TestModelWithGlobalConversionOnlyWithoutCollection)->collectionDimensionsLegend('logo');
    }

    /**
     * @test
     */
    public function itThrowsExceptionWhenItIsCalledWithNonExistentConversions()
    {
        $this->expectException(ConversionsNotFound::class);
        $this->expectExceptionMessage('No conversion declared in the Okipa\MedialibraryExtension\Tests\Support'
            . '\TestModels\TestModelWithCollectionWithoutConversions-model');
        (new TestModelWithCollectionWithoutConversions)->collectionDimensionsLegend('logo');
    }

    /**
     * @test
     */
    public function itReturnsOnlyWidthDimensionLegendWhenOnlyWidthIsDeclared()
    {
        $dimensionsLegendString = (new TestModelWithGlobalConversionWithOnlyWidth)->collectionDimensionsLegend('logo');
        $this->assertEquals(__('medialibrary::medialibrary.constraint.dimensions.width', [
            'width' => 120,
        ]), $dimensionsLegendString);
    }

    /**
     * @test
     */
    public function itReturnsOnlyHeightDimensionLegendWhenOnlyHeightIsDeclared()
    {
        $dimensionsLegendString = (new TestModelWithGlobalConversionWithOnlyHeight)->collectionDimensionsLegend('logo');
        $this->assertEquals(__('medialibrary::medialibrary.constraint.dimensions.height', [
            'height' => 30,
        ]), $dimensionsLegendString);
    }

    /**
     * @test
     */
    public function itReturnsNoDimensionLegendWhenNoSizeIsDeclared()
    {
        $dimensionsLegendString = (new TestModelWithGlobalConversionWithNoSize)->collectionDimensionsLegend('logo');
        $this->assertEquals('', $dimensionsLegendString);
    }

    /**
     * @test
     */
    public function itReturnsWidthAndHeightDimensionLegendWhenBothAreDeclared()
    {
        $dimensionsLegendString = (new TestModelWithGlobalAndCollectionConversions)->collectionDimensionsLegend('logo');
        $this->assertEquals(__('medialibrary::medialibrary.constraint.dimensions.both', [
            'width'  => 100,
            'height' => 80,
        ]), $dimensionsLegendString);
    }
}

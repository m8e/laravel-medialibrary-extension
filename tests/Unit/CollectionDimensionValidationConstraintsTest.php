<?php

namespace Okipa\MediaLibraryExtension\Tests\Unit\UrlGenerator;

use Okipa\MediaLibraryExtension\Exceptions\CollectionNotFound;
use Okipa\MediaLibraryExtension\Exceptions\ConversionsNotFound;
use Okipa\MediaLibraryExtension\Tests\Support\TestModels\TestModelWithCollectionConversionsOnly;
use Okipa\MediaLibraryExtension\Tests\Support\TestModels\TestModelWithCollectionWithoutConversions;
use Okipa\MediaLibraryExtension\Tests\Support\TestModels\TestModelWithGlobalAndCollectionConversions;
use Okipa\MediaLibraryExtension\Tests\Support\TestModels\TestModelWithGlobalConversionOnly;
use Okipa\MediaLibraryExtension\Tests\Support\TestModels\TestModelWithGlobalConversionOnlyWithoutCollection;
use Okipa\MediaLibraryExtension\Tests\Support\TestModels\TestModelWithGlobalConversionWithNoSize;
use Okipa\MediaLibraryExtension\Tests\Support\TestModels\TestModelWithGlobalConversionWithOnlyHeight;
use Okipa\MediaLibraryExtension\Tests\Support\TestModels\TestModelWithGlobalConversionWithOnlyWidth;
use Okipa\MediaLibraryExtension\Tests\TestCase;

class CollectionDimensionValidationConstraintsTest extends TestCase
{
    /**
     * @test
     */
    public function itThrowsExceptionWhenItIsCalledWithNonExistentCollection()
    {
        $this->expectException(CollectionNotFound::class);
        $this->expectExceptionMessage('No collection `logo` declared in the Okipa\MediaLibraryExtension\Tests'
            . '\Support\TestModels\TestModelWithGlobalConversionOnlyWithoutCollection-model');
        (new TestModelWithGlobalConversionOnlyWithoutCollection)->dimensionValidationConstraints('logo');
    }

    /**
     * @test
     */
    public function itThrowsExceptionWhenItIsCalledWithNonExistentConversions()
    {
        $this->expectException(ConversionsNotFound::class);
        $this->expectExceptionMessage('No conversion declared in the Okipa\MedialibraryExtension\Tests'
            .'\Support\TestModels\TestModelWithCollectionWithoutConversions-model');
        (new TestModelWithCollectionWithoutConversions)->dimensionValidationConstraints('logo');
    }

    /**
     * @test
     */
    public function itReturnsGlobalConversionDimensionValidationConstraintsWhenNoCollectionConversionsDeclared()
    {
        $dimensionsValidationConstraintsString = (new TestModelWithGlobalConversionOnly)
            ->dimensionValidationConstraints('logo');
        $this->assertEquals('dimensions:min_width=60,min_height=20', $dimensionsValidationConstraintsString);
    }

    /**
     * @test
     */
    public function itReturnsOnlyWidthDimensionValidationConstraintWhenOnlyWidthIsDeclared()
    {
        $dimensionsValidationConstraintsString = (new TestModelWithGlobalConversionWithOnlyWidth)
            ->dimensionValidationConstraints('logo');
        $this->assertEquals('dimensions:min_width=120', $dimensionsValidationConstraintsString);
    }

    /**
     * @test
     */
    public function itReturnsOnlyHeightDimensionValidationConstraintWhenOnlyHeightIsDeclared()
    {
        $dimensionsValidationConstraintsString = (new TestModelWithGlobalConversionWithOnlyHeight)
            ->dimensionValidationConstraints('logo');
        $this->assertEquals('dimensions:min_height=30', $dimensionsValidationConstraintsString);
    }

    /**
     * @test
     */
    public function itReturnsNoDimensionValidationConstraintWhenNoSizeIsDeclared()
    {
        $dimensionsValidationConstraintsString = (new TestModelWithGlobalConversionWithNoSize)
            ->dimensionValidationConstraints('logo');
        $this->assertEquals('', $dimensionsValidationConstraintsString);
    }

    /**
     * @test
     */
    public function itReturnsCollectionDimensionValidationConstraintsWhenNoGlobalConversionsDeclared()
    {
        $dimensionsValidationConstraintsString = (new TestModelWithCollectionConversionsOnly)
            ->dimensionValidationConstraints('logo');
        $this->assertEquals('dimensions:min_width=120,min_height=140', $dimensionsValidationConstraintsString);
    }

    /**
     * @test
     */
    public function itReturnsGlobalAndCollectionDimensionValidationConstraintsWhenBothAreDeclared()
    {
        $dimensionsValidationConstraintsString = (new TestModelWithGlobalAndCollectionConversions)
            ->dimensionValidationConstraints('logo');
        $this->assertEquals('dimensions:min_width=100,min_height=80', $dimensionsValidationConstraintsString);
    }
}

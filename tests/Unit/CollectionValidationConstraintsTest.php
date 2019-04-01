<?php

namespace Okipa\MediaLibraryExtension\Tests\Unit\UrlGenerator;

use Okipa\MediaLibraryExtension\Exceptions\CollectionNotFound;
use Okipa\MediaLibraryExtension\Exceptions\ConversionsNotFound;
use Okipa\MediaLibraryExtension\Tests\Support\TestModels\TestModelWithCollectionWithoutConversions;
use Okipa\MediaLibraryExtension\Tests\Support\TestModels\TestModelWithGlobalConversionOnly;
use Okipa\MediaLibraryExtension\Tests\Support\TestModels\TestModelWithGlobalConversionOnlyWithoutCollection;
use Okipa\MediaLibraryExtension\Tests\Support\TestModels\TestModelWithGlobalConversionWithNoSize;
use Okipa\MediaLibraryExtension\Tests\Support\TestModels\testModelWithGlobalConversionWithNoSizeAndNoMimeTypes;
use Okipa\MediaLibraryExtension\Tests\TestCase;

class CollectionValidationConstraintsTest extends TestCase
{
    /**
     * @test
     */
    public function itThrowsExceptionWhenItIsCalledWithNonExistentCollection()
    {
        $this->expectException(CollectionNotFound::class);
        $this->expectExceptionMessage('No collection `logo` declared in the Okipa\MediaLibraryExtension\Tests'
            . '\Support\TestModels\TestModelWithGlobalConversionOnlyWithoutCollection-model');
        (new TestModelWithGlobalConversionOnlyWithoutCollection)->validationConstraints('logo');
    }

    /**
     * @test
     */
    public function itThrowsExceptionWhenItIsCalledWithNonExistentConversions()
    {
        $this->expectException(ConversionsNotFound::class);
        $this->expectExceptionMessage('No conversion declared in the Okipa\MedialibraryExtension\Tests'
            . '\Support\TestModels\TestModelWithCollectionWithoutConversions-model');
        (new TestModelWithCollectionWithoutConversions)->validationConstraints('logo');
    }

    /**
     * @test
     */
    public function itReturnsNoValidationConstraintWhenNoneIsDeclared()
    {
        $validationConstraintsString =
            (new TestModelWithGlobalConversionWithNoSizeAndNoMimeTypes)->validationConstraints('logo');
        $this->assertEquals('', $validationConstraintsString);
    }

    /**
     * @test
     */
    public function itReturnsOnlyDimensionValidationConstraintsWhenOnlyDimensionsDeclared()
    {
        $validationConstraintsString = (new TestModelWithGlobalConversionOnly)
            ->validationConstraints('logo');
        $this->assertEquals('dimensions:min_width=60,min_height=20', $validationConstraintsString);
    }

    /**
     * @test
     */
    public function itReturnsOnlyMimeTypesValidationConstraintsWhenOnlyMimeTypesDeclared()
    {
        $validationConstraintsString = (new TestModelWithGlobalConversionWithNoSize)
            ->validationConstraints('logo');
        $this->assertEquals('mimetypes:image/jpeg,image/png', $validationConstraintsString);
    }
}

<?php

namespace Okipa\MediaLibraryExtension\Tests\Unit\UrlGenerator;

use Okipa\MediaLibraryExtension\Exceptions\CollectionNotFound;
use Okipa\MediaLibraryExtension\Tests\Support\TestModels\TestModelWithGlobalAndCollectionConversions;
use Okipa\MediaLibraryExtension\Tests\Support\TestModels\TestModelWithGlobalConversionOnly;
use Okipa\MediaLibraryExtension\Tests\Support\TestModels\TestModelWithGlobalConversionOnlyWithoutCollection;
use Okipa\MediaLibraryExtension\Tests\TestCase;

class CollectionMimeTypesValidationConstraintsTest extends TestCase
{
    /**
     * @test
     */
    public function itThrowsExceptionWhenItIsCalledWithNonExistentCollection()
    {
        $this->expectException(CollectionNotFound::class);
        $this->expectExceptionMessage('No collection `logo` declared in the Okipa\MediaLibraryExtension\Tests\Support'
            . '\TestModels\TestModelWithGlobalConversionOnlyWithoutCollection-model');
        (new TestModelWithGlobalConversionOnlyWithoutCollection)->mimeTypesValidationConstraints('logo');
    }

    /**
     * @test
     */
    public function itReturnsMimeTypesValidationConstraintsWhenDeclaredInCollection()
    {
        $mimeTypesValidationConstraintsString = (new TestModelWithGlobalAndCollectionConversions)
            ->mimeTypesValidationConstraints('logo');
        $this->assertEquals('mimetypes:image/jpeg,image/png', $mimeTypesValidationConstraintsString);
    }

    /**
     * @test
     */
    public function itReturnsNoCollectionMimeTypesValidationConstraintsWhenNoneDeclared()
    {
        $mimeTypesValidationConstraintsString = (new TestModelWithGlobalConversionOnly)
            ->mimeTypesValidationConstraints('logo');
        $this->assertEquals('', $mimeTypesValidationConstraintsString);
    }
}

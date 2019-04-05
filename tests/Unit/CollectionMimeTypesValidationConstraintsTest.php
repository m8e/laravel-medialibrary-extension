<?php

namespace Okipa\MediaLibraryExtension\Tests\Unit\UrlGenerator;

use Spatie\MediaLibrary\File;
use Spatie\Image\Manipulations;
use Spatie\MediaLibrary\Models\Media;
use Okipa\MediaLibraryExtension\Tests\TestCase;
use Okipa\MediaLibraryExtension\Exceptions\CollectionNotFound;
use Okipa\MediaLibraryExtension\Tests\Support\TestModels\TestModel;

class CollectionMimeTypesValidationConstraintsTest extends TestCase
{
    /**
     * @test
     */
    public function itThrowsExceptionWhenItIsCalledWithNonExistentCollection()
    {
        $testModel = new class extends TestModel
        {
            public function registerMediaCollections()
            {
                $this->addMediaConversion('thumb')->crop(Manipulations::CROP_CENTER, 60, 20);
            }
        };
        $this->expectException(CollectionNotFound::class);
        $testModel->mimeTypesValidationConstraints('logo');
    }

    /**
     * @test
     */
    public function itReturnsMimeTypesValidationConstraintsWhenDeclaredInCollection()
    {
        $testModel = new class extends TestModel
        {
            public function registerMediaCollections()
            {
                $this->addMediaCollection('logo')
                    ->acceptsFile(function (File $file) {
                        return true;
                    })
                    ->acceptsMimeTypes(['image/jpeg', 'image/png'])
                    ->registerMediaConversions(function (Media $media = null) {
                        $this->addMediaConversion('admin-panel')
                            ->crop(Manipulations::CROP_CENTER, 20, 80);
                    });
            }

            public function registerMediaConversions(Media $media = null)
            {
                $this->addMediaConversion('thumb')->crop(Manipulations::CROP_CENTER, 100, 70);
            }
        };
        $mimeTypesValidationConstraintsString = $testModel->mimeTypesValidationConstraints('logo');
        $this->assertEquals('mimetypes:image/jpeg,image/png', $mimeTypesValidationConstraintsString);
    }

    /**
     * @test
     */
    public function itReturnsNoCollectionMimeTypesValidationConstraintsWhenNoneDeclared()
    {
        $testModel = new class extends TestModel
        {
            public function registerMediaCollections()
            {
                $this->addMediaCollection('logo');
            }

            public function registerMediaConversions(Media $media = null)
            {
                $this->addMediaConversion('thumb')->crop(Manipulations::CROP_CENTER, 60, 20);
            }
        };
        $mimeTypesValidationConstraintsString = $testModel->mimeTypesValidationConstraints('logo');
        $this->assertEquals('', $mimeTypesValidationConstraintsString);
    }
}

<?php

namespace Okipa\MediaLibraryExtension\Tests\Unit\UrlGenerator;

use Spatie\Image\Manipulations;
use Spatie\MediaLibrary\Models\Media;
use Okipa\MediaLibraryExtension\Tests\TestCase;
use Okipa\MediaLibraryExtension\Exceptions\CollectionNotFound;
use Okipa\MediaLibraryExtension\Exceptions\ConversionsNotFound;
use Okipa\MediaLibraryExtension\Tests\Support\TestModels\TestModel;

class CollectionValidationConstraintsTest extends TestCase
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
        $testModel->validationConstraints('logo');
    }

    /**
     * @test
     */
    public function itThrowsExceptionWhenItIsCalledWithNonExistentConversions()
    {
        $testModel = new class extends TestModel
        {
            public function registerMediaCollections()
            {
                $this->addMediaCollection('logo')->acceptsMimeTypes(['image/jpeg', 'image/png']);
            }
        };
        $this->expectException(ConversionsNotFound::class);
        $testModel->validationConstraints('logo');
    }

    /**
     * @test
     */
    public function itReturnsNoValidationConstraintWhenNoneIsDeclared()
    {
        $testModel = new class extends TestModel
        {
            public function registerMediaCollections()
            {
                $this->addMediaCollection('logo');
            }

            public function registerMediaConversions(Media $media = null)
            {
                $this->addMediaConversion('thumb');
            }
        };
        $validationConstraintsString = $testModel->validationConstraints('logo');
        $this->assertEquals('', $validationConstraintsString);
    }

    /**
     * @test
     */
    public function itReturnsOnlyDimensionValidationConstraintsWhenOnlyDimensionsDeclared()
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
        $validationConstraintsString = $testModel->validationConstraints('logo');
        $this->assertEquals('dimensions:min_width=60,min_height=20', $validationConstraintsString);
    }

    /**
     * @test
     */
    public function itReturnsOnlyMimeTypesValidationConstraintsWhenOnlyMimeTypesDeclared()
    {
        $testModel = new class extends TestModel
        {
            public function registerMediaCollections()
            {
                $this->addMediaCollection('logo')->acceptsMimeTypes(['image/jpeg', 'image/png']);
            }

            public function registerMediaConversions(Media $media = null)
            {
                $this->addMediaConversion('thumb');
            }
        };
        $validationConstraintsString = $testModel->validationConstraints('logo');
        $this->assertEquals('mimetypes:image/jpeg,image/png', $validationConstraintsString);
    }
}

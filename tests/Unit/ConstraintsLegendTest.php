<?php

namespace Okipa\MediaLibraryExtension\Tests\Unit\UrlGenerator;

use Okipa\MediaLibraryExtension\Exceptions\CollectionNotFound;
use Okipa\MediaLibraryExtension\Exceptions\ConversionsNotFound;
use Okipa\MediaLibraryExtension\Tests\Support\TestModels\TestModel;
use Okipa\MediaLibraryExtension\Tests\TestCase;
use Spatie\Image\Manipulations;
use Spatie\MediaLibrary\Models\Media;

class ConstraintsLegendTest extends TestCase
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
        $testModel->constraintsLegend('logo');
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
        $testModel->constraintsLegend('logo');
    }

    /**
     * @test
     */
    public function itReturnsNoLegendWhenNoConstraintIsDeclared()
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
        $legendString = $testModel->constraintsLegend('logo');
        $this->assertEquals('', $legendString);
    }

    /**
     * @test
     */
    public function itReturnsOnlyDimensionLegendWhenOnlyDimensionsDeclared()
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
        $legendString = $testModel->constraintsLegend('logo');
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
        $legendString = $testModel->constraintsLegend('logo');
        $this->assertEquals(__('medialibrary::medialibrary.constraint.mimeTypes', [
            'mimetypes' => 'image/jpeg, image/png',
        ]), $legendString);
    }

    /**
     * @test
     */
    public function itReturnsOnlyMimeTypesLegendWhenNoImageDeclared()
    {
        $testModel = new class extends TestModel
        {
            public function registerMediaCollections()
            {
                $this->addMediaCollection('logo')->acceptsMimeTypes(['application/pdf']);
                ;
            }

            public function registerMediaConversions(Media $media = null)
            {
                $this->addMediaConversion('thumb')->crop(Manipulations::CROP_CENTER, 60, 20);
            }
        };
        $legendString = $testModel->constraintsLegend('logo');
        $this->assertEquals(__('medialibrary::medialibrary.constraint.mimeTypes', [
            'mimetypes' => 'application/pdf',
        ]), $legendString);
    }
}

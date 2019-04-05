<?php

namespace Okipa\MediaLibraryExtension\Tests\Unit\UrlGenerator;

use Spatie\MediaLibrary\File;
use Spatie\Image\Manipulations;
use Spatie\MediaLibrary\Models\Media;
use Okipa\MediaLibraryExtension\Tests\TestCase;
use Okipa\MediaLibraryExtension\Exceptions\CollectionNotFound;
use Okipa\MediaLibraryExtension\Exceptions\ConversionsNotFound;
use Okipa\MediaLibraryExtension\Tests\Support\TestModels\TestModel;

class CollectionDimensionLegendTest extends TestCase
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
        $testModel->dimensionsLegend('logo');
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
        $testModel->dimensionsLegend('logo');
    }

    /**
     * @test
     */
    public function itReturnsOnlyWidthDimensionLegendWhenOnlyWidthIsDeclared()
    {
        $testModel = new class extends TestModel
        {
            public function registerMediaCollections()
            {
                $this->addMediaCollection('logo')->acceptsMimeTypes(['image/jpeg', 'image/png']);
            }

            public function registerMediaConversions(Media $media = null)
            {
                $this->addMediaConversion('thumb')->width(120);
            }
        };
        $dimensionsLegendString = $testModel->dimensionsLegend('logo');
        $this->assertEquals(__('medialibrary::medialibrary.constraint.dimensions.width', [
            'width' => 120,
        ]), $dimensionsLegendString);
    }

    /**
     * @test
     */
    public function itReturnsOnlyHeightDimensionLegendWhenOnlyHeightIsDeclared()
    {
        $testModel = new class extends TestModel
        {
            public function registerMediaCollections()
            {
                $this->addMediaCollection('logo')->acceptsMimeTypes(['image/jpeg', 'image/png']);
            }

            public function registerMediaConversions(Media $media = null)
            {
                $this->addMediaConversion('thumb')->height(30);
            }
        };
        $dimensionsLegendString = $testModel->dimensionsLegend('logo');
        $this->assertEquals(__('medialibrary::medialibrary.constraint.dimensions.height', [
            'height' => 30,
        ]), $dimensionsLegendString);
    }

    /**
     * @test
     */
    public function itReturnsNoDimensionLegendWhenNoSizeIsDeclared()
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
        $dimensionsLegendString = $testModel->dimensionsLegend('logo');
        $this->assertEquals('', $dimensionsLegendString);
    }

    /**
     * @test
     */
    public function itReturnsWidthAndHeightDimensionLegendWhenBothAreDeclared()
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
        $dimensionsLegendString = $testModel->dimensionsLegend('logo');
        $this->assertEquals(__('medialibrary::medialibrary.constraint.dimensions.both', [
            'width'  => 100,
            'height' => 80,
        ]), $dimensionsLegendString);
    }

    /**
     * @test
     */
    public function itDoesNotReturnsDimensionsLegendWhenNoImageDeclared()
    {
        $testModel = new class extends TestModel
        {
            public function registerMediaCollections()
            {
                $this->addMediaCollection('logo')
                    ->acceptsFile(function (File $file) {
                        return true;
                    })
                    ->acceptsMimeTypes(['application/pdf'])
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
        $dimensionsLegendString = $testModel->dimensionsLegend('logo');
        $this->assertEquals('', $dimensionsLegendString);
    }
}

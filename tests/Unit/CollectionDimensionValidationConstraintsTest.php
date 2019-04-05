<?php

namespace Okipa\MediaLibraryExtension\Tests\Unit\UrlGenerator;

use Spatie\MediaLibrary\File;
use Spatie\Image\Manipulations;
use Spatie\MediaLibrary\Models\Media;
use Okipa\MediaLibraryExtension\Tests\TestCase;
use Okipa\MediaLibraryExtension\Exceptions\CollectionNotFound;
use Okipa\MediaLibraryExtension\Exceptions\ConversionsNotFound;
use Okipa\MediaLibraryExtension\Tests\Support\TestModels\TestModel;

class CollectionDimensionValidationConstraintsTest extends TestCase
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
        $testModel->dimensionValidationConstraints('logo');
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
        $testModel->dimensionValidationConstraints('logo');
    }

    /**
     * @test
     */
    public function itReturnsGlobalConversionDimensionValidationConstraintsWhenNoCollectionConversionsDeclared()
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
        $dimensionsValidationConstraintsString = $testModel->dimensionValidationConstraints('logo');
        $this->assertEquals('dimensions:min_width=60,min_height=20', $dimensionsValidationConstraintsString);
    }

    /**
     * @test
     */
    public function itReturnsOnlyWidthDimensionValidationConstraintWhenOnlyWidthIsDeclared()
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
        $dimensionsValidationConstraintsString = $testModel->dimensionValidationConstraints('logo');
        $this->assertEquals('dimensions:min_width=120', $dimensionsValidationConstraintsString);
    }

    /**
     * @test
     */
    public function itReturnsOnlyHeightDimensionValidationConstraintWhenOnlyHeightIsDeclared()
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
        $dimensionsValidationConstraintsString = $testModel->dimensionValidationConstraints('logo');
        $this->assertEquals('dimensions:min_height=30', $dimensionsValidationConstraintsString);
    }

    /**
     * @test
     */
    public function itReturnsNoDimensionValidationConstraintWhenNoSizeIsDeclared()
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
        $dimensionsValidationConstraintsString = $testModel->dimensionValidationConstraints('logo');
        $this->assertEquals('', $dimensionsValidationConstraintsString);
    }

    /**
     * @test
     */
    public function itReturnsCollectionDimensionValidationConstraintsWhenNoGlobalConversionsDeclared()
    {
        $testModel = new class extends TestModel
        {
            public function registerMediaCollections()
            {
                $this->addMediaCollection('logo')
                    ->acceptsMimeTypes(['image/jpeg', 'image/png'])
                    ->registerMediaConversions(function (Media $media = null) {
                        $this->addMediaConversion('admin-panel')
                            ->crop(Manipulations::CROP_CENTER, 100, 140);
                        $this->addMediaConversion('mail')
                            ->crop(Manipulations::CROP_CENTER, 120, 100);
                    });
            }

            public function registerMediaConversions(Media $media = null)
            {
                $this->addMediaConversion('thumb')->crop(Manipulations::CROP_CENTER, 40, 40);
            }
        };
        $dimensionsValidationConstraintsString = $testModel->dimensionValidationConstraints('logo');
        $this->assertEquals('dimensions:min_width=120,min_height=140', $dimensionsValidationConstraintsString);
    }

    /**
     * @test
     */
    public function itReturnsGlobalAndCollectionDimensionValidationConstraintsWhenBothAreDeclared()
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
        $dimensionsValidationConstraintsString = $testModel->dimensionValidationConstraints('logo');
        $this->assertEquals('dimensions:min_width=100,min_height=80', $dimensionsValidationConstraintsString);
    }

    /**
     * @test
     */
    public function itDoesNotReturnsDimensionValidationConstraintsWhenNoImageDeclared()
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
        $dimensionsValidationConstraintsString = $testModel->dimensionValidationConstraints('logo');
        $this->assertEquals('', $dimensionsValidationConstraintsString);
    }
}

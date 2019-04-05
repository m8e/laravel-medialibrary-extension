<?php

namespace Okipa\MedialibraryExtension\Tests\Feature\FileAdder\MediaConversions;

use Spatie\MediaLibrary\File;
use Okipa\MediaLibraryExtension\Tests\TestCase;
use Okipa\MediaLibraryExtension\Tests\Support\TestModels\TestModelWithConversion;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\FileUnacceptableForCollection;

class MediaCollectionTest extends TestCase
{
    /** @test */
    public function itCanAcceptCertainMimeTypes()
    {
        $testModel = new class extends TestModelWithConversion
        {
            public function registerMediaCollections()
            {
                $this->addMediaCollection('images')->acceptsMimeTypes(['image/jpeg']);
            }
        };
        $model = $testModel::create(['name' => 'testmodel']);
        $model->addMedia($this->getTestJpg())->preservingOriginal()->toMediaCollection('images');
        $this->expectException(FileUnacceptableForCollection::class);
        $model->addMedia($this->getTestPng())->preservingOriginal()->toMediaCollection('images');
    }

    /** @test */
    public function itCanAcceptCertainFilesAndMimeTypes()
    {
        $testModel = new class extends TestModelWithConversion
        {
            public function registerMediaCollections()
            {
                $this->addMediaCollection('images')->acceptsFile(function (File $file) {
                    return $file->size <= 30000;
                })->acceptsMimeTypes(['image/jpeg']);
            }
        };
        $model = $testModel::create(['name' => 'testmodel']);
        $model->addMedia($this->getTestJpg())->preservingOriginal()->toMediaCollection('images');
        $this->expectException(FileUnacceptableForCollection::class);
        $model->addMedia($this->getTestPng())->preservingOriginal()->toMediaCollection('images');
    }
}

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

class CollectionMaxSizeTest extends TestCase
{
    /**
     * @test
     */
    public function itThrowsExceptionWhenItIsCalledWithNonExistentCollection()
    {
        $this->expectException(CollectionNotFound::class);
        $this->expectExceptionMessage('No collection `logo` declared in the Okipa\MediaLibraryExtension\Tests\Support'
            . '\TestModels\TestModelWithGlobalConversionOnlyWithoutCollection-model');
        (new testModelWithGlobalConversionOnlyWithoutCollection)->collectionMaxSizes('logo');
    }

    /**
     * @test
     */
    public function itThrowsExceptionWhenItIsCalledWithNonExistentConversions()
    {
        $this->expectException(ConversionsNotFound::class);
        $this->expectExceptionMessage('No conversion declared in the Okipa\MedialibraryExtension\Tests\Support'
            . '\TestModels\TestModelWithCollectionWithoutConversions-model');
        (new TestModelWithCollectionWithoutConversions)->collectionMaxSizes('logo');
    }

    /**
     * @test
     */
    public function itReturnsGlobalConversionMaxSizesWhenNoCollectionConversionsDeclared()
    {
        $maxSizes = (new TestModelWithGlobalConversionOnly)->collectionMaxSizes('logo');
        $this->assertEquals(60, $maxSizes['width']);
        $this->assertEquals(20, $maxSizes['height']);
    }

    /**
     * @test
     */
    public function itReturnsOnlyWidthWhenOnlyWidthIsDeclared()
    {
        $maxSizes = (new TestModelWithGlobalConversionWithOnlyWidth)->collectionMaxSizes('logo');
        $this->assertEquals(120, $maxSizes['width']);
        $this->assertNull($maxSizes['height']);
    }

    /**
     * @test
     */
    public function itReturnsOnlyHeightWhenOnlyHeightIsDeclared()
    {
        $maxSizes = (new TestModelWithGlobalConversionWithOnlyHeight)->collectionMaxSizes('logo');
        $this->assertNull($maxSizes['width']);
        $this->assertEquals(30, $maxSizes['height']);
    }

    /**
     * @test
     */
    public function itReturnsNoSizeWhenNoneIsDeclared()
    {
        $maxSizes = (new TestModelWithGlobalConversionWithNoSize)->collectionMaxSizes('logo');
        $this->assertNull($maxSizes['width']);
        $this->assertNull($maxSizes['height']);
    }

    /**
     * @test
     */
    public function itReturnsCollectionConversionsMaxSizesWhenNoGlobalConversionsDeclared()
    {
        $maxSizes = (new TestModelWithCollectionConversionsOnly)->collectionMaxSizes('logo');
        $this->assertEquals(120, $maxSizes['width']);
        $this->assertEquals(140, $maxSizes['height']);
    }

    /**
     * @test
     */
    public function itReturnsGlobalAndCollectionConversionsMaxSizesWhenBothAreDeclared()
    {
        $maxSizes = (new TestModelWithGlobalAndCollectionConversions)->collectionMaxSizes('logo');
        $this->assertEquals(100, $maxSizes['width']);
        $this->assertEquals(80, $maxSizes['height']);
    }
}

<?php

namespace Okipa\MediaLibraryExtension\Tests\Support\TestModels;

use Illuminate\Database\Eloquent\Model;
use Okipa\MediaLibraryExtension\HasMedia\HasMedia;
use Okipa\MediaLibraryExtension\HasMedia\HasMediaTrait;

class TestModelWithoutMediaConversions extends Model implements HasMedia
{
    use HasMediaTrait;

    protected $table = 'test_models';
    protected $guarded = [];
    public $timestamps = false;
}

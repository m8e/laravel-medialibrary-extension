<?php

namespace Okipa\MediaLibraryExtension\Tests;

use File;
use Illuminate\Database\Schema\Blueprint;
use Okipa\MediaLibraryExtension\Tests\Support\TestModels\TestModel;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpDatabase($this->app);
        $this->setUpTempTestFiles();
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            \Okipa\MediaLibraryExtension\MediaLibraryExtensionServiceProvider::class,
        ];
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application $app
     *
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     */
    protected function setUpDatabase($app)
    {
        $app['db']->connection()->getSchemaBuilder()->create('test_models', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('width')->nullable();
            $table->softDeletes();
        });
        TestModel::create(['name' => 'test']);
        include_once __DIR__ . '/../vendor/spatie/laravel-medialibrary/database/migrations/create_media_table.php.stub';
        (new \CreateMediaTable())->up();
    }

    protected function initializeDirectory($directory)
    {
        if (File::isDirectory($directory)) {
            File::deleteDirectory($directory);
        }
        File::makeDirectory($directory, 0777, true, true);
    }

    protected function setUpTempTestFiles()
    {
        $this->initializeDirectory($this->getTestFilesDirectory());
        File::copyDirectory(__DIR__ . '/Support/testfiles', $this->getTestFilesDirectory());
    }

    public function getTempDirectory($suffix = '')
    {
        return __DIR__ . '/Support/temp' . ($suffix == '' ? '' : '/' . $suffix);
    }

    public function getTestFilesDirectory($suffix = '')
    {
        return $this->getTempDirectory() . '/testfiles' . ($suffix == '' ? '' : '/' . $suffix);
    }

    public function getTestJpg()
    {
        return $this->getTestFilesDirectory('test.jpg');
    }

    public function getTestPng()
    {
        return $this->getTestFilesDirectory('test.png');
    }
}

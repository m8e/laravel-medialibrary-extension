<?php

namespace Okipa\MediaLibraryExtension\Tests;

use File;
use Illuminate\Database\Schema\Blueprint;
use Okipa\MediaLibraryExtension\MediaLibraryExtensionServiceProvider;
use Okipa\MediaLibraryExtension\Tests\Support\TestModels\TestModel;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    /**
     * @return string
     */
    public function getTestJpg()
    {
        return $this->getTestFilesDirectory('test.jpg');
    }

    /**
     * @param string $suffix
     *
     * @return string
     */
    public function getTestFilesDirectory($suffix = '')
    {
        return $this->getTempDirectory() . '/testfiles' . ($suffix == '' ? '' : '/' . $suffix);
    }

    /**
     * @param string $suffix
     *
     * @return string
     */
    public function getTempDirectory($suffix = '')
    {
        return __DIR__ . '/Support/temp' . ($suffix == '' ? '' : '/' . $suffix);
    }

    /**
     * @return string
     */
    public function getTestPng()
    {
        return $this->getTestFilesDirectory('test.png');
    }

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

    /**
     *
     */
    protected function setUpTempTestFiles()
    {
        $this->initializeDirectory($this->getTestFilesDirectory());
        File::copyDirectory(__DIR__ . '/Support/testfiles', $this->getTestFilesDirectory());
    }

    /**
     * @param $directory
     */
    protected function initializeDirectory($directory)
    {
        if (File::isDirectory($directory)) {
            File::deleteDirectory($directory);
        }
        File::makeDirectory($directory, 0777, true, true);
    }

    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            MediaLibraryExtensionServiceProvider::class,
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
}

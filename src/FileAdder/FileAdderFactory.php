<?php

namespace Okipa\MediaLibraryExtension\FileAdder;

use Illuminate\Database\Eloquent\Model;

class FileAdderFactory extends \Spatie\MediaLibrary\FileAdder\FileAdderFactory
{
    /**
     * @param \Illuminate\Database\Eloquent\Model $subject
     * @param string|\Symfony\Component\HttpFoundation\File\UploadedFile $file
     *
     * @return \Spatie\MediaLibrary\FileAdder\FileAdder
     */
    public static function create(Model $subject, $file)
    {
        return app(FileAdder::class)
            ->setSubject($subject)
            ->setFile($file);
    }
}

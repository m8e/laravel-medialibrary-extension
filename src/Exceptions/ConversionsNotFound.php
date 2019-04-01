<?php

namespace Okipa\MediaLibraryExtension\Exceptions;

use Exception;
use Illuminate\Database\Eloquent\Model;

class ConversionsNotFound extends Exception
{
    /**
     * @param \Illuminate\Database\Eloquent\Model $model
     *
     * @return \Okipa\MediaLibraryExtension\Exceptions\ConversionsNotFound
     */
    public static function noneDeclaredInModel(Model $model)
    {
        $modelClass = get_class($model);

        return new static("No conversion declared in the {$modelClass}-model");
    }
}

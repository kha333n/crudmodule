<?php

namespace Traits;


use Illuminate\Support\Facades\App;

trait Crudable
{
    public static function repository()
    {
        return App::makeWith('Repositories\CrudRepository', ['model' => self::class]);
    }
}

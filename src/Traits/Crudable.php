<?php

namespace kha333n\crudmodule\Traits;


use Illuminate\Support\Facades\App;
use kha333n\crudmodule\Repositories\CrudRepository;

trait Crudable
{
    public static function newRepository()
    {
        return new CrudRepository(new self());
    }

    public function repository()
    {
        return new CrudRepository($this);
    }
}

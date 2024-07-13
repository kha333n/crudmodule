<?php

namespace kha333n\crudmodule\Traits;


use kha333n\crudmodule\Repositories\CrudRepository;
use Spatie\QueryBuilder\QueryBuilder;

trait Crudable
{
    public static function newRepository()
    {
        return new CrudRepository(new self());
    }

    public function defaultQuery()
    {
        return QueryBuilder::for($this->newQuery())
            ->allowedFilters($this->filters())
            ->allowedSorts($this->sorts());
    }

    public function filters()
    {
        return $this->getFillable();
    }

    public function sorts()
    {
        return $this->getFillable();
    }

    public function repository()
    {
        return new CrudRepository($this);
    }
}

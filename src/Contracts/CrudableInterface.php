<?php

namespace kha333n\crudmodule\Contracts;

interface CrudableInterface
{
    /**
     * <h3>Define the columns that should be used in the crud.</h3>
     * @return array
     */
    public static function crudable(): array;
}

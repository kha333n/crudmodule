<?php

namespace kha333n\crudmodule\Structures;

class CrudConfiguration
{
    public bool $withTrashed = false;
    public bool $onlyTrashed = false;
    public bool $paginate = false;
    public int $perPage = 10;

    /**
     * CrudConfiguration constructor.
     * @param bool $withTrashed
     * @param bool $onlyTrashed <h4>CAUTION: Overrides `$withTrashed`</h4>
     * @param bool $paginate
     * @param int $perPage
     */
    public function __construct(
        bool $withTrashed = false,
        bool $onlyTrashed = false,
        bool $paginate = false,
        int  $perPage = 10,
    )
    {
        $this->withTrashed = $withTrashed;
        $this->onlyTrashed = $onlyTrashed;
        $this->paginate = $paginate;
        $this->perPage = $perPage < 1 ? 10 : $perPage;
    }
}

<?php

namespace kha333n\crudmodule\Traits;

use Illuminate\Database\Eloquent\Model;

trait CrudableAdapter
{
    public function canViewAny(Model $model): bool
    {
        return true;
    }

    public function canView(Model $model): bool
    {
        return true;
    }

    public function canCreate(Model $model): bool
    {
        return true;
    }

    public function canUpdate(Model $model): bool
    {
        return true;
    }

    public function canDelete(Model $model): bool
    {
        return true;
    }

    public function canForceDelete(Model $model): bool
    {
        return true;
    }

    public function canRestore(Model $model): bool
    {
        return true;
    }
}

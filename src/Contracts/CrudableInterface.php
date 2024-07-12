<?php

namespace kha333n\crudmodule\Contracts;

use Illuminate\Database\Eloquent\Model;

/**
 * <h3>Define the authorization callbacks for the crud operations. return true if not required.</h3>
 */
interface CrudableInterface
{
    /**
     * <h3>Define the columns that should be used in the crud.</h3>
     * @return array
     */
    public static function crudable(): array;


    public function canDelete(Model $model): bool;

    public function canViewAny(Model $model): bool;

    public function canView(Model $model): bool;

    public function canCreate(Model $model): bool;

    public function canUpdate(Model $model): bool;

    public function canForceDelete(Model $model): bool;

    public function canRestore(Model $model): bool;
}

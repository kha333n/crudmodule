<?php

namespace kha333n\crudmodule\Repositories;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\UnauthorizedException;
use Illuminate\Validation\ValidationException;
use kha333n\crudmodule\Structures\CrudConfiguration;

class CrudRepository
{
    protected $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function find($id, $columns = ['*'], CrudConfiguration $configuration = null)
    {
        $model = $this->model;
        if ($configuration) {
            if ($configuration->withTrashed && $this->usesSoftDeletes()) {
                $model = $model->withTrashed();
            }
        }
        if (is_array($id) || $id instanceof Arrayable) {
            return $model->findMany($id, $columns);
        }

        return $model->whereKey($id)->first($columns);
    }

    public function all(CrudConfiguration $configuration = null)
    {
        $this->authorize('viewAny');
        $query = $this->model->defaultQuery();
        if ($configuration) {
            if ($this->usesSoftDeletes()) {
                if ($configuration->withTrashed && !$configuration->onlyTrashed) $query = $query->withTrashed();
                if ($configuration->onlyTrashed) $query = $query->onlyTrashed();
            }

            if ($configuration->paginate) {
                return $query->paginate($configuration->perPage);
            }
        }
        return $query->get();
    }

    protected function authorize($action, $model = null)
    {
        $model = $model ?: $this->model;

        $funtion = 'can' . ucfirst($action);

        if (!$model->$funtion($model)) {
            throw new UnauthorizedException("Unauthorized action by custom method: {$action}");
        }
    }

    public function usesSoftDeletes(): bool
    {
        return in_array(SoftDeletes::class, class_uses_recursive($this->model));
    }

    public function create(array $data)
    {


        $this->authorize('create');
        $data = $this->validateData($data);
        $instance = $this->model->create($data);
        $this->triggerEvent('Created', $instance);
        return $instance;
    }

    /**
     * Validate the data
     * @param array $data
     * @return array
     */
    protected function validateData(array $data): array
    {
        $rules = $this->getValidationRules();
        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        // strip out any data that is not in the rules
        return array_intersect_key($data, $rules);
    }

    protected function getValidationRules(): array
    {
        $model = $this->model;
        $rules = [];
        $casts = $model->getCasts();

        foreach ($model->crudable() as $field) {
            if (isset($model->rules[$field])) {
                $rules[$field] = $model->rules[$field];
                continue;
            }

            $rule = 'sometimes';

            if (isset($casts[$field])) {
                $rule .= match ($casts[$field]) {
                    'int', 'integer' => '|integer',
                    'float', 'double' => '|numeric',
                    'boolean' => '|boolean',
                    'date', 'datetime' => '|date',
                    default => '|string|max:255',
                };
            } else {
                $rule .= '|string|max:255';
            }

            $rules[$field] = $rule;
        }
        return $rules;
    }

    protected function triggerEvent($action, $model): void
    {
        $eventName = class_basename($model) . $action;
        Event::dispatch($eventName, $model);
    }

    public function update(array $data): Model
    {
        $this->authorize('update', $this->model);
        $data = $this->validateData($data);
        $this->model->update($data);
        $this->triggerEvent('Updated', $this->model);
        return $this->model;
    }


    public function delete(): bool
    {
        $this->authorize('delete', $this->model);
        $this->triggerEvent('Deleted', $this->model);
        $this->model->delete();
        return true;
    }

    public function forceDelete(): bool
    {
        $this->authorize('forceDelete', $this->model);
        $this->triggerEvent('ForceDeleted', $this->model);
        $this->model->forceDelete();
        return true;
    }

    public function restore(): bool
    {
        $this->authorize('restore', $this->model);
        $this->triggerEvent('Restored', $this->model);
        $this->model->restore();
        return true;
    }
}

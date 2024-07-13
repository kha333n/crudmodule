<?php

namespace kha333n\crudmodule\Repositories;

use App\Models\Book;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\UnauthorizedException;
use Illuminate\Validation\ValidationException;
use kha333n\crudmodule\Exceptions\ModelCannotBeLocked;
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

    public function usesSoftDeletes(): bool
    {
        return in_array(SoftDeletes::class, class_uses_recursive($this->model));
    }

    public function setModel(Model $model)
    {
        $this->model = $model;
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
            throw new UnauthorizedException();
        }
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

    /**
     * @throws LockTimeoutException
     */
    public function update(array $data): Model
    {
        $this->authorize('update', $this->model);
        $this->lock($this->model->getTable() . ':' . $this->model->getKey(), function () use ($data) {
            $data = $this->validateData($data);
            $this->model->update($data);
            $this->triggerEvent('Updated', $this->model);
        });
        return $this->model;
    }

    protected function lock(string $key, callable $callback)
    {
        $lock = Cache::lock($key, 10);

        if ($lock->get()) {
            try {
                return $callback();
            } finally {
                $lock->release();
            }
        } else {
            throw new LockTimeoutException();
        }
    }

    /**
     * @throws LockTimeoutException
     */
    public function delete(): bool
    {
        $this->authorize('delete', $this->model);
        $this->lock($this->model->getTable() . ':' . $this->model->getKey(), function () {
            $this->model->delete();
            $this->triggerEvent('Deleted', $this->model);
        });
        return true;
    }

    /**
     * @throws LockTimeoutException
     */
    public function forceDelete(): bool
    {
        $this->authorize('forceDelete', $this->model);
        $this->lock($this->model->getTable() . ':' . $this->model->getKey(), function () {
            $this->model->forceDelete();
            $this->triggerEvent('ForceDeleted', $this->model);
        });
        return true;
    }

    /**
     * @throws LockTimeoutException
     */
    public function restore(): bool
    {
        $this->authorize('restore', $this->model);
        $this->lock($this->model->getTable() . ':' . $this->model->getKey(), function () {
            if ($this->usesSoftDeletes()) {
                $this->model->restore();
                $this->triggerEvent('Restored', $this->model);
            } else {
                abort(404);
            }
        });
        return true;
    }
}

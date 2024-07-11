<?php

namespace kha333n\crudmodule\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use kha333n\crudmodule\Exceptions\ModalCannotBeDeleted;

class CrudRepository
{
    protected $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function all()
    {
        return $this->model->all();
    }

    public function create(array $data)
    {
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
        $data = $this->validateData($data);
        $this->model->update($data);
        $this->triggerEvent('Updated', $this->model);
        return $this->model;
    }

    /**
     * <h3>For better control define a canDelete method in your model</h3>
     * @throws ModalCannotBeDeleted
     */
    public function delete(): bool
    {
        if (method_exists($this->model, 'canDelete')) {
            if ($this->model->canDelete()) {
                return $this->deleteAndTriggerEvent($this->model);
            }
            throw new ModalCannotBeDeleted('Model cannot be deleted');
        }
        return $this->deleteAndTriggerEvent();
    }

    private function deleteAndTriggerEvent()
    {
        $this->triggerEvent('Deleted', $this->model);
        $this->model->delete();
        return true;
    }

    public function forceDelete(): bool
    {
        $this->triggerEvent('ForceDeleted', $this->model);
        $this->model->forceDelete();
        return true;
    }
}

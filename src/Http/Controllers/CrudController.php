<?php

namespace kha333n\crudmodule\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use kha333n\crudmodule\Repositories\CrudRepository;
use kha333n\crudmodule\Structures\CrudConfiguration;

class CrudController extends Controller
{
    protected $repository;
    protected $modelRootNamespace = 'App\\Models\\';

    public function __construct(Request $request)
    {
        //if in terminal skip all checking
        if (php_sapi_name() === 'cli') {
            return;
        }

        $model = $this->resolveModel($request->route('model'));
        $this->repository = new CrudRepository(new $model());
    }

    protected function resolveModel($modelName)
    {
        $modelMappings = config('crudmodule.model_route_mappings');
        if (isset($modelMappings[$modelName])) {
            $modelClass = $modelMappings[$modelName];
            if (class_exists($modelClass)) {
                return $modelClass;
            }
        }

        $modelName = $this->convertToSingular($modelName);
        $modelName = str_replace('/', '\\', ucfirst($modelName)); // Convert to namespace format
        $modelClass = $this->modelRootNamespace . $modelName;

        if (class_exists($modelClass)) {
            return $modelClass;
        }

        abort(404);
    }

    protected function convertToSingular($modelName)
    {
        if ($modelName) {
            $singularModelName = Str::singular($modelName);
            $pluralModelName = Str::plural($singularModelName);

            // If the model name is already singular, return 404
            if ($modelName === $singularModelName && $modelName !== $pluralModelName) {
                abort(404);
            }

            // Ensure the plural form matches the original model name
            if ($pluralModelName === $modelName) {
                return $singularModelName;
            }
        }
        return $modelName;
    }

    public function index(Request $request)
    {
        $response = $this->repository->all(configuration: $this->setupConfiguration($request));
        return response()->json($response->appends($request->query()));
    }

    protected function setupConfiguration(Request $request): CrudConfiguration
    {
        $configuration = new CrudConfiguration();
        if ($request->query('withTrashed')) {
            match ($request->query('withTrashed')) {
                'true', '1', 1 => $configuration->withTrashed = true,
                default => $configuration->withTrashed = false,
            };
        }
        if ($request->has('onlyTrashed')) {
            match ($request->query('onlyTrashed')) {
                'true', '1', 1 => $configuration->onlyTrashed = true,
                default => $configuration->onlyTrashed = false,
            };
        }
        if ($request->has('paginate')) {
            match ($request->query('paginate')) {
                'true', '1', 1 => $configuration->paginate = true,
                default => $configuration->paginate = false,
            };
        }
        if ($request->has('perPage')) {
            $configuration->perPage = $request->perPage;
        }
        return $configuration;
    }

    public function store(Request $request)
    {
        $data = $request->all();
        $instance = $this->repository->create($data);
        return response()->json($instance, 201);
    }

    public function show($model, $id, Request $request)
    {
        $instance = $this->repository->find($id, configuration: $this->setupConfiguration($request));
        if ($instance) {
            return response()->json($instance);
        } else {
            abort(404);
        }
    }

    public function update($model, $id, Request $request)
    {
        $data = $request->all();
        $configuration = $this->setupConfiguration($request);
        $instance = $this->repository->find($id, configuration: $configuration);
        if ($instance) {
            $this->repository->setModel($instance);
        } else {
            abort(404);
        }
        $updatedInstance = $this->repository->update($data);
        return response()->json($updatedInstance);
    }

    public function destroy($model, $id)
    {
        $instance = $this->repository->find($id);
        if ($instance) {
            $this->repository->setModel($instance);
        } else {
            abort(404);
        }
        $this->repository->delete();
        return response()->json(null, 204);
    }

    public function forceDestroy($model, $id)
    {
        $instance = $this->repository->find($id, configuration: new CrudConfiguration(withTrashed: true));
        if ($instance) {
            $this->repository->setModel($instance);
        } else {
            abort(404);
        }
        $this->repository->forceDelete();
        return response()->json(null, 204);
    }

    public function restore($model, $id)
    {
        $instance = $this->repository->find($id, configuration: new CrudConfiguration(withTrashed: true));
        if ($instance) {
            $this->repository->setModel($instance);
        } else {
            abort(404);
        }
        $this->repository->restore($instance);
        return response()->json($instance);
    }
}

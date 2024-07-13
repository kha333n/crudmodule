# Laravel CRUD Module

This is a simple CRUD module for Laravel. It is a simple module that can be used to create, read, update, and delete
records in a database.

It will help you implement basic CRUD operations in Laravel with faster speed and better efficiency.

## Don't waste your time on repetitive basic tasks

#### It is extensible and customizable to fit your needs

## Features

- Create, Read, Update, and Delete records in a database
- Trigger events on each CRUD operation to perform additional tasks if any required
- Easier to implement validation and authorization rules

## Installation

You can install the package via composer:

```bash
composer require kha333n/crudmodule
```

## Usage

Add `Crudable` trait and implement `CrudableInterface` in your model.
And Implement required `crudable` methods in your model.

```php
use Kha333n\Crudable\Crudable;

class YourModel extends Model implements CrudableInterface
{
    use Crudable;
    
    public function crudable(): array {
        return [
            'column1',
            'column2',
            '...'
        ];
    }
}
```

Then use in your controller

```php
// Create new record
$model = YourModel::getRepository()->create($request->all());

// Update record
$model->repository()->update($request->all());

// Delete record
$model->repository()->delete();

// Get all records
$models = YourModel::getRepository()->all();
//OR
$models = $model->repository()->all();

// force delete record
$model->repository()->forceDelete();
```

## Adding validation rules

In your model, you can add validation rules for each column.

If not provided, it will use default validation rules based on its cast.

If both not defined it will treat it as a string.

Default Rules:

- `string: sometimes|string|max:255`
- `integer: sometimes|integer`
- `float: sometimes|numeric`
- `boolean: sometimes|boolean`
- `date: sometimes|date`

```php
// in YourModel.php add array

    public array $rules = [
        'column1' => 'required|string|min:3|max:255',
        'column2' => 'required|string',
        '...' => 'required|string',
    ];

```

## Adding Authorization rules

In your model, you can add authorization rule for each column

implement `CrudableInterface` in your model

Implement all required methods in your model and in each method, add your authorization logic

Available method:

* `canViewAny(Model $model)`
* `canView(Model $model)`
* `canCreate(Model $model)`
* `canUpdate(Model $model)`
* `canDelete(Model $model)`
* `canForceDelete(Model $model)`
* `canRestore(Model $model)`

In case you require only a few of them.
Use `CrudableAdapter` trait in your model and then only add required ones.
All others will be allowed by default.

Example:

```php
// Only can create implemented
class Book implements \kha333n\crudmodule\Contracts\CrudableInterface {
    use \kha333n\crudmodule\Traits\CrudableAdapter;
    use \kha333n\crudmodule\Traits\Crudable;
    
    public function crudable(): array {
        return [
            'column1',
            'column2',
            '...'
        ];
    }
    
    public function canCreate(\Illuminate\Database\Eloquent\Model $model): bool {
        return auth()->user()->hasRole('Author');
    }
}
```

## Filtering & Sorting

For filtering and sorting we are
using [Spatie Laravel Query Builder](https://spatie.be/docs/laravel-query-builder/v5/introduction) package.

By default, it will allow all fillable columns for both filtering and sorting

If you want to modify them or add own filtering rules, implement filters OR sorts method in your model

```php
// See spatie query builder package for rules
    public function filters()
    {
        return $this->getFillable();
    }

    public function sorts()
    {
        return $this->getFillable();
    }
```

## Listing Customization

By default `all` function returns all records in model. But you can customize them
by passing `CurdConfiguration` class to it with custom configuration.

#### Configurations Available

* `withTrashed = false`
* `onlyTrashed = false`
* `paginate = false`
* `perPage = 10`

Example:

```php
YourModel::getRepository()->all(new \kha333n\crudmodule\Structures\CrudConfiguration(
    withTrashed: true,
    onlyTrashed: true, // When onlyTrashed is true, it overrides, the withTrashed and only deleted record will show
    paginate: true,
    perPage: 23
))
```


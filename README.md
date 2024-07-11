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


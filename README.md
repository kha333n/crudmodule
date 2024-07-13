# Laravel CRUD Module

This is a simple CRUD module for Laravel. It is a simple module that can be used to create, read, update, and delete
records in a database.

It will help you implement basic CRUD operations in Laravel with faster speed and better efficiency.

## Don't waste your time on repetitive basic tasks

#### It is extensible and customizable to fit your needs

## Features

- Create, Read, Update, and Delete records in a database
- Easier to implement validation and authorization rules
- Filtering and Sorting records
- Customizing listing records
- Extending functionality using Observers and Events
- Out-of-the-box API support

## Index

- [Installation](#installation)
- [Usage](#usage)
- [Using via API routes](#using-via-api-routes)
- [Using via Controller](#using-via-controller)
- [Adding validation rules](#adding-validation-rules)
- [Adding Authorization rules](#adding-authorization-rules)
- [Filtering & Sorting](#filtering--sorting)
- [Listing Customization](#listing-customization)
- [Extending Functionality](#extending-functionality)
- [License](#license)
- [Contributing](#contributing)
- [About kha333n](#about-kha333n)

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

## Using via API routes

For most of the usage you can use it via built-in API routes
They provide full CRUD operations

API Routes Available:

* GET `/{model}` - List all records
* GET `/{model}/{id}` - Get single record
* POST `/{model}` - Create new record
* PUT `/{model}/{id}` - Update record
* DELETE `/{model}/{id}` - Delete record
* DELETE `/{model}/{id}/force` - Force delete record
* PATCH `/{model}/{id}` - Restore record

Example: Using `Book` model

* GET `/api/book`
* GET `/api/book/1`
* POST `/api/book`
* PUT `/api/book/1`
* DELETE `/api/book/1`
* DELETE `/api/book/1/force`
* PATCH `/api/book/1`

## Listing Customizations

You can customize listing records by passing query parameters in url

Available query parameters:

* `withTrashed = true` OR `withTrashed = 1` - Include soft deleted records
* `onlyTrashed = true` OR `onlyTrashed = 1` - Only show soft deleted records **(overrides withTrashed)**
* `paginate = true` OR `paginate = 1` - Paginate records
* `perPage = 10` - Number of records per page

Example: Using `Book` model

### GET `/api/book?withTrashed=true&paginate=true&perPage=23`

## Filtering & Sorting

You can filter and sort records bypassing query parameters in url
We are using [Spatie Laravel Query Builder](https://spatie.be/docs/laravel-query-builder/v5/introduction) for this see
Spatie guide for more details

Example: Using `Book` model

### GET `/api/book?filter[title]=abc&sort=created_at`

## Using via Controller

In case you want greater control over CRUD or want customized application flow, you can directly
interact with the model repository.

```php
// Create new record
$model = Book::getRepository()->create($request->all());

// Update record
$model->repository()->update($request->all());

// Delete record
$model->repository()->delete();

// Get all records
$models = Book::getRepository()->all();
//OR
$models = $model->repository()->all();

// force delete record
$model->repository()->forceDelete();
```

## Adding validation rules

In your model, you can add validation rules for each column.

If not provided, it will use default validation rules based on its cast.

If both are not defined, it will treat it as a string.

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

## Extending Functionality

Functionality of CRUD can be extended by 2 methods

1. Using Observers
   If you need to add additional data, modify or change data while some operation is on going
   use Observers on your model. Like while creating a book via CRUD you want to add a generated
   book_slug to it. You can use observer for it.
2. Using Events
   If you need to perform some additional tasks after some operation is done. Like sending an email
   after creating a book. You can use events for it.
   Events available:
   * `Created`
   * `Updated`
   * `Deleted`
   * `ForceDeleted`
   * `Restored`

Example: On `Book` model register event listeners in `EventsServiceProvider` like this

```php
    protected $listen = [
        'BookCreated' => [
            BookCreatedListener::class,
        ],
        'BookUpdated' => [
            BookUpdatedListener::class,
        ],
        'BookDeleted' => [
            BookDeletedListener::class,
        ],
        'BookForceDeleted' => [
            BookForceDeletedListener::class,
        ],
        'BookRestored' => [
            BookRestoredListener::class,
        ],
    ];
```

```php
// BookCreatedListener.php
class BookCreatedListener
{
    public function handle(object $event)
    {
        // $event will contain the model instance on which operation performed
        // Perform your task here
    }
}
```

## License

MIT License. Please see the [License File](LICENSE.md) for more information.

## Contributing

Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## About kha333n

[kha333n](https://kha333n.com) is a web developer and open-source contributor.


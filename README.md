# action-crud-helper

Generate everything needed for a CRUD operation in Laravel with or without Action classes executing just one command.
Also generates frontend scaffolding for blade or vue inertia stack.

## Installation

- Install the package via [composer](https://getcomposer.org/):

```
composer require kamrul-haque/action-crud-helper --dev
```

- Publish stubs for files generated:

```
php artisan vendor:publish --tag="action-crud-helper-stubs"
```

## Usage

- To generate `CRUD` helpers:

```
php artisan make:crud ModelName --stack --all
``` 

*supported stacks are api, blade or inertia (inertia vue). --all option will generate everything required for a CRUD
operation along with Action classes. Alternatively, run the command without stack & all options to select files to
generate interactively.*

- To generate only `Action` class:

```
php artisan make:action ActionName
``` 

- To customize the `files` generated, customize the stubs files located in `stubs` folder:
- To publish the tests:

```
php artisan vendor:publish --tag="action-crud-helper-tests"
```

*Note: the tests for the package only support PestPHP currently.*

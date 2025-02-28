# action-crud-helper
Generate everything needed for a CRUD operation in Laravel with or without Action classes executing just one command. Also generates frontend scaffolding for blade or vue inertia stack.

## Installation

Install the package via [composer](https://getcomposer.org/):
```
composer require kamrul-haque/action-crud-helper
```

## Usage

- To generate *CRUD* helpers:
```
php artisan make:crud ModelName --stack --all
``` 
*supported stacks are api, blade or inertia (inertia vue). --all option will generate everything required for a CRUD operation along with Action classes. Alternatively, run the command without stack & all options to select files to generate interactively.*
- To generate only *action* class:
```
php artisan make:action ActionName
``` 
- To customize the *files* generated, publish package stubs:
```
php artisan vendor:publish --tag="action-crud-stubs"
```
- To publish the tests:
```
php artisan vendor:publish --tag="action-crud-tests"
```
*Note: the tests for the package only support PestPHP currently*

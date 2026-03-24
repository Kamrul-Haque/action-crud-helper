<?php

use Illuminate\Support\Facades\File;

beforeEach(function () {
    $this->dto = Str::studly(trim(fake()->unique()->slug(2)));
    $this->assertFileDoesNotExist(app_path("DTOs/{$this->dto}Data.php"));
});

test('make:dto command works without class name prompt', function () {
    $this->artisan("make:dto {$this->dto}Data")
        ->assertExitCode(0);

    $this->assertFileExists(app_path("DTOs/{$this->dto}Data.php"));
});

test('make:dto command works with class name prompt', function () {
    $this->artisan('make:dto')
        ->expectsQuestion('Please enter the class name', "{$this->dto}Data")
        ->assertExitCode(0);

    $this->assertFileExists(app_path("DTOs/{$this->dto}Data.php"));
});

test('make:dto command works with name prefix', function () {
    $this->artisan("make:dto Api/{$this->dto}Data")
        ->assertExitCode(0);

    $this->assertFileExists(app_path("DTOs/Api/{$this->dto}Data.php"));
});

afterEach(function () {
    if (File::exists(app_path("DTOs/{$this->dto}Data.php"))) {
        File::delete(app_path("DTOs/{$this->dto}Data.php"));
    }

    if (File::exists(app_path("DTOs/Api/{$this->dto}Data.php"))) {
        File::delete(app_path("DTOs/Api/{$this->dto}Data.php"));
    }

    if (File::isEmptyDirectory(app_path('DTOs'))) {
        File::deleteDirectory(app_path('DTOs'));
    }
});

<?php

namespace KamrulHaque\ActionCrudHelper\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Console\PromptsForMissingInput;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\File;
use Str;

class MakeDto extends Command implements PromptsForMissingInput
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:dto
                            {name : The name of the class}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a DTO class in app/DTOs directory';

    /**
     * Execute the console command.
     *
     * @throws FileNotFoundException
     */
    public function handle(): int
    {
        $name = $this->argument('name');
        $hasPrefix = Str::contains($name, '/');
        $className = Str::studly($hasPrefix ? Str::afterLast($name, '/') : $name);
        $directoryPrefix = $hasPrefix ? Str::beforeLast($name, '/') : '';
        $stubPath = base_path('stubs/dto.stub');
        $namespace = "App\DTOs".($directoryPrefix ? '\\'.$directoryPrefix : '');
        $directoryPath = app_path('DTOs/'.$directoryPrefix);
        $targetPath = "{$directoryPath}/{$className}.php";

        if (! File::exists($targetPath)) {
            $this->ensureTheDirectoryExists($directoryPath);

            $this->generateClass($stubPath, $className, $namespace, $targetPath);

            $this->components->info("DTO class [$targetPath] created successfully.");
        } else {
            $this->components->error("DTO class [$targetPath] already exists.");
        }

        return 0;
    }

    /**
     * Ensure the directory exists
     *
     * @param  string  $directoryPath  Path of the directory
     */
    private function ensureTheDirectoryExists(string $directoryPath): void
    {
        if (! File::exists($directoryPath)) {
            File::makeDirectory($directoryPath, 0755, true);
        }
    }

    /**
     * Generate the DTO class
     *
     * @param  string  $stubPath  path of the class stub
     * @param  string  $className  name of the DTO class
     * @param  string  $namespace  namespace of the class
     * @param  string  $targetPath  path to create the class
     *
     * @throws FileNotFoundException
     */
    private function generateClass(string $stubPath, string $className, string $namespace, string $targetPath): void
    {
        // Get the stub template
        $stub = File::get($stubPath);

        // Replace placeholders with actual values
        $stub = str_replace(['{{ className }}', '{{ namespace }}'],
            [$className, $namespace],
            $stub);

        // Save the generated file
        File::put($targetPath, $stub);
    }

    /**
     * Prompt for missing input arguments using the returned questions.
     *
     * @return array<string, string>
     */
    protected function promptForMissingArgumentsUsing(): array
    {
        return [
            'name' => 'Please enter the class name',
        ];
    }
}

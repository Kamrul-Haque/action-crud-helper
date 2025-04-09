<?php

namespace KamrulHaque\ActionCrudHelper\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Console\PromptsForMissingInput;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\File;
use Str;

class MakeAction extends Command implements PromptsForMissingInput
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:action
                            {name : The name of the class}
                            {--api : Make the class inside app/Actions/Api directory}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates an action class in app/Actions directory';

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
        $stubPath = base_path('stubs/action.stub');
        $namespace = $this->option('api')
            ? "App\Actions\Api" . ($directoryPrefix ? '\\' . $directoryPrefix : '')
            : "App\Actions" . ($directoryPrefix ? '\\' . $directoryPrefix : '');
        $directoryPath = $this->option('api')
            ? app_path('Actions/Api/' . $directoryPrefix)
            : app_path('Actions/' . $directoryPrefix);
        $targetPath = "{$directoryPath}/{$className}.php";

        if (!File::exists($targetPath)) {
            $this->ensureTheBaseActionClassExists();

            $this->ensureTheDirectoryExists($directoryPath);

            $this->generateClass($stubPath, $className, $namespace, $targetPath);

            $this->components->info("Action class [$targetPath] created successfully.");
        } else {
            $this->components->error("Action class [$targetPath] already exists.");
        }

        return 0;
    }

    /**
     * Ensure the BaseAction class exists
     *
     * @throws FileNotFoundException
     */
    private function ensureTheBaseActionClassExists(): void
    {
        if (!File::exists(app_path('Actions'))) {
            File::makeDirectory(app_path('Actions'));
        }

        if (!File::exists('app/Actions/BaseAction.php')) {
            $baseActionStub = File::get(base_path('stubs/base.action.stub'));

            File::put('app/Actions/BaseAction.php', $baseActionStub);
        }
    }

    /**
     * Ensure the directory exists
     *
     * @param string $directoryPath Path of the directory
     */
    private function ensureTheDirectoryExists(string $directoryPath): void
    {
        if (!File::exists($directoryPath)) {
            File::makeDirectory($directoryPath, 0755, true);
        }
    }

    /**
     * Generate the Action class
     *
     * @param string $stubPath path of the class stub
     * @param string $className name of the action class
     * @param string $namespace namespace of the class
     * @param string $targetPath path to create the class
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

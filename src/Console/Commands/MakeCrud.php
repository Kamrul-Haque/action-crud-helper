<?php

namespace KamrulHaque\ActionCrudHelper\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Console\PromptsForMissingInput;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use PhpSchool\CliMenu\Builder\CliMenuBuilder;
use PhpSchool\CliMenu\CliMenu;
use PhpSchool\CliMenu\Exception\InvalidTerminalException;
use PhpSchool\CliMenu\Style\CheckboxStyle;

class MakeCrud extends Command implements PromptsForMissingInput
{
    protected array $actions = ['index', 'create', 'store', 'show', 'edit', 'update', 'destroy'];

    protected array $apiActions = ['index', 'store', 'show', 'update', 'destroy'];

    protected array $classes = [
        'Model & Migration',
        'Resource Controller',
        'Form Request',
        'API Resource',
        'Seeder',
        'Test',
        'Action',
        'Resource Route',
        'Views',
        'All'
    ];

    protected array $selectedClasses = [];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:crud
                            {model : The name of the model}
                            {--api : Generate classes needed for API development without frontend scaffolding}
                            {--blade : Generate classes needed for full-stack development with blade frontend scaffolding}
                            {--inertia : Generate classes needed for full-stack development with vue inertia frontend scaffolding}
                            {--all : Generate all the CRUD classes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate classes required for entity CRUD operation';

    /**
     * Execute the console command.
     *
     * @throws InvalidTerminalException
     * @throws FileNotFoundException
     */
    public function handle(): int
    {
        $option = $this->parseOption();

        if (!$this->option('all')) {
            $this->selectClasses($option);
        } else {
            $this->selectedClasses[] = 'All';
        }

        $model = Str::studly($this->argument('model'));
        $actions = $option === 'Api' ? $this->apiActions : $this->actions;
        $apiPrefix = $option === 'Api' ? 'Api/' : null;
        $generateAll = in_array($this->classes[9], $this->selectedClasses);

        if ($generateAll || in_array($this->classes[0], $this->selectedClasses)) {
            $this->createModelMigration($model);
        }

        if ($generateAll || in_array($this->classes[1], $this->selectedClasses)) {
            $this->createController($model, $apiPrefix);
        }

        if ($generateAll || in_array($this->classes[2], $this->selectedClasses)) {
            $this->createRequest($model, $apiPrefix);
        }

        if ($apiPrefix == 'Api/') {
            if ($generateAll || in_array($this->classes[3], $this->selectedClasses)) {
                $this->createResource($model);
            }
        }

        if ($generateAll || in_array($this->classes[4], $this->selectedClasses)) {
            $this->createSeeder($model);
        }

        if ($generateAll || in_array($this->classes[5], $this->selectedClasses)) {
            $this->createTest($model, $apiPrefix);
        }

        if ($generateAll || in_array($this->classes[6], $this->selectedClasses)) {
            $this->createActions($actions, $model, $apiPrefix);
        }

        if ($generateAll || in_array($this->classes[7], $this->selectedClasses)) {
            $this->createRoute($model, $apiPrefix);
        }

        if (!$apiPrefix) {
            if ($generateAll || in_array($this->classes[8], $this->selectedClasses)) {
                $this->createViews($model, $option);
            }
        }

        if (empty($this->selectedClasses)) {
            $this->components->error('Aborted or Empty Selection.');
        } else {
            $this->components->info('Required units generated successfully.');
        }

        return 0;
    }

    /**
     * Processes the option argument passed
     *
     * @return string|null the option selected
     */
    private function parseOption(): ?string
    {
        $option = null;

        if ($this->option('api')) {
            $option = 'Api';
        }

        if ($this->option('blade')) {
            $option = 'Blade';
        }

        if ($this->option('inertia')) {
            $option = 'Inertia';
        }

        if (!$option) {
            $option = $this->choice(
                'Choose Option',
                ['Api', 'Blade', 'Inertia'],
                0
            );
        }

        return $option;
    }

    /**
     * Select Classes to Generate
     *
     * @param string $option the option selected
     *
     * @throws InvalidTerminalException
     */
    private function selectClasses(string $option): void
    {
        $classSelection = $this->choice(
            'Units to Generate',
            ['All', 'Select Units'],
            0
        );

        if ($classSelection === 'All') {
            $this->selectedClasses[] = 'All';

            return;
        }

        $menuBuilder = (new CliMenuBuilder)
            ->modifyCheckboxStyle(function (CheckboxStyle $style) {
                $style->setUncheckedMarker('[ ] ')
                      ->setCheckedMarker('[●] ');
            })
            ->setTitle('Select Units to Generate (ARROW to navigate, SPACE/ENTER to toggle, EXIT to confirm)')
            ->setWidth(60)
            ->setBackgroundColour('default')
            ->setForegroundColour('default');

        $selectableClasses = $option === 'Api' ? Arr::except($this->classes, [8, 9]) :
            Arr::except($this->classes, [3, 9]);

        // Add checkbox items to the menu
        foreach ($selectableClasses as $class) {
            $menuBuilder->addCheckboxItem($class, function (CliMenu $menu) use ($class) {
                if (in_array($class, $this->selectedClasses)) {
                    $this->selectedClasses = array_diff($this->selectedClasses, [$class]); // Uncheck
                } else {
                    $this->selectedClasses[] = $class;
                }
            });
        }

        $menu = $menuBuilder->build();
        $menu->open();
    }

    /**
     * Create Model Class
     *
     * @param string $model the model name
     */
    private function createModelMigration(string $model): void
    {
        $this->call('make:model', [
            'name' => $model,
            '--migration' => true,
        ]);
    }

    /**
     * Create Resourceful Controller
     *
     * @param string $model the model name
     * @param string|null $apiPrefix prefix for API option
     */
    private function createController(string $model, ?string $apiPrefix): void
    {
        $this->call('make:controller', [
            'name' => $apiPrefix . $model . 'Controller',
            '--model' => $model,
            '--resource' => true,
            '--api' => (bool)$apiPrefix,
        ]);
    }

    /**
     * Create Form Request Class
     *
     * @param string $model the model name
     * @param string|null $apiPrefix prefix for API option
     */
    private function createRequest(string $model, ?string $apiPrefix): void
    {
        $this->call('make:request', [
            'name' => $apiPrefix . $model . 'Request',
        ]);
    }

    /**
     * Create API Resource Class
     *
     * @param string $model the model name
     */
    private function createResource(string $model): void
    {
        $this->call('make:resource', [
            'name' => $model . 'Resource',
        ]);
    }

    /**
     * Create Seeder Class
     *
     * @param string $model the model name
     */
    private function createSeeder(string $model): void
    {
        $this->call('make:seeder', [
            'name' => $model . 'Seeder',
        ]);
    }

    /**
     * Create Feature Test Class
     *
     * @param string $model the model name
     * @param string|null $apiPrefix prefix for API option
     */
    private function createTest(string $model, ?string $apiPrefix): void
    {
        $this->call('make:test', [
            'name' => $apiPrefix . $model . 'Test',
        ]);
    }

    /**
     * Create Action Classes
     *
     * @param array $actions list of actions
     * @param string $model the model name
     * @param string|null $apiPrefix prefix for API option
     */
    private function createActions(array $actions, string $model, ?string $apiPrefix): void
    {
        foreach ($actions as $action) {
            $this->call('make:action', [
                'name' => $model . 'Actions/' . $action . $model . 'Action',
                '--api' => (bool)$apiPrefix,
            ]);
        }
    }

    /**
     * Create Resource Route
     *
     * @param string $model the model name
     * @param string|null $apiPrefix prefix for API option
     */
    private function createRoute(string $model, ?string $apiPrefix): void
    {
        $uri = Str::plural(Str::slug(Str::snake($model)));
        $prefix = $apiPrefix ? rtrim($apiPrefix, '/') . '\\' : '';
        $route = "\nRoute::resource('{$uri}', App\Http\Controllers\\{$prefix}{$model}Controller::class);";
        $filePath = $apiPrefix ? base_path('routes/api.php') : base_path('routes/web.php');

        if ($apiPrefix) {
            if (!File::exists(base_path('routes/api.php'))) {
                $this->call('install:api');
            }
        }

        if (
            str_contains(
                file_get_contents($filePath),
                "Route::resource('{$uri}', App\Http\Controllers\\{$prefix}{$model}Controller::class);"
            )
        ) {
            $this->components->error('Route already exists.');

            return;
        }

        file_put_contents($filePath, $route, FILE_APPEND);

        $this->components->info('Route generated successfully.');
    }

    /**
     * Create Views
     *
     * @param string $model the model name
     * @param string $option the option selected
     *
     * @throws FileNotFoundException
     */
    private function createViews(string $model, string $option): void
    {
        $slug = Str::plural(Str::slug(Str::snake($model)));
        $variable = Str::camel($model);

        if ($option == 'Blade') {
            $this->createBladeFiles($model, $slug, $variable);
        }

        if ($option == 'Inertia') {
            $this->createVueFiles($model, $slug, $variable);
        }
    }

    /**
     * Create blade frontend scaffolding
     *
     * @param string $model the model name
     * @param string $slug slug from model name
     * @param string $variable camel case variable name from model name
     *
     * @throws FileNotFoundException
     */
    private function createBladeFiles(string $model, string $slug, string $variable): void
    {
        if (!File::exists(base_path('resources/views/layouts/app.blade.php'))) {
            $appComponentStub = File::get(base_path('stubs/AppLayout.php.stub'));
            $appLayoutStub = File::get(base_path('stubs/app.blade.php.stub'));
            $navigationStub = File::get(base_path('stubs/navigation.blade.php.stub'));

            if (!File::exists(base_path('resources/views/layouts'))) {
                File::makeDirectory(base_path('resources/views/layouts'), 0755, true);
            }

            if (!File::exists(app_path('View/Components'))) {
                File::makeDirectory(app_path('View/Components'), 0755, true);
            }

            File::put(app_path('View/Components/AppLayout.php'), $appComponentStub);
            File::put(base_path('resources/views/layouts/navigation.blade.php'), $navigationStub);
            File::put(base_path('resources/views/layouts/app.blade.php'), $appLayoutStub);
        }

        // get file contents from stubs
        $indexStub = File::get(base_path('stubs/index.blade.php.stub'));
        $createStub = File::get(base_path('stubs/create.blade.php.stub'));
        $showStub = File::get(base_path('stubs/show.blade.php.stub'));
        $editStub = File::get(base_path('stubs/edit.blade.php.stub'));

        // replace variables in stubs
        $indexStub = Str::replace(['{{ model }}', '{{ uri }}', '{{ variable }}'],
            [$model, $slug, $variable],
            $indexStub);
        $createStub = Str::replace(['{{ model }}', '{{ uri }}'], [$model, $slug], $createStub);
        $showStub = Str::replace('{{ model }}', $model, $showStub);
        $editStub = Str::replace(['{{ model }}', '{{ uri }}', '{{ variable }}'], [$model, $slug, $variable], $editStub);

        // create directory for the model
        if (!File::exists(base_path("resources/views/{$slug}"))) {
            File::makeDirectory(base_path("resources/views/{$slug}"), 0755, true);
        }

        // create files with dynamic content
        if (!File::exists(base_path("resources/views/{$slug}/index.blade.php"))) {
            File::put(base_path("resources/views/{$slug}/index.blade.php"), $indexStub);

            $this->components->info("File [resources/views/{$slug}/index.blade.php] created successfully.");
        } else {
            $this->components->error("File [resources/views/{$slug}/index.blade.php] already exists.");
        }

        if (!File::exists(base_path("resources/views/{$slug}/create.blade.php"))) {
            File::put(base_path("resources/views/{$slug}/create.blade.php"), $createStub);

            $this->components->info("File [resources/views/{$slug}/create.blade.php] created successfully.");
        } else {
            $this->components->error("File [resources/views/{$slug}/create.blade.php] already exists.");
        }

        if (!File::exists(base_path("resources/views/{$slug}/show.blade.php"))) {
            File::put(base_path("resources/views/{$slug}/show.blade.php"), $showStub);

            $this->components->info("File [resources/views/{$slug}/show.blade.php] created successfully.");
        } else {
            $this->components->error("File [resources/views/{$slug}/show.blade.php] already exists.");
        }

        if (!File::exists(base_path("resources/views/{$slug}/edit.blade.php"))) {
            File::put(base_path("resources/views/{$slug}/edit.blade.php"), $editStub);

            $this->components->info("File [resources/views/{$slug}/edit.blade.php] created successfully.");
        } else {
            $this->components->error("File [resources/views/{$slug}/edit.blade.php] already exists.");
        }
    }

    /**
     * Create vue frontend scaffolding
     *
     * @param string $model the model name
     * @param string $slug slug from model name
     * @param string $variable camel case variable name from model name
     *
     * @throws FileNotFoundException
     */
    private function createVueFiles(string $model, string $slug, string $variable): void
    {
        if (!File::exists(base_path('resources/js/Layouts'))) {
            $appLayoutStub = File::get(base_path('stubs/AuthenticatedLayout.vue.stub'));

            File::makeDirectory(base_path('resources/js/Layouts'), 0755, true);

            File::put(base_path('resources/js/Layouts/AuthenticatedLayout.vue'), $appLayoutStub);
        }

        // create directory for the model
        if (!File::exists(base_path("resources/js/Pages/{$model}"))) {
            File::makeDirectory(base_path("resources/js/Pages/{$model}"), 0755, true);
        }

        // get file contents from stubs
        $indexStub = File::get(base_path('stubs/Index.vue.stub'));
        $createOrEditStub = File::get(base_path('stubs/CreateOrEdit.vue.stub'));
        $showStub = File::get(base_path('stubs/Show.vue.stub'));

        // replace variables in stubs
        $indexStub = Str::replace(['{{ model }}', '{{ uri }}', '{{ variable }}'],
            [$model, $slug, $variable],
            $indexStub);
        $createOrEditStub = Str::replace(['{{ model }}', '{{ uri }}', '{{ variable }}'],
            [$model, $slug, $variable],
            $createOrEditStub);
        $showStub = Str::replace(['{{ model }}', '{{ variable }}'], [$model, $variable], $showStub);

        // create files with dynamic content
        if (!File::exists(base_path("resources/js/Pages/{$model}/Index.vue"))) {
            File::put(base_path("resources/js/Pages/{$model}/Index.vue"), $indexStub);

            $this->components->info("File [resources/js/Pages/{$model}/Index.vue] created successfully.");
        } else {
            $this->components->error("File [resources/js/Pages/{$model}/Index.vue] already exists.");
        }

        if (!File::exists(base_path("resources/js/Pages/{$model}/CreateOrEdit.vue"))) {
            File::put(base_path("resources/js/Pages/{$model}/CreateOrEdit.vue"), $createOrEditStub);

            $this->components->info("File [resources/js/Pages/{$model}/CreateOrEdit.vue] created successfully.");
        } else {
            $this->components->error("File [resources/js/Pages/{$model}/CreateOrEdit.vue] already exists.");
        }

        if (!File::exists(base_path("resources/js/Pages/{$model}/Show.vue"))) {
            File::put(base_path("resources/js/Pages/{$model}/Show.vue"), $showStub);

            $this->components->info("File [resources/js/Pages/{$model}/Show.vue] created successfully.");
        } else {
            $this->components->error("File [resources/js/Pages/{$model}/Show.vue] already exists.");
        }
    }

    /**
     * Prompt for missing input arguments using the returned questions.
     *
     * @return array<string, string>
     */
    protected function promptForMissingArgumentsUsing(): array
    {
        return [
            'model' => 'Please enter the Model name',
        ];
    }
}

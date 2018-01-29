<?php

namespace RonAppleton\Commander;

use Illuminate\Support\ServiceProvider;
use File;

class ModuleServiceProvider extends ServiceProvider
{
    /**
     * The application instance.
     *
     * @var \Illuminate\Contracts\Foundation\Application
     */
    protected $app;

    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'RonAppleton\Commander\Http\Controllers';

    /**
     * Create a new service provider instance.
     *
     * @param  \Illuminate\Contracts\Foundation\Application $app
     * @return void
     */
    public function __construct($app)
    {
        parent::__construct($app);
        $this->app = $app;
    }

    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                Commands\Laravel\UserSearch::class,
                Commands\Laravel\ListUsers::class,
                Commands\Laravel\AddUser::class,

                Commands\Laratrust\AddRole::class,
                Commands\Laratrust\GiveRole::class,
                Commands\Laratrust\ListRoles::class,
            ]);
        }
    }

    public function getCommands()
    {
        $commandDirectorys = $this->getCommandDirs();

        $commands = [];

        foreach ($commandDirectorys as $commandDirectory) {
            $commands[] = $this->getClasses($commandDirectory);
        }
        $commands = array_flatten($commands);

        return $commands;
    }

    public function getCommandDirs()
    {
        $commandDirs = [];

        $dirs = File::directories(__DIR__ . '/Commands');

        foreach($dirs as $dir)
        {
            $path = explode('/', $dir);
            $commandDirs[] = $path[count($path) - 1];
        }

        return $commandDirs;
    }

    public function getClasses($directory)
    {
        $commandFiles = [];

        $files = File::files(__DIR__ . '/Commands/' . $directory);
        foreach($files as $file)
        {
            $classsName = str_replace('.php', '', $file->getFilename());
            $commandFiles[] = "Commands\\{$directory}\\{$classsName}::class";
        }

        return $commandFiles;
    }
}
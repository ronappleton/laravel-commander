<?php

namespace RonAppleton\Commander\Commands\Laratrust;

use Illuminate\Console\Command;

class AddRole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laratrust:create-role 
    {name : Name of role i.e. superadmin} 
    {display_name : Pretty name for role i.e. SuperAdmin}
    {description : The roles description}';

    protected $model;
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a laratrust role for attaching to users.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $roleModel = app()->getNamespace() . 'Role';

        $this->model = new $roleModel;
    }


    /**
     * Execute the console command.
     */
    public function handle()
    {
        $input = $this->arguments();
        unset($input['command']);

        if($this->model->where('name', $input['name'])->exists())
        {
            $this->warn("The role given ({$input['name']}) already exists");
            exit();
        }

        $role = $this->model;

        $role->name = $input['name'];
        $role->display_name = $input['display_name'];
        $role->description = $input['description'];

        $role->save() ? $this->info('Role created!') : $this->error('An error occurred and the role was not created.');
    }
}

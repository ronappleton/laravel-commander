<?php

namespace RonAppleton\Commander\Commands;

use Illuminate\Console\Command;

class GiveRole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laratrust:attach-role
    {user : Name or id of user to give role too}
    {roleName : Role to apply to user}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Attaches a Laratrust role to a given user.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $userModel = app()->getNamespace() . 'User';
        $roleModel = app()->getNamespace() . 'Role';

        if (is_numeric($this->argument('user')))
        {
            $user = $userModel::where('id', $this->argument('user'))->first();

            if(empty($user)) {
                $this->error("User with id of {$this->argument('user')} was not found.");
                return;
            }
        }
        else {
            $user = $userModel::where('name', $this->argument('user'))->first();

            if(empty($user)) {
                $this->error("User with name of \"{$this->argument('user')}\" was not found.");
                return;
            }
        }

        $role = $roleModel::where('name', $this->argument('roleName'))->first();

        if(empty($role))
        {
            $this->error("Role with name \"{$this->argument('roleName')}\" was not found.");
            return;
        }

        $user->attachRole($this->argument('roleName')) ?
            $this->info("User \"{$user->name}\" was given the role \"{$role->name}\".")
            :
            $this->error("An error occurred stopping \"{$user->name}\" being given the \"{$role->name}\"");

        return;
    }
}

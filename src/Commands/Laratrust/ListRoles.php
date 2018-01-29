<?php

namespace RonAppleton\Commander\Commands;

use Illuminate\Console\Command;

class ListRoles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laratrust:list-roles';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
     */
    public function handle()
    {
        $roles = \App\Role::all();

        $headers = array_keys($roles[0]->toArray());

        $this->table($headers, $roles);
    }
}

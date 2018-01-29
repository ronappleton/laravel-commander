<?php

namespace RonAppleton\Commander\Commands\Laravel;

use Illuminate\Console\Command;
use DB;

class ListUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:list 
    {--skip= : Amount of records to skip}
    {--take= : Amount of user records to return}
    {--conditions= : String of conditions uses WhereRaw}
    {--select= : Comma seperated fields to return, syntax: id,name,age,shoesize }
    {--orderby= : Comma seperated orderby statement pairs, syntax: name:asc,age:desc }';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Lists Users, can be controlled by conditions';

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
        $query = DB::table('users');

        if (!empty($this->option('select'))) {
            $query->select($this->option('select'));
        }

        if (!empty($this->option('conditions'))) {
            $query->whereRaw($this->option('conditions'));
        }

        if (!empty($this->option('orderby'))) {
            $orderBy = $this->splitOrderby($this->option('orderby'));

            foreach ($orderBy as $clause) {
                $query->orderBy($clause[0], $clause[1]);
            }
        }

        if(!empty($this->option('skip')))
        {
            $query->skip($this->option('skip'));
        }

        $query->take(!empty($this->option('take')) ? $this->option('take') : 10);

        $this->displayUsers($query->get());
    }

    private function displayUsers($users)
    {
        $displayArray = [];

        foreach($users as $user)
        {
            $user = collect($user)->except('password', 'remember_token');
            $displayArray[] = $user->toArray();
        }

        $headers = array_keys($displayArray[0]);

        $this->table($headers, $displayArray);
    }

    private function splitOrderby(string $string)
    {
        $pairs = explode(',',$string);

        $orderBys = [];

        foreach ($pairs as $pair) {
            $parts = explode(':', $pair);

            $orderBys[] = $parts;
        }

        return $orderBys;
    }
}

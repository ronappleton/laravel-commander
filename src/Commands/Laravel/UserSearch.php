<?php

namespace RonAppleton\Commander\Commands;

use Illuminate\Console\Command;

class UserSearch extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:search
    {--perPage= : How many users to show per page}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'For finding a user within the system.';

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
        while (($choice = $this->choice('How would you like to find the user?', ['Id','Name', 'All', 'Exit'])) != 'Exit')
        {
            $prettyChoice = strtoupper($choice);

            if($choice == 'All')
            {
                $users = (app()->getNamespace() . 'User')::all();
            }
            else {
                $criteria = $this->ask("Enter the {$prettyChoice} of the user you wish to find");
                $users = (app()->getNamespace() . 'User')::where(strtolower($choice), 'like', '%' . $criteria . '%')->get();
            }

            if(!count($users))
            {
                $this->warn('No users found matching that criteria.');
            }

            $perPage = !empty($this->option('perPage')) ? $this->option('perPage') : 10;

            $this->paginateUsers($users, $perPage);
        }


    }

    private function paginateUsers($users, $perPage)
    {
        $choice = '';

        $showCount = $perPage;

        $headers = array_keys($users[0]->toArray());

        $count = count($users);

        $users = $users->chunk($showCount);

        $pages = (int) ceil((float) $count / $showCount);

        $page = 1;

        do {
            if($choice == 'Next Page')
            {
                $page += 1;
            }
            if($choice == 'Previous Page')
            {
                $page -= 1;
            }

            $data = ['records' => $count, 'pages' => $pages, 'page' => $page];

            $this->table(['Total Records', 'Pages', 'Current Page'], [$data]);

            $this->table($headers, $users[$page - 1]);

            $options = [];

            if($page < $pages)
            {
                $options[] = 'Next Page';
            }
            if($page > 1)
            {
                $options[] = 'Previous Page';
            }

            $options[] = 'Main Menu';
        }
        while (($choice = $this->choice("Options", $options)) != 'Main Menu');
    }
}

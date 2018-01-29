<?php

namespace RonAppleton\Commander\Commands;

use Validator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class AddUser extends Command
{
    private $requiredRules = ['name', 'email', 'password']; // Rules to be validated.

    private static $rules = [
        'name' => 'required|string|max:191',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|string|min:10|max:191',
    ]; // Used if not present in user model.

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:create
    {name : Users name}
    {email : Users email address}
    {password : Users Password}
    {--userModel=App\User : Custom user model name}
    {--userTable=users : Custom user table name}
    {--nameField=name : Custom user name column name}
    {--emailField=email : Custom user email address column name}
    {--passwordField=password : Custom password column name}';


    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Adds a user to laravels user table';

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
        $data = $this->arguments();

        $data = $this->validateOptions($data);

        $rules = $this->rules($data['userModel']);

        $this->validate($data, $rules);

        $this->createUser($data) ? $this->info('User created.') : $this->error('Could not save user!');;
    }

    /**
     * @param $data
     * @return mixed
     */
    private function validateOptions($data)
    {
        $data['nameField'] = $this->option('nameField') ?: 'name';
        $data['emailField'] = $this->option('emailField') ?: 'email';
        $data['passwordField'] = $this->option('passwordField') ?: 'password';

        $fieldArray = collect([
            'nameField' => $data['nameField'],
            'emailField' => $data['emailField'],
            'passwordField' => $data['passwordField'],
        ]);

        $data['userModel'] = $this->option('userModel') ?: app()->getNamespace() . 'User';

        $this->validateClass($data['userModel']);

        $data['userTable'] = $this->option('userTable') ?: 'users';

        $this->validateTable($data['userTable']);

        $this->validateFields($data['userTable'], $fieldArray);

        return $data;
    }

    /**
     * @param string $class
     */
    private function validateClass(string $class)
    {
        class_exists($class) ? null : $this->showErrors('User model does not exist!');
    }

    private function validateTable($table)
    {
        Schema::hasTable($table) ? null : $this->showErrors('The user table does not exist!');
    }

    private function validateFields($table, $fields)
    {
        foreach($fields as $fieldName => $fieldValue)
        {
            if(Schema::hasColumn($table, $fieldValue) == false)
            {
                $messages[] = "The {$fieldName} \"{$fieldValue}\" is incorrect, the column does not exist in your user table.";
            }
        }

        empty($messages) ?: $this->showErrors($messages);
    }

    private function validate($data, $rules)
    {
        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            $messages = $validator->messages()->messages();
            $messages = array_flatten($messages);

            $this->showErrors($messages);
        }
    }

    private function rules($model)
    {
        $rules = property_exists($model, 'rules') ? $model::$rules : self::$rules;

        foreach ($this->requiredRules as $requiredRule) {
            if (!in_array($requiredRule, $rules)) {
                $rules[$requiredRule] = self::$rules[$requiredRule];
            }
        }

        foreach ($rules as $rule => $value) {
            if (!in_array($rule, $this->requiredRules)) {
                unset($rules[$rule]);
            }
        }

        return $rules;
    }

    private function createUser($data)
    {
        $user = new $data['userModel'];

        $nameField = $data['nameField'];
        $emailField = $data['emailField'];
        $passwordField = $data['passwordField'];

        $user->$nameField = $data['name'];
        $user->$emailField = $data['email'];
        $user->$passwordField = bcrypt($data['password']);

        return $user->save();
    }



    private function showErrors($messages)
    {
        $messages = collect($messages);

        collect($messages)->each(function ($item) {
            $this->error($item);
        });

        exit();
    }


}

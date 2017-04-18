# 命令行工具

Pails的命令行工具完全从laravel移植而来。下面的文档就来自laravel。

- [简介](#introduction)
- [创建命令](#writing-commands)
    - [生成命令](#generating-commands)
    - [命令结构](#command-structure)
- [定义输入输出](#defining-input-expectations)
    - [Arguments](#arguments)
    - [Options](#options)
    - [Input Arrays](#input-arrays)
    - [Input Descriptions](#input-descriptions)
- [Command I/O](#command-io)
    - [Retrieving Input](#retrieving-input)
    - [Prompting For Input](#prompting-for-input)
    - [Writing Output](#writing-output)
- [Registering Commands](#registering-commands)
- [Programmatically Executing Commands](#programmatically-executing-commands)
    - [Calling Commands From Other Commands](#calling-commands-from-other-commands)

<a name="introduction"></a>
## Introduction

Pails 是框架自带的命令行工具的名称。它提供了一些对您的应用开发有帮助的命令。它是由强大的 Symfony Console 组件驱动的。为了查看所有可用的 Pails 的命令，您可以使用 list 命令来列出它们：


    php pails list

每一个命令都包含有 ”help” 信息，用来显示和描述这个命令可用的参数和选项。为了查看帮助信息，仅需在命令前添加 `help` 即可：

    php pails help db:migrate

<a name="writing-commands"></a>
## 创建命令

除了 Pails 自带的命令，您也可以创建与您的应用相关的自定义命令。这些命令将会被储存在 `app/Console/Commands` 文件夹下。

<a name="generating-commands"></a>
### Generating Commands

要创建一个新命令，您可以使用 `make:command` 这个 Pails 命令，这将会生成一个命令的模板文件帮助您进行开发：

    php artisan make:console SendEmails --command=emails:send

上面的命令会在 app/Console/Commands/SendEmailsCommand.php 文件中生成一个类。在创建命令时，加上 `--command` 这个选项，将可以指定这个终端命令的名称，即在运行 Pails 命令的时候所输入的名称。

<a name="command-structure"></a>
### 命令结构

一旦您的命令生成后，您需要填写对应命令类中的 `signature` 和 `description` 属性，这将会在 `list` 命令清单上显示您的命令。

`handle` 方法会在您的命令执行时被调用。您可以在这个方法中加入命令的具体逻辑。下面是一个自定义命令的例子。

Pails中的命令行同时还自动注入了Pails的DI容器，在命令行工具中可以方便使用DI中所有其他的服务。

    <?php

    namespace App\Console\Commands;
    
    use App\Models\User;
    use Pails\Console\Command;
    
    class SendEmails extends Command
    {
        /**
         * The name and signature of the console command.
         *
         * @var string
         */
        protected $signature = 'email:send {user}';

        /**
         * The console command description.
         *
         * @var string
         */
        protected $description = 'Send e-mails to a user';

        /**
         * Execute the console command.
         *
         * @return mixed
         */
        public function handle()
        {
            $this->someService->send(User::find($this->argument('user')));
        }
    }

<a name="closure-commands"></a>
### Closure Commands

Closure based commands provide an alternative to defining console commands as classes. In the same way that route Closures are an alternative to controllers, think of command Closures as an alternative to command classes. Within the `commands` method of your `app/Console/Kernel.php` file, Laravel loads the `routes/console.php` file:

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');
    }

Even though this file does not define HTTP routes, it defines console based entry points (routes) into your application. Within this file, you may define all of your Closure based routes using the `Artisan::command` method. The `command` method accepts two arguments: the [command signature](#defining-input-expectations) and a Closure which receives the commands arguments and options:

    Artisan::command('build {project}', function ($project) {
        $this->info("Building {$project}!");
    });

The Closure is bound to the underlying command instance, so you have full access to all of the helper methods you would typically be able to access on a full command class.

#### Type-Hinting Dependencies

In addition to receiving your command's arguments and options, command Closures may also type-hint additional dependencies that you would like resolved out of the [service container](/docs/{{version}}/container):

    use App\User;
    use App\DripEmailer;

    Artisan::command('email:send {user}', function (DripEmailer $drip, $user) {
        $drip->send(User::find($user));
    });

#### Closure Command Descriptions

When defining a Closure based command, you may use the `describe` method to add a description to the command. This description will be displayed when you run the `php artisan list` or `php artisan help` commands:

    Artisan::command('build {project}', function ($project) {
        $this->info("Building {$project}!");
    })->describe('Build the project');

<a name="defining-input-expectations"></a>
## Defining Input Expectations

When writing console commands, it is common to gather input from the user through arguments or options. Laravel makes it very convenient to define the input you expect from the user using the `signature` property on your commands. The `signature` property allows you to define the name, arguments, and options for the command in a single, expressive, route-like syntax.

<a name="arguments"></a>
### Arguments

All user supplied arguments and options are wrapped in curly braces. In the following example, the command defines one **required** argument: `user`:

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:send {user}';

You may also make arguments optional and define default values for arguments:

    // Optional argument...
    email:send {user?}

    // Optional argument with default value...
    email:send {user=foo}

<a name="options"></a>
### Options

Options, like arguments, are another form of user input. Options are prefixed by two hyphens (`--`) when they are specified on the command line. There are two types of options: those that receive a value and those that don't. Options that don't receive a value serve as a boolean "switch". Let's take a look at an example of this type of option:

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:send {user} {--queue}';

In this example, the `--queue` switch may be specified when calling the Artisan command. If the `--queue` switch is passed, the value of the option will be `true`. Otherwise, the value will be `false`:

    php artisan email:send 1 --queue

<a name="options-with-values"></a>
#### Options With Values

Next, let's take a look at an option that expects a value. If the user must specify a value for an option, suffix the option name with a `=` sign:

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:send {user} {--queue=}';

In this example, the user may pass a value for the option like so:

    php artisan email:send 1 --queue=default

You may assign default values to options by specifying the default value after the option name. If no option value is passed by the user, the default value will be used:

    email:send {user} {--queue=default}

<a name="option-shortcuts"></a>
#### Option Shortcuts

To assign a shortcut when defining an option, you may specify it before the option name and use a | delimiter to separate the shortcut from the full option name:

    email:send {user} {--Q|queue}

<a name="input-arrays"></a>
### Input Arrays

If you would like to define arguments or options to expect array inputs, you may use the `*` character. First, let's take a look at an example that specifies an array argument:

    email:send {user*}

When calling this method, the `user` arguments may be passed in order to the command line. For example, the following command will set the value of `user` to `['foo', 'bar']`:

    php artisan email:send foo bar

When defining an option that expects an array input, each option value passed to the command should be prefixed with the option name:

    email:send {user} {--id=*}

    php artisan email:send --id=1 --id=2

<a name="input-descriptions"></a>
### Input Descriptions

You may assign descriptions to input arguments and options by separating the parameter from the description using a colon. If you need a little extra room to define your command, feel free to spread the definition across multiple lines:

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:send
                            {user : The ID of the user}
                            {--queue= : Whether the job should be queued}';

<a name="command-io"></a>
## Command I/O

<a name="retrieving-input"></a>
### Retrieving Input

While your command is executing, you will obviously need to access the values for the arguments and options accepted by your command. To do so, you may use the `argument` and `option` methods:

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $userId = $this->argument('user');

        //
    }

If you need to retrieve all of the arguments as an `array`, call the `arguments` method:

    $arguments = $this->arguments();

Options may be retrieved just as easily as arguments using the `option` method. To retrieve all of the options as an array, call the `options` method:

    // Retrieve a specific option...
    $queueName = $this->option('queue');

    // Retrieve all options...
    $options = $this->options();

If the argument or option does not exist, `null` will be returned.

<a name="prompting-for-input"></a>
### Prompting For Input

In addition to displaying output, you may also ask the user to provide input during the execution of your command. The `ask` method will prompt the user with the given question, accept their input, and then return the user's input back to your command:

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $name = $this->ask('What is your name?');
    }

The `secret` method is similar to `ask`, but the user's input will not be visible to them as they type in the console. This method is useful when asking for sensitive information such as a password:

    $password = $this->secret('What is the password?');

#### Asking For Confirmation

If you need to ask the user for a simple confirmation, you may use the `confirm` method. By default, this method will return `false`. However, if the user enters `y` or `yes` in response to the prompt, the method will return `true`.

    if ($this->confirm('Do you wish to continue?')) {
        //
    }

#### Auto-Completion

The `anticipate` method can be used to provide auto-completion for possible choices. The user can still choose any answer, regardless of the auto-completion hints:

    $name = $this->anticipate('What is your name?', ['Taylor', 'Dayle']);

#### Multiple Choice Questions

If you need to give the user a predefined set of choices, you may use the `choice` method. You may set the default value to be returned if no option is chosen:

    $name = $this->choice('What is your name?', ['Taylor', 'Dayle'], $default);

<a name="writing-output"></a>
### Writing Output

To send output to the console, use the `line`, `info`, `comment`, `question` and `error` methods. Each of these methods will use appropriate ANSI colors for their purpose. For example, let's display some general information to the user. Typically, the `info` method will display in the console as green text:

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('Display this on the screen');
    }

To display an error message, use the `error` method. Error message text is typically displayed in red:

    $this->error('Something went wrong!');

If you would like to display plain, uncolored console output, use the `line` method:

    $this->line('Display this on the screen');

#### Table Layouts

The `table` method makes it easy to correctly format multiple rows / columns of data. Just pass in the headers and rows to the method. The width and height will be dynamically calculated based on the given data:

    $headers = ['Name', 'Email'];

    $users = App\User::all(['name', 'email'])->toArray();

    $this->table($headers, $users);

#### Progress Bars

For long running tasks, it could be helpful to show a progress indicator. Using the output object, we can start, advance and stop the Progress Bar. First, define the total number of steps the process will iterate through. Then, advance the Progress Bar after processing each item:

    $users = App\User::all();

    $bar = $this->output->createProgressBar(count($users));

    foreach ($users as $user) {
        $this->performTask($user);

        $bar->advance();
    }

    $bar->finish();

For more advanced options, check out the [Symfony Progress Bar component documentation](https://symfony.com/doc/2.7/components/console/helpers/progressbar.html).

<a name="registering-commands"></a>
## Registering Commands

Once your command is finished, you need to register it with Artisan. All commands are registered in the `app/Console/Kernel.php` file. Within this file, you will find a list of commands in the `commands` property. To register your command, simply add the command's class name to the list. When Artisan boots, all the commands listed in this property will be resolved by the [service container](/docs/{{version}}/container) and registered with Artisan:

    protected $commands = [
        Commands\SendEmails::class
    ];

<a name="programmatically-executing-commands"></a>
## Programmatically Executing Commands

Sometimes you may wish to execute an Artisan command outside of the CLI. For example, you may wish to fire an Artisan command from a route or controller. You may use the `call` method on the `Artisan` facade to accomplish this. The `call` method accepts the name of the command as the first argument, and an array of command parameters as the second argument. The exit code will be returned:

    Route::get('/foo', function () {
        $exitCode = Artisan::call('email:send', [
            'user' => 1, '--queue' => 'default'
        ]);

        //
    });

Using the `queue` method on the `Artisan` facade, you may even queue Artisan commands so they are processed in the background by your [queue workers](/docs/{{version}}/queues). Before using this method, make sure you have configured your queue and are running a queue listener:

    Route::get('/foo', function () {
        Artisan::queue('email:send', [
            'user' => 1, '--queue' => 'default'
        ]);

        //
    });

If you need to specify the value of an option that does not accept string values, such as the `--force` flag on the `migrate:refresh` command, you may pass `true` or `false`:

    $exitCode = Artisan::call('migrate:refresh', [
        '--force' => true,
    ]);

<a name="calling-commands-from-other-commands"></a>
### Calling Commands From Other Commands

Sometimes you may wish to call other commands from an existing Artisan command. You may do so using the `call` method. This `call` method accepts the command name and an array of command parameters:

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->call('email:send', [
            'user' => 1, '--queue' => 'default'
        ]);

        //
    }

If you would like to call another console command and suppress all of its output, you may use the `callSilent` method. The `callSilent` method has the same signature as the `call` method:

    $this->callSilent('email:send', [
        'user' => 1, '--queue' => 'default'
    ]);
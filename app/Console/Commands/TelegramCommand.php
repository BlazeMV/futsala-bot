<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TelegramCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:telegram {what} {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new Bot Command at app/Commands';

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
        if ($this->argument('what') == 'command') {
            $command_name = studly_case($this->argument('name'));
            $file_path = app_path() . "/TelegramCommands/";
            if (!file_exists($file_path)) {
                mkdir($file_path, 0777, true);
            }
            file_put_contents($file_path . $command_name . "Command.php", "<?php\n\nnamespace App\TelegramCommands;\n\nuse Telegram\Bot\Commands\Command;\n\nclass $command_name" . "Command extends Command\n{\n\n    protected \$name = \"" . strtolower($command_name) . "\";\n\n    protected \$description = \"\";\n\n    public function handle(\$arguments)\n    {\n        \$this->replyWithMessage(['text' => '$command_name']);\n    }\n}");
            $this->info("Bot Command created!");
        } elseif ($this->argument('what') == 'callbackquery' || $this->argument('what') == 'cbq') {
            $query_name = studly_case($this->argument('name'));
            $file_path = app_path() . "/TelegramCallbackQueries/";
            if (!file_exists($file_path)) {
                mkdir($file_path, 0777, true);
            }
            file_put_contents($file_path . $query_name . "CallbackQuery.php", "<?php\n\nnamespace App\TelegramCallbackQueries;\n\nuse Telegram\Bot\Commands\Command;\n\nclass $query_name" . "CallbackQuery extends Command\n{\n\n    protected \$name = \"" . strtolower($query_name) . "\";\n\n    protected \$description = \"\";\n\n    public function handle(\$arguments)\n    {\n        \$this->replyWithMessage(['text' => '$query_name']);\n    }\n}");
            $this->info("Bot Callback Query created!");
        } else {
            $this->error("Unsupported command!");
        }
    }
}

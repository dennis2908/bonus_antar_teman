<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\RabbitMQ\OrderConfirmation;

class MQSendCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mq:send';

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
     *
     * @return int
     */
    public function handle(): void
    {
        $message = "jaka@yahoo.com";

        $rabbitMQService = new OrderConfirmation();
        $rabbitMQService->publish($message);

        echo "send message ";
    }
}

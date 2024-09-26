<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\RabbitMQ\OrderConfirmation;
use App\Models\Muser;
use DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendEmail;
use Illuminate\Support\Facades\Redis;
use App\Events\MessageEvent;

class MQOrderConfirmation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mq:oc';


    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'order confirmation command';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $rabbitMQService = new OrderConfirmation();

        $callback = function ($msg) {
            $email = new SendEmail();
            Mail::to(Redis::get($msg->body))->send($email);
            event(new MessageEvent(1,"dadsasd"));
            // $data = Muser::select(['id'])->where('no_sim','=',$msg->body)->first();
            // if(!$data)
            //     echo "Received message: null" . "\n";
            // else
            //     echo "Received message: " . $data->id . "\n";
            echo "Received message: " . $msg->body . "\n";
        };

        $rabbitMQService->consume($callback);
    }
}

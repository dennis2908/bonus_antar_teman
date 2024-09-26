<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\RabbitMQ\OrderProcess;
use App\Models\Order;
use DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendEmail;
use App\Events\MessageEvent;


class MQOrderProcess extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mq:op';


    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Order process command';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $rabbitMQService = new OrderProcess();

        $callback = function ($msg) {
            

            Order::where('id',$msg->body)->update(['status'=>'Processed']);
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

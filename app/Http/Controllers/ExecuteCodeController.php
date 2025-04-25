<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class ExecuteCodeController extends Controller
{
    public function sendCodeToQueue()
    {
        $validator = Validator::make(request()->all(), [
            'code' => 'required|string',
            'language' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $data = $validator->validate();

        $connection = new AMQPStreamConnection('rabbitmq', 5672, 'guest', 'guest');
        $channel = $connection->channel();

        $channel->queue_declare($data['language'], false, true, false, false);

        $fileUniqId = uniqid();
        $filePath = '/var/www/code-share/php/code/' . $fileUniqId . '.php';
        file_put_contents($filePath, $data['code']);

        $msg = new AMQPMessage($fileUniqId);
        $channel->basic_publish($msg, '', $data['language']);

        $channel->close();
        $connection->close();

        return response()->json(['message' => 'Код отправлен в очередь', 'unique_id' => $fileUniqId]);
    }
}

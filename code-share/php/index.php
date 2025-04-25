<?php

require __DIR__ . '/vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;

$connection = new AMQPStreamConnection('rabbitmq', 5672, 'guest', 'guest');
$channel = $connection->channel();

$channel->queue_declare('php', false, true, false, false);

function sendResult(string $id, string $result, float $time): void {
    $url = 'http://nginx/api/code/execution-result';

    $headers = array();
    $headers[] = "Accept: application/json";
    $headers[] = "Content-Type: application/json";

    $params = Array(
        "id" => $id,
        "result" => $result,
        "execution_time" => $time
    );

    $params = json_encode($params);

    $crl = curl_init();
    curl_setopt($crl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($crl, CURLOPT_URL, $url);
    curl_setopt($crl, CURLOPT_POST, true);
    curl_setopt($crl, CURLOPT_POSTFIELDS, $params);

    curl_exec($crl);

    curl_close($crl);
}

function errorHandler($errno, $errstr, $errfile, $errline): void {
    echo "Ошибка: $errstr, \nна строке - $errline" . PHP_EOL;
}

function runCode(string $filePath): array {
    set_error_handler('errorHandler');
    register_shutdown_function(function () {
        $error = error_get_last();
        if ($error) {
            echo "[Shutdown Error] {$error['message']}\nна строке - {$error['line']}";
        }
    });

    ob_start();
    $start = microtime(true);
    try {
        include $filePath;
    } catch (Throwable $e) {
        echo "[Exception] " . $e->getMessage();
    }
    $time = microtime(true) - $start;
    $output = ob_get_clean();

    return ["output" => $output, 'time' => $time];
}

$callback = function($msg) {
    $fileId = $msg->body;
    $filePath = '/var/www/code/php/code/' . $fileId . '.php';

    $results = runCode($filePath);
    sendResult($fileId, $results['output'], $results['time']);
    unlink($filePath);
};

$channel->basic_consume('php', '', false, true, false, false, $callback);

echo "Ожидание задач на выполнение...\n";

while ($channel->is_consuming()) {
    $channel->wait();
}

$channel->close();
$connection->close();

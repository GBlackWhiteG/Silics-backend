import pika
import signal
import sys
import os
import subprocess
import time
import requests
import json

connection = None
channel = None

def isCodeSafe(filePath: str):
    banned = [
            'import os', 'import subprocess', 'import sys', 'open(', '__import__',
            'eval(', 'exec(', 'from os', 'from subprocess', 'import shutil',
            'socket', 'requests', 'import threading'
        ]
    with open(filePath, 'r') as file:
        code = file.read()
    return not any(b in code for b in banned)

def runCode(filePath: str):
    start = time.time()
    if (isCodeSafe(filePath)):
        result = subprocess.run(['python', filePath], capture_output=True, text=True, timeout=30)
    else:
        result = "Ошибка: запрещенная команда"
        return {'output': result, 'time': 0}
    exTime = time.time() - start

    if result.returncode != 0:
        output = result.stderr
    else:
        output = result.stdout
    return {'output': output, 'time': exTime}

def sendResult(id: str, result, time: float):
    url = 'http://nginx/api/code/execution-result'
    data = {'id': id, 'result': result, 'execution_time': time}
    params = json.dumps(data)
    response = requests.post(url, json=data)

def callback(ch, method, properties, body):
    fileId = body.decode()
    filePath = f"/var/www/code/python/code/{fileId}.py"
    result = runCode(filePath)
    os.unlink(filePath)
    sendResult(fileId, result['output'], result['time'])

def graceful_shutdown(signum, frame):
    if channel and channel.is_open:
        channel.close()
    if connection and connection.is_open:
        connection.close()
    sys.exit(0)

def main():
    global connection, channel

    credentials = pika.PlainCredentials('guest', 'guest')
    parameters = pika.ConnectionParameters('rabbitmq', 5672, '/', credentials)
    connection = pika.BlockingConnection(parameters)
    channel = connection.channel()
    channel.queue_declare(queue='python', durable=True)

    channel.basic_consume(
        queue='python',
        on_message_callback=callback,
        auto_ack=True
    )

    signal.signal(signal.SIGINT, graceful_shutdown)

    print("Ожидание задач на выполенени...\n", flush=True)
    channel.start_consuming()

if __name__ == '__main__':
    main()

const amqp = require('amqplib/callback_api');
const {spawn} = require('child_process');
const fs = require('fs');

const url = 'http://nginx/api/code/execution-result';

const sendResult = (id, result, time) => {
    const data = {
        "id": id,
        "result": result,
        "execution_time": time
    }

    fetch(url, {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
        },
        body: JSON.stringify(data)
    }).catch(err => {
        console.error(err);
    })
}

const runCode = (filePath) => {
    return new Promise((resolve) => {
        const output = [];
        const start = performance.now();
        const child = spawn('node', [filePath]);

        child.stdout.on('data', (data) => {
            output.push(data);
        });

        child.stderr.on('data', (data) => {
            output.push(`Ошибка: ${data}`);
        });

        child.on('close', (code) => {
            const result = Buffer.concat(output).toString();
            const time = performance.now() - start;
            resolve({"output": result, "time": (time / 1000)});
        })
    })
}

const callback = async (msg) => {
    const fileId = msg.content.toString();
    const filePath = '/var/www/code/js/code/'.concat(fileId, '.js');
    try {
        const result = await runCode(filePath);
        sendResult(fileId, result['output'], result['time']);
        fs.unlink(filePath, (err) => {
            if (err) console.error('Ошибка удаления файла: ', err);
        });
    } catch (err) {
        console.log(`Ошибка выполнения кода: ${err}`);
    }
}


const credentials = amqp.credentials.plain('guest', 'guest')
amqp.connect({
    protocol: "amqp",
    hostname: 'rabbitmq',
    port: 5672,
    virtualHost: '/',
    credentials
}, function (error0, connection) {
    if (error0) {
        console.error('Ошибка подключения:', error0);
        return;
    }

    console.log('Ожидание задач на выполнение...\n');

    connection.createChannel(function (error1, channel) {
        if (error1) {
            console.error('Ошибка создания канала:', error1);
        }

        const queue = 'javascript';

        channel.assertQueue(queue, {durable: true});
        channel.consume(queue, callback, {
            noAck: true
        });
    });
});


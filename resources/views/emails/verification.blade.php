<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Подтверждение почты</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 30px auto;
            background: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            color: #333;
        }
        .content {
            margin: 20px 0;
            text-align: center;
            font-size: 16px;
            color: #555;
        }
        .code {
            display: block;
            width: fit-content;
            margin: 20px auto;
            font-size: 22px;
            font-weight: bold;
            padding: 10px 15px;
            background: #007bff;
            color: #fff;
            border-radius: 5px;
            text-align: center;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 14px;
            color: #888;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">Подтверждение почты</div>
    <div class="content">
        Здравствуйте! Ваш код подтверждения:
    </div>
    <div class="code">{{ $code }}</div>
    <div class="footer">
        Если вы не запрашивали этот код, просто проигнорируйте это письмо.
    </div>
</div>
</body>
</html>

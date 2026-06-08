<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        .header {
            background-color: #007bff;
            color: white;
            padding: 20px;
            border-radius: 5px 5px 0 0;
            text-align: center;
        }
        .content {
            padding: 20px;
            background-color: white;
        }
        .code-box {
            background-color: #f0f0f0;
            border: 2px solid #007bff;
            padding: 15px;
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            letter-spacing: 5px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .footer {
            text-align: center;
            padding: 10px;
            font-size: 12px;
            color: #999;
            background-color: #f9f9f9;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Confirm Your E-mail Address</h1>
        </div>
        <div class="content">
            <h2>Hello, {{ $userName }}</h2>
            <p>Thanks for registering with {{ config('app.name') }}. Please use the following code to confirm your e-mail address:</p>

            <div class="code-box">
                {{ $confirmationCode }}
            </div>

            <p>This code will expire in <strong>{{ intdiv($expiresIn, 60) }} minutes</strong>.</p>

            <p>If you didn't create an account, please ignore this email.</p>

            <hr>

            <p style="font-size: 12px; color: #999;">
                This is an automated email. Please do not reply to this message.
            </p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>

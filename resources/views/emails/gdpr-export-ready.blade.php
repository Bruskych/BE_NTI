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
            <h1>Your Data Export</h1>
        </div>
        <div class="content">
            <h2>Hello, {{ $userName }},</h2>
            <p>As requested, we have prepared an export of your personal data held in our system.</p>
            <p>The export is attached to this email as a JSON file.</p>

            <p>If you did not request this export, please contact us immediately.</p>

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

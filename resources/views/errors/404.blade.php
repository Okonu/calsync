<!-- resources/views/errors/404.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Not Found</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.5;
            color: #374151;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: #f3f4f6;
        }
        .container {
            max-width: 600px;
            padding: 40px;
            background-color: #fff;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
            text-align: center;
        }
        .error-icon {
            font-size: 72px;
            margin-bottom: 20px;
            color: #ef4444;
        }
        h1 {
            color: #111827;
            font-size: 24px;
            margin-bottom: 16px;
        }
        p {
            color: #4b5563;
            margin-bottom: 24px;
        }
        .btn {
            display: inline-block;
            background-color: #4f46e5;
            color: #ffffff;
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 6px;
            font-weight: 500;
            transition: background-color 0.2s;
        }
        .btn:hover {
            background-color: #4338ca;
        }
        .btn-secondary {
            background-color: #9ca3af;
            margin-left: 10px;
        }
        .btn-secondary:hover {
            background-color: #6b7280;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="error-icon">😕</div>
    <h1>Oops! Resource Not Found</h1>
    <p>The resource you're looking for is not here or has just been deleted.</p>
    <a href="{{ url('/') }}" class="btn">Go to Homepage</a>
    <a href="{{ url()->previous() }}" class="btn btn-secondary">Go Back</a>
</div>
</body>
</html>

<!-- resources/views/pages/privacy-policy.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy - {{ config('app.name') }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #374151;
            margin: 0;
            padding: 0;
            background-color: #f9fafb;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 40px 20px;
        }
        header {
            text-align: center;
            margin-bottom: 40px;
        }
        h1 {
            color: #111827;
            font-size: 32px;
            margin-bottom: 16px;
        }
        h2 {
            color: #1f2937;
            font-size: 24px;
            margin-top: 32px;
            margin-bottom: 16px;
            padding-bottom: 8px;
            border-bottom: 1px solid #e5e7eb;
        }
        p {
            margin-bottom: 16px;
        }
        .updated-date {
            color: #6b7280;
            font-style: italic;
            margin-bottom: 32px;
        }
        .btn {
            display: inline-block;
            background-color: #4f46e5;
            color: #ffffff;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 6px;
            font-weight: 500;
            transition: background-color 0.2s;
        }
        .btn:hover {
            background-color: #4338ca;
        }
        footer {
            margin-top: 60px;
            text-align: center;
            color: #6b7280;
            font-size: 14px;
        }
    </style>
</head>
<body>
<div class="container">
    <header>
        <h1>Privacy Policy</h1>
    </header>

    <section>
        <h2>1. Introduction</h2>
        <p>Welcome to {{ config('app.name') }}. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you use our service.</p>

        <h2>2. Information We Collect</h2>
        <p>We collect information that you provide directly to us when you register for an account, create or modify your profile, set preferences, or make purchases through the Service.</p>

        <h2>3. How We Use Your Information</h2>
        <p>We use the information we collect to provide, maintain, and improve our services, including to process transactions, send you related information, and provide customer support.</p>

        <h2>4. Sharing Your Information</h2>
        <p>We may share the information we collect in various ways, including with vendors and service providers who need access to such information to carry out work on our behalf.</p>

        <h2>5. Your Rights</h2>
        <p>You have certain rights regarding the personal information we hold about you, subject to local law. These may include the rights to access, correct, delete, restrict processing of, and object to processing of your personal information.</p>

        <h2>6. Contact Us</h2>
        <p>If you have any questions about this Privacy Policy, please contact us at:</p>
        <h3><a href="mailto:hello@sysnqs.site">hello@sysnqs.site</a></h3>

    </section>

    <div style="text-align: center; margin-top: 40px;">
        <a href="{{ url('/') }}" class="btn">Return to Homepage</a>
    </div>

    <footer>
        <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        <p class="updated-date">Last Updated: {{ date('Y') }}</p>
    </footer>
</div>
</body>
</html>

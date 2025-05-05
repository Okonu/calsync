<!-- resources/views/pages/terms-of-service.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms of Service - {{ config('app.name') }}</title>
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
        <h1>Terms of Service</h1>
    </header>

    <section>
        <h2>1. Acceptance of Terms</h2>
        <p>By accessing or using the {{ config('app.name') }} service, you agree to be bound by these Terms of Service and all applicable laws and regulations.</p>

        <h2>2. Use License</h2>
        <p>Permission is granted to temporarily use the service for personal, non-commercial use only. This is the grant of a license, not a transfer of title.</p>

        <h2>3. User Account</h2>
        <p>To access some features of the service, you may be required to register for an account. You must provide accurate, current, and complete information during the registration process.</p>

        <h2>4. Prohibited Uses</h2>
        <p>You may not use the service for any illegal or unauthorized purpose nor may you violate any laws in your jurisdiction.</p>

        <h2>5. Termination</h2>
        <p>We may terminate or suspend access to our service immediately, without prior notice or liability, for any reason whatsoever.</p>

        <h2>6. Governing Law</h2>
        <p>These Terms shall be governed and construed in accordance with the laws, without regard to its conflict of law provisions.</p>

        <h2>7. Contact Us</h2>
        <p>If you have any questions about these Terms, please contact us at: </p>
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

<!-- resources/views/emails/booking/cancellation.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meeting Cancelled</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.5;
            color: #374151;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .card {
            text-align: center;
            margin-bottom: 25px;
        }
        .card-inner {
            display: inline-block;
            background-color: #fef2f2;
            border-radius: 8px;
            padding: 20px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }
        .card-title {
            color: #b91c1c;
            margin-top: 0;
            margin-bottom: 15px;
            font-size: 20px;
        }
        .card-text {
            color: #4b5563;
            margin-bottom: 20px;
        }
        .details-box {
            background-color: #ffffff;
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 20px;
            text-align: left;
            border-left: 4px solid #ef4444;
        }
        .details-title {
            margin-top: 0;
            margin-bottom: 10px;
            color: #111827;
            font-size: 16px;
        }
        .details-item {
            margin: 0;
            color: #374151;
            font-size: 14px;
            margin-bottom: 6px;
        }
        .section-title {
            color: #111827;
            font-size: 18px;
            margin-top: 25px;
            margin-bottom: 15px;
        }
        .button {
            display: inline-block;
            background-color: #4f46e5;
            color: #ffffff;
            text-decoration: none;
            padding: 12px 20px;
            border-radius: 4px;
            font-weight: 500;
            margin: 10px 0;
        }
        .footer {
            margin-top: 30px;
            text-align: left;
            color: #6b7280;
            font-size: 14px;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>Meeting Cancelled</h1>
    </div>

    <div class="card">
        <div class="card-inner">
            <h2 class="card-title">Meeting Cancelled</h2>

            <p class="card-text">
                @if($isOrganizer)
                    {{ $booking->name }} has cancelled their meeting with you.
                @else
                    Your meeting with {{ $booking->bookingPage->user->name }} has been cancelled.
                @endif
            </p>

            <div class="details-box">
                <h3 class="details-title">Cancelled Meeting Details</h3>
                <p class="details-item"><strong>Date:</strong> {{ $booking->starts_at->format('l, F j, Y') }}</p>
                <p class="details-item"><strong>Time:</strong> {{ $booking->starts_at->format('g:i A') }} - {{ $booking->ends_at->format('g:i A') }} ({{ $booking->starts_at->timezone->getName() }})</p>
                <p class="details-item"><strong>Duration:</strong> {{ $booking->bookingPage->duration }} minutes</p>
            </div>
        </div>
    </div>

    @if(!$isOrganizer)
        <h2 class="section-title">Would you like to reschedule?</h2>
        <p>If you'd like to book another meeting, please click the button below:</p>
        <a href="{{ url('/book/' . $booking->bookingPage->slug) }}" class="button">Book Another Meeting</a>
    @endif

    <div class="footer">
        <p>Thanks,<br>
            {{ config('app.name') }}</p>
    </div>
</div>
</body>
</html>

<!-- resources/views/pages/docs.blade.php -->
@extends('layouts.legal')

@section('title', 'Developer API')
@section('description', 'Check availability and create bookings on a Synqs booking page from your own app or site.')
@section('updated', 'July 29, 2026')

@section('toc')
    <li><a href="#overview">Overview</a></li>
    <li><a href="#authentication">Authentication</a></li>
    <li><a href="#rate-limits">Rate limits</a></li>
    <li><a href="#availability">Get availability</a></li>
    <li><a href="#create-booking">Create a booking</a></li>
    <li><a href="#errors">Errors</a></li>
    <li><a href="#example">Server-side example</a></li>
@endsection

@section('content')

    <style>
        pre {
            background: #0e0e1a;
            color: #e4e4ec;
            border-radius: 10px;
            padding: 1rem 1.25rem;
            overflow-x: auto;
            font-size: 0.85rem;
            line-height: 1.6;
        }
        code {
            font-family: 'JetBrains Mono', ui-monospace, monospace;
        }
        :not(pre) > code {
            background: var(--paper-dim);
            padding: 0.1rem 0.4rem;
            border-radius: 4px;
            font-size: 0.9em;
        }
        .endpoint {
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.9rem;
            background: var(--paper-dim);
            border: 1px solid var(--line);
            border-radius: 8px;
            padding: 0.6rem 1rem;
            margin: 0.75rem 0 1.25rem;
        }
        .endpoint .method {
            font-weight: 700;
            color: var(--indigo);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9rem;
            margin: 1rem 0;
        }
        th, td {
            text-align: left;
            padding: 0.5rem 0.75rem;
            border-bottom: 1px solid var(--line);
        }
        th {
            color: var(--ink-mute);
            font-weight: 600;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
    </style>

    <section id="overview">
        <h2><span class="num">01</span> Overview</h2>
        <p>
            The Synqs developer API lets your own app or site check real availability on a Synqs booking page and
            create bookings on it directly &mdash; useful if you want a "book a call" step inside your own product
            instead of sending people to a Synqs link. It's the same conflict-checking and calendar-event logic that
            powers a Synqs booking page itself, exposed as two JSON endpoints.
        </p>
        <p>Base URL: <code>https://synqs.site/api/v1</code></p>
    </section>

    <section id="authentication">
        <h2><span class="num">02</span> Authentication</h2>
        <p>
            Generate an API key from <strong>Settings &rarr; Developer API</strong> in your Synqs account. Send it as
            a bearer token on every request:
        </p>
        <pre><code>Authorization: Bearer YOUR_API_KEY</code></pre>
        <div class="callout">
            Your API key is a secret. Call these endpoints from your own backend, not from client-side JavaScript on
            a public page &mdash; anyone who views the page source would be able to read it.
        </div>
    </section>

    <section id="rate-limits">
        <h2><span class="num">03</span> Rate limits</h2>
        <p>Each API key is limited to <strong>60 requests per minute</strong>. Responses over the limit return <code>429 Too Many Requests</code>.</p>
    </section>

    <section id="availability">
        <h2><span class="num">04</span> Get availability</h2>
        <div class="endpoint"><span class="method">GET</span> /booking-pages/{slug}/availability?date=YYYY-MM-DD</div>
        <p><code>slug</code> is the booking page's slug (the part after <code>/book/</code> in its public URL).</p>

        <table>
            <thead><tr><th>Param</th><th>Type</th><th>Required</th></tr></thead>
            <tbody>
                <tr><td><code>date</code></td><td>string, <code>YYYY-MM-DD</code></td><td>Yes</td></tr>
            </tbody>
        </table>

        <pre><code>curl "https://synqs.site/api/v1/booking-pages/okonu/availability?date=2026-08-03" \
  -H "Authorization: Bearer YOUR_API_KEY"</code></pre>

        <p>Response:</p>
        <pre><code>{
  "slots": [
    { "start": "09:00", "end": "09:30" },
    { "start": "09:30", "end": "10:00" },
    { "start": "11:00", "end": "11:30" }
  ],
  "timezone": "UTC"
}</code></pre>
    </section>

    <section id="create-booking">
        <h2><span class="num">05</span> Create a booking</h2>
        <div class="endpoint"><span class="method">POST</span> /booking-pages/{slug}/bookings</div>

        <table>
            <thead><tr><th>Field</th><th>Type</th><th>Required</th></tr></thead>
            <tbody>
                <tr><td><code>name</code></td><td>string</td><td>Yes</td></tr>
                <tr><td><code>email</code></td><td>string</td><td>Yes</td></tr>
                <tr><td><code>date</code></td><td>string, <code>YYYY-MM-DD</code></td><td>Yes</td></tr>
                <tr><td><code>time</code></td><td>string, <code>HH:MM</code> (24h, matches a slot from availability)</td><td>Yes</td></tr>
                <tr><td><code>notes</code></td><td>string, up to 500 characters</td><td>No</td></tr>
            </tbody>
        </table>

        <pre><code>curl -X POST "https://synqs.site/api/v1/booking-pages/okonu/bookings" \
  -H "Authorization: Bearer YOUR_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Amina T.",
    "email": "amina@example.com",
    "date": "2026-08-03",
    "time": "09:00",
    "notes": "Referred by the Mundly audit form"
  }'</code></pre>

        <p>Response:</p>
        <pre><code>{
  "booking": {
    "name": "Amina T.",
    "starts_at": "Monday, August 3, 2026 9:00 AM",
    "ends_at": "9:30 AM",
    "meeting_link": "https://meet.google.com/abc-defg-hij",
    "with": "Okonu",
    "uid": "b3f1c9a2-..."
  }
}</code></pre>
        <p>
            Synqs re-checks the slot against every connected calendar before confirming, creates the calendar event
            and Meet link, and emails both sides a confirmation &mdash; identical to what happens when someone books
            through the Synqs page directly.
        </p>
    </section>

    <section id="errors">
        <h2><span class="num">06</span> Errors</h2>
        <table>
            <thead><tr><th>Status</th><th>Meaning</th></tr></thead>
            <tbody>
                <tr><td><code>401</code></td><td>Missing or invalid API key</td></tr>
                <tr><td><code>403</code></td><td>API key doesn't have the required ability</td></tr>
                <tr><td><code>404</code></td><td>No active booking page with that slug</td></tr>
                <tr><td><code>422</code></td><td>Validation error, or the slot was booked by someone else in the meantime</td></tr>
                <tr><td><code>429</code></td><td>Rate limit exceeded</td></tr>
            </tbody>
        </table>
    </section>

    <section id="example">
        <h2><span class="num">07</span> Server-side example (Node/Express)</h2>
        <p>Call the API from your backend, then return only what your frontend needs:</p>
        <pre><code>app.post('/book-a-call', async (req, res) => {
  const response = await fetch(
    'https://synqs.site/api/v1/booking-pages/okonu/bookings',
    {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${process.env.SYNQS_API_KEY}`,
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        name: req.body.name,
        email: req.body.email,
        date: req.body.date,
        time: req.body.time,
      }),
    }
  );

  const data = await response.json();

  if (!response.ok) {
    return res.status(response.status).json({ error: data.message || 'Booking failed' });
  }

  res.json({ meetingLink: data.booking.meeting_link });
});</code></pre>
    </section>

@endsection

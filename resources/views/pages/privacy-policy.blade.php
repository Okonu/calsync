<!-- resources/views/pages/privacy-policy.blade.php -->
@extends('layouts.legal')

@section('title', 'Privacy Policy')
@section('description', 'How Synqs collects, uses, and protects your data across calendar sync, booking pages, and community events.')

@section('toc')
    <li><a href="#introduction">Introduction</a></li>
    <li><a href="#information-we-collect">Information we collect</a></li>
    <li><a href="#how-we-use-it">How we use it</a></li>
    <li><a href="#how-we-share-it">How we share it</a></li>
    <li><a href="#retention-deletion">Retention &amp; deletion</a></li>
    <li><a href="#your-rights">Your rights</a></li>
    <li><a href="#security">Security</a></li>
    <li><a href="#children">Children's privacy</a></li>
    <li><a href="#international">International use</a></li>
    <li><a href="#changes">Changes to this policy</a></li>
    <li><a href="#contact">Contact us</a></li>
@endsection

@section('content')

    <section id="introduction">
        <h2><span class="num">01</span> Introduction</h2>
        <p>
            Synqs ("Synqs", "we", "us") helps people connect multiple Google and Microsoft calendars into one view,
            share a booking page so others can reserve real open time, and run community events and call-for-speakers
            programs. This policy explains what information we collect to do that, why we collect it, who we share it
            with, and the choices you have &mdash; including deleting your account entirely.
        </p>
        <p>
            This policy applies to everyone who uses Synqs: people who sign in and connect a calendar, and guests who
            book time through someone else's public booking page without ever creating an account.
        </p>
    </section>

    <section id="information-we-collect">
        <h2><span class="num">02</span> Information we collect</h2>

        <h3>Account &amp; calendar data</h3>
        <p>
            When you sign in with Google or connect a Microsoft account, we receive your name, email address, and an
            OAuth access token and refresh token that let Synqs read your calendar list and events, and create or
            cancel events on your behalf (for example, when someone books a meeting with you). We store calendar and
            event metadata &mdash; titles, start and end times, and attendee counts &mdash; so your unified calendar
            view and conflict checks stay accurate.
        </p>

        <h3>Booking data</h3>
        <p>
            When someone books time through your public booking page, we collect the booker's name, email address,
            any notes they choose to add, and the time they booked. If the booking includes a video meeting, we
            request a Google Meet link as part of creating the calendar event.
        </p>

        <h3>Community &amp; event data</h3>
        <p>
            If you create a community, we store its name, description, logo, contact details, and any events or
            call-for-speakers listings you publish. When someone submits a speaker application, we store what they
            enter in the form along with any files they choose to upload, such as a résumé or talk pitch.
        </p>

        <h3>Technical data</h3>
        <p>
            Like most web services, we log standard technical data &mdash; IP address, browser type, and pages
            requested &mdash; for security monitoring and troubleshooting.
        </p>

        <h3>Cookies</h3>
        <p>
            We use a small number of essential, first-party cookies to keep you signed in and to protect against
            cross-site request forgery. We don't use advertising or third-party tracking cookies.
        </p>
    </section>

    <section id="how-we-use-it">
        <h2><span class="num">03</span> How we use it</h2>
        <ul>
            <li>Sync your connected calendars into one view and check them for conflicts before a booking is confirmed</li>
            <li>Create, update, and cancel calendar events and Google Meet links tied to bookings</li>
            <li>Send transactional email &mdash; booking confirmations and cancellations &mdash; to the people involved in a booking</li>
            <li>Operate community pages, event listings, and call-for-speakers review</li>
            <li>Maintain the security, integrity, and reliability of the service, and diagnose problems when something breaks</li>
        </ul>
        <p>We don't use your calendar content, booking details, or community data to train models or to serve ads.</p>
    </section>

    <section id="how-we-share-it">
        <h2><span class="num">04</span> How we share it</h2>
        <p>We share information only where it's needed to run the service:</p>
        <ul>
            <li><strong>Google and Microsoft</strong> &mdash; to read and write calendar events through their APIs, as authorized by you when you connect an account</li>
            <li><strong>Email delivery</strong> &mdash; to send booking confirmations and cancellations to the organizer and the guest</li>
            <li><strong>Service providers</strong> &mdash; infrastructure providers who host the application and database on our behalf, under obligations to protect your data</li>
        </ul>
        <p>We do not sell personal information, and we don't share your booking or community data with third parties for their own marketing purposes.</p>
    </section>

    <section id="retention-deletion">
        <h2><span class="num">05</span> Retention &amp; deletion</h2>
        <p>
            You can delete your Synqs account at any time from your account settings. When you do, we remove your
            profile, connected calendar credentials, booking pages, and community data.
        </p>
        <div class="callout">
            Because deleting your account can affect calendar events that were created through bookings on your
            page, we'll ask you at deletion time whether you'd like us to cancel those events on your connected
            calendars, or leave them as they are.
        </div>
        <p>
            Guest booking records tied to a deleted booking page are kept only as long as needed for support,
            security, or legal purposes, and are then removed. Backups that include deleted data age out on our
            standard backup rotation.
        </p>
    </section>

    <section id="your-rights">
        <h2><span class="num">06</span> Your rights</h2>
        <p>Depending on where you live, you may have the right to:</p>
        <ul>
            <li>Access the personal information we hold about you</li>
            <li>Correct inaccurate information</li>
            <li>Request a copy of your data in a portable format</li>
            <li>Delete your account and associated data, as described above</li>
            <li>Object to or restrict certain processing</li>
        </ul>
        <p>To exercise any of these rights, contact us at the email address below.</p>
    </section>

    <section id="security">
        <h2><span class="num">07</span> Security</h2>
        <p>
            We apply reasonable technical and organizational measures to protect your information, including
            encrypted transport (HTTPS/TLS) for all traffic and access controls on our production systems. No method
            of transmission or storage is completely secure, and we can't guarantee absolute security.
        </p>
    </section>

    <section id="children">
        <h2><span class="num">08</span> Children's privacy</h2>
        <p>
            Synqs is not directed at children under 16, and we don't knowingly collect personal information from
            them. If you believe a child has provided us with personal information, contact us and we'll remove it.
        </p>
    </section>

    <section id="international">
        <h2><span class="num">09</span> International use</h2>
        <p>
            Synqs may process and store information in countries other than your own. Wherever your information is
            processed, we apply the same protections described in this policy.
        </p>
    </section>

    <section id="changes">
        <h2><span class="num">10</span> Changes to this policy</h2>
        <p>
            We may update this policy as the service changes. If we make material changes, we'll update the date at
            the top of this page. Continuing to use Synqs after a change means you accept the updated policy.
        </p>
    </section>

    <section id="contact">
        <h2><span class="num">11</span> Contact us</h2>
        <p>Questions about this policy or your data? Reach us at:</p>
        <p class="contact-email"><a href="mailto:hello@synqs.site">hello@synqs.site</a></p>
    </section>

@endsection

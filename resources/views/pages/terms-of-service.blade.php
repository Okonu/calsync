<!-- resources/views/pages/terms-of-service.blade.php -->
@extends('layouts.legal')

@section('title', 'Terms of Service')
@section('description', 'The terms that govern using Synqs for calendar sync, booking pages, and community events.')

@section('toc')
    <li><a href="#acceptance">Acceptance of terms</a></li>
    <li><a href="#the-service">The service</a></li>
    <li><a href="#accounts">Accounts &amp; calendar access</a></li>
    <li><a href="#booking-pages">Booking pages &amp; bookings</a></li>
    <li><a href="#communities">Communities &amp; call for speakers</a></li>
    <li><a href="#acceptable-use">Acceptable use</a></li>
    <li><a href="#third-party">Third-party services</a></li>
    <li><a href="#ip">Intellectual property</a></li>
    <li><a href="#disclaimers">Disclaimers &amp; liability</a></li>
    <li><a href="#termination">Termination &amp; deletion</a></li>
    <li><a href="#changes">Changes to these terms</a></li>
    <li><a href="#law">Governing law</a></li>
    <li><a href="#contact">Contact us</a></li>
@endsection

@section('content')

    <section id="acceptance">
        <h2><span class="num">01</span> Acceptance of terms</h2>
        <p>
            By accessing or using Synqs, you agree to be bound by these Terms of Service. If you're using Synqs on
            behalf of an organization or community, you're agreeing on its behalf and confirming you have the
            authority to do so.
        </p>
    </section>

    <section id="the-service">
        <h2><span class="num">02</span> The service</h2>
        <p>Synqs lets you:</p>
        <ul>
            <li>Connect multiple Google and Microsoft calendars and view them in one place</li>
            <li>Publish a public booking page with your availability, meeting length, and buffer time</li>
            <li>Let guests book real open time on your calendar, with automatic conflict checking and optional Google Meet links</li>
            <li>Run a community page with public events and a call-for-speakers process, including reviewing speaker applications</li>
        </ul>
        <p>We may add, change, or remove features over time as the product evolves.</p>
    </section>

    <section id="accounts">
        <h2><span class="num">03</span> Accounts &amp; calendar access</h2>
        <p>
            You sign in to Synqs using Google or Microsoft OAuth &mdash; we don't manage separate Synqs passwords. By
            connecting a calendar account, you authorize Synqs to read your calendar data and to create, update, and
            cancel events on that calendar in order to provide the booking and sync features you've enabled. You're
            responsible for keeping the Google or Microsoft account you sign in with secure.
        </p>
        <p>
            You may disconnect a connected calendar account, or delete your Synqs account entirely, at any time from
            Settings.
        </p>
    </section>

    <section id="booking-pages">
        <h2><span class="num">04</span> Booking pages &amp; bookings</h2>
        <p>
            You're responsible for the availability, durations, and details you configure on your booking page.
            When a guest books time with you, Synqs creates a calendar event on your connected calendar and, if
            requested, generates a Google Meet link. Either side can cancel a confirmed booking; cancellation
            notifications are sent by email and the corresponding calendar event is removed.
        </p>
        <p>
            Synqs checks your connected calendars for conflicts before confirming a booking, but we can't guarantee
            against every edge case &mdash; for example, events added directly on a connected calendar at the same
            moment a booking is being confirmed. You remain responsible for reviewing your calendar.
        </p>
    </section>

    <section id="communities">
        <h2><span class="num">05</span> Communities &amp; call for speakers</h2>
        <p>
            If you create or administer a community, you're responsible for the accuracy of the information you
            publish and for how you handle applications submitted to your call-for-speakers listings, including any
            attachments applicants upload. You agree not to use community or application data you receive through
            Synqs for purposes unrelated to running that community's events.
        </p>
    </section>

    <section id="acceptable-use">
        <h2><span class="num">06</span> Acceptable use</h2>
        <p>You agree not to:</p>
        <ul>
            <li>Use Synqs for any unlawful purpose or in violation of any applicable law</li>
            <li>Attempt to access another user's account, calendar, or community data without authorization</li>
            <li>Interfere with or disrupt the service, or attempt to bypass its security or rate limits</li>
            <li>Use booking pages or community listings to collect information for spam, harassment, or fraud</li>
        </ul>
        <p>We may suspend or terminate access for accounts that violate these terms.</p>
    </section>

    <section id="third-party">
        <h2><span class="num">07</span> Third-party services</h2>
        <p>
            Synqs depends on Google Calendar, Google Meet, and Microsoft's calendar APIs to function. We aren't
            responsible for outages, changes, or limitations in those third-party services that affect Synqs, though
            we'll do our best to work around them.
        </p>
    </section>

    <section id="ip">
        <h2><span class="num">08</span> Intellectual property</h2>
        <p>
            Synqs and its branding are owned by us. You retain ownership of the content you publish through Synqs
            &mdash; booking page details, community pages, event listings, and application content. By publishing
            content through Synqs, you grant us the limited right to store and display it as part of operating the
            service.
        </p>
    </section>

    <section id="disclaimers">
        <h2><span class="num">09</span> Disclaimers &amp; limitation of liability</h2>
        <p>
            Synqs is provided "as is," without warranties of any kind. To the fullest extent permitted by law, we
            aren't liable for indirect, incidental, or consequential damages arising from your use of the service,
            including missed bookings, calendar sync errors, or third-party service outages.
        </p>
    </section>

    <section id="termination">
        <h2><span class="num">10</span> Termination &amp; deletion</h2>
        <p>
            You may delete your account at any time from Settings, which removes your profile, connected calendar
            credentials, booking pages, and community data as described in our
            <a href="{{ route('privacy-policy') }}">Privacy Policy</a>. We may suspend or terminate accounts that
            violate these terms, or if we discontinue the service, with notice where reasonably possible.
        </p>
    </section>

    <section id="changes">
        <h2><span class="num">11</span> Changes to these terms</h2>
        <p>
            We may update these terms as Synqs changes. We'll update the date at the top of this page when we do.
            Continuing to use Synqs after a change means you accept the updated terms.
        </p>
    </section>

    <section id="law">
        <h2><span class="num">12</span> Governing law</h2>
        <p>
            These terms are governed by the laws of the jurisdiction in which Synqs operates, without regard to
            conflict-of-law principles.
        </p>
    </section>

    <section id="contact">
        <h2><span class="num">13</span> Contact us</h2>
        <p>Questions about these terms? Reach us at:</p>
        <p class="contact-email"><a href="mailto:hello@synqs.site">hello@synqs.site</a></p>
    </section>

@endsection

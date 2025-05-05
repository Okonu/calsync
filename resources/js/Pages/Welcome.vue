<!-- resources/js/Pages/Welcome.vue -->
<script setup>
import { Head } from '@inertiajs/vue3';

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
});
</script>

<template>
    <Head title="Synsqs | Smart Calendar Booking" />

    <div class="landing-page">
        <div class="nav-bar">
            <div class="logo-container">
                <div class="logo-icon">
                    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M8 2V5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M16 2V5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M3.5 9.09H20.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M21 8.5V17C21 20 19.5 22 16 22H8C4.5 22 3 20 3 17V8.5C3 5.5 4.5 3.5 8 3.5H16C19.5 3.5 21 5.5 21 8.5Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <h1>Synsqs</h1>
            </div>
            <a href="/auth/google" class="nav-button">Sign in with Google</a>
        </div>

        <div class="content">
            <div class="left-section">
                <h2>One place for all your calendars</h2>
                <p class="tagline">Sync multiple calendars and let others book your available time slots with zero conflicts.</p>

                <div class="features">
                    <div class="feature">
                        <span>✓</span>
                        <p>Connect all your Google accounts and see all your calendars in one unified interface</p>
                    </div>
                    <div class="feature">
                        <span>✓</span>
                        <p>Let others book only your available time slots to prevent double bookings</p>
                    </div>
                    <div class="feature">
                        <span>✓</span>
                        <p>Your calendars stay in sync. Changes from Google Calendar appear automatically</p>
                    </div>
                </div>

                <a href="/auth/google" class="cta-button">
                    <svg viewBox="0 0 24 24" width="20" height="20">
                        <path fill="currentColor" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" />
                        <path fill="currentColor" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" />
                        <path fill="currentColor" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z" />
                        <path fill="currentColor" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" />
                    </svg>
                    Get Started with Google
                </a>
            </div>

            <div class="right-section">
                <div class="calendar-image-container">
                    <img src="/images/synqs.png" alt="Synsqs Calendar Interface" class="calendar-image" />
                    <div class="image-overlay"></div>
                </div>
            </div>
        </div>

        <div class="footer">
            <div class="footer-content">
                <p>© {{ new Date().getFullYear() }} Synsqs</p>
                <div class="footer-links">
                    <a href="/privacy-policy">Privacy Policy</a>
                    <a href="/terms-of-service">Terms of Service</a>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.landing-page {
    min-height: 100vh;
    background-color: #fdf7fb;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
    display: flex;
    flex-direction: column;
}

.nav-bar {
    background-color: white;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    padding: 1rem 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.logo-container {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.logo-icon {
    height: 2.5rem;
    width: 2.5rem;
    background-color: #6366f1;
    color: white;
    border-radius: 8px;
    padding: 0.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
}

h1 {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1e293b;
    margin: 0;
}

.nav-button {
    padding: 0.5rem 1rem;
    background-color: #f8f9fa;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    font-size: 0.875rem;
    color: #4b5563;
    text-decoration: none;
    transition: all 0.2s ease;
}

.nav-button:hover {
    background-color: #f1f5f9;
}

.content {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem;
    max-width: 1200px;
    margin: 0 auto;
    gap: 3rem;
}

.left-section {
    flex: 1;
    max-width: 500px;
}

h2 {
    font-size: 2.5rem;
    font-weight: 800;
    color: #111827;
    margin: 0 0 1rem;
    line-height: 1.2;
}

.tagline {
    font-size: 1.25rem;
    color: #4b5563;
    margin-bottom: 2rem;
    line-height: 1.5;
}

.features {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    margin-bottom: 2rem;
}

.feature {
    display: flex;
    gap: 0.75rem;
    align-items: flex-start;
}

.feature span {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 1.5rem;
    width: 1.5rem;
    background-color: #6366f1;
    color: white;
    border-radius: 50%;
    font-weight: bold;
}

.feature p {
    margin: 0;
    color: #4b5563;
    font-size: 1.125rem;
    line-height: 1.5;
}

.cta-button {
    display: inline-flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.875rem 1.5rem;
    background-color: #6366f1;
    color: white;
    font-weight: 600;
    font-size: 1.125rem;
    border-radius: 8px;
    text-decoration: none;
    transition: all 0.2s ease;
}

.cta-button:hover {
    background-color: #4f46e5;
}

.right-section {
    flex: 1;
    max-width: 600px;
}

.calendar-image-container {
    position: relative;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

.calendar-image {
    width: 100%;
    height: auto;
    display: block;
}

.image-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(to bottom right, rgba(99, 102, 241, 0.1), rgba(99, 102, 241, 0));
    pointer-events: none;
}

.footer {
    padding: 2rem;
    background-color: white;
    border-top: 1px solid #e2e8f0;
}

.footer-content {
    max-width: 1200px;
    margin: 0 auto;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.footer p {
    margin: 0;
    color: #64748b;
}

.footer-links {
    display: flex;
    gap: 1.5rem;
}

.footer-links a {
    color: #64748b;
    text-decoration: none;
    transition: color 0.2s ease;
}

.footer-links a:hover {
    color: #6366f1;
}

@media (max-width: 768px) {
    .content {
        flex-direction: column;
        padding: 2rem 1rem;
    }

    .left-section {
        max-width: 100%;
    }

    h2 {
        font-size: 2rem;
    }

    .right-section {
        max-width: 100%;
    }

    .footer-content {
        flex-direction: column;
        gap: 1rem;
        text-align: center;
    }
}
</style>

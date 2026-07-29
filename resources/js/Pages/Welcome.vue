<!-- resources/js/Pages/Welcome.vue -->
<script setup>
import { Head } from '@inertiajs/vue3';
import { onMounted, onBeforeUnmount, ref } from 'vue';

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
});

const revealRoot = ref(null);

onMounted(() => {
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const targets = revealRoot.value?.querySelectorAll('.reveal') ?? [];

    if (prefersReducedMotion || !('IntersectionObserver' in window)) {
        targets.forEach((el) => el.classList.add('is-visible'));
        return;
    }

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        },
        { threshold: 0.15, rootMargin: '0px 0px -40px 0px' },
    );

    targets.forEach((el) => observer.observe(el));

    onBeforeUnmount(() => observer.disconnect());
});
</script>

<template>
    <Head title="Synqs | Calendar sync &amp; booking, without the double-booking">
        <link rel="preconnect" href="https://fonts.bunny.net" />
        <link href="https://fonts.bunny.net/css?family=space-grotesk:500,600,700|jetbrains-mono:500,600&display=swap" rel="stylesheet" />
        <meta name="description" content="Synqs syncs every Google and Microsoft calendar you own into one view, then lets people book only the time you actually have. Zero double-bookings, one link." />
    </Head>

    <div class="page" ref="revealRoot">
        <!-- Nav -->
        <header class="nav">
            <div class="nav-inner">
                <div class="brand">
                    <span class="brand-mark" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M8 2V5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M16 2V5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M3.5 9.09H20.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M21 8.5V17C21 20 19.5 22 16 22H8C4.5 22 3 20 3 17V8.5C3 5.5 4.5 3.5 8 3.5H16C19.5 3.5 21 5.5 21 8.5Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                    <span class="brand-name">Synqs</span>
                </div>

                <nav class="nav-links" aria-label="Page sections">
                    <a href="#features">Features</a>
                    <a href="#how-it-works">How it works</a>
                    <a href="#communities">Communities</a>
                </nav>

                <a href="/auth/google" class="nav-cta">Sign in with Google</a>
            </div>
        </header>

        <main>
            <!-- Hero -->
            <section class="hero">
                <div class="hero-inner">
                    <div class="hero-copy">
                        <p class="eyebrow"><span class="pulse-dot" aria-hidden="true"></span>LIVE SYNC ACROSS EVERY CALENDAR</p>
                        <h1>
                            Your calendars,<br />
                            <span class="accent">finally in sync.</span><br />
                            Never double-booked.
                        </h1>
                        <p class="hero-lede">
                            Connect every Google and Microsoft calendar you own, share one booking link,
                            and let people reserve only the time you actually have free.
                        </p>
                        <div class="hero-actions">
                            <a href="/auth/google" class="btn btn-primary">
                                <svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true">
                                    <path fill="currentColor" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" />
                                    <path fill="currentColor" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" />
                                    <path fill="currentColor" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z" />
                                    <path fill="currentColor" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" />
                                </svg>
                                Continue with Google
                            </a>
                            <a href="#how-it-works" class="btn btn-ghost">See how it works</a>
                        </div>

                        <dl class="stat-row">
                            <div class="stat">
                                <dt>2</dt>
                                <dd>Calendar providers, one view</dd>
                            </div>
                            <div class="stat">
                                <dt>0</dt>
                                <dd>Double-bookings once synced</dd>
                            </div>
                            <div class="stat">
                                <dt>1</dt>
                                <dd>Link to share, ever</dd>
                            </div>
                        </dl>
                    </div>

                    <div class="hero-visual reveal">
                        <div class="product-frame">
                            <div class="frame-bar">
                                <span class="frame-dots" aria-hidden="true"><i></i><i></i><i></i></span>
                                <span class="frame-url">synqs.site/calendar</span>
                                <span class="synced-pill"><span class="pulse-dot" aria-hidden="true"></span>Synced</span>
                            </div>
                            <img
                                src="/images/synqs.png"
                                alt="Synqs calendar view showing three connected Google accounts and every calendar merged into one color-coded month view"
                                class="product-shot"
                                width="1125"
                                height="825"
                                loading="eager"
                            />
                        </div>
                    </div>
                </div>
            </section>

            <!-- Problem -->
            <section class="problem">
                <div class="section-inner">
                    <p class="eyebrow eyebrow-warn reveal">SOUND FAMILIAR?</p>
                    <h2 class="reveal">Double-booked. <span class="accent-warn">Again.</span></h2>
                    <p class="section-lede reveal">
                        None of this is a you problem &mdash; it's what happens when three calendars never talk to each other.
                    </p>

                    <div class="finding-card reveal">
                        <div class="finding-head">
                            <span>Before Synqs</span>
                            <span class="finding-badge">3 conflicts</span>
                        </div>
                        <div class="finding-row">
                            <span class="finding-code">DBL&#8209;01</span>
                            <div>
                                <p class="finding-title">Double-booked with a client call</p>
                                <p class="finding-desc">A meeting invite landed on top of one already on your personal calendar.</p>
                            </div>
                        </div>
                        <div class="finding-row">
                            <span class="finding-code">VIS&#8209;01</span>
                            <div>
                                <p class="finding-title">No single view across your calendars</p>
                                <p class="finding-desc">Work, personal, and community calendars each tell a different story.</p>
                            </div>
                        </div>
                        <div class="finding-row">
                            <span class="finding-code">MAN&#8209;01</span>
                            <div>
                                <p class="finding-title">Manual back-and-forth to find a time</p>
                                <p class="finding-desc">Five emails to agree on a slot that a shared link could've handled.</p>
                            </div>
                        </div>
                        <div class="finding-resolved">
                            Synqs checks every connected calendar before a single time slot is ever offered.
                        </div>
                    </div>
                </div>
            </section>

            <!-- Features -->
            <section id="features" class="features">
                <div class="section-inner">
                    <p class="eyebrow reveal">WHAT SYNQS DOES</p>
                    <h2 class="reveal">Everything scheduling needs, connected.</h2>

                    <div class="feature-grid">
                        <div class="feature-card reveal">
                            <span class="feature-tag">SYNC</span>
                            <h3>Multi-calendar sync</h3>
                            <p>Connect every Google and Microsoft calendar you own and see them as one unified, color-coded view.</p>
                        </div>
                        <div class="feature-card reveal">
                            <span class="feature-tag">BOOK</span>
                            <h3>Public booking pages</h3>
                            <p>Set your availability, meeting length, and buffer time once. Guests only ever see real open slots.</p>
                        </div>
                        <div class="feature-card reveal">
                            <span class="feature-tag">MEET</span>
                            <h3>Instant meeting links</h3>
                            <p>Every booking can auto-generate a Google Meet link, with confirmations sent to both sides.</p>
                        </div>
                        <div class="feature-card reveal">
                            <span class="feature-tag">EVENTS</span>
                            <h3>Community &amp; speaker events</h3>
                            <p>Run a community calendar, publish events, and manage call-for-speaker applications in one place.</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- How it works -->
            <section id="how-it-works" class="how">
                <div class="section-inner">
                    <p class="eyebrow reveal">HOW IT WORKS</p>
                    <h2 class="reveal">Three steps, then it runs itself.</h2>

                    <ol class="steps">
                        <li class="step reveal">
                            <span class="step-num">01</span>
                            <div>
                                <h3>Connect your calendars</h3>
                                <p>Sign in with Google &mdash; add Microsoft too &mdash; and Synqs pulls in every calendar you choose to share.</p>
                            </div>
                        </li>
                        <li class="step reveal">
                            <span class="step-num">02</span>
                            <div>
                                <h3>Share one link</h3>
                                <p>Set your hours, meeting length, and buffer time once. Your booking link stays the same from then on.</p>
                            </div>
                        </li>
                        <li class="step reveal">
                            <span class="step-num">03</span>
                            <div>
                                <h3>Get booked, never doubled</h3>
                                <p>Synqs cross-checks every connected calendar before confirming, so nothing you own ever collides.</p>
                            </div>
                        </li>
                    </ol>
                </div>
            </section>

            <!-- Communities (inverted) -->
            <section id="communities" class="communities">
                <div class="section-inner">
                    <p class="eyebrow eyebrow-inverted reveal">BEYOND BOOKING</p>
                    <h2 class="reveal">Run your community's calendar too.</h2>
                    <p class="section-lede lede-inverted reveal">
                        Communities on Synqs get a public events page, a structured call-for-speakers with application
                        review, and a shared calendar that stays in sync with everything else you've connected.
                    </p>
                    <div class="community-tags reveal">
                        <span>Public event pages</span>
                        <span>Call for speakers</span>
                        <span>Application review</span>
                        <span>Shared calendar</span>
                    </div>
                </div>
            </section>

            <!-- Final CTA -->
            <section class="cta-final">
                <div class="section-inner cta-inner reveal">
                    <h2>Stop reconciling calendars by hand.</h2>
                    <a href="/auth/google" class="btn btn-primary btn-large">
                        <svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true">
                            <path fill="currentColor" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" />
                            <path fill="currentColor" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" />
                            <path fill="currentColor" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z" />
                            <path fill="currentColor" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" />
                        </svg>
                        Continue with Google
                    </a>
                    <p class="cta-note">Takes about 30 seconds. No credit card, no setup calls.</p>
                </div>
            </section>
        </main>

        <footer class="footer">
            <div class="footer-inner">
                <p>&copy; {{ new Date().getFullYear() }} Synqs</p>
                <div class="footer-links">
                    <a href="/privacy-policy">Privacy Policy</a>
                    <a href="/terms-of-service">Terms of Service</a>
                </div>
            </div>
        </footer>
    </div>
</template>

<style scoped>
.page {
    --ink: #0e0e1a;
    --ink-mute: #63636f;
    --paper: #fafaf8;
    --paper-dim: #f1f0ec;
    --line: #e4e2dc;
    --indigo: #4f46e5;
    --indigo-deep: #3730a3;
    --indigo-soft: #eef0ff;
    --mint: #16a34a;
    --mint-soft: #ecfdf3;
    --warn: #b45309;

    min-height: 100vh;
    background: var(--paper);
    color: var(--ink);
    font-family: 'Figtree', -apple-system, BlinkMacSystemFont, sans-serif;
    display: flex;
    flex-direction: column;
}

h1, h2, h3 {
    font-family: 'Space Grotesk', 'Figtree', sans-serif;
    letter-spacing: -0.02em;
    color: var(--ink);
    margin: 0;
}

.eyebrow {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    font-family: 'JetBrains Mono', ui-monospace, monospace;
    font-size: 0.75rem;
    font-weight: 600;
    letter-spacing: 0.12em;
    color: var(--indigo);
    margin: 0 0 1.25rem;
}

.eyebrow-warn { color: var(--warn); }
.eyebrow-inverted { color: #a5b4fc; }

.pulse-dot {
    width: 0.5rem;
    height: 0.5rem;
    border-radius: 50%;
    background: var(--mint);
    box-shadow: 0 0 0 0 rgba(22, 163, 74, 0.5);
    animation: pulse 2s infinite;
    flex: none;
}

@keyframes pulse {
    0% { box-shadow: 0 0 0 0 rgba(22, 163, 74, 0.45); }
    70% { box-shadow: 0 0 0 8px rgba(22, 163, 74, 0); }
    100% { box-shadow: 0 0 0 0 rgba(22, 163, 74, 0); }
}

.reveal {
    opacity: 0;
    transform: translateY(16px);
    transition: opacity 0.6s ease, transform 0.6s ease;
}

.reveal.is-visible {
    opacity: 1;
    transform: translateY(0);
}

/* ---------- Nav ---------- */
.nav {
    position: sticky;
    top: 0;
    z-index: 20;
    background: rgba(250, 250, 248, 0.85);
    backdrop-filter: blur(8px);
    border-bottom: 1px solid var(--line);
}

.nav-inner {
    max-width: 1180px;
    margin: 0 auto;
    padding: 1rem 2rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1.5rem;
}

.brand {
    display: flex;
    align-items: center;
    gap: 0.625rem;
}

.brand-mark {
    height: 2.25rem;
    width: 2.25rem;
    background: var(--indigo);
    color: white;
    border-radius: 8px;
    padding: 0.45rem;
    display: flex;
    align-items: center;
    justify-content: center;
}

.brand-name {
    font-family: 'Space Grotesk', sans-serif;
    font-size: 1.25rem;
    font-weight: 700;
}

.nav-links {
    display: flex;
    gap: 2rem;
    font-size: 0.9rem;
    color: var(--ink-mute);
}

.nav-links a {
    color: inherit;
    text-decoration: none;
    transition: color 0.15s ease;
}

.nav-links a:hover,
.nav-links a:focus-visible {
    color: var(--indigo);
}

.nav-cta {
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--ink);
    text-decoration: none;
    border: 1px solid var(--line);
    padding: 0.5rem 1rem;
    border-radius: 8px;
    white-space: nowrap;
    transition: border-color 0.15s ease, background-color 0.15s ease;
}

.nav-cta:hover,
.nav-cta:focus-visible {
    border-color: var(--indigo);
    background: var(--indigo-soft);
}

/* ---------- Buttons ---------- */
.btn {
    display: inline-flex;
    align-items: center;
    gap: 0.625rem;
    padding: 0.8rem 1.4rem;
    border-radius: 9px;
    font-weight: 600;
    font-size: 1rem;
    text-decoration: none;
    transition: transform 0.15s ease, background-color 0.15s ease, box-shadow 0.15s ease;
}

.btn:focus-visible {
    outline: 2px solid var(--indigo);
    outline-offset: 2px;
}

.btn-primary {
    background: var(--indigo);
    color: white;
    box-shadow: 0 8px 20px -8px rgba(79, 70, 229, 0.55);
}

.btn-primary:hover {
    background: var(--indigo-deep);
    transform: translateY(-1px);
}

.btn-ghost {
    color: var(--ink);
    border: 1px solid var(--line);
}

.btn-ghost:hover {
    border-color: var(--indigo);
    color: var(--indigo);
}

.btn-large {
    padding: 1rem 1.75rem;
    font-size: 1.05rem;
}

/* ---------- Hero ---------- */
.hero-inner {
    max-width: 1180px;
    margin: 0 auto;
    padding: 4.5rem 2rem 5rem;
    display: grid;
    grid-template-columns: 1.05fr 0.95fr;
    gap: 4rem;
    align-items: center;
}

.hero-copy h1 {
    font-size: 3.25rem;
    font-weight: 700;
    line-height: 1.08;
    margin-bottom: 1.5rem;
}

.accent { color: var(--indigo); }
.accent-warn { color: var(--warn); }

.hero-lede {
    font-size: 1.1rem;
    color: var(--ink-mute);
    line-height: 1.6;
    max-width: 34rem;
    margin: 0 0 2rem;
}

.hero-actions {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
    margin-bottom: 2.75rem;
}

.stat-row {
    display: flex;
    gap: 2.5rem;
    padding-top: 1.75rem;
    border-top: 1px solid var(--line);
    margin: 0;
}

.stat dt {
    font-family: 'Space Grotesk', sans-serif;
    font-size: 1.75rem;
    font-weight: 700;
    color: var(--ink);
}

.stat dd {
    margin: 0.15rem 0 0;
    font-size: 0.8rem;
    color: var(--ink-mute);
    max-width: 9rem;
}

/* ---------- Hero visual: real product shot ---------- */
.product-frame {
    background: white;
    border: 1px solid var(--line);
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 24px 48px -24px rgba(14, 14, 26, 0.18);
}

.frame-bar {
    display: flex;
    align-items: center;
    gap: 0.85rem;
    padding: 0.7rem 1rem;
    border-bottom: 1px solid var(--line);
    background: var(--paper-dim);
}

.frame-dots {
    display: inline-flex;
    gap: 0.3rem;
    flex: none;
}

.frame-dots i {
    width: 0.55rem;
    height: 0.55rem;
    border-radius: 50%;
    background: var(--line);
}

.frame-url {
    font-family: 'JetBrains Mono', monospace;
    font-size: 0.75rem;
    color: var(--ink-mute);
    flex: 1;
}

.synced-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    background: var(--mint-soft);
    color: var(--mint);
    padding: 0.25rem 0.6rem;
    border-radius: 999px;
    font-size: 0.7rem;
    font-family: 'JetBrains Mono', monospace;
    flex: none;
}

.product-shot {
    display: block;
    width: 100%;
    height: auto;
}

/* ---------- Shared section layout ---------- */
.section-inner {
    max-width: 1180px;
    margin: 0 auto;
    padding: 5.5rem 2rem;
}

.problem .section-inner,
.features .section-inner,
.how .section-inner {
    border-top: 1px solid var(--line);
}

h2 {
    font-size: 2.25rem;
    font-weight: 700;
    line-height: 1.15;
    max-width: 30rem;
    margin-bottom: 1rem;
}

.section-lede {
    font-size: 1.05rem;
    color: var(--ink-mute);
    max-width: 34rem;
    line-height: 1.6;
    margin-bottom: 2.5rem;
}

/* ---------- Problem / finding card ---------- */
.finding-card {
    background: white;
    border: 1px solid var(--line);
    border-radius: 14px;
    max-width: 46rem;
    overflow: hidden;
}

.finding-head {
    display: flex;
    justify-content: space-between;
    padding: 1rem 1.5rem;
    font-size: 0.85rem;
    font-weight: 600;
    border-bottom: 1px solid var(--line);
}

.finding-badge {
    font-family: 'JetBrains Mono', monospace;
    font-size: 0.7rem;
    background: #fef3ea;
    color: var(--warn);
    padding: 0.15rem 0.55rem;
    border-radius: 999px;
}

.finding-row {
    display: flex;
    gap: 1.25rem;
    padding: 1.1rem 1.5rem;
    border-bottom: 1px solid var(--line);
}

.finding-code {
    flex: none;
    font-family: 'JetBrains Mono', monospace;
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--warn);
    padding-top: 0.15rem;
}

.finding-title {
    font-weight: 600;
    margin: 0 0 0.2rem;
}

.finding-desc {
    color: var(--ink-mute);
    font-size: 0.9rem;
    margin: 0;
}

.finding-resolved {
    padding: 1.1rem 1.5rem;
    background: var(--indigo-soft);
    color: var(--indigo-deep);
    font-size: 0.9rem;
    font-weight: 600;
}

/* ---------- Features ---------- */
.feature-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1.25rem;
}

.feature-card {
    background: white;
    border: 1px solid var(--line);
    border-radius: 14px;
    padding: 1.5rem;
}

.feature-tag {
    display: inline-block;
    font-family: 'JetBrains Mono', monospace;
    font-size: 0.7rem;
    font-weight: 600;
    letter-spacing: 0.06em;
    color: var(--indigo);
    background: var(--indigo-soft);
    padding: 0.2rem 0.55rem;
    border-radius: 6px;
    margin-bottom: 1rem;
}

.feature-card h3 {
    font-size: 1.1rem;
    margin-bottom: 0.5rem;
}

.feature-card p {
    color: var(--ink-mute);
    font-size: 0.9rem;
    line-height: 1.55;
    margin: 0;
}

/* ---------- How it works ---------- */
.steps {
    list-style: none;
    margin: 0;
    padding: 0;
    max-width: 40rem;
    display: flex;
    flex-direction: column;
    gap: 2.25rem;
}

.step {
    display: flex;
    gap: 1.5rem;
    align-items: flex-start;
}

.step-num {
    flex: none;
    font-family: 'Space Grotesk', sans-serif;
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--indigo);
    width: 3rem;
}

.step h3 {
    font-size: 1.15rem;
    margin-bottom: 0.35rem;
}

.step p {
    color: var(--ink-mute);
    margin: 0;
    line-height: 1.55;
}

/* ---------- Communities (inverted) ---------- */
.communities {
    background: var(--ink);
}

.communities h2,
.communities h3 {
    color: white;
}

.lede-inverted {
    color: #c7c7d6;
}

.community-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
}

.community-tags span {
    font-family: 'JetBrains Mono', monospace;
    font-size: 0.75rem;
    color: #c7d2fe;
    border: 1px solid rgba(199, 210, 254, 0.3);
    padding: 0.4rem 0.85rem;
    border-radius: 999px;
}

/* ---------- Final CTA ---------- */
.cta-final .section-inner {
    border-top: 1px solid var(--line);
}

.cta-inner {
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1.5rem;
}

.cta-inner h2 {
    max-width: none;
}

.cta-note {
    font-size: 0.85rem;
    color: var(--ink-mute);
    margin: 0;
}

/* ---------- Footer ---------- */
.footer {
    border-top: 1px solid var(--line);
    background: var(--paper);
}

.footer-inner {
    max-width: 1180px;
    margin: 0 auto;
    padding: 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.footer-inner p {
    margin: 0;
    color: var(--ink-mute);
}

.footer-links {
    display: flex;
    gap: 1.5rem;
}

.footer-links a {
    color: var(--ink-mute);
    text-decoration: none;
}

.footer-links a:hover {
    color: var(--indigo);
}

/* ---------- Responsive ---------- */
@media (max-width: 900px) {
    .hero-inner {
        grid-template-columns: 1fr;
        padding: 3rem 1.5rem 3.5rem;
        gap: 2.5rem;
    }

    .nav-links { display: none; }

    .hero-copy h1 { font-size: 2.4rem; }

    .stat-row { gap: 1.5rem; flex-wrap: wrap; }

    .feature-grid { grid-template-columns: repeat(2, 1fr); }

    .section-inner { padding: 3.5rem 1.5rem; }
}

@media (max-width: 560px) {
    .feature-grid { grid-template-columns: 1fr; }
    .footer-inner { flex-direction: column; gap: 1rem; text-align: center; }
    .hero-copy h1 { font-size: 2rem; }
}
</style>

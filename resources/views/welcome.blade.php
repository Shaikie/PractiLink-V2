<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="description" content="PractiLink helps students manage practical training applications, documents, reviews and placement progress in one secure platform.">
    <meta name="robots" content="index,follow">
    <link rel="canonical" href="{{ url('/') }}">
    <meta property="og:type" content="website">
    <meta property="og:title" content="PractiLink | Practical Training Platform">
    <meta property="og:description" content="Manage practical training applications, documents and placement progress in one secure platform.">
    <meta property="og:url" content="{{ url('/') }}">
    <meta property="og:image" content="{{ asset('images/og-practilink.svg') }}">
    <meta name="twitter:card" content="summary_large_image">
    <title>PractiLink | Practical Training Platform</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.15.4/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/practilink.css') }}">
    <link rel="stylesheet" href="{{ asset('css/skeuomorphism.css') }}">
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "WebApplication",
        "name": "PractiLink",
        "applicationCategory": "EducationApplication",
        "description": "Platform for managing practical training applications, documents and placement progress."
    }
    </script>
</head>
<body class="pl-auth-page">
    <div class="pl-auth-shell">
        <header class="pl-auth-nav">
            <a href="{{ route('home') }}" class="pl-auth-brand">
                <span class="pl-brand-mark">P</span>
                <span>Practi<span>Link</span></span>
            </a>
            <a href="{{ route('login') }}" class="btn btn-sm pl-btn-outline">Sign in</a>
        </header>

        <main class="pl-auth-main" style="grid-template-columns: minmax(0, 1.35fr) minmax(280px, .65fr); gap: 3rem;">
            <section class="pl-hero-copy">
                <div class="pl-eyebrow"><span></span>PRACTICAL TRAINING</div>
                <h1>Practical training, <strong>connected.</strong></h1>
                <p class="pl-hero-text">Apply for training, submit documents, follow review progress and keep your placement journey organized in one secure workspace.</p>
                <div class="d-flex flex-wrap" style="gap: .75rem;">
                    <a href="{{ route('register') }}" class="btn pl-btn-primary px-4 py-2">Create student account</a>
                    <a href="{{ route('login') }}" class="btn pl-btn-outline px-4 py-2">Sign in</a>
                </div>
            </section>

            <aside class="pl-login-column" style="max-width: 390px;">
                <div class="pl-login-card">
                    <div class="pl-login-card-top">
                        <div class="pl-login-icon"><i class="fas fa-route"></i></div>
                        <div>
                            <div class="pl-login-kicker">ONE WORKSPACE</div>
                            <h2>Everything in one place</h2>
                        </div>
                    </div>
                    <div class="mt-4">
                        <div class="pl-feature-item mb-3">
                            <span class="pl-feature-icon blue"><i class="fas fa-file-alt"></i></span>
                            <div>
                                <strong>Applications</strong>
                                <small>Create and track your practical training application.</small>
                            </div>
                        </div>
                        <div class="pl-feature-item mb-3">
                            <span class="pl-feature-icon green"><i class="fas fa-folder-open"></i></span>
                            <div>
                                <strong>Documents</strong>
                                <small>Upload, preview and securely download application files.</small>
                            </div>
                        </div>
                        <div class="pl-feature-item">
                            <span class="pl-feature-icon blue"><i class="fas fa-bell"></i></span>
                            <div>
                                <strong>Progress</strong>
                                <small>Stay informed as your application moves through review.</small>
                            </div>
                        </div>
                    </div>
                </div>
            </aside>
        </main>

        <footer class="pl-auth-footer">
            <span>© {{ date('Y') }} PractiLink</span>
            <span>Practical training, connected.</span>
        </footer>
    </div>
</body>
</html>

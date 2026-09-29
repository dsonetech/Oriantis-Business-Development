<?php
$config = require __DIR__ . '/../config/app.php';

$langCode = $_GET['lang'] ?? $config['default_language'];
if (!in_array($langCode, $config['supported_languages'], true)) {
    $langCode = $config['default_language'];
}

$lang = require __DIR__ . '/../lang/' . $langCode . '.php';

function t(array $lang, string $key, string $fallback = ''): string {
    return htmlspecialchars($lang[$key] ?? $fallback ?: $key);
}
?>
<!doctype html>
<html lang="<?= htmlspecialchars($langCode) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($config['app_name']) ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="/assets/css/app.css" rel="stylesheet">
</head>
<body>
<div class="app-shell">

    <aside class="sidebar d-flex flex-column">
        <div class="brand-wrap">
            <div class="brand-mark">O</div>
            <div>
                <div class="brand-title">Oriantis</div>
                <div class="brand-subtitle">Business Development</div>
            </div>
        </div>

        <div class="sidebar-label">MENU</div>

        <nav class="nav flex-column sidebar-nav">
            <a class="nav-link active" href="#">
                <i class="bi bi-grid-1x2-fill"></i>
                <span><?= t($lang, 'dashboard', 'Dashboard') ?></span>
            </a>

            <a class="nav-link" href="#">
                <i class="bi bi-building"></i>
                <span><?= t($lang, 'hotels', 'Hotels') ?></span>
            </a>

            <a class="nav-link" href="#">
                <i class="bi bi-briefcase"></i>
                <span><?= t($lang, 'services', 'Services') ?></span>
            </a>

            <a class="nav-link" href="#">
                <i class="bi bi-people"></i>
                <span><?= t($lang, 'agencies', 'Agencies') ?></span>
            </a>

            <div class="sidebar-label mt-4">QUOTES</div>

            <a class="nav-link" href="#">
                <i class="bi bi-file-earmark-text"></i>
                <span><?= t($lang, 'quotes', 'Quotes') ?></span>
            </a>

            <a class="nav-link" href="#">
                <i class="bi bi-file-earmark-plus"></i>
                <span><?= t($lang, 'new_quote', 'New Quote') ?></span>
            </a>

            <div class="sidebar-label mt-4">SYSTEM</div>

            <a class="nav-link" href="#">
                <i class="bi bi-currency-exchange"></i>
                <span><?= t($lang, 'exchange_rates', 'Exchange Rates') ?></span>
            </a>

            <a class="nav-link" href="#">
                <i class="bi bi-gear"></i>
                <span><?= t($lang, 'settings', 'Settings') ?></span>
            </a>
        </nav>

        <div class="mt-auto sidebar-footer">
            <div class="small text-white-50">Oriantis Quote Management</div>
            <div class="small text-white-50">PHP + MySQL</div>
        </div>
    </aside>

    <main class="main-content">
        <header class="topbar">
            <div>
                <div class="page-eyebrow">ORIANTIS BUSINESS DEVELOPMENT</div>
                <h1 class="page-title mb-0"><?= t($lang, 'dashboard', 'Dashboard') ?></h1>
            </div>

            <div class="d-flex align-items-center gap-3">
                <div class="language-switcher btn-group">
                    <a href="?lang=en" class="btn btn-sm <?= $langCode === 'en' ? 'btn-primary' : 'btn-light border' ?>">EN</a>
                    <a href="?lang=fr" class="btn btn-sm <?= $langCode === 'fr' ? 'btn-primary' : 'btn-light border' ?>">FR</a>
                </div>

                <button class="btn btn-light border rounded-circle icon-btn">
                    <i class="bi bi-bell"></i>
                </button>

                <div class="user-chip">
                    <div class="avatar">OB</div>
                    <div class="d-none d-md-block">
                        <div class="fw-semibold small">Oriantis</div>
                        <div class="text-muted user-role">Administrator</div>
                    </div>
                </div>
            </div>
        </header>

        <section class="container-fluid px-0">

            <div class="hero-card mb-4">
                <div>
                    <span class="badge text-bg-primary-subtle text-primary mb-2">Quote Management</span>
                    <h2 class="mb-2">Welcome to Oriantis</h2>
                    <p class="mb-0 text-secondary">
                        Manage hotels, purchase rates, services, currencies and customer quotations from one workspace.
                    </p>
                </div>
                <a href="#" class="btn btn-primary px-4">
                    <i class="bi bi-plus-lg me-2"></i><?= t($lang, 'new_quote', 'New Quote') ?>
                </a>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-12 col-md-6 col-xl-3">
                    <div class="stat-card h-100">
                        <div class="stat-icon bg-primary-subtle text-primary"><i class="bi bi-building"></i></div>
                        <div class="stat-label"><?= t($lang, 'hotels', 'Hotels') ?></div>
                        <div class="stat-value">0</div>
                        <div class="stat-meta">Active suppliers</div>
                    </div>
                </div>

                <div class="col-12 col-md-6 col-xl-3">
                    <div class="stat-card h-100">
                        <div class="stat-icon bg-success-subtle text-success"><i class="bi bi-file-earmark-text"></i></div>
                        <div class="stat-label"><?= t($lang, 'quotes', 'Quotes') ?></div>
                        <div class="stat-value">0</div>
                        <div class="stat-meta">Created quotations</div>
                    </div>
                </div>

                <div class="col-12 col-md-6 col-xl-3">
                    <div class="stat-card h-100">
                        <div class="stat-icon bg-warning-subtle text-warning"><i class="bi bi-people"></i></div>
                        <div class="stat-label"><?= t($lang, 'agencies', 'Agencies') ?></div>
                        <div class="stat-value">0</div>
                        <div class="stat-meta">B2B customers</div>
                    </div>
                </div>

                <div class="col-12 col-md-6 col-xl-3">
                    <div class="stat-card h-100">
                        <div class="stat-icon bg-info-subtle text-info"><i class="bi bi-currency-exchange"></i></div>
                        <div class="stat-label"><?= t($lang, 'exchange_rates', 'Exchange Rates') ?></div>
                        <div class="stat-value">4</div>
                        <div class="stat-meta">QAR · USD · EUR · DZD</div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-12 col-xl-8">
                    <div class="content-card h-100">
                        <div class="card-head">
                            <div>
                                <h5 class="mb-1">Recent Quotes</h5>
                                <div class="text-muted small">Latest quotations created in Oriantis</div>
                            </div>
                            <a href="#" class="btn btn-sm btn-outline-primary">View all</a>
                        </div>

                        <div class="empty-state">
                            <div class="empty-icon"><i class="bi bi-file-earmark-plus"></i></div>
                            <h6>No quotes yet</h6>
                            <p>Create your first quotation to start using the platform.</p>
                            <a href="#" class="btn btn-primary btn-sm">
                                <i class="bi bi-plus-lg me-1"></i> New Quote
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-xl-4">
                    <div class="content-card h-100">
                        <div class="card-head">
                            <div>
                                <h5 class="mb-1">Quick Actions</h5>
                                <div class="text-muted small">Common operations</div>
                            </div>
                        </div>

                        <div class="quick-actions">
                            <a href="#" class="quick-action">
                                <span class="quick-icon"><i class="bi bi-building-add"></i></span>
                                <span>
                                    <strong>Add Hotel</strong>
                                    <small>Add hotel and room purchase rates</small>
                                </span>
                                <i class="bi bi-chevron-right ms-auto"></i>
                            </a>

                            <a href="#" class="quick-action">
                                <span class="quick-icon"><i class="bi bi-plus-square"></i></span>
                                <span>
                                    <strong>Add Service</strong>
                                    <small>Transfer, safari, city tour and more</small>
                                </span>
                                <i class="bi bi-chevron-right ms-auto"></i>
                            </a>

                            <a href="#" class="quick-action">
                                <span class="quick-icon"><i class="bi bi-file-earmark-plus"></i></span>
                                <span>
                                    <strong>New Quote</strong>
                                    <small>Create a multi-currency quotation</small>
                                </span>
                                <i class="bi bi-chevron-right ms-auto"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

        </section>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

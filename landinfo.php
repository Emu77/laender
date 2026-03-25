<?php
$name  = trim($_GET['name'] ?? '');
$data  = null;
$error = '';

if ($name === '') {
    $error = "Kein Ländername übergeben.";
} else {
    $apiUrl = "https://restcountries.com/v3.1/name/" . urlencode($name) . "?fullText=true";
    $raw    = @file_get_contents($apiUrl);
    if ($raw === false) {
        // fallback: partial match
        $apiUrl = "https://restcountries.com/v3.1/name/" . urlencode($name);
        $raw    = @file_get_contents($apiUrl);
    }
    if ($raw === false) {
        $error = "Verbindung zur API fehlgeschlagen.";
    } else {
        $decoded = json_decode($raw, true);
        if (isset($decoded['status']) && $decoded['status'] === 404) {
            $error = "Land „" . htmlspecialchars($name) . " nicht gefunden.";
        } else {
            // pick first match
            $data = $decoded[0] ?? null;
            if (!$data) $error = "Keine Daten erhalten.";
        }
    }
}

// helpers
function fmt_number($n) {
    return number_format($n ?? 0, 0, ',', '.');
}
function fmt_area($a) {
    if (!$a) return '–';
    return number_format($a, 0, ',', '.') . ' km²';
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Länder-Info – <?= htmlspecialchars($data['name']['common'] ?? $name) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;700;800&family=Manrope:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg: #0d0f14;
            --surface: #161820;
            --border: #2a2d38;
            --accent: #e8c84a;
            --accent2: #4a8fe8;
            --text: #eeeef0;
            --muted: #7a7d8a;
            --radius: 12px;
        }

        body {
            font-family: 'Manrope', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
        }

        header {
            padding: 2.5rem 2rem 1.5rem;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
            position: sticky;
            top: 0;
            background: rgba(13,15,20,0.92);
            backdrop-filter: blur(12px);
            z-index: 100;
        }

        .logo {
            font-family: 'Syne', sans-serif;
            font-size: 1.6rem;
            font-weight: 800;
            letter-spacing: -0.03em;
        }

        .logo span { color: var(--accent); }

        nav a {
            color: var(--muted);
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            margin-left: 1.5rem;
            transition: color 0.2s;
        }

        nav a:hover { color: var(--accent); }

        main {
            max-width: 900px;
            margin: 0 auto;
            padding: 3rem 2rem;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            color: var(--muted);
            text-decoration: none;
            font-size: 0.85rem;
            margin-bottom: 2rem;
            transition: color 0.2s;
        }

        .back-link:hover { color: var(--accent); }

        .error {
            background: rgba(232,80,80,0.12);
            border: 1px solid rgba(232,80,80,0.35);
            color: #f08080;
            border-radius: var(--radius);
            padding: 1rem 1.2rem;
            font-size: 0.9rem;
        }

        /* Hero */
        .hero {
            display: flex;
            gap: 2rem;
            align-items: flex-start;
            margin-bottom: 2.5rem;
            flex-wrap: wrap;
        }

        .hero-flag {
            flex-shrink: 0;
            width: 260px;
        }

        .hero-flag img {
            width: 100%;
            aspect-ratio: 3/2;
            object-fit: cover;
            border-radius: var(--radius);
            border: 1px solid var(--border);
            box-shadow: 0 8px 32px rgba(0,0,0,0.5);
        }

        .hero-title {
            flex: 1;
            min-width: 220px;
        }

        .hero-title h1 {
            font-family: 'Syne', sans-serif;
            font-size: 2.8rem;
            font-weight: 800;
            letter-spacing: -0.04em;
            line-height: 1.05;
            margin-bottom: 0.4rem;
        }

        .native-name {
            color: var(--muted);
            font-size: 1rem;
            margin-bottom: 1rem;
        }

        .tag {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            background: rgba(232,200,74,0.12);
            border: 1px solid rgba(232,200,74,0.3);
            color: var(--accent);
            border-radius: 999px;
            font-size: 0.78rem;
            font-weight: 600;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            margin-right: 0.4rem;
            margin-bottom: 0.4rem;
        }

        /* Stats grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 1px;
            background: var(--border);
            border-radius: var(--radius);
            overflow: hidden;
            margin-bottom: 2rem;
        }

        .stat-cell {
            background: var(--surface);
            padding: 1.2rem 1.4rem;
        }

        .stat-label {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: var(--muted);
            margin-bottom: 0.4rem;
        }

        .stat-value {
            font-size: 1.05rem;
            font-weight: 600;
            line-height: 1.3;
        }

        /* Section */
        .section {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 1.5rem;
            margin-bottom: 1rem;
        }

        .section h2 {
            font-family: 'Syne', sans-serif;
            font-size: 1rem;
            font-weight: 700;
            color: var(--accent);
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 1rem;
        }

        .tag-list { display: flex; flex-wrap: wrap; gap: 0.4rem; }

        .lang-tag, .cur-tag {
            display: inline-block;
            padding: 0.3rem 0.85rem;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 500;
        }

        .lang-tag {
            background: rgba(74,143,232,0.12);
            border: 1px solid rgba(74,143,232,0.3);
            color: #7ab4f0;
        }

        .cur-tag {
            background: rgba(232,200,74,0.1);
            border: 1px solid rgba(232,200,74,0.25);
            color: var(--accent);
        }

        footer {
            text-align: center;
            padding: 2rem;
            color: var(--muted);
            font-size: 0.8rem;
            border-top: 1px solid var(--border);
            margin-top: 3rem;
        }

        @media (max-width: 560px) {
            .hero-flag { width: 100%; }
            .hero-title h1 { font-size: 2rem; }
        }
    </style>
</head>
<body>

<header>
    <div class="logo">Länder<span>.</span>Info</div>
    <nav>
        <a href="index.php">Alle Länder</a>
        <a href="suche.php">Suche</a>
    </nav>
</header>

<main>
    <a class="back-link" href="javascript:history.back()">← Zurück</a>

    <?php if ($error): ?>
        <div class="error"><?= $error ?></div>

    <?php elseif ($data): ?>
        <?php
            $commonName  = $data['name']['common'] ?? '–';
            $officialName= $data['name']['official'] ?? '';
            $capital     = implode(', ', $data['capital'] ?? ['–']);
            $region      = $data['region'] ?? '–';
            $subregion   = $data['subregion'] ?? '';
            $population  = fmt_number($data['population'] ?? 0);
            $area        = fmt_area($data['area'] ?? null);
            $flag        = $data['flags']['png'] ?? $data['flags']['svg'] ?? '';
            $flagAlt     = $data['flags']['alt'] ?? '';

            // Languages
            $languages = [];
            foreach (($data['languages'] ?? []) as $lang) {
                $languages[] = $lang;
            }

            // Currencies
            $currencies = [];
            foreach (($data['currencies'] ?? []) as $code => $cur) {
                $sym = $cur['symbol'] ?? '';
                $currencies[] = ($cur['name'] ?? $code) . ($sym ? " ($sym)" : '');
            }
        ?>

        <div class="hero">
            <?php if ($flag): ?>
            <div class="hero-flag">
                <img src="<?= htmlspecialchars($flag) ?>" alt="<?= htmlspecialchars($flagAlt ?: "Flagge $commonName") ?>">
            </div>
            <?php endif; ?>
            <div class="hero-title">
                <h1><?= htmlspecialchars($commonName) ?></h1>
                <?php if ($officialName && $officialName !== $commonName): ?>
                    <p class="native-name"><?= htmlspecialchars($officialName) ?></p>
                <?php endif; ?>
                <div>
                    <span class="tag"><?= htmlspecialchars($region) ?></span>
                    <?php if ($subregion): ?>
                        <span class="tag"><?= htmlspecialchars($subregion) ?></span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Stats -->
        <div class="stats-grid">
            <div class="stat-cell">
                <div class="stat-label">Hauptstadt</div>
                <div class="stat-value"><?= htmlspecialchars($capital) ?></div>
            </div>
            <div class="stat-cell">
                <div class="stat-label">Bevölkerung</div>
                <div class="stat-value"><?= $population ?></div>
            </div>
            <div class="stat-cell">
                <div class="stat-label">Fläche</div>
                <div class="stat-value"><?= $area ?></div>
            </div>
            <div class="stat-cell">
                <div class="stat-label">Region</div>
                <div class="stat-value"><?= htmlspecialchars($region) ?><?= $subregion ? " / $subregion" : '' ?></div>
            </div>
        </div>

        <!-- Languages -->
        <?php if (!empty($languages)): ?>
        <div class="section">
            <h2>Sprachen</h2>
            <div class="tag-list">
                <?php foreach ($languages as $lang): ?>
                    <span class="lang-tag"><?= htmlspecialchars($lang) ?></span>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Currencies -->
        <?php if (!empty($currencies)): ?>
        <div class="section">
            <h2>Währungen</h2>
            <div class="tag-list">
                <?php foreach ($currencies as $cur): ?>
                    <span class="cur-tag"><?= htmlspecialchars($cur) ?></span>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

    <?php endif; ?>
</main>

<footer>Daten von <a href="https://restcountries.com" style="color:var(--accent);text-decoration:none;">restcountries.com</a></footer>

</body>
</html>

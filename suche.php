<?php
$query   = trim($_GET['q'] ?? '');
$results = [];
$error   = '';

if ($query !== '') {
    $apiUrl  = "https://restcountries.com/v3.1/name/" . urlencode($query);
    $raw     = @file_get_contents($apiUrl);
    if ($raw === false) {
        $error = "Verbindung zur API fehlgeschlagen.";
    } else {
        $data = json_decode($raw, true);
        if (isset($data['status']) && $data['status'] === 404) {
            $error = "Kein Land mit dem Namen „" . htmlspecialchars($query) . " gefunden.";
        } else {
            $results = $data;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Länder-Info – Suche</title>
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

        nav a:hover, nav a.active { color: var(--accent); }

        main {
            max-width: 900px;
            margin: 0 auto;
            padding: 3rem 2rem;
        }

        h1 {
            font-family: 'Syne', sans-serif;
            font-size: 2.2rem;
            font-weight: 800;
            letter-spacing: -0.03em;
            margin-bottom: 2rem;
        }

        h1 span { color: var(--accent); }

        .search-form {
            display: flex;
            gap: 0.75rem;
            margin-bottom: 2.5rem;
            flex-wrap: wrap;
        }

        .search-form input {
            flex: 1;
            min-width: 220px;
            padding: 0.85rem 1.2rem;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            color: var(--text);
            font-family: 'Manrope', sans-serif;
            font-size: 1rem;
            outline: none;
            transition: border-color 0.2s;
        }

        .search-form input:focus { border-color: var(--accent); }
        .search-form input::placeholder { color: var(--muted); }

        .search-form button {
            padding: 0.85rem 1.8rem;
            background: var(--accent);
            color: #0d0f14;
            border: none;
            border-radius: var(--radius);
            font-family: 'Syne', sans-serif;
            font-size: 0.95rem;
            font-weight: 700;
            cursor: pointer;
            transition: opacity 0.2s, transform 0.15s;
            letter-spacing: 0.02em;
        }

        .search-form button:hover { opacity: 0.88; transform: translateY(-1px); }

        .error {
            background: rgba(232,80,80,0.12);
            border: 1px solid rgba(232,80,80,0.35);
            color: #f08080;
            border-radius: var(--radius);
            padding: 1rem 1.2rem;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
        }

        .results-label {
            font-size: 0.85rem;
            color: var(--muted);
            margin-bottom: 1.2rem;
        }

        .results-label strong { color: var(--accent); }

        .result-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 1.5rem;
            display: flex;
            gap: 1.5rem;
            align-items: flex-start;
            margin-bottom: 1rem;
            transition: border-color 0.2s;
            text-decoration: none;
            color: var(--text);
        }

        .result-card:hover { border-color: var(--accent); }

        .result-card img {
            width: 120px;
            aspect-ratio: 3/2;
            object-fit: cover;
            border-radius: 6px;
            flex-shrink: 0;
            border: 1px solid var(--border);
        }

        .result-info h2 {
            font-family: 'Syne', sans-serif;
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 0.8rem;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
            gap: 0.5rem 1rem;
        }

        .info-item {
            display: flex;
            flex-direction: column;
            gap: 0.1rem;
        }

        .info-label {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--muted);
        }

        .info-value {
            font-size: 0.9rem;
            font-weight: 500;
        }

        .detail-link {
            display: inline-block;
            margin-top: 1rem;
            font-size: 0.82rem;
            color: var(--accent2);
            text-decoration: none;
            font-weight: 500;
        }

        .detail-link:hover { text-decoration: underline; }

        footer {
            text-align: center;
            padding: 2rem;
            color: var(--muted);
            font-size: 0.8rem;
            border-top: 1px solid var(--border);
            margin-top: 3rem;
        }

        @media (max-width: 560px) {
            .result-card { flex-direction: column; }
            .result-card img { width: 100%; }
        }
    </style>
</head>
<body>

<header>
    <div class="logo">Länder<span>.</span>Info</div>
    <nav>
        <a href="index.php">Alle Länder</a>
        <a href="suche.php" class="active">Suche</a>
    </nav>
</header>

<main>
    <h1>Land <span>suchen</span></h1>

    <form class="search-form" method="GET" action="suche.php">
        <input
            type="text"
            name="q"
            placeholder="z.B. Deutschland, France, Japan …"
            value="<?= htmlspecialchars($query) ?>"
            autofocus
        >
        <button type="submit">Suchen</button>
    </form>

    <?php if ($error): ?>
        <div class="error"><?= $error ?></div>
    <?php endif; ?>

    <?php if (!empty($results)): ?>
        <p class="results-label"><strong><?= count($results) ?></strong> Ergebnis(se) für „<?= htmlspecialchars($query) ?>"</p>

        <?php foreach ($results as $c):
            $name       = $c['name']['common'] ?? '–';
            $capital    = implode(', ', $c['capital'] ?? ['–']);
            $region     = $c['region'] ?? '–';
            $population = number_format($c['population'] ?? 0, 0, ',', '.');
            $flag       = $c['flags']['png'] ?? $c['flags']['svg'] ?? '';
        ?>
        <div class="result-card">
            <?php if ($flag): ?>
                <img src="<?= htmlspecialchars($flag) ?>" alt="Flagge <?= htmlspecialchars($name) ?>">
            <?php endif; ?>
            <div class="result-info">
                <h2><?= htmlspecialchars($name) ?></h2>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Hauptstadt</span>
                        <span class="info-value"><?= htmlspecialchars($capital) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Region</span>
                        <span class="info-value"><?= htmlspecialchars($region) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Bevölkerung</span>
                        <span class="info-value"><?= $population ?></span>
                    </div>
                </div>
                <a class="detail-link" href="landinfo.php?name=<?= urlencode($name) ?>">→ Detailseite öffnen</a>
            </div>
        </div>
        <?php endforeach; ?>
    <?php elseif ($query !== '' && !$error): ?>
        <p style="color:var(--muted);">Keine Ergebnisse.</p>
    <?php endif; ?>
</main>

<footer>Daten von <a href="https://restcountries.com" style="color:var(--accent);text-decoration:none;">restcountries.com</a></footer>

</body>
</html>

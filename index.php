<?php
$apiUrl = "https://restcountries.com/v3.1/all?fields=name,flags,cca3";
$response = file_get_contents($apiUrl);
$countries = json_decode($response, true);

if ($countries) {
    usort($countries, function($a, $b) {
        return strcmp($a['name']['common'], $b['name']['common']);
    });
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Länder-Info – Alle Länder</title>
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

        .search-bar {
            width: 100%;
            max-width: 360px;
        }

        .search-bar input {
            width: 100%;
            padding: 0.6rem 1rem;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 8px;
            color: var(--text);
            font-family: 'Manrope', sans-serif;
            font-size: 0.9rem;
            outline: none;
            transition: border-color 0.2s;
        }

        .search-bar input:focus { border-color: var(--accent); }
        .search-bar input::placeholder { color: var(--muted); }

        main {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2.5rem 2rem;
        }

        .meta {
            font-size: 0.85rem;
            color: var(--muted);
            margin-bottom: 2rem;
        }

        .meta strong { color: var(--accent); }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
            gap: 1rem;
        }

        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            overflow: hidden;
            text-decoration: none;
            color: var(--text);
            transition: transform 0.2s, border-color 0.2s, box-shadow 0.2s;
            display: flex;
            flex-direction: column;
        }

        .card:hover {
            transform: translateY(-4px);
            border-color: var(--accent);
            box-shadow: 0 8px 24px rgba(232,200,74,0.12);
        }

        .card img {
            width: 100%;
            aspect-ratio: 3/2;
            object-fit: cover;
            display: block;
        }

        .card-body {
            padding: 0.7rem 0.9rem;
            font-size: 0.82rem;
            font-weight: 500;
            line-height: 1.3;
        }

        .no-results {
            text-align: center;
            color: var(--muted);
            padding: 4rem 0;
            font-size: 1rem;
        }

        footer {
            text-align: center;
            padding: 2rem;
            color: var(--muted);
            font-size: 0.8rem;
            border-top: 1px solid var(--border);
            margin-top: 3rem;
        }
    </style>
</head>
<body>

<header>
    <div class="logo">Länder<span>.</span>Info</div>
    <nav>
        <a href="index.php" class="active">Alle Länder</a>
        <a href="suche.php">Suche</a>
    </nav>
    <div class="search-bar">
        <input type="text" id="filterInput" placeholder="Filtern …" oninput="filterCards()">
    </div>
</header>

<main>
    <p class="meta" id="countLabel">
        <?php if ($countries): ?>
            <strong><?= count($countries) ?></strong> Länder geladen
        <?php else: ?>
            Fehler beim Laden der Länderdaten.
        <?php endif; ?>
    </p>

    <?php if ($countries): ?>
    <div class="grid" id="countryGrid">
        <?php foreach ($countries as $country):
            $name = $country['name']['common'];
            $flag = $country['flags']['png'] ?? $country['flags']['svg'] ?? '';
            $code = $country['cca3'];
        ?>
        <a class="card" href="landinfo.php?name=<?= urlencode($name) ?>" data-name="<?= strtolower(htmlspecialchars($name)) ?>">
            <?php if ($flag): ?>
                <img src="<?= htmlspecialchars($flag) ?>" alt="Flagge <?= htmlspecialchars($name) ?>" loading="lazy">
            <?php endif; ?>
            <div class="card-body"><?= htmlspecialchars($name) ?></div>
        </a>
        <?php endforeach; ?>
    </div>
    <p class="no-results" id="noResults" style="display:none;">Kein Land gefunden.</p>
    <?php else: ?>
    <p class="no-results">API-Daten konnten nicht geladen werden.</p>
    <?php endif; ?>
</main>

<footer>Daten von <a href="https://restcountries.com" style="color:var(--accent);text-decoration:none;">restcountries.com</a></footer>

<script>
function filterCards() {
    const q = document.getElementById('filterInput').value.toLowerCase();
    const cards = document.querySelectorAll('#countryGrid .card');
    let visible = 0;
    cards.forEach(c => {
        const match = c.dataset.name.includes(q);
        c.style.display = match ? '' : 'none';
        if (match) visible++;
    });
    document.getElementById('noResults').style.display = visible === 0 ? '' : 'none';
    document.getElementById('countLabel').innerHTML =
        `<strong>${visible}</strong> Länder angezeigt`;
}
</script>
</body>
</html>

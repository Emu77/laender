# 🌍 Länder-Info

Eine kleine PHP-Webanwendung, die Länderdaten über die [REST Countries API](https://restcountries.com) abruft und anzeigt.

## Seiten

| Datei | Beschreibung |
|---|---|
| `index.php` | Alle Länder als Grid mit Flagge und Name, inkl. Live-Filter |
| `suche.php` | Suche nach einem Land per Name |
| `landinfo.php` | Detailseite eines Landes (via `?name=Germany`) |

## Angezeigte Informationen

**Suche & Übersicht**
- Name, Flagge, Hauptstadt, Region, Bevölkerung

**Detailseite**
- Name (offiziell & gebräuchlich), Flagge, Hauptstadt
- Region & Subregion, Bevölkerung, Fläche
- Sprachen, Währungen

## Installation

1. Dateien in den XAMPP-Ordner kopieren, z.B. `C:\xampp\htdocs\laender\`
2. XAMPP starten (Apache)
3. Im Browser öffnen: `http://localhost/laender/`

> **Hinweis:** PHP benötigt `allow_url_fopen = On` in der `php.ini`, damit die API-Aufrufe funktionieren (Standard bei XAMPP).

## API

Genutzte Endpunkte der [REST Countries API v3.1](https://restcountries.com):

```
GET https://restcountries.com/v3.1/all
GET https://restcountries.com/v3.1/name/{name}
GET https://restcountries.com/v3.1/name/{name}?fullText=true
```

## Kompatibilität

Kompatibel mit **PHP 5.6+** – keine modernen PHP-Features (Arrow Functions, Null-Coalescing etc.) verwendet.

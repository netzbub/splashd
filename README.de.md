# Splashd — wechselnde Nextcloud-Hintergründe aus dem eigenen Ordner

<p align="center">
  <img src="https://img.shields.io/github/v/tag/netzbub/splashd?label=version&color=blue" alt="Version">
  <img src="https://img.shields.io/github/license/netzbub/splashd?color=olive&cacheSeconds=3600" alt="Lizenz">
  <img src="https://img.shields.io/github/last-commit/netzbub/splashd?color=blueviolet" alt="letzter Commit">
  <img src="https://img.shields.io/github/issues/netzbub/splashd?color=yellow" alt="offene Issues">
  <img src="https://img.shields.io/badge/Nextcloud-35-0082C9?logo=nextcloud&logoColor=white" alt="Nextcloud 35">
  <img src="https://img.shields.io/badge/PHP-8.3--8.5-777BB4?logo=php&logoColor=white" alt="PHP 8.3 bis 8.5">
</p>

Zeigt ein wechselndes Hintergrundbild auf der Anmeldeseite und im Dashboard einer Nextcloud.

🇬🇧 **English version: [README.md](README.md)**

Dies ist ein Fork der Nextcloud-App [Splash](https://github.com/nextcloud/unsplash) (App-Kennung `unsplash`), aktualisiert auf Nextcloud 35 und um einen Gedanken herum neu gebaut: der Hintergrund darf die Besucher einer Anmeldeseite nicht ihre IP-Adresse kosten.

## Was anders ist als im Original

* **Eigener Ordner als Bildquelle.** Die App wird auf einen Ordner dieser Nextcloud gerichtet und nimmt die Bilder, die darin liegen.
* **Keine Anfragen an Dritte aus dem Browser.** Das Original schrieb die Bild-URL von Unsplash, Wallhaven, Wikimedia oder Bing direkt in das Anmelde-Stylesheet. Der Browser jedes Besuchers nahm damit Verbindung zu diesen Servern auf, bevor sich überhaupt jemand angemeldet hatte. Hier holt und verkleinert der Server die Bilder, und die Anmeldeseite lädt ausschließlich von dieser Instanz.
* **Die fremden Quellen sind entfernt.** Unsplash und Pexels verbieten Wallpaper-Anwendungen in ihren API-Bedingungen, Wallhaven räumt überhaupt kein Nutzungsrecht ein, und Bing-Bilder sind nicht zur Weitergabe lizenziert. Geblieben ist Openverse, beschränkt auf CC0 und Public Domain Mark, die keine Namensnennung verlangen. Diese App nutzt die Openverse-API, wird von Openverse aber nicht befürwortet oder zertifiziert.
* **Ein Vorrat statt eines einzelnen Bildes.** Die Quelle legt einen Satz verkleinerter Bilder an; der Hintergrund kann deshalb bei jedem einzelnen Aufruf wechseln, nicht nur einmal täglich.
* **Die Bilder werden neu kodiert**, wodurch auch EXIF, XMP und IPTC wegfallen — kein Kameramodell, keine GPS-Position und kein Urheberfeld aus den Ursprungsdateien wird herausgegeben.

## Bildquellen

| Quelle | Schlüssel nötig | Lizenz |
|:---|:---|:---|
| Eigener Ordner | nein | deine |
| Mitgelieferte Bilder | nein | [CC BY 4.0](img/samples/LICENSE.md), netzbub |
| [Openverse](https://openverse.org) | nein | nur CC0 und Public Domain Mark |
| Anmeldebild aus Design | nein | deine |

## Einstellungen

Administrationseinstellungen > Design, Abschnitt „Splashd":
<https://deine.cloud/settings/admin/theming>

Für den eigenen Ordner werden zwei Angaben gebraucht: ein **Konto** und ein **Pfad** innerhalb der Dateien dieses Kontos. Ein Teamordner ist in die Dateiansicht jedes Mitglieds eingehängt; ein Pfad, der auf einen Teamordner zeigt, macht die Bildquelle also unabhängig von den persönlichen Dateien einer einzelnen Person — solange das eingetragene Konto Mitglied bleibt.

Gelesen werden JPEG, PNG und WebP, alles andere im Ordner wird übergangen. Fehlt der Ordner oder ist er leer, wird das Anmeldebild aus Administrationseinstellungen > Design verwendet.

Über das Bild lässt sich ein Farbschleier legen: Farbe wählen — voreingestellt Weiß — und dazu die Stärke, vom zarten Schleier bis zur volldeckenden Fläche. Daneben steht eine Unschärfe zur Verfügung.

## Wie die Bilder ausgeliefert werden

Ein Hintergrundauftrag füllt den Vorrat einmal täglich auf. `/apps/splashd/api/image` liefert daraus ein Bild: bei jedem Aufruf ein zufälliges, oder den ganzen Tag dasselbe, je nach Einstellung. Der Endpunkt gibt nichts heraus außer den verkleinerten Kopien — keine Dateinamen, keine Pfade, keine Ordnerliste.

## Voraussetzungen

Nextcloud 35, PHP 8.3 bis 8.5, die PHP-Erweiterung GD zum Verkleinern.

## Lizenz

Der Quelltext steht unter den Bedingungen der [AGPLv3](LICENSE.md), wie im Original. Die Urheberrechtshinweise der ursprünglichen Autoren bleiben erhalten, Änderungen sind in den Dateiköpfen als solche gekennzeichnet.

Die Fotografien in `img/samples/` fallen **nicht** unter die AGPL. Sie stehen unter [CC BY 4.0](img/samples/LICENSE.md) mit Namensnennung netzbub.

# Symcon Raspberry Monitor 0.6

IP-Symcon 9.0: native HTML-SDK-Instanzkachel mit responsiven Geräten, IP-Hinzufügen, manuell begrenzter SSH-Port-Suche, SSH-Systemstatus.

## Upgrade
Bestehende Modulinstanz und Geräte-Property `Devices` bleiben erhalten. Keine zweite Instanz erzeugen. Die alten Bedien-/HTMLBox-Variablen werden **ausgeblendet, nicht gelöscht**. IP-Symcon Visualisierung: Instanzkachel "Raspberry Überwachung" anzeigen und auf volle Breite stellen.

## Funktionen
- Zwei oder mehr Rechner nebeneinander bei genügend Breite.
- Neue IP über Dialog hinzufügen.
- Netzwerk-SSH-Suche über einstellbaren 32-IP-Abschnitt.
- Geräte nur auf ausdrücklichen Wunsch eintragen.
- Live-Aktualisierung über das HTML-SDK.
- Alle Aktionen lesend; keine Updates, Neustarts oder SSH-Veränderungen.

## Sicherheit
Zugriff auf Instanzkachel nur über geschützte Symcon-Visualisierung. Für Remotezugriff benötigt Symcon einen bereits eingerichteten SSH-Schlüssel. Das Erfassen eines offenen SSH-Ports ersetzt keine Authentifizierung. Keine Kennwörter im Repository. Bitte bei mehreren Visualisierungsnutzern deren Zugriffsrechte prüfen.

## Testen
Update der bestehenden Bibliothek, bestehende Instanz öffnen, Geräte sichtbar? Aktualisieren, Dialog IP hinzufügen und Scan durchführen. PHP-Lint und JSON-Prüfung ersetzen keinen Symcon-Livetest.

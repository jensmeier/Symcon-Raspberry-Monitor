# Symcon Raspberry Monitor 0.8 (Entwicklungsstand)

IP-Symcon 9.0: responsive, helle HTML-SDK-Oberfläche zur Überwachung von Linux-/Raspberry-Pi-Systemen.

## Funktionen
- Systemkarten mit CPU-Temperatur, RAM, Festplatte, Debian, Kernel, APT-Paketupdateanzahl und Zigbee2MQTT-Dienststatus.
- Geräte hinzufügen, Namen ändern, Geräte nur aus der Überwachung entfernen.
- Begrenzte Suche nach offenen SSH-Ports im lokalen Netz.
- Geräte manuell prüfen, alle Systeme auffrischen und APT-Updates **simulieren** (ohne Paketinstallation).
- Helles Design mit Abstand zur von Symcon eingeblendeten Kachelüberschrift.
- Neustartvorgänge mit expliziter administrativer Opt-in-Einstellung, einmaligem Token und 60 Sekunden Ablaufzeit.
- Bestehende Geräte und Instanz werden übernommen; keine neuen Bedien-Variablen pro Schaltfläche.

## Installation
Bestehende Modulbibliothek in IP-Symcon auf GitHub-Stand aktualisieren und die vorhandene Instanz behalten.
In der Kachelvisualisierung breite Darstellung wählen.

## Sicherheitsstatus
**Standardmäßig sind Neustarts gesperrt.** Unter Instanzkonfiguration `AllowRestart` kann ein Administrator sie aktivieren, nachdem eingeschränkte sudo-Berechtigungen und Rückfallweg getestet wurden. Eine Bestätigung in der Visualisierung ist zusätzlich erforderlich. Ein angenommener Neustartbefehl ist noch kein Beweis, dass das Ziel wieder gestartet ist.

**Paketupdates werden in dieser Version nicht installiert.** Kein Debian-11-auf-13-Upgrade.
Remote-SSH-Verbindung benötigt vorher eingerichteten SSH-Schlüssel mit geprüftem Hostschlüssel. Geräte aus der Überwachung zu entfernen löscht keine Daten auf dem Zielgerät.

## Noch nicht implementiert / zu prüfen
- Echte MQTT-Protokollprüfung (statt bloß TCP-Port).
- Sichere Updateinstallation mit getestetem Backup/Rollback, Jobs und Erfolgskontrolle.
- Dienstspezifische Neustarts, SSH-Onboarding und echte Geräteidentifikation.
- End-to-End-Test auf dem laufenden Symcon 9.0.

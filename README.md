# Symcon Raspberry Monitor — 0.1 (Testversion)

Eigenständiges IP-Symcon-Modul für mehrere Linux-/Raspberry-Pi-Rechner. **Nur lesend.**

## Bereits umgesetzt

- Eine zentrale HTML-Übersicht mit responsiven Karten (bei ausreichender Kachelbreite nebeneinander).
- Geräte in der Instanzkonfiguration als Liste hinzufügen, ändern und entfernen.
- Lokale Abfrage und SSH-Abfrage mit vorhandener Schlüsselanmeldung.
- OS, Kernel, CPU-Temperatur, RAM, Festplatte, bekannte APT-Updates, Zigbee-Dienststatus.
- Ein Timer für regelmäßige Aktualisierung, keine neuen Variablen pro Lauf.
- `SRM_ProbeIP(INSTANZ_ID, "192.168.178.90")` prüft eine **manuell eingegebene IP-Adresse** auf SSH-Port 22. Keine Netzwerksuche in 0.1.

## Einrichtung

1. Ordner als eigenes GitHub-Repository veröffentlichen (z.B. über GitHub Desktop).
2. In IP-Symcon unter **Modulverwaltung** die Repository-URL als Entwicklungsmodul hinzufügen.
3. Instanz vom Typ **Raspberry Überwachung** erstellen.
4. In der Instanzkonfiguration IPs/Namen prüfen; SSH-Benutzer und privaten Schlüsselpfad hinterlegen, **niemals den Schlüsselinhalt**.
5. Änderungen übernehmen und `Raspberry Übersicht` in der Visualisierung möglichst breit platzieren.

Wichtig: Die vorkonfigurierten Adressen sind ausschließlich für das bestehende private Heimnetz gedacht. Für andere Installationen bitte anpassen. Vorhandene Skript-Variable `17543` wird **nicht** verändert. Alte Skripte können parallel bleiben.

## Nicht in v0.1 enthalten

- Automatische Suche über das Subnetz, SSH-Schlüssel-Einrichtung, Anmeldung, System-/App-Updates oder Neustart.
- Echtes MQTT-Protokoll-Login oder MQTT-Verbindungsstatus.
- IP-Symcon-Paket-/Zigbee2MQTT-Releaseversionsprüfung.

## Sicherheit

SSH nutzt `StrictHostKeyChecking=yes`, erwartet vorab bestätigte Hostschlüssel und einen bereits funktionierenden Schlüssel. Der Prototyp verwendet den angegebenen SSH-Benutzer; langfristig einen separaten **nur lesenden** Benutzer verwenden. **Nicht ungeschützt ins Internet freigeben.**

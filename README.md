# Symcon Raspberry Monitor 0.7

IP-Symcon 9.0 HTML-SDK – helle Systemverwaltung im Stil der Symcon-Kachelvisualisierung.

## Änderungen
- Helles Dashboard mit zwei responsiven Systemkarten.
- Gerät direkt in der Oberfläche verwalten (umbenennen, Status per SSH neu prüfen, aus Überwachung entfernen).
- Raspberry über IP hinzufügen und begrenzte SSH-Port-Suche.
- Bestehende Instanz, Geräte-Property `Devices` und Modulkennungen bleiben erhalten.
- Beim Entfernen werden **keine Dateien oder Geräte auf dem Raspberry gelöscht**.

## Update
Bestehende IP-Symcon Modulbibliothek auf GitHub aktualisieren, vorhandene Instanz weiterverwenden, Moduländerungen übernehmen. Die Kachel in der Visualisierung möglichst breit anordnen.

## Sicherheit
Lesender Systemstatus. Neustarts und Updateinstallationen bleiben deaktiviert. SSH benötigt vorher eingerichtete Schlüssel. Netzwerksuche weist nur erreichbare SSH-Ports nach, nicht Gerätetyp oder gültige Anmeldung. Zugang zur Visualisierung einschränken.

## Einschränkungen
Die grafische Oberfläche nutzt das HTML-SDK. Bei sehr kleinen Kachelbreiten erfolgt eine einspaltige Darstellung. Die JavaScript-Dialoge benötigen einen Test in deiner echten Symcon-Visualisierung. Kein vollautomatischer SSH-Onboarding-Assistent, keine geprüfte MQTT-Anmeldung und keine Updateinstallation in dieser Version.

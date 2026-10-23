# Symcon Raspberry Monitor v0.5 – Testversion

**Nur lesender Betrieb**: keine Neustarts und keine Updateinstallation. Das Modul arbeitet ohne Variable 17543. Es erstellt seine benötigten Status- und Bedienvariablen selbst.

## Installation / Aktualisierung

1. Dateien in das bisherige GitHub-Repository übernehmen und committen. **Die GUIDs in `library.json` und `module.json` bleiben gleich**, damit die bestehende Instanz erhalten bleibt.
2. In der IP-Symcon Modulverwaltung das Modul aktualisieren und die Instanzkonfiguration **Übernehmen**.
3. In der Visualisierung die Variable **Raspberry Übersicht** möglichst breit platzieren; daneben bzw. darunter die Modulvariablen **Neue Raspberry-IP**, **Gerät hinzufügen**, **Jetzt aktualisieren**, **Netzwerk suchen**, **Gefundene SSH-Geräte**, **Bedienhinweis** hinzufügen.
4. Neue IP eintragen und „Gerät hinzufügen“ auf EIN stellen. Der Schalter wird automatisch zurückgesetzt. In der Instanzkonfiguration lassen sich Name, Nutzer und Schlüsselpfad ändern.

## Netzwerk-Suche

Die Suche ist manuell und auf **32 Adressen pro Durchlauf** begrenzt, ausgehend von `ScanStart` im /24 der lokalen Symcon-IP. Sie prüft ausschließlich TCP-Port 22. Zum Durchsuchen weiterer Bereiche `ScanStart` ändern. Gefundene Geräte werden **nicht** automatisch aufgenommen; IP in das Eingabefeld kopieren. Ein offener SSH-Port beweist nicht, dass ein Raspberry Pi vorhanden ist.

## Grenzen und Sicherheit

- Die HTML-Übersicht ist reine Anzeige. Symcon-Statusvariablen sind keine eingebetteten HTML-Buttons. In der Visualisierungsbearbeitung die genannten Bedienvariablen **auf derselben Seite** anordnen.
- SSH-Verbindung benötigt einen vorher geprüften Host-Schlüssel, gültigen Login und den Pfad zu einem vorhandenen privaten Schlüssel. Keinen Schlüssel in GitHub speichern.
- Keine Prüfung des echten MQTT-Protokollzustands; nur Dienstüberwachung. APT verwendet bestehende Listen.
- Keine Verbindung zu privaten Geräten außerhalb des bewusst konfigurierten LAN. Scan nur im festgelegten /24.
- Nach einem Modulupdate keine zweite Instanz erstellen. Die vorhandene Instanz aktualisieren.

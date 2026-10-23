# Symcon Raspberry Monitor 1.8 (Entwicklungsstand)

IP-Symcon 9.0: responsive, helle HTML-SDK-Oberfläche zur Überwachung von Linux-/Raspberry-Pi-Systemen.

## Neu in 1.2
- Bei aktivem Zigbee2MQTT-Dienst werden die letzten fünf Minuten aus `journalctl` auf typische MQTT-Verbindungsfehler geprüft.
- Keine Fehlerinträge bedeutet **nicht**, dass die MQTT-Sitzung bestätigt ist. Die tatsächliche MQTT-Protokollprüfung per `zigbee2mqtt/bridge/health` steht noch aus.
- Keine Konfigurationsänderungen, Neustarts oder Updates durch diese Diagnose.

## Ergänzungen in 1.2
- Pro Gerät Systemlaufzeit und tatsächlich freien Speicher anzeigen.
- Sichtbare Warnungen bei Datenträgerbelegung ab 85 % / 90 % und CPU-Temperatur ab 70 °C / 80 °C.
- Warnung bei im Journal gefundenen MQTT-Fehlermeldungen.
- Es erfolgen weiterhin keine automatischen Updates und keine automatischen Neustarts.

## Neu in 1.3
- Kleine Verlaufsdiagramme pro Gerät für Temperatur, RAM und Speicherverbrauch direkt in der HTML-SDK-Karte.
- Messwerte im internen Modul-Puffer: maximal 288 Punkte je Gerät, mindestens vier Minuten zwischen Messpunkten, maximal etwa 24 Stunden bei einem 5-Minuten-Intervall.
- Beim Modulneustart kann die Pufferhistorie verloren gehen; für dauerhafte Archivierung ist eine gesonderte Persistenz nötig.
- Keine zusätzlichen Symcon-Variablen, Updates oder Neustarts.

## Neu in 1.4
- Schaltfläche **Updates prüfen** öffnet eine eigene übersichtliche Liste mit Paketnamen, installierter und angebotener Version, soweit APT diese Informationen liefert.
- APT-Upgrade wird ausschließlich **simuliert**; Paketlisten werden dabei nicht aktualisiert und Pakete nicht installiert.
- Ausgabe im HTML-SDK-Dialog; keine zusätzlichen Symcon-Variablen.

## Neu in 1.5
- Neustart-Erfolgskontrolle ohne pauschale Erfolgsmeldung: Systemneustart gilt nur mit neuer `/proc/sys/kernel/random/boot_id` und aktivem relevanten Dienst als bestätigt.
- Dienstneustart gilt erst mit veränderter `systemctl show ... ActiveEnterTimestampMonotonic` und aktivem Dienst als bestätigt.
- Nach sechs Minuten ohne Bestätigung erscheint **nicht bestätigt**, nicht fälschlich erfolgreich.
- Status je Gerät in der vorhandenen HTML-SDK-Kachel; keine neuen Variablen.
- Die Nachprüfung erfolgt mit den normalen Modul-Aktualisierungen (standardmäßig alle fünf Minuten). Für schnellere Rückmeldung muss das Überwachungsintervall reduziert werden.
- Die bisherigen Neustart-Freigaben bleiben standardmäßig ausgeschaltet. Kein automatischer Neustart oder Update wird ausgelöst.

## Neu in 1.6 – vorhandene Zigbee2MQTT-Bridge
- Die **bereits bestehende** Status-Variable der Zigbee2MQTT-Bridge wird ausgelesen; Standard-ID `27220` aus der vorhandenen Symcon-Konfiguration (Boolean „Status / OK“).
- Der Zeitpunkt `VariableUpdated` wird geprüft. Frische Bridge-Meldungen (standardmäßig innerhalb zehn Minuten) können als „Bridge meldet OK“ oder „Bridge meldet Störung“ erscheinen.
- Alte oder nie aktualisierte Statuswerte werden **nicht** als erfolgreiche MQTT-Verbindung ausgegeben.
- Die ID und das Frischefenster sind in der Instanz einstellbar, falls die Bridge-Verknüpfung anders aufgebaut ist.
- Zusätzlich bleibt die Journalauswertung aktiv. **Kein eigenständiger MQTT-CONNACK-Test:** Ein Bridge-Status kann von der tatsächlichen MQTT-Sitzung abweichen; die Auswertung bleibt als Statusindikator gekennzeichnet.
- Es werden keine bestehenden Zigbee-Variablen verändert, keine neuen IDs erzeugt und keine Neustarts ausgeführt.

## Neu in 1.7
- Aktiver MQTT-3.1.1-Protokolltest über TCP CONNECT/CONNACK gegen den konfigurierten LAN-Broker (Standard 192.168.178.41:1029). Kein Publish, Subscribe oder Passwort.
- Unterscheidet Broker nicht erreichbar, gültige CONNACK-Antwort, Anmeldung erforderlich und Protokollfehler.
- Dieser Test bestätigt **nicht** die MQTT-Sitzung des Zigbee2MQTT-Clients. Bridge-Status und Journalauswertung bleiben daher separat.
- IP und Port sind konfigurierbar. Kein Neustart, keine Updates, keine Passwortspeicherung.

## Neu in 1.8
- Einklappbare Systemmeldungen direkt in der Visualisierung.
- Erfasst ausschließlich Zustandswechsel: Online/Offline, neu auftretende Warnungen und deren Behebung.
- Begrenzung auf die letzten 100 Ereignisse im Modul-Puffer. Kein wiederholtes Logging identischer Warnungen bei jeder Prüfung.
- Keine neuen Variablen. Kein automatisches Installieren oder Neustarten. Modul-Puffer ist keine dauerhafte Audit-Datenbank; seine Beständigkeit muss in der jeweiligen Symcon-Installation geprüft werden.

## Funktionen
- Systemkarten mit CPU-Temperatur, RAM, Festplatte, Debian, Kernel und APT-Paketupdateanzahl.
- Kontextbezogene Anwendungserkennung: Symcon-Dienst auf dem lokalen Rechner; Zigbee2MQTT-Version, Zigbee-Dienst und Node.js auf dem Zigbee-Raspberry.
- Geräte hinzufügen, Namen ändern, Geräte nur aus der Überwachung entfernen.
- Begrenzte Suche nach offenen SSH-Ports im lokalen Netz.
- Geräte manuell prüfen, alle Systeme auffrischen und APT-Updates **simulieren** (ohne Paketinstallation).
- Helles Design mit Abstand zur von Symcon eingeblendeten Kachelüberschrift.
- Raspberry-Neustarts mit expliziter administrativer Opt-in-Einstellung, einmaligem Token und 60 Sekunden Ablaufzeit.
- Separate, standardmäßig gesperrte Dienstneustarts (lokaler Symcon-Dienst oder entfernter Zigbee2MQTT-Dienst) mit eigener Bestätigung.
- Bestehende Geräte und Instanz werden übernommen; keine neuen Bedien-Variablen pro Schaltfläche.

## Installation
Bestehende Modulbibliothek in IP-Symcon auf GitHub-Stand aktualisieren und die vorhandene Instanz behalten.
In der Kachelvisualisierung breite Darstellung wählen.

## Sicherheitsstatus
**Standardmäßig sind Raspberry- und Dienstneustarts gesperrt.** Unter Instanzkonfiguration `AllowRestart` und `AllowServiceRestart` kann ein Administrator diese getrennt aktivieren, nachdem eingeschränkte sudo-Berechtigungen und Rückfallweg getestet wurden. Eine Bestätigung in der Visualisierung ist zusätzlich erforderlich. Ein angenommener Neustartbefehl ist noch kein Beweis, dass das Ziel wieder gestartet ist.

**Paketupdates werden in dieser Version nicht installiert.** Kein Debian-11-auf-13-Upgrade.
Remote-SSH-Verbindung benötigt vorher eingerichteten SSH-Schlüssel mit geprüftem Hostschlüssel. Geräte aus der Überwachung zu entfernen löscht keine Daten auf dem Zielgerät.

## Noch nicht implementiert / zu prüfen
- Echte MQTT-Protokollprüfung (statt bloß TCP-Port).
- Sichere Updateinstallation mit getestetem Backup/Rollback, Jobs und Erfolgskontrolle.
- SSH-Onboarding und echte Geräteidentifikation.
- Dienstneustarts sind implementiert, aber die erforderliche sudo-Freigabe und ein erfolgreicher Neustart wurden nicht live verifiziert.
- End-to-End-Test auf dem laufenden Symcon 9.0.

# Uptime Kuma Incidents – Nextcloud App

**Manage Uptime Kuma instances and incidents from the Nextcloud administration interface.**

- 🛡️ **Admin Only:** Exclusively accessible for system administrators.
- 🚦 **Incident Management:** Trigger incidents directly from Nextcloud with custom styles (info, warning, success, danger).
- 🔄 **Lifecycle Control:** Resolve ongoing incidents or simulate service failures with a single click.
- 🔑 **Token-Based API:** Secure REST endpoint for external scripts and automation workflows.
- 🎯 **Granular Access:** Assign individual API tokens to specific jobs for fine-grained authorization.
- 🤖 **Script Automation:** Easily execute status updates automatically from outside.

## Establishment

1. Under **Kuma instances**, create an uptime Kuma instance with URL, username and password.
2. With **Test connection** check if the login works.
3. Create a job under **Jobs**. A job connects a Kuma instance to a status page (‘statusSlug’) and defines the title, text and default style of the incident.
4. Open **API token** at the job and create a token. The token belongs to the job and is intended for external scripts, not for Uptime Kuma.
5. The token is displayed in full only once. The three API addresses generated can be copied directly.
6. The external script calls via POST depending on the result:
- `/start` at the beginning of a task
- `/failed` in case of error
- `/resolve` after successful completion

## Conduct

Start creates an incident if there is no open incident for the job.
- A restart continues to use the already open incident and does not generate a second one.
Failed sets the open incident to "danger" and leaves it open.
Resolve resolves an active or failed incident.
- A later successful run can thus resolve a previous error.

## Manual test actions

Under **Test actions**, the Job view contains the three actions Start, Failed and Resolve. These serve only for testing the reporting logic. The actual backup or other external process runs outside of this app.



# Uptime Kuma Incidents – Nextcloud App

**Verwalten Sie Uptime Kuma-Instanzen und -Vorfälle über die Nextcloud-Administrationsoberfläche.**

- 🛡️ **Nur für Administratoren:** Ausschließlich für Systemadministratoren zugänglich.
- 🚦 **Vorfalls-Management:** Erstelle Incidents direkt aus Nextcloud mit verschiedenen Status-Farben (Info, Warning, Success, Danger).
- 🔄 **Lebenszyklus-Steuerung:** Löse bestehende Incidents mit einem Klick auf oder simuliere Systemausfälle.
- 🔑 **Token-basierte API:** Sichere Schnittstelle für externe Skripte und Automatisierungs-Workflows.
- 🎯 **Granulare Rechte:** Weise einzelnen Jobs spezifische API-Token für präzise Zugriffskontrolle zu.
- 🤖 **Skript-Automatisierung:** Steuere Statusseiten vollständig automatisiert von außen.

## Einrichtung

1. Unter **Kuma-Instanzen** eine Uptime-Kuma-Instanz mit URL, Benutzername und Passwort anlegen.
2. Mit **Verbindung testen** prüfen, ob die Anmeldung funktioniert.
3. Unter **Jobs** einen Job anlegen. Ein Job verbindet eine Kuma-Instanz mit einer Statusseite (`statusSlug`) und definiert Titel, Text und Standard-Stil des Incidents.
4. Beim Job **API-Token** öffnen und ein Token erzeugen. Das Token gehört zum Job und ist für externe Scripts gedacht, nicht für Uptime Kuma.
5. Das Token wird nur einmal vollständig angezeigt. Die drei erzeugten API-Adressen können direkt kopiert werden.
6. Das externe Script ruft per POST je nach Ergebnis auf:
   - `/start` am Beginn eines Vorgangs
   - `/failed` bei einem Fehler
   - `/resolve` nach erfolgreichem Abschluss

## Verhalten

- `start` erzeugt einen Incident, wenn für den Job kein offener Incident existiert.
- Ein erneuter `start` verwendet den bereits offenen Incident weiter und erzeugt keinen zweiten.
- `failed` setzt den offenen Incident auf `danger` und lässt ihn offen.
- `resolve` löst einen aktiven oder fehlgeschlagenen Incident auf.
- Ein späterer erfolgreicher Lauf kann damit einen vorherigen Fehler auflösen.

## Manuelle Testaktionen

Die Job-Ansicht enthält unter **Testaktionen** die drei Aktionen Start, Failed und Resolve. Diese dienen nur zum Testen der Meldelogik. Das eigentliche Backup oder ein anderer externer Vorgang läuft außerhalb dieser App.

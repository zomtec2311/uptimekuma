# Uptime Kuma Incidents – Nextcloud App 0.3.0

Die App steuert Meldungen an Uptime Kuma. Sie führt selbst keine Backups aus.

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

## Entwicklung

Für den Build der Vue-2-Oberfläche:

```bash
npm install
npm run build
```

Die App verwendet Vue 2.7.16 und Nextcloud 32+.

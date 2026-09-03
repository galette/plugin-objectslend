---
title: Dokumentation
description: Object lending and renting
---

This plugin provides:

* objects management (description, size, length, price, ...)
* groups objects by categories,
* manage object state, and their presence in stock or not,
* manage lending and/or renting objects,
* contribution generation,
* ...

## Installation

Laden Sie zunächst das Plugin herunter:

* [Get latest ObjectsLend
  plugin!](https://github.com/galette-plugins/plugin-objectslend/releases/latest)
* [Get ObjectsLend plugin nightly
  build!](https://github.com/galette-plugins/plugin-objectslend/releases/tag/nightly)

Extrahieren Sie das heruntergeladene Archiv im Verzeichnis Galette `plugins`.
Zum Beispiel unter Linux (Ersetzen Sie `{url}` und `{version}` durch korrekte
Werte):

```bash
$ cd /var/www/html/galette/plugins
$ wget {url}
$ tar xjvf galette-plugin-objectslend-{version}.tar.bz2
```

## Datenbank Initialisierung

Um zu funktionieren, benötigt dieses Plugin mehrere Tabellen in der Datenbank.
Siehe [Galette Plugins
Management-Schnittstelle](https://doc.galette.eu/en/master/plugins/index.html#plugins-managment).

And this is finished; ObjectsLend plugin is installed :)

## Plugin usage

Once plugin has been installed, a `Object lend` group is added to Galette menu.

Defaults status are provided at installation, but they can not fit your needs,
you can of course define your own.

![The list of object status](images/status.png)

Define status, create categories and objects; users can lend objects with a
reason, then give them back with location.

A lend history is provided for administrators and staff members from object
page.

### Einstellungen

Several preferences allows to change plugin behavior.

![The plugin preferences](images/plugin_preferences.png)

![The images preferences](images/images_preferences.png)

![The display preferences](images/display_preferences.png)

From this screen, you can define if members can lend objects or not, if it
should create a new contribution (and its type and description), if image should
be displayed in objects list, and thumbnails size.

It is possible to activate the fullsize photo display.

> **Note** — Added in version 0.5.
> 
> Photos sent with previous plugin version were always resized, only the
> thumbnail was stored. If you want to get fullsize display, you will have to
> send photos again.

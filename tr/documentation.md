---
title: Documentation
description: Object lending and renting
---

Bu eklenti şunları sağlar:

* objects management (description, size, length, price, ...)
* groups objects by categories,
* manage object state, and their presence in stock or not,
* manage lending and/or renting objects,
* contribution generation,
* ...

## Kurulum

Öncelikle, eklentiyi indirin:

* [Get latest ObjectsLend
  plugin!](https://github.com/galette-plugins/plugin-objectslend/releases/latest)
* [Get ObjectsLend plugin nightly
  build!](https://github.com/galette-plugins/plugin-objectslend/releases/tag/nightly)

İndirilen arşivi Galette `plugins` dizinine çıkarın. Örneğin, Linux altında
(`{url}` ve `{version}` değerlerini doğru değerlerle değiştirerek):

```bash
$ cd /var/www/html/galette/plugins
$ wget {url}
$ tar xjvf galette-plugin-objectslend-{version}.tar.bz2
```

## Veritabanı başlatma

Çalışabilmesi için, bu eklenti veritabanında birkaç tablo gerektirir. Bkz.
[Galette eklenti yönetim
arayüzü](https://doc.galette.eu/en/master/plugins/index.html#plugins-managment).

And this is finished; ObjectsLend plugin is installed :)

## Eklenti kullanımı

Once plugin has been installed, a `Object lend` group is added to Galette menu.

Defaults status are provided at installation, but they can not fit your needs,
you can of course define your own.

![The list of object status](images/status.png)

Define status, create categories and objects; users can lend objects with a
reason, then give them back with location.

A lend history is provided for administrators and staff members from object
page.

### Preferences

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

---
title: Documentación
description: Object lending and renting
---

Este complemento proporciona:

* gestión de objetos (descripción, tamaño, longitud, precio, ...)
* agrupa objetos por categorías,
* gestiona el estado del objeto, y su presencia en stock o no,
* gestiona el préstamo y/o alquiler de objetos,
* generación de contribución,
* ...

## Instalación

Lo primero de todo, descarga el complemento:

* [Get latest ObjectsLend
  plugin!](https://github.com/galette-plugins/plugin-objectslend/releases/latest)
* [Get ObjectsLend plugin nightly
  build!](https://github.com/galette-plugins/plugin-objectslend/releases/tag/nightly)

Extrae el archivo descargado en la carpeta `plugin` de Galette . Por ejemplo, en
linux (sustituyendo `{url}` y `{version}` con los valores correctos):

```bash
$ cd /var/www/html/galette/plugins
$ wget {url}
$ tar xjvf galette-plugin-objectslend-{version}.tar.bz2
```

## Inicialización de base de datos

Para que funcione, este complemento necesita varias tablas en la base de datos.
Consulta [la interfaz de gestión de complementos de
Galette](https://doc.galette.eu/en/master/plugins/index.html#plugins-managment).

Y esto está terminado; se ha instalado el complemento ObjectsLend :)

## Plugin usage

Una vez que el complemento ha sido instalado, se añadirá un grupo `Préstamo de
objeto` al menú de Galette.

Con la instalación se proporcionan los estados por defecto, pero puede que no
encajen con tus necesidades, así que por supuesto puedes definir los tuyos
propios.

![The list of object status](images/status.png)

Define estados, crea categorías y objetos; los usuarios pueden prestar objetos
con un motivo, y después recuperarlos con una ubicación.

Desde la página del objeto se muestra un historial de préstamos para los
administradores y el personal interno.

### Ajustes

Varios ajustes permiten cambiar el funcionamiento del complemento.

![The plugin preferences](images/plugin_preferences.png)

![The images preferences](images/images_preferences.png)

![The display preferences](images/display_preferences.png)

Desde esta pantalla, puedes definir si los miembros pueden prestar objetos o no,
si se debe crear una nueva contribución (y su tipo y descripción), si se debe
mostrar una imagen en el listado de objetos, y el tamaño de la miniatura.

Es posible activar la pantalla de la foto a tamaño completo.

> **Note** — Added in version 0.5.
> 
> Las fotos enviadas con las versiones anteriores del complemento siempre eran
> redimensionadas, solo se guardaba la miniatura. Si quieres obtener la pantalla
> a tamaño completo, tendrás que enviar las fotos otra vez.

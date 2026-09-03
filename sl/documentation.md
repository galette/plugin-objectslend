---
title: Dokumentacija
description: Object lending and renting
---

Ta vtičnik ponuja:

* upravljanje objektov (opis, velikost, dolžina, cena, ...)
* združuje predmete po kategorijah,
* upravljati stanje objektov in njihovo prisotnost na zalogi ali ne,
* upravljati posojanje in/ali najem predmetov,
* ustvarjanje prispevkov,
* ...

## Namestitev

Najprej prenesite vtičnik:

* [Get latest ObjectsLend
  plugin!](https://github.com/galette-plugins/plugin-objectslend/releases/latest)
* [Get ObjectsLend plugin nightly
  build!](https://github.com/galette-plugins/plugin-objectslend/releases/tag/nightly)

Razširite prenesen arhiv v imenik Galette `plugins`. Na primer v Linuxu
(zamenjajte `{url}` in `{version}` s pravilnimi vrednostmi):

```bash
$ cd /var/www/html/galette/plugins
$ wget {url}
$ tar xjvf galette-plugin-objectslend-{version}.tar.bz2
```

## Inicializacija baze podatkov

Za delovanje ta vtičnik potrebuje več tabel v bazi podatkov. Glejte [Vmesnik za
upravljanje vtičnikov
Galette](https://doc.galette.eu/en/master/plugins/index.html#plugins-managment).

In to je končano; vtičnik ObjectsLend je nameščen :)

## Plugin usage

Ko je vtičnik nameščen, se v meni Galette doda skupina »Posodi predmete«.

Privzete nastavitve so na voljo ob namestitvi, vendar morda ne ustrezajo vašim
potrebam, seveda lahko določite svoje.

![The list of object status](images/status.png)

Določite status, ustvarite kategorije in predmete; uporabniki lahko predmete
posodijo z razlogom in jih nato vrnejo z navedbo lokacije.

Zgodovina izposoje je na voljo administratorjem in zaposlenim na strani objekta.

### Nastavitve

Več nastavitev omogoča spreminjanje delovanja vtičnika.

![The plugin preferences](images/plugin_preferences.png)

![The images preferences](images/images_preferences.png)

![The display preferences](images/display_preferences.png)

Na tem zaslonu lahko določite, ali lahko člani posojajo predmete ali ne, ali naj
se ustvari nov prispevek (ter njegov tip in opis), ali naj se slika prikaže na
seznamu predmetov in velikost sličic.

Možno je aktivirati prikaz fotografij v polni velikosti.

> **Note** — Added in version 0.5.
> 
> Velikost fotografij, poslanih s prejšnjo različico vtičnika, je bila vedno
> spremenjena, shranjena je bila le sličica. Če želite prikazati fotografije v
> polni velikosti, jih boste morali poslati znova.

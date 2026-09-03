---
title: Documentazione
description: Object lending and renting
---

Questo componente aggiuntivo fornisce:

* gestione oggetti (descrizione, dimensione, lunghezza, prezzo, ...)
* raggruppa oggetti per categorie,
* gestisce lo stato degli oggetti, e la loro disponibilità in magazzino,
* gestisce i prestiti e/o il noleggio degli oggetti,
* generazione del contributo,
* ...

## Installazione

Prima di tutto, scaricare il plugin:

* [Get latest ObjectsLend
  plugin!](https://github.com/galette-plugins/plugin-objectslend/releases/latest)
* [Get ObjectsLend plugin nightly
  build!](https://github.com/galette-plugins/plugin-objectslend/releases/tag/nightly)

Estrai l'archivio scaricato nella cartella `plugins` di Galette. Per esempio, su
Linux (sostituendo `{url}` e `{version}` con i rispettivi valori):

```bash
$ cd /var/www/html/galette/plugins
$ wget {url}
$ tar xjvf galette-plugin-objectslend-{version}.tar.bz2
```

## Inizializzazione del database

Per poter funzionare, questo componente aggiuntivo richiede diverse nuove
tabelle nel database. Vedi [Interfaccia di gestione dei componenti aggiuntivi di
Galette](https://doc.galette.eu/en/master/plugins/index.html#plugins-managment).

Abbiamo finito; il componente aggiuntivo ObjectsLend è stato installato :)

## Plugin usage

Una volta che il componente aggiuntivo è stato installato, verrò aggiunto un
gruppo `Prestiti oggetti` al menu di Galette.

L'installazione prevede uno stato predefinito, che, se non ti soddisfa, può
ovviamente essere modificato.

![The list of object status](images/status.png)

Definisci stato, crea categorie e oggetti; gli utenti possono concedere a
prestito con una motivazione, e poi restituirli con un'ubicazione.

Agli amministratori e membri dello staff viene fornita una cronologia dei
prestiti.

### Preferenze

Il comportamento del componente aggiuntivo può essere ampiamente personalizzato.

![The plugin preferences](images/plugin_preferences.png)

![The images preferences](images/images_preferences.png)

![The display preferences](images/display_preferences.png)

In questa pagina, puoi permettere ai membri di concedere a prestito gli oggetti,
se ciò darà adito ad un nuovo contributo (con tipo e descrizione), se l'immagine
dovrà essere mostrata nella lista degli oggetti e la dimensione delle anteprime.

E' possibile attivare la visualizzazione a dimensione intera.

> **Note** — Added in version 0.5.
> 
> Le foto inviate con la versione precedente venivano sempre ridimensionate, e
> veniva salvata solo l'anteprima. Se ora volessi avere le foto a dimensione
> intera, devi caricarle nuovamente.

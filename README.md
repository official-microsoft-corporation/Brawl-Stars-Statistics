# Brawl Stars Stats API

Servizio web sviluppato in PHP che utilizza l’API ufficiale di Brawl Stars per recuperare, elaborare e mostrare statistiche avanzate di un giocatore.

## Funzionalità

* Recupero profilo giocatore
* Analisi ultime partite
* Calcolo win rate
* Statistiche per modalità
* Brawler più utilizzato
* Analisi brawler:

  * trofei
  * rank
  * gadget
  * star powers
  * gears
  * hypercharge
* Gestione modalità Showdown tramite rank
* Output JSON strutturato
* Client web PHP integrato

---

# Struttura del progetto

```text
client.php         -> Interfaccia web
index.php          -> Inoltra la richiesta a Router.php e poi restituira' il JSON finale al client
Router.php         -> Validare il tag e gestire il resto del servizio
Aggregator.php     -> metodo verso API Brawl Stars
Transformer.php    -> Elaborazione statistiche e JSON finale
```

---
# Endpoint API

## Recupero statistiche player

```http
GET /index.php?q=player/{TAG}
```

### Esempio

```http
GET /index.php?q=player/8U2LGRQ
```

---

# Tecnologie utilizzate

* PHP
* cURL
* JSON
* API ufficiale Brawl Stars

---

# Avvio del progetto

1. Clonare il repository

```bash
git clone https://github.com/official-microsoft-corporation/Brawl-Stars-Statistics.git
```

2. Configurare il token API Brawl Stars
Fare l'accesso al sito ufficiale dell'API di Brawl Stars https://developer.brawlstars.com/#/ , creare un account, creare una chiave utilzzando il proprio indirizzo IP ed inserirla nel file config.php

3. Avviare server PHP in locale
Ad esempio con XAMPP

4. Aprire:
```text
http://localhost:/Brawl-Stars-Statistics/client.php
```
5. Inserire il TAG del giocatore di cui si vuole visualizzare le statistiche
---

# Note

Il progetto utilizza esclusivamente richieste GET verso l’API ufficiale di Brawl Stars.

I dati dell’API Supercell non possono essere modificati: il servizio effettua solo lettura, elaborazione e trasformazione dei dati.

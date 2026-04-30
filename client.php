<?php
$risultato = null;
$errore = null;

if (isset($_GET['tag'])) { //controlla se il parametro TAG e' stato inserito
    $tag = $_GET['tag'];

    $urlbase = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']) . '/';
    //urlencode trasforma il # del tag in %23 (deve essere cosi per l'API)
    $url = $urlbase . 'index.php?q=player/' . urlencode($tag); 

    //setup curl
    $curl = curl_init(); //inizializzazione curl
    curl_setopt($curl, CURLOPT_URL, $url); 
    //RETURNTRANSFER: restituisce la risposta come stringa invece di stamparla
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    //esegue la richiesta e salva la risposta
    $risposta = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE); //prende il codice della risposta http
    curl_close($curl);

    if ($risposta === false) {
        $errore = 'Impossibile connettersi al server.';
    } else {
        //json decode con true converte il json ricevuto in array
        //restituisce null se la risposta non è un JSON valido
        $risultato = json_decode($risposta, true);

        // Controlla che json_decode abbia prodotto un array valido e non null
        if ($risultato === null) {
            $errore = 'Risposta non valida dal server. Codice HTTP: ' . $httpCode;
        } elseif (!$risultato['success']) {
            // Se il server ha risposto con un errore, estrae il messaggio

             //controlla se $risultato sia null, se non lo è assegna il suo valore a $errore, altrimenti assegna a $errore 'ErroreSconosciuto'
            $errore = $risultato['error']['message'] ?? 'Errore sconosciuto';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
</head>
<body>

<h1>Le API di Brawl Stars</h1>
<form method='GET' action=client.php> <!-- <form method='GET' action=index.php> -->
    <!-- serve a ripopolare il campo tag quando ricarica la pagina -->

    <input
        type="text"
        name="tag"
        placeholder="inserisci il tag"
        value="<?php
                if (isset($_GET['tag'])) {
                    echo htmlspecialchars($_GET['tag']);
                } else {
                    echo '';
                }
                ?>"
    > 
    <button type="submit">Cerca</button>
</form>

</body>
</html>

<?php
    //se errore
    if ($errore) {
        echo "<p>Errore: $errore</p>";
    }
    else if ($risultato !== null && $risultato['success']) {
        // Salva le sezioni in variabili per comodità
        $profilo = $risultato['data']['profile'];
        $stats = $risultato['data']['battle_stats'];
        $brawlers = $risultato['data']['brawlers'];
        $ranking = $risultato['data']['ranking'];
        $meta = $risultato['meta'];

        //TODO: completare display json risposta
    }
?>
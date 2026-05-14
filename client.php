<?php
$risultato = null;
$errore = null;

if (isset($_GET['tag'])) { //se tag esiste

    $tag = $_GET['tag'];

    //server HTTPHOST restituisce il dominiio (localhost)
    //server SCRIPTNAME restituisce il file corrente (progettotepsit/client.php) dirname restituisce solo la directory (progettotepsit)
    $urlbase = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']) . '/';

    //setup url per la curl verso index
    $url = $urlbase . 'index.php?q=player/' . $tag;

    
    //setup CURL
    
    $curl = curl_init();

    curl_setopt($curl, CURLOPT_URL, $url);

    //se non metti questo la curl stamperebbe a schermo, così salva in una variabile
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    $risposta = curl_exec($curl);

    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

    curl_close($curl);

    //getione errori API
    if ($risposta === false) {

        $errore = 'Impossibile connettersi al server.';
    }
    else {
        //converte il json in array php
        $risultato = json_decode($risposta, true);

        if ($risultato === null) {

            $errore =
                'Risposta non valida dal server. Codice HTTP: '
                . $httpCode;
        }
        elseif (!$risultato['success']) {

            $errore =
                $risultato['error']['message']
                ?? 'Errore sconosciuto';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>

    <meta charset="UTF-8">

    <title>Brawl Stars API</title>

    <style>

        body{
            font-family: Arial;
            margin: 30px;
            background: #f4f4f4;
        }

        h1{
            color: #222;
        }

        .box{
            background: white;
            padding: 20px;
            margin-top: 20px;
            border-radius: 10px;
        }

        table{
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th, td{
            border: 1px solid #ccc;
            padding: 10px;
            text-align: center;
        }

        th{
            background: #222;
            color: white;
        }

        .errore{
            color: red;
            font-weight: bold;
        }

    </style>
</head>

<body>

<h1>Statistiche Brawl Stars</h1>

<form method="GET" action="client.php">

    <input
        type="text"
        name="tag"
        placeholder="Inserisci il tag senza #"
        value="<?=
            isset($_GET['tag'])
            ? $_GET['tag']
            : ''
        ?>"
    >

    <button type="submit">Cerca</button>

</form>

<?php

//ERRORE
if ($errore) {

    echo "<p class='errore'>Errore: $errore</p>";
}


//RISULTATO DELLA RICHIESTA FATTA AD INDEX.PHP
elseif ($risultato !== null && $risultato['success']) {

    $profilo =
        $risultato['data']['profile'];

    $stats =
        $risultato['data']['battle_stats'];

    $brawlers =
        $risultato['data']['brawlers'];

    $meta =
        $risultato['meta'];

?>


<!-- PROFILO -->
<div class="box">

    <h2>Profilo</h2>

    <p><strong>Nome:</strong>
        <!-- serve a convertire caratteri html speciali in testo sicuro -->
        <?= htmlspecialchars($profilo['name']) ?>
    </p>

    <p><strong>Tag:</strong>
        <?= strtoupper($profilo['tag']) ?>
    </p>

    <p><strong>Trofei:</strong>
        <?= $profilo['trophies'] ?>
    </p>

    <p><strong>Record Trofei:</strong>
        <?= $profilo['highest_trophies'] ?>
    </p>

    <p><strong>Livello:</strong>
        <?= $profilo['exp_level'] ?>
    </p>

    <p><strong>Vittorie 3v3:</strong>
        <?= $profilo['victories_3v3'] ?>
    </p>

    <p><strong>Vittorie Solo:</strong>
        <?= $profilo['solo_victories'] ?>
    </p>

    <p><strong>Vittorie Duo:</strong>
        <?= $profilo['duo_victories'] ?>
    </p>

    <p><strong>Club:</strong>
        <?= htmlspecialchars($profilo['club']['name'] ?? 'Nessun club') ?>
    </p>

</div>

<!-- STATISTICHE RECENTI -->

<div class="box">

    <h2>Statistiche Recenti</h2>

    <p><strong>Partite analizzate:</strong>
        <?= $stats['games_analyzed'] ?>
    </p>

    <p><strong>Vittorie:</strong>
        <?= $stats['wins'] ?>
    </p>

    <p><strong>Sconfitte:</strong>
        <?= $stats['losses'] ?>
    </p>

    <p><strong>Win Rate:</strong>
        <?= $stats['win_rate'] ?>%
    </p>

    <p><strong>Media trofei guadagnati per partita:</strong>
        <?= $stats['avg_trophy_change'] ?>
    </p>

    <p><strong>Brawler più usato:</strong>
        <?= $stats['most_used_brawler'] ?>
    </p>

</div>


<!-- MODALITA -->

<div class="box">

    <h2>Statistiche Modalità</h2>

    <table>

        <tr>
            <th>Modalità</th>
            <th>Partite</th>
            <th>Vittorie</th>
            <th>Win Rate</th>
        </tr>

        <?php foreach ($stats['mode_breakdown'] as $mode): ?>

        <tr>

            <td>
                <?= $mode['mode'] ?>
            </td>

            <td>
                <?= $mode['games'] ?>
            </td>

            <td>
                <?= $mode['wins'] ?>
            </td>

            <td>
                <?= $mode['win_rate'] ?>%
            </td>

        </tr>

        <?php endforeach; ?>

    </table>

</div>

<!-- BRAWLERS -->

<div class="box">

    <h2>Brawlers</h2>

    <table>

        <tr>

            <th>Nome</th>

            <th>Livello</th>

            <th>Rank</th>

            <th>Trofei</th>

            <th>Record Trofei</th>

            <th>Gadget</th>

            <th>Abilità Stellare</th>

            <th>Equipaggiamenti</th>

            <th>HyperCharge</th>

            <th>Buffies</th>

        </tr>

        <?php foreach ($brawlers as $b): ?>

        <tr>

            <td>
                <?= $b['name'] ?>
            </td>

            <td>
                <?= $b['power'] ?>
            </td>

            <td>
                <?= $b['rank'] ?>
            </td>

            <td>
                <?= $b['trophies'] ?>
            </td>

            <td>
                <?= $b['highest_trophies'] ?>
            </td>

            <td>
                <?= $b['gadgets_unlocked'] ?>
            </td>

            <td>
                <?= $b['star_powers_unlocked'] ?>
            </td>

           <td>

            <?php

            if (!empty($b['gears'])) {

                $gearNames = [];

                foreach ($b['gears'] as $gear) {

                    $gearNames[] = $gear['name'];
                }

               echo implode(', ', $gearNames);

            } else {

                echo '-';
            }

            ?>

            </td>

            <td>
                <!--? è operatore ternario (if hypercharge e valorizzata, stampa v altrimenti x)--->
                <?= !empty($b['hypercharges']) ? '✔' : '✘' ?>

            </td>

            <td>

            <?php

            $buffies = $b['buffies'];
            //il . è concatenazione
            echo
                'Gadget: ' . ($buffies['gadget'] ? '✔' : '✘')
                . ' | ' .
                'Abilità Stellare: ' . ($buffies['starPower'] ? '✔' : '✘')
                . ' | ' .
                'HyperCharge: ' . ($buffies['hyperCharge'] ? '✔' : '✘');

            ?>

            </td>

        </tr>

        <?php endforeach; ?>

    </table>

</div>


<!-- META -->

<div class="box">

    <h2>Meta</h2>

    <p><strong>Generato il:</strong>
        <?= $meta['generated_at'] ?>
    </p>

    <p><strong>Chiamate API:</strong>
        <?= $meta['api_calls_made'] ?>
    </p>

</div>

<?php
}
?>

</body>
</html>
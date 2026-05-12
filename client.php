<?php
$risultato = null;
$errore = null;

if (isset($_GET['tag'])) {

    $tag = $_GET['tag'];

    $urlbase = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']) . '/';

    $url = $urlbase . 'index.php?q=player/' . urlencode($tag);

    // =====================================
    // CURL
    // =====================================
    $curl = curl_init();

    curl_setopt($curl, CURLOPT_URL, $url);

    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    $risposta = curl_exec($curl);

    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

    curl_close($curl);

    if ($risposta === false) {

        $errore = 'Impossibile connettersi al server.';
    }
    else {

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
            ? htmlspecialchars($_GET['tag'])
            : ''
        ?>"
    >

    <button type="submit">Cerca</button>

</form>

<?php

// =====================================
// ERRORE
// =====================================
if ($errore) {

    echo "<p class='errore'>Errore: $errore</p>";
}

// =====================================
// RISULTATO
// =====================================
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

<!-- ===================================== -->
<!-- PROFILO -->
<!-- ===================================== -->
<div class="box">

    <h2>Profilo</h2>

    <p><strong>Nome:</strong>
        <?= htmlspecialchars($profilo['name']) ?>
    </p>

    <p><strong>Tag:</strong>
        <?= htmlspecialchars($profilo['tag']) ?>
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

<!-- ===================================== -->
<!-- STATISTICHE RECENTI -->
<!-- ===================================== -->
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

    <p><strong>Media trofei:</strong>
        <?= $stats['avg_trophy_change'] ?>
    </p>

    <p><strong>Brawler più usato:</strong>
        <?= htmlspecialchars($stats['most_used_brawler']) ?>
    </p>

</div>

<!-- ===================================== -->
<!-- MODALITA -->
<!-- ===================================== -->
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
                <?= htmlspecialchars($mode['mode']) ?>
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

<!-- ===================================== -->
<!-- BRAWLERS -->
<!-- ===================================== -->
<div class="box">

    <h2>Brawlers</h2>

    <table>

        <tr>

            <th>Nome</th>

            <th>Power</th>

            <th>Rank</th>

            <th>Trofei</th>

            <th>Record</th>

            <th>Gadget</th>

            <th>Star Powers</th>

            <th>Usato Recentemente</th>

            <th>Usage Rate</th>

            <th>Win Rate Recent</th>

        </tr>

        <?php foreach ($brawlers as $b): ?>

        <tr>

            <td>
                <?= htmlspecialchars($b['name']) ?>
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
                <?= $b['times_used_recently'] ?>
            </td>

            <td>
                <?= $b['usage_rate'] ?>%
            </td>

            <td>
                <?= $b['win_rate_recent'] ?>%
            </td>

        </tr>

        <?php endforeach; ?>

    </table>

</div>

<!-- ===================================== -->
<!-- META -->
<!-- ===================================== -->
<div class="box">

    <h2>Meta</h2>

    <p><strong>Generato il:</strong>
        <?= $meta['generated_at'] ?>
    </p>

    <p><strong>API Calls:</strong>
        <?= $meta['api_calls_made'] ?>
    </p>

</div>

<?php
}
?>

</body>
</html>
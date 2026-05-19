<?php

class Transformer {

    //se $dati['profilo'] esiste assegna il suo valore altrimenti [] (array vuoto)

    public function elabora($dati) {

        
        $profilo   = $dati['profilo'];
        $battlelog = $dati['battlelog'];
        $brawlers  = $profilo['brawlers'];

        //analizza statistiche del giocatore richiesto
        $stats = $this->calcolaStatistiche($battlelog, $profilo['tag']);

        //elabora i brawler del giocatore richiesto
        $brawlersElaborati = $this->elaboraBrawlers(
            $brawlers,
            $stats['brawler_usage'],
            $stats['brawler_wins'],
            $stats['games_analyzed']
        );

        return [

            'success' => true,

            //(tag => array)  : la chiave tag contiene quest array (associazione chiave valore) 
            'data' => [

                // PROFILO
                'profile' => [

                    'tag' => $profilo['tag'],
                    'name' => $profilo['name'],

                    'trophies' => $profilo['trophies'],
                    'highest_trophies' => $profilo['highestTrophies'],

                    'exp_level' => $profilo['expLevel'],

                    'solo_victories' => $profilo['soloVictories'],

                    'duo_victories' => $profilo['duoVictories'],

                    'victories_3v3' => $profilo['3vs3Victories'],

                    'club' => [
                        'name' => $profilo['club']['name'] ?? null,

                        'tag'  => $profilo['club']['tag'] ?? null,
                    ]
                ],

                // STATISTICHE RECENTI
                'battle_stats' => [
                    'games_analyzed' => $stats['games_analyzed'],

                    'wins' => $stats['wins'],

                    'losses' => $stats['losses'],

                    'win_rate' => $stats['win_rate'],

                    'avg_trophy_change' => $stats['avg_trophy_change'],

                    'most_used_brawler' => $stats['most_used_brawler'],

                    'mode_breakdown' => $stats['mode_breakdown'],
                ],

                // BRAWLERS
                'brawlers' => $brawlersElaborati,
            ],

            // META
            'meta' => [
                'generated_at' => date('c'),
                'api_calls_made' => 2,
            ]
        ];
    }

    // CALCOLO STATISTICHE RECENTI
    private function calcolaStatistiche($battlelog, $playerTag) {

        $partite = array_slice($battlelog['items'] ?? [], 0, 10);

        $wins = 0;
        $losses = 0;

        $trophyDiff = 0;

        $brawlerUsage = [];
        $brawlerWins = [];

        $modeGames = [];
        $modeWins = [];

        //scorre tutto l'array delle partite
        foreach ($partite as $partita) {

            $battle = $partita['battle'];

            $mode = $battle['mode'];

            //cerca SOLO il brawler del player richiesto
            $brawler = $this->trovaBrawler($battle, $playerTag);

            $nomeBrawler = $brawler['name'];
            
            //gestione vittorie delle modalità
            $result = null;

            $mode = $battle['mode'];

            // SHOWDOWN
            if ($mode === 'soloShowdown' || $mode === 'duoShowdown' || $mode === 'trioShowdown') {

                $rank = $battle['rank'];
                $result = 'defeat';

                if ($mode === 'soloShowdown') {
                    
                    if ($rank <= 4) {
                        $result = 'victory';
                    }

                //gestione di duo e trio 
                } else {

                    if ($rank <= 2) {
                        $result = 'victory';
                    }
                }
                
            }

            //gestione tutte altre modalità
            else {
                $result = $battle['result'] ?? null;
            }

            if ($result === 'victory') {
                $wins++;
            }

            if ($result === 'defeat') {
                $losses++;
            }

            //aggiunge i trofei di ogni partita
            $trophyDiff += $battle['trophyChange'];

            //utilizzo brawler per trovare il brawler piu giocato nelle partite reenti
            $brawlerUsage[$nomeBrawler] =($brawlerUsage[$nomeBrawler] ?? 0) + 1;

            // Vittorie per brawler
            if ($result === 'victory') {
                $brawlerWins[$nomeBrawler] = ($brawlerWins[$nomeBrawler] ?? 0) + 1;
            }

            // Modalità
            $modeGames[$mode] = ($modeGames[$mode] ?? 0) + 1;

            //segno vittorie per ogni singola modalità
            if ($result === 'victory') {
                $modeWins[$mode] = ($modeWins[$mode] ?? 0) + 1;
            }
        }
        //fine for each

        $games = count($partite);

        //win Rate 
        $winRate = $games > 0 ? round(($wins / $games) * 100, 1) : 0;

        //trofei medi guadagnati
        $avgTrophyChange = $games > 0 ? round($trophyDiff / $games, 1) : 0;

        //brawler più usato
        $mostUsed = null;

        
        //ordina brawler del giocatore per presenza nelle partite 
        arsort($brawlerUsage);

        //prende la prima chiave dell'array ordinato brawlerusage e la assegna a mostUsed
        $mostUsed = array_key_first($brawlerUsage);
        

        //breakdown modalità
        $modeBreakdown = [];

        foreach ($modeGames as $mode => $totGames) {

            $winsMode = $modeWins[$mode];

            $modeBreakdown[] = [
                'mode' => $mode,

                'games' => $totGames,

                'wins' => $winsMode,

                'win_rate' => $totGames > 0 ? round(($winsMode / $totGames) * 100, 1) : 0
            ];
        }

        return [
            'games_analyzed' => $games,

            'wins' => $wins,

            'losses' => $losses,

            'win_rate' => $winRate,

            'avg_trophy_change' => $avgTrophyChange,

            'most_used_brawler' => $mostUsed,

            'mode_breakdown' => $modeBreakdown,

            // dati interni
            'brawler_usage' => $brawlerUsage,

            'brawler_wins' => $brawlerWins,
        ];
    }

    // ELABORAZIONE BRAWLERS
    private function elaboraBrawlers($brawlers, $usage, $wins, $gamesAnalyzed) {

        $lista = [];

        foreach ($brawlers as $b) {

            $nome = $b['name'];

            $usato = $usage[$nome] ?? 0;

            $vinte = $wins[$nome] ?? 0;

            $usageRate = $gamesAnalyzed > 0 ? round(($usato / $gamesAnalyzed) * 100, 1) : 0;

            $winRate = $usato > 0 ? round(($vinte / $usato) * 100, 1) : 0;

            $lista[] = [

                'id' => $b['id'] ?? null,

                'name' => $nome,

                'power' => $b['power'],

                'rank' => $b['rank'],

                'trophies' => $b['trophies'],

                'highest_trophies' => $b['highestTrophies'],

                'gadgets_unlocked' => count($b['gadgets'] ?? []),

                'star_powers_unlocked' => count($b['starPowers'] ?? []),

                // EQUIPAGGIAMENTO
                'gears' => $b['gears'] ?? [],

                // HYPERCHARGE
                'hypercharges' => $b['hyperCharges'] ?? [],

                // BUFFIES
                'buffies' => $b['buffies'],
            ];
        }

        // Ordina per trofei
        usort($lista, function($a, $b) {
            return $b['trophies'] - $a['trophies'];
        });

        return $lista;
    }

    // TROVA IL BRAWLER DEL GIOCATORE
    private function trovaBrawler($battle, $playerTag = null) {

        // Modalità team
        if (isset($battle['teams'])) {

            foreach ($battle['teams'] as $team) {

                /*scorre tutte le squadre e cerca il tag del giocatore 
                per trovare il brawler che utilizza in quella partita */
                foreach ($team as $player) {

                    //se il tag è set e il tag inserito è uguale a quello in quell'indice dell'array
                    if (strtoupper($player['tag']) === strtoupper($playerTag) ) {
                        return $player['brawler'] ?? null;
                    }
                }
            }
        }

        // se non ci sono teams ma giocatori singoli
        if (isset($battle['players'])) {

            foreach ($battle['players'] as $player) {

                if (strtoupper($player['tag']) === strtoupper($playerTag) ) {
                    return $player['brawler'] ?? null;
                }
            }
        }
        return null;
    }
}
?>
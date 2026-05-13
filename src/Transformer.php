<?php

class Transformer {

    public function elabora($dati) {

        $profilo   = $dati['profilo'] ?? [];
        $battlelog = $dati['battlelog'] ?? [];
        $brawlers  = $profilo['brawlers'] ?? [];

        // Analizza statistiche del giocatore richiesto
        $stats = $this->calcolaStatistiche(
            $battlelog,
            $profilo['tag'] ?? null
        );

        // Arricchisce i brawler con usage_rate e win_rate
        $brawlersElaborati = $this->elaboraBrawlers(
            $brawlers,
            $stats['brawler_usage'],
            $stats['brawler_wins'],
            $stats['games_analyzed']
        );

        return [

            'success' => true,

            'data' => [

                // =====================================
                // PROFILO
                // =====================================
                'profile' => [

                    'tag' => $profilo['tag'] ?? null,
                    'name' => $profilo['name'] ?? null,

                    'trophies' => $profilo['trophies'] ?? 0,
                    'highest_trophies' =>
                        $profilo['highestTrophies'] ?? 0,

                    'exp_level' => $profilo['expLevel'] ?? 0,

                    'solo_victories' =>
                        $profilo['soloVictories'] ?? 0,

                    'duo_victories' =>
                        $profilo['duoVictories'] ?? 0,

                    'victories_3v3' =>
                        $profilo['3vs3Victories'] ?? 0,

                    'club' => [
                        'name' =>
                            $profilo['club']['name'] ?? null,

                        'tag'  =>
                            $profilo['club']['tag'] ?? null,
                    ]
                ],

                // =====================================
                // STATISTICHE RECENTI
                // =====================================
                'battle_stats' => [

                    'games_analyzed' =>
                        $stats['games_analyzed'],

                    'wins' =>
                        $stats['wins'],

                    'losses' =>
                        $stats['losses'],

                    'win_rate' =>
                        $stats['win_rate'],

                    'avg_trophy_change' =>
                        $stats['avg_trophy_change'],

                    'most_used_brawler' =>
                        $stats['most_used_brawler'],

                    'mode_breakdown' =>
                        $stats['mode_breakdown'],
                ],

                // =====================================
                // BRAWLERS
                // =====================================
                'brawlers' => $brawlersElaborati,
            ],

            // =====================================
            // META
            // =====================================
            'meta' => [

                'generated_at' => date('c'),

                'api_calls_made' => 2,
            ]
        ];
    }

    // =========================================================
    // CALCOLO STATISTICHE RECENTI
    // =========================================================
    private function calcolaStatistiche($battlelog, $playerTag) {

        $partite = array_slice($battlelog['items'] ?? [], 0, 10);

        $wins = 0;
        $losses = 0;

        $trophyDiff = 0;

        $brawlerUsage = [];
        $brawlerWins = [];

        $modeGames = [];
        $modeWins = [];

        foreach ($partite as $partita) {

            $battle = $partita['battle'] ?? [];

            $result = $battle['result'] ?? null;

            $mode = $battle['mode'] ?? 'unknown';

            // Cerca SOLO il brawler del player richiesto
            $brawler = $this->trovaBrawler(
                $battle,
                $playerTag
            );

            // Se il player non viene trovato, salta  (penso sia da togliere dato che il giocatore viene trovato per forza)
            if ($brawler === null) {
                continue;
            }

            $nomeBrawler = $brawler['name'] ?? 'Unknown';

            // =====================================
            // GESTIONE SHOWDOWN (SOLO / DUO)
            // =====================================

            //showdown non segna nel json vicotry o defeat, ma un rank che indica la posizione
            if ($mode === 'soloShowdown' || $mode === 'duoShowdown' || $mode === 'trioShowdown') {

                $rank = $battle['rank'] ?? null;

                if ($rank !== null) {

                    // SOLO SHOWDOWN
                    if ($mode === 'soloShowdown') {

                        if ($rank <= 4) {
                            $result = 'victory';
                        } else {
                            $result = 'defeat';
                        }
                    }else if ($mode === 'duoShowdown') {
                        if ($rank <= 2) {
                            $result = 'victory';
                        } else {
                            $result = 'defeat';
                        }
                    }

                    else if ($mode === 'trioShowdown') {
                        if ($rank <= 2) {
                            $result = 'victory';
                        } else {
                            $result = 'defeat';
                        }
                    }
                }
            }

            if ($result === 'victory') {
                $wins++;
            }

            if ($result === 'defeat') {
                $losses++;
            }

            // =====================================
            // Trofei
            // =====================================
            $trophyDiff += $battle['trophyChange'] ?? 0;

            // =====================================
            // Utilizzo brawler
            // =====================================
            $brawlerUsage[$nomeBrawler] =
                ($brawlerUsage[$nomeBrawler] ?? 0) + 1;

            // =====================================
            // Vittorie per brawler
            // =====================================
            if ($result === 'victory') {

                $brawlerWins[$nomeBrawler] =
                    ($brawlerWins[$nomeBrawler] ?? 0) + 1;
            }

            // =====================================
            // Modalità
            // =====================================
            $modeGames[$mode] =
                ($modeGames[$mode] ?? 0) + 1;

            if ($result === 'victory') {

                $modeWins[$mode] =
                    ($modeWins[$mode] ?? 0) + 1;
            }
        }

        $games = count($partite);

        // =====================================
        // Win Rate totale
        // =====================================
        $winRate = $games > 0
            ? round(($wins / $games) * 100, 1)
            : 0;

        // =====================================
        // Trofei medi
        // =====================================
        $avgTrophyChange = $games > 0
            ? round($trophyDiff / $games, 1)
            : 0;

        // =====================================
        // Brawler più usato
        // =====================================
        $mostUsed = null;

        if (!empty($brawlerUsage)) {

            arsort($brawlerUsage);

            $mostUsed = array_key_first($brawlerUsage);
        }

        // =====================================
        // Breakdown modalità
        // =====================================
        $modeBreakdown = [];

        foreach ($modeGames as $mode => $totGames) {

            $winsMode = $modeWins[$mode] ?? 0;

            $modeBreakdown[] = [

                'mode' => $mode,

                'games' => $totGames,

                'wins' => $winsMode,

                'win_rate' => $totGames > 0
                    ? round(($winsMode / $totGames) * 100, 1)
                    : 0
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

    // =========================================================
    // ELABORAZIONE BRAWLERS
    // =========================================================
    private function elaboraBrawlers(
        $brawlers,
        $usage,
        $wins,
        $gamesAnalyzed
    ) {

        $lista = [];

        foreach ($brawlers as $b) {

            $nome = $b['name'] ?? 'Unknown';

            $usato = $usage[$nome] ?? 0;

            $vinte = $wins[$nome] ?? 0;

            $usageRate = $gamesAnalyzed > 0
                ? round(($usato / $gamesAnalyzed) * 100, 1)
                : 0;

            $winRate = $usato > 0
                ? round(($vinte / $usato) * 100, 1)
                : 0;

            $lista[] = [

                'id' => $b['id'] ?? null,

                'name' => $nome,

                'power' => $b['power'] ?? 0,

                'rank' => $b['rank'] ?? 0,

                'trophies' => $b['trophies'] ?? 0,

                'highest_trophies' =>
                    $b['highestTrophies'] ?? 0,

                'gadgets_unlocked' =>
                    count($b['gadgets'] ?? []),

                'star_powers_unlocked' =>
                    count($b['starPowers'] ?? []),

                // =====================================
                // EQUIPAGGIAMENTO
                // =====================================
                'gears' => $b['gears'] ?? [],

                // =====================================
                // HYPERCHARGE
                // =====================================
                'hypercharges' => $b['hyperCharges'] ?? [],

                // =====================================
                // BUFFIES
                // =====================================
                'buffies' => $b['buffies'] ?? [
                    'gadget' => false,
                    'starPower' => false,
                    'hyperCharge' => false
                ],
            ];
        }

        // Ordina per trofei
        usort($lista, function($a, $b) {
            return $b['trophies'] - $a['trophies'];
        });

        return $lista;
    }

    // =========================================================
    // TROVA IL BRAWLER DEL GIOCATORE
    // =========================================================
    private function trovaBrawler($battle, $playerTag = null) {

        // Modalità team
        if (isset($battle['teams'])) {

            foreach ($battle['teams'] as $team) {

                foreach ($team as $player) {

                    if (
                        isset($player['tag']) &&
                        strtoupper($player['tag']) === strtoupper($playerTag)
                    ) {
                        return $player['brawler'] ?? null;
                    }
                }
            }
        }

        // Modalità players
        if (isset($battle['players'])) {

            foreach ($battle['players'] as $player) {

                if (
                    isset($player['tag']) &&
                    strtoupper($player['tag']) === strtoupper($playerTag)
                ) {
                    return $player['brawler'] ?? null;
                }
            }
        }

        return null;
    }
}

?>
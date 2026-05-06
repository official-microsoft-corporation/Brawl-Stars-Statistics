<?php
class Transformer {

    public function elabora($dati) {
        $brawlers    = $this->elaboraBrawlers($dati['brawlers']);
        $battleStats = $this->elaboraBattleStats($dati['battlelog'], $brawlers);

        // Estrae e rimuove il campo interno _brawler_uso
        $brawlerUso  = $battleStats['_brawler_uso'] ?? [];
        unset($battleStats['_brawler_uso']);

        // Calcola usage_rate per ogni brawler (quante volte usato / partite totali)
        $totalePartite = $battleStats['games_analyzed'];
        foreach ($brawlers as &$brawler) {
            $volteUsato = $brawlerUso[$brawler['name']] ?? 0;
            $brawler['usage_rate'] = $totalePartite > 0
                ? round($volteUsato / $totalePartite, 2)
                : 0;
        }
        unset($brawler); // rompe il riferimento dell'ultimo elemento dopo foreach con &

        return [
            'success' => true,
            'data'    => [
                'profile'      => $this->elaboraProfilo($dati['profilo']),
                'battle_stats' => $battleStats,
                'brawlers'     => $brawlers,
                'ranking'      => $this->elaboraRanking($dati['rankingGlobale'], $dati['rankingLocale'], $dati['brawlers']),
            ],
            'meta' => [
                'generated_at' => date('c'),
                'api_calls'    => $dati['api_calls'],
                'latency_ms'   => $dati['latency_ms'],
            ]
        ];
    }

    private function elaboraProfilo($p) {
        return [
            'tag'              => $p['tag']             ?? null,
            'name'             => $p['name']            ?? null,
            'trophies'         => $p['trophies']        ?? 0,
            'highest_trophies' => $p['highestTrophies'] ?? 0,
            'experience_level' => $p['expLevel']        ?? 0,
            'victories_3v3'    => $p['3vs3Victories']   ?? 0,
            'solo_victories'   => $p['soloVictories']   ?? 0,
            'duo_victories'    => $p['duoVictories']    ?? 0,
            'club'             => [
                'name' => $p['club']['name'] ?? null,
                'tag'  => $p['club']['tag']  ?? null,
            ],
        ];
    }

    private function elaboraBattleStats($battlelog, array $brawlersElaborati) {
        $partite   = array_slice($battlelog['items'] ?? [], 0, 10);
        $vittorie  = 0;
        $sconfitte = 0;
        $kills     = 0;
        $deaths    = 0;
        $totTrofeiCambiati = 0;

        // Per usage_rate: contiamo quante volte ogni brawler appare nelle partite
        $brawlerUso      = [];
        $brawlerVittorie = [];

        $modeConteggio = [];
        $modeVittorie  = [];

        foreach ($partite as $partita) {
            $battle    = $partita['battle'] ?? [];
            $risultato = $battle['result']  ?? '';

            if ($risultato === 'victory') $vittorie++;
            if ($risultato === 'defeat')  $sconfitte++;

            $totTrofeiCambiati += $battle['trophyChange'] ?? 0;

            // K/D: disponibile solo in alcune modalità (es. Brawl Ball non ha kill)
            $kills  += $battle['starPlayer']['brawler']['kills']  ?? 0;
            $deaths += $battle['starPlayer']['brawler']['deaths'] ?? 0;

            // Trova il brawler usato dal giocatore
            $brawlerUsato = $this->trovaBrawlerGiocatore($battle);
            if ($brawlerUsato !== null) {
                $nome = $brawlerUsato['name'] ?? 'Unknown';
                $brawlerUso[$nome]      = ($brawlerUso[$nome]      ?? 0) + 1;
                if ($risultato === 'victory') {
                    $brawlerVittorie[$nome] = ($brawlerVittorie[$nome] ?? 0) + 1;
                }
            }

            // Mode breakdown
            $mode = $battle['mode'] ?? 'unknown';
            $modeConteggio[$mode] = ($modeConteggio[$mode] ?? 0) + 1;
            if ($risultato === 'victory') {
                $modeVittorie[$mode] = ($modeVittorie[$mode] ?? 0) + 1;
            }
        }

        $totale    = count($partite);
        $winRate   = $totale > 0 ? round($vittorie / $totale, 2) : 0;
        $avgTrofei = $totale > 0 ? round($totTrofeiCambiati / $totale, 1) : 0;
        $kdRatio   = $deaths > 0 ? round($kills / $deaths, 2) : null;
        // null se nessuna modalità con K/D era presente

        // Brawler più usato
        $mostUsedName    = null;
        $mostUsedWinRate = 0;
        if (!empty($brawlerUso)) {
            arsort($brawlerUso);
            $mostUsedName = array_key_first($brawlerUso);
            $giocate = $brawlerUso[$mostUsedName];
            $vinte   = $brawlerVittorie[$mostUsedName] ?? 0;
            $mostUsedWinRate = $giocate > 0 ? round($vinte / $giocate, 2) : 0;
        }

        // Aggiunge usage_rate a ogni brawler in base alle partite analizzate
        // Passiamo $brawlerUso al Transformer principale tramite ritorno esteso
        // (viene usato in elaboraBrawlers separatamente, vedi sotto)

        // Mode breakdown
        $modeBreakdown = [];
        foreach ($modeConteggio as $mode => $games) {
            $wins = $modeVittorie[$mode] ?? 0;
            $modeBreakdown[] = [
                'mode'     => $mode,
                'games'    => $games,
                'wins'     => $wins,
                'win_rate' => $games > 0 ? round($wins / $games, 2) : 0,
            ];
        }

        return [
            'games_analyzed'    => $totale,
            'wins'              => $vittorie,
            'losses'            => $sconfitte,
            'win_rate'          => $winRate,
            'kd_ratio'          => $kdRatio,
            'most_used_brawler' => [
                'name'     => $mostUsedName,
                'win_rate' => $mostUsedWinRate,
            ],
            'avg_trophies_change' => $avgTrofei,
            'mode_breakdown'      => $modeBreakdown,
            // Passiamo l'uso grezzo per usage_rate nei brawlers
            '_brawler_uso'        => $brawlerUso,
        ];
    }

    private function trovaBrawlerGiocatore($battle) {
        if (isset($battle['teams'])) {
            foreach ($battle['teams'] as $team) {
                foreach ($team as $player) {
                    return $player['brawler'] ?? null;
                }
            }
        }
        if (isset($battle['players'])) {
            return $battle['players'][0]['brawler'] ?? null;
        }
        return null;
    }

    private function elaboraBrawlers($dati) {
        $lista = [];

        foreach ($dati['items'] ?? [] as $b) {
            $lista[] = [
                'id'                   => $b['id']              ?? null,
                'name'                 => $b['name']            ?? null,
                'power'                => $b['power']           ?? 0,
                'trophies'             => $b['trophies']        ?? 0,
                'highest_trophies'     => $b['highestTrophies'] ?? 0,
                'rank'                 => $b['rank']            ?? 0,
                'gadgets_unlocked'     => count($b['gadgets']    ?? []),
                'star_powers_unlocked' => count($b['starPowers'] ?? []),
                // usage_rate viene aggiunto dopo da elabora()
                'usage_rate'           => 0,
            ];
        }

        usort($lista, function($a, $b) {
            return $b['trophies'] - $a['trophies'];
        });

        return $lista;
    }

    private function elaboraRanking($rankingGlobale, $rankingLocale, $brawlersDati) {
        $brawlerRankings = [];
        foreach ($brawlersDati['items'] ?? [] as $b) {
            $brawlerRankings[] = [
                'brawler_name' => $b['name']     ?? null,
                'rank'         => $b['rank']     ?? 0,
                'trophies'     => $b['trophies'] ?? 0,
            ];
        }
        usort($brawlerRankings, function($a, $b) {
            return $b['trophies'] - $a['trophies'];
        });

        return [
            'global_rank'      => $rankingGlobale['rank'] ?? null,
            'local_rank'       => $rankingLocale['rank']  ?? null,
            'country_code'     => COUNTRY_CODE,
            'brawler_rankings' => $brawlerRankings,
        ];
    }
}

<?php
/**
 * Čitanje i analiza log fajla za statistiku poseta stranica.
 * @return array 
 */
function getPageStatsFromLog() {
    $logFilePath = dirname(__DIR__) . '/data/access_log.txt';
    $stats = [];
    $totalHits = 0;

    if (file_exists($logFilePath)) {
        $lines = file($logFilePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            // Find the position of 'Page: '
            $pagePos = strpos($line, 'Page: ');
            if ($pagePos !== false) {
                // Extract everything after 'Page: ' up to the next vertical bar ' |'
                $start = $pagePos + 6;
                $end = strpos($line, ' |', $start);
                
                if ($end !== false) {
                    $pageName = trim(substr($line, $start, $end - $start));
                    
                    if (!isset($stats[$pageName])) {
                        $stats[$pageName] = 0;
                    }
                    $stats[$pageName]++;
                    $totalHits++;
                }
            }
        }
        
        arsort($stats);
    }

    $formattedStats = [];
    foreach ($stats as $page => $hits) {
        $percentage = $totalHits > 0 ? round(($hits / $totalHits) * 100, 1) : 0;
        $formattedStats[] = [
            'page' => $page,
            'hits' => $hits,
            'percentage' => $percentage
        ];
    }

    return [
        'total_hits' => $totalHits,
        'pages' => $formattedStats
    ];
}
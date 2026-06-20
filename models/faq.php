<?php
require_once dirname(__DIR__) . '/models/functions/faq.php';

/**
 * Logika za dobijanje FAQ podataka sa validacijom i formatiranjem.
 * @return array Formatirani FAQ podaci
 */
function getFAQLogic() {
    $rawFAQs = getAllFAQsFromDB();
    
    return array_map(function($faq) {
        return [
            'id'       => (int)$faq->id,
            'question' => htmlspecialchars($faq->question),
            'answer'   => htmlspecialchars($faq->answer)
        ];
    }, $rawFAQs);
}
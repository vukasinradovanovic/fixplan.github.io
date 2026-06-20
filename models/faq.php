<?php
require_once dirname(__DIR__) . '/models/functions/faq.php';

/**
 * Process and sanitize FAQ items for frontend delivery
 * @return array Formatted FAQ data list
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
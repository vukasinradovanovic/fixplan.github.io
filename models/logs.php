<?php
require_once dirname(__DIR__) . '/models/functions/logs.php';

/**
 * Appends a structural tracking record line to the data text file logs
 * @return bool
 */
function writePageAccessLog() {
    $context = captureCurrentAccessContext();
    $logFilePath = dirname(__DIR__) . '/data/access_log.txt';

    // Added Role parameter inside the tracking string template
    $logLine = "[{$context['timestamp']}] | IP: {$context['ip']} | Page: {$context['page']} | Role: {$context['role']} | Identity: {$context['user']}\n";

    try {
        return file_put_contents($logFilePath, $logLine, FILE_APPEND | LOCK_EX) !== false;
    } catch (Exception $e) {
        error_log("Failed to write to access log file: " . $e->getMessage());
        return false;
    }
}
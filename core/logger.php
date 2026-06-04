<?php
/**
 * NeuralPress - Audit Logger
 */

namespace NeuralPress\Core;

class Logger {
    private static string $logFile = __DIR__ . '/../cache/system_audit.log';

    public static function log(string $message, string $level = 'INFO'): void {
        $entry = sprintf("[%s] [%s]: %s\n", date('Y-m-d H:i:s'), $level, $message);
        @file_put_contents(self::$logFile, $entry, FILE_APPEND);
    }
}

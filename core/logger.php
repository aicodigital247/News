<?php
/**
 * NeuralPress - Audit Logger
 */

namespace NeuralPress\Core;

class Logger {
    private static string $logFile = __DIR__ . '/../cache/system_audit.log';

    public static function log(string $message, string $level = 'INFO'): void {
        $dir = dirname(self::$logFile);
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }
        $entry = sprintf("[%s] [%s]: %s\n", date('Y-m-d H:i:s'), $level, $message);
        @file_put_contents(self::$logFile, $entry, FILE_APPEND);
    }
}

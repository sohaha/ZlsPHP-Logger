<?php

namespace Zls\Logger;

/*
 * Zls_Logger_File
 * @author        影浅
 * @email         seekwe@gmail.com
 * @copyright     Copyright (c) 2015 - 2017, 影浅, Inc.
 * @link          ---
 * @since         v0.0.1
 * @updatetime    2017-01-03 11:09
 */

use Z;

class FileWriter implements \Zls_Logger
{
    private $logsDirPath;
    private $log404;
    private $saveFile;

    public function __construct($logsDirPath, $log404 = true, $saveFile = true)
    {
        $this->log404      = $log404;
        $this->saveFile    = $saveFile;
        $this->logsDirPath = Z::realPath($logsDirPath) . '/';
    }

    public function write(\Zls_Exception $exception)
    {
        if (!$this->log404 && ($exception instanceof \Zls_Exception_404)) {
            return;
        }
        if ($this->saveFile) {
            $logsDirPath = $this->logsDirPath . date(Z::config()->getLogsSubDirNameFormat()) . '/';
            $showData    = $this->showDate();
            $debug       = z::debug('', false, true, false);
            $content     = str_repeat('=', 25) . (new \DateTime())->format('Y-m-d H:i:s u')
                . str_repeat('=', 25) . PHP_EOL
                . (Z::server('http_host', '') ? 'Url : ' . Z::host(true, true, true) . "\n" : '')
                . 'Type : ' . $exception->getErrorType() . PHP_EOL
                . 'Environment : ' . $exception->getEnvironment() . PHP_EOL
                . 'Message : ' . $exception->getErrorMessage() . PHP_EOL
                . 'File : ' . $exception->getFile() . PHP_EOL
                . 'Line : ' . $exception->getErrorLine() . PHP_EOL
                . 'WasteTime : ' . $debug['runtime'] . PHP_EOL
                . 'Memory : ' . $debug['memory'] . PHP_EOL
                . 'ClientIp : ' . Z::clientIp() . PHP_EOL
                . 'ServerIp : ' . Z::serverIp() . PHP_EOL
                . 'Hostname : ' . Z::hostname() . PHP_EOL
                . ($showData ? 'PostData : ' . json_encode(Z::post()) . PHP_EOL : '')
                . ($showData ? 'PostRawData : ' . json_encode(Z::postRaw()) . PHP_EOL : '')
                . ($showData ? 'CookieData : ' . json_encode(Z::cookie()) . PHP_EOL : '')
                . (!Z::isCli() ? 'ServerData : ' . json_encode(Z::server()) . PHP_EOL : '')
                . 'Trace : ' . $exception->getTraceAsString() . PHP_EOL . PHP_EOL;
            if (!is_dir($logsDirPath)) {
                mkdir($logsDirPath, 0700, true);
            }
            if (!file_exists($logsFilePath = $logsDirPath . 'logs.php')) {
                $content = '<?php defined("IN_ZLS") or die();?>' . "\n" . $content;
            }
            file_put_contents($logsFilePath, $content, LOCK_EX | FILE_APPEND);
        }
    }

    private function showDate()
    {
        return !Z::isCli() || Z::isSwoole(true);
    }
}

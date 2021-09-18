<?php

namespace Zls\Logger;

use Z;

/*
 * Zls_Logger_File
 */

class FileWriter implements \Zls_Logger
{
    private $logsDirPath;
    private $log404;
    private $saveFile;
    private $logsSubNameFormat;

    public function __construct($logsDirPath = 'errorLogs', $log404 = false, $saveFile = true, $logsSubNameFormat = 'Y-m-d/H')
    {
        $this->log404 = $log404;
        $this->saveFile = $saveFile;
        $this->logsSubNameFormat = $logsSubNameFormat;
        $this->logsDirPath = Z::realPath(Z::config()->getStorageDirPath() . $logsDirPath, true);
    }

    public function write(\Zls_Exception $exception)
    {
        if (!$this->log404 && ($exception instanceof \Zls_Exception_404)) {
            return;
        }
        if ($this->saveFile) {
            $now = time();
            $dir = explode('/', $this->logsSubNameFormat);
            if (count($dir) > 1) {
                $file = date(array_pop($dir), $now);
                $dir = date(join('/', $dir), $now);
            } else {
                $dir = '';
                $file = date($this->logsSubNameFormat, $now);
            }
            $logsDirPath = $this->logsDirPath . $dir . '/';
            $content = "\n";
            if (!Z::isCli()) {
                $content = 'URL : ' . Z::host(true, true, true) . "\n"
                    . 'ClientIP : ' . Z::clientIp() . "\n"
                    . 'ServerIP : ' . Z::serverIp() . "\n"
                    . 'ServerHostname : ' . Z::hostname() . "\n"
                    . (!Z::isCli() ? 'Server Data : ' . json_encode(Z::server()) . "\n" : '');
            }
            if ($this->showDate()) {
                $content .= 'Request Uri : ' . Z::server('request_uri') . "\n" .
                    'Get Data : ' . json_encode(Z::get()) . "\n" .
                    'Post Data : ' . json_encode(Z::post()) . "\n" .
                    'Cookie Data : ' . json_encode(Z::cookie()) . "\n";
            }
            $content .= $exception->renderCli() . "\n";
            if (!is_dir($logsDirPath)) {
                Z::forceUmask(function () use ($logsDirPath) {
                    mkdir($logsDirPath, 0777, true);
                });
            }
            if (!file_exists($logsFilePath = $logsDirPath . $file . '.log')) {
                // todo 进行日志清除
            }
            Z::forceUmask(function () use ($logsFilePath, $content) {
                file_put_contents($logsFilePath, $content, LOCK_EX | FILE_APPEND);
            });
        }
    }

    private function showDate()
    {
        return !Z::isCli();
    }
}

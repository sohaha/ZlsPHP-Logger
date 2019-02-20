<?php

namespace Zls\Logger;

/*
 * Zls_Logger_File
 * @author        影浅
 * @email         seekwe@gmail.com
 * @copyright     Copyright (c) 2015 - 2017, 影浅, Inc.
 * @updatetime    2019-2-20 14:40:20
 */

use Z;

class FileWriter implements \Zls_Logger
{
    private $logsDirPath;
    private $log404;
    private $saveFile;

    public function __construct($logsDirPath, $log404 = true, $saveFile = true)
    {
        $this->log404 = $log404;
        $this->saveFile = $saveFile;
        $this->logsDirPath = Z::realPath($logsDirPath) . '/';
    }

    public function write(\Zls_Exception $exception)
    {
        if (!$this->log404 && ($exception instanceof \Zls_Exception_404)) {
            return;
        }
        if ($this->saveFile) {
            $logsDirPath = $this->logsDirPath . date(Z::config()->getLogsSubDirNameFormat()) . '/';
            $content = 'URL : ' . Z::host(true, true, true) . "\n"
                . 'ClientIP : ' . Z::clientIp() . "\n"
                . 'ServerIP : ' . Z::serverIp() . "\n"
                . 'ServerHostname : ' . Z::hostname() . "\n"
                . (!$this->showDate() ? 'Request Uri : ' . Z::server('request_uri') . "\n" : '')
                . (!$this->showDate() ? 'Get Data : ' . json_encode(Z::get()) . "\n" : '')
                . (!$this->showDate() ? 'Post Data : ' . json_encode(Z::post()) . "\n" : '')
                . (!$this->showDate() ? 'Cookie Data : ' . json_encode(Z::cookie()) . "\n" : '')
                . (!Z::isCli() ? 'Server Data : ' . json_encode(Z::server()) . "\n" : '')
                . $exception->renderCli() . "\n";
            if (!is_dir($logsDirPath)) {
                Z::forceUmask(function () use ($logsDirPath) {
                    mkdir($logsDirPath, 0777, true);
                });
            }
            if (!file_exists($logsFilePath = $logsDirPath . 'logs.php')) {
                $content = '<?php defined("IN_ZLS") or die();?>' . "\n" . $content;
            }
            Z::forceUmask(function () use ($logsFilePath, $content) {
                file_put_contents($logsFilePath, $content, LOCK_EX | FILE_APPEND);
            },777);

        }
        if (z::isAjax()) {
            z::finish($exception->renderJson());
        }
    }

    private function showDate()
    {
        return !Z::isCli() || Z::isSwoole(true);
    }
}

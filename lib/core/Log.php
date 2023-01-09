<?php

namespace core;


/*
  84PHP开源框架

  ©2022 84PHP.com

  框架版本号：6.1.0
*/

class Log
{

    //获取等级
    private static function getLevel($LevelName)
    {
        if (strtolower($LevelName) == 'debug') {
            return 0;
        } elseif (strtolower($LevelName) == 'info') {
            return 1;
        } elseif (strtolower($LevelName) == 'notice') {
            return 2;
        } elseif (strtolower($LevelName) == 'warning') {
            return 3;
        } elseif (strtolower($LevelName) == 'error') {
            return 4;
        } else {
            return false;
        }
    }

    //添加记录
    public static function add($UnionData = []): bool
    {
        $Info = Common::quickParameter($UnionData, 'info', '内容', false, '');
        $LevelName = Common::quickParameter($UnionData, 'level', '等级', false, 'info');
        $Level = self::getLevel($LevelName);

        if ($Level === false) {
            return false;
        }

        $_SERVER['84PHP']['Log'][] = [
            'LevelName' => $LevelName,
            'Level' => $Level,
            'Content' => $Info,
            'Time' => (intval(microtime(true) * 1000) - intval(__TIME__ * 1000)) / 1000
        ];
        return true;
    }

    //写入文件
    public static function output(): bool
    {
        if (strlen($_SERVER['84PHP']['Config']['Base']['safeCode']) < 10) {
            return false;
        }

        if (strtoupper($_SERVER['84PHP']['Config']['Log']['interval']) == 'H') {
            $LogFileName = date('H\H', __TIME__);
        } elseif (strtoupper($_SERVER['84PHP']['Config']['Log']['interval']) == 'M') {
            $LogFileName = date('H\H_i', __TIME__);
        } elseif (strtoupper($_SERVER['84PHP']['Config']['Log']['interval']) == 'HM') {
            $LogFileName = date('H\H_i', __TIME__);
            if (__TIME__ % 60 < 30) {
                $LogFileName .= '_(1)';
            } else {
                $LogFileName .= '_(2)';
            }
        } else {
            $LogFileName = 'applog';
        }

        $AccessInfo = '';

        if ($_SERVER['84PHP']['Config']['Log']['access']) {
            $AccessInfo =
                '[access] IP:' . $_SERVER['REMOTE_ADDR'] .
                ' | DOMAIN:' . $_SERVER['SERVER_NAME'] .
                ' | METHOD:' . $_SERVER['REQUEST_METHOD'] .
                ' | REFERER:' . ((empty($_SERVER['HTTP_REFERER'])) ? '' : $_SERVER['HTTP_REFERER']) .
                ' | UA:' . ((empty($_SERVER['HTTP_USER_AGENT'])) ? '' : $_SERVER['HTTP_USER_AGENT']) .
                "\r\n";
        }

        $FilePath = '/temp/log/' . $_SERVER['84PHP']['Config']['Base']['safeCode'] . date('/Y-m/d', __TIME__);
        if (!file_exists(__ROOT__ . $FilePath)) {
            mkdir(__ROOT__ . $FilePath, 0777, true);
        }

        $Content = '### ' . date(
                'Y-m-d H:i:s',
                __TIME__
            ) . ' (' . __TIME__ . ")\r\n[path] " . __URI__ . "\r\n" . $AccessInfo;

        $ConfigLevel = self::getLevel($_SERVER['84PHP']['Config']['Log']['level']);

        if ($ConfigLevel === false) {
            return false;
        }

        foreach ($_SERVER['84PHP']['Log'] as $Val) {
            if ($Val['Level'] >= $ConfigLevel) {
                $Content .= '[' . $Val['LevelName'] . '] ' . $Val['Content'] . "\r\n<" . $Val['Time'] . "s>\r\n";
            }
        }

        $Content .= "\r\n";

        $_SERVER['84PHP']['Log'] = [];
        $Handle = fopen(__ROOT__ . $FilePath . '/' . $LogFileName . '.txt', 'a');
        if ($Handle) {
            if (flock($Handle, LOCK_EX)) {
                fwrite($Handle, $Content);
            }
            fclose($Handle);
        }
        return true;
    }

    //清空日志
    public static function clean()
    {
        $_SERVER['84PHP']['Log'] = [];
    }

    //获取累积日志
    public static function get()
    {
        return $_SERVER['84PHP']['Log'];
    }

    //调用方法不存在
    public static function __callStatic($Method, $Parameters)
    {
        Common::unknownStaticMethod(__CLASS__, $Method);
    }
}
<?php

namespace core;

/*
  84PHP开源框架

  ©2022 84PHP.com

  框架版本号：6.1.0
*/

class Filter
{

    //非空检查
    private static function emptyCheck($OpArray, $Value): bool
    {
        if (isset($OpArray[0]) && strtoupper(
                $OpArray[0]
            ) == 'TRUE' && ($Value === '' || $Value === null || $Value === [])) {
            return false;
        }
        return true;
    }

    //长度检查
    private static function lengthCheck($OpArray, $Value): bool
    {
        $Value = strval($Value);
        $StrLen = mb_strlen($Value);
        if (
            (isset($OpArray[1]) && $StrLen < intval($OpArray[1])) ||
            (isset($OpArray[2]) && intval($OpArray[2]) > 0 && $StrLen > intval($OpArray[2]))) {
            return false;
        }
        return true;
    }

    //指定规则检查
    private static function ruleCheck($OpArray, $Value)
    {
        if (empty($OpArray[3]) || empty($Value)) {
            return true;
        }
        if ($OpArray[3] == 'email') {
            return filter_var($Value, FILTER_VALIDATE_EMAIL);
        }
        if ($OpArray[3] == 'ip') {
            return filter_var($Value, FILTER_VALIDATE_IP);
        }
        $RuleName = $OpArray[3];
        if (!empty($_SERVER['84PHP']['Config']['Filter']['rule'][$RuleName])) {
            if (preg_match($_SERVER['84PHP']['Config']['Filter']['rule'][$RuleName], $Value) == 0) {
                return false;
            }
        }
        return true;
    }

    //按模式检查
    public static function byMode($UnionData = []): bool
    {
        $Field = Common::quickParameter($UnionData, 'field', '字段');
        $Optional = Common::quickParameter($UnionData, 'optional', '可选', false, []);
        $Mode = Common::quickParameter($UnionData, 'mode', '模式');
        $Mode = strtolower($Mode);
        if ($Mode != 'get' && $Mode != 'post' && $Mode != 'header') {
            Api::wrong(['level' => 'F', 'detail' => 'Error#M.7.0' . "\r\n\r\n @ " . $Mode, 'code' => 'M.7.0']);
        }
        foreach ($Field as $Key => $Val) {
            $TempOp = explode(',', $Val);
            $TempData = false;
            if ($Mode == 'post' && isset($_POST[$Key])) {
                $TempData = $_POST[$Key];
            } elseif ($Mode == 'get' && isset($_GET[$Key])) {
                $TempData = $_GET[$Key];
            } elseif ($Mode == 'header') {
                $KeyName = 'HTTP_' . str_replace('-', '_', strtoupper($Key));
                if (isset($_SERVER[$KeyName])) {
                    $TempData = $_SERVER[$KeyName];
                }
            }

            if ($TempData === false && !in_array($Key, $Optional)) {
                return false;
            }
            if (!self::emptyCheck($TempOp, $TempData) || !self::lengthCheck($TempOp, $TempData) || !self::ruleCheck(
                    $TempOp,
                    $TempData
                )) {
                return false;
            }
        }
        return true;
    }

    //从数据检查
    public static function byData($UnionData = []): bool
    {
        $Data = Common::quickParameter($UnionData, 'data', '数据');
        $Check = Common::quickParameter($UnionData, 'check', '校验');

        if (!self::emptyCheck($Check, $Data) || !self::lengthCheck($Check, $Data) || !self::ruleCheck($Check, $Data)) {
            return false;
        }
        return true;
    }

    //调用方法不存在
    public static function __callStatic($Method, $Parameters)
    {
        Common::unknownStaticMethod(__CLASS__, $Method);
    }
}
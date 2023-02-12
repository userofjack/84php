<?php

namespace core;

/*
  84PHP开源框架

  ©2022 84PHP.com

  框架版本号：6.1.0
*/

class Setting
{

    //检查配置文件
    private static function fileCheck($Module): bool
    {
        $FilePath = __ROOT__ . '/config/core/' . ucfirst($Module) . '.php';
        if (file_exists($FilePath)) {
            return true;
        }
        Api::wrong(['level' => 'F', 'detail' => 'Error#M.9.1', 'code' => 'M.9.1']);
        return true;
    }

    //数组转字符串
    private static function arrayToStr($Array)
    {
        $TempText = '[' . "\r\n";
        foreach ($Array as $Key => $Val) {
            if (is_string($Key)) {
                $TempText .= '\'' . str_replace("'", '"', $Key) . '\'=>';
            } else {
                $TempText .= $Key . '=>';
            }
            if (is_string($Val)) {
                $TempText .= '\'' . str_replace("'", '"', $Val) . '\',' . "\r\n";
            } elseif (is_bool($Val)) {
                if ($Val) {
                    $TempText .= 'TRUE,' . "\r\n";
                } else {
                    $TempText .= 'FALSE,' . "\r\n";
                }
            } elseif (is_array($Val)) {
                $TempText .= self::arrayToStr($Val) . ',' . "\r\n";
            } elseif (is_int($Val) || is_float($Val)) {
                $TempText .= $Val . ',' . "\r\n";
            } else {
                $TempText .= '\'\',' . "\r\n";
            }
        }
        $TempText = str_replace("\r\n", "\r\n    ", $TempText);
        $TempText = rtrim($TempText, ' ');
        $TempText .= ']';
        return str_replace(",\r\n]", "\r\n]", $TempText);
    }

    //变量转字符串
    private static function varToStr($ValueName, $Value): string
    {
        if (is_string($Value)) {
            return '\'' . $ValueName . '\'=>\'' . str_replace("'", '\\\'', $Value) . '\',' . "\r\n";
        } elseif (is_bool($Value)) {
            if ($Value) {
                return '\'' . $ValueName . '\'=>TRUE,' . "\r\n";
            } else {
                return '\'' . $ValueName . '\'=>FALSE,' . "\r\n";
            }
        } elseif (is_array($Value)) {
            return '\'' . $ValueName . '\'=>' . self::arrayToStr($Value) . ',' . "\r\n";
        } elseif (is_int($Value) || is_float($Value)) {
            return '\'' . $ValueName . '\'=>' . $Value . ',' . "\r\n";
        } else {
            return '\'' . $ValueName . '\'=>\'\',' . "\r\n";
        }
    }

    //获取配置项的值
    public static function get($UnionData = [])
    {
        $Module = Common::quickParameter($UnionData, 'module', '模块');
        $Name = Common::quickParameter($UnionData, 'name', '名称');

        self::fileCheck($Module);
        require_once(__ROOT__ . '/config/core/' . ucfirst($Module) . '.php');
        if (!isset($_SERVER['84PHP']['Config'][$Module][$Name])) {
            Api::wrong(['level' => 'F', 'detail' => 'Error#M.9.2', 'code' => 'M.9.2']);
        }
        return $_SERVER['84PHP']['Config'][$Module][$Name];
    }

    //写入配置项
    public static function set($UnionData = [])
    {
        $Module = Common::quickParameter($UnionData, 'module', '模块');
        $Name = Common::quickParameter($UnionData, 'name', '名称');
        $Value = Common::quickParameter($UnionData, 'value', '值');
        $Module = ucfirst($Module);

        $CodeText = self::fileCheck($Module);
        $OldValue = self::get(['module' => $Module, 'name' => $Name]);
        require_once(__ROOT__ . '/config/core/' . $Module . '.php');
        if (gettype($OldValue) != gettype($Value)) {
            Api::wrong(['level' => 'F', 'detail' => 'Error#M.9.3', 'code' => 'M.9.3']);
        }
        $CodeText = '<?php' . "\r\n" . '$_SERVER[\'84PHP_CONFIG\'][\'' . $Module . '\']=[' . "\r\n";
        foreach ($_SERVER['84PHP']['Config'][$Module] as $Key => $Val) {
            $CodeText .= '    ';
            if ($Key != $Name) {
                $CodeText .= self::varToStr($Name, $Val);
            } else {
                $CodeText .= self::varToStr($Name, $Value);
            }
        }
        $CodeText .= "\r\n];";
        $Handle = @fopen(__ROOT__ . '/config/core/' . $Module . '.php', 'w');
        if (!$Handle) {
            Api::wrong(['level' => 'F', 'detail' => 'Error#M.9.4', 'code' => 'M.9.4']);
        }
        fwrite($Handle, $CodeText);
        fclose($Handle);
    }

    //临时改变配置项
    public static function change($UnionData = [])
    {
        $Module = Common::quickParameter($UnionData, 'module', '模块');
        $Name = Common::quickParameter($UnionData, 'name', '名称');
        $Value = Common::quickParameter($UnionData, 'value', '值');

        if (!isset($_SERVER['84PHP']['Config'][$Module])) {
            Api::wrong(['level' => 'F', 'detail' => 'Error#M.9.4', 'code' => 'M.9.4']);
        }
        if (!isset($_SERVER['84PHP']['Config'][$Module][$Name])) {
            Api::wrong(['level' => 'F', 'detail' => 'Error#M.9.2', 'code' => 'M.9.2']);
        }
        if (gettype($_SERVER['84PHP']['Config'][$Module][$Name]) != gettype($Value)) {
            Api::wrong(['level' => 'F', 'detail' => 'Error#M.9.3', 'code' => 'M.9.3']);
        }
        $_SERVER['84PHP']['Config'][$Module][$Name] = $Value;
    }

    //调用方法不存在
    public static function __callStatic($Method, $Parameters)
    {
        Common::unknownStaticMethod(__CLASS__, $Method);
    }
}
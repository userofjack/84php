<?php

namespace core;

/*
  84PHP开源框架

  ©2022 84PHP.com

  框架版本号：6.1.0
*/

class Common
{
    //快捷传参
    public static function quickParameter($UnionData, $Name, $Dialect, $Must = true, $Default = null)
    {
        if (isset($UnionData[$Name])) {
            return $UnionData[$Name];
        } elseif (isset($UnionData[$Dialect])) {
            return $UnionData[$Dialect];
        } elseif (isset($UnionData[strtolower($Name)])) {
            return $UnionData[strtolower($Name)];
        } elseif (isset($UnionData[strtoupper($Name)])) {
            return $UnionData[strtoupper($Name)];
        } elseif (isset($UnionData[mb_convert_case($Dialect, MB_CASE_LOWER, 'UTF-8')])) {
            return $UnionData[mb_convert_case($Dialect, MB_CASE_LOWER, 'UTF-8')];
        } elseif (isset($UnionData[mb_convert_case($Dialect, MB_CASE_UPPER, 'UTF-8')])) {
            return $UnionData[mb_convert_case($Dialect, MB_CASE_UPPER, 'UTF-8')];
        }
        if ($Must) {
            $Stack = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
            $ErrorMsg = '';
            if (isset($Stack[1]['class'])) {
                $ErrorMsg = "\r\n\r\n @ " . $Stack[1]['class'] . $Stack[1]['type'] . $Stack[1]['function'] . '() @ ' . $Name . '（' . $Dialect . '）';
            }
            Api::wrong(['level' => 'F', 'detail' => 'Error#C.0.3' . $ErrorMsg, 'code' => 'C.0.3']);
        }
        return $Default;
    }

    //获取磁盘路径
    public static function diskPath($Path, $Prefix = '')
    {
        $Path = str_replace(['\\', '//'], ['/', '/'], $Path);
        if (substr($Path, 0, 1) == '/') {
            $Path = substr($Path, 1);
        }
        if (substr($Path, -1, 1) == '/') {
            $Path = substr($Path, 0, -1);
        }
        if (substr($Path, 0, strlen(__ROOT__)) != __ROOT__) {
            if (!empty($Prefix)) {
                $Path = __ROOT__ . $Prefix . '/' . $Path;
            } else {
                $Path = __ROOT__ . '/' . $Path;
            }
        }

        return $Path;
    }

    //方法不存在
    public static function unknownStaticMethod($ModuleName, $MethodName)
    {
        Api::wrong(
            [
                'level' => 'F',
                'detail' => 'Error#C.0.4 @ ' . $ModuleName . ' :: ' . $MethodName . '()',
                'code' => 'C.0.4'
            ]
        );
    }
}
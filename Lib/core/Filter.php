<?php
namespace core;

use core\Common;
use core\Api;

/*
  84PHP开源框架

  ©2022 84PHP.com

  框架版本号：6.0.0
*/

class Filter
{
    
    //非空检查
    private static function emptyCheck($OpArray,$Value)
    {
        if (isset($OpArray[0])&&strtoupper($OpArray[0])=='TRUE'&&($Value===''||$Value===NULL||$Value===[])) {
            return FALSE;
        }
        return TRUE;
    }
    
    //长度检查
    private static function lengthCheck($OpArray,$Value)
    {
        $Value=strval($Value);
        $StrLen=mb_strlen($Value);
        if (
        (isset($OpArray[1])&&$StrLen<intval($OpArray[1]))||
        (isset($OpArray[2])&&intval($OpArray[2])>0&&$StrLen>intval($OpArray[2])))
        {
            return FALSE;
        }
        return TRUE;
    }
    
    //指定规则检查
    private static function ruleCheck($OpArray,$Value)
    {
        if (empty($OpArray[3])||empty($Value)) {
            return TRUE;
        }
        if ($OpArray[3]=='email') {
            return filter_var($Value, FILTER_VALIDATE_EMAIL);
        }
        if ($OpArray[3]=='ip') {
            return filter_var($Value, FILTER_VALIDATE_IP);
        }
        $RuleName=$OpArray[3];
        if (!empty($_SERVER['84PHP']['Config']['Filter']['rule'][$RuleName])) {
            if (preg_match($_SERVER['84PHP']['Config']['Filter']['rule'][$RuleName],$Value)==0) {
                return FALSE;
            }
        }
        return TRUE;
    }

    //按模式检查
    public static function byMode($UnionData=[])
    {
        $Field=Common::quickParamet($UnionData,'field','字段');
        $Optional=Common::quickParamet($UnionData,'optional','可选',FALSE,[]);
        $Mode=Common::quickParamet($UnionData,'mode','模式');
        $Mode=strtolower($Mode);
        if ($Mode!='get'&&$Mode!='post'&&$Mode!='header') {
            Api::wrong(['level'=>'F','detail'=>'Error#M.7.0'."\r\n\r\n @ ".$TempOp[0],'code'=>'M.7.0']);
        }
        foreach ($Field as $Key => $Val) {
            $TempOp=explode(',',$Val);
            $TempData=FALSE;
            if ($Mode=='post'&&isset($_POST[$Key])) {
                $TempData=$_POST[$Key];
            }
            else if ($Mode=='get'&&isset($_GET[$Key])) {
                $TempData=$_GET[$Key];
            }
            else if ($Mode=='header') {
                $KeyName='HTTP_'.str_replace('-','_',strtoupper($Key));
                if (isset($_SERVER[$KeyName])) {
                    $TempData=$_SERVER[$KeyName];
                }
            }

            if ($TempData===FALSE&&!in_array($Key,$Optional)) {
                return FALSE;
            }
            if (!self::emptyCheck($TempOp,$TempData)||!self::lengthCheck($TempOp,$TempData)||!self::ruleCheck($TempOp,$TempData)) {
                return FALSE;
            }

        }
        return TRUE;
    }

    //从数据检查
    public static function byData($UnionData=[])
    {
        $Data=Common::quickParamet($UnionData,'data','数据');
        $Check=Common::quickParamet($UnionData,'check','校验');
        
        $Operate=explode(',',$Data);

        if (!self::emptyCheck($Check,$Data)||!self::lengthCheck($Check,$Data)||!self::ruleCheck($Check,$Data)) {
            return FALSE;
        }
        return TRUE;
    }

    //调用方法不存在
    public static function __callStatic($Method,$Parameters)
    {
        Common::unknownStaticMethod(__CLASS__,$Method);
    }
}
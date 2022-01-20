<?php
namespace core;

use core\Common;
use core\Api;

/*
  84PHP开源框架

  ©2022 84PHP.com

  框架版本号：6.0.0
*/

require(__ROOT__.'/config/core/Ftp.php');

class Ftp
{

    //上传
    public static function up($UnionData=[])
    {
        $From=Common::quickParamet($UnionData,'from','本地路径');
        $To=Common::quickParamet($UnionData,'to','远程路径');
        $Timeout=Common::quickParamet($UnionData,'timeout','超时时间',FALSE,90);
        
        $From=Common::diskPath($From);

        $Connect=ftp_connect($_SERVER['84PHP']['Config']['Ftp']['Server'],$_SERVER['84PHP']['Config']['Ftp']['Port'],$Timeout);
        $Login=ftp_login($Connect,$_SERVER['84PHP']['Config']['Ftp']['User'],$_SERVER['84PHP']['Config']['Ftp']['Password']);
        if ((!$Connect)||(!$Login)) {
            Api::wrong(['level'=>'F','detail'=>'Error#M.1.0','code'=>'M.1.0']);
        }
        $Upload=ftp_put($Connect,$To,$From,FTP_ASCII); 
        ftp_close($Connect);
        if (!$Upload) {
            return FALSE;
        }
        else {
            return TRUE;
        }
    }

    //下载
    public static function down($UnionData=[])
    {
        $From=Common::quickParamet($UnionData,'from','远程路径');
        $To=__ROOT__.Common::quickParamet($UnionData,'to','本地路径');
        $Timeout=Common::quickParamet($UnionData,'timeout','超时时间',FALSE,90);
        
        $To=Common::diskPath($To);

        $Connect=ftp_connect($_SERVER['84PHP']['Config']['Ftp']['Server'],$_SERVER['84PHP']['Config']['Ftp']['Port'],$Timeout);
        $Login=ftp_login($Connect,$_SERVER['84PHP']['Config']['Ftp']['User'],$_SERVER['84PHP']['Config']['Ftp']['Password']);
        if ((!$Connect)||(!$Login)) {
            Api::wrong(['level'=>'F','detail'=>'Error#M.1.0','code'=>'M.1.0']);
        }
        $Download=ftp_get($Connect,$To,$From,FTP_ASCII); 
        ftp_close($Connect);
        if (!$Download) {
            return FALSE;
        }
        else {
            return TRUE;
        }
    }
    
    //调用方法不存在
    public static function __callStatic($Method,$Parameters)
    {
        Common::unknownStaticMethod(__CLASS__,$Method);
    }
}
<?php
/*
  84PHP开源框架

  ©2022 84PHP.com

  框架版本号：6.0.0
*/

require(__ROOT__.'/Config/Mail.php');

class Mail
{
    
    //SocketError
    private static function ssendError($Handle)
    {
        fclose($Handle);
        return FALSE;
    }
    
    //Socket发送
    public static function send($UnionData=[])
    {
        $Address=quickParamet($UnionData,'address','地址');
        $Title=quickParamet($UnionData,'title','标题');
        $Content=quickParamet($UnionData,'content','内容');
        $Timeout=quickParamet($UnionData,'timeout','超时时间',FALSE,15);

        $Send=NULL;
        $Response='';
        $Handle=fsockopen($_SERVER['84PHP']['Config']['Mail']['Server'],$_SERVER['84PHP']['Config']['Mail']['Port'],$Errno,$ErrMsg,$Timeout);
        if (!$Handle&&$Errno===0) {
            self::ssendError($Handle);
        }
        stream_set_blocking($Handle,1);
        $Response.=fgets($Handle,512);
        $Send='EHLO '.'=?utf-8?B?'.base64_encode($_SERVER['84PHP']['Config']['Mail']['FromName']).'?='."\r\n";
        if (fwrite($Handle,$Send)===FALSE) {
            return FALSE;
        }
        $Response.=fgets($Handle,512);
        while (TRUE) {
            $Response.=fgets($Handle,512);
            if (substr($Response,3,1)!='-'||empty($Response)) {
                break;
            }
        }
        $Send="AUTH LOGIN\r\n";
        if (fwrite($Handle,$Send)===FALSE) {
            self::ssendError($Handle);
        }
        $Response.=fgets($Handle,512);
        $Send=base64_encode($_SERVER['84PHP']['Config']['Mail']['UserName'])."\r\n";
        if (fwrite($Handle,$Send)===FALSE) {
            self::ssendError($Handle);
        }
        $Response.=fgets($Handle,512);
        $Send=base64_encode($_SERVER['84PHP']['Config']['Mail']['PassWord'])."\r\n";
        if (fwrite($Handle,$Send)===FALSE) {
            self::ssendError($Handle);
        }
        $Response.=fgets($Handle,512);
        $Send='MAIL FROM: <'.$_SERVER['84PHP']['Config']['Mail']['FromAddress'].">\r\n";

        if (fwrite($Handle,$Send)===FALSE) {
            self::ssendError($Handle);
        }
        $Response.=fgets($Handle,512);
        $Send='RCPT TO: <'.$Address."> \r\n";
        if (fwrite($Handle,$Send)===FALSE) {
            self::ssendError($Handle);
        }
        $Response.=fgets($Handle,512);
        $Send="DATA\r\n";
        if (fwrite($Handle,$Send)===FALSE) {
            self::ssendError($Handle);
        }
        $Response.=fgets($Handle,512);
        if (!empty($NewFromAddress)) {
            $Head='From: =?utf-8?B?'.base64_encode($_SERVER['84PHP']['Config']['Mail']['FromName']).'?= <'.$NewFromAddress.">\r\n";
        }
        else {
            $Head='From: =?utf-8?B?'.base64_encode($_SERVER['84PHP']['Config']['Mail']['FromName']).'?= <'.$_SERVER['84PHP']['Config']['Mail']['FromAddress'].">\r\n";
        }
        $Head.='To: '.$Address."\r\n";
        $Head.='Subject: =?utf-8?B?'.base64_encode($Title)."?=\r\n";
        $Head.="Content-Type: text/html; charset=utf-8\r\nContent-Transfer-Encoding:8bit\r\n";
        $Content=$Head."\r\n".$Content;
        $Content.="\r\n.\r\n";
        if (fwrite($Handle,$Content)===FALSE) {
            return FALSE;
        }
        $Send="QUIT\r\n";
        $Response.=fgets($Handle,512);
        
        if (strstr($Response,'535 Authentication')) {
            return FALSE;
        }
        
        fclose($Handle);
        return TRUE;
    }
    
    //调用方法不存在
    public static function __callStatic($Method,$Parameters)
    {
        unknownStaticMethod(__CLASS__,$Method);
    }
}
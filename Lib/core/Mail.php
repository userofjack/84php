<?php
namespace core;



/*
  84PHP开源框架

  ©2022 84PHP.com

  框架版本号：6.0.0
*/

class Mail
{
    
    //SocketError
    private static function sendError($Handle): bool
    {
        fclose($Handle);
        return FALSE;
    }
    
    //Socket发送
    public static function send($UnionData=[]): bool
    {
        $Address=Common::quickParameter($UnionData,'address','地址');
        $Title=Common::quickParameter($UnionData,'title','标题');
        $Content=Common::quickParameter($UnionData,'content','内容');
        $Timeout=Common::quickParameter($UnionData,'timeout','超时时间',FALSE,15);

        $Response='';
        $Handle=fsockopen($_SERVER['84PHP']['Config']['Mail']['server'],$_SERVER['84PHP']['Config']['Mail']['port'],$Errno,$ErrMsg,$Timeout);
        if (!$Handle&&$Errno===0) {
            self::sendError($Handle);
        }
        stream_set_blocking($Handle,1);
        $Response.=fgets($Handle,512);
        $Send='EHLO '.'=?utf-8?B?'.base64_encode($_SERVER['84PHP']['Config']['Mail']['fromName']).'?='."\r\n";
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
            self::sendError($Handle);
        }
        $Response.=fgets($Handle,512);
        $Send=base64_encode($_SERVER['84PHP']['Config']['Mail']['userName'])."\r\n";
        if (fwrite($Handle,$Send)===FALSE) {
            self::sendError($Handle);
        }
        $Response.=fgets($Handle,512);
        $Send=base64_encode($_SERVER['84PHP']['Config']['Mail']['passWord'])."\r\n";
        if (fwrite($Handle,$Send)===FALSE) {
            self::sendError($Handle);
        }
        $Response.=fgets($Handle,512);
        $Send='MAIL FROM: <'.$_SERVER['84PHP']['Config']['Mail']['fromAddress'].">\r\n";

        if (fwrite($Handle,$Send)===FALSE) {
            self::sendError($Handle);
        }
        $Response.=fgets($Handle,512);
        $Send='RCPT TO: <'.$Address."> \r\n";
        if (fwrite($Handle,$Send)===FALSE) {
            self::sendError($Handle);
        }
        $Response.=fgets($Handle,512);
        $Send="DATA\r\n";
        if (fwrite($Handle,$Send)===FALSE) {
            self::sendError($Handle);
        }
        $Response.=fgets($Handle,512);
        if (!empty($NewFromAddress)) {
            $Head='From: =?utf-8?B?'.base64_encode($_SERVER['84PHP']['Config']['Mail']['fromName']).'?= <'.$NewFromAddress.">\r\n";
        }
        else {
            $Head='From: =?utf-8?B?'.base64_encode($_SERVER['84PHP']['Config']['Mail']['fromName']).'?= <'.$_SERVER['84PHP']['Config']['Mail']['fromAddress'].">\r\n";
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
        if (fwrite($Handle,$Send)===FALSE) {
            self::sendError($Handle);
        }
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
        Common::unknownStaticMethod(__CLASS__,$Method);
    }
}
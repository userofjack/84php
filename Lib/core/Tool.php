<?php
namespace core;

/*
  84PHP开源框架

  ©2022 84PHP.com

  框架版本号：6.0.0
*/

use CURLFile;

class Tool
{

    //随机字符
    public static function random($UnionData=[])
    {
        $Mode=Common::quickParameter($UnionData,'mode','模式',FALSE,'AaN');
        $StringLength=Common::quickParameter($UnionData,'length','长度',FALSE,32);

        $String=NULL;
        $NWord='0123456789';
        $AUpperWord='QWERTYUIOPASDFGHJKLZXCVBNM';
        $ALowerWord='qwertyuiopasdfghjklzxcvbnm';
        $Word=NULL;
        if (strstr($Mode,'A')) {
            $Word.=$AUpperWord;
        }
        if (strstr($Mode,'a')) {
            $Word.=$ALowerWord;
        }
        if (strstr($Mode,'N')) {
            $Word.=$NWord;
        }
        if (empty($Mode)) {
            $Word=$NWord.$ALowerWord.$AUpperWord;
        }
        if (!empty($Word)) {
            for ($n=0;$n<$StringLength;$n++) {
                $Random=mt_rand(0,strlen($Word)-1);
                $String.=$Word[$Random];
            }
        }
        return $String;
    }
    
    //生成UUID
    public static function uuid($UnionData=[]): string
    {
        $MD5=Common::quickParameter($UnionData,'md5','md5',FALSE,FALSE);
        $Return=md5(memory_get_usage().self::random().uniqid('', true).mt_rand(1,99999).$_SERVER['REMOTE_ADDR']);
        
        if (!$MD5) {
            $Return=
                '{'.
                substr($Return,0,8).'-'.
                substr($Return,8,4).'-'.
                substr($Return,12,4).'-'.
                substr($Return,16,4).'-'.
                substr($Return,20,12).
                '}';
        }
        
        return $Return;
    }
    
    //获取Header指定字段的值
    public static function getHeader($UnionData=[]): array
    {
        $Field=Common::quickParameter($UnionData,'field','字段');
        $ReturnArray=[];
        foreach ($Field as $Val) {
            $FieldName='HTTP_'.str_replace('-','_',strtoupper($Val));

            if (isset($_SERVER[$FieldName])) {
                $ReturnArray[$Val]=$_SERVER[$FieldName];
            }
        }
        return $ReturnArray;
    }
    
    //向目标地址发送数据
    public static function send($UnionData=[])
    {
        $Url=Common::quickParameter($UnionData,'url','地址');
        $Mode=Common::quickParameter($UnionData,'mode','模式');
        $Data=Common::quickParameter($UnionData,'data','数据',FALSE,[]);
        $File=Common::quickParameter($UnionData,'file','文件',FALSE,[]);
        $Headers=Common::quickParameter($UnionData,'header','header',FALSE,[]);
        $Timeout=Common::quickParameter($UnionData,'timeout','超时时间',FALSE,15);
        $Ssl=Common::quickParameter($UnionData,'ssl','ssl',FALSE,FALSE);
        
        $Mode=strtoupper($Mode);
        if ($Mode!='GET'&&$Mode!='POST'&&$Mode!='PUT'&&$Mode!='DELETE'){
            return FALSE;
        }
        
        if (!function_exists('curl_init'))
        {
            Api::wrong(['level'=>'F','detail'=>'Error#M.6.0','code'=>'M.6.0']);
        }
        
        $SendData=[];
        $Handle=curl_init();
        
        if ($Mode=='GET'){
            if (!empty($Data)) {
               $Url.='?'.http_build_query($Data);
            }
        }
        
        curl_setopt($Handle,CURLOPT_URL,$Url);
        curl_setopt($Handle,CURLOPT_CONNECTTIMEOUT,0);
        curl_setopt($Handle,CURLOPT_TIMEOUT,$Timeout);
        curl_setopt($Handle,CURLOPT_HEADER,FALSE);
        curl_setopt($Handle,CURLOPT_HTTPHEADER,$Headers);
        
        curl_setopt($Handle,CURLOPT_AUTOREFERER,TRUE);
        curl_setopt($Handle,CURLOPT_FOLLOWLOCATION,TRUE);
        curl_setopt($Handle,CURLOPT_MAXREDIRS,20);
        curl_setopt($Handle,CURLOPT_RETURNTRANSFER,TRUE);
        
        curl_setopt($Handle,CURLOPT_SSL_VERIFYPEER,$Ssl); 
        curl_setopt($Handle,CURLOPT_SSL_VERIFYHOST,$Ssl);
                
        if ($Mode!='GET'){
            if ($Mode=='POST'){
                curl_setopt($Handle,CURLOPT_POST,TRUE);

                foreach ($File as $Key=>$Val) {
                    if (file_exists(Common::diskPath($Val))) {
                        $SendData[$Key]=new CURLFile(Common::diskPath($Val));
                    }
                } 
            }
            
            if ($Mode=='PUT'||$Mode=='DELETE'){
                curl_setopt($Handle,CURLOPT_CUSTOMREQUEST,$Mode);
            }
            
            foreach ($Data as $Key=>$Val) {
                $Val=urlencode($Val);
                $SendData[$Key]=$Val;
            }

            curl_setopt($Handle,CURLOPT_POSTFIELDS,$SendData);
        }
       
        $Response=curl_exec($Handle);
        $CurlErrno=curl_errno($Handle);
        curl_close($Handle);
        if ($Response===FALSE&&$CurlErrno>0) {
            Api::wrong(['level'=>'F','detail'=>'Error#M.6.1'."\r\n\r\n @ ".$CurlErrno,'code'=>'M.6.1']);
        }
        return $Response;
    }
        
    //调用方法不存在
    public static function __callStatic($Method,$Parameters)
    {
        Common::unknownStaticMethod(__CLASS__,$Method);
    }
}
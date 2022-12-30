<?php
namespace core;

/*
  84PHP开源框架

  ©2022 84PHP.com

  框架版本号：6.1.0
*/

class Load
{

    private static function upCall($FileError,$FileName,$FileSize,$FileTmpName,$SaveNameInfo,$PathInfo,$SizeInfo,$TypeInfo,$IgnoreErrorInfo)
    {
        if ($FileError>0) {
            switch ($FileError) {
                case 1:
                    $ModuleError='3';
                    break;
                case 2:
                    $ModuleError='4';  
                    break;
                case 3:
                    $ModuleError='5'; 
                    break;
                case 4:
                    $ModuleError='6'; 
                    break;
                default:
                    $ModuleError='7';
                    break;
            }
            if (!$IgnoreErrorInfo) {
                Api::wrong(['level'=>'F','detail'=>'Error#M.4.'.$ModuleError,'code'=>'M.4.'.$ModuleError]);
            }
            else {
                return NULL;
            }
        }
        $Exp=explode('.',$FileName);
        $Suffix=strtolower(end($Exp));
        
        $TypeInfo=explode(',',$TypeInfo);
        foreach ($TypeInfo as $Key => $Val) {
            $TypeInfo[$Key]=strtoupper($Val);
        }
        if (!in_array(strtoupper($Suffix),$TypeInfo)) {
            if (!$IgnoreErrorInfo) {
                Api::wrong(['level'=>'F','detail'=>'Error#M.4.8','code'=>'M.4.8']);
            }
            else {
                return NULL;
            }
        }
            
        if ($FileSize>$SizeInfo) {
            if (!$IgnoreErrorInfo) {
                Api::wrong(['level'=>'F','detail'=>'Error#M.4.9','code'=>'M.4.9']);
            }
            else {
                return NULL;
            }
        }
        if (empty($SaveNameInfo)) {
        $FileName=md5(date("YmdHis").mt_rand(1000000, 9999999).$_SERVER['REMOTE_ADDR']).'.'.$Suffix;
        }
        else {
            $FileName=$SaveNameInfo.'.'.$Suffix;
        }
        if (!file_exists($PathInfo)) {
            mkdir($PathInfo,0777,TRUE);
        }
        if (!move_uploaded_file($FileTmpName,$PathInfo.'/'.$FileName)) {
            if (!$IgnoreErrorInfo) {
                Api::wrong(['level'=>'F','detail'=>'Error#M.4.10','code'=>'M.4.10']);
            }
            else {
                return NULL;
            }
        }
        return str_replace(__ROOT__,'',$PathInfo.'/'.$FileName);

    }
    
    //上传
    public static function up($UnionData=[]): array
    {
        $FieldCheck=Common::quickParameter($UnionData,'field','字段');
        $Path=Common::quickParameter($UnionData,'path','路径');
        $Type=Common::quickParameter($UnionData,'type','类型');
        $SaveName=Common::quickParameter($UnionData,'save_name','保存名称',FALSE);
        $Size=Common::quickParameter($UnionData,'size','大小',FALSE);
        $Number=Common::quickParameter($UnionData,'number','数量',FALSE);
        $IgnoreError=Common::quickParameter($UnionData,'ignore_error','忽略错误',FALSE,FALSE);
        
        $Path=Common::diskPath($Path);
        $Return=[];
        if (!empty($FieldCheck)&&is_array($FieldCheck)) {
            foreach ($FieldCheck as $Val) {
                $TempOp=explode(',',$Val);
                $TempField=str_replace('[]','',$TempOp[0]);
                if ((!isset($_FILES[$TempField]))||(isset($TempOp[1])&&strtoupper($TempOp[1])=='TRUE'&&empty($_FILES[$TempField]['tmp_name']))) {
                    Api::wrong(['level'=>'F','detail'=>'Error#M.4.0'."\r\n\r\n @ ".$TempField,'code'=>'M.4.0']);
                }
                $TempPath=$Path;
                if (is_array($Path)) {
                    if (empty($Path[$TempField])) {
                        Api::wrong(['level'=>'F','detail'=>'Error#M.4.1'."\r\n\r\n @ ".$TempField,'code'=>'M.4.1']);
                    }
                    else {
                        $TempPath=$Path[$TempField];
                    }
                }
                $TempType=$Type;
                if (is_array($Type)) {
                    if (empty($Type[$TempField])) {
                        Api::wrong(['level'=>'F','detail'=>'Error#M.4.2'."\r\n\r\n @ ".$TempField,'code'=>'M.4.2']);
                    }
                    else {
                        $TempType=$Type[$TempField];
                    }
                }

                if (empty($SaveName[$TempField])) {
                    $TempSaveName=NULL;
                }
                else {
                    $TempSaveName=$SaveName[$TempField];
                }

                if (empty($Size[$TempField])||intval($Size[$TempField])<0) {
                    if (is_int($Size)) {
                        $TempSize=$Size*1024;
                    }
                    else {
                        $TempSize=10485760;
                    }
                }
                else {
                    $TempSize=intval($Size[$TempField])*1024;
                }
                                
                if (empty($_FILES[$TempField])) {
                    $Return[$TempField]=[];
                }
                elseif (is_string($_FILES[$TempField]['tmp_name'])) {
                    $Return[$TempField][0]=self::upCall($_FILES[$TempField]['error'],
                                                         $_FILES[$TempField]['name'],
                                                         $_FILES[$TempField]['size'],
                                                         $_FILES[$TempField]['tmp_name'],
                                                         $TempSaveName,
                                                         $TempPath,
                                                         $TempSize,
                                                         $TempType,
                                                         $IgnoreError
                                                        );
                }
                elseif (is_array($_FILES[$TempField]['tmp_name'])) {
                    if (empty($Number[$TempField])||intval($Number[$TempField])<0) {
                        if (is_int($Number)) {
                            $TempNumber=$Number;
                        }
                        else {
                            $TempNumber=1;
                        }
                    }
                    else {
                        $TempNumber=intval($Number[$TempField]);
                    }
                    if (count($_FILES[$TempField]['tmp_name'])<$TempNumber) {
                        $TempNumber=count($_FILES[$TempField]['tmp_name']);
                    }
                    for ($i=0;$i<$TempNumber;$i++) {
                        $Return[$TempField][$i]=self::upCall($_FILES[$TempField]['error'][$i],
                                                             $_FILES[$TempField]['name'][$i],
                                                             $_FILES[$TempField]['size'][$i],
                                                             $_FILES[$TempField]['tmp_name'][$i],
                                                             NULL,
                                                             $TempPath,
                                                             $TempSize,
                                                             $TempType,
                                                             $IgnoreError
                                                            );
                    }
                }
            }
        }
        return $Return;
    }
    
    //下载
    public static function down($UnionData=[]): string
    {
        $Url=Common::quickParameter($UnionData,'url','地址');
        $Path=Common::quickParameter($UnionData,'path','路径');
        $FileName=Common::quickParameter($UnionData,'filename','文件名',FALSE,'');
        $Headers=Common::quickParameter($UnionData,'header','header',FALSE,[]);
        $Timeout=Common::quickParameter($UnionData,'timeout','超时时间',FALSE,86400);
        $Ssl=Common::quickParameter($UnionData,'ssl','ssl',FALSE,FALSE);

        $Path=Common::diskPath($Path);
        
        set_time_limit($Timeout);
        if (!function_exists('curl_init'))
        {
            Api::wrong(['level'=>'F','detail'=>'Error#M.4.11','code'=>'M.4.11']);
        }

        if (!file_exists($Path)) {
            mkdir($Path,0777,TRUE);
        }
        
        if (!empty($FileName)){
            $NewName=$Path.$FileName;
        }
        else {
            $NewName=$Path.intval(__TIME__).mt_rand(111,999).'-'.basename($Url);
        }
        
        $Handle=curl_init();
        $FileHandle=@fopen($NewName,'wb');
        if (!$FileHandle) {
            Api::wrong(['level'=>'F','detail'=>'Error#M.4.12','code'=>'M.4.12']);
        }
        curl_setopt($Handle,CURLOPT_URL,$Url);
        curl_setopt($Handle,CURLOPT_CONNECTTIMEOUT,0);
        curl_setopt($Handle,CURLOPT_TIMEOUT,$Timeout);
        curl_setopt($Handle,CURLOPT_HEADER,FALSE);
        curl_setopt($Handle,CURLOPT_HTTPHEADER,$Headers);
        
        curl_setopt($Handle,CURLOPT_FILE, $FileHandle);
        curl_setopt($Handle,CURLOPT_FOLLOWLOCATION,TRUE);
        curl_setopt($Handle,CURLOPT_MAXREDIRS,20);
        
        curl_setopt($Handle,CURLOPT_SSL_VERIFYPEER,$Ssl); 
        curl_setopt($Handle,CURLOPT_SSL_VERIFYHOST,$Ssl);

        $Response=curl_exec($Handle);
        $CurlErrno=curl_errno($Handle);
        fclose($FileHandle);
        curl_close($Handle);
        if ($Response===FALSE&&$CurlErrno>0) {
            Api::wrong(['level'=>'F','detail'=>'Error#M.4.13'."\r\n\r\n @ ".$CurlErrno,'code'=>'M.4.13']);
        }
        
        return $NewName;
    }
    
    //调用方法不存在
    public static function __callStatic($Method,$Parameters)
    {
        Common::unknownStaticMethod(__CLASS__,$Method);
    }
}
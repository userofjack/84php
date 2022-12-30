<?php
namespace core;

/*
  84PHP开源框架

  ©2022 84PHP.com

  框架版本号：6.1.0
*/

use Throwable;

class Data
{
    private static $Handle;
    private static $Connect;

    private static function initial(): bool
    {
        if(!empty($_SERVER['84PHP']['Runtime']['Data']['initial'])){
            return TRUE;
        }

        self::$Handle=strtolower($_SERVER['84PHP']['Config']['Data']['handle']);
        
        if (self::$Handle=='redis') {
            self::redisConnect();
        }
        
        $_SERVER['84PHP']['Runtime']['Data']['initial']=1;
        return TRUE;
    }

    //设置
    public static function set($UnionData=[]): bool
    {
        $Key=Common::quickParameter($UnionData,'key','键');
        $Value=Common::quickParameter($UnionData,'value','值');
        $Time=Common::quickParameter($UnionData,'time','时间',FALSE,3600);
        $Prefix=Common::quickParameter($UnionData,'prefix','前缀',FALSE,'');

        self::initial();

        if ($Key=='') {
            return FALSE;
        }
        if ($Value==NULL) {
            $Value='';
        }
        if (!is_bool($Value)&&!is_array($Value)&&!is_int($Value)&&!is_float($Value)&&!is_string($Value)&&!is_object($Value)) {
            return FALSE;
        }
        $Time=intval($Time);
        if (self::$Handle=='file') {
            return self::setByFile($Prefix,$Key,$Value,$Time);
        }
        if (self::$Handle=='redis') {
            return self::setByRedis($Prefix,$Key,$Value,$Time);
        }
        return TRUE;
    }
    
    //获取
    public static function get($UnionData=[])
    {
        $Key=Common::quickParameter($UnionData,'key','键');
        $Prefix=Common::quickParameter($UnionData,'prefix','前缀',FALSE,'');
        $Callback=Common::quickParameter($UnionData,'callback','回调',FALSE);

        self::initial();

        if ($Key=='') {
            return NULL;
        }
        if (self::$Handle=='file') {
            $Result=self::getByFile($Prefix,$Key);
        }
        elseif (self::$Handle=='redis') {
            $Result=self::getByRedis($Prefix,$Key);
        }
        else {
            return NULL;
        }

        if ($Result===NULL&&is_object($Callback)) {
            return $Callback();
        }
        else {
            return $Result;
        }
    }
    
    //变量转字符串
    private static function varToStr($Value): string
    {
        return serialize($Value);
    }
    
    //字符串转变量
    private static function strToVar($String)
    {
        return unserialize($String);
    }
    
    //获取文件缓存路径
    private static function getFilePath($Prefix,$Key,$Mkdir=FALSE)
    {
        $MD5=md5($Key);
        $Path=__ROOT__.'/temp/data/'.$Prefix;
        $Level=intval($_SERVER['84PHP']['Config']['Data']['connect']['file']['level']);
        if ($_SERVER['84PHP']['Config']['Data']['connect']['file']['level']<1) {
            $Level=0;
        }
        if ($_SERVER['84PHP']['Config']['Data']['connect']['file']['level']>15) {
            $Level=15;
        }
        for ($i=0;$i<$Level;$i++) {
            $Path.='/'.$MD5[0].$MD5[1];
            $MD5=substr($MD5,2);
        }
        $Path=str_replace(['\\','//'],['/','/'],$Path);
        if ($Mkdir) {
            if (!is_dir($Path)) {
                mkdir($Path,0777,TRUE);
            }
        }
        
        $Path.='/'.$MD5.'.tmp';
        if ($Path[0]=='/') {
            $Path=substr($Path,1);
        }
        return $Path;
    }

    //设置文件緩存
    private static function setByFile($Prefix,$Key,$Value,$Time): bool
    {
        if ($Time<1) {
            return self::deleteByFile($Key,$Prefix);
        }
        $Cache=intval(__TIME__)+$Time."\r\n".self::varToStr($Value);
        $FileHandle=fopen(self::getFilePath($Prefix,$Key,TRUE),'w');
        if (!$FileHandle) {
            Api::wrong(['level'=>'F','detail'=>'Error#M.12.0','code'=>'M.12.0']);
        }
        fwrite($FileHandle,$Cache);
        fclose($FileHandle);
        return TRUE;
    }
    
    //删除文件緩存
    private static function deleteByFile($Key,$Prefix,$Path=''): bool
    {
        if ($Path=='') {
            $Path=self::getFilePath($Prefix,$Key);
        }
        if (file_exists($Path)) {
            $Result=unlink($Path);
        }
        else {
            $Result=TRUE;
        }
        return $Result;
    }

    //获取文件缓存
    private static function getByFile($Prefix,$Key)
    {
        $FilePath=self::getFilePath($Prefix,$Key);
        if (!file_exists($FilePath)) {
            return NULL;
        }
        $Cache=file_get_contents($FilePath);
        if ($Cache===FALSE) {
            Api::wrong(['level'=>'F','detail'=>'Error#M.12.4','code'=>'M.12.4']);
        }
        $ExpTime=intval(strtok($Cache, "\r\n"));
        if ($ExpTime<=0||$ExpTime<intval(__TIME__)) {
            if (mt_rand(1,$_SERVER['84PHP']['Config']['Data']['connect']['file']['clean'])==1) {
                self::deleteByFile($Key,$Prefix,$FilePath);
            }
            return NULL;
        }
        return self::strToVar(strtok("\r\n"));
    }
    
    //连接Redis
    private static function redisConnect()
    {
        self::$Connect=new Redis();
        try
        {
            self::$Connect->connect($_SERVER['84PHP']['Config']['Data']['connect']['redis']['address'],$_SERVER['84PHP']['Config']['Data']['connect']['redis']['port'],$_SERVER['84PHP']['Config']['Data']['connect']['redis']['timeout']);
        }
        catch(Throwable $t)
        {
            Api::wrong(['level'=>'F','detail'=>'Error#M.12.1','code'=>'M.12.1']);
        }
        if ($_SERVER['84PHP']['Config']['Data']['connect']['redis']['password']!='') {
            self::$Connect->auth($_SERVER['84PHP']['Config']['Data']['connect']['redis']['password']) ?:Api::wrong(['level'=>'F','detail'=>'Error#M.12.2','code'=>'M.12.2']);
        }
        self::$Connect->select($_SERVER['84PHP']['Config']['Data']['connect']['redis']['dbnumber'])?:Api::wrong(['level'=>'F','detail'=>'Error#M.12.3','code'=>'M.12.3']);
    }

    //设置Redis緩存
    private static function setByRedis($Prefix,$Key,$Value,$Time): bool
    {
        $MD5=md5($Key);
        if ($Prefix!='') {
            $Prefix.='_';
        }
        if ($Time<1) {
            self::$Connect->delete($MD5);
            return TRUE;
        }
        $Cache=self::varToStr($Value);
        self::$Connect->set($Prefix.$MD5,$Cache);
        self::$Connect->expire($Prefix.$MD5,$Time);
        return TRUE;
    }
    
    //获取Redis缓存
    private static function getByRedis($Prefix,$Key)
    {
        $MD5=md5($Key);
        if ($Prefix!='') {
            $Prefix.='_';
        }
        $Cache=self::$Connect->get($Prefix.$MD5);

        if (!$Cache) {
            return NULL;
        }
        return self::strToVar($Cache);
    }

    //调用方法不存在
    public static function __callStatic($Method,$Parameters)
    {
        Common::unknownStaticMethod(__CLASS__,$Method);
    }
}
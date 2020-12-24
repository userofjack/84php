<?php
/*
  84PHP开源框架

  ©2017-2021 84PHP.COM

  框架版本号：5.0.0
*/

class DataFile{

	//设置
	static function Set($Config,$K,$V,$Time){
		$FilePath='/Temp/Log/'.FrameworkConfig['SafeCode'].date('/Y-m/d',Runtime);
		if(!file_exists(RootPath.$FilePath)){
			mkdir(RootPath.$FilePath,0777,TRUE);
		}
		
		$Microtime=explode('.',strval(Runtime));

		$NewContent='###'."\r\n[path] ".URI."\r\n[time] ".date('Y-m-d H:i:s',Runtime).'.'.$Microtime[1].' <'.Runtime.">\r\n".$_SERVER['84PHP_AccessInfo'].$_SERVER['84PHP_LOG']."\r\n";
		$Handle=fopen(RootPath.$FilePath.'/'.$LogFileName.'.txt','a');
		if($Handle){
			if(flock($Handle,LOCK_EX)){
				fwrite($Handle,$NewContent);
			}
			fclose($Handle);
		}
	}

	//获取
	static function Get($Config,$K){
		
	}

	//调用方法不存在
	public function __call($Method,$Parameters){
		MethodNotExist(__CLASS__,$Method);
	}
}
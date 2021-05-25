<?php
/*
  84PHP开源框架

  ©2017-2021 84PHP.COM

  框架版本号：5.1.0
*/

Class Setting{
	
	//检查配置文件
	private static function FileCheck($Module){
		$FilePath=RootPath.'/Config/'.ucfirst($Module).'.php';
		if(file_exists($FilePath)){
			return TRUE;
		}
		Wrong::Report(['detail'=>'Error#M.9.1','code'=>'M.9.1']);
	}
		
	//数组转字符串
	private static function ArrayToStr($Array){
		$TempText='['."\r\n";
		foreach($Array as $Key => $Val){
			if(is_string($Key)){
				$TempText.='\''.str_replace("'",'"',$Key).'\'=>';
			}
			else{
				$TempText.=$Key.'=>';
			}
			if(is_string($Val)){
				$TempText.='\''.str_replace("'",'"',$Val).'\','."\r\n";
			}
			else if(is_bool($Val)){
				if($Val){
					$TempText.='TRUE,'."\r\n";
				}
				else{
					$TempText.='FALSE,'."\r\n";
				}
			}
			else if(is_array($Val)){
				$TempText.=self::ArrayToStr($Val).','."\r\n";
			}
			else if(is_int($Val)||is_float($Val)){
				$TempText.=$Val.','."\r\n";
			}
			else{
				$TempText.='\'\','."\r\n";
			}
		}
		$TempText=str_replace("\r\n","\r\n	",$TempText);
		$TempText=rtrim($TempText,'	'); 
		$TempText.=']';
		$TempText=str_replace(",\r\n]","\r\n]",$TempText);
		return $TempText;
	}
	
	//变量转字符串
	private static function VarToStr($ValueName,$Value){
		if(is_string($Value)){
			return '\''.$ValueName.'\'=>\''.str_replace("'",'\\\'',$Value).'\','."\r\n";
		}
		else if(is_bool($Value)){
			if($Value){
				return '\''.$ValueName.'\'=>TRUE,'."\r\n";
			}
			else{
				return '\''.$ValueName.'\'=>FALSE,'."\r\n";
			}
		}
		else if(is_array($Value)){
			return '\''.$ValueName.'\'=>'.self::ArrayToStr($Value).','."\r\n";
		}
		else if(is_int($Value)||is_float($Value)){
			return '\''.$ValueName.'\'=>'.$Value.','."\r\n";
		}
		else{
			return '\''.$ValueName.'\'=>\'\','."\r\n";
		}
	}

	//获取配置项的值
	public static function Get($UnionData=[]){
		$Module=QuickParamet($UnionData,'module','模块');
		$Name=QuickParamet($UnionData,'name','名称');
		
		self::FileCheck($Module);
		require_once(RootPath.'/Config/'.ucfirst($Module).'.php');
		if(isset($_SERVER['84PHP_CONFIG'][$Module][$Name])){
			return $_SERVER['84PHP_CONFIG'][$Module][$Name];
		}
		Wrong::Report(['detail'=>'Error#M.9.2','code'=>'M.9.2']);
	}
	
	//写入配置项项
	public static function Set($UnionData=[]){
		$Module=QuickParamet($UnionData,'module','模块');
		$Name=QuickParamet($UnionData,'name','名称');
		$Value=QuickParamet($UnionData,'value','值');
		$Module=ucfirst($Module);

		$CodeText=self::FileCheck($Module);
		$OldValue=self::Get($Module,$Name);
		require_once(RootPath.'/Config/'.$Module.'.php');
		if(gettype($OldValue)!=gettype($Value)){
			Wrong::Report(['detail'=>'Error#M.9.3','code'=>'M.9.3']);
		}
		$CodeText='<?php'."\r\n".'$_SERVER[\'84PHP_CONFIG\'][\''.$Module.'\']=['."\r\n";
		foreach($_SERVER['84PHP_CONFIG'][$Module] as $Key => $Val){
			$CodeText.='	';
			if($Key!=$Name){
				$CodeText.=self::VarToStr($Name,$Val);
			}
			else{
				$CodeText.=self::VarToStr($Name,$Value);
			}
		}
		$CodeText.="\r\n];";
		$Handle=@fopen(RootPath.'/Config/'.$Module.'.php','w');
		if(!$Handle){
			Wrong::Report(['detail'=>'Error#M.9.4','code'=>'M.9.4']);
		}
		fwrite($Handle,$CodeText);
		fclose($Handle);
	}
	
	//临时改变配置项
	public static function Change($UnionData=[]){
		$Module=QuickParamet($UnionData,'module','模块');
		$Name=QuickParamet($UnionData,'name','名称');
		$Value=QuickParamet($UnionData,'value','值');
				
		if(!isset($_SERVER['84PHP_CONFIG'][$Module])){
			Wrong::Report(['detail'=>'Error#M.9.4','code'=>'M.9.4']);
		}
		if(!isset($_SERVER['84PHP_CONFIG'][$Module][$Name])){
			Wrong::Report(['detail'=>'Error#M.9.2','code'=>'M.9.2']);
		}
		if(gettype($_SERVER['84PHP_CONFIG'][$Module][$Name])!=gettype($Value)){
			Wrong::Report(['detail'=>'Error#M.9.3','code'=>'M.9.3']);
		}
		$_SERVER['84PHP_CONFIG'][$Module][$Name]=$Value;
	}
	
	//调用方法不存在
	public static function __callStatic($Method,$Parameters){
		UnknownStaticMethod(__CLASS__,$Method);
	}
}
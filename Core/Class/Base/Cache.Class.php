<?php
/*****************************************************/
/*****************************************************/
/*                                                   */
/*               84PHP-www.84php.com                 */
/*                                                   */
/*****************************************************/
/*****************************************************/

/*
  本框架为免费开源、遵循Apache2开源协议的框架，但不得删除此文件的版权信息，违者必究。
  This framework is free and open source, following the framework of Apache2 open source protocol, but the copyright information of this file is not allowed to be deleted,violators will be prosecuted to the maximum extent possible.

  ©2017-2020 Bux. All rights reserved.

  框架版本号：3.0.0
*/

require(RootPath.'/Config/Cache.php');

class Cache{
	private $IncludeArray;
	
	//模块语法编译调用
	private function FastClassCall($WaitReplace){
		$this->IncludeArray[]=$WaitReplace[1];
		return	$WaitReplace[0]='$_SERVER[\'84PHP_MODULE\'][\''.$WaitReplace[1].'\']->'.$WaitReplace[2].'('.$WaitReplace[3].')';
	}
	
	//得到核心目录相对路径
	private function GetCorePath($Path){
		$DirArray=explode('/',$Path);
		$CorePath='';
		foreach ($DirArray as $Val){
			if(!empty($Val)){
				$CorePath.='/..';
			}
		}
		return $CorePath;
	}
	
	//模块调用语法翻译
	private function ModuleTranslate($FilePath){
		$Return=NULL;
		$this->IncludeArray=array();
		$TempWait=NULL;
		$NewClass=NULL;
		if(filesize($FilePath)>0){
			$TempWait=file_get_contents($FilePath);
			if($TempWait===FALSE){
				Wrong::Report(__FILE__,__LINE__,'Error#B.0.0');
			}
			$TempWait=str_replace(array(';;'),array(';'),$TempWait);
			$TempWait=preg_replace(array('/(?:^|\n|\s+)\/\/.*/',"/\/\*(.|\r\n)*\*\//"),array('',"\r\n"),$TempWait);
			$Return=preg_replace_callback('/\[[ \t]*([_a-zA-Z][_a-zA-Z0-9]*)[ \t]*>>[ \t]*([_a-zA-Z][_a-zA-Z0-9]*)[ \t]*\((.*?)\)[ \t]*\]/',array($this,'FastClassCall'),$TempWait);
			$Return=preg_replace(array('/(?:^|\n|\s+)#.*/','/\?>(\s\r\n)*/'),'',$Return)."?>\r\n";
			$this->IncludeArray=array_unique($this->IncludeArray);
			foreach($this->IncludeArray as $IncludeClass){
				$ClassType='';
				if(file_exists(RootPath.'/Core/Class/Base/'.ucfirst($IncludeClass).'.Class.php')){
					$ClassType='Base';
				}
				else if(file_exists(RootPath.'/Core/Class/Module/'.ucfirst($IncludeClass).'.Class.php')){
					$ClassType='Module';
				}
				else{
					Wrong::Report(__FILE__,__LINE__,'Error#B.0.1 @ '.ucfirst($IncludeClass).' @ '.$FilePath);
				}
				$NewClass.='LoadModule(\''.ucfirst($IncludeClass).'\',\''.$ClassType."');\r\n";
			}
			if(!empty($NewClass)){
				$Return="<?php\r\n".$NewClass."?>\r\n".$Return;
			}
		}
		return $Return;
	}
	
	//写入缓存
	private function WriteCache($Context,$FilePath){
		$Context=str_replace('exit;#','',$Context);
		$Context=preg_replace("/(\?>(\\s*<\?php)+)/","\r\n",$Context);
		$Context=preg_replace("/(<\?(\\s*\r?\n)+)/","<?php\r\n",$Context);
		$Context=preg_replace("/(\r?\n(\\s*\r?\n)+)/","\r\n",$Context);

		$Handle=@fopen($FilePath,'w');
		if(!$Handle){
			Wrong::Report(__FILE__,__LINE__,'Error#B.0.2');
		}
		if(!fwrite($Handle,$Context)){
			Wrong::Report(__FILE__,__LINE__,'Error#B.0.3');
		};
		fclose($Handle);
	}

	
	//前端语法翻译
	private function Translate($From,$To,$CacheChanged){
		$Temp=NULL;
		$Cache=NULL;
		$Template=NULL;
		$Data=NULL;

		if(($CacheChanged['T']||$CacheChanged['D'])&&file_exists($From['TPath'])){
			if(filesize($From['TPath'])>0){
				$Temp=file_get_contents($From['TPath']);
				if($Temp===FALSE){
					Wrong::Report(__FILE__,__LINE__,'Error#B.0.4');
				}
				$Template=preg_replace($_SERVER['84PHP_CONFIG']['Cache']['CacheMatch'],$_SERVER['84PHP_CONFIG']['Cache']['CacheReplace'],$Temp);
				$Template=str_replace(array("\t",'	'),'',$Template);
				$Template=preg_replace('/>\\s</','> <',$Template);
			}
		}

		if(($CacheChanged['T']||$CacheChanged['D'])&&file_exists($From['DPath'])){
			$Data=$this->ModuleTranslate($From['DPath']);
		}
		if($CacheChanged['T']||$CacheChanged['D']){
			$Cache=$Data."\r\n".$Template;
			
		}
				
		if($CacheChanged['T']||$CacheChanged['D']){
			$this->WriteCache($Cache,$To['CPath']);
		}
	}
	
	//文件信息
	private function FileInfo($FilePath){
		$ReturnArray=array(
			'path'=>$FilePath,
			'exist'=>FALSE,
			'time'=>0
		);
		if(file_exists($FilePath)){
			$ReturnArray['exist']=TRUE;
			$ReturnArray['time']=@filemtime($FilePath);
			if($ReturnArray['time']===FALSE){
				$ReturnArray['time']=0;
			}
		}
		return $ReturnArray;
	}

	
	//编译
	public function Compile($UnionData=array()){
		$Path=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'path','路径');
		$Force=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'force','强制编译',FALSE,'');

		if(!stristr($Force,'D')&&!FrameworkConfig['Debug']){
			return FALSE;
		}

		$CacheChanged=array('T'=>FALSE,'D'=>FALSE);
		$Path=$Path.'.php';
		$CachePath=RootPath.'/Temp/Cache';
				
		$CacheFile=$this->FileInfo($CachePath.$Path);
		$TSource=$this->FileInfo(RootPath.$_SERVER['84PHP_CONFIG']['Cache']['TPath'].$Path);
		$DSource=$this->FileInfo(RootPath.$_SERVER['84PHP_CONFIG']['Cache']['DPath'].$Path);
		
		if(!is_dir($CachePath)&&!@mkdir($CachePath,0777,TRUE)){
			Wrong::Report(__FILE__,__LINE__,'Error#B.0.5 @ '.$CachePath);
		};


		if((!$TSource['exist']&&!$DSource['exist'])){
			@unlink($CacheFile['path']);
		}
		
		$CacheDir=dirname($CacheFile['path']);
		while(TRUE){
			if(strlen($CacheDir)>strlen($CachePath)){
				if(is_dir($CacheDir)){
					if(count(scandir($CacheDir))==2){
						rmdir($CacheDir);
					}
					else{
						break;
					}
				}
				$CacheDir=dirname($CacheDir.'.xxx');
			}
			else{
				break;
			}
		}

		if(!$CacheFile['exist']||$CacheFile['time']>Runtime){
			if($TSource['exist']){
				$CacheChanged['T']=TRUE;
			}
			if($DSource['exist']){
				$CacheChanged['D']=TRUE;
			}
		}

		if($TSource['exist']&&$CacheFile['exist']){
			if($TSource['time']>$CacheFile['time']||$TSource['time']>Runtime||stristr($Force,'T')){
				if($TSource['time']>Runtime){
					touch($TSource['path']);
				}
				$CacheChanged['T']=TRUE;
			}
		}
		if($DSource['exist']&&$CacheFile['exist']){
			if($DSource['time']>$CacheFile['time']||$DSource['time']>Runtime||stristr($Force,'T')){
				if($DSource['time']>Runtime){
					touch($DSource['path']);
				}
				$CacheChanged['D']=TRUE;
			}
		}

		if(!is_dir(dirname($CacheFile['path']))&&($CacheChanged['T']||$CacheChanged['D'])){
			if(!mkdir(dirname($CacheFile['path']),0777,TRUE)){
				Wrong::Report(__FILE__,__LINE__,'Error#B.0.5 @ '.dirname($CacheFile['path']));
			}
		}

		$this->Translate(array(
			'TPath'=>$TSource['path'],
			'DPath'=>$DSource['path'],
		),array(
			'CPath'=>$CacheFile['path'],
		),$CacheChanged);
	}
	
	//遍历文件
	private function EveryFile($Path){
		$DirHandle=@opendir($Path);
		while($SourceFile=readdir($DirHandle)){
			if($SourceFile!="."&&$SourceFile!=".."){
				$AllFile=$Path."/".$SourceFile;
				$Exp=explode('.',$AllFile);
				if(is_dir($AllFile)){
					$this->EveryFile($AllFile);
				}
				else if(strtoupper(end($Exp))=='PHP'){
					$this->Compile(substr(str_replace(array(RootPath.$_SERVER['84PHP_CONFIG']['Cache']['TPath'],RootPath.$_SERVER['84PHP_CONFIG']['Cache']['DPath']),'',$AllFile),0,-4),TRUE);
				}
			}
		}
		closedir($DirHandle);
	}
	
	//重建所有缓存
	public function ReBuild($UnionData=array()){
		$this->EveryFile(RootPath.$_SERVER['84PHP_CONFIG']['Cache']['TPath']);
		$this->EveryFile(RootPath.$_SERVER['84PHP_CONFIG']['Cache']['DPath']);
	}
	
	//调用方法不存在
	public function __call($Method,$Parameters){
		MethodNotExist(__CLASS__,$Method);
	}
}
<?php
/*
  84PHP开源框架

  ©2017-2021 84PHP.COM

  框架版本号：5.0.0
*/

require(RootPath.'/Config/Cache.php');

class Cache{
	
	//数据模板语法检查
	private static function DataCheck($FilePath){
		$Return=NULL;
		if(filesize($FilePath)>0){
			$Return=file_get_contents($FilePath);
			if($Return===FALSE){
				Wrong::Report(__FILE__,__LINE__,'Error#M.11.0');
			}
			$Return=str_replace([';;'],[';'],$Return);
			$Return=preg_replace(['/(?:^|\n|\s+)\/\/.*/',"/\/\*(.|\r\n)*\*\//"],['',"\r\n"],$Return);
			$Return=preg_replace(['/(?:^|\n|\s+)#.*/','/\?>(\s\r\n)*/'],'',$Return);
		}
		return $Return;
	}
	
	//写入缓存
	private static function WriteCache($Context,$FilePath){
		$Context=preg_replace("/(\?>(\\s*<\?php)+)/","\r\n",$Context);
		$Context=preg_replace("/(<\?(\\s*\r?\n)+)/","<?php\r\n",$Context);
		$Context=preg_replace("/(\r?\n(\\s*\r?\n)+)/","\r\n",$Context);

		$Handle=@fopen($FilePath,'w');
		if(!$Handle){
			Wrong::Report(__FILE__,__LINE__,'Error#M.11.2');
		}
		if(!fwrite($Handle,$Context)){
			Wrong::Report(__FILE__,__LINE__,'Error#M.11.3');
		};
		fclose($Handle);
	}

	
	//前端模板语法翻译
	private static function TemplateTranslate($From,$To,$CacheChanged){
		$Temp=NULL;
		$Cache=NULL;
		$Template=NULL;
		$Data=NULL;

		if(($CacheChanged['T']||$CacheChanged['D'])&&file_exists($From['TPath'])){
			if(filesize($From['TPath'])>0){
				$Temp=file_get_contents($From['TPath']);
				if($Temp===FALSE){
					Wrong::Report(__FILE__,__LINE__,'Error#M.11.4');
				}
				$Template=preg_replace($_SERVER['84PHP_CONFIG']['Cache']['CacheMatch'],$_SERVER['84PHP_CONFIG']['Cache']['CacheReplace'],$Temp);
				$Template=str_replace(["	",'	'],'',$Template);
				$Template=preg_replace('/>\\s</','> <',$Template);
			}
		}

		if(($CacheChanged['T']||$CacheChanged['D'])&&file_exists($From['DPath'])){
			$Data=self::DataCheck($From['DPath']);
		}
		if($CacheChanged['T']||$CacheChanged['D']){
			if(!empty($Template)){
				$Template="\r\n?>\r\n".$Template;
			}
			$Cache=$Data.$Template;
			
		}
				
		if($CacheChanged['T']||$CacheChanged['D']){
			self::WriteCache($Cache,$To['CPath']);
		}
	}
	
	//文件信息
	private static function FileInfo($FilePath){
		$ReturnArray=[
			'path'=>$FilePath,
			'exist'=>FALSE,
			'time'=>0
		];
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
	public static function Compile($UnionData=[]){
		$Path=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'path','路径');
		$Force=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'force','强制编译',FALSE,FALSE);
		
		if(FrameworkConfig['Debug']){
			$Force=TRUE;
		}

		$CacheChanged=['T'=>FALSE,'D'=>FALSE];
		$CacheDir=RootPath.'/Temp/Cache';
				
		$CacheFile=self::FileInfo($CacheDir.$Path.'.php');
		
		if(!FrameworkConfig['Debug']&&!$Force&&$CacheFile['exist']&&$CacheFile['time']+$_SERVER['84PHP_CONFIG']['Cache']['ExpTime']>Runtime){
			return FALSE;
		}

		$TSource=self::FileInfo(RootPath.$_SERVER['84PHP_CONFIG']['Cache']['TPath'].$Path.'.php');
		$DSource=self::FileInfo(RootPath.$_SERVER['84PHP_CONFIG']['Cache']['DPath'].$Path.'.php');

		if(!is_dir($CacheDir)&&!@mkdir($CacheDir,0777,TRUE)){
			Wrong::Report(__FILE__,__LINE__,'Error#M.11.5'."\r\n\r\n @ ".$CacheDir);
		};


		if(!$TSource['exist']&&!$DSource['exist']){
			if($CacheFile['exist']){
				@unlink($CacheFile['path']);
			}
			$CheckPath=dirname($CacheDir.$Path.'/xxx');
			while(TRUE){
				if(strlen($CheckPath)>strlen($CacheDir)){
					if(is_dir($CheckPath)){
						if(count(scandir($CheckPath))==2){
							rmdir($CheckPath);
						}
						else{
							break;
						}
					}
					$CheckPath=dirname($CheckPath);
				}
				else{
					break;
				}
			}
			return FALSE;
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
			if($TSource['time']>$CacheFile['time']||$TSource['time']>Runtime||$Force){
				if($TSource['time']>Runtime){
					touch($TSource['path']);
				}
				$CacheChanged['T']=TRUE;
			}
		}
		if($DSource['exist']&&$CacheFile['exist']){
			if($DSource['time']>$CacheFile['time']||$DSource['time']>Runtime||$Force){
				if($DSource['time']>Runtime){
					touch($DSource['path']);
				}
				$CacheChanged['D']=TRUE;
			}
		}
		
		if(!FrameworkConfig['Debug']&&!$Force&&$CacheFile['exist']&&$CacheFile['time']+$_SERVER['84PHP_CONFIG']['Cache']['ExpTime']<=Runtime&&!$CacheChanged['T']&&!$CacheChanged['D']){
			touch($CacheFile['path']);
			return FALSE;
		}

		if(!is_dir(dirname($CacheFile['path']))&&($CacheChanged['T']||$CacheChanged['D'])){
			if(!mkdir(dirname($CacheFile['path']),0777,TRUE)){
				Wrong::Report(__FILE__,__LINE__,'Error#M.11.5'."\r\n\r\n @ ".dirname($CacheFile['path']));
			}
		}

		self::TemplateTranslate([
			'TPath'=>$TSource['path'],
			'DPath'=>$DSource['path']
		],['CPath'=>$CacheFile['path']],$CacheChanged);
	}
	
	//遍历文件
	private static function TraversalFile($Path){
		$DirHandle=@opendir($Path);
		while($SourceFile=readdir($DirHandle)){
			if($SourceFile!="."&&$SourceFile!=".."){
				$AllFile=$Path."/".$SourceFile;
				$Exp=explode('.',$AllFile);
				if(is_dir($AllFile)){
					self::TraversalFile($AllFile);
				}
				else if(strtoupper(end($Exp))=='PHP'){
					self::Compile(['path'=>substr(str_replace([RootPath.$_SERVER['84PHP_CONFIG']['Cache']['TPath'],RootPath.$_SERVER['84PHP_CONFIG']['Cache']['DPath']],'',$AllFile),0,-4),TRUE]);
				}
			}
		}
		closedir($DirHandle);
	}
	
	//重建所有缓存
	public static function ReBuild($UnionData=[]){
		self::TraversalFile(RootPath.$_SERVER['84PHP_CONFIG']['Cache']['TPath']);
		self::TraversalFile(RootPath.$_SERVER['84PHP_CONFIG']['Cache']['DPath']);
	}
	
	//调用方法不存在
	public static function __callStatic($Method,$Parameters){
		UnknownStaticMethod(__CLASS__,$Method);
	}
}
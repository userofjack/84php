<?php
/*
  84PHP开源框架

  ©2017-2021 84PHP.COM

  框架版本号：5.0.0
*/

class Dir{
	//目录文件属性
	public static function State($UnionData=[]){
		$Path=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'path','路径');
		
		if(!is_array($Path)){
			$PathArray=[$Path];
		}
		else{
			$PathArray=$Path;
		}

		clearstatcache();
		$Return=[];
		foreach ($PathArray as $Key => $Val){
			$TempArray=[];

			if(file_exists(DiskPath($Val))){
				if (is_readable(DiskPath($Val))){
					$TempArray['R']='Y';
				}
				else{
					$TempArray['R']='N';
				}
				
				if (is_writable(DiskPath($Val))){
					$TempArray['W']='Y';
				}
				else{
					$TempArray['W']='N';
				}
				
				if(is_dir(DiskPath($Val))){
					if (is_executable(DiskPath($Val))){
						$TempArray['Ex']='Y';
					}
					else{
						$TempArray['Ex']='N';
					}
				}
			}
			else{
				$TempArray=[];
			}
			$Return[$Val]=$TempArray;
		}
		return $Return;
	}
	
	//目录大小调用
	private static function SizeCall($Path){
		$DirSize=0;
		if(file_exists($Path)&&$DirHandle=@opendir($Path)){
			while($FileName=readdir($DirHandle)){
				if($FileName!="."&&$FileName!=".."){
					$SubFile=$Path."/".$FileName;
					if(is_dir($SubFile))
						$DirSize+=self::SizeCall($SubFile);
					if(is_file($SubFile))
						$DirSize+=filesize($SubFile);
				}
			}
			closedir($DirHandle);
			return $DirSize;
		}
		else{
			Wrong::Report(__FILE__,__LINE__,'Error#M.0.0');
		}
	}
	
	//目录大小
	public static function Size($UnionData=[]){
		$Path=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'path','路径');
		$Unit=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'unit','单位',FALSE,NULL);

		$DirSize=self::SizeCall(DiskPath($Path));
		
		if($Unit=='KB'){
			$DirSize=round($DirSize/pow(1024,1),2);
			return $DirSize;
		}
		elseif($Unit=='MB'){
			$DirSize=round($DirSize/pow(1024,2),2);
			return $DirSize;
		}
		elseif($Unit=='GB'){
			$DirSize=round($DirSize/pow(1024,3),2);
			return $DirSize;
		}
		else{
			return $DirSize;
		}
	}
	
	//删除目录调用
	private static function DeleteCall($Dir){
		if(file_exists($Dir)){
			if($DirHandle=@opendir($Dir)){
				while($FileName=readdir($DirHandle)){
					if($FileName!="."&&$FileName!=".."){
						$SubFile=$Dir."/".$FileName;
						if(is_dir($SubFile)){
							self::DelCall($SubFile);
						}
						if(is_file($SubFile)){
							@unlink($SubFile);
						}
					}
				}
				closedir($DirHandle);
				rmdir($Dir);
			}
			else{
				Wrong::Report(__FILE__,__LINE__,'Error#M.0.1');
			}
		}
	}
	
	//删除目录
	public static function Delete($UnionData=[]){
		$Path=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'path','路径');

		if(!is_array($Path)){
			self::DeleteCall(DiskPath($Path));
		}
		else{
			foreach($Path as $Val){
				self::DeleteCall(DiskPath($Path));
			}
		}
	}
	
	//复制目录调用
	private static function CopyCall($From,$To){
		if(!file_exists($From)){
			Wrong::Report(__FILE__,__LINE__,'Error#M.0.0');
		}
		if(is_file($To)){
			exit;
		}
		if(!file_exists($To)){
			mkdir($To,0777,TRUE);
		}
		if($DirHandle=@opendir($From)){
			while($FileName=readdir($DirHandle)){
				if($FileName!="." && $FileName!=".."){
					$FromPath=$From."/".$FileName;
					$ToPath=$To."/".$FileName;
					if(is_dir($FromPath)){
						self::CopyCall($FromPath,$ToPath);
					}
					if(is_file($FromPath)){
						copy($FromPath,$ToPath);
					}
				}
			}
			closedir($DirHandle);
		}
		else{
			Wrong::Report(__FILE__,__LINE__,'Error#M.0.1');
		}
	}
	
	//复制目录
	public static function Copy($UnionData=[]){
		$From=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'from','源路径');
		$To=QuickParamet($UnionData,__FILE__,__LINE__,__CLASS__,__FUNCTION__,'to','目标路径');

		self::CopyCall(DiskPath($Path), DiskPath($Path));
	}
	
	//调用方法不存在
	public function __call($Method,$Parameters){
		MethodNotExist(__CLASS__,$Method);
	}
}
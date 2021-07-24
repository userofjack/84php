<?php
/*
  84PHP开源框架

  ©2017-2021 84PHP.COM

  框架版本号：5.1.0
*/

require(RootPath.'/Config/Session.php');

class Session{

	//设置Token
	public static function Token($UnionData=[]){
		$Token=QuickParamet($UnionData,'token','token',FALSE,NULL);
		$SessionId=QuickParamet($UnionData,'id','id',FALSE,NULL);

		self::Start(['id'=>$SessionId]);

		if(!isset($_SESSION)){
			session_start();
		}
		if(!empty($Token)){
			$Token=Tool::Uuid(['md5'=>TRUE]);
		}
		if(!isset($_SESSION['84PHP_TOKEN'])){
			$_SESSION['84PHP_TOKEN']=[];
		}
		$ArrayLength=count($_SESSION['84PHP_TOKEN']);
		if($ArrayLength>=$_SERVER['84PHP_CONFIG']['Session']['TokenLimit']){
			$_SESSION['84PHP_TOKEN']=array_slice($_SESSION['84PHP_TOKEN'],$ArrayLength+1-$_SERVER['84PHP_CONFIG']['Session']['TokenLimit']);
		}
		$_SESSION['84PHP_TOKEN'][]=[
								'token'=>$Token,
								'time'=>Runtime
							];
		return $Token;
	}

	//来源检测
	public static function Csrf($UnionData=[]){
 		$Token=QuickParamet($UnionData,'token','token',FALSE,NULL);
		$SessionId=QuickParamet($UnionData,'id','id',FALSE,NULL);
		$UnsetToken=QuickParamet($UnionData,'unset_token','清除token',FALSE,TRUE);

		self::Start(['id'=>$SessionId]);
		
		if(empty($Token)){
			if(isset($_GET['csrf'])){
				$Token=$_GET['csrf'];
			}
			if(isset($_POST['csrf'])){
				$Token=$_POST['csrf'];
			}
			if(isset($_COOKIE['csrf'])){
				$Token=$_COOKIE['csrf'];
			}
			if(isset($_SERVER['HTTP_CSRF'])){
				$Token=$_SERVER['HTTP_CSRF'];
			}
		}
		
		$CheckState=FALSE;
		if(!empty($_SESSION['84PHP_TOKEN'])&&!empty($Token)){
			foreach($_SESSION['84PHP_TOKEN'] as $Key => $Val){
				if($Val['token']==$Token&&$Val['time']+$_SERVER['84PHP_CONFIG']['Session']['TokenExpTime']>Runtime){
					$CheckState=TRUE;
					if($UnsetToken){
						unset($_SESSION['84PHP_TOKEN'][$Key]);
					}
				}
			}
		}
		if(!$CheckState){
			return FALSE;
		}
		return TRUE;
	}

	public static function Start($UnionData=[]){
		$SessionId=QuickParamet($UnionData,'id','id',FALSE,NULL);
		if(session_status()!==PHP_SESSION_ACTIVE){
			if(!empty($SessionId)){
				session_id(md5($SessionId));
			}
			foreach($_SERVER['84PHP_CONFIG']['Session']['System'] as $Key => $Val){
				if($Val!==NULL){
					ini_set('session.'.$Key,$Val);
				}
			}
			if(!@session_start()){
				Wrong::Report(['detail'=>'Error#M.15.1','code'=>'M.15.1']);
			};
		}
	}
}
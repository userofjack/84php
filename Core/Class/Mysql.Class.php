<?php
/*
  84PHP开源框架

  ©2017-2021 84PHP.COM

  框架版本号：5.0.0
*/

require(RootPath.'/Config/Mysql.php');

class Mysql{
	private static $Mysqli;
	private static $NowDb;
	
	public static function ClassInitial(){
		self::Connect();
		$_SERVER['84PHP_LastWork']['Mysql']='CloseConnect';
	}
	
	//读写分离随机选择数据库
	private static function RandomDb(){
		if($_SERVER['84PHP_CONFIG']['Mysql']['RW_Splitting']){
			$AllSql=[];
			foreach($_SERVER['84PHP_CONFIG']['Mysql']['DbInfo'] as $Key => $Val){
				$AllSql[]=$Key;
			}
			self::$Mysqli->close();
			self::$NowDb=$AllSql[mt_rand(1,(count($AllSql)-1))];
			self::Connect();
		}
	}
	
	//选择数据库
	public static function Choose($ChooseDb){
		self::$Mysqli->close();
		self::$NowDb=$ChooseDb;
		self::Connect();
	}
	
	//连接数据库
	private static function Connect(){
		if(empty(self::$NowDb)){
			self::$NowDb='default';
		}
		if(empty($_SERVER['84PHP_CONFIG']['Mysql']['DbInfo'][self::$NowDb])){
			Wrong::Report(['detail'=>'Error#M.6.0','code'=>'M.6.0']);
		}
		self::$Mysqli=@new mysqli($_SERVER['84PHP_CONFIG']['Mysql']['DbInfo'][self::$NowDb]['address'],$_SERVER['84PHP_CONFIG']['Mysql']['DbInfo'][self::$NowDb]['username'],$_SERVER['84PHP_CONFIG']['Mysql']['DbInfo'][self::$NowDb]['password'],$_SERVER['84PHP_CONFIG']['Mysql']['DbInfo'][self::$NowDb]['dbname'],$_SERVER['84PHP_CONFIG']['Mysql']['DbInfo'][self::$NowDb]['port']);
		if(self::$Mysqli->connect_errno){
			Wrong::Report(['detail'=>'Error#M.6.1'."\r\n\r\n @ ".'Detail: '.self::$Mysqli->connect_error,'code'=>'M.6.1']);
		}
	}
	
	//写入日志
	private static function SqlLog($SqL){
		if($_SERVER['84PHP_CONFIG']['Mysql']['Log']){
			$_SERVER['84PHP_LOG'].='[sql] '.$SqL.' <'.strval((intval(microtime(TRUE)*1000)-intval(Runtime*1000))/1000)."s>\r\n";
		}
	}

	//字段名解析
	private static function SplitField($FieldName){
		$FieldName=str_replace(' ','',$FieldName);
		$Return=explode('*',$FieldName);
		if(!empty($Return[2])){
			Wrong::Report(['detail'=>'Error#M.6.6','code'=>'M.6.6']);
		}
		if(empty($Return[1])){
			return $Return[0];
		}
		else{
			return $Return[0].'`.`'.$Return[1];
		}
	}
	
	//获取表列表
	private static function GetTableList($TableData){
		$TableList='';
		if(is_array($TableData)){
			foreach($TableData as $Val){
				$TableList.=' `'.$Val.'` ,';
			}
			$TableList=substr($TableList,0,-1);
			return $TableList;
		}
		else{
			return ' `'.$TableData.'`';
		}
	}
	
	//获取字段列表
	private static function GetFieldList($FieldData,$Default){
		$FieldList='';
		if(!empty($FieldData)){
			if(is_string($FieldData)){
				return ' `'.self::SplitField($FieldData).'`';
			}
			else if(is_array($FieldData)){
				foreach($FieldData as $Val){
					$FieldList.=' `'.self::SplitField($Val).'` ,';
				}
				$FieldList=substr($FieldList,0,-1);
				return $FieldList;
			}
		}
		return $Default;
	}

	//查询条件转SQL语句
	private static function QueryToSql($OtherSql,$Field,$Value,$Condition,$Order,$Desc,$Limit,$Index,$GroupBy){
		if(empty($Condition)){
			$Condition='=';
		}

		$WhereSql='';
		if((!is_array($Field)&&!is_array($Value))&&!empty($Field)){
			$WhereSql=' WHERE `'.self::SplitField($Field).'`'.$Condition.'\''.$Value.'\'';
		}
		else if(is_array($Field)&&is_array($Value)){
			$WhereSql=' WHERE';
			foreach($Field as $Key => $Val){
				if(!is_array($Condition)||empty($Condition[$Key])){
					$TempCo=['=','AND'];
					$WhereSql.=' `'.self::SplitField($Val).'` '.$TempCo[0].' \''.$Value[$Key].'\'';
					if($Key<(count($Field)-1)){
						$WhereSql.=' '.$TempCo[1];
					}
				}
				else if(!is_array($Condition[$Key])){
					if(strpos($Condition[$Key],',')===FALSE){
						$Condition[$Key]=str_replace(' ','',$Condition[$Key]);
						$TempCo=[$Condition[$Key],'AND'];
					}
					else{
						$Condition[$Key]=str_replace(' ','',$Condition[$Key]);
						$TempCo=explode(',',$Condition[$Key]);
						if(empty($TempCo[1])){
							$TempCo[1]='AND';
						}
					}
					$WhereSql.=' `'.self::SplitField($Val).'` '.$TempCo[0].' \''.$Value[$Key].'\'';
					if($Key<(count($Field)-1)){
						$WhereSql.=' '.$TempCo[1];
					}
				}
				else{
					if(empty($Condition[$Key][0])){
						$TempCo=['=','AND'];
					}
					else if(strpos($Condition[$Key][0],',')===FALSE){
						$Condition[$Key][0]=str_replace(' ','',$Condition[$Key][0]);
						$TempCo=[$Condition[$Key][0],'AND'];
					}
					else{
						$Condition[$Key][0]=str_replace(' ','',$Condition[$Key][0]);
						$TempCo=explode(',',$Condition[$Key][0]);
						if(empty($TempCo[1])){
							$TempCo[1]='AND';
						}
					}
					$TempBeforeTag='';
					$TempAfterTag='';
					if(empty($Condition[$Key][1])){
					}
					else if(strpos($Condition[$Key][1],',')===FALSE){
						$Condition[$Key][1]=str_replace(' ','',$Condition[$Key][1]);
						$TempBeforeTag=$Condition[$Key][1]; 
					}
					else{
						$Condition[$Key][1]=str_replace(' ','',$Condition[$Key][1]);
						$TempTag=explode(',',$Condition[$Key][1]);
						$TempBeforeTag=$TempTag[0];
						$TempAfterTag=$TempTag[1];
					}

					$WhereSql.=' '.$TempBeforeTag.'`'.self::SplitField($Val).'` '.$TempCo[0].' \''.$Value[$Key].'\''.$TempAfterTag;
					if($Key<(count($Field)-1)){
						$WhereSql.=' '.$TempCo[1];
					}
				}
			}
		}
		if(is_string($Order)){
			$OrderSql=' ORDER BY `'.self::SplitField($Order).'`';
			if($Desc){
				$OrderSql.=' DESC';
			}
		}
		else if(is_array($Order)){
			$OrderSql=' ORDER BY ';
			foreach($Order as $Key => $Val){
				if(!empty($Val)){
					$OrderSql.='`'.self::SplitField($Val).'`';
					if($Desc||(isset($Desc[$Key])&&$Desc[$Key])){
						$OrderSql.=' DESC';
					}
					$OrderSql.=',';
				}
			}
			$OrderSql=substr($OrderSql,0,-1);
		}
		else{
			$OrderSql='';
		}
		if(!empty($Index)){
			$IndexSql=' FORCE INDEX(`'.self::SplitField($Index).'`)';
		}
		else{
			$IndexSql='';
		}
		if(is_array($Limit)){
			if(!empty($Limit[1])){
				$LimitSql=' LIMIT '.$Limit[0].','.$Limit[1];
			}
			else if(isset($Limit[0])){
				$LimitSql=' LIMIT 0,'.$Limit[0];
			}
		}
		else{
			$LimitSql='';
		}
		
		if(!empty($GroupBy)){
			$GroupBySql='GROUP BY '.self::GetFieldList($GroupBy,'');
		}
		else{
			$GroupBySql='';
		}
		
		return $WhereSql.$OrderSql.$LimitSql.$IndexSql.$GroupBySql.' '.$OtherSql;
	}
	
	//查询一条数据
	public static function Select($UnionData=[]){
		$Table=QuickParamet($UnionData,'table','表');
		$Field=QuickParamet($UnionData,'field','字段',FALSE,NULL);
		$Value=QuickParamet($UnionData,'value','值',FALSE,NULL);
		$Condition=QuickParamet($UnionData,'condition','条件',FALSE,'=');
		$Order=QuickParamet($UnionData,'order','顺序',FALSE,NULL);
		$Desc=QuickParamet($UnionData,'desc','降序',FALSE,FALSE);
		$Index=QuickParamet($UnionData,'index','索引',FALSE,NULL);		
		$Sql=QuickParamet($UnionData,'sql','sql',FALSE,NULL);

		$FieldLimit=QuickParamet($UnionData,'field_limit','字段限制',FALSE,NULL);		
		$QueryString=self::QueryToSql($Sql,$Field,$Value,$Condition,$Order,$Desc,[1],$Index,NULL);

		self::RandomDb();
		
		$QueryString='SELECT '.self::GetFieldList($FieldLimit,'*').' FROM'.self::GetTableList($Table).$QueryString;

		self::SqlLog($QueryString);
		$Result=self::$Mysqli->query($QueryString);
		if(!$Result){
			$ModuleError='Detail: '.self::$Mysqli->error.' | SQL String: '.$QueryString.' | errno:'.self::$Mysqli->errno;
			Wrong::Report(['detail'=>'Error#M.6.2'."\r\n\r\n @ ".'Detail: '.$ModuleError,'code'=>'M.6.2']);
		}
		$Return=$Result->fetch_assoc();
		$Result->free();
		if(empty($Return)){
			$Return=[];
			return $Return;
		}
		return $Return;
	}
	
	//查询多条数据
	public static function SelectMore($UnionData=[]){
		$Table=QuickParamet($UnionData,'table','表');
		$Field=QuickParamet($UnionData,'field','字段',FALSE,NULL);
		$Value=QuickParamet($UnionData,'value','值',FALSE,NULL);
		$Condition=QuickParamet($UnionData,'condition','条件',FALSE,'=');
		$Order=QuickParamet($UnionData,'order','顺序',FALSE,NULL);
		$Desc=QuickParamet($UnionData,'desc','降序',FALSE,FALSE);
		$Limit=QuickParamet($UnionData,'limit','限制',FALSE,NULL);
		$Index=QuickParamet($UnionData,'index','索引',FALSE,NULL);		
		$Sql=QuickParamet($UnionData,'sql','sql',FALSE,NULL);

		$FieldLimit=QuickParamet($UnionData,'field_limit','字段限制',FALSE,NULL);		
		$GroupBy=QuickParamet($UnionData,'group_by','分组',FALSE,NULL);
		$QueryString=self::QueryToSql($Sql,$Field,$Value,$Condition,$Order,$Desc,$Limit,$Index,$GroupBy);
		
		self::RandomDb();

		$QueryString='SELECT '.self::GetFieldList($FieldLimit,'*').' FROM'.self::GetTableList($Table).$QueryString;

		self::SqlLog($QueryString);
		$Result=self::$Mysqli->query($QueryString,MYSQLI_USE_RESULT);

		if(!$Result){
			$ModuleError='Detail: '.self::$Mysqli->error.' | SQL String: '.$QueryString.' | errno:'.self::$Mysqli->errno;
			Wrong::Report(['detail'=>'Error#M.6.2'."\r\n\r\n @ ".'Detail: '.$ModuleError,'code'=>'M.6.2']);
		}
		
		$Return=$Result->fetch_all(MYSQLI_ASSOC);
		$Result->free();
		if(empty($Return)){
			$Return=[];
		}
		return $Return;
	}
		
	//记录总数
	public static function Total($UnionData=[]){
		$Table=QuickParamet($UnionData,'table','表');
		$Field=QuickParamet($UnionData,'field','字段',FALSE,NULL);
		$Value=QuickParamet($UnionData,'value','值',FALSE,NULL);
		$Condition=QuickParamet($UnionData,'condition','条件',FALSE,'=');
		$Order=QuickParamet($UnionData,'order','顺序',FALSE,'Total');
		$Desc=QuickParamet($UnionData,'desc','降序',FALSE,FALSE);
		$Limit=QuickParamet($UnionData,'limit','限制',FALSE,NULL);
		$Index=QuickParamet($UnionData,'index','索引',FALSE,NULL);		
		$Sql=QuickParamet($UnionData,'sql','sql',FALSE,NULL);

		$GroupBy=QuickParamet($UnionData,'group_by','分组',FALSE,NULL);
		$QueryString=self::QueryToSql($Sql,$Field,$Value,$Condition,$Order,$Desc,$Limit,$Index,$GroupBy);
						
		self::RandomDb();
		
		$FieldLimit='';
		if(!empty($GroupBy)){
			$FieldLimit.=self::GetFieldList($GroupBy,'').',';
		}
		
		$QueryString='SELECT '.$FieldLimit.' COUNT(*) AS `Total` FROM'.self::GetTableList($Table).$QueryString;
		
		self::SqlLog($QueryString);
		$Result=self::$Mysqli->query($QueryString,MYSQLI_USE_RESULT);

		if(!$Result){
			$ModuleError='Detail: '.self::$Mysqli->error.' | SQL String: '.$QueryString.' | errno:'.self::$Mysqli->errno;
			Wrong::Report(['detail'=>'Error#M.6.2'."\r\n\r\n @ ".'Detail: '.$ModuleError,'code'=>'M.6.2']);
		}
		
		$Return=$Result->fetch_all(MYSQLI_ASSOC);
		$Result->free();
		if(!empty($GroupBy)){
			return $Return;
		}
		else{
			return $Return[0]['Total'];
		}
			
	}
	
	//求和
	public static function Sum($UnionData=[]){
		$Table=QuickParamet($UnionData,'table','表');
		$Field=QuickParamet($UnionData,'field','字段',FALSE,NULL);
		$Value=QuickParamet($UnionData,'value','值',FALSE,NULL);
		$Condition=QuickParamet($UnionData,'condition','条件',FALSE,'=');
		$Order=QuickParamet($UnionData,'order','顺序',FALSE,NULL);
		$Desc=QuickParamet($UnionData,'desc','降序',FALSE,FALSE);
		$Limit=QuickParamet($UnionData,'limit','限制',FALSE,NULL);
		$Index=QuickParamet($UnionData,'index','索引',FALSE,NULL);		
		$Sql=QuickParamet($UnionData,'sql','sql',FALSE,NULL);
		
		$SumField=QuickParamet($UnionData,'sum','合计');		
		$QueryString=self::QueryToSql($Sql,$Field,$Value,$Condition,$Order,$Desc,$Limit,$Index,NULL);
		
		$SumSql='';
		if(empty($SumField)){
			return [];
		}
		if(is_string($SumField)){
			$SumSql=' SUM(`'.self::SplitField($SumField).'`) AS `'.$SumResult.'`';
		}
		else if(is_array($SumField)){
			foreach($SumField as $Key => $Val){
				$SumSql.=' SUM(`'.self::SplitField($Val).'`)'.' AS `'.$Val.'`,';
			}
			$SumSql=substr($SumSql,0,-1);
		}

		$QueryString=self::QueryToSql($Sql,$Field,$Value,$Condition,$Order,$Desc,$Limit,$Index,NULL);
		
		self::RandomDb();

		$QueryString='SELECT'.$SumSql.' FROM'.self::GetTableList($Table).$QueryString;

		self::SqlLog($QueryString);
		$Result=self::$Mysqli->query($QueryString,MYSQLI_USE_RESULT);

		if(!$Result){
			$ModuleError='Detail: '.self::$Mysqli->error.' | SQL String: '.$QueryString.' | errno:'.self::$Mysqli->errno;
			Wrong::Report(['detail'=>'Error#M.6.2'."\r\n\r\n @ ".'Detail: '.$ModuleError,'code'=>'M.6.2']);
		}
		
		$Return=$Result->fetch_assoc();
		$Result->free();
		if(empty($Return)){
			$Return=[];
		}
		else{
			foreach($Return as $Key => $Val){
				if(empty($Val)){
					$Return[$Key]=0;
				}
			}
		}
		return $Return;
	}
	
	//插入数据
	public static function Insert($UnionData=[]){
		$Table=QuickParamet($UnionData,'table','表');
		$Data=QuickParamet($UnionData,'data','数据');
		$Sql=QuickParamet($UnionData,'sql','sql',FALSE,NULL);

		$InsertField=NULL;
		$InsertValue=NULL;
		
		foreach ($Data as $Key => $Val) {
			$InsertField.='`'.self::SplitField($Key).'`,';
			$InsertValue.='\''.$Val.'\',';
		}
		$InsertField=substr($InsertField,0,-1);
		$InsertValue=substr($InsertValue,0,-1);
		
		if($_SERVER['84PHP_CONFIG']['Mysql']['RW_Splitting']&&self::$NowDb!='default'){
			self::$Mysqli->close();
			self::$NowDb='default';
			self::Connect();
		}
		
		$QueryString='INSERT INTO'.self::GetTableList($Table).' ( '.$InsertField.' ) VALUES ( '.$InsertValue.' )'.' '.$Sql;

		self::SqlLog($QueryString);
		$Result=self::$Mysqli->query($QueryString,MYSQLI_USE_RESULT);

		if(!$Result){
			$ModuleError='Detail: '.self::$Mysqli->error.' | SQL String: '.$QueryString.' | errno:'.self::$Mysqli->errno;
			Wrong::Report(['detail'=>'Error#M.6.3'."\r\n\r\n @ ".'Detail: '.$ModuleError,'code'=>'M.6.3']);
		}

		$Result=self::$Mysqli->insert_id;
		return $Result;
	}
	
	//删除数据
	public static function Delete($UnionData=[]){
		$Table=QuickParamet($UnionData,'table','表');
		$Field=QuickParamet($UnionData,'field','字段',FALSE,NULL);
		$Value=QuickParamet($UnionData,'value','值',FALSE,NULL);
		$Condition=QuickParamet($UnionData,'condition','条件',FALSE,'=');
		$Order=QuickParamet($UnionData,'order','顺序',FALSE,NULL);
		$Desc=QuickParamet($UnionData,'desc','降序',FALSE,FALSE);
		$Limit=QuickParamet($UnionData,'limit','限制',FALSE,NULL);
		$Index=QuickParamet($UnionData,'index','索引',FALSE,NULL);		
		$Sql=QuickParamet($UnionData,'sql','sql',FALSE,NULL);
		$QueryString=self::QueryToSql($Sql,$Field,$Value,$Condition,$Order,$Desc,$Limit,$Index,NULL);
		
		if($_SERVER['84PHP_CONFIG']['Mysql']['RW_Splitting']&&self::$NowDb!='default'){
			self::$Mysqli->close();
			self::$NowDb='default';
			self::Connect();
		}
		
		$QueryString='DELETE FROM'.self::GetTableList($Table).$QueryString;

		self::SqlLog($QueryString);
		$Result=self::$Mysqli->query($QueryString,MYSQLI_USE_RESULT);

		if(!$Result){
			$ModuleError='Detail: '.self::$Mysqli->error.' | SQL String: '.$QueryString.' | errno:'.self::$Mysqli->errno;
			Wrong::Report(['detail'=>'Error#M.6.2'."\r\n\r\n @ ".'Detail: '.$ModuleError,'code'=>'M.6.2']);
		}
	}
	
	//更新数据
	public static function Update($UnionData=[]){
		$Table=QuickParamet($UnionData,'table','表');
		$Field=QuickParamet($UnionData,'field','字段',FALSE,NULL);
		$Value=QuickParamet($UnionData,'value','值',FALSE,NULL);
		$Condition=QuickParamet($UnionData,'condition','条件',FALSE,'=');
		$Order=QuickParamet($UnionData,'order','顺序',FALSE,NULL);
		$Desc=QuickParamet($UnionData,'desc','降序',FALSE,FALSE);
		$Limit=QuickParamet($UnionData,'limit','限制',FALSE,NULL);
		$Index=QuickParamet($UnionData,'index','索引',FALSE,NULL);		
		$Sql=QuickParamet($UnionData,'sql','sql',FALSE,NULL);

		$Data=QuickParamet($UnionData,'data','数据');
		$AutoOP=QuickParamet($UnionData,'auto_operate','自动操作',FALSE,NULL);

		$QueryString=self::QueryToSql($Sql,$Field,$Value,$Condition,$Order,$Desc,$Limit,$Index,NULL);

		$DataSql=NULL;
		$AutoOPNumber=0;

		foreach ($Data as $Key => $Val){
			
			if(!empty($AutoOP[$AutoOPNumber])){
				$DataSql.='`'.self::SplitField($Key).'`='.$Key.' '.$AutoOP[$AutoOPNumber];
			}
			else{
				$DataSql.='`'.self::SplitField($Key).'`=\''.$Val.'\'';
			}
			$DataSql.=',';
			$AutoOPNumber++;
		}
		$DataSql=substr($DataSql,0,-1);
		
		if($_SERVER['84PHP_CONFIG']['Mysql']['RW_Splitting']&&self::$NowDb!='default'){
			self::$Mysqli->close();
			self::$NowDb='default';
			self::Connect();
		}
		
		$QueryString='UPDATE'.self::GetTableList($Table).' SET '.$DataSql.$QueryString;

		self::SqlLog($QueryString);
		$Result=self::$Mysqli->query($QueryString,MYSQLI_USE_RESULT);

		if(!$Result){
			$ModuleError='Detail: '.self::$Mysqli->error.' | SQL String: '.$QueryString.' | errno:'.self::$Mysqli->errno;
			Wrong::Report(['detail'=>'Error#M.6.4'."\r\n\r\n @ ".'Detail: '.$ModuleError,'code'=>'M.6.4']);
		}
	}
	
	//查询自定义语句
	public static function Other($UnionData=[]){
		$Sql=QuickParamet($UnionData,'sql','sql',FALSE,NULL);
		$Fetch=QuickParamet($UnionData,'fetch_result','取回结果',FALSE,FALSE);

		self::SqlLog($Sql);
		$Result=self::$Mysqli->query($Sql,MYSQLI_USE_RESULT);

		if(!$Result){
			$ModuleError='Detail: '.self::$Mysqli->error.' | SQL String: '.$Sql.' | errno:'.self::$Mysqli->errno;
			Wrong::Report(['detail'=>'Error#M.6.5'."\r\n\r\n @ ".'Detail: '.$ModuleError,'code'=>'M.6.5']);
		}
		
		if($Fetch){
			$Return=$Result->fetch_all(MYSQLI_ASSOC);
			$Result->free();
			if(empty($Return)){
				$Return=[];
			}
		}
		else{
			$Return=$Result;
		}
		return $Return;
	}
	
	//备份
	public static function BackUp($UnionData=[]){
		$Path=QuickParamet($UnionData,'path','路径');

		$Path=DiskPath($Path);
		
		if(!file_exists($Path)){
			mkdir($Path,0777,TRUE);
		}
		
		$FilePath=$Path.'/'.md5(date("YmdHis").mt_rand(1000000, 9999999).$_SERVER['REMOTE_ADDR']).'.sql';
		
		$Handle=@fopen($FilePath,'w');
		if(!$Handle){
			Wrong::Report(['detail'=>'Error#M.6.7','code'=>'M.6.7']);
		}
		
		self::$Mysqli->query('set names \'utf8\'');
		$SQLContext='set charset utf8;'."\r\n";
		$AllTables=self::$Mysqli->query('show tables');
		while ($Result=$AllTables->fetch_array()){
			$Table=$Result[0];
			$TableField=self::$Mysqli->query("show create table `$Table`");
			$Sql=$TableField->fetch_array();
			$SQLContext.=$Sql['Create Table'].';'."\r\n";
			$TableField->free();
			$TableData=self::$Mysqli->query("select * from `$Table`");
			
			while ($Data=$TableData->fetch_assoc()){
				$Key=array_keys($Data);
				$Key=array_map('addslashes',$Key);
				$Key=join('`,`',$Key);
				$Key='`'.$Key.'`';
				$Val=array_values($Data);
				$Val=array_map('addslashes',$Val);
				$Val=join('\',\'',$Val);
				$Val='\''.$Val.'\'';
				$SQLContext.='insert into `'.$Table.'`('.$Key.') values('.$Val.');'."\r\n";
			}
			$TableData->free();
		}
		
		if(!fwrite($Handle,$SQLContext)){
			Wrong::Report(['detail'=>'Error#M.6.8','code'=>'M.6.8']);
		};
		fclose($Handle);
		$AllTables->free();
	}
	
	//关闭连接
	public static function CloseConnect(){
		if(!self::$Mysqli->connect_errno){
			self::$Mysqli->close();
		}
	}
	
	//调用方法不存在
	public static function __callStatic($Method,$Parameters){
		UnknownStaticMethod(__CLASS__,$Method);
	}
}
Mysql::ClassInitial();
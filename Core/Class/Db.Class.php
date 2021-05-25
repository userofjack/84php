<?php
/*
  84PHP开源框架

  ©2017-2021 84PHP.COM

  框架版本号：5.1.0
*/

require(RootPath.'/Config/Db.php');

class Db{
	private static $DbHandle;
	private static $NowDb;
	private static $Stmts;
	
	public static function ClassInitial(){
		self::Connect();
	}
	
	//读写分离随机选择数据库
	private static function RandomDb(){
		if($_SERVER['84PHP_CONFIG']['Db']['RW_Splitting']){
			$AllDb=[];
			foreach($_SERVER['84PHP_CONFIG']['Db']['DbInfo'] as $Key => $Val){
				$AllDb[]=$Key;
			}
			self::$DbHandle=null;
			self::$NowDb=$AllDb[mt_rand(1,(count($AllDb)-1))];
			self::Connect();
		}
	}
	
	//选择数据库
	public static function Choose($ChooseDb){
		self::$DbHandle=null;
		self::$NowDb=$ChooseDb;
		self::Connect();
	}
	
	//连接数据库
	private static function Connect(){
		if(empty(self::$NowDb)){
			self::$NowDb='default';
		}
		self::$Stmts=[];

		if(empty($_SERVER['84PHP_CONFIG']['Db']['DbInfo'][self::$NowDb])){
			Wrong::Report(['detail'=>'Error#M.17.0','code'=>'M.17.0']);
		}
		$Dsn=$_SERVER['84PHP_CONFIG']['Db']['DbType'].
			':host='.$_SERVER['84PHP_CONFIG']['Db']['DbInfo'][self::$NowDb]['address'].
			';port='.$_SERVER['84PHP_CONFIG']['Db']['DbInfo'][self::$NowDb]['port'].
			';dbname='.$_SERVER['84PHP_CONFIG']['Db']['DbInfo'][self::$NowDb]['dbname'].
			';charset='.$_SERVER['84PHP_CONFIG']['Db']['DbInfo'][self::$NowDb]['charset'];
		
		try{
			self::$DbHandle=@new PDO($Dsn,$_SERVER['84PHP_CONFIG']['Db']['DbInfo'][self::$NowDb]['username'],$_SERVER['84PHP_CONFIG']['Db']['DbInfo'][self::$NowDb]['password']);
			self::$DbHandle->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		}
		catch(PDOException $Error){
			Wrong::Report(['detail'=>'Error#M.17.1'."\r\n\r\n @ ".'ErrorInfo: ('.$Error->getCode().') '.$Error->getMessage(),'code'=>'M.17.1']);
		}
	}
	
	//预处理语句转SQL
	private static function PreToSql($PreSql,$Data,$Tag){
		$TrueSql='';
	}
	
	//创建绑定
	private static function CreateBind($PreSql){
		$StmtKey=md5($PreSql);
		if(empty(self::$Stmts[$StmtKey])){
			self::$Stmts[$StmtKey]=self::$DbHandle->prepare($PreSql);
		}
		return $StmtKey;
	}

	//绑定参数
	private static function BindData($StmtKey,$Field,$Data,$Tag='',$Mix=FALSE){
		if(!$Mix){
			foreach($Field as $Key => $Val){
				self::$Stmts[$StmtKey]->bindValue(':'.$Tag.$Val, $Data[$Key]);
			}
		}
		else{
			foreach($Data as $Key => $Val){
				self::$Stmts[$StmtKey]->bindValue(':'.$Tag.$Key, $Val);
			}

		}
	}

	//执行预处理
	private static function ExecBind($StmtKey,$PreSql,$Action=''){
		self::SqlLog($PreSql);

		if(!self::$Stmts[$StmtKey]->execute()){
			$ErrorInfo=self::$Stmts[$StmtKey]->errorInfo();
			$ModuleError='Detail: '.$ErrorInfo[2].' | SQL String: '.$PreSql.' | errno:'.$ErrorInfo[0].' / '.$ErrorInfo[1];
			Wrong::Report(['detail'=>'Error#M.6.2'."\r\n\r\n @ ".'Detail: '.$ModuleError,'code'=>'M.6.2']);
		}
		
		if($Action=='Fetch'){
			return self::$Stmts[$StmtKey]->fetch(PDO::FETCH_ASSOC);
		}
		else if($Action=='FetchAll'){
			return self::$Stmts[$StmtKey]->fetchAll(PDO::FETCH_ASSOC);
		}
		else if($Action=='InsertId'){
			return self::$DbHandle->lastInsertId();
		}
		else if($Action=='RowCount'){
			return self::$Stmts[$StmtKey]->rowCount();
		}
		else{
			return NULL;
		}

	}
	
	//写入日志
	private static function SqlLog($Sql){
		if($_SERVER['84PHP_CONFIG']['Db']['Log']){
			Log::Add(['level'=>'S','info'=>$Sql]);
		}
	}
	
	//获取表列表
	private static function GetTableList($TableData){
		$TableList='';
		if(is_array($TableData)){
			foreach($TableData as $Val){
				$TableList.=' '.$Val.' ,';
			}
			$TableList=substr($TableList,0,-1);
			return $TableList;
		}
		else{
			return ' '.$TableData;
		}
	}
	
	//获取字段列表
	private static function GetFieldList($FieldData,$Default){
		$FieldList='';
		if(!empty($FieldData)){
			if(is_string($FieldData)){
				return ' '.$FieldData;
			}
			else if(is_array($FieldData)){
				foreach($FieldData as $Val){
					$FieldList.=' '.$Val.' ,';
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
		foreach($Field as $Key => $Val){
			if($WhereSql==''){
				$WhereSql=' WHERE';
			}
			
			if(!is_array($Condition)||empty($Condition[$Key])){
				$TempCo=['=','AND'];
				$WhereSql.=' '.$Val.' '.$TempCo[0].' :_Where_'.$Val;
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
				$WhereSql.=' '.$Val.' '.$TempCo[0].' :_Where_'.$Val;
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

				$WhereSql.=' '.$TempBeforeTag.$Val.' '.$TempCo[0].' :_Where_'.$Val.' '.$TempAfterTag;
				if($Key<(count($Field)-1)){
					$WhereSql.=' '.$TempCo[1];
				}
			}
		}
		if(is_string($Order)){
			$OrderSql=' ORDER BY '.$Order;
			if($Desc){
				$OrderSql.=' DESC';
			}
		}
		else if(is_array($Order)){
			$OrderSql=' ORDER BY ';
			foreach($Order as $Key => $Val){
				if(!empty($Val)){
					$OrderSql.=$Val;
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
			$IndexSql=' FORCE INDEX('.$Index.')';
		}
		else{
			$IndexSql='';
		}
		if(is_array($Limit)){
			if(!empty($Limit[1])){
				$LimitSql=' LIMIT '.intval($Limit[0]).','.intval($Limit[1]);
			}
			else if(isset($Limit[0])){
				$LimitSql=' LIMIT 0,'.intval($Limit[0]);
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
		$Field=QuickParamet($UnionData,'field','字段',FALSE,[]);
		$Value=QuickParamet($UnionData,'value','值',FALSE,[]);
		$Condition=QuickParamet($UnionData,'condition','条件',FALSE,'=');
		$Order=QuickParamet($UnionData,'order','顺序',FALSE,NULL);
		$Desc=QuickParamet($UnionData,'desc','降序',FALSE,FALSE);
		$Index=QuickParamet($UnionData,'index','索引',FALSE,NULL);		
		$Sql=QuickParamet($UnionData,'sql','sql',FALSE,NULL);
		$Bind=QuickParamet($UnionData,'bind','绑定',FALSE,[]);

		$FieldLimit=QuickParamet($UnionData,'field_limit','字段限制',FALSE,NULL);		
		$QueryString=self::QueryToSql($Sql,$Field,$Value,$Condition,$Order,$Desc,[1],$Index,NULL);

		self::RandomDb();
		
		$QueryString='SELECT '.self::GetFieldList($FieldLimit,'*').' FROM'.self::GetTableList($Table).$QueryString;

		$StmtKey=self::CreateBind($QueryString);
		self::BindData($StmtKey,$Field,$Value,$Tag='_Where_');
		self::BindData($StmtKey,[],$Bind,$Tag='',TRUE);

		$Return=self::ExecBind($StmtKey,$QueryString,'Fetch');

		return $Return;
	}
	
	//查询多条数据
	public static function SelectMore($UnionData=[]){
		$Table=QuickParamet($UnionData,'table','表');
		$Field=QuickParamet($UnionData,'field','字段',FALSE,[]);
		$Value=QuickParamet($UnionData,'value','值',FALSE,[]);
		$Condition=QuickParamet($UnionData,'condition','条件',FALSE,'=');
		$Order=QuickParamet($UnionData,'order','顺序',FALSE,NULL);
		$Desc=QuickParamet($UnionData,'desc','降序',FALSE,FALSE);
		$Limit=QuickParamet($UnionData,'limit','限制',FALSE,NULL);
		$Index=QuickParamet($UnionData,'index','索引',FALSE,NULL);		
		$Sql=QuickParamet($UnionData,'sql','sql',FALSE,NULL);
		$Bind=QuickParamet($UnionData,'bind','绑定',FALSE,[]);

		$FieldLimit=QuickParamet($UnionData,'field_limit','字段限制',FALSE,NULL);		
		$GroupBy=QuickParamet($UnionData,'group_by','分组',FALSE,NULL);
		$QueryString=self::QueryToSql($Sql,$Field,$Value,$Condition,$Order,$Desc,$Limit,$Index,$GroupBy);
		
		self::RandomDb();

		$QueryString='SELECT '.self::GetFieldList($FieldLimit,'*').' FROM'.self::GetTableList($Table).$QueryString;

		$StmtKey=self::CreateBind($QueryString);
		self::BindData($StmtKey,$Field,$Value,$Tag='_Where_');
		self::BindData($StmtKey,[],$Bind,$Tag='',TRUE);
 
		$Return=self::ExecBind($StmtKey,$QueryString,'FetchAll');

		return $Return;
	}
		
	//记录总数
	public static function																	  Total($UnionData=[]){
		$Table=QuickParamet($UnionData,'table','表');
		$Field=QuickParamet($UnionData,'field','字段',FALSE,[]);
		$Value=QuickParamet($UnionData,'value','值',FALSE,[]);
		$Condition=QuickParamet($UnionData,'condition','条件',FALSE,'=');
		$Order=QuickParamet($UnionData,'order','顺序',FALSE,'Total');
		$Desc=QuickParamet($UnionData,'desc','降序',FALSE,FALSE);
		$Limit=QuickParamet($UnionData,'limit','限制',FALSE,NULL);
		$Index=QuickParamet($UnionData,'index','索引',FALSE,NULL);		
		$Sql=QuickParamet($UnionData,'sql','sql',FALSE,NULL);
		$Bind=QuickParamet($UnionData,'bind','绑定',FALSE,[]);

		$GroupBy=QuickParamet($UnionData,'group_by','分组',FALSE,NULL);
		$QueryString=self::QueryToSql($Sql,$Field,$Value,$Condition,$Order,$Desc,$Limit,$Index,$GroupBy);
						
		self::RandomDb();
		
		$FieldLimit='';
		if(!empty($GroupBy)){
			$FieldLimit.=self::GetFieldList($GroupBy,'').',';
		}
		
		$QueryString='SELECT '.$FieldLimit.' COUNT(*) AS Total FROM'.self::GetTableList($Table).$QueryString;
		
		$StmtKey=self::CreateBind($QueryString);
		self::BindData($StmtKey,$Field,$Value,$Tag='_Where_');
		self::BindData($StmtKey,[],$Bind,$Tag='',TRUE);

		$Return=self::ExecBind($StmtKey,$QueryString,'FetchAll');

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
		$Field=QuickParamet($UnionData,'field','字段',FALSE,[]);
		$Value=QuickParamet($UnionData,'value','值',FALSE,[]);
		$Condition=QuickParamet($UnionData,'condition','条件',FALSE,'=');
		$Order=QuickParamet($UnionData,'order','顺序',FALSE,NULL);
		$Desc=QuickParamet($UnionData,'desc','降序',FALSE,FALSE);
		$Limit=QuickParamet($UnionData,'limit','限制',FALSE,NULL);
		$Index=QuickParamet($UnionData,'index','索引',FALSE,NULL);		
		$Sql=QuickParamet($UnionData,'sql','sql',FALSE,NULL);
		$Bind=QuickParamet($UnionData,'bind','绑定',FALSE,[]);
		
		$SumField=QuickParamet($UnionData,'sum','合计');		
		$QueryString=self::QueryToSql($Sql,$Field,$Value,$Condition,$Order,$Desc,$Limit,$Index,NULL);
		
		$SumSql='';
		foreach($SumField as $Key => $Val){
			$SumSql.=' SUM('.$Key.')'.' AS '.$Val.',';
		}
		$SumSql=substr($SumSql,0,-1);

		$QueryString=self::QueryToSql($Sql,$Field,$Value,$Condition,$Order,$Desc,$Limit,$Index,NULL);
		
		self::RandomDb();

		$QueryString='SELECT'.$SumSql.' FROM'.self::GetTableList($Table).$QueryString;

		$StmtKey=self::CreateBind($QueryString);
		self::BindData($StmtKey,$Field,$Value,$Tag='_Where_');
		self::BindData($StmtKey,[],$Bind,$Tag='',TRUE);
		
		$Return=self::ExecBind($StmtKey,$QueryString,'FetchAll');

		foreach($Return as $Key => $Val){
			if(empty($Val)){
				$Return[$Key]=0;
			}
		}
		return $Return;
	}
	
	//插入数据
	public static function Insert($UnionData=[]){
		$Table=QuickParamet($UnionData,'table','表');
		$Data=QuickParamet($UnionData,'data','数据');
		$Sql=QuickParamet($UnionData,'sql','sql',FALSE,NULL);
		$Bind=QuickParamet($UnionData,'bind','绑定',FALSE,[]);

		$InsertField=NULL;
		$InsertValue=NULL;
		
		foreach ($Data as $Key => $Val) {
			$InsertField.=$Key.',';
			$InsertValue.=':_Insert_'.$Key.',';
		}
		$InsertField=substr($InsertField,0,-1);
		$InsertValue=substr($InsertValue,0,-1);
		
		if($_SERVER['84PHP_CONFIG']['Db']['RW_Splitting']&&self::$NowDb!='default'){
			self::$DbHandle=null;
			self::$NowDb='default';
			self::Connect();
		}
		
		$QueryString='INSERT INTO'.self::GetTableList($Table).' ( '.$InsertField.' ) VALUES ( '.$InsertValue.' )'.' '.$Sql;

		$StmtKey=self::CreateBind($QueryString);
		self::BindData($StmtKey,$Field,$Value,$Tag='_Where_');
		self::BindData($StmtKey,[],$Data,$Tag='_Insert_');
		self::BindData($StmtKey,[],$Bind,$Tag='',TRUE);

		return self::ExecBind($StmtKey,$QueryString,'InsertId');
	}
	
	//删除数据
	public static function Delete($UnionData=[]){
		$Table=QuickParamet($UnionData,'table','表');
		$Field=QuickParamet($UnionData,'field','字段',FALSE,[]);
		$Value=QuickParamet($UnionData,'value','值',FALSE,[]);
		$Condition=QuickParamet($UnionData,'condition','条件',FALSE,'=');
		$Order=QuickParamet($UnionData,'order','顺序',FALSE,NULL);
		$Desc=QuickParamet($UnionData,'desc','降序',FALSE,FALSE);
		$Limit=QuickParamet($UnionData,'limit','限制',FALSE,NULL);
		$Index=QuickParamet($UnionData,'index','索引',FALSE,NULL);		
		$Sql=QuickParamet($UnionData,'sql','sql',FALSE,NULL);
		$Bind=QuickParamet($UnionData,'bind','绑定',FALSE,[]);
		$RowCount=QuickParamet($UnionData,'row_count','行数统计',FALSE,FALSE);
		
		$QueryString=self::QueryToSql($Sql,$Field,$Value,$Condition,$Order,$Desc,$Limit,$Index,NULL);
		
		if($_SERVER['84PHP_CONFIG']['Db']['RW_Splitting']&&self::$NowDb!='default'){
			self::$DbHandle=null;
			self::$NowDb='default';
			self::Connect();
		}
		
		$QueryString='DELETE FROM'.self::GetTableList($Table).$QueryString;

		$StmtKey=self::CreateBind($QueryString);
		self::BindData($StmtKey,$Field,$Value,$Tag='_Where_');
		self::BindData($StmtKey,[],$Bind,$Tag='',TRUE);

		return self::ExecBind($StmtKey,$QueryString,$RowCount?'RowCount':'');
	}
	
	//更新数据
	public static function Update($UnionData=[]){
		$Table=QuickParamet($UnionData,'table','表');
		$Field=QuickParamet($UnionData,'field','字段',FALSE,[]);
		$Value=QuickParamet($UnionData,'value','值',FALSE,[]);
		$Condition=QuickParamet($UnionData,'condition','条件',FALSE,'=');
		$Order=QuickParamet($UnionData,'order','顺序',FALSE,NULL);
		$Desc=QuickParamet($UnionData,'desc','降序',FALSE,FALSE);
		$Limit=QuickParamet($UnionData,'limit','限制',FALSE,NULL);
		$Index=QuickParamet($UnionData,'index','索引',FALSE,NULL);		
		$Sql=QuickParamet($UnionData,'sql','sql',FALSE,NULL);
		$Bind=QuickParamet($UnionData,'bind','绑定',FALSE,[]);
		$RowCount=QuickParamet($UnionData,'row_count','行数统计',FALSE,FALSE);

		$Data=QuickParamet($UnionData,'data','数据');
		$AutoOP=QuickParamet($UnionData,'auto_operate','自动操作',FALSE,NULL);

		$QueryString=self::QueryToSql($Sql,$Field,$Value,$Condition,$Order,$Desc,$Limit,$Index,NULL);

		$DataSql=NULL;
		$AutoOPNumber=0;

		foreach ($Data as $Key => $Val){
			
			if(!empty($AutoOP[$AutoOPNumber])){
				$DataSql.=$Key.' = '.$Key.' '.$AutoOP[$AutoOPNumber];
			}
			else{
				$DataSql.=$Key.' = :_Update_'.$Key;
			}
			$DataSql.=',';
			$AutoOPNumber++;
		}
		$DataSql=substr($DataSql,0,-1);
		
		if($_SERVER['84PHP_CONFIG']['Db']['RW_Splitting']&&self::$NowDb!='default'){
			self::$DbHandle=null;
			self::$NowDb='default';
			self::Connect();
		}
		
		$QueryString='UPDATE'.self::GetTableList($Table).' SET '.$DataSql.$QueryString;

		$StmtKey=self::CreateBind($QueryString);
		self::BindData($StmtKey,$Field,$Value,$Tag='_Where_');
		self::BindData($StmtKey,[],$Data,$Tag='_Update_');
		self::BindData($StmtKey,[],$Bind,$Tag='',TRUE);

		return self::ExecBind($StmtKey,$QueryString,$RowCount?'RowCount':'');
	}
	
	//查询自定义语句
	public static function Other($UnionData=[]){
		$Sql=QuickParamet($UnionData,'sql','sql',FALSE,NULL);
		$Bind=QuickParamet($UnionData,'bind','绑定',FALSE,[]);
		$Fetch=QuickParamet($UnionData,'fetch_result','取回结果',FALSE,FALSE);

		$StmtKey=self::CreateBind($Sql);
		self::BindData($StmtKey,[],$Bind,$Tag='',TRUE);

		$Return=self::ExecBind($StmtKey,$QueryString,$Fetch?'FetchAll':'');
		
		return $Return;
	}
	
	//事务
	private static function Acid($UnionData=[]){
		$Option=QuickParamet($UnionData,'option','操作');
		if($Option=='begin'){
			try {  
				self::$DbHandle->beginTransaction();
				return TRUE;				
			} catch (Exception $Error) {
				self::$DbHandle->rollBack();
				Wrong::Report(['detail'=>'Error#M.6.2'."\r\n\r\n @ ".'Detail: '.$Error->getMessage(),'code'=>'M.6.2']);
			}
		}
		else if($Option=='commit'){
			if(!self::$DbHandle->commit()){
				return FALSE;
			}
			else{
				return TRUE;
			}
		}
		else if($Option=='cancel'){
			if(!self::$DbHandle->rollBack()){
				return FALSE;
			}
			else{
				return TRUE;
			}
		}
		return FALSE;
	}
		
	//调用方法不存在
	public static function __callStatic($Method,$Parameters){
		UnknownStaticMethod(__CLASS__,$Method);
	}
}
Db::ClassInitial();
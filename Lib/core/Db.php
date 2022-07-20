<?php
namespace core;

use PDO;
use core\Common;
use core\Api;
use core\Log;

/*
  84PHP开源框架

  ©2022 84PHP.com

  框架版本号：6.0.0
*/

class Db
{
    private static $DbHandle;
    private static $NowDb;
    private static $Stmts;
    
    private static function initial()
    {
        if(!empty($_SERVER['84PHP']['Runtime']['Db']['initial'])){
            return TRUE;
        }

        self::connect();
        
        $_SERVER['84PHP']['Runtime']['Db']['initial']=1;
        return TRUE;
    }
    
    //读写分离随机选择数据库
    private static function randomDb()
    {
        if ($_SERVER['84PHP']['Config']['Db']['rwSplitting']) {
            $AllDb=[];
            foreach ($_SERVER['84PHP']['Config']['Db']['dbInfo'] as $Key => $Val) {
                $AllDb[]=$Key;
            }
            self::$DbHandle=null;
            self::$NowDb=$AllDb[mt_rand(1,(count($AllDb)-1))];
            self::connect();
        }
    }
    
    //选择数据库
    public static function choose($ChooseDb)
    {
        self::$DbHandle=null;
        self::$NowDb=$ChooseDb;
        self::connect();
    }
    
    //连接数据库
    private static function connect()
    {
        if (empty(self::$NowDb)) {
            self::$NowDb='default';
        }
        self::$Stmts=[];

        if (empty($_SERVER['84PHP']['Config']['Db']['dbInfo'][self::$NowDb])) {
            Api::wrong(['level'=>'F','detail'=>'Error#M.8.0','code'=>'M.8.0']);
        }
        $Dsn=$_SERVER['84PHP']['Config']['Db']['dbType'].
            ':host='.$_SERVER['84PHP']['Config']['Db']['dbInfo'][self::$NowDb]['address'].
            ';port='.$_SERVER['84PHP']['Config']['Db']['dbInfo'][self::$NowDb]['port'].
            ';dbname='.$_SERVER['84PHP']['Config']['Db']['dbInfo'][self::$NowDb]['dbname'].
            ';charset='.$_SERVER['84PHP']['Config']['Db']['dbInfo'][self::$NowDb]['charset'];
        try {
            self::$DbHandle=@new PDO($Dsn,$_SERVER['84PHP']['Config']['Db']['dbInfo'][self::$NowDb]['username'],$_SERVER['84PHP']['Config']['Db']['dbInfo'][self::$NowDb]['password']);
            self::$DbHandle->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
        }
        catch(\PDOException $Error) {
            Api::wrong(['level'=>'F','detail'=>'Error#M.8.1'."\r\n\r\n @ ".'ErrorInfo: ('.$Error->getCode().') '.$Error->getMessage(),'code'=>'M.8.1']);
        }
    }
    
    //预处理语句转SQL
    private static function preToSql($PreSql,$Data,$Tag)
    {
        $TrueSql='';
    }
    
    //创建绑定
    private static function createBind($PreSql)
    {
        $StmtKey=md5($PreSql);
        if (empty(self::$Stmts[$StmtKey])) {
            self::$Stmts[$StmtKey]=self::$DbHandle->prepare($PreSql);
        }
        return $StmtKey;
    }

    //绑定参数
    private static function bindData($StmtKey,$Field,$Data,$Tag='',$Mix=FALSE)
    {
        if (!$Mix) {
            foreach ($Field as $Key => $Val) {
                self::$Stmts[$StmtKey]->bindValue(':'.$Tag.$Val, $Data[$Key]);
            }
        }
        else {
            foreach ($Data as $Key => $Val) {
                self::$Stmts[$StmtKey]->bindValue(':'.$Tag.$Key, $Val);
            }

        }
    }

    //执行预处理
    private static function execBind($StmtKey,$PreSql,$Action='')
    {
        self::sqlLog($PreSql);

        try {
            self::$Stmts[$StmtKey]->execute();
        }
        catch(\PDOException $Error) {
            $ModuleError='Detail: '.$Error->getMessage().' | SQL String: '.$PreSql.' | errno:'.$Error->getCode();
            Api::wrong(['level'=>'F','detail'=>'Error#M.8.2'."\r\n\r\n @ ".$ModuleError,'code'=>'M.8.2']);
        }
        
        if ($Action=='Fetch') {
            return self::$Stmts[$StmtKey]->fetch(PDO::FETCH_ASSOC);
        }
        else if ($Action=='FetchAll') {
            return self::$Stmts[$StmtKey]->fetchAll(PDO::FETCH_ASSOC);
        }
        else if ($Action=='InsertId') {
            return self::$DbHandle->lastInsertId();
        }
        else if ($Action=='RowCount') {
            return self::$Stmts[$StmtKey]->rowCount();
        }
        else {
            return NULL;
        }

    }
    
    //写入日志
    private static function sqlLog($Sql)
    {
        if ($_SERVER['84PHP']['Config']['Db']['log']) {
            Log::add(['level'=>'debug','info'=>'[SQL] '.$Sql]);
        }
    }
    
    //获取表列表
    private static function getTableList($TableData)
    {
        $TableList='';
        if (is_array($TableData)) {
            foreach ($TableData as $Val) {
                $TableList.=' '.$Val.' ,';
            }
            $TableList=substr($TableList,0,-1);
            return $TableList;
        }
        else {
            return ' '.$TableData;
        }
    }
    
    //获取字段列表
    private static function getFieldList($FieldData,$Default)
    {
        $FieldList='';
        if (!empty($FieldData)) {
            if (is_string($FieldData)) {
                return ' '.$FieldData;
            }
            else if (is_array($FieldData)) {
                foreach ($FieldData as $Val) {
                    $FieldList.=' '.$Val.' ,';
                }
                $FieldList=substr($FieldList,0,-1);
                return $FieldList;
            }
        }
        return $Default;
    }

    //查询条件转SQL语句
    private static function queryToSql($OtherSql,$Field,$Value,$Condition,$Order,$Desc,$Limit,$Index,$GroupBy)
    {
        if (empty($Condition)) {
            $Condition='=';
        }

        $WhereSql='';
        foreach ($Field as $Key => $Val) {
            if ($WhereSql=='') {
                $WhereSql=' WHERE';
            }
            
            if (!is_array($Condition)||empty($Condition[$Key])) {
                $TempCo=['=','AND'];
                $WhereSql.=' '.$Val.' '.$TempCo[0].' :_Where_'.$Val;
                if ($Key<(count($Field)-1)) {
                    $WhereSql.=' '.$TempCo[1];
                }
            }
            else if (!is_array($Condition[$Key])) {
                if (strpos($Condition[$Key],',')===FALSE) {
                    $Condition[$Key]=str_replace(' ','',$Condition[$Key]);
                    $TempCo=[$Condition[$Key],'AND'];
                }
                else {
                    $Condition[$Key]=str_replace(' ','',$Condition[$Key]);
                    $TempCo=explode(',',$Condition[$Key]);
                    if (empty($TempCo[1])) {
                        $TempCo[1]='AND';
                    }
                }
                $WhereSql.=' '.$Val.' '.$TempCo[0].' :_Where_'.$Val;
                if ($Key<(count($Field)-1)) {
                    $WhereSql.=' '.$TempCo[1];
                }
            }
            else {
                if (empty($Condition[$Key][0])) {
                    $TempCo=['=','AND'];
                }
                else if (strpos($Condition[$Key][0],',')===FALSE) {
                    $Condition[$Key][0]=str_replace(' ','',$Condition[$Key][0]);
                    $TempCo=[$Condition[$Key][0],'AND'];
                }
                else {
                    $Condition[$Key][0]=str_replace(' ','',$Condition[$Key][0]);
                    $TempCo=explode(',',$Condition[$Key][0]);
                    if (empty($TempCo[1])) {
                        $TempCo[1]='AND';
                    }
                }
                $TempBeforeTag='';
                $TempAfterTag='';
                if (empty($Condition[$Key][1])) {
                }
                else if (strpos($Condition[$Key][1],',')===FALSE) {
                    $Condition[$Key][1]=str_replace(' ','',$Condition[$Key][1]);
                    $TempBeforeTag=$Condition[$Key][1]; 
                }
                else {
                    $Condition[$Key][1]=str_replace(' ','',$Condition[$Key][1]);
                    $TempTag=explode(',',$Condition[$Key][1]);
                    $TempBeforeTag=$TempTag[0];
                    $TempAfterTag=$TempTag[1];
                }

                $WhereSql.=' '.$TempBeforeTag.$Val.' '.$TempCo[0].' :_Where_'.$Val.' '.$TempAfterTag;
                if ($Key<(count($Field)-1)) {
                    $WhereSql.=' '.$TempCo[1];
                }
            }
        }
        if (is_string($Order)) {
            $OrderSql=' ORDER BY '.$Order;
            if ($Desc) {
                $OrderSql.=' DESC';
            }
        }
        else if (is_array($Order)) {
            $OrderSql=' ORDER BY ';
            foreach ($Order as $Key => $Val) {
                if (!empty($Val)) {
                    $OrderSql.=$Val;
                    if ($Desc||(isset($Desc[$Key])&&$Desc[$Key])) {
                        $OrderSql.=' DESC';
                    }
                    $OrderSql.=',';
                }
            }
            $OrderSql=substr($OrderSql,0,-1);
        }
        else {
            $OrderSql='';
        }
        if (!empty($Index)) {
            $IndexSql=' FORCE INDEX('.$Index.')';
        }
        else {
            $IndexSql='';
        }
        if (is_array($Limit)) {
            if (!empty($Limit[1])) {
                $LimitSql=' LIMIT '.intval($Limit[0]).','.intval($Limit[1]);
            }
            else if (isset($Limit[0])) {
                $LimitSql=' LIMIT 0,'.intval($Limit[0]);
            }
        }
        else {
            $LimitSql='';
        }
        
        if (!empty($GroupBy)) {
            $GroupBySql='GROUP BY '.self::getFieldList($GroupBy,'');
        }
        else {
            $GroupBySql='';
        }
        
        return $WhereSql.$OrderSql.$LimitSql.$IndexSql.$GroupBySql.' '.$OtherSql;
    }
    
    //查询一条数据
    public static function select($UnionData=[])
    {
        $Table=Common::quickParamet($UnionData,'table','表');
        $Field=Common::quickParamet($UnionData,'field','字段',FALSE,[]);
        $Value=Common::quickParamet($UnionData,'value','值',FALSE,[]);
        $Condition=Common::quickParamet($UnionData,'condition','条件',FALSE,'=');
        $Order=Common::quickParamet($UnionData,'order','顺序',FALSE,NULL);
        $Desc=Common::quickParamet($UnionData,'desc','降序',FALSE,FALSE);
        $Index=Common::quickParamet($UnionData,'index','索引',FALSE,NULL);        
        $Sql=Common::quickParamet($UnionData,'sql','sql',FALSE,NULL);
        $Bind=Common::quickParamet($UnionData,'bind','绑定',FALSE,[]);
        $FieldLimit=Common::quickParamet($UnionData,'field_limit','字段限制',FALSE,NULL);        

        self::initial();
        
        $QueryString=self::queryToSql($Sql,$Field,$Value,$Condition,$Order,$Desc,[1],$Index,NULL);

        self::randomDb();
        
        $QueryString='SELECT '.self::getFieldList($FieldLimit,'*').' FROM'.self::getTableList($Table).$QueryString;

        $StmtKey=self::createBind($QueryString);
        self::bindData($StmtKey,$Field,$Value,$Tag='_Where_');
        self::bindData($StmtKey,[],$Bind,$Tag='',TRUE);

        $Return=self::execBind($StmtKey,$QueryString,'Fetch');

        return $Return;
    }
    
    //查询多条数据
    public static function selectMore($UnionData=[])
    {
        $Table=Common::quickParamet($UnionData,'table','表');
        $Field=Common::quickParamet($UnionData,'field','字段',FALSE,[]);
        $Value=Common::quickParamet($UnionData,'value','值',FALSE,[]);
        $Condition=Common::quickParamet($UnionData,'condition','条件',FALSE,'=');
        $Order=Common::quickParamet($UnionData,'order','顺序',FALSE,NULL);
        $Desc=Common::quickParamet($UnionData,'desc','降序',FALSE,FALSE);
        $Limit=Common::quickParamet($UnionData,'limit','限制',FALSE,NULL);
        $Index=Common::quickParamet($UnionData,'index','索引',FALSE,NULL);        
        $Sql=Common::quickParamet($UnionData,'sql','sql',FALSE,NULL);
        $Bind=Common::quickParamet($UnionData,'bind','绑定',FALSE,[]);

        $FieldLimit=Common::quickParamet($UnionData,'field_limit','字段限制',FALSE,NULL);        
        $GroupBy=Common::quickParamet($UnionData,'group_by','分组',FALSE,NULL);

        self::initial();
        
        $QueryString=self::queryToSql($Sql,$Field,$Value,$Condition,$Order,$Desc,$Limit,$Index,$GroupBy);
        
        self::randomDb();

        $QueryString='SELECT '.self::getFieldList($FieldLimit,'*').' FROM'.self::getTableList($Table).$QueryString;

        $StmtKey=self::createBind($QueryString);
        self::bindData($StmtKey,$Field,$Value,$Tag='_Where_');
        self::bindData($StmtKey,[],$Bind,$Tag='',TRUE);
 
        $Return=self::execBind($StmtKey,$QueryString,'FetchAll');

        return $Return;
    }
        
    //记录总数
    public static function                                                                      Total($UnionData=[])
    {
        $Table=Common::quickParamet($UnionData,'table','表');
        $Field=Common::quickParamet($UnionData,'field','字段',FALSE,[]);
        $Value=Common::quickParamet($UnionData,'value','值',FALSE,[]);
        $Condition=Common::quickParamet($UnionData,'condition','条件',FALSE,'=');
        $Order=Common::quickParamet($UnionData,'order','顺序',FALSE,'Total');
        $Desc=Common::quickParamet($UnionData,'desc','降序',FALSE,FALSE);
        $Limit=Common::quickParamet($UnionData,'limit','限制',FALSE,NULL);
        $Index=Common::quickParamet($UnionData,'index','索引',FALSE,NULL);        
        $Sql=Common::quickParamet($UnionData,'sql','sql',FALSE,NULL);
        $Bind=Common::quickParamet($UnionData,'bind','绑定',FALSE,[]);
        $GroupBy=Common::quickParamet($UnionData,'group_by','分组',FALSE,NULL);

        self::initial();
                        
        self::randomDb();

        $QueryString=self::queryToSql($Sql,$Field,$Value,$Condition,$Order,$Desc,$Limit,$Index,$GroupBy);
        
        $FieldLimit='';
        if (!empty($GroupBy)) {
            $FieldLimit.=self::getFieldList($GroupBy,'').',';
        }
        
        $QueryString='SELECT '.$FieldLimit.' COUNT(*) AS Total FROM'.self::getTableList($Table).$QueryString;
        
        $StmtKey=self::createBind($QueryString);
        self::bindData($StmtKey,$Field,$Value,$Tag='_Where_');
        self::bindData($StmtKey,[],$Bind,$Tag='',TRUE);

        $Return=self::execBind($StmtKey,$QueryString,'FetchAll');

        if (!empty($GroupBy)) {
            return $Return;
        }
        else {
            return $Return[0]['Total'];
        }
            
    }
    
    //求和
    public static function sum($UnionData=[])
    {
        $Table=Common::quickParamet($UnionData,'table','表');
        $Field=Common::quickParamet($UnionData,'field','字段',FALSE,[]);
        $Value=Common::quickParamet($UnionData,'value','值',FALSE,[]);
        $Condition=Common::quickParamet($UnionData,'condition','条件',FALSE,'=');
        $Order=Common::quickParamet($UnionData,'order','顺序',FALSE,NULL);
        $Desc=Common::quickParamet($UnionData,'desc','降序',FALSE,FALSE);
        $Limit=Common::quickParamet($UnionData,'limit','限制',FALSE,NULL);
        $Index=Common::quickParamet($UnionData,'index','索引',FALSE,NULL);        
        $Sql=Common::quickParamet($UnionData,'sql','sql',FALSE,NULL);
        $Bind=Common::quickParamet($UnionData,'bind','绑定',FALSE,[]);
        $SumField=Common::quickParamet($UnionData,'sum','合计');        

        self::initial();

        $QueryString=self::queryToSql($Sql,$Field,$Value,$Condition,$Order,$Desc,$Limit,$Index,NULL);
        
        $SumSql='';
        foreach ($SumField as $Key => $Val) {
            $SumSql.=' SUM('.$Key.')'.' AS '.$Val.',';
        }
        $SumSql=substr($SumSql,0,-1);

        $QueryString=self::queryToSql($Sql,$Field,$Value,$Condition,$Order,$Desc,$Limit,$Index,NULL);
        
        self::randomDb();

        $QueryString='SELECT'.$SumSql.' FROM'.self::getTableList($Table).$QueryString;

        $StmtKey=self::createBind($QueryString);
                
        self::bindData($StmtKey,$Field,$Value,$Tag='_Where_');
        self::bindData($StmtKey,[],$Bind,$Tag='',TRUE);
        
        $Return=self::execBind($StmtKey,$QueryString,'Fetch');
        foreach ($Return as $Key => $Val) {
            if (empty($Val)) {
                $Return[$Key]=0;
            }
        }
        return $Return;
    }
    
    //插入数据
    public static function insert($UnionData=[])
    {
        $Table=Common::quickParamet($UnionData,'table','表');
        $Data=Common::quickParamet($UnionData,'data','数据');
        $Sql=Common::quickParamet($UnionData,'sql','sql',FALSE,NULL);
        $Bind=Common::quickParamet($UnionData,'bind','绑定',FALSE,[]);

        self::initial();

        $InsertField=NULL;
        $InsertValue=NULL;
        
        foreach ($Data as $Key => $Val) {
            $InsertField.=$Key.',';
            $InsertValue.=':_Insert_'.$Key.',';
        }
        $InsertField=substr($InsertField,0,-1);
        $InsertValue=substr($InsertValue,0,-1);
        
        if ($_SERVER['84PHP']['Config']['Db']['rwSplitting']&&self::$NowDb!='default') {
            self::$DbHandle=null;
            self::$NowDb='default';
            self::connect();
        }
        
        $QueryString='INSERT INTO'.self::getTableList($Table).' ( '.$InsertField.' ) VALUES ( '.$InsertValue.' )'.' '.$Sql;

        $StmtKey=self::createBind($QueryString);
        self::bindData($StmtKey,[],$Data,$Tag='_Insert_',TRUE);
        self::bindData($StmtKey,[],$Bind,$Tag='',TRUE);

        return self::execBind($StmtKey,$QueryString,'InsertId');
    }
    
    //删除数据
    public static function delete($UnionData=[])
    {
        $Table=Common::quickParamet($UnionData,'table','表');
        $Field=Common::quickParamet($UnionData,'field','字段',FALSE,[]);
        $Value=Common::quickParamet($UnionData,'value','值',FALSE,[]);
        $Condition=Common::quickParamet($UnionData,'condition','条件',FALSE,'=');
        $Order=Common::quickParamet($UnionData,'order','顺序',FALSE,NULL);
        $Desc=Common::quickParamet($UnionData,'desc','降序',FALSE,FALSE);
        $Limit=Common::quickParamet($UnionData,'limit','限制',FALSE,NULL);
        $Index=Common::quickParamet($UnionData,'index','索引',FALSE,NULL);        
        $Sql=Common::quickParamet($UnionData,'sql','sql',FALSE,NULL);
        $Bind=Common::quickParamet($UnionData,'bind','绑定',FALSE,[]);
        $RowCount=Common::quickParamet($UnionData,'row_count','行数统计',FALSE,FALSE);

        self::initial();
        
        $QueryString=self::queryToSql($Sql,$Field,$Value,$Condition,$Order,$Desc,$Limit,$Index,NULL);
        
        if ($_SERVER['84PHP']['Config']['Db']['rwSplitting']&&self::$NowDb!='default') {
            self::$DbHandle=null;
            self::$NowDb='default';
            self::connect();
        }
        
        $QueryString='DELETE FROM'.self::getTableList($Table).$QueryString;

        $StmtKey=self::createBind($QueryString);
        self::bindData($StmtKey,$Field,$Value,$Tag='_Where_');
        self::bindData($StmtKey,[],$Bind,$Tag='',TRUE);

        return self::execBind($StmtKey,$QueryString,$RowCount?'RowCount':'');
    }
    
    //更新数据
    public static function update($UnionData=[])
    {
        $Table=Common::quickParamet($UnionData,'table','表');
        $Field=Common::quickParamet($UnionData,'field','字段',FALSE,[]);
        $Value=Common::quickParamet($UnionData,'value','值',FALSE,[]);
        $Condition=Common::quickParamet($UnionData,'condition','条件',FALSE,'=');
        $Order=Common::quickParamet($UnionData,'order','顺序',FALSE,NULL);
        $Desc=Common::quickParamet($UnionData,'desc','降序',FALSE,FALSE);
        $Limit=Common::quickParamet($UnionData,'limit','限制',FALSE,NULL);
        $Index=Common::quickParamet($UnionData,'index','索引',FALSE,NULL);        
        $Sql=Common::quickParamet($UnionData,'sql','sql',FALSE,NULL);
        $Bind=Common::quickParamet($UnionData,'bind','绑定',FALSE,[]);
        $RowCount=Common::quickParamet($UnionData,'row_count','行数统计',FALSE,FALSE);
        $Data=Common::quickParamet($UnionData,'data','数据');
        $AutoOP=Common::quickParamet($UnionData,'auto_operate','自动操作',FALSE,NULL);

        self::initial();

        $QueryString=self::queryToSql($Sql,$Field,$Value,$Condition,$Order,$Desc,$Limit,$Index,NULL);

        $DataSql=NULL;
        $AutoOPNumber=0;

        foreach ($Data as $Key => $Val) {
            
            if (!empty($AutoOP[$AutoOPNumber])) {
                $DataSql.=$Key.' = '.$Key.' '.$AutoOP[$AutoOPNumber];
            }
            else {
                $DataSql.=$Key.' = :_Update_'.$Key;
            }
            $DataSql.=',';
            $AutoOPNumber++;
        }
        $DataSql=substr($DataSql,0,-1);
        
        if ($_SERVER['84PHP']['Config']['Db']['rwSplitting']&&self::$NowDb!='default') {
            self::$DbHandle=null;
            self::$NowDb='default';
            self::connect();
        }
        
        $QueryString='UPDATE'.self::getTableList($Table).' SET '.$DataSql.$QueryString;

        $StmtKey=self::createBind($QueryString);
        self::bindData($StmtKey,$Field,$Value,$Tag='_Where_');
        self::bindData($StmtKey,[],$Data,$Tag='_Update_',TRUE);
        self::bindData($StmtKey,[],$Bind,$Tag='',TRUE);

        return self::execBind($StmtKey,$QueryString,$RowCount?'RowCount':'');
    }
    
    //查询自定义语句
    public static function other($UnionData=[])
    {
        $Sql=Common::quickParamet($UnionData,'sql','sql',FALSE,NULL);
        $Bind=Common::quickParamet($UnionData,'bind','绑定',FALSE,[]);
        $Fetch=Common::quickParamet($UnionData,'fetch_result','取回结果',FALSE,FALSE);

        self::initial();

        $StmtKey=self::createBind($Sql);
        self::bindData($StmtKey,[],$Bind,$Tag='',TRUE);

        $Return=self::execBind($StmtKey,$Sql,$Fetch?'FetchAll':'');
        
        return $Return;
    }
    
    //事务
    private static function acid($UnionData=[])
    {
        $Option=Common::quickParamet($UnionData,'option','操作');

        self::initial();

        if ($Option=='begin') {
            try {  
                self::$DbHandle->beginTransaction();
                return TRUE;                
            } catch (\Exception $Error) {
                Api::wrong(['level'=>'F','detail'=>'Error#M.8.3'."\r\n\r\n @ ".'Detail: '.$Error->getMessage(),'code'=>'M.8.3']);
            }
        }
        else if ($Option=='commit') {
            if (!self::$DbHandle->commit()) {
                return FALSE;
            }
            else {
                return TRUE;
            }
        }
        else if ($Option=='cancel') {
            if (!self::$DbHandle->rollBack()) {
                return FALSE;
            }
            else {
                return TRUE;
            }
        }
        return FALSE;
    }
        
    //调用方法不存在
    public static function __callStatic($Method,$Parameters)
    {
        Common::unknownStaticMethod(__CLASS__,$Method);
    }
}
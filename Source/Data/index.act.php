<?php
$Config=array(
	'字段'=>['testinput']
);


$Post=Receive::Post($Config);

/*
	调用“Receive”模块中的“Post()”方法，并将返回值存储于“$Post”变量中。
*/

if(empty($Post['testinput'])){
	die('<script>alert("这是用POST方式传递的表单数据，由index.act.php进行处理。\r\n【您没有输入字符！】");window.location.href="/index"</script>');
}
else{
	die('<script>alert("这是用POST方式传递的表单数据，由index.act.php进行处理。\r\n您输入的是：【'.$Post['testinput'].'】");window.location.href="/index"</script>');
}
?>
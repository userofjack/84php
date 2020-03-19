<?php
exit;#
//“exit;#”是为了防止模板代码被运行。它不会存在于生成的文件中。

$Config=array(
	'字段'=>array('testinput')
);

$Post=[Receive >> Post($Config)];

/*
	调用“Receive”模块中的“Post()”方法，并将返回值存储于“$Post”变量中。
	
	请注意，“$Post”是经过安全处理的“$_POST”变量，即“$Post”是安全的，而“$_POST”可能包含恶意数据。因此，尽量不要直接使用“$_POST”变量。
*/

if(empty($Post['testinput'])){
	die('<script>alert("这是用POST方式传递的表单数据，由index.act.php进行处理。\r\n【您没有输入字符！】");window.location.href="/index"</script>');
}
else{
	die('<script>alert("这是用POST方式传递的表单数据，由index.act.php进行处理。\r\n您输入的是：【'.$Post['testinput'].'】");window.location.href="/index"</script>');
}
?>
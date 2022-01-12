<?php
require(dirname(__FILE__).'/Core/Initial.php');

Cache::compile(['path'=>__URI__]);
if (file_exists(__ROOT__.'/Temp/Cache'.__URI__.'.php')) {
	require(__ROOT__.'/Temp/Cache'.__URI__.'.php');
}
else if (file_exists(__ROOT__.'/Web'.__URI__.'/index.html')) {
	$Content=file_get_contents(__ROOT__.'/Web'.__URI__.'/index.html');
	if($Content===FALSE){
		Api::wrong(['level'=>'F','detail'=>'Error#C.0.0','code'=>'C.0.0','http'=>404]);
	}
	echo($Content);
}
else if (file_exists(__ROOT__.'/Web'.__URI__.'/index.htm')) {
	$Content=file_get_contents(__ROOT__.'/Web'.__URI__.'/index.htm');
	if($Content===FALSE){
		Api::wrong(['level'=>'F','detail'=>'Error#C.0.0','code'=>'C.0.0','http'=>404]);
	}
	echo($Content);
}
else {
	Api::wrong(['level'=>'U','detail'=>'Error#C.0.0','code'=>'C.0.0','http'=>404]);
}
if (!empty($_SERVER['84PHP']['Log'])) {
    Log::output();
}
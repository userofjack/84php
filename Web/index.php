<?php
require(substr(dirname(__FILE__),0,-4).'/Core/Initial.php');

Cache::Compile(['path'=>URI]);

if(file_exists(RootPath.'/Temp/Cache'.URI.'.php')){
	require(RootPath.'/Temp/Cache'.URI.'.php');
}
else {
	if(FrameworkConfig['Debug']){
		Cache::Compile(['path'=>URI.'/index']);
	}
	if(file_exists(RootPath.'/Temp/Cache'.URI.'/index.php')){
		require(RootPath.'/Temp/Cache'.URI.'/index.php');
	}
	else{
		Wrong::Report('','','Error#C.0.0',FALSE,404);
	}
}
LastWork();
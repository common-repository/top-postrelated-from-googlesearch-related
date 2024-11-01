<?php 
define("RESULT_FOR_PAGE" , 10 );
if( $_POST["title"] && $_POST["api_key"] ){
	if( $_POST["pag"] == 0 ) $start = 0;
	else $start = RESULT_FOR_PAGE;
	$url = "https://ajax.googleapis.com/ajax/services/search/blogs?v=1.0&rsz=".(RESULT_FOR_PAGE / 2)."&start=".$start."&q=". urlencode( $_POST["title"] ). "&key=" . $_POST["api_key"] . "&userip=" . $_SERVER["REMOTE_ADDR"];
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$result = curl_exec($ch);
	curl_close($ch);
	echo $result;
}
?>
<?php
ob_start();
srand((double)microtime()*1000000);
$auto_challenge = md5(uniqid(rand()));
$auto_id=md5(uniqid(rand()));
$fp=fopen("$TMP_PATH/auto_key_$auto_id","a");
fputs($fp,$auto_challenge);
fclose($fp);
chmod("$TMP_PATH/auto_key_$auto_id", 0600);
header("Content-type: text/javascript");
header("Pragma: no-cache");
header("Expires: 0");
header("cache-control: no-cache");
echo "var auto_key = \"$auto_challenge\";\nvar auto_id = \"$auto_id\";\n";
ob_end_flush();
?>

<?php

$cmd = "zip redirect.zip redirect.html";
$exec = exec($cmd . " > /dev/null &");

print_r($exec);
	
?>
<?php
include("includes/config.php");
include("includes/functions.php");
include("includes/db_connect.php");
include("includes/upload.class.php");
	
if(!isset($_GET["id"])) {
	header("Location: dashboard.php?action=forbidden");
	exit;
}	

if(!$_SESSION) {
	header("Location: sign-in.php");
	exit;
}

$file_id = htmlspecialchars($_GET["id"]);
$user_id = $_SESSION["USER_ID"];

$file_query = $dbh->prepare("SELECT id, url, user_id, is_picture, short_id, title, in_community FROM file WHERE unique_id = :file_unique_id AND user_id = :user_id");
$file_query->bindParam(":file_unique_id", $file_id);
$file_query->bindParam(":user_id", $user_id);
$file_query->execute();

if($file_query->rowCount() > 0) {
	
	$file = $file_query->fetch();
	$url = $file["url"];
	
	header('Content-Type: application/octet-stream');
	header("Content-Transfer-Encoding: Binary"); 
	header("Content-disposition: attachment; filename=\"" . basename($url) . "\""); 
	readfile($url); 
		
}
?>
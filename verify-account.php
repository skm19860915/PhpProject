<?php
include("includes/config.php");
include("includes/functions.php");
include("includes/db_connect.php");
include("includes/upload.class.php");

if(!isset($_GET["code"])) {
	header("Location: index.php?action=invalid_verification_code");
	exit;
}

$code = htmlspecialchars($_GET["code"]);

$user_query = $dbh->prepare("SELECT id, email_verified FROM user WHERE email_activation_code = :code");
$user_query->bindParam(":code", $code);
$user_query->execute();

if($user_query->rowCount() > 0) {
	
	$user = $user_query->fetch();
	
	if($user["email_verified"] == 1) {
		
		header("Location: index.php?action=invalid_verification_code");
		exit;
		
	} else {
		
		$user_query = $dbh->prepare("UPDATE user SET email_verified = 1 WHERE email_activation_code = :code");
		$user_query->bindParam(":code", $code);
		$user_query->execute();
		
		header("Location: sign-in.php?action=code_validated");
		exit;
				
	}
	
}
?>
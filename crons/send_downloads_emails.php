<?php
include(__DIR__ . "/../includes/config.php");
include(__DIR__ . "/../includes/db_connect.php");
include(__DIR__ . "/../includes/upload.class.php");
include(__DIR__ . "/../includes/functions.php");


$select = $dbh->prepare("SELECT * FROM download_email");
$select->execute();

while($se = $select->fetch(PDO::FETCH_ASSOC)) {
	
	// Send mail
	$to = $se["to_email"];
	$download_url = $se["download_url"];
	$id = $se["id"];

	$subject = 'Download your album from Radtriads';
	
	$headers = "From: noreply@radtriads.com\r\n";
	$headers .= "Reply-To: noreply@radtriads.com\r\n";
	$headers .= "MIME-Version: 1.0\r\n";
	$headers .= "Content-Type: text/html; charset=UTF-8\r\n";
	
	$message = '<p style="font-family:Helvetica,Arial;text-align:left;">Hey,</p>';
	$message.= '<p style="font-family:Helvetica,Arial;text-align:left;">We are sending you this email because you requested to download your files from an album on our website. You can download it now by clicking on this link :<br>.';
	$message.= '<p style="font-family:Helvetica,Arial;text-align:left;"><a href="' . $download_url . '" style="font-size:16px; font-family: Helvetica, Arial, sans-serif;">' . $download_url . '</a></p>';
	$message.= '<p style="font-family:Helvetica,Arial;text-align:left;">Thanks.<br>The Radtriads Team</p><br><br><br>';
	
	mail($to, $subject, $message, $headers);
	
	// Delete this entry
	$delete = $dbh->prepare("DELETE FROM download_email WHERE id = $id");
	$delete->execute();
	
}

?>
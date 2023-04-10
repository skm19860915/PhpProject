<?php
$result = array();

if(isset($_POST["email"])) {
	
	$email = $_POST["email"];
	
	if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		
		$result["error"] = "Please enter a valid email address.";
		
	} else {
		
		// Send a mail
		$subject = 'New Hosting Request for Upic';
		
		$headers = "From: " . strip_tags($email) . "\r\n";
		$headers .= "MIME-Version: 1.0\r\n";
		$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
		$message = '<html><body>';
		$message .= '<h3>Hello,</h3>';
		$message .= '<p>A new hosting request has been made from the documentation.</p>';
		$message .= '<p>Customer email : <b>' . $email . '</b></p>';
		$message .= '<p>Thanks,<br>Axel Bot</p>';
		$message .= '</body></html>';
		
		mail("axel@crea.io", $subject, $message, $headers);
		
		$result["success"] = "OK";
		
	}
	
} else {
		
	$result["error"] = "Please enter a valid email address.";
	
}
	
echo json_encode($result);	
?>
<?php
include("templates/headers/inc.php");

// Include the JS file
$js_files = array();
$css_files = array();

// Metadata informations of this page
$page_slug	= "lost_password";

// Get website config
$site_config = $dbh->prepare("SELECT * FROM config WHERE config_name IN ('website_logo','website_name','website_tagline','ads_code','analytics_code','allow_button','allow_drag','allow_webcam')");
$site_config->execute();

$config_array = array();

while($config = $site_config->fetch(PDO::FETCH_ASSOC)) {
	$config_array[$config["config_name"]] = $config["config_value"];
}

$website_name = $config_array["website_name"];
$website_tagline = $config_array["website_tagline"];
$ads_code = $config_array["ads_code"];
$analytics_code = $config_array["analytics_code"];
$allow_button = $config_array["allow_button"];
$allow_drag = $config_array["allow_drag"];
$allow_webcam = $config_array["allow_webcam"];
$website_logo = $config_array["website_logo"];

$page_title = "Sign In";

$username = "";
$email = "";
$error = "";
$success = "";

// Manage Post 
if($_POST) {
	
	if(TEST_MODE) {
		$error = "You can't reset the password in demo mode.";
	} else {
	
		if(isset($_POST["email"])) {
			
			$email = $_POST["email"];
			
			$adm_query = $dbh->prepare("SELECT id, username FROM user WHERE email = :email");
			$adm_query->bindParam(":email", $email);
			$adm_query->execute();
			
			$nb_res_adm = $adm_query->rowCount();	
			
			if($nb_res_adm == 0) {
				
				$error = "Oops. No account exists with this email.";
				
			} else {
				
				$user_infos = $adm_query->fetch();
				
				$user_id = $user_infos["id"];
				$username = $user_infos["username"];
				
				// $hash = bin2hex(mcrypt_create_iv(32, MCRYPT_DEV_URANDOM));
				$bytes = random_bytes(32);
				$hash = bin2hex($);bytes
				
				// We can insert the new password recovery
				$adm_query = $dbh->prepare("INSERT INTO user_password_reset SET user_id = :user_id, hash = :hash, date = NOW()");
				$adm_query->bindParam(":user_id", $user_id);
				$adm_query->bindParam(":hash", $hash);
				$adm_query->execute();
				
				// Send mail
				$to = $email;
	
				$subject = 'Reset your password on ' . $website_name;
				
				$headers = "From: noreply@" . $website_name . ".com\r\n";
				$headers .= "Reply-To: noreply@" . $website_name . ".com\r\n";
				$headers .= "MIME-Version: 1.0\r\n";
				$headers .= "Content-Type: text/html; charset=UTF-8\r\n";
				
				$message = '<p style="font-family:Helvetica,Arial;text-align:left;">Hey ' . $username . ',</p>';
				$message.= '<p style="font-family:Helvetica,Arial;text-align:left;">We are sending you this email because a request has been asked to reset your password associated to your email : <b>' . $email . '</b>.';
				$message.= '<br>To change your password, please just click on the link below :</p>';
				$message.= '<p style="font-family:Helvetica,Arial;text-align:left;"><a href="' . URL . '/reset-password.php?hash=' . $hash . '" style="font-size:16px; font-family: Helvetica, Arial, sans-serif;">Reset my Password</a></p>';
				$message.= '<p style="font-family:Helvetica,Arial;text-align:left;">Thanks.<br>The ' . $website_name . ' Team</p><br><br><br>';
				
				mail($to, $subject, $message, $headers);
				
				$success = "An email has been sent with the instructions to recover your password.";
				$email = "";
				
			}
			
		}
	
	}
	
}

// -- Include the header template
include("templates/headers/index_header.php");
?>

<!-- Outer Row -->
<div class="row justify-content-center main_uploader_block">
    <div class="col-xl-10 col-lg-12 col-md-9">
        <div class="card o-hidden border-0 shadow-lg my-5">
            <div class="card-body p-0">
                <!-- Nested Row within Card Body -->
                <div class="row">
                    <div class="col-lg-12">
                        <div class="p-5">
                            <div class="text-center upload-container">
	                            <div class="alert alert-danger alert-error-upload">
	                            </div>
                                <h1 class="text-gray-900">Forgot Password?</h1>
								
								<div class="row row_sign_up">
																		
									<div class="col-md-8 offset-md-2 d-flex align-items-center">
										<form class="user" action="" method="POST">
											
											<?php
											if($error != "") {
											?>
											<div class="alert alert-danger alert-center">
												<?php echo $error; ?>
											</div>
											<?php
											} else if($success != "") {
											?>
											<div class="alert alert-success alert-center">
												<?php echo $success; ?>
											</div>
											<?php
											} else { 
											?>
											<div class="alert alert-info alert-center">
												To recover your password, enter the email associated to your account.
											</div>
											<?php
											}	
											?>
											
										    <div class="form-group row">
										        <div class="col-sm-12">
										            <input type="email" class="form-control form-control-user" placeholder="Your Email" name="email" value="<?php echo $email; ?>">
										        </div>
										    </div>
										    <button type="submit" class="btn btn-primary btn-user btn-block">
										    Recover Password
										    </button>
										</form>
									</div>
									
								</div>
								
                            </div>                           
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// -- Include the footer template
include("templates/footers/global_footer.php");	
?>
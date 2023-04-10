<?php
include("templates/headers/inc.php");

// Include the JS file
$js_files = array();
$css_files = array();

// Metadata informations of this page
$page_slug	= "reset_password";

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

$page_title = "Reset Password";

$error = "";
$success = "";

if(isset($_GET["hash"])) {
	
	$hash = htmlspecialchars($_GET["hash"]);
	
	// Check if the hash and user id combination is valid
	$check_acc_query = $dbh->prepare("SELECT id, user_id FROM user_password_reset WHERE hash = :hash");
	$check_acc_query->bindParam(":hash", $hash);
	$check_acc_query->execute();
	
	// If the email is already taken...
	if($check_acc_query->rowCount() > 0) {
		
		$user = $check_acc_query->fetch();

		$error = "";
	
		if($_POST) {
			
			if(isset($_POST["pass"]) && isset($_POST["repass"])) {
				
				$password = htmlspecialchars($_POST["pass"]);
				$repassword = htmlspecialchars($_POST["repass"]);
				
				if(strlen($password) < 4) {
					$error = "Your password should be at least 4 characters long.";
				} else if($password != $repassword) {
					$error = "Your passwords don't match.";
				} else {
					
					$new_pass = sha1($password);
					$user_id = $user["user_id"];
					
					// Update the user password
					$upd_acc_query = $dbh->prepare("UPDATE user SET password = :password WHERE id = :user_id");
					$upd_acc_query->bindParam(":password", $new_pass);
					$upd_acc_query->bindParam(":user_id", $user_id);
					$upd_acc_query->execute();
					
					
					// Delete the password reset entry
					$del_acc_query = $dbh->prepare("DELETE FROM user_password_reset WHERE hash = :hash");
					$del_acc_query->bindParam(":hash", $hash);
					$del_acc_query->execute();
					
					header("Location: sign-in.php?action=password_reset");
					
				}
				
			}
			
		}
		
	} else {
		header("Location: index.php");
		exit;
	}
} else {
	header("Location: index.php");
	exit;
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
                                <h1 class="text-gray-900">Reset Password</h1>
								
								<div class="row row_sign_up">
																		
									<div class="col-md-8 offset-md-2 d-flex align-items-center">
										<form class="user" method="POST" action="">
											
											<?php
											if($error != "") {
											?>
											<div class="alert alert-danger"><b>Oops</b>. <?php echo $error; ?></div>
											<?php
											} else { 
											?>
											<div class="alert alert-info alert-center">
												Please enter your new password below.
											</div>
											<?php
											}	
											?>
											
											<div class="form-group">
												<input type="password" name="pass" class="form-control form-control-user" id="pass" placeholder="Enter your new password">
											</div>
											<div class="form-group">
												<input type="password" name="repass" class="form-control form-control-user" id="repass" placeholder="Enter your new password again">
											</div>

											<button type="submit" class="btn btn-primary btn-user btn-block">
										    Reset Password
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
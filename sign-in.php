<?php
include("templates/headers/inc.php");

// Include the JS file
$js_files = array();
$css_files = array();

// Metadata informations of this page
$page_slug	= "sign_in";

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

// Manage Post 
if($_POST) {
	
	if(isset($_POST["username"]) && isset($_POST["password"])) {
	
		$username = $_POST["username"];
		$password = $_POST["password"];
		
		if(strlen($username) < 3) {
			
			$error = "Your username must be at least 3 characters long.";
			
		} else if(strlen($password) < 4) {
			
			$error = "You need to specify a password with at last 4 characters.";
			
		} else {
			
			$password = sha1($password);
			
			$check_user_sql = $dbh->prepare("SELECT id, username, email, profile_picture, rank, first_pay, is_paying, stripe_plan, email_verified
											 FROM user
											 WHERE 
											 username = :username 
											 AND password = :password");
			$check_user_sql->bindParam(":username", $username);
			$check_user_sql->bindParam(":password", $password);
			$check_user_sql->execute();
			
			if($check_user_sql->rowCount() == 0) {
				
				$error = "Wrong username or / and password.";
				
			} else {
			
				$user = $check_user_sql->fetch();
				
				if($user["email_verified"] == 0) {
					
					$error = "Your account has not been verified. Please verify it via the confirmation email we sent you when you created an account.";
					
				} else {
				
					$user_id = $user["id"];
					$email = $user["email"];
					$rank = $user["rank"]; 
					$first_pay = $user["first_pay"];
					$is_paying = $user["is_paying"];
					$stripe_plan = $user["stripe_plan"];
					
					// Create the sessions variables
					$_SESSION["USER_ID"] = $user_id;
					$_SESSION["EMAIL"] = $email;
					$_SESSION["USERNAME"] = $username;
					$_SESSION["RANK"] = $rank;
					$_SESSION["FIRST_PAY"] = $first_pay;
					$_SESSION["IS_PAYING"] = $is_paying;
					
					if($stripe_plan == "") {
						$_SESSION["SUBSCRIPTION_PLAN"] = FREE_PLAN;
					} else {
						$_SESSION["SUBSCRIPTION_PLAN"] = $stripe_plan;
					}
					
						
					header("Location: dashboard.php?action=sign_in");
				
				}
					
				
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
                                <h1 class="text-gray-900">Sign In</h1>
								
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
											} else if(isset($_GET["action"])) {
												
												$action = $_GET["action"];
												
												if($action == "password_reset") {
													
												?>
												<div class="alert alert-success alert-center">
													
													<b>Awesome.</b> Your password has been reset. You can now log-in again.
													
												</div>
												<?php
													
												} else if($action == "uploaded_need_log_in") {
													
												?>
												<div class="alert alert-success alert-center">
													
													<b>Awesome.</b> Your photos have been uploaded. Please log-in / create an account to manage them in your personal space.
													
												</div>
												<?php
													
												}
												
											}
											?>
											
											<?php
											if(TEST_MODE == 1) {
											?>
											<div class="alert alert-info alert-center">
												<b>DEMO LOGIN</b><br>
												
												<b>Username</b> : admin<br>
												<b>Password</b> : admin
											</div>
											<?php
											}
											?>
											
										    <div class="form-group row">
										        <div class="col-sm-6 mb-3 mb-sm-0">
										            <input type="text" class="form-control form-control-user" placeholder="Username" name="username" value="<?php echo $username; ?>">
										        </div>
										        <div class="col-sm-6">										            
											        <input type="password" class="form-control form-control-user" placeholder="Password" name="password">

										        </div>
										    </div>
										    <button type="submit" class="btn btn-primary btn-user btn-block">
										    Sign In
										    </button>
										    <div class="lost_pass_sign_in">
										    	<a href="lost-password.php">Lost Password?</a>
										    </div>
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
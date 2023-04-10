<?php
include("templates/headers/inc.php");

// Include the JS file
$js_files = array();
$css_files = array();

// Check if we have a plan in the GET var
if(!isset($_GET["plan"])) {
	header("Location: index.php?action=pick_plan");
	exit;
}

$plan_selected = htmlspecialchars($_GET["plan"]);

// Metadata informations of this page
$page_slug	= "sign_up";

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

$page_title = "Sign Up";

$username = "";
$email = "";
$error = "";
$success_msg = "";

// Manage Post
if($_POST) {
	
	if(isset($_POST["username"]) && isset($_POST["email"]) && isset($_POST["password"]) && isset($_POST["repassword"])) {
	
		$username = $_POST["username"];
		$email = $_POST["email"];
		$password = $_POST["password"];
		$repassword = $_POST["repassword"];
		
		if(!isset($_POST["accept_tos"])) {
			
			$error = "Please accept the terms and conditions.";
			
		} else if(strlen($username) < 4) {
			
			$error = "Your username must be at least 3 characters long.";
			
		} else if(strlen($username) > 12) {
			
			$error = "Your username must be at most 12 characters long.";
			
		} else if(!preg_match('/^[\w-]+$/', $username)) {
			
			$error = "Your username can't contain spaces or special characters.";
			
		} else if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			
			$error = "You need to specify a valid email address.";
			
		} else if(strlen($password) < 4) {
			
			$error = "You need to specify a password with at last 4 characters.";
			
		} else if($password != $repassword) {
			
			$error = "Your password and confirmation password are different.";
			
		} else if(	$plan_selected != "silver_monthly" && 
					$plan_selected != "gold_monthly" && 
					$plan_selected != "platinum_monthly" && 
					$plan_selected != "silver_yearly" &&
					$plan_selected != "gold_yearly" &&
					$plan_selected != "platinum_yearly") {
					
			$error = "Your plan <strong>$plan_selected</strong> is not valid.<br>Please go back to the homepage and pick a valid plan.";	
						
		} else {
			
			$check_username_sql = $dbh->prepare("SELECT id FROM user WHERE username = :username");
			$check_username_sql->bindParam(":username", $username);
			$check_username_sql->execute();
			
			if($check_username_sql->rowCount() > 0) {
				
				$error = "This username is already taken. Please choose another one.";
				
			} else {
			
				$check_email_sql = $dbh->prepare("SELECT id FROM user WHERE email = :email");
				$check_email_sql->bindParam(":email", $email);
				$check_email_sql->execute();
				
				if($check_email_sql->rowCount() > 0) {
				
					$error = "This email is already taken. Please choose another one.";
					
				} else {
					
					// Count total users in the DB
					$user_total_sql = $dbh->prepare("SELECT id FROM user");
					$user_total_sql->execute();
					$total_users = $user_total_sql->rowCount();
					
					if($total_users == 0) {
						$rank = 1;
					} else {
						$rank = 0;
					}
					
					$password = sha1($password);
					$unique_id = sha1(time().mt_rand(0,9999));
					
					// Determine the plan ID
					if($plan_selected == "silver_monthly" || $plan_selected == "silver_yearly") {
						$plan_id = 1;
					} 
					else if($plan_selected == "gold_monthly" || $plan_selected == "gold_yearly") 
					{
						$plan_id = 2;
					} 
					else if($plan_selected == "platinum_monthly" || $plan_selected == "platinum_yearly") 
					{
						$plan_id = 3;
					}
					
					$activationcode = md5($email.time());
					
					// We can create the user
					$stmt = $dbh->prepare("INSERT INTO 
										   user 
										   SET 
										   unique_id = :unique_id,
										   username = :username,
										   password = :password,
										   email = :email,
										   profile_picture = '',
										   created_at = NOW(),
										   rank = :rank,
										   stripe_plan = :plan,
										   plan_id = :plan_id,
										   email_activation_code = '$activationcode',
										   status = 1");
										
					$stmt->bindParam(':unique_id', $unique_id);
					$stmt->bindParam(':username', $username);
					$stmt->bindParam(':password', $password);
					$stmt->bindParam(':email', $email);
					$stmt->bindParam(':rank', $rank);
					$stmt->bindParam(':plan', $plan_selected);
					$stmt->bindParam(':plan_id', $plan_id);
					$stmt->execute();
					
					$user_id = $dbh->lastInsertId();
					
					/*
					// Create the sessions variables
					$_SESSION["USER_ID"] = $user_id;
					$_SESSION["EMAIL"] = $email;
					$_SESSION["USERNAME"] = $username;
					$_SESSION["RANK"] = $rank;
					$_SESSION["FIRST_PAY"] = 0;
					$_SESSION["IS_PAYING"] = 0;
					$_SESSION["SUBSCRIPTION_PLAN"] = $plan_selected;
					*/
					
					$user_directory = "uploads/" . $unique_id;
					
					// Create a folder for this user
					if (!is_dir($user_directory)) {
						mkdir($user_directory);
					}
					
					/*
					header("Location: dashboard.php?action=registered");
					exit;
					*/
					
					$to = $email;
					$subject = "Confirm your email address on RadTriads.com";
					$headers = "MIME-Version: 1.0"."\r\n";
					$headers .= 'Content-type: text/html; charset=iso-8859-1'."\r\n";
					$headers .= 'From: RadTriads.com <radtriadbusiness@gmail.com>'."\r\n";
					$ms ="<html></body><div><div>Dear $username,</div></br></br>";
					$ms .="<div style='padding-top:8px;'>Please click on the following link to confirm your account on RadTriads.com :</div>
					<div style='padding-top:10px; font-size:20px;'><a href='https://www.radtriads.com/verify-account.php?code=$activationcode'>Activate my RadTriads.com account</a></div>
					<p>
					Thanks,<br>
					The RadTriads team
					</p>
					</body></html>";
					
					mail($to,$subject,$ms,$headers);
					
					$success_msg = "<b>Congrats!</b> Your account has been created. Please confirm it by clicking on the link we just sent you by email.";
								
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
                                <h1 class="text-gray-900">Sign Up</h1>
								
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
											} else if($success_msg != "") {
											?>
											<div class="alert alert-info alert-center">
												
												<?php echo $success_msg; ?>
												
											</div>
											<?php
											}
											?>
											
										    <div class="form-group row">
										        <div class="col-sm-6 mb-3 mb-sm-0">
										            <input type="text" class="form-control form-control-user" placeholder="Username" name="username" value="<?php echo $username; ?>">
										        </div>
										        <div class="col-sm-6">
										            <input type="email" class="form-control form-control-user" placeholder="Email Address" name="email" value="<?php echo $email; ?>">
										        </div>
										    </div>
										    <div class="form-group row">
										        <div class="col-sm-6 mb-3 mb-sm-0">
										            <input type="password" class="form-control form-control-user" placeholder="Password" name="password">
										        </div>
										        <div class="col-sm-6">
										            <input type="password" class="form-control form-control-user" placeholder="Repeat Password" name="repassword">
										        </div>
										    </div>
										    <p>
											    <input type="checkbox" name="accept_tos" style="vertical-align: -1px; margin-right: 1px; zoom: 1.2;" /> By creating an account you accept our <a href="page.php?id=4">Terms of Use</a>.
										    </p>
										    <button type="submit" class="btn btn-primary btn-user btn-block">
										    Register
										    </button>
										    <small>- You can start for free -</small>
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
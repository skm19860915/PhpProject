<?php
include("templates/headers/inc.php");
include("templates/headers/check_user_subscription.php");
include("templates/headers/calculate_usage.php");

// Include the JS file
$js_files = array("js/bootbox.all.min.js", "js/pages/my_account.js");
$css_files = array();

// Metadata informations of this page
$page_slug	= "my_account";


if(!$_SESSION) {
	
	header("Location: sign-in.php");
	exit;
	
}

if(!isset($_GET["tab"])) {
	$tab = "my_infos";
} else {
	$tab = htmlspecialchars($_GET["tab"]);
}

// Check the user and update his session...
$user_id = $_SESSION["USER_ID"];

$get_user_query = $dbh->prepare("SELECT stripe_plan, stripe_subscription_id, stripe_customer_id, first_pay FROM user WHERE id = :user_id");
$get_user_query->bindParam(":user_id", $user_id);
$get_user_query->execute();

if($get_user_query->rowCount() == 0) {
	
	header("Location: index.php?action=need_choose_plan");
	exit;
	
}


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

$get_files_query = $dbh->prepare("SELECT id FROM file WHERE user_id = :user_id");
$get_files_query->bindParam(":user_id", $user_id);
$get_files_query->execute();

$nb_files_user = $get_files_query->rowCount();

$page_title = "My Info";

$username = "";
$email = "";
$error = "";
$error_password = "";

$user_infos = $dbh->prepare("SELECT * FROM user WHERE id = :user_id");
$user_infos->bindParam(":user_id", $user_id);
$user_infos->execute();

$user = $user_infos->fetch();

$first_pay = $user["first_pay"];
$username = trim($user["username"]);
$email = trim($user["email"]);

$success_my_infos = "";
$success_settings = "";
$success_security = "";
$success_password = "";
$success_privacy = "";

$user_id = $_SESSION["USER_ID"];

$usr_query = $dbh->prepare("SELECT id, is_account_public, stripe_plan, plan_id, unique_id, created_at, username, stripe_subscription_id, stripe_customer_id, show_social_share, show_direct_link, show_forum_code, show_html_code FROM user WHERE id = :id");
$usr_query->bindParam(":id", $user_id);
$usr_query->execute();

$user = $usr_query->fetch();

$sub = $user["stripe_subscription_id"];
$cus = $user["stripe_customer_id"];

$show_html_code = $user["show_html_code"];
$show_direct_link = $user["show_direct_link"];
$show_forum_code = $user["show_forum_code"];
$show_social_share = $user["show_social_share"];
$is_account_public = $user["is_account_public"];

$stripe_plan = $user["plan_id"];

if(isset($_GET["apply_pass_change"])) {
	
	$new_pass = htmlspecialchars($_GET["apply_pass_change"]);
	
	$upd_user = $dbh->prepare("UPDATE user SET password = :new_pass, password_tmp = '' WHERE id = :user_id");
	$upd_user->bindParam(":user_id", $user_id);
	$upd_user->bindParam(":new_pass", $new_pass);
	$upd_user->execute();
	
	$success_password = "Your password has been successfully changed and validated.";
	
}

if($stripe_plan == "") {
	$_SESSION["SUBSCRIPTION_PLAN"] = FREE_PLAN;
} else {
	$_SESSION["SUBSCRIPTION_PLAN"] = $stripe_plan;
}


$error_stripe = "";
		
try {
	
	\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);
	$subscription = \Stripe\Subscription::retrieve($sub);

	
	$customer = \Stripe\Customer::retrieve($cus);
	
} catch (Exception $e) {
	
	$error_stripe = $e->getMessage();
	
}


// Manage Post 
if($_POST) {
		
	if(isset($_POST["form_type"]) && $_POST["form_type"] == "my_infos") {
		
		$tab = "my_infos";
				
		if(isset($_POST["email"])) {
			
			$email = $_POST["email"]; 
			
			if(isset($_POST["current_pass"]) && empty($_POST["current_pass"])) {
				$error = "Please enter your password to change your info.";
			} else {
				
				$current_pass = $_POST["current_pass"];
				$current_pass_sha1 = sha1($current_pass);
				
				$adm_query = $dbh->prepare("SELECT id FROM user WHERE username = :username AND password = :password");
				$adm_query->bindParam(":username", $username);
				$adm_query->bindParam(":password", $current_pass_sha1);
				$adm_query->execute();
				
				$nb_res_adm = $adm_query->rowCount();	
				
				if($nb_res_adm == 0) {
					
					$error = "Oops. Your current password is incorrect.";
					
				} else {
				
					if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
						
						$error = "Please provide a valid email address...";
						
					} else {
					
						$user_infos = $dbh->prepare("UPDATE user SET email = :email WHERE id = :user_id");
						$user_infos->bindParam(":email", $email);
						$user_infos->bindParam(":user_id", $user_id);
						$user_infos->execute();
									
						// Update the sessions variables
						$_SESSION["EMAIL"] = $email;
							
						$success_my_infos = "Your profile has been updated.";
					
					}
				
				}
			
			}
		
		}
		
	
		
	} else if(isset($_POST["form_type"]) && $_POST["form_type"] == "settings") {
		
		$tab = "settings";
		
		if(isset($_POST["show_direct_link"])) {
			
			$show_direct_link = 1;
			
		} else {
			
			$show_direct_link = 0;
			
		}
		
		if(isset($_POST["show_html_code"])) {
			
			$show_html_code = 1;
			
		} else {
			
			$show_html_code = 0;
			
		}
		
		if(isset($_POST["show_forum_code"])) {
			
			$show_forum_code = 1;
			
		} else {
			
			$show_forum_code = 0;
			
		}
		
		if(isset($_POST["show_social_share"])) {
			
			$show_social_share = 1;
			
		} else {
			
			$show_social_share = 0;
			
		}
		
		$user_infos = $dbh->prepare("UPDATE user SET show_social_share = :show_social_share, show_forum_code = :show_forum_code, show_html_code = :show_html_code, show_direct_link = :show_direct_link WHERE id = :user_id");
		$user_infos->bindParam(":show_direct_link", $show_direct_link);
		$user_infos->bindParam(":show_social_share", $show_social_share);
		$user_infos->bindParam(":show_forum_code", $show_forum_code);
		$user_infos->bindParam(":show_html_code", $show_html_code);
		$user_infos->bindParam(":user_id", $user_id);
		$user_infos->execute();
		
		$success_settings = "Your settings have been updated.";

	} else if(isset($_POST["form_type"]) && $_POST["form_type"] == "password") {
			
		$tab = "password";
		
		if(isset($_POST["current_pass"]) && isset($_POST["pass"]) && isset($_POST["repass"])) {
			
			$current_pass = $_POST["current_pass"];
			$pass = $_POST["pass"];
			$repass = $_POST["repass"];
			$current_pass_sha1 = sha1($current_pass);
			
			$adm_query = $dbh->prepare("SELECT id, email, username FROM user WHERE username = :username AND password = :password");
			$adm_query->bindParam(":username", $username);
			$adm_query->bindParam(":password", $current_pass_sha1);
			$adm_query->execute();
			
			$nb_res_adm = $adm_query->rowCount();	
			
			if($nb_res_adm == 0) {
				
				$error_password = "Oops. Your current password is incorrect.";
				
			} else {
				
				if (strlen($pass) < 4) {
					$error_password = "Your password must be at least 4 characters long.";
				} else if ($pass != $repass) {
					$error_password = "Your password and confirmation password don't match.";
				} else {
					
					$user_email_i = $adm_query->fetch();
					$user_email = $user_email_i["email"];
					$user_username = $user_email_i["username"];
					
					$pass_sha1 = sha1($pass);
					
					$upd_user = $dbh->prepare("UPDATE user SET password_tmp = :password WHERE id = :user_id");
					$upd_user->bindParam(":user_id", $user_id);
					$upd_user->bindParam(":password", $pass_sha1);
					$upd_user->execute();
					
					$to = $user_email;
					$subject = "Confirm your password change on RadTriads.com";
					$headers = "MIME-Version: 1.0"."\r\n";
					$headers .= 'Content-type: text/html; charset=iso-8859-1'."\r\n";
					$headers .= 'From: RadTriads.com <radtriadbusiness@gmail.com>'."\r\n";
					$ms ="<html></body><div><div>Dear $username,</div></br></br>";
					$ms .="<div style='padding-top:8px;'>Please click on the following link to confirm your password change on RadTriads.com :</div>
					<div style='padding-top:10px; font-size:20px;'><a href='https://www.radtriads.com/my-account.php?tab=password&apply_pass_change=$pass_sha1'>Confirm my RadTriads.com password change</a></div>
					<p>
					Thanks,<br>
					The RadTriads team
					</p>
					</body></html>";
					
					mail($to,$subject,$ms,$headers);
															
					$success_password = "<b>Thanks!</b> Please click on the email we just sent you to validate your password change.";
					
				}
				
			}
			
		}
					
	} else if(isset($_POST["form_type"]) && $_POST["form_type"] == "security") {
			
		$tab = "security";
		
		if(isset($_POST["privacy_settings"])) {
			
			$is_account_public = $_POST["privacy_settings"];
			
			if($is_account_public == 1) {
				$is_protected = 0;
			} else {
				$is_protected = 1;
			}
									
			$upd_user = $dbh->prepare("UPDATE user SET is_account_public = :privacy_settings WHERE id = :user_id");
			$upd_user->bindParam(":user_id", $user_id);
			$upd_user->bindParam(":privacy_settings", $is_account_public);
			$upd_user->execute();
			
			$upd_user = $dbh->prepare("UPDATE album SET is_protected = :is_protected WHERE user_id = :user_id");
			$upd_user->bindParam(":user_id", $user_id);
			$upd_user->bindParam(":is_protected", $is_protected);
			$upd_user->execute();
			
			$upd_user = $dbh->prepare("UPDATE album_home SET is_protected = :is_protected WHERE user_id = :user_id");
			$upd_user->bindParam(":user_id", $user_id);
			$upd_user->bindParam(":is_protected", $is_protected);
			$upd_user->execute();
			
			$success_privacy = "<b>Congrats!</b> Your privacy settings have been updated.";
			
		}
					
	}
}

/*
if($subscription->plan->id == "price_1GzekbI8XlJR7K1GPh8g1FOO") {
	$plan_name = "SILVER";
} else if($subscription->plan->id == "price_1Gzek2I8XlJR7K1GK6F2HHz0") {
	$plan_name = "GOLD";
} else {
	$plan_name = "PLATINUM";
}
*/




// -- Include the header template
include("templates/headers/index_header.php");
?>

<!-- Outer Row -->
<div class="row justify-content-center main_uploader_block">
	
	<div class="col-md-3">
		<div class="card my-infos o-hidden border-0 shadow-lg my-5 p-2">
			<div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
				<a class="nav-link <?php if($tab == "my_infos"): ?>active<?php endif; ?>" id="v-pills-home-tab" data-toggle="pill" href="#v-pills-home" role="tab" aria-controls="v-pills-home" aria-selected="true">My Info</a>
				<a class="nav-link <?php if($tab == "plan"): ?>active<?php endif; ?>" id="v-pills-profile-tab" data-toggle="pill" href="#v-pills-profile" role="tab" aria-controls="v-pills-profile" aria-selected="false">Plan & Usage</a>
				<a class="nav-link <?php if($tab == "settings"): ?>active<?php endif; ?>" id="v-pills-messages-tab" data-toggle="pill" href="#v-pills-messages" role="tab" aria-controls="v-pills-messages" aria-selected="false">Settings</a>
				<a class="nav-link <?php if($tab == "security"): ?>active<?php endif; ?>" id="v-pills-settings-tab" data-toggle="pill" href="#v-pills-settings" role="tab" aria-controls="v-pills-settings" aria-selected="false">Privacy & Security</a>
				<a class="nav-link <?php if($tab == "password"): ?>active<?php endif; ?>" id="v-pills-settings-tab" data-toggle="pill" href="#v-pills-password" role="tab" aria-controls="v-pills-settings" aria-selected="false">Password</a>
			</div>
		</div>
	</div>
    <div class="col-md-9">
        <div class="card card-dashboard o-hidden border-0 shadow-lg my-5">
            <div class="card-body p-0">
                <!-- Nested Row within Card Body -->
                <div class="tab-content" id="v-pills-tabContent">
	                <div class="tab-pane fade  <?php if($tab == "my_infos"): ?>show active<?php endif; ?>" id="v-pills-home" role="tabpanel" aria-labelledby="v-pills-home-tab">
		                <div class="row">
		                    <div class="col-lg-12">
		                        <div class="p-5">
		                            <div class="text-center upload-container">
			                            <div class="alert alert-danger alert-error-upload">
			                            </div>
		                                <h1 class="text-gray-900">My Info</h1>
										
										<div class="row row_sign_up">
																				
											<div class="col-md-8 offset-md-2 d-flex align-items-center">
												<form class="user" action="" method="POST">
													<input type="hidden" name="form_type" value="my_infos" />
													<input type="hidden" name="username" value="-" />
													<?php
													if($error != "") {
													?>
													<div class="alert alert-danger alert-center">
														
														<?php echo $error; ?>
														
													</div>
													<?php
													} else if($success_my_infos != "") {
													?>
														
														<div class="alert alert-success alert-center">
															
															<b>Awesome.</b> Your account has been updated.
															
														</div>
													<?php
													}
													?>
													
												    <div class="form-group row">
												        <div class="col-sm-6 mb-3 mb-sm-0">
													        <label class="text-gray-900">Username</label>
												            <input type="text" class="form-control form-control-user" disabled placeholder="Username" value="<?php echo $username; ?>">
												        </div>
												        <div class="col-sm-6">	
													        <label class="text-gray-900">Email</label>									            
													        <input type="email" class="form-control form-control-user" placeholder="Email" name="email" value="<?php echo $email; ?>">
		
												        </div>
												    </div>
												    
												    <div class="form-group row">
												        <div class="col-sm-12">	
													        <label class="text-gray-900">Enter your password to change your email</label>									            
													        <input type="password" class="form-control form-control-user" placeholder="Your Current Password" name="current_pass">
		
												        </div>
												    </div>
												    
												    <button type="submit" class="btn btn-primary btn-user btn-block">
												    Update Account
												    </button>
												    <div class="row row-account-actions">
													    
													    <div class="col-md-6">
														    <a href="logout.php">Logout</a>
													    </div>
													    
													    <div class="col-md-6">
														    <a href="delete-account.php" class="btn-delete-account">Delete Account</a>
													    </div>
													    
												    </div>
												</form>
											</div>
											
										</div>
										
		                            </div>                           
		                        </div>
		                    </div>
		                </div>
		            </div>
		            <div class="tab-pane fade  <?php if($tab == "plan"): ?>show active<?php endif; ?>" id="v-pills-profile" role="tabpanel" aria-labelledby="v-pills-profile-tab">
			                     
			            <div class="row">
		                    <div class="col-lg-12">
		                        <div class="p-5">
		                            <div class="text-center upload-container">
			                            <?php
				                        if(isset($_GET["action_payment"])) {
					                    ?>
					                    
			                            <div class="alert alert-success">
				                            Your payment info has been updated!
			                            </div>
					                    <?php
				                        }  
				                        ?>
			                            <div class="alert alert-danger alert-error-upload">
			                            </div>
		                                <h1 class="text-gray-900">Plan & Usage</h1>
		                                
		                                <div class="row row_sign_up">
			                                <div class="col-md-12">
					                            <h5>Files Count</h5>
					                            
					                            <p>
						                            You have uploaded <strong><?php echo $nb_files_user; ?></strong> files in your account.
					                            </p>
			                                </div>
		                                </div>
										<hr />
										<div class="row">   
											<!--            
				                            <div class="col-md-6">
				                                
				                                <h5>Bandwidth Usage</h5>
				                                
												<div class="progress">
													<div class="progress-bar" role="progressbar" style="width: <?php echo $percent_bandwidth; ?>%;" aria-valuenow="<?php echo $percent_bandwidth; ?>" aria-valuemin="0" aria-valuemax="100">
														<div class="percent_val"><?php echo $percent_bandwidth; ?>%</div>
														
													</div>
												</div>
												<?php echo $user_bandwidth; ?> / <?php echo $max_bandwidth; ?> MB
				                            </div>
				                            -->
				                            <div class="col-md-12">
				                                <h5>Disk Space Usage</h5>
												<div class="progress">
													<div class="progress-bar" role="progressbar" style="width: <?php echo $percent_diskspace; ?>%;" aria-valuenow="<?php echo $percent_diskspace; ?>" aria-valuemin="0" aria-valuemax="100">
														<div class="percent_val"><?php echo $percent_diskspace; ?>%</div>
													</div>
												</div>
												<?php echo formatBytes($user_diskspace, 1); ?> /
												<?php 
												if($max_diskspace != 0) {
													echo $max_diskspace/100000000 . "GB";
												} else {
													echo '<i class="fas fa-infinity"></i> (Unlimited Storage)';
												}
												?>
				                            </div>
				                        </div>
		                            </div>
		                            <hr />
		                            <div class="row">
										
			                            
			                            <div class="col-md-12">
				                            <div class="form_container pay_container sub_container">
					                            <?php
						                        if($sub_admin_set) {
							                    ?>
							                    <h4>Plan Selected : <b><?php echo strtoupper($displayed_plan_name); ?></b></h4>
							                    <a href="switch-plan.php" class="btn btn-primary upgrade_plan_btn">Upgrade Plan</a>
							                    <?php
						                        } else {   
							                    ?>
						                            <?php
							                        if($is_free_trial) {
								                    ?>
													<h4>Plan Selected : <b><?php echo strtoupper($displayed_plan_name); ?></b></h4>
													<h3>You are currently on a <b>FREE TRIAL</b> until <?php echo date("m/d/Y", $end_free_trial_date); ?></h3>
													<a href="switch-plan.php" class="btn btn-primary upgrade_plan_btn">Upgrade Plan</a>
								                    <?php
									                } else {    
							                        ?>
													<h4>Plan Selected : <b><?php echo strtoupper($displayed_plan_name); ?></b> <small><a class="switch-plan-link" href="switch-plan.php?hide_<?php echo strtolower($displayed_plan_name); ?>=1">CHANGE</a></small></h4>
													<h3>Your subscription is currently : <span class="badge badge-success"><?php echo $subscription->status; ?></span></h3>
													<hr />
													<p>
														Your cart will be billed automatically on <b><?php echo date("m/d/Y", $subscription->current_period_end); ?></b>
														<a href="reditect-update-pay.php" class="update_card_infos">Update Card Info</a>
													</p>
												<?php
													}
												}
												?>
											</div>
			                            </div>
		                            </div>
		                        </div>
		                    </div>
			            </div>
			            
		            </div>
					<div class="tab-pane fade  <?php if($tab == "settings"): ?>show active<?php endif; ?>" id="v-pills-messages" role="tabpanel" aria-labelledby="v-pills-messages-tab">
						
						<div class="row">
		                    <div class="col-lg-12">
		                        <div class="p-5">
		                            <div class="text-center upload-container">
			                            <div class="alert alert-danger alert-error-upload">
			                            </div>
		                                <h1 class="text-gray-900">Settings</h1>
		                                <div class="row row_sign_up">
																				
											<div class="col-md-8 offset-md-2 d-flex align-items-center">
												<form class="user" action="" method="POST">
													<input type="hidden" name="form_type" value="settings" />
													<?php
													if($success_settings != "") {
													?>
														
														<div class="alert alert-success alert-center">
															
															<b>Awesome.</b> Your settings have been updated.
															
														</div>
													<?php
													}
													?>
													<div class="text-left">
														<h5>Quick Link Share <small>Show / Hide links below your files</small></h5>
														<?php 
														if($displayed_plan_name == "Silver") {
														?>
														
														<div class="form-check">
															<input name="show_direct_link" class="form-check-input" type="checkbox" value="" id="show_direct_link" disabled="">
															<label class="form-check-label" for="show_direct_link">
																Show Direct Link
															</label>
														</div>
														<?php
														} else {	
														?>
														<div class="form-check">
															<input name="show_direct_link" class="form-check-input" type="checkbox" value="" id="show_direct_link" <?php if($show_direct_link == 1): ?>checked<?php endif; ?>>
															<label class="form-check-label" for="show_direct_link">
																Show Direct Link
															</label>
														</div>
														<?php
														}
														?>
														<div class="form-check">
															<input name="show_html_code" class="form-check-input" type="checkbox" value="" id="show_html_code" <?php if($show_html_code == 1): ?>checked<?php endif; ?>>
															<label class="form-check-label" for="show_html_code">
																Show HTML Code
															</label>
														</div>
														<div class="form-check">
															<input name="show_forum_code" class="form-check-input" type="checkbox" value="" id="show_forum_code" <?php if($show_forum_code == 1): ?>checked<?php endif; ?>>
															<label class="form-check-label" for="show_forum_code">
																Show Bulletin Boards & Forums Code
															</label>
														</div>
														<div class="form-check">
															<input name="show_social_share" class="form-check-input" type="checkbox" value="" id="show_social_share" <?php if($show_social_share == 1): ?>checked<?php endif; ?>>
															<label class="form-check-label" for="show_social_share">
																Show Social Share Buttons
															</label>
														</div>
													</div>
													<br />
													<button type="submit" class="btn btn-primary btn-user btn-block">
												    Update Settings
												    </button>
												</form>
												
											</div>
											
		                                </div>
		                                
		                                
		                            </div>
		                        </div>
		                    </div>
						</div>
						
					</div>
					<div class="tab-pane fade  <?php if($tab == "security"): ?>show active<?php endif; ?>" id="v-pills-settings" role="tabpanel" aria-labelledby="v-pills-settings-tab">
						
						<div class="row">
		                    <div class="col-lg-12">
		                        <div class="p-5">
		                            <div class="text-center upload-container">
			                            <div class="alert alert-danger alert-error-upload">
			                            </div>
		                                <h1 class="text-gray-900">Privacy & Security</h1>
		                                <div class="row row_sign_up">
																				
											<div class="col-md-8 offset-md-2 d-flex align-items-center">
									
												<form class="user user_update_privacy" action="" method="POST">
													<input type="hidden" name="form_type" value="security" />
													<?php
													if($success_privacy != "") {
													?>
														<div class="alert alert-info alert-center">
															
															<b>Awesome.</b> Your privacy settings have been changed.
															
														</div>
													<?php
													} else {
													?>
													<div class="alert alert-info text-center">
														Set your account privacy to <b>private</b> if you don't want anyone to be able to access your files or <b>public</b> if you want to allow anyone who has the links to your files to access them.
													</div>
													<?php
													}
													?>
													
													<div class="row">
														<div class="col-md-12">
															<div class="form-group">
																<select name="privacy_settings" class="form-control">
																	<option value="0" <?php if($is_account_public == 0): ?>selected<?php endif; ?>>Account is Private</option>
																	<option value="1" <?php if($is_account_public == 1): ?>selected<?php endif; ?>>Account is Public</option>
																</select>
												        	</div>
														</div>
													</div>
													
													
													<br />
													<button type="submit" class="btn btn-primary btn-user btn-block btn-update-privacy">
												    Update Privacy
												    </button>
												</form>

												
											</div>
											
		                                </div>
		                                
		                                
		                            </div>
		                        </div>
		                    </div>
						</div>
						
					</div>
					
					<div class="tab-pane fade  <?php if($tab == "password"): ?>show active<?php endif; ?>" id="v-pills-password" role="tabpanel" aria-labelledby="v-pills-password-tab">
						
						<div class="row">
		                    <div class="col-lg-12">
		                        <div class="p-5">
		                            <div class="text-center upload-container">
			                            <div class="alert alert-danger alert-error-upload">
			                            </div>
		                                <h1 class="text-gray-900">Password</h1>
		                                <div class="row row_sign_up">
																				
											<div class="col-md-8 offset-md-2 d-flex align-items-center">
												<form class="user" action="" method="POST">
													<input type="hidden" name="form_type" value="password" />
													<?php
													if($success_password != "") {
													?>
														<div class="alert alert-info alert-center">
															
															<?php echo $success_password; ?>
															
														</div>
													<?php
													} else if($error_password != "") {
													?>
														<div class="alert alert-danger alert-center">
															
															<?php echo $error_password; ?>
															
														</div>
													<?php
													} else {
													?>
													<div class="alert alert-info text-center">
														To change your password, please first type your actual password for security reasons.
													</div>
													<?php
													}
													?>
													
													<div class="row">
														<div class="col-md-12">
															<div class="form-group">
																<input type="password" class="form-control" placeholder="Current Password" name="current_pass">
												        	</div>
														</div>
													</div>
													
													<div class="row">
										
														<div class="col-md-6">
												        	<div class="form-group">
																<input type="password" class="form-control" placeholder="New Password" name="pass">
												        	</div>
											        	</div>
											        	
											        	<div class="col-md-6">
												        	<div class="form-group">
											            		<input type="password" class="form-control" placeholder="New Password again" name="repass">
												        	</div>
											        	</div>
														
													</div>
													
													<br />
													<button type="submit" class="btn btn-primary btn-user btn-block">
												    Update Password
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
    </div>
</div>

<?php
// -- Include the footer template
include("templates/footers/global_footer.php");	
?>
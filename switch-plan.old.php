<?php
include("templates/headers/inc.php");
include("templates/headers/calculate_usage.php");

// Include the JS file
$js_files = array();
$css_files = array();

// Metadata informations of this page
$page_slug	= "choose_plan";

if(!$_SESSION) {
	
	header("Location: sign-in.php");
	exit;
	
}

if(isset($_GET["hide_platinum"])) {
	$hide_platinum = true;
} else {
	$hide_platinum = false;
}

if(isset($_GET["hide_silver"])) {
	$hide_silver = true;
} else {
	$hide_silver = false;
}

if(isset($_GET["hide_gold"])) {
	$hide_gold = true;
} else {
	$hide_gold = false;
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

$user_id = $_SESSION["USER_ID"];

$usr_query = $dbh->prepare("SELECT id, stripe_plan, unique_id, created_at, username, stripe_subscription_id, stripe_customer_id FROM user WHERE id = :id");
$usr_query->bindParam(":id", $user_id);
$usr_query->execute();

$user = $usr_query->fetch();

$sub = $user["stripe_subscription_id"];
$cus = $user["stripe_customer_id"];

$stripe_plan = $user["stripe_plan"];
				
try {
	
	\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);
	$subscription = \Stripe\Subscription::retrieve($sub);

	if($subscription->status != "active") {
		header("Location: redirect-pay.php?action=renew");
		exit;
	}
	
	$customer = \Stripe\Customer::retrieve($cus);
	
} catch (Exception $e) {
	
	$error_stripe = $e->getMessage();
	
}

$page_title = "Choose Plan";

if(isset($_GET["action"]) && $_GET["action"] == "switching" && isset($_GET["plan_id"]) && isset($_GET["plan"])) {
	
	$new_plan_name = $_GET["plan"];
	$new_plan_id = $_GET["plan_id"];
	
	if(isset($_GET["is_free"])) {
		
		// -- Update the user
		$stmt = $dbh->prepare("	UPDATE user 
								SET 
								first_pay = 1,
								stripe_plan = 'free_plan',
								stripe_subscription_id = NULL,
								stripe_customer_id = NULL,
								plan_id = 4
								WHERE id = :user_id");
								
		$stmt->bindParam(':user_id', $user_id);
		$stmt->execute();
		
		$_SESSION["SUBSCRIPTION_PLAN"] = FREE_PLAN;
		
		// Cancel the actual subscription
		$subscription->cancel();
		
		header("Location: payment-success.php?plan=" . $plan . "&plan_id=" . $plan_id);
		
		exit;
		
	} else {
	
		try {
			
			$new_sub = \Stripe\Subscription::update($sub, [
				'cancel_at_period_end' => false,
				'items' => [
					[
						'id' => $subscription->items->data[0]->id,
						'plan' => $new_plan_name
					],
				],
			]);
					
			$stmt = $dbh->prepare("UPDATE user SET stripe_plan = :plan_id WHERE id = :user_id");
			$stmt->bindParam(':user_id', $user_id);
			$stmt->bindParam(':plan_id', $new_plan_id);
			$stmt->execute();
			
			$_SESSION["SUBSCRIPTION_PLAN"] = $new_plan_id;
		
			header("Location: payment-success.php?action=switch_success");
			
		} catch (Exception $e) {
			
			$error = $e->getMessage();
					
			header("Location: switch-plan.php?action=error_switch");
			exit;
		
		}		
	
	}
	
}

// -- Include the header template
include("templates/headers/index_header.php");

// FREE PLAN = 4
$free_plan = $dbh->prepare("SELECT * FROM plan WHERE id = " . FREE_PLAN);
$free_plan->execute();
$free_plan = $free_plan->fetch();

// PLATINUM PLAN = 3
$platinum_plan = $dbh->prepare("SELECT * FROM plan WHERE id = " . PLATINUM_PLAN);
$platinum_plan->execute();
$platinum_plan = $platinum_plan->fetch();

// GOLD PLAN = 2
$gold_plan = $dbh->prepare("SELECT * FROM plan WHERE id = " . GOLD_PLAN);
$gold_plan->execute();
$gold_plan = $gold_plan->fetch();

// SILVER PLAN = 1
$silver_plan = $dbh->prepare("SELECT * FROM plan WHERE id = " . SILVER_PLAN);
$silver_plan->execute();
$silver_plan = $silver_plan->fetch();
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
                                <h1 class="text-gray-900">Choose Subscription Plan</h1>
								
								<div class="row row_sign_up">
									<div class="col-md-8 offset-md-2 d-flex align-items-center">
										
										<?php
										if(isset($_GET["action"])) {
										?>
											
											<?php
											if($_GET["action"] == "need_choose_plan") {
											?>
											<div class="alert alert-danger alert-center">
												<b>Oops.</b> You need to choose a plan to start using our platform.
											</div>
											<?php
											}	
											?>
											
										<?php
										}	
										?>
										
									</div>
									
								</div>
								
								<div class="row pricing">
									<?php
									if(!$hide_silver) {	
									?>
								    <div class="col-lg-6">
								        <div class="card mb-5 mb-lg-0">
								            <div class="card-body">
								                <h5 class="card-title text-muted text-uppercase text-center">Silver</h5>
								                <h6 class="card-price text-center">$5.49<span class="period">/month</span></h6>
								                <hr>
								                <ul class="fa-ul">
									                <li><span class="fa-li"><i class="fas fa-check"></i></span><b><?php echo $silver_plan["images"]; ?></b> Images</li>
								                    <li><span class="fa-li"><i class="fas fa-check"></i></span><b><?php echo $silver_plan["diskspace"]; ?></b>mb Storage</li>
								                    <li><span class="fa-li"><i class="fas fa-check"></i></span><b><?php echo $silver_plan["bandwidth"]; ?></b>mb Bandwidth</li>
								                    <li><span class="fa-li"><i class="fas fa-check"></i></span>Hosting not Included</li>
								                    <li><span class="fa-li"><i class="fas fa-check"></i></span>Image Editor</li>
								                    <li><span class="fa-li"><i class="fas fa-check"></i></span>Social Sharing</li>
								                    <li><span class="fa-li"><i class="fas fa-check"></i></span>No Adverts</li>
								                    <li><span class="fa-li"><i class="fas fa-check"></i></span>Unlimited Albums</li>
								                    <li><span class="fa-li"><i class="fas fa-check"></i></span>Secure Private Album Sharing</li>
								                    <li><span class="fa-li"><i class="fas fa-check"></i></span>Lifetime Storage</li>
								                </ul>
								                <a href="switch-plan.php?action=switching&plan=price_1GzekbI8XlJR7K1GPh8g1FOO&plan_id=<?php echo $silver_plan["id"]; ?>" class="btn btn-block btn-primary text-uppercase">Choose Plan</a>
								            </div>
								        </div>
								    </div>
								    <?php
									}
									?>
									<?php
									if(!$hide_gold) {	
									?>
								    <div class="col-lg-6">
								        <div class="card mb-5 mb-lg-0">
								            <div class="card-body">
								                <h5 class="card-title text-muted text-uppercase text-center">Gold</h5>
								                <h6 class="card-price text-center">$7.49<span class="period">/month</span></h6>
								                <hr>
								                <ul class="fa-ul">
									                <li><span class="fa-li"><i class="fas fa-check"></i></span><b><?php echo $silver_plan["images"]; ?></b> Images</li>
								                    <li><span class="fa-li"><i class="fas fa-check"></i></span><b><?php echo $gold_plan["diskspace"]; ?></b>mb Storage</li>
								                    <li><span class="fa-li"><i class="fas fa-check"></i></span><b><?php echo $gold_plan["bandwidth"]; ?></b>mb Bandwidth</li>
								                    <li><span class="fa-li"><i class="fas fa-check"></i></span>Unlimited Hosting</li>
								                    <li><span class="fa-li"><i class="fas fa-check"></i></span>Image Editor</li>
								                    <li><span class="fa-li"><i class="fas fa-check"></i></span>Social Sharing</li>
								                    <li><span class="fa-li"><i class="fas fa-check"></i></span>No Adverts</li>
								                    <li><span class="fa-li"><i class="fas fa-check"></i></span>Unlimited Albums</li>
								                    <li><span class="fa-li"><i class="fas fa-check"></i></span>Secure Private Album Sharing</li>
								                    <li><span class="fa-li"><i class="fas fa-check"></i></span>Lifetime Storage</li>
								                </ul>
								                <a href="switch-plan.php?action=switching&plan=price_1Gzek2I8XlJR7K1GK6F2HHz0&plan_id=<?php echo $gold_plan["id"]; ?>" class="btn btn-block btn-primary text-uppercase">Choose Plan</a>
								            </div>
								        </div>
								    </div>
								    <?php
									}
									?>
									
									<?php
									if(!$hide_platinum) {	
									?>
								    <div class="col-lg-6">
								        <div class="card mb-5 mb-lg-0">
								            <div class="card-body">
								                <h5 class="card-title text-muted text-uppercase text-center">Platinum</h5>
								                <h6 class="card-price text-center">$12.59<span class="period">/month</span></h6>
								                <hr>
								                <ul class="fa-ul">
									                <li><span class="fa-li"><i class="fas fa-check"></i></span><b>Unlimited</b> Images</li>
								                    <li><span class="fa-li"><i class="fas fa-check"></i></span><b><?php echo $platinum_plan["diskspace"]; ?></b>mb Storage</li>
								                    <li><span class="fa-li"><i class="fas fa-check"></i></span><b><?php echo $platinum_plan["bandwidth"]; ?></b>mb Bandwidth</li>
								                    <li><span class="fa-li"><i class="fas fa-check"></i></span>Unlimited Hosting</li>
								                    <li><span class="fa-li"><i class="fas fa-check"></i></span>Image Editor</li>
								                    <li><span class="fa-li"><i class="fas fa-check"></i></span>Social Sharing</li>
								                    <li><span class="fa-li"><i class="fas fa-check"></i></span>No Adverts</li>
								                    <li><span class="fa-li"><i class="fas fa-check"></i></span>Unlimited Albums</li>
								                    <li><span class="fa-li"><i class="fas fa-check"></i></span>Secure Private Album Sharing</li>
								                    <li><span class="fa-li"><i class="fas fa-check"></i></span>Lifetime Storage</li>
								                </ul>
								                <a href="switch-plan.php?action=switching&plan=price_1GzeeUI8XlJR7K1Gy8JVMvAn&plan_id=<?php echo $platinum_plan["id"]; ?>" class="btn btn-block btn-primary text-uppercase">Choose Plan</a>
								            </div>
								        </div>
								    </div>
								    <?php
									}
									?>
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
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

if(isset($_GET["hide_free"])) {
	$hide_free = true;
} else {
	$hide_free = false;
}

$hide_free = true;

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

$page_title = "Choose Plan";

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
    <div class="col-xl-12 col-lg-12 col-md-12">
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
								
								<?php
								if(!$hide_free) {
								?>
								<div class="row pricing pricing-horizontal">
								    <!-- Free Tier -->
								    <div class="col-lg-8 offset-lg-2">
								        <div class="card mb-5 mb-lg-0">
								            <div class="card-body">
								                <h5 class="card-title text-muted text-uppercase text-center">Basic 30 Day Trial</h5>
								                <h6 class="card-price text-center">FREE</span></h6>
								                <hr>
								                <ul class="fa-ul">
								                    <li><span class="fa-li"><i class="fas fa-check"></i></span><b><?php echo $free_plan["diskspace"]; ?>mb</b> Storage</li>
													<li><span class="fa-li"><i class="fas fa-check"></i></span><b><?php echo $free_plan["bandwidth"]; ?>mb</b> Bandwidth</li>
								                    <li class="text-muted"><span class="fa-li"><i class="fas fa-times"></i></span>Password Protected Folders</li>
								                    <li class="text-muted"><span class="fa-li"><i class="fas fa-times"></i></span>Create Folders and Subfolders</li>
								                    <li class="text-muted"><span class="fa-li"><i class="fas fa-times"></i></span>Advanced Sharing Features</li>
								                </ul>
								                <a href="redirect-pay.php?plan=free&plan_id=<?php echo $free_plan["id"]; ?>" class="btn btn-block btn-primary text-uppercase">Choose Plan</a>
								            </div>
								        </div>
								    </div>
								</div>
								<?php
								}
								?>
								<div class="row pricing">
								    <div class="col-lg-4">
								        <div class="card mb-5 mb-lg-0">
								            <div class="card-body">
								                <h5 class="card-title text-muted text-uppercase text-center">Silver</h5>
								                <h6 class="card-price text-center">$5.49<span class="period">/month</span></h6>
								                <hr>
								                <h4 class="free_trial">30 Days Free Trial</h4>
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
								                <a href="redirect-pay.php?plan=price_1GzekbI8XlJR7K1GPh8g1FOO&plan_id=<?php echo $silver_plan["id"]; ?>" class="btn btn-block btn-primary text-uppercase">Choose Plan</a>
								            </div>
								        </div>
								    </div>
								    <div class="col-lg-4">
								        <div class="card mb-5 mb-lg-0">
								            <div class="card-body">
								                <h5 class="card-title text-muted text-uppercase text-center">Gold</h5>
								                <h6 class="card-price text-center">$7.49<span class="period">/month</span></h6>
								                <hr>
								                <h4 class="free_trial">30 Days Free Trial</h4>
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
								                <a href="redirect-pay.php?plan=price_1Gzek2I8XlJR7K1GK6F2HHz0&plan_id=<?php echo $gold_plan["id"]; ?>" class="btn btn-block btn-primary text-uppercase">Choose Plan</a>
								            </div>
								        </div>
								    </div>
								    <div class="col-lg-4">
								        <div class="card mb-5 mb-lg-0">
								            <div class="card-body">
								                <h5 class="card-title text-muted text-uppercase text-center">Platinum</h5>
								                <h6 class="card-price text-center">$12.59<span class="period">/month</span></h6>
								                <hr />
								                <h4 class="free_trial">30 Days Free Trial</h4>
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
								                <a href="redirect-pay.php?plan=price_1GzeeUI8XlJR7K1Gy8JVMvAn&plan_id=<?php echo $platinum_plan["id"]; ?>" class="btn btn-block btn-primary text-uppercase">Choose Plan</a>
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
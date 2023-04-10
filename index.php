<?php
include("templates/headers/inc.php");
include("templates/headers/calculate_usage.php");

// Include the JS file
$js_files = array("js/cropper.min.js", "js/dropzone.js", "js/pages/index.js");
$css_files = array("css/dropzone.css", "css/uploading.css", "css/cropper.min.css");

// Metadata informations of this page
$page_slug	= "index";

// Redirect if we have a user that is has not chosen his plan...
if($_SESSION) {
	
	// Check the user and update his session...
	$user_id = $_SESSION["USER_ID"];
	
	$get_user_query = $dbh->prepare("SELECT stripe_plan, stripe_subscription_id, stripe_customer_id, show_social_share, show_direct_link, show_forum_code, show_html_code FROM user WHERE id = :user_id AND first_pay = 1");
	$get_user_query->bindParam(":user_id", $user_id);
	$get_user_query->execute();
	
	if($get_user_query->rowCount() == 0) {
		header("Location: switch-plan.php?action=need_choose_plan");
		exit;
	} else {
		header("Location: dashboard.php");
		exit;
	}
}

// Get website config
$site_config = $dbh->prepare("SELECT * FROM config WHERE config_name IN 
							('website_logo','website_name','website_tagline','ads_code','analytics_code','allow_button','allow_drag',
							'allow_webcam','max_upload_size','max_files_upload','auto_deletion','auto_deletion_days','auto_deletion_last_date')");
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
$max_upload_size = $config_array["max_upload_size"];
$max_files_upload = $config_array["max_files_upload"];
$auto_deletion = $config_array["auto_deletion"];
$auto_deletion_days = intval($config_array["auto_deletion_days"]);
$auto_deletion_last_date = $config_array["auto_deletion_last_date"];
$website_logo = $config_array["website_logo"];

// -- Manage auto-deletion part
if($auto_deletion == 1) {
	$today_date = date("Y-m-d");
	
	// Did we already deleted the photos today? If not, let's do it...
	if($auto_deletion_last_date == "" || $today_date != $auto_deletion_last_date) {
		// Get the photos that are older than the defined deletion days...
		$photos_older = $dbh->prepare("SELECT id, url FROM photo WHERE created_at <= (CURRENT_DATE() - INTERVAL $auto_deletion_days DAY)");
		$photos_older->execute();
		
		while($photo_old = $photos_older->fetch(PDO::FETCH_ASSOC)) {
			$photo_old_id = $photo_old["id"];
			$photo_old_url = $photo_old["url"];
						
			// Delete the photo from the database
			$photos_older_deletion = $dbh->prepare("DELETE FROM photo WHERE id = :photo_id");
			$photos_older_deletion->bindParam(":photo_id", $photo_old_id);
			$photos_older_deletion->execute();
			
			// Delete the photo from the disk 
			unlink($photo_old_url);
		}
				
		$site_config = $dbh->prepare("UPDATE config SET config_value = :config_value WHERE config_name = 'auto_deletion_last_date'");
		$site_config->bindParam(":config_value", $today_date);
		$site_config->execute();
	}
}

$page_title = "";

// -- Include the header template
include("templates/headers/index_header.php");

// Get the infos for each packages
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

<div class="row">
	<div class="col-12 homepage-pricings">
		<section class="header_section">
			<img src="img/section_home1.jpg" />
		</section>
		<section class="content_section section-home-1">
			<div class="row">
				<div class="col-md-12">
					<div class="homepage-pricing">
						<div class="row pricing">
						    <div class="col-md-12 pricing-switcher-container">
							    <div class="cd-pricing-switcher">
							      <p class="fieldset">
							         <input type="radio" name="duration" value="monthly" id="monthly" checked>
							         <label for="monthly" class="monthly">Monthly</label>
							         <input type="radio" name="duration" value="yearly" id="yearly">
							         <label for="yearly" class="yearly">Yearly</label>
							         <span class="cd-switch"></span>
							      </p>
							   </div> <!-- .cd-pricing-switcher -->
						    </div>
						    <div class="col-lg-4">
						        <div class="card mb-5 mb-lg-0">
						            <div class="card-body">
							            <div class="pricing-header">
							                <div class="monthly_prices">
							                	<h6 class="card-price text-center"><strike>$<?php echo number_format($silver_plan["monthly_price"], 2); ?></strike> $<?php echo number_format($silver_plan["monthly_price"]/2, 2); ?><span class="period">/month</span></h6>
												<div class="or_per_year">or <h6 class="card-price text-center"><strike>$<?php echo number_format($silver_plan["yearly_price"], 2); ?></strike> $<?php echo number_format($silver_plan["yearly_price"]/2, 2); ?><span class="period">/year</span></h6></div>
							                </div>
							                <div class="yearly_prices">
							                	<h6 class="card-price text-center"><h6 class="card-price text-center"><strike>$<?php echo number_format($silver_plan["yearly_price"]/12, 2); ?></strike> $<?php echo number_format(($silver_plan["yearly_price"]/2)/12, 2); ?><span class="period">/month</span></h6></h6>
												<div class="or_per_year">or <strike>$<?php echo number_format($silver_plan["yearly_price"], 2); ?></strike> $<?php echo number_format($silver_plan["yearly_price"]/2, 2); ?><span class="period">/year</span></div>
							                </div>
							            </div>
						                <h5 class="card-title text-muted text-uppercase text-center">Silver<br><b><?php echo $silver_plan["images"]; ?></b> Images (<b><?php echo $silver_plan["diskspace"]/1000; ?>GB</b>)</h5>
						                <h4 class="percent_off">50% OFF</h4>
						                <hr>
						                <ul class="fa-ul">
						                    <li><span class="fa-li"><i class="fas fa-check"></i></span>Hosting not Included</li>
						                    <li><span class="fa-li"><i class="fas fa-check"></i></span>Image Editor</li>
						                    <li><span class="fa-li"><i class="fas fa-check"></i></span>Social Sharing</li>
						                    <li><span class="fa-li"><i class="fas fa-check"></i></span>No Adverts</li>
						                    <li><span class="fa-li"><i class="fas fa-check"></i></span>Unlimited Albums</li>
						                    <li><span class="fa-li"><i class="fas fa-check"></i></span>Secure Private Album Sharing</li>
						                    <li><span class="fa-li"><i class="fas fa-check"></i></span>Lifetime Storage</li>
						                </ul>
						                <div class="monthly_prices">
						                	<a href="sign-up.php?plan=silver_monthly" class="btn btn-block btn-primary text-uppercase">Sign Up<br><small>30 days free trial</small></a>
						                </div>
						                <div class="yearly_prices">
						                	<a href="sign-up.php?plan=silver_yearly" class="btn btn-block btn-primary text-uppercase">Sign Up<br><small>30 days free trial</small></a>
						                </div>
						            </div>
						        </div>
						    </div>
						    <div class="col-lg-4">
						        <div class="card mb-5 mb-lg-0">
						            <div class="card-body">
							            <div class="pricing-header">
							                <div class="monthly_prices">
							                	<h6 class="card-price text-center"><strike>$<?php echo number_format($gold_plan["monthly_price"], 2); ?></strike> $<?php echo number_format($gold_plan["monthly_price"]/2, 2); ?><span class="period">/month</span></h6>
							                	<div class="or_per_year">or <h6 class="card-price text-center"><strike>$<?php echo number_format($gold_plan["yearly_price"], 2); ?></strike> $<?php echo number_format($gold_plan["yearly_price"]/2, 2); ?><span class="period">/year</span></h6></div>
							                </div>
							                <div class="yearly_prices">
								                <h6 class="card-price text-center"><h6 class="card-price text-center"><strike>$<?php echo number_format($gold_plan["yearly_price"]/12, 2); ?></strike> $<?php echo number_format(($gold_plan["yearly_price"]/2)/12, 2); ?><span class="period">/month</span></h6></h6>
												<div class="or_per_year">or <strike>$<?php echo number_format($silver_plan["yearly_price"], 2); ?></strike> $<?php echo number_format($gold_plan["yearly_price"]/2, 2); ?><span class="period">/year</span></div>
							               	</div>
							            </div>
						                <h5 class="card-title text-muted text-uppercase text-center">Gold<br><b><?php echo $gold_plan["images"]; ?></b> Images (<b><?php echo $gold_plan["diskspace"]/1000; ?>GB)</b></h5>
						                <h4 class="percent_off">50% OFF</h4>
						                <hr>
						                <ul class="fa-ul">
						                    <li><span class="fa-li"><i class="fas fa-check"></i></span>Unlimited Hosting</li>
						                    <li><span class="fa-li"><i class="fas fa-check"></i></span>Image Editor</li>
						                    <li><span class="fa-li"><i class="fas fa-check"></i></span>Social Sharing</li>
						                    <li><span class="fa-li"><i class="fas fa-check"></i></span>No Adverts</li>
						                    <li><span class="fa-li"><i class="fas fa-check"></i></span>Unlimited Albums</li>
						                    <li><span class="fa-li"><i class="fas fa-check"></i></span>Secure Private Album Sharing</li>
						                    <li><span class="fa-li"><i class="fas fa-check"></i></span>Lifetime Storage</li>
						                </ul>
						                <div class="monthly_prices">
						                	<a href="sign-up.php?plan=gold_monthly" class="btn btn-block btn-primary text-uppercase">Sign Up<br><small>30 days free trial</small></a>
						                </div>
						                <div class="yearly_prices">
						                	<a href="sign-up.php?plan=gold_yearly" class="btn btn-block btn-primary text-uppercase">Sign Up<br><small>30 days free trial</small></a>
						                </div>
						            </div>
						        </div>
						    </div>
						    <div class="col-lg-4">
						        <div class="card mb-5 mb-lg-0">
						            <div class="card-body">
							            <div class="pricing-header">
								            <div class="monthly_prices">
							                	<h6 class="card-price text-center"><strike>$<?php echo number_format($platinum_plan["monthly_price"], 2); ?></strike> $<?php echo number_format($platinum_plan["monthly_price"]/2, 2); ?><span class="period">/month</span></h6>
												<div class="or_per_year">or <h6 class="card-price text-center"><strike>$<?php echo number_format($platinum_plan["yearly_price"], 2); ?></strike> $<?php echo number_format($platinum_plan["yearly_price"]/2, 2); ?><span class="period">/year</span></h6></div>
							                </div>
							                <div class="yearly_prices">
												<h6 class="card-price text-center"><h6 class="card-price text-center"><strike>$<?php echo number_format($platinum_plan["yearly_price"]/12, 2); ?></strike> $<?php echo number_format(($platinum_plan["yearly_price"]/2)/12, 2); ?><span class="period">/month</span></h6></h6>
												<div class="or_per_year">or <strike>$<?php echo number_format($platinum_plan["yearly_price"], 2); ?></strike> $<?php echo number_format($platinum_plan["yearly_price"]/2, 2); ?><span class="period">/year</span></div>
							                </div>
							            </div>
						                <h5 class="card-title text-muted text-uppercase text-center">Platinum<br><b>Unlimited</b> Images</h5>
						                <h4 class="percent_off">50% OFF</h4>
						                <hr>
						                <ul class="fa-ul">
						                    <li><span class="fa-li"><i class="fas fa-check"></i></span>Unlimited Hosting</li>
						                    <li><span class="fa-li"><i class="fas fa-check"></i></span>Image Editor</li>
						                    <li><span class="fa-li"><i class="fas fa-check"></i></span>Social Sharing</li>
						                    <li><span class="fa-li"><i class="fas fa-check"></i></span>No Adverts</li>
						                    <li><span class="fa-li"><i class="fas fa-check"></i></span>Unlimited Albums</li>
						                    <li><span class="fa-li"><i class="fas fa-check"></i></span>Secure Private Album Sharing</li>
						                    <li><span class="fa-li"><i class="fas fa-check"></i></span>Lifetime Storage</li>
						                </ul>
						                <div class="monthly_prices">
						                	<a href="sign-up.php?plan=platinum_monthly" class="btn btn-block btn-primary text-uppercase">Sign Up<br><small>30 days free trial</small></a>
						                </div>
						                <div class="yearly_prices">
						                	<a href="sign-up.php?plan=platinum_yearly" class="btn btn-block btn-primary text-uppercase">Sign Up<br><small>30 days free trial</small></a>
						                </div>
						            </div>
						        </div>
						    </div>
						</div>
					</div>
					
				</div>
			</div>
		</section>
		<section class="slideshow_section">
			<div id="home_slideshow" class="carousel slide" data-ride="carousel">
				<div class="carousel-inner">
					<div class="carousel-item active">
						<a href="content.php#sec1"><img src="img/slide3.jpg" class="d-block w-100" alt=""></a>
					</div>
					<div class="carousel-item">
						<a href="content.php#sec1"><img src="img/slide4.jpg" class="d-block w-100" alt=""></a>
					</div>
					<div class="carousel-item">
						<a href="content.php#sec1"><img src="img/slide5.jpg" class="d-block w-100" alt=""></a>
					</div>
					<div class="carousel-item">
						<a href="content.php#sec1"><img src="img/slide6.jpg" class="d-block w-100" alt=""></a>
					</div>
				</div>
				<a class="carousel-control-prev" href="#home_slideshow" role="button" data-slide="prev">
					<span class="carousel-control-prev-icon" aria-hidden="true"></span>
					<span class="sr-only">Previous</span>
				</a>
				<a class="carousel-control-next" href="#home_slideshow" role="button" data-slide="next">
					<span class="carousel-control-next-icon" aria-hidden="true"></span>
					<span class="sr-only">Next</span>
				</a>
			</div>
		</section>
		<section class="header_section">
			<a href="content.php#sec3"><img src="img/bot2_2.jpg" /></a>
		</section>
		<section class="header_section">
			<a href="content.php#sec4"><img src="img/bot2.jpg" /></a>
		</section>
		<section class="header_section">
			<a href="content.php#sec5"><img src="img/bot3.jpg" /></a>
		</section>
		<section class="header_section">
			<a href="content.php#sec5"><img src="img/bot4.jpg" /></a>
		</section>
	</div>
</div>

<script type="text/javascript">
	var max_upload_size = '<?php echo $max_upload_size; ?>';	
	var max_files_upload = '<?php echo $max_files_upload; ?>';
</script>

<?php
	// -- Include the footer template
	include("templates/footers/global_footer.php");	
?>
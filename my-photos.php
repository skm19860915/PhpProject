<?php
include("templates/headers/inc.php");

if(!$_SESSION) {
	
	header("Location: index.php");
	exit;
	
}

// Include the JS file
$js_files = array("js/bootbox.all.min.js", "js/masonry.pkgd.min.js", "js/imagesloaded.pkgd.min.js", "js/pages/my_photos.js");
$css_files = array();

// Metadata informations of this page
$page_slug	= "my_photos";

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

$page_title = "My Photos";

// Check the user and update his session...
$user_id = $_SESSION["USER_ID"];

$get_user_query = $dbh->prepare("SELECT stripe_plan, stripe_subscription_id, stripe_customer_id FROM user WHERE id = :user_id AND first_pay = 1");
$get_user_query->bindParam(":user_id", $user_id);
$get_user_query->execute();

if($get_user_query->rowCount() == 0) {
	
	header("Location: choose-plan.php?action=need_choose_plan");
	exit;
	
} else {
	
	header("Location: dashboard.php");
	exit;
	
}


$username = $_SESSION["USERNAME"];

// -- Include the header template
include("templates/headers/index_header.php");
?>

<!-- Outer Row -->
<div class="row justify-content-center main_uploader_block">
    <div class="col-xl-10 col-lg-12 col-md-9">
	    <?php
		if(isset($_GET["multiple_uploaded"])) {
		?>
		<div class="row" style="margin-top: 40px;">
			<div class="col-md-12">
				<div class="alert alert-success alert-center" style="margin-bottom: 0;">
					<b>Awesome</b>. Your photos have been successfully uploaded !
				</div>
			</div>
		</div>
		<?php
		}	
		?>
        <div class="card o-hidden border-0 shadow-lg my-5">
            <div class="card-body p-0">
                <!-- Nested Row within Card Body -->
                <div class="row">
                	<div class="col-lg-12">
                        <div class="text-center upload-container">
	                        
                        	<div class="p-5 p-no-bottom">
	                            <h1 class="text-gray-900">My Photos</h1>
								
								<div class="row row_my_photos">
										
									<div class="col-md-12">								
										<?php
										if(isset($_GET["action"])) {
											
											$action = $_GET["action"];
											
											if($action == "sign_in_imported") {
											?>
											<div class="alert alert-success alert-center">
												<b>Welcome back <?php echo ucfirst($username); ?>.</b><br />Some photos you uploaded while not logged in have been automatically imported to your account.
											</div>
											<?php
											} else if($action == "sign_in") {
											?>
											<div class="alert alert-success alert-center">
												<b>Welcome back <?php echo ucfirst($username); ?>.</b><br />You are now logged in and can manage your photos from this page.
											</div>
											<?php
											} else if($action == "photo_deleted") {
											?>
											<div class="alert alert-success alert-center">
												Your photo has been deleted.
											</div>
											<?php
											} else if($action == "sign_up_imported") {
											?>
											<div class="alert alert-success alert-center">
												<b>Welcome <?php echo ucfirst($username); ?>.</b><br />Some photos you uploaded while not logged in have been automatically imported to your account.
											</div>
											<?php
											}
											
										} else {
										?>
										<div class="alert alert-info alert-center">
											You can manage, delete or update your photos from this page.
										</div>
										<?php
										}
										?>	
									</div>
									
								</div>
                        	</div>
							
							
							<?php
							// Get all the photos of this user
							$photos_query = $dbh->prepare("SELECT * FROM photo WHERE user_id = :user_id ORDER BY id DESC LIMIT 10");
							$photos_query->bindParam(":user_id", $user_id);
							$photos_query->execute();	
							?>
							
							<div class="grid-container">
								<div class="row grid">
									
									<?php
									if($photos_query->rowCount() == 0) {
									?>
									<div class="col-md-12">
										<div class="alert alert-danger alert-center">
											You have not uploaded any photo for the moment.
										</div>
									</div>
									<?php
									}
									
									while($photo = $photos_query->fetch(PDO::FETCH_ASSOC)) {
									?>
									
										
									<div class="col-md-4 card-photo" data-id="<?php echo $photo["id"]; ?>">
										
										<div class="card shadow">
									
											<div class="card-photo-container">
												
												<a href="photo.php?id=<?php echo $photo["unique_id"]; ?>">
												<img src="<?php echo URL; ?>/<?php echo $photo["url"]; ?>" class="card-img-top" alt="">					
												</a>					
											
											</div>
											
											<div class="card-body">
												<h5 class="card-title"><?php echo $photo["title"]; ?></h5>
											    <div class="row no-gutters">
												    <div class="col-md-6">
													    <a href="photo.php?id=<?php echo $photo["unique_id"]; ?>" class="btn btn-primary btn-block"><i class="fas fa-pencil-alt"></i> View / Edit</a>
												    </div>
												    
												    <div class="col-md-6">
													    <a href="#" class="btn btn-danger btn-block btn-delete"><i class="fas fa-times"></i> Delete</a>
												    </div>
											    </div>
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

<script type="text/javascript">

var url = "<?php echo URL; ?>";
	
</script>

<?php
// -- Include the footer template
include("templates/footers/global_footer.php");	
?>
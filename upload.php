<?php
include("templates/headers/inc.php");
include("templates/headers/check_user_subscription.php");
include("templates/headers/calculate_usage.php");

// Include the JS file
$js_files = array("js/cropper.min.js", "js/dropzone.js?v=2", "js/jquery.toast.min.js", "js/pages/upload.js");
$css_files = array("css/dropzone.css?v=2", "css/uploading.css", "css/jquery.toast.min.css", "css/cropper.min.css");

// Metadata informations of this page
$page_slug	= "upload";

if(!$_SESSION) {
	
	header("Location: index.php");
	exit;
	
}

// Check the user and update his session...
$user_id = $_SESSION["USER_ID"];

$get_user_query = $dbh->prepare("SELECT stripe_plan, stripe_subscription_id, stripe_customer_id FROM user WHERE id = :user_id");
$get_user_query->bindParam(":user_id", $user_id);
$get_user_query->execute();

if($get_user_query->rowCount() == 0) {
	
	header("Location: index.php?action=need_choose_plan");
	exit;
	
}

// Get website config
$site_config = $dbh->prepare("SELECT * FROM config WHERE config_name IN ('website_logo','website_name','website_tagline','ads_code','analytics_code','allow_button','allow_drag','allow_webcam','max_upload_size','max_files_upload','auto_deletion','auto_deletion_days','auto_deletion_last_date')");
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

$page_title = $website_name . " - " . $website_tagline;

// -- Include the header template
include("templates/headers/index_header.php");
?>

<!-- Outer Row -->
<div class="row justify-content-center main_uploader_block">
    <div class="col-xl-12 col-lg-12 col-md-12">
	    
        
        <?php
		if(isset($_GET["action"])) {
			
			$action = $_GET["action"];
			
			if($action == "photo_deleted") {
			?>
			<div class="alert alert-success alert-center alert-action-index">
				Your file has been deleted.
			</div>
			<?php
			}
			
		}
		?>
		
		
		<div id="preview-template">
		    <div class="dz-preview dz-preview-multiple m-0 d-flex flex-column">
		        <div class="media align-items-center mb-3 pb-3 border-bottom btn-reveal-trigger">
		            <img class="dz-image" src="img/file_2.png" alt="..." data-dz-thumbnail />
		            <div class="media-body d-flex flex-between-center">
		                <div>
		                    <h6 data-dz-name></h6>
		                    <div class="d-flex align-items-center">
		                        <p class="mb-0 fs--1 text-400 line-height-1" data-dz-size></p>
		                        <div class="dz-progress"><span class="dz-upload" data-dz-uploadprogress=""></span></div>
		                         <div class="dz-success-mark"><span><i class="fas fa-check-circle"></i></span></div>
								 <div class="dz-error-mark"><span><i class="fas fa-exclamation-circle"></i></span></div>
								 <div class="dz-error-message"><span data-dz-errormessage></span></div>
		               
		                    </div>
		                </div>
		                
		            </div>
		        </div>
		    </div>
		</div>

		
        <div class="card card-dashboard o-hidden border-0 shadow-lg my-5">
            <div class="card-body p-0">
                <!-- Nested Row within Card Body -->
                <div class="row">
                    <div class="col-lg-12">
	                    <form <?php if($allow_drag == 0) { ?>style="display:none !important;";<?php } ?> action="ajax/upload_file.php" class="dropzone" method="POST" enctype="multipart/form-data">
	
	                        <div class="p-5">
	
	                            <div class="text-center upload-container">
		                            <div class="alert alert-danger alert-error-upload">
		                            </div>
	                                <h1 class="text-gray-900">Upload Files</h1>
	                                
	                                <?php
		                            if($limit_nb_files_reached == 1 || $limit_diskspace_reached == 1) {
			                        ?>
										<div class="usage_reached_title">Your usage limits have been reached!</div>
										<div class="usage_reached_img"><img src="img/danger.png" /></div>
										<p>
											<?php
											if($limit_diskspace_reached == 1) {
											?>
											<p>
												You have used all of the diskspace allowed in your subscription plan.<br>If you want to continue to upload new files, <a href="switch-plan.php">please upgrade your account</a> or delete some files.
											</p>
											<?php
											} else {
											?>
											<p>
												You have reached the maximum of files allowed in your subscription plan.<br>If you want to continue to upload new files, <a href="switch-plan.php">please upgrade your account</a> or delete some files.
											</p>
											<?php
											}
											?>
										</p>
			                        <?php
		                            } else {
			                        ?>
			                       
		                                <p>
		                                    Upload your Files & Share Them
		                                </p>
			                            
			                            
		                                <a <?php if($allow_button == 0) { ?>style="display:none";<?php } ?> href="" class="btn btn-primary btn-upload"><i class="fas fa-upload"></i> Pick File(s)</a>
		                                
		                                <?php
			                           	if($allow_button == 1 && ($allow_drag == 1 || $allow_webcam == 1)) {  
										?>
		                                <div class="or_separator">
		                                    OR
		                                </div>
		                                <?php
			                            }
			                            ?>
			                            
			                            <div class="drop-file-here">Drop Files Here</div>
		                                
			                            <input type="hidden" value="<?php if(isset($_GET["path"])): ?><?php echo htmlspecialchars($_GET["path"]); ?><?php else: ?>/<?php endif; ?>" name="upload_path" />
		                            	
		                            	<?php
			                           	if($allow_drag == 1 && $allow_webcam == 1) {  
										?>
										<div class="or_separator">
		                                    OR
		                                </div>
		                                <?php
			                           	} 
			                            ?>
		                            	<div class="webcam_capture_container">
			                                
			                                <video id="video-webcam" width="640" height="480" autoplay></video>
			                                <a <?php if($allow_webcam == 0) { ?>style="display:none";<?php } ?> href="" class="btn btn-primary btn-webcam"><i class="fas fa-camera"></i> Webcam</a>
			                                
		                            	</div>
	                            	
									<?php
		                            }	
		                            ?>
	                            </div>
	                            
	                            <div class="text-center uploading-container">
		                            <h1 class="text-gray-900"><span>.</span><span>.</span><span>.</span>Uploading<span>.</span><span>.</span><span>.</span></h1>
		                            
		                            <div class="cssload-spin-box"></div>
		                            <div class="progress progress-upload">
										<div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100" style="width: 0%"></div>
									</div>
	                            </div>
	                            
	                            <div class="text-center cropping-container">
		                            <h1 class="text-gray-900">Crop it</h1>
		                            <div class="alert alert-success">
			                            <b>Awesome!</b> Your photo has been uploaded, you can now crop it if you want.
		                            </div>
		                            
		                            <div class="cropper-block">
			                            <img src="" class="crop-upload-img" id="crop-upload-img" /> 
		                            </div>
		                            
		                            <a href="" class="btn btn-primary btn-crop"><i class="fas fa-crop"></i> Crop</a><br>
		                            <a href="" class="btn btn-default btn-skip-crop">Skip</a>
	                            </div>

                        	</div>
                            
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
var max_upload_size = '<?php echo $max_upload_size; ?>';	
var max_files_upload = '<?php echo $max_files_upload; ?>';
var limit_nb_files_reached = '<?php echo $limit_nb_files_reached; ?>';
var limit_diskspace_reached = '<?php echo $limit_diskspace_reached; ?>';
</script>

<?php
// -- Include the footer template
include("templates/footers/global_footer.php");	
?>
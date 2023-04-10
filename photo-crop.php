<?php
include("templates/headers/inc.php");
include("templates/headers/calculate_usage.php");

// Include the JS file
$js_files = array("js/cropper.min.js", "js/bootbox.all.min.js", "js/clipboard.min.js", "js/jssocials.min.js", "js/jquery.toast.min.js", "js/jquery.loading.min.js", "photo_editor/scripts.min.js", "js/pages/photo_crop.js");
$css_files = array("css/jssocials.css", "css/cropper.min.css", "css/jquery.toast.min.css", "css/jssocials-theme-flat.css", "photo_editor/styles.min.css");

// Metadata informations of this page
$page_slug	= "photo";

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

$album_title = "";

$page_title = $website_name . " - " . $website_tagline;

// Get the photo by ID
if(!isset($_GET["id"]) && !isset($_GET["s"])) {
	header("Location: 404.php?action=404");
	exit;
}

if(isset($_GET["id"])) {
	$photo_unique_id = $_GET["id"];
	
	$photo_query = $dbh->prepare("SELECT id, url, folder_path, user_id, is_picture, short_id, title, in_community  FROM file WHERE unique_id = :photo_unique_id");
	$photo_query->bindParam(":photo_unique_id", $photo_unique_id);
	$photo_query->execute();
} else if(isset($_GET["s"])) {
	$photo_short_id = $_GET["s"];
	
	$photo_query = $dbh->prepare("SELECT id, url, folder_path, user_id, is_picture, short_id, title, in_community FROM file WHERE short_id = :photo_short_id");
	$photo_query->bindParam(":photo_short_id", $photo_short_id);
	$photo_query->execute();
}

$nb_res_photo = $photo_query->rowCount();	

if($nb_res_photo == 0) {
	header("Location: 404.php");
	exit;	
}

$photo = $photo_query->fetch();
$photo_folder_path = $photo["folder_path"];

$file_user_id = $photo["user_id"];

// Get folder infos
// Get the album name
$get_album_name_query = $dbh->prepare("SELECT id, title, is_protected, password FROM album WHERE user_id = :user_id AND path = :path");
$get_album_name_query->bindParam(":user_id", $file_user_id);
$get_album_name_query->bindParam(":path", $photo_folder_path);
$get_album_name_query->execute();

if($get_album_name_query->rowCount() > 0) {
	
	$get_album_name = $get_album_name_query->fetch();
	$album_title = htmlspecialchars($get_album_name["title"]);
	$is_protected = $get_album_name["is_protected"];
	$album_password = $get_album_name["password"];
	$album_id = $get_album_name["id"];
	
} 
// We are probably on the dashboard album
else {
	
	$get_album_home_name_query = $dbh->prepare("SELECT id, title, is_protected, password FROM album_home WHERE user_id = :user_id");
	$get_album_home_name_query->bindParam(":user_id", $file_user_id);
	$get_album_home_name_query->execute();
	
	$get_album_name = $get_album_home_name_query->fetch();
	$is_protected = $get_album_name["is_protected"];
	$album_password = $get_album_name["password"];
	$album_id = $get_album_name["id"];
	
}


$is_picture = $photo["is_picture"];
$is_owner = false;

// Check if the current visitor owns this photo
if($_SESSION) {
	
	if($_SESSION["USER_ID"] == $photo["user_id"]) {
		
		$is_owner = true;
		
	}
	
} else if(isset($_COOKIE["MY_PHOTOS"])) {
	
	$my_photo_array = json_decode($_COOKIE["MY_PHOTOS"]);
			
	if(in_array($photo["id"], $my_photo_array)) {
		
		$is_owner = true;
		
	}
	
}

$photo_short_id = $photo["short_id"];

$actual_photo = getimagesize($photo["url"]);
$actual_width = $actual_photo[0];
$actual_height = $actual_photo[1];

// -- Include the header template
include("templates/headers/index_header.php");
?>

<!-- Outer Row -->
<div class="row justify-content-center main_uploader_block">
    <div class="col-xl-12 col-lg-12 col-md-12">

        <?php
        if($album_title == "") {
            $album_title = "Home";
        }  
        ?>
        <a href="dashboard.php?path=<?php echo $photo_folder_path; ?>" class="btn btn-primary btn-sm btn-back-album"><i class="fas fa-arrow-left"></i> Back to <b><?php echo $album_title; ?></b></a>

        <div class="alert alert-danger alert-error-upload">
        </div>
        <?php
        if(isset($_GET["action"])) {
            
            $action = $_GET["action"];
            
            if($action == "uploaded") {
            ?>
            <div class="alert alert-success">
                <b>Awesome!</b> Your can now share your photo where you want.
            </div>
            <?php
            }
            
        } 
		?>
		
        <div class="card file-page card-editor o-hidden border-0 shadow-lg my-5">
            <div class="card-body  p-0">
                <!-- Nested Row within Card Body -->
                <div class="row">
                    <div class="col-lg-12">
                        <div class="p-5">
                            <div class="text-center upload-container">
								
								<div class="pixie_container">
									<div class="loading-pixie">
										<h4>Loading RadTriads Editor</h4>
										<i class="fas fa-spinner fa-pulse"></i>
									</div>
									<div class="pixie_main_container">
										<pixie-editor></pixie-editor>
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

<!-- Modal -->
<div class="modal fade" id="resize-modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLongTitle">Resize Modal</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form>
	                <div class="row">
				        <div class="col-md-5">
				            <div class="input-group input-group-sm">
				                <span class="input-group-prepend">
									<label class="input-group-text" for="data-width">Width</label>
								</span>
				                <input type="number" class="form-control data-width" id="data-width" placeholder="width" value="<?php echo $actual_width; ?>">
				                <span class="input-group-append">
									<span class="input-group-text">px</span>
				                </span>
				            </div>
				        </div>
				        <div class="col-md-2">
				            <div class="bg-grey x-resize">x</div>
				        </div>
				        <div class="col-md-5">
				            <div class="input-group input-group-sm">
				                <span class="input-group-prepend">
									<label class="input-group-text" for="data-height">Height</label>
								</span>
				                <input type="number" class="form-control data-height" id="data-height" placeholder="height" value="<?php echo $actual_height; ?>">
				                <span class="input-group-append">
									<span class="input-group-text">px</span>
				                </span>
				            </div>
				        </div>
				    </div>
				    <hr />
				    <div class="row">
					    <div class="col-md-12">
                       	 	<div class="form-control save_as_new_container">
	                        	<input type="checkbox" class="keep_ratio" /> Keep ratio
	                        </div>
					    </div>
				    </div>
                </form>
            </div>
            <div class="modal-footer">
	            <div style="width: 100%">
		            <div class="row">
			            <div class="col-md-12">
							<div class="alert alert-info text-center alert-copy-files"></div>
			            </div>
		            </div>
		            
		            <div class="row">
			            <div class="col-md-12">
							<div class="text-center">
								<button type="submit" class="btn btn-primary btn-resize-ok"><i class="fas fa-check"></i> <span class="action-text">Resize</span></button>
							</div>
			            </div>
		            </div>
	            </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">

	var uploaded_mime_type = "<?php echo mime_content_type($photo["url"]); ?>";
	var url = "<?php echo URL; ?>";
	var photo_unique_id = "<?php echo $photo_unique_id; ?>";
	var photo_url = "<?php echo $photo["url"]; ?>?r=<?php echo mt_rand(0,9999999) ?>";
	var photo_short_id = "<?php echo $photo_short_id; ?>";
	var initialWidth = "<?php echo $actual_width; ?>";
    var initialHeight = "<?php echo $actual_height; ?>";
    
    <?php
	if(isset($_GET["action"])) {
	?>
	var get_action = "<?php echo htmlspecialchars($_GET["action"]) ?>";
	<?php
	} else {
	?>
	var get_action = "";
	<?php
	}
	?>
	
</script>

<?php
// -- Include the footer template
include("templates/footers/global_footer.php");	
?>
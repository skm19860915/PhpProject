<?php
include("templates/headers/inc.php");
include("includes/dirtree.class.php");

$is_owner = false;
$can_access_protected = false;
$error_password = "";

// Include the JS file
$js_files = array("js/jquery-ui.min.js", "js/bootbox.all.min.js", "js/jstree.min.js", "js/bootbox.all.min.js", "js/clipboard.min.js", "js/jssocials.min.js", "js/jquery.toast.min.js", "js/tooltipster.bundle.min.js", "js/jquery.waypoints.min.js", "js/pages/album.js");
$css_files = array("css/jquery-ui.min.css", "css/jssocials.css", "css/jssocials-theme-flat.css", "css/tooltipster.bundle.min.css", "css/plugins/tooltipster/sideTip/themes/tooltipster-sideTip-light.min.css", "css/jquery.toast.min.css", "css/jstree-themes/proton/style.css");

// Metadata informations of this page
$page_slug	= "album";

if(!isset($_GET["owner"])) {
	header("Location: index.php?error=no_owner");
	exit;
}

$cookie_name = "ALBUM_ACCESS_IDS";


// Check the user and update his session...
$user_id = intval($_GET["owner"]);

$get_user_query = $dbh->prepare("SELECT stripe_plan, unique_id, stripe_subscription_id, stripe_customer_id, show_social_share, show_direct_link, show_forum_code, show_html_code, username FROM user WHERE id = :user_id");
$get_user_query->bindParam(":user_id", $user_id);
$get_user_query->execute();

if($get_user_query->rowCount() == 0) {
		
	header("Location: index.php?error=owner_not_found");
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

$page_title = "Dashboard";

$username = "";
$email = "";
$error = "";

$user = $get_user_query->fetch();


$user_unique_id = $user["unique_id"];
	
$show_html_code = $user["show_html_code"];
$show_direct_link = $user["show_direct_link"];
$show_forum_code = $user["show_forum_code"];
$show_social_share = $user["show_social_share"];

$basepath    = 'uploads/' . $user_unique_id;
$search_path = $basepath;

$album_title = $user["username"];
$album_id = 0;
$is_dashboard = 1;

if(isset($_GET["path"])) {
	
	$param_path = htmlspecialchars($_GET["path"]);
	
	if (strpos($param_path, '//') !== false) {
		$param_path = str_replace("//", "/", $param_path);
	}
	
} else {
	
	$param_path = $basepath . "/";
	
}

$param_path = "uploads/$user_unique_id". "" .$param_path . "/";

// We are on the home dashboard
$get_album_home_name_query = $dbh->prepare("SELECT id, title, is_protected, password FROM album_home WHERE user_id = :user_id");
$get_album_home_name_query->bindParam(":user_id", $user_id);
$get_album_home_name_query->execute();

// We create it when it doesn't exist
if($get_album_home_name_query->rowCount() == 0) {
	
	$get_album_home_name_query = $dbh->prepare("INSERT INTO album_home SET is_protected = 1, user_id = :user_id");
	$get_album_home_name_query->bindParam(":user_id", $user_id);
	$get_album_home_name_query->execute();
	
	$is_protected = 1;
	$album_password = "";
	$album_title = "Home";
	$album_home_title = "Home";
	
} else {
	
	$get_album_home_name = $get_album_home_name_query->fetch();
	$is_protected = $get_album_home_name["is_protected"];
	$album_password = $get_album_home_name["password"];
	$album_title = $get_album_home_name["title"];
	$album_home_title = $get_album_home_name["title"];
	
}


// Get the album name
$get_album_name_query = $dbh->prepare("SELECT id, title, is_protected, password FROM album WHERE user_id = :user_id AND path = :path");
$get_album_name_query->bindParam(":user_id", $user_id);
$get_album_name_query->bindParam(":path", $param_path);
$get_album_name_query->execute();

if($get_album_name_query->rowCount() > 0) {
	
	$get_album_name = $get_album_name_query->fetch();
	$album_title = htmlspecialchars($get_album_name["title"]);
	$album_id = intval($get_album_name["id"]);
	$is_dashboard = 0;
	$is_protected = $get_album_name["is_protected"];
	$album_password = $get_album_name["password"];
	
}

$user_directory = array_diff(scan_dir($search_path), array('.', '..'));

// Check with the cookie
if(isset($_COOKIE[$cookie_name]) && $is_protected == 2) {
	
	$is_protected_cookie = unserialize($_COOKIE[$cookie_name]);
	
	if(in_array($album_id, $is_protected_cookie)) {
		$can_access_protected = true;
	}
	
}

if($_POST && isset($_POST["password_form"])) {
	
	$password = $_POST["password"];
	
	if($password != $album_password) {
		$error_password = "Oops. The password is incorrect";
		$can_access_protected = false;
	} else {
		
		if(!isset($_COOKIE[$cookie_name])) {
			
			$cookie_access_array = array();
			
		} else {
			
			$cookie_access_array = unserialize($_COOKIE[$cookie_name]);
			
		}
		
		$cookie_access_array[] = $album_id;
		
		setcookie($cookie_name, serialize($cookie_access_array), time() + (86400 * 30 * 24), "/");
		$can_access_protected = true;
	}
	
}

// -- Include the header template
include("templates/headers/index_header.php");
?>

<?php
if($is_protected == 1 && !$is_owner) {
?>
<!-- Outer Row -->
<div class="row justify-content-center main_uploader_block">
    <div class="col-xl-12 col-lg-12 col-md-12">
        <div class="card file-page o-hidden border-0 shadow-lg my-5">
            <div class="card-body p-0">
	            <div class="row">
		            <div class="col-md-12">
			            <div class="file-container-header">
				            <div class="alert alert-info text-center">
					            <h3>Not Allowed</h3>
					            <p>
						            This is a private album. You are now allowed to view it.
					            </p>
				            </div>
			            </div>
		            </div>
	            </div>
            </div>
        </div>
    </div>
</div>
<?php
} else if($is_protected == 2 && !$is_owner && !$can_access_protected) {
?>
<!-- Outer Row -->
<div class="row justify-content-center main_uploader_block">
    <div class="col-xl-12 col-lg-12 col-md-12">
        <div class="card file-page o-hidden border-0 shadow-lg my-5">
            <div class="card-body p-0">
	            <div class="row">
		            <div class="col-md-12">
			            <div class="file-container-header">
				            <div class="alert alert-info text-center">
					            <h3>Password Protected</h3>
					            <p>
						            This album is password protected. If you want to access it, please enter the password below.
					            </p>
					            <?php
						        if($error_password != "") {
							    ?>
							    <span class='txt_error'><?php echo $error_password; ?></span>
							    <?php
						        }  
						        ?>
					            <div class="row">
						            <div class="col-md-6 offset-md-3">
							            <form action="" method="post">
								            <input type="hidden" name="password_form" value="OK" />
								           	<div class="form-group">
									           	<input type="password" class="form-control" name="password" placeholder="Enter the password..." />
								           	</div> 
								           	<button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Access Album</button>
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
} else {
?>
<!-- Outer Row -->
<div class="row justify-content-center main_uploader_block">
    <div class="col-lg-8">
        <div class="card card-dashboard border-0 shadow-lg my-5">
            <div class="card-body p-0">
                <!-- Nested Row within Card Body -->
                <div class="row">
                    <div class="col-lg-12">
                        <div class="p-4">
                            <div class="text-center upload-container">                                
                
                                
                                <div class="album_title">
                                
                                	<h4>
	                                	<span class="album_name" style="display: block;"><?php echo $album_title; ?></span> 
	                               	</h4>
                                	
                                </div>
								

	                            <div class="my_files_container">
		                            <?php
									
									if(sizeof($user_directory) == 0) {
									?>
	                                <div class="row">
		                                <div class="col-md-12">
			                                <div class="alert alert-info alert-center">
				                                No files uploaded for the moment.
			                                </div>
		                                </div>
	                                </div>
									<?php
									} else {
									?>
									<div class="row row_files">
									<?php
										$nb_files = 0;
																												
																								
										// Let's get the infos about this file!
										$get_file_infos = $dbh->prepare("  SELECT 
				                        								   f.id AS file_id, 
				                        								   f.short_id, 
				                        								   f.updated_at,
				                        								   f.title, 
				                        								   f.unique_id,
				                        								   f.url,
				                        								   f.thumb_url,
				                        								   f.ext,
				                        								   f.diskspace,
				                        								   f.created_at,
				                        								   f.is_picture,
				                        								   f.status
				                        								   FROM file f
				                        								   WHERE 
				                        								   f.user_id = :user_id
				                        								   AND 
				                        								   f.folder_path = :folder_path
				                        								   AND 
				                        								   f.is_deleted = 0
				                        								   ORDER BY
				                        								   created_at DESC
				                        									");
				                        
				                        $get_file_infos->bindParam(":user_id", $user_id);
				                        $get_file_infos->bindParam(":folder_path", $param_path);
				                        $get_file_infos->execute();
				                        						                        
				                        if($get_file_infos->rowCount() > 0) {
					                        
					                        while($file_infos = $get_file_infos->fetch(PDO::FETCH_ASSOC)) {
					                        
						                        $nb_files++;
						                        
						                        $file_uploaded_date = date("d/m/Y H:i", strtotime($file_infos["created_at"]));
						                        $file_type = strtoupper($file_infos["ext"]);
						                        $file_size = $file_infos["diskspace"] / 1000;
						                        $is_picture = $file_infos["is_picture"];
						                        $file_url = $file_infos["url"];
						                        $thumb_url = $file_infos["thumb_url"];
						                        $file_id = $file_infos["file_id"];
						                        $file_unique_id = $file_infos["unique_id"];
						                        $file_timestamp = strtotime($file_infos["created_at"]);
						                        $filename = $file_infos["title"];
											?>
											<div class="col-md-3 file_col_container" data-id="<?php echo $file_id; ?>" data-name="<?php echo $filename; ?>" data-timestamp="<?php echo $file_timestamp; ?>">
												<div class="file_container card" data-id="<?php echo $file_id; ?>" data-url="file.php?id=<?php echo $file_unique_id; ?>">
													<div class="card-body">
										
														<?php
														if($is_picture == 1 || $file_type == "GIF") {
														?>
														<div class="crop_btn_container">
															<a href="download.php?id=<?php echo $file_unique_id; ?>" class="btn-download btn btn-primary btn-sm"><i class="fas fa-download"></i></a>
															<!--<a href="photo-crop.php?id=<?php echo $file_unique_id; ?>" class="btn-crop btn btn-danger btn-sm"><i class="fas fa-crop"></i></a>-->
														</div>
														<div class="is_picture_container">
															<a href="file.php?id=<?php echo $file_unique_id; ?>">
																<?php
																if($thumb_url != "") {
																	$p_url = URL . "/" . $thumb_url;
																} else {
																	$p_url = URL . "/" . $file_url;
																}
																?>
																<img class="lazy" data-src="<?php echo $p_url; ?>?v=<?php echo strtotime($file_infos["updated_at"]); ?>" />
															</a>
														</div>
														<?php
														} else { 
														?>
														<div class="crop_btn_container">
															<a href="download.php?id=<?php echo $file_unique_id; ?>" class="btn-download btn btn-primary btn-sm"><i class="fas fa-download"></i></a>
														</div>
														<div class="is_file_container f_container d-flex align-items-center">
															<a href="file.php?id=<?php echo $file_unique_id; ?>"><?php echo $file_type; ?></a>
															<?php
															if($file_type == "MOV" || $file_type == "FLV" || $file_type == "MP4" || $file_type == "WEBM") {
																
																if($file_type == "MOV") {
																	$file_type_player = "MP4";
																} else {
																	$file_type_player = $file_type;
																}
															?>
															<div class="video_play_btn">
																<a href="#myVideo" class="btn btn-primary btn-video-play"  data-type="video/<?php echo strtolower($file_type_player); ?>" data-url="<?php echo $file_url; ?>"><i class="fas fa-play"></i> Play Video</a>
															</div>
															<?php
															}
															?>
														</div>
														<?php
														}	
														?>
														<div class="file_filename">
															<?php echo $filename; ?>
														</div>
														<div class="file_infos">
															Uploaded : <?php echo $file_uploaded_date; ?>
															<br />
															<?php echo $file_type; ?> | <?php echo $file_size; ?>kb
														</div>
	
														<?php
														if($show_direct_link != 0 || $show_forum_code != 0 || $show_html_code != 0 || $show_social_share != 0) {	
														?>
														<div class="file_url_lst">
															<?php
															if($show_direct_link == 1) {	
															?>
															<div class="form-group">
																<label>Direct URL</label>
																<div class="input-group input-group-copy-link-small">
																	<input type="text"  class="form-control" id="direct_link_<?php echo $file_unique_id; ?>" value="<?php echo URL ."/". $file_url; ?>" />
																	<div class="input-group-append">
																		<button class="btn btn-primary btn-copy" data-clipboard-target="#direct_link_<?php echo $file_unique_id; ?>" type="button" id="button-addon2">Copy</button>
																	</div>
																</div>
															</div>
															<?php
															}
															?>
															
															<?php
															if($show_html_code == 1) {	
															?>
															<div class="form-group">
																<label>HTML Link</label>
																<div class="input-group input-group-copy-link-small">
																	<input type="text" class="form-control" id="html_link_<?php echo $file_unique_id; ?>" value="<a href='<?php echo URL; ?>/file.php?id=<?php echo $file_unique_id; ?>'><img src='<?php echo STACKPATH_URL ."/". $file_url; ?>' /></a>" />
																	<div class="input-group-append">
																		<button class="btn btn-primary btn-copy" data-clipboard-target="#html_link_<?php echo $file_unique_id; ?>" type="button" id="button-addon2">Copy</button>
																	</div>
																</div>
															</div>
															<?php
															}
															?>
															
															<?php
															if($show_forum_code == 1) { 	
															?>
															<div class="form-group">
																<label>IMG Link</label>
																<div class="input-group input-group-copy-link-small">
																	<input type="text" class="form-control" id="img_link_<?php echo $file_unique_id; ?>" value="[IMG]<?php echo URL ."/". $file_url; ?>[/IMG]" />
																	<div class="input-group-append">
																		<button class="btn btn-primary btn-copy" data-clipboard-target="#img_link_<?php echo $file_unique_id; ?>" type="button" id="button-addon2">Copy</button>
																	</div>
																</div>
															</div>
															<?php
															}
															?>
															
															<?php
															if($show_social_share == 1) { 	
															?>
															<div class="form-group">
																<div class="shareIcons"></div>
															</div>
															<?php
															}
															?>
														</div>
														<?php
														}
														?>
													</div>
												</div>
											</div>
										<?php
											} // endwhile
											
										} else {
										?>
											
										<div class="col-md-12"><div class="alert alert-info text-center">No files in this directory for the moment...</div></div>
										
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
        </div>
    </div>
</div>
<?php
}
?>


<script type="text/javascript">

<?php
if(isset($_GET["path"])) {
?>
var get_path = "<?php echo str_replace("&amp;", "%26", htmlspecialchars($_GET["path"])); ?>";
<?php
} else {
?>
var get_path = "";
<?php
}
?>

var user_default_path = "<?php echo $basepath; ?>/";

var photo_unique_id = "";
var url = "<?php echo URL; ?>";

var is_dashboard = <?php echo $is_dashboard; ?>;
var album_title = "<?php echo ucfirst($album_title); ?>";	
var album_id = "<?php echo $album_id; ?>";
var album_home_title = "<?php echo $album_home_title; ?>";
	
</script>
<?php
// -- Include the footer template
include("templates/footers/global_footer.php");	
?>
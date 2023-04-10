<?php
include("templates/headers/inc.php");
include("templates/headers/check_user_subscription.php");
include("templates/headers/calculate_usage.php");

// Include the JS file
$js_files = array("js/bootbox.all.min.js", "js/clipboard.min.js", "js/jssocials.min.js", "js/pages/file.js");
$css_files = array("css/jssocials.css", "css/jssocials-theme-flat.css");

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

$page_title = $website_name . " - " . $website_tagline;

$photo_short_id = "";
$photo_unique_id = "";

// Get the photo by ID
if(!isset($_GET["id"]) && !isset($_GET["s"])) {
	header("Location: 404.php?action=404");
	exit;
}

if(isset($_GET["id"])) {
	$photo_unique_id = $_GET["id"];
	
	$photo_query = $dbh->prepare("SELECT id, updated_at, url, unique_id, user_id, is_picture, short_id, title, in_community, folder_path, ext FROM file WHERE unique_id = :photo_unique_id");
	$photo_query->bindParam(":photo_unique_id", $photo_unique_id);
	$photo_query->execute();
} else if(isset($_GET["s"])) {
	$photo_short_id = $_GET["s"];
	
	$photo_query = $dbh->prepare("SELECT id, updated_at, url, unique_id, user_id, is_picture, short_id, title, in_community, folder_path, ext FROM file WHERE short_id = :photo_short_id");
	$photo_query->bindParam(":photo_short_id", $photo_short_id);
	$photo_query->execute();
}

$nb_res_photo = $photo_query->rowCount();	

if($nb_res_photo == 0) {
	header("Location: 404.php");
	exit;	
}

$photo = $photo_query->fetch();
$current_photo_id = $photo["id"];
$photo_unique_id = $photo["unique_id"];
$photo_folder_path = $photo["folder_path"];
$file_type = strtoupper($photo["ext"]);

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

// Select the other photos of this album
$get_other_files_infos = $dbh->prepare("  SELECT 
										   f.id AS file_id, 
										   f.short_id, 
										   f.updated_at,
										   f.title, 
										   f.unique_id,
										   f.url,
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

$get_other_files_infos->bindParam(":user_id", $user_id);
$get_other_files_infos->bindParam(":folder_path", $photo_folder_path);
$get_other_files_infos->execute();

// Select the other photos of this album
$random_other_files_infos = $dbh->prepare(" SELECT 
											f.id AS file_id, 
											f.short_id, 
											f.updated_at,
											f.title, 
											f.thumb_url,
											f.unique_id,
											f.url,
											f.ext,
											f.diskspace,
											f.created_at,
											f.is_picture,
											f.status
											FROM file f
											WHERE 
											f.folder_path = :folder_path
											AND 
											f.is_deleted = 0
											AND
											f.id <= :current_photo_id
											ORDER BY
											created_at DESC LIMIT 250
											");

$random_other_files_infos->bindParam(":folder_path", $photo_folder_path);
$random_other_files_infos->bindParam(":current_photo_id", $current_photo_id);
$random_other_files_infos->execute();

$prev_file = "";
$next_file = "";
$album_title = "";

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


if($get_other_files_infos->rowCount() > 0) {
	
	$prev_tmp_file_url = "";
	$files_infos = $get_other_files_infos->fetchAll();
	$file_infos_index = 0;
	
	
	foreach($files_infos as $file_info) {
				
		if($file_info["unique_id"] == $photo_unique_id || $file_info["short_id"] == $photo_short_id) {
			
			// Check if we have a previous
			if($file_infos_index > 0) {
				$prev_file = $files_infos[$file_infos_index-1];
			}
			
			$next_file_index = $file_infos_index+1;
			
			// Check if we have a next
			if(isset($files_infos[$next_file_index])) {
				$next_file = $files_infos[$next_file_index];
			}
			
			break;
			
		}
		
		$file_infos_index++;
		
	}
	
	
}

if($_SESSION) {

	// Check the user and update his session...
	$user_id = $_SESSION["USER_ID"];
	
	$get_user_query = $dbh->prepare("SELECT stripe_plan, unique_id, stripe_subscription_id, stripe_customer_id, show_social_share, show_direct_link, show_forum_code, show_html_code FROM user WHERE id = :user_id AND first_pay = 1");
	$get_user_query->bindParam(":user_id", $user_id);
	$get_user_query->execute();
	
	$user = $get_user_query->fetch();
	
	$user_unique_id = $user["unique_id"];
		
	$show_html_code = $user["show_html_code"];
	$show_direct_link = $user["show_direct_link"];
	$show_forum_code = $user["show_forum_code"];
	$show_social_share = $user["show_social_share"];

}
	
$cookie_name = "ALBUM_ACCESS_IDS";

$show_og_meta = 1;
$og_meta_url = URL . "/file.php?id=" . $photo_unique_id;
$og_title = "RadTriads - File #$current_photo_id";
$og_image = URL . "/" . $photo["url"];

// -- Include the header template
include("templates/headers/index_header.php");

$error_password = "";
$can_access_protected = false;

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
						            This file is in a private album. You are now allowed to view it.
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
						            This file is in a password protected album. If you want to access it, please enter the password below.
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
								           	<button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Access File</button>
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
    <div class="col-xl-12 col-lg-12 col-md-12">
        <div class="card file-page o-hidden border-0 shadow-lg my-5">
            <div class="card-body p-0">
	            <div class="row">
		            <div class="col-md-12">
			            <div class="file-container-header">
				            
				            
				            <?php 
					            
					        if($is_owner) { 
					        ?>
		                    <?php
			                if($album_title == "") {
				                $album_title = "Home";
			                }  
			                ?>
				            <a href="dashboard.php?path=<?php echo $photo_folder_path; ?>" class="btn btn-primary btn-sm btn-back-album"><i class="fas fa-arrow-left"></i> Back to <b><?php echo $album_title; ?></b></a>
		                    <div class="alert alert-danger alert-error-upload">
		                    </div>
		                    <h1 class="photo_title text-gray-900"><?php echo $photo["title"]; ?></h1>
	                                
				            <div class="photo_edit_title">
	                            <input data-id="<?php echo $photo["id"]; ?>" value="<?php echo $photo["title"]; ?>" type="text" class="form-control input_photo_title" placeholder="Enter your Photo Title" />
	                            <small><i class="fas fa-info-circle"></i> Type Enter to save your title</small>
	                        </div>
	                        <?php
		                    }  
		                    ?>
	                        <?php
	                        if(isset($_GET["action"])) {
	                            
	                            $action = $_GET["action"];
	                            
	                            if($action == "uploaded") {
		                        ?>
		                        <div class="alert alert-success">
			                        <b>Awesome!</b> Your can now share your photo where you want.
		                        </div>
		                        <?php
	                            } else if($action == "cropped") {
		                            
		                            if(!isset($_GET["upload"])) {
		                        ?>
		                        <div class="alert alert-success">
			                        <b>Awesome!</b> Your photo has been updated.
		                        </div>
		                        <?php
			                        } else {
				                ?>
		                        <div class="alert alert-success">
			                        <b>Awesome!</b> Your photo copy has been created!
		                        </div>
				                <?php
			                        }
	                            }
	                            
	                        } 
	
	                        if($is_owner) {
							?>
							<div class="my_photo_actions">
								<div class="row actions-my-photos">
									<div class="col-md-3 <?php if($is_picture == 0): ?>offset-md-2<?php endif ?>">
										<a href="" class="btn btn-dark btn-share btn-block"><i class="fas fa-share"></i> Share</a>
									</div>
									<?php
									if($is_picture == 1) {	
									?>
									<div class="col-md-3">
										<a href="photo-crop.php?id=<?php echo $photo_unique_id; ?>" class="btn btn-primary btn-block" data-id="<?php echo $photo["id"]; ?>"><i class="fas fa-pencil-alt"></i> Edit</a>
									</div>
									<?php
									}
									?>
									<div class="col-md-3">
										<a href="download.php?id=<?php echo $photo_unique_id; ?>" class="btn btn-primary btn-block" data-id="<?php echo $photo["id"]; ?>"><i class="fas fa-download"></i> Download</a>
									</div>
									<div class="col-md-3">
										<a href="" class="btn btn-danger btn-block btn-delete" data-id="<?php echo $photo["id"]; ?>"><i class="fas fa-times"></i> Delete</a>
									</div>
								</div>
							</div>
							<?php	
							} 
							?>
			            </div>
		            </div>
	            </div>
                <!-- Nested Row within Card Body -->
                <div class="row">
	                <div class="col-lg-2 d-flex align-items-center text-center">
		                <?php
			            if($prev_file == "") {
				        ?>
		                <a href="" class="arrow_nav disabled">
			                <i class="fas fa-chevron-circle-left"></i>
		                </a>
				        <?php
			            } else { 
			            ?>
		                <a href="<?php echo URL ."/file.php?id=" . $prev_file["unique_id"]; ?>" class="arrow_nav">
			                <i class="fas fa-chevron-circle-left"></i>
		                </a>
		                <?php
			            }
			            ?>
	                </div>
                    <div class="col-lg-8">
                        <div class="p-5 pb-2">
                            <div class="text-center upload-container">
								
								<?php
								if($is_picture == 1 || $file_type == "GIF") {
								?>
                                <div class="p_photo_container">
	                                <img src="<?php echo STACKPATH_URL; ?>/<?php echo $photo["url"]; ?>?v=<?php echo strtotime($photo["updated_at"]); ?>" />
                                </div>
                                <?php
	                            } else {
		                        ?>
                                <div class="p_photo_container p_photo_file_container">
	                            	<a style="font-size: 50px; font-weight: bold;" href="file.php?id=<?php echo $file_unique_id; ?>"><?php echo $file_type; ?></a>
                                </div>
		                        <?php
	                            }  
	                            ?>
                            </div>                           
                        </div>
                    </div>
	                <div class="col-lg-2 d-flex align-items-center text-center">
		                <?php
			            if($next_file == "") {
				        ?>
		                <a href="" class="arrow_nav disabled">
			                <i class="fas fa-chevron-circle-right"></i>
		                </a>
				        <?php
			            } else { 
			            ?>
		                <a href="<?php echo URL ."/file.php?id=" . $next_file["unique_id"]; ?>" class="arrow_nav">
			                <i class="fas fa-chevron-circle-right"></i>
		                </a>
		                <?php
			            }
			            ?>
	                </div>
                </div>
                <div class="row">
	                <div class="col-md-12 text-center">
		                <div class="row p_row_actions" id="shareit">
                            
                            <div class="col-md-10 offset-md-1">
	                            
	                            <h3 class="text-gray-900">Other Files of this Album</h3>
	                            
	                            <div class="album-file-carousel">
		                            <div class="row no-gutters">
			                            <?php
				                        if($random_other_files_infos->rowCount() == 0) {
					                    ?>
					                    <div class="alert alert-info col-md-12">
						                    No other files to show in this album...
					                    </div>
					                    <?php
				                        } else {
					                        
					                        while($random_photo = $random_other_files_infos->fetch(PDO::FETCH_ASSOC)) {
						                        
						                        $file_uploaded_date = date("d/m/Y H:i", strtotime($random_photo["created_at"]));
						                        $file_type = strtoupper($random_photo["ext"]);
						                        $file_size = $random_photo["diskspace"] / 1000;
						                        $is_picture = $random_photo["is_picture"];
						                        $file_url = $random_photo["url"];
						                        $file_id = $random_photo["file_id"];
						                        $file_unique_id = $random_photo["unique_id"];
						                        $file_timestamp = strtotime($random_photo["created_at"]);
						                        $filename = $random_photo["title"];
						                        $thumb_url = $random_photo["thumb_url"];
						                    ?>
						                    <div class="col-md-3 file_col_container" data-id="<?php echo $file_id; ?>" data-name="<?php echo $filename; ?>" data-timestamp="<?php echo $file_timestamp; ?>">
												<div class="file_container card" data-id="<?php echo $file_id; ?>" data-url="file.php?id=<?php echo $file_unique_id; ?>">
													<div class="card-body">
														<?php
														if($is_picture == 1 || $file_type == "GIF") {
														?>
														<div class="is_picture_container f_container d-flex align-items-center">
															<a href="file.php?id=<?php echo $file_unique_id; ?>">
																<?php
																if($thumb_url != "") {
																	$p_url = URL . "/" . $thumb_url;
																} else {
																	$p_url = URL . "/" . $file_url;
																}
																?> 
																<img class="lazy" data-src="<?php echo $file_url; ?>?v=<?php echo strtotime($random_photo["updated_at"]); ?>" />
															</a>
														</div>
														<?php
														} else { 
														?>
														
														<div class="is_file_container f_container d-flex align-items-center">
															<a href="file.php?id=<?php echo $file_unique_id; ?>"><?php echo $file_type; ?></a>
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
													</div>
												</div>
											</div>
						                    <?php
					                        }
					                        
				                        }
				                        ?>
		                            </div>
	                            </div>
	                            <hr />
                                
                                <h3 class="text-gray-900">Share it</h3>
                                
                                <h4>Link Type</h4>
                                <select class="form-control link-type">
	                                <?php 
									if($displayed_plan_name != "Silver") {
									?>
	                                <option value="0">Direct Link</option>
	                                <?php
		                            }
		                            ?>
	                                <option value="4">Short Link</option>
	                                <option value="1"><?php echo $website_name; ?> Link</option>
	                                <option value="2">HTML Link</option>
	                                <option value="3">BBCode</option>
                                </select>
                                
                                <div class="input-group input-group-copy-link">
									<textarea id="photo-link" class="form-control" rows="4"><?php echo URL; ?>/<?php echo $photo["url"]; ?></textarea>
									<div class="input-group-append">
										<button class="btn btn-primary btn-copy" data-clipboard-target="#photo-link" type="button" id="button-addon2">Copy</button>
									</div>
								</div>
                                <h4>Social Sharing</h4>
                                <div id="share"></div>
                                
                            </div>
                            <div class="col-md-10 offset-md-1">
                           	 	<hr />
                                
                                <?php
	                            // He is the photo owner but not logged in
	                            if($is_owner && !$_SESSION) {  
	                            ?>
                                <h3 class="text-gray-900">Get more of it</h3>
								
								<div class="alert alert-info">Create an account to get more features</div>
								
								<ul  class="list-group align-left">
									<li class="list-group-item">
										<i class="fas fa-check"></i> List your uploaded photos in one place
									</li>
									<li class="list-group-item">
										<i class="fas fa-check"></i> Delete or edit photos informations
									</li>
									<li class="list-group-item">
										<i class="fas fa-check"></i> Share photos easily
									</li>
									<li class="list-group-item">
										<i class="fas fa-check"></i> Upload unlimited photos
									</li>
								</ul>
								
								<a href="sign-up.php" class="btn btn-primary btn-block btn-photo-create-account"><i class="fas fa-user-plus"></i> Create Account</a>
								
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
<?php
}
?>

<script type="text/javascript">

	var url = "<?php echo URL; ?>";
	var photo_unique_id = "<?php echo $photo_unique_id; ?>";
	var photo_url = "<?php echo $photo["url"]; ?>";
	var photo_short_id = "<?php echo $photo_short_id; ?>";
	var back_url = "dashboard.php?path=<?php echo $photo_folder_path; ?>";
	var stackpath_url = "<?php echo STACKPATH_URL; ?>";
	var sub_plan = "<?php echo $displayed_plan_name; ?>";
	
</script>

<?php
// -- Include the footer template
include("templates/footers/global_footer.php");	
?>
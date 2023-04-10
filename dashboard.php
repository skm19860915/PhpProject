<?php
include("templates/headers/inc.php");
include("includes/dirtree.class.php");
include("templates/headers/check_user_subscription.php");
include("templates/headers/calculate_usage.php");

// Include the JS file
$js_files = array("js/jquery-ui.min.js", "js/bootbox.all.min.js", "js/jstree.min.js", "js/bootbox.all.min.js", "js/clipboard.min.js", "js/jssocials.min.js", "js/jquery.toast.min.js", "js/tooltipster.bundle.min.js", "js/jquery.waypoints.min.js", "js/pages/dashboard.js");
$css_files = array("css/jquery-ui.min.css", "css/jssocials.css", "css/jssocials-theme-flat.css", "css/tooltipster.bundle.min.css", "css/plugins/tooltipster/sideTip/themes/tooltipster-sideTip-light.min.css", "css/jquery.toast.min.css", "css/jstree-themes/proton/style.css");

// Metadata informations of this page
$page_slug	= "dashboard";

if(!$_SESSION) {
	header("Location: index.php");
	exit;
}

// Check the user and update his session...
$user_id = $_SESSION["USER_ID"];

$get_user_query = $dbh->prepare("SELECT stripe_plan, unique_id, stripe_subscription_id, stripe_customer_id, show_social_share, show_direct_link, show_forum_code, show_html_code FROM user WHERE id = :user_id");
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

$album_title = $_SESSION["USERNAME"];
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

// OK the path is the user's one
if(strpos($param_path, $user_unique_id) !== false) {
	// Cleaned path to keep only the folders we want
	$cleaned_path = str_replace($basepath, "", $param_path);
	$cleaned_path = rtrim($cleaned_path, '/');
	$search_path = $basepath . $cleaned_path;

	// Save the path
	$_SESSION["PARAM_PATH"] = $cleaned_path;
} else {
	header("Location: index.php?action=forbidden");
	exit;
}

if(scan_dir($search_path) === false){
	$user_directory = null;	
}
else{
	$user_directory = array_diff(scan_dir($search_path), array('.', '..'));
}

$treeView1 = new TreeView($basepath, ucfirst($album_home_title), 2, $dbh);	
$treeView2 = new TreeView($basepath, ucfirst($album_home_title), 1, $dbh);	
$treeView3 = new TreeView($basepath, ucfirst($album_home_title), 2, $dbh);	

// -- Include the header template
include("templates/headers/index_header.php");
?>

<!-- Outer Row -->
<div class="row justify-content-center main_uploader_block">
	<div class="col-lg-4 sidebar-block">
		<div class="card o-hidden border-0 shadow-lg my-5">
          	<div class="card-title">Albums</div>
            <div class="card-body aside aside-albums">
                <div id="jstree-folders">
	                <?php echo $treeView1->getTree(); ?>
                </div> 
            </div>
            <a href="trash.php" class="btn btn-light btn-block btn-trash"><i class="fas fa-trash"></i> Trash</a>
		</div>
		
		<div class="">
		</div>
	</div>
    <div class="col-lg-8">
        <div class="card card-dashboard border-0 shadow-lg my-5">

			<?php
			if($is_dashboard == 0) {
				$album_name_type = "normal";
			} else {
				$album_name_type = "home";
			}
			?>
			
            <div class="card-body p-0">
                <!-- Nested Row within Card Body -->
                <div class="row">
                    <div class="col-lg-12">
                        <div class="p-4">
                            <div class="text-center upload-container">                                
                                <div class="dashboard-actions clearfix" id="dashboard-actions">
	                                <a href="" class="btn btn-sm btn-light btn-create-album">
		                                <i class="fas fa-folder-plus"></i> Create Album
		                            </a>
	                                <a href="download-album.php?path=<?php echo $param_path; ?>" class="btn btn-sm btn-light btn-download-album disabled">
		                                <i class="fas fa-download"></i> Download Album
		                            </a>
		                            <a href="#" class="btn btn-sm btn-light btn-edit-album-name" data-type="<?php echo $album_name_type; ?>">
		                                <i class="fas fa-pencil-alt"></i> Rename Album
		                            </a>
                                	<?php
	                                if(!$is_dashboard) {	
	                                ?>
                                	<a href="" class="btn btn-light btn-sm btn-delete-album" data-id="<?php echo $album_id; ?>"><i class="fas fa-trash"></i> Delete Album</a>
									<?php
									}
									?>
		                            
		                            <a href="#" class="btn btn-sm btn-light btn-edit-privacy">
		                                <i class="fas fa-user-secret"></i> Edit Privacy
		                            </a>
		                            <a href="#" class="btn btn-sm btn-light btn-share-album">
		                                <i class="fas fa-share"></i> Share Album
		                            </a>
		                            <br>
									
		                            <a href="upload.php<?php if(isset($cleaned_path) && (isset($cleaned_path) && $cleaned_path != "")): ?>?path=<?php echo $cleaned_path; ?><?php endif; ?>" class="btn btn-sm btn-light btn-menu-upload">
		                                <i class="fas fa-upload"></i> Upload
		                            </a>
		                            <a href="" class="btn btn-sm btn-light btn-select-all">
		                                <i class="far fa-square"></i> Select All
		                            </a>
		                            <div class="sort_container">
			                            <div class="btn-group" role="group">
											<button id="btnGroupDrop1" type="button" class="btn btn-sm btn-light dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
											<b>Sort:</b> <span class="sorting_value">Newest to Oldest</span>
											</button>
											<div class="dropdown-menu" aria-labelledby="btnGroupDrop1">
												<a class="dropdown-item btn-change-sort active" href="#" data-sort="date-new"><i class="fas fa-check-square"></i> Newest to Oldest</a>
												<a class="dropdown-item btn-change-sort" href="#" data-sort="date-old"><i class="far fa-square"></i> Oldest to Newest</a>
												<a class="dropdown-item btn-change-sort" href="#" data-sort="A-Z"><i class="far fa-square"></i> A-Z</a>
												<a class="dropdown-item btn-change-sort" href="#" data-sort="Z-A"><i class="far fa-square"></i> Z-A</a>
											</div>
										</div>
		                            </div>
                                </div>
                                
                                <div class="album_title">
                                
                                	<h4>
	                                	<span class="album_name"></span> 
	                                	<input type="text" name="album_name_edt" value="" />
	                                	<i class="fas fa-circle-notch fa-spin"></i> 
	                               	</h4>
                                	
                                </div>
								

	                            <div class="my_files_container">
		                            <?php
										if($user_directory === null || sizeof($user_directory) == 0) {
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
				                        								   f.title, 
				                        								   f.updated_at,
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
				                        								   created_at DESC LIMIT 48
				                        									");
				                        
				                        $get_file_infos->bindParam(":user_id", $user_id);
				                        $get_file_infos->bindParam(":folder_path", $param_path);
				                        $get_file_infos->execute();
				                        						                        
				                        if($get_file_infos->rowCount() > 0) {
					                        
					                        while($file_infos = $get_file_infos->fetch(PDO::FETCH_ASSOC)) {
					                        
						                        $nb_files++;
						                        
						                        $file_uploaded_date = date("d/m/Y H:i", strtotime($file_infos["created_at"]));
						                        $file_type = strtoupper($file_infos["ext"]);
						                        $file_size = $file_infos["diskspace"];
						                        $is_picture = $file_infos["is_picture"];
						                        $file_url = $file_infos["url"];
						                        $thumb_url = $file_infos["thumb_url"];
						                        $file_id = $file_infos["file_id"];
						                        $file_unique_id = $file_infos["unique_id"];
						                        $file_timestamp = strtotime($file_infos["created_at"]);
						                        $filename = $file_infos["title"];
											?>
											<div class="col-md-3 file_col_container" data-type="<?php echo $file_type; ?>" data-id="<?php echo $file_id; ?>" data-name="<?php echo $filename; ?>" data-timestamp="<?php echo $file_timestamp; ?>" data-created-at="<?php echo $file_infos["created_at"]; ?>">
												<div class="file_container card" data-id="<?php echo $file_id; ?>" data-url="file.php?id=<?php echo $file_unique_id; ?>">
													<div class="card-body">
														<div class="file_actions">
															<div class="file_action_check">
																<i class="far fa-square"></i>
															</div>
														</div>
														<?php
														if($is_picture == 1 || $file_type == "GIF") {
														?>
														<div class="crop_btn_container">
															<a href="download.php?id=<?php echo $file_unique_id; ?>" class="btn-download btn btn-primary btn-sm"><i class="fas fa-download"></i></a>
															<!--<a href="photo-crop.php?id=<?php echo $file_unique_id; ?>" class="btn-crop btn btn-danger btn-sm"><i class="fas fa-crop"></i></a>-->
														</div>
														<div class="is_picture_container f_container">
															<a href="file.php?id=<?php echo $file_unique_id; ?>">
																
																<?php
																if($thumb_url != "") {
																	$p_url = STACKPATH_URL . "/" . $thumb_url;
																} else {
																	$p_url = STACKPATH_URL . "/" . $file_url;
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
															if($file_type == "MOV" || $file_type == "FLV" || $file_type == "MP4" || $file_type == "WEBM" || $file_type == "SWF" || $file_type == "OGG") {
																
																if($file_type == "MOV") {
																	$file_type_player = "video/mp4";
																} else if($file_type == "FLV" || $file_type == "SWF") {
																	$file_type_player = "video/x-flv";
																} else {
																	$file_type_player = "video/$file_type";
																}
															?>
															<div class="video_play_btn">
																
																
																<a href="#myVideo" class="btn btn-primary btn-video-play"  data-type="<?php echo strtolower($file_type_player); ?>" data-url="<?php echo $file_url; ?>"><i class="fas fa-play"></i> Play Video</a>
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
															<?php echo $file_type; ?> | <?php echo formatBytes($file_size, 1); ?>
														</div>
	
														<?php
														if($show_direct_link != 0 || $show_forum_code != 0 || $show_html_code != 0 || $show_social_share != 0) {	
														?>
														<div class="file_url_lst">
															<?php
															if($show_direct_link == 1 && $displayed_plan_name != "Silver") {	
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

<!-- Modal -->
<div class="modal fade" id="create-folder-modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
	        
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLongTitle"><span class="action-text">Create Album</span></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">

                <form>
	                
	                <div class="form-group">
		                <label>Album Title</label>
						<input type="text" class="form-control" id="title_album" placeholder="Enter the title of this album">
					</div>
	                <hr />
	                <label>Create Album in <b><span class="album_name"></span></b> or select another album...</label>
	                <div id="jstree2-folders">
		                <?php echo $treeView2->getTree(); ?>
	                </div>
		            <input type="hidden" name="album_path" value="" id="path_album" />
                </form>
            </div>
            <div class="modal-footer">
	            <div style="width: 100%">
		            <div class="row">
			            <div class="col-md-12">
							<div class="alert alert-info text-center alert-create-album"></div>
			            </div>
		            </div>
		            <div class="row">
			            <div class="col-md-12">
							<div class="text-center">
								<button type="submit" class="btn btn-primary btn-create-album-ok btn-sm"><i class="fas fa-check"></i> Create Album</button>
							</div>
			            </div>
		            </div>
	            </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="share-album-modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLongTitle">Share album</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form>
	                
	                <label>Your Album Link</label>
	                
	                <?php
		            if(isset($cleaned_path) && $cleaned_path != "") {
			            $public_album_url = URL . "/album.php?path=$cleaned_path&owner=" . $_SESSION["USER_ID"];  
		            } else {		            	
			            $public_album_url = URL . "/album.php?owner=" . $_SESSION["USER_ID"];  
		            }
		            ?>
	                
	                <div class="input-group input-group-copy-link-small">
						<input type="text"  class="form-control" id="public_album_url" value="<?php echo $public_album_url; ?>" />
						<div class="input-group-append">
							<button class="btn btn-primary btn-copy-album-url" data-clipboard-target="#public_album_url" type="button" id="button-addon2">Copy</button>
						</div>
					</div>
					<small class="info"><i class="fas fa-info-circle"></i> Share this album to who you want to access it. Don't forget to edit your album privacy to make your link public / password protected.</small>

                </form>
            </div>
            
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="rename-album-modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLongTitle">Rename album</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form>	                
	                <input type="text" class="form-control input_edt_album_name" /> 	                
                </form>
            </div>
            <div class="modal-footer">
	            <div style="width: 100%">
		            <div class="row">
			            <div class="col-md-12">
							<div class="alert alert-info text-center alert-rename-album"></div>
			            </div>
		            </div>
		            
		            <div class="row">
			            <div class="col-md-12">
							<div class="text-center">
								<button type="submit" class="btn btn-primary btn-rename-album-ok"><i class="fas fa-check"></i> Rename Album</button>
							</div>
			            </div>
		            </div>
	            </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="edit-privacy-modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLongTitle"><span class="action-text">Edit Album Privacy</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form>
                	<div class="form-group">
	                	<label>Album Privacy</label>
	                	<select class="select_album_privacy form-control">
		                	<option value="1" <?php if($is_protected == 1): ?>selected<?php endif; ?>>Private</option>
		                	<option value="2" <?php if($is_protected == 2): ?>selected<?php endif; ?>>Private with Password</option>
		                	<option value="0" <?php if($is_protected == 0): ?>selected<?php endif; ?>>Public</option>
	                	</select>
	                </div>
	                <div class="form-group from-group-album-password <?php if($is_protected == 2): ?>active<?php endif; ?>">
		                <label>Album Password</label>
	                	<input type="text" class="password_album_privacy form-control" placeholder="Enter your album's password" value="<?php echo $album_password; ?>" />
	                </div>
                </form>
            </div>
            <div class="modal-footer">
	            <div style="width: 100%">
		            <div class="row">
			            <div class="col-md-12">
							<div class="alert alert-info text-center alert-album-privacy"></div>
			            </div>
		            </div>
		            
		            <div class="row">
			            <div class="col-md-12">
							<div class="text-center">
								<button type="submit" class="btn btn-primary btn-update-privacy-ok"><i class="fas fa-check"></i> <span class="action-text">Update Privacy</button>
							</div>
			            </div>
		            </div>
	            </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="copy-files-modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLongTitle"><span class="action-text">Copy</span> files to...</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form>
	                <div id="jstree3-folders">
		                <?php echo $treeView3->getTree(); ?>
	                </div>
		            <input type="hidden" name="path_album_copy" value="" id="path_album_copy" />
	                
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
								<button type="submit" class="btn btn-primary btn-copy-files-ok"><i class="fas fa-check"></i> <span class="action-text">Copy</span> Files</button>
							</div>
			            </div>
		            </div>
	            </div>
            </div>
        </div>
    </div>
</div>

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

var folder_path = "<?php echo $param_path; ?>";

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



<?php
if(isset($_GET["toast"]) && $_GET["toast"] == "download_soon_ready") {
?>
<script>
	$(document).ready(function() {
		
		$.toast({
			text: 'We are preparing your download and it will be sent to your email in 30 minutes max!',
			hideAfter: 10000   // in milli seconds
		});

	});
</script>
<?php
}	
?>
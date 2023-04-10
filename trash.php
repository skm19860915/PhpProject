<?php
include("templates/headers/inc.php");
include("includes/dirtree.class.php");
include("templates/headers/check_user_subscription.php");
include("templates/headers/calculate_usage.php");

// Include the JS file
$js_files = array("js/bootbox.all.min.js", "js/clipboard.min.js", "js/jstree.min.js", "js/bootbox.all.min.js", "js/pages/trash.js");
$css_files = array("css/jstree-themes/proton/style.min.css");

// Metadata informations of this page
$page_slug	= "dashboard";

if(!$_SESSION) {
	
	header("Location: index.php");
	exit;
	
}

// Check the user and update his session...
$user_id = $_SESSION["USER_ID"];

$get_user_query = $dbh->prepare("SELECT stripe_plan, unique_id, stripe_subscription_id, stripe_customer_id FROM user WHERE id = :user_id");
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

$show_html_code = $user["show_html_code"];
$show_direct_link = $user["show_direct_link"];
$show_forum_code = $user["show_forum_code"];
$show_social_share = $user["show_social_share"];

$user_infos = $dbh->prepare("SELECT u.id, u.unique_id, u.stripe_plan, p.diskspace AS max_diskspace, p.bandwidth AS max_bandwidth FROM user u, plan p WHERE u.id = :user_id AND u.plan_id = p.id");
$user_infos->bindParam(":user_id", $user_id);
$user_infos->execute();

$user = $user_infos->fetch();
$max_diskspace = $user["max_diskspace"];
$max_bandwidth = $user["max_bandwidth"];

$user_unique_id = $user["unique_id"];

$basepath    = 'uploads/' . $user_unique_id;
$search_path = $basepath;

$album_title = $_SESSION["USERNAME"];

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

// Get total bandwidth used
$user_usage_used = $dbh->prepare("SELECT 
								  SUM(bandwidth) AS month_bandwidth,
								  SUM(diskspace) AS month_diskspace
								  FROM file 
								  WHERE 
								  user_id = :user_id
								  AND MONTH(created_at) = MONTH(CURRENT_DATE())
								  AND YEAR(created_at) = YEAR(CURRENT_DATE())");
$user_usage_used->bindParam(":user_id", $user_id);
$user_usage_used->execute();
									  
if($user_usage_used->rowCount() > 0) {
	
	$user_usage = $user_usage_used->fetch();
	$user_diskspace = get_mb($user_usage["month_diskspace"], 3);
	$user_bandwidth = get_mb($user_usage["month_bandwidth"], 3);
		
} else {
	
	$user_diskspace = 0;
	$user_bandwidth = 0;
	
}

$user_directory = array_diff(scan_dir($search_path), array('.', '..'));

$treeView1 = new TreeView($basepath, ucfirst($album_home_title), 2, $dbh);	
$treeView2 = new TreeView($basepath, ucfirst($album_home_title), 1, $dbh);
$treeView3 = new TreeView($basepath, ucfirst($album_home_title), 2, $dbh);

// -- Include the header template
include("templates/headers/index_header.php");
?>

<!-- Outer Row -->
<div class="row justify-content-center main_uploader_block">
	
	<div class="col-lg-4  sidebar-block">
		<div class="card o-hidden border-0 shadow-lg my-5">
          	<div class="card-title">Albums</div>
            <div class="card-body aside aside-albums">
                <div id="jstree-folders">
	                <?php echo $treeView1->getTree(); ?>
                </div>
                <hr />
                <a href="trash.php" class="btn btn-light btn-block btn-trash"><i class="fas fa-trash"></i> Trash</a>
            </div>
		</div>
	</div>
    <div class="col-lg-8">
        <div class="card card-dashboard o-hidden border-0 shadow-lg my-5">
            <div class="card-body p-0">
                <!-- Nested Row within Card Body -->
                <div class="row">
                    <div class="col-lg-12">
                        <div class="p-5">
                            <div class="text-center upload-container">                                
                                <div class="dashboard-actions clearfix" id="dashboard-actions">
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
                                	<h4>Trash</h4>
                                </div>
                                
	                            <div class="my_files_container">
		                            <?php
									$nb_files = 0;
									
									if(sizeof($user_directory) == 0) {
									?>
	                                <div class="row">
		                                <div class="col-md-12">
			                                <div class="alert alert-info alert-center">
				                                No files in the trash folder for the moment.
			                                </div>
		                                </div>
	                                </div>
									<?php
									} else {
										
										$get_file_infos = $dbh->prepare("  SELECT 
							            								   f.id AS file_id, 
							            								   f.short_id, 
							            								   f.title, 
							            								   f.unique_id,
							            								   f.url,
							            								   f.thumb_url,
							            								   f.folder_path,
							            								   f.ext,
							            								   f.diskspace,
							            								   f.created_at,
							            								   f.is_picture,
							            								   f.status
							            								   FROM file f
							            								   WHERE 
							            								   f.user_id = :user_id
							            								   AND 
							            								   f.is_deleted = 1
							            								   ORDER BY f.created_at DESC
							            									");
							            
							            $get_file_infos->bindParam(":user_id", $user_id);
							            $get_file_infos->execute();
							            
							            if($get_file_infos->rowCount() == 0) {
								        ?>
		                                <div class="row">
			                                <div class="col-md-12">
				                                <div class="alert alert-info alert-center">
					                                No files in the trash folder for the moment.
				                                </div>
			                                </div>
		                                </div>
								        <?php
										} else {
											?>
											<div class="row row_files">
								            <?php
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
							                    $filename = $file_infos["title"];
							                    $file_timestamp = strtotime($file_infos["created_at"]);
												?>
												<div class="col-md-4 file_col_container" data-name="<?php echo $filename; ?>" data-timestamp="<?php echo $file_timestamp; ?>">
													<div class="file_container card" data-id="<?php echo $file_id; ?>">
														<div class="card-body">
															<div class="file_actions">
																<div class="file_action_check">
																	<i class="far fa-square"></i>
																</div>
															</div>
															<?php
															if($is_picture == 1) {
															?>
															<div class="is_picture_container">
																<a href="file.php?id=<?php echo $file_unique_id; ?>">
																	<?php
																	if($thumb_url != "") {
																		$p_url = STACKPATH_URL . "/" . $thumb_url;
																	} else {
																		$p_url = STACKPATH_URL . "/" . $file_url;
																	}
																	?>
																	<img class="lazy" data-src="<?php echo $p_url; ?>" />
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
															<?php
															if($show_direct_link != 0 || $show_forum_code != 0 || $show_html_code != 0) {	
															?>
															<div class="file_url_lst">
																<?php
																if($show_direct_link == 1) {	
																?>
																<div class="form-group">
																	<label>Direct URL</label>
																	<div class="input-group input-group-copy-link-small">
																		<input type="text"  class="form-control" id="direct_link_<?php echo $file_unique_id; ?>" value="<?php echo STACKPATH_URL ."/". $file_url; ?>" />
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
																		<input type="text" class="form-control" id="img_link_<?php echo $file_unique_id; ?>" value="[IMG]<?php echo STACKPATH_URL ."/". $file_url; ?>[/IMG]" />
																		<div class="input-group-append">
																			<button class="btn btn-primary btn-copy" data-clipboard-target="#img_link_<?php echo $file_unique_id; ?>" type="button" id="button-addon2">Copy</button>
																		</div>
																	</div>
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
									        }
									        ?>
											</div>
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

<!-- Modal -->
<div class="modal fade" id="create-folder-modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLongTitle">Create Album</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form>
	                <label>Create Album in...</label>
	                <div id="jstree2-folders">
		                <?php echo $treeView2->getTree(); ?>
	                </div>
		            <input type="hidden" name="album_path" value="" id="path_album" />
	                <hr />
	                <div class="form-group">
		                <label>Album Title</label>
						<input type="text" class="form-control" id="title_album" placeholder="Enter the title of this album">
					</div>
					<div class="alert alert-info text-center alert-create-album"></div>
					<div class="text-center">
						<button type="submit" class="btn btn-primary btn-create-album-ok"><i class="fas fa-check"></i> Create Album</button>
					</div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="copy-files-modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLongTitle"><span class="action-text">Copy</span></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form>
	                <label><span class="action-text"><span class="action-text">Copy</span> file(s) to...</label>
	                <div id="jstree3-folders">
		                <?php echo $treeView3->getTree(); ?>
	                </div>
		            <input type="hidden" name="path_album_copy" value="" id="path_album_copy" />
	                <hr />
					<div class="alert alert-info text-center alert-copy-files"></div>
					<div class="text-center">
						<button type="submit" class="btn btn-primary btn-copy-files-ok"><i class="fas fa-check"></i> <span class="action-text">Copy</span> Files</button>
					</div>
                </form>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">

<?php
if(isset($_GET["path"])) {
?>
var get_path = "<?php echo htmlspecialchars($_GET["path"]); ?>";
<?php
} else {
?>
var get_path = "";
<?php
}
?>
	
var nb_files = <?php echo $nb_files; ?>;
var album_title = "<?php echo ucfirst($album_title); ?>";	
	
</script>
<?php
// -- Include the footer template
include("templates/footers/global_footer.php");	
?>
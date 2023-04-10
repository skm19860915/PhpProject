<?php
include("templates/headers/inc.php");
include("includes/dirtree.class.php");
include("templates/headers/check_user_subscription.php");
include("templates/headers/calculate_usage.php");

// Include the JS file
$js_files = array("js/bootbox.all.min.js", "js/clipboard.min.js", "js/jstree.min.js", "js/bootbox.all.min.js", "js/pages/recent_uploads.js");
$css_files = array("css/jstree-themes/proton/style.min.css");

// Metadata informations of this page
$page_slug	= "recent_uploads";
$nb_files = 0;

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



// -- Include the header template
include("templates/headers/index_header.php");
?>

<!-- Outer Row -->
<div class="row justify-content-center main_uploader_block page-recent-uploads">
	

    <div class="col-lg-12">
        <div class="card card-dashboard o-hidden border-0 shadow-lg my-5">
            <div class="card-body p-0">
                <!-- Nested Row within Card Body -->
                <div class="row">
                    <div class="col-lg-12">
                        <div class="p-5">
                            <div class="text-center upload-container"> 
                                
                                <div class="album_title">
                                	<h4>My Recent Uploads</h4>
                                </div>
                                
                                
	                            <div class="my_files_container">
                                <?php
	                            $get_file_infos = $dbh->prepare("  SELECT 
					            								   f.id AS file_id, 
					            								   f.short_id, 
					            								   f.title, 
					            								   f.updated_at,
					            								   f.unique_id,
					            								   f.url,
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
					            								   f.is_deleted = 0
					            								   ORDER BY created_at DESC LIMIT 200
					            									");
					            
					            $get_file_infos->bindParam(":user_id", $user_id);
					            $get_file_infos->execute();
					            
					            if($get_file_infos->rowCount() == 0) {
						        ?>
                                <div class="row">
	                                <div class="col-md-12">
		                                <div class="alert alert-info alert-center">
			                                No recent uploads to show for the moment...
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
				                        $file_id = $file_infos["file_id"];
					                    $file_unique_id = $file_infos["unique_id"];
					                    $filename = $file_infos["title"];
					                    $file_timestamp = strtotime($file_infos["created_at"]);
										?>
										<div class="col-md-3 file_col_container" data-name="<?php echo $filename; ?>" data-timestamp="<?php echo $file_timestamp; ?>">
											<div class="file_container card" data-id="<?php echo $file_id; ?>">
												<div class="card-body">
													<?php
													if($is_picture == 1 || $file_type == "GIF") {
													?>
													<div class="is_picture_container">
														<a href="file.php?id=<?php echo $file_unique_id; ?>"><img class="lazy" data-src="<?php echo $file_url; ?>?v=<?php echo strtotime($file_infos["updated_at"]); ?>" /></a>
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
																<input type="text" class="form-control" id="html_link_<?php echo $file_unique_id; ?>" value="<a href='<?php echo URL; ?>/file.php?id=<?php echo $file_unique_id; ?>'><img src='<?php echo URL ."/". $file_url; ?>' /></a>" />
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
	
</script>
<?php
// -- Include the footer template
include("templates/footers/global_footer.php");	
?>
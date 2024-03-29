<?php
include("templates/headers/inc.php");

if(!isset($_GET["id"])) {
	header("Location: admin.php?action=forbidden");
	exit;
}

$user_id = $_GET["id"];

// Include the JS file
$js_files = array("js/bootbox.all.min.js", "js/masonry.pkgd.min.js", "js/imagesloaded.pkgd.min.js", "js/pages/view_user_files.js");
$css_files = array("");

// Metadata informations of this page
$page_slug	= "manage_photos";

// Get website config
$site_config = $dbh->prepare("SELECT * FROM config WHERE config_name IN ('website_name','website_tagline','ads_code','analytics_code','allow_button','allow_drag','allow_webcam')");
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

$page_title = $website_name . " - " . $website_tagline;

if($_SESSION["RANK"] == 0) {
	header("Location: index.php?action=forbidden");
	exit;	
}

$total_users = $dbh->prepare("	SELECT *
								FROM user
								ORDER BY created_at DESC
								");
					
$total_users->execute();

// -- Include the header template
include("templates/headers/admin_header.php");

?>


<!-- Content Row -->
<div class="row">
	
	<div class="col-xl-12">
		<?php
		// Get all the photos
		$photos_query = $dbh->prepare("SELECT * FROM file WHERE user_id = :user_id ORDER BY id DESC LIMIT 30");
		$photos_query->bindParam(":user_id", $user_id);
		$photos_query->execute();
		
		if($photos_query->rowCount() == 0) {
		?>
		<div class="col-md-12">
			<div class="alert alert-danger alert-center">
				No files have been uploaded by this user for the moment.
			</div>
		</div>
		<?php
		}	
		?>
		
		<div class="grid-container">
			<div class="row grid">
				
				
				<?php
				while($photo = $photos_query->fetch(PDO::FETCH_ASSOC)) {
					
					$is_picture = $photo["is_picture"];
				?>
				
					
				<div class="col-md-2 card-photo" data-id="<?php echo $photo["id"]; ?>">
					
					<div class="card shadow">
				
						<div class="card-photo-container">
							
							<a href="file.php?id=<?php echo $photo["unique_id"]; ?>">
							<?php
							if($is_picture == 1) {
							?>
								<img src="<?php echo URL; ?>/<?php echo $photo["url"]; ?>" class="card-img-top" alt="">					
							<?php
							} else {
							?>
								<img class="card-img-top" src="img/file_2.png" />
							<?php
							}
							?>
							</a>					
						
						</div>
						
						<div class="card-body">
							<h5 class="card-title"><?php echo $photo["title"]; ?></h5>
						    <div class="row no-gutters">
							    <div class="col-md-12">
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

<script type="text/javascript">

var url = "<?php echo URL; ?>";
var profile_id = "<?php echo $user_id; ?>";
	
</script>
<?php
// -- Include the footer template
include("templates/footers/admin_footer.php");	
?>
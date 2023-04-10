<?php
include("templates/headers/inc.php");

// Include the JS file
$js_files = array("js/pages/tax_infos.js");

$css_files = array();

// Metadata informations of this page
$page_slug	= "tax_infos";

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

$page_title = "Tax Information";

$username = "";
$email = "";
$error = "";

$user_id = $_SESSION["USER_ID"];

$country = "";
$state = "";
$postal_code = "";
$locality = "";

// Manage Post 
if($_POST) {
	
	if(isset($_POST["state"]) && isset($_POST["country"])) {
		
		$country = $_POST["country"];
		$state = $_POST["state"];
		
		if(empty($country) || empty($state)) {
			
			$error = "Please enter a valid postal address...";
			
		} else {
			
		
			if(isset($_POST["locality"])) {
				
				$locality = $_POST["locality"];
				
			}	
			
			if(isset($_POST["postal_code"])) {
				
				$postal_code = $_POST["postal_code"];
				
			}		
						
			$check_user_sql = $dbh->prepare("UPDATE user SET country = :country, state = :state, locality = :locality, postal_code = :postal_code WHERE id = :user_id");
			$check_user_sql->bindParam(":country", $country);
			$check_user_sql->bindParam(":state", $state);
			$check_user_sql->bindParam(":locality", $locality);
			$check_user_sql->bindParam(":postal_code", $postal_code);
			$check_user_sql->bindParam(":user_id", $user_id);
			$check_user_sql->execute();
								
			header("Location: " . $_COOKIE["TAX_REDIRECT_URL"]);
		
		}
	
	}
	
}

// -- Include the header template
include("templates/headers/index_header.php");
?>

<!-- Outer Row -->
<div class="row justify-content-center main_uploader_block">
    <div class="col-xl-10 col-lg-12 col-md-9">
        <div class="card o-hidden border-0 shadow-lg my-5">
            <div class="card-body p-0">
                <!-- Nested Row within Card Body -->
                <div class="row">
                    <div class="col-lg-12">
                        <div class="p-5">
                            <div class="text-center upload-container">
	                            <div class="alert alert-danger alert-error-upload">
	                            </div>
                                <h1 class="text-gray-900">Your Location</h1>
								
								<div class="row row_sign_up">
																		
									<div class="col-md-8 offset-md-2 d-flex align-items-center">
										<form class="user" action="" method="POST">
											
											<?php
											if($error != "") {
											?>
											<div class="alert alert-danger alert-center">
												
												<?php echo $error; ?>
												
											</div>
											<?php
											} else {
													
											?>
											<div class="alert alert-success alert-center">
												
												Please enter your location below
												
											</div>
											<?php
																									
											}
											?>
											
										    <input type="text" id="autocomplete" name="address" class="form-control" placeholder="Start typing your city name here..." />
										    
										    <input type="hidden" name="state" id="administrative_area_level_1" />
										    <input type="hidden" name="postal_code" id="postal_code" />
										    <input type="hidden" name="country" id="country" />
										    <input type="hidden" name="locality" id="locality" />
										    <input type="hidden" id="street_number" />
										    <input type="hidden" id="route" />
										    
										    <button type="submit" disabled class="btn btn-update-address btn-primary btn-user btn-block" style="margin-top: 10px">
										    Update Location
										    </button>
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
// -- Include the footer template
include("templates/footers/global_footer.php");	
?>
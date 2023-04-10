<?php
include("templates/headers/inc.php");

// Include the JS file
$js_files = array();
$css_files = array();

// Metadata informations of this page
$page_slug	= "404";

// Get website config
$site_config = $dbh->prepare("SELECT * FROM config WHERE config_name IN ('website_logo','website_name','website_tagline','ads_code')");
$site_config->execute();

$config_array = array();

while($config = $site_config->fetch(PDO::FETCH_ASSOC)) {
	$config_array[$config["config_name"]] = $config["config_value"];
}

$website_name = $config_array["website_name"];
$website_logo = $config_array["website_logo"];
$website_tagline = $config_array["website_tagline"];
$ads_code = $config_array["ads_code"];

$page_title = $website_name . " - " . $website_tagline;

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
                                <h1 class="text-gray-900">Oops</h1>
                                <div class="alert alert-danger">
                                	We can't find this info for you on the website :-(
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
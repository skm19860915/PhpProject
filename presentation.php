<?php
include("templates/headers/inc.php");
include("templates/headers/calculate_usage.php");

// Include the JS file
$js_files = array("js/cropper.min.js", "js/dropzone.js", "js/pages/index.js");
$css_files = array("css/dropzone.css", "css/uploading.css", "css/cropper.min.css");

// Metadata informations of this page
$page_slug	= "index";

// Get website config
$site_config = $dbh->prepare("SELECT * FROM config WHERE config_name IN ('website_name','website_tagline','ads_code','allow_button','allow_drag','allow_webcam')");
$site_config->execute();

$config_array = array();

while($config = $site_config->fetch(PDO::FETCH_ASSOC)) {
	$config_array[$config["config_name"]] = $config["config_value"];
}

$website_name = $config_array["website_name"];
$website_tagline = $config_array["website_tagline"];
$ads_code = $config_array["ads_code"];
$allow_button = intval($config_array["allow_button"]);
$allow_drag = intval($config_array["allow_drag"]);
$allow_webcam = intval($config_array["allow_webcam"]);

$page_title = $website_name . " - " . $website_tagline;

// -- Include the header template
include("templates/headers/index_header_pres.php");
?>

<!-- Outer Row -->
<div class="row justify-content-center main_uploader_block">
    <div class="pres-block">
	    
	    <p><b>Upic</b> is an awesome and unique PHP application that allows you to start your own Image Hosting platform in a few minutes.</p>
	    <div class="mc_img">
		    <img src="documentation/img/mockups/mc1.png" />
	    </div>
    </div>
    
    <div class="pres-block pres-block-white" style="padding-bottom: 110px;">
	    <h3>Upload Features</h3>
	    <p>You can upload a photo in Ajax (no page reload) with <b>a simple button</b>, <b>drag&drop</b> or by taking it directly <b>from your webcam</b>!</p>
	    <div class="mc_img">
		    <img src="documentation/img/mockups/mc2.png" />
	    </div>
    </div>
    
    <div class="pres-block" style="z-index: 9997 !important;padding-top: 173px;margin-top: -123px;padding-bottom: 110px;">
	    
	    <h3>User Accounts</h3>
	    <p>On <b>Upic</b> you can let users create their accounts in a just a few minutes so they can manage their photos from one place.</p>
	    <div class="mc_img">
		    <img src="documentation/img/mockups/mc3.png" />
	    </div>
    </div>
    
    <div class="pres-block pres-block-white" style="z-index: 9996 !important;padding-top: 173px;margin-top: -123px;padding-bottom: 110px;">
	    <h3>Monetization</h3>
	    <p>Of course we have thought about monetization and you can show ads by just pasting your code to generate money.</p>
	    <div class="mc_img">
		    <img src="documentation/img/mockups/mc7.png" />
	    </div>
    </div>
    
    <div class="pres-block" style="z-index: 9994 !important;padding-top: 173px;margin-top: -123px;padding-bottom: 110px;">
	    
	    <h3>Easy Sharing</h3>
	    <p>You can share the link of your photo to use it where you want or you can also directly post it to your favorite social networks thanks to our optimized sharing plugins.</p>
	    <div class="mc_img">
		    <img src="documentation/img/mockups/mc4.png" />
	    </div>
    </div>
    
    <div class="pres-block pres-block-white" style="z-index: 9993 !important;padding-top: 173px;margin-top: -123px;padding-bottom: 110px;">
	    <h3>Awesome Admin</h3>
	    <p>We have made a full-featured, beautiful and easy-to-use admin interface to allow you to manage the website in just a few click.</p>
	    <div class="mc_img">
		    <img src="documentation/img/mockups/mc5.png" />
	    </div>
    </div>
        
    <div class="pres-block" style="z-index: 9991 !important;padding-top: 173px;margin-top: -123px;padding-bottom: 110px;">
	    
	    <h3>Ultimate Cropping System</h3>
	    <p>Use the awesome cropping system to allow your users to make the perfect adjustments to their photos.</p>
	    <div class="mc_img">
		    <img src="documentation/img/mockups/mc8.png?v=1" />
	    </div>
    </div>
    
    <div class="pres-block pres-block-white" style="z-index: 9990 !important;padding-top: 173px;margin-top: -123px;padding-bottom:40px;">
	    
	    <h3>And much more!</h3>
	    <p>There is so much more to know about <b>Upic</b>. Discover all these awesome features directly on the demo website!</p>
    </div>
    
</div>

<?php
// -- Include the footer template
include("templates/footers/global_footer.php");	
?>
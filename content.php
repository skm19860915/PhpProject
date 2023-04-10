<?php
include("templates/headers/inc.php");
include("templates/headers/calculate_usage.php");

// Include the JS file
$js_files = array();
$css_files = array();

// Metadata informations of this page
$page_slug	= "";

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


// -- Include the header template
include("templates/headers/index_header.php");
?>

<!-- Outer Row -->
<div class="row justify-content-center main_uploader_block">
    <div class="col-xl-12 col-lg-12 col-md-9">
        <div class="card o-hidden border-0 shadow-lg my-5">
            <div class="card-body p-0">
                <!-- Nested Row within Card Body -->
                <div class="row">
                    <div class="col-lg-12">
                        <div class="p-5">
                            <div class="text-center upload-container">
								
								<div class="row row_sign_up">
																		
									<div class="col-md-12 page-content content-hpage">
										
										<h4 id="sec1" class="content-hpageblock">OUR MISSION STATEMENT</h4>
										
										<p>
											This website has been developed by users who grew tired of substandard, irresponsible service on other hosting sites resulting in access to our content being denied for long periods at crucial times. Never again have your projects and marketplaces interrupted. No adverts, no false promises!
											<br><br>Our support is online 24 hours. Never again experience lack of communication again for a service you pay for.
										</p>
										
										<h4 id="sec2" class="content-hpageblock">LIFETIME MEMORIES KEPT SAFE FOREVER</h4>
										
										<p>
											Store your memories with RadTriads and rest assured that your images are safe & secure with us. Never lose a photo or a video again. Your content is here to stay forever once uploaded. Social media platforms, blogs, websites come and go, personal devices are upgraded regularly, hardware fails, taking your images with them. Your content will always remain with RadTriads. RadTriads will keep your images safe for a lifetime and beyond, from device to device, upgrade after upgrade.
										</p>
										
										<h4 id="sec3" class="content-hpageblock">PRIVACY</h4>
										
										<p>
											You have complete control over the privacy of your images and content. Securely share specific albums with family and loved ones. You choose which albums you share with who, without them being open to the public, changing access anytime you need.
											<br><br>No adverts! 24 Hour support. Daily backups ensure a smooth & consistent service, no matter what! 

										</p>
										
										<h4 id="sec4" class="content-hpageblock">SOCIAL MEDIA</h4>
										
										<p>
											Keep all your original content in one place. Easily share to various social media platforms with just one click. RadTriads is optimised so that you can upload and share right there in the moment.
										</p>
										
										<h4 id="sec5" class="content-hpageblock">3RD PARTY HOSTING & EMBEDDING</h4>
										
										<p>
											No need to upload the same images over and over. Upload once and share or embed to multiple websites, blogs, forums & social media. Its just one click!
											<br><br>Now your website can have both speed and quality images. Images and content hosted with RadTriads are delivered using our high-speed Content Delivery Network without slowing down your website, taking the load off your server.
										</p>
										
										<h4 id="sec6" class="content-hpageblock">IMAGE QUALITY & COMPRESSION</h4>
										
										<p>
											Many hosting sites and websites automatically compress images to save space and maintain load speed degrading the original image. Image size matters with SEO for your website. At RadTriads your images are preserved in their original quality delivering high speed upload without compromising quality with our high-speed Content Delivery Network. When sharing to social media sites, images are automatically compressed. With our built-in sharing features for sites that do this, minimal compression is applied minimizing duplicating compression, leaving your original untouched without compression. Store and organize your originals as they were uploaded, ready for any editing, resizing, high quality print or just simply preserving their best quality.
										</p>
										
										<h4 id="sec7" class="content-hpageblock">EDIT & ENHANCE YOUR IMAGES</h4>
										
										<p>
											RadTriads offers basic integrated features like cropping, rotating, flipping & resizing. Need more? Enhance your images with top class library of filters giving it that “POP” you are looking for. Brighten, saturate, contrast with minimal effort. Availability through our site makes it accessible to you from any device anywhere. No need for expensive software. 
										</p>
										
										<h4 id="sec8" class="content-hpageblock">SECURITY</h4>
										
										<p>
											Private encryption, EXIF data removal, always keeps your content private and safe.
											<br><br>256 BIT RSA ENCRYPTION<br>
											We ensure your data is kept secure using the absolute best level of encryption.
											<br><br>AUTOMATED EXIF DATA REMOVAL<br>
											All images contain personal information you may be sharing without even knowing it such as where the image was taken, how the image was edited etc. All of this is removed and stays private with RadTriads.

										</p>
										
										<h4 id="sec9" class="content-hpageblock">STORAGE</h4>
										
										<p>
											We take the longevity of your content seriously. Your content is kept safe from hardware failure, natural disasters, and accidents by being stored and backed up in multiple ways both on both cloud and multiple servers daily.
										</p>
										
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
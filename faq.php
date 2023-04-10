<?php
include("templates/headers/inc.php");
include("templates/headers/calculate_usage.php");

// Include the JS file
$js_files = array();

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

$page_title = "RadTriads - FAQ";

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
	                            <div class="alert alert-danger alert-error-upload">
	                            </div>
                                <h1 class="text-gray-900">FAQ</h1>
								
								<div class="row row_sign_up">
																		
									<div class="col-md-12 page-content">
										
										<center>
											<img src="img/faq_header.png" width="100%" />
										</center>
										
										<hr />
										
										<div class="accordion" id="accordionExample">
										    <div class="card">
										        <div class="card-header" id="headingOne">
										            <h2 class="mb-0">
										                <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
										                How to change your email
										                </button>
										            </h2>
										        </div>
										        <div id="collapseOne" class="collapse" aria-labelledby="headingOne" data-parent="#accordionExample">
										            <div class="card-body">
														Go to my account > My Info > Change your email and enter your password
														<br />
														A confirmation message will be sent to your original email
										            </div>
										        </div>
										    </div>
										    <div class="card">
										        <div class="card-header" id="headingTwo">
										            <h2 class="mb-0">
										                <button class="btn btn-link btn-block text-left collapsed" type="button" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
										                How to upgrade/downgrade your plan & payment details
										                </button>
										            </h2>
										        </div>
										        <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionExample">
										            <div class="card-body">
														Go to my account > Plan & Usage > Change your plan here
										            </div>
										        </div>
										    </div>
										    <div class="card">
										        <div class="card-header" id="headingThree">
										            <h2 class="mb-0">
										                <button class="btn btn-link btn-block text-left collapsed" type="button" data-toggle="collapse" data-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
										                Cancelling your subscription
										                </button>
										            </h2>
										        </div>
										        <div id="collapseThree" class="collapse" aria-labelledby="headingThree" data-parent="#accordionExample">
										            <div class="card-body">
														Your account will remain with all access to your current subscription until it becomes due, at which time it will not renew. Please ensure that you download all your images & content prior to the next renewal date.
										            </div>
										        </div>
										    </div>
										    
										    <div class="card">
										        <div class="card-header" id="headingFour">
										            <h2 class="mb-0">
										                <button class="btn btn-link btn-block text-left collapsed" type="button" data-toggle="collapse" data-target="#collapseFour" aria-expanded="false" aria-controls="collapseThree">
										                Easy Sharing and linking
										                </button>
										            </h2>
										        </div>
										        <div id="collapseFour" class="collapse" aria-labelledby="headingFour" data-parent="#accordionExample">
										            <div class="card-body">
														Go to my account > Settings > Check the boxes that you want to show for easy sharing and linking. These will appear in your album view for easy access

										            </div>
										        </div>
										    </div>
										    <div class="card">
										        <div class="card-header" id="headingFive">
										            <h2 class="mb-0">
										                <button class="btn btn-link btn-block text-left collapsed" type="button" data-toggle="collapse" data-target="#collapseFive" aria-expanded="false" aria-controls="collapseThree">
										                Who can see your content settings
										                </button>
										            </h2>
										        </div>
										        <div id="collapseFive" class="collapse" aria-labelledby="headingFive" data-parent="#accordionExample">
										            <div class="card-body">
														Go to My Account >  Privacy & Security > Make your Account Private or Public
										            </div>
										        </div>
										    </div>
										    <div class="card">
										        <div class="card-header" id="headingSix">
										            <h2 class="mb-0">
										                <button class="btn btn-link btn-block text-left collapsed" type="button" data-toggle="collapse" data-target="#collapseSix" aria-expanded="false" aria-controls="collapseThree">
										                Sharing your albums to select group/individual
										                </button>
										            </h2>
										        </div>
										        <div id="collapseSix" class="collapse" aria-labelledby="headingSix" data-parent="#accordionExample">
										            <div class="card-body">
														Go to the Album you wish to share > Select ‘Edit Privacy’ : 
														<br>
														1. Select Public
														2. Select Private with password > Create password you will give to gain access
														3. Select ‘Share Album’ > Copy link to share
										            </div>
										        </div>
										    </div>
										    <div class="card">
										        <div class="card-header" id="headingSeven">
										            <h2 class="mb-0">
										                <button class="btn btn-link btn-block text-left collapsed" type="button" data-toggle="collapse" data-target="#collapseSeven" aria-expanded="false" aria-controls="collapseThree">
										                Creating albums
										                </button>
										            </h2>
										        </div>
										        <div id="collapseSeven" class="collapse" aria-labelledby="headingSeven" data-parent="#accordionExample">
										            <div class="card-body">
														Click ‘Create Album’ > Give your new Album a name and where you want to create it
										            </div>
										        </div>
										    </div>
										    <div class="card">
										        <div class="card-header" id="headingEight">
										            <h2 class="mb-0">
										                <button class="btn btn-link btn-block text-left collapsed" type="button" data-toggle="collapse" data-target="#collapseEight" aria-expanded="false" aria-controls="collapseThree">
										                Adding content
										                </button>
										            </h2>
										        </div>
										        <div id="collapseEight" class="collapse" aria-labelledby="headingEight" data-parent="#accordionExample">
										            <div class="card-body">
														Go to the Album you wish to add content to > Click ‘Upload’<br>Either drag & drop or click to select content you want to upload. You can select up to 100 files at a time.

										            </div>
										        </div>
										    </div>
										    <div class="card">
										        <div class="card-header" id="headingNone">
										            <h2 class="mb-0">
										                <button class="btn btn-link btn-block text-left collapsed" type="button" data-toggle="collapse" data-target="#collapseNine" aria-expanded="false" aria-controls="collapseThree">
										                Renaming an Album
										                </button>
										            </h2>
										        </div>
										        <div id="collapseNine" class="collapse" aria-labelledby="headingNone" data-parent="#accordionExample">
										            <div class="card-body">
														Go to the Album you wish to rename and click ‘Rename Album’ > Give your album a new name & save.
										            </div>
										        </div>
										    </div>
										    <div class="card">
										        <div class="card-header" id="headingTen">
										            <h2 class="mb-0">
										                <button class="btn btn-link btn-block text-left collapsed" type="button" data-toggle="collapse" data-target="#collapseTen" aria-expanded="false" aria-controls="collapseThree">
										                Copying & moving files 
										                </button>
										            </h2>
										        </div>
										        <div id="collapseTen" class="collapse" aria-labelledby="headingTen" data-parent="#accordionExample">
										            <div class="card-body">
														Select the files you want to copy or move and then select either copy or move at the bottom of your screen. Select the album you want to copy or move these files to. Please note that copying will put an additional copy in the new album, leaving the original in its album and moving will remove it from its original album and place it into the new album.
										            </div>
										        </div>
										    </div>
										    <div class="card">
										        <div class="card-header" id="headingEleven">
										            <h2 class="mb-0">
										                <button class="btn btn-link btn-block text-left collapsed" type="button" data-toggle="collapse" data-target="#collapseEleven" aria-expanded="false" aria-controls="collapseThree">
										                Sorting Albums
										                </button>
										            </h2>
										        </div>
										        <div id="collapseEleven" class="collapse" aria-labelledby="headingEleven" data-parent="#accordionExample">
										            <div class="card-body">
														Albums are set to Newest to Oldest by default. You can change this by clicking Sort and changing that to A-Z, Z-A, Oldest to Newest etc.
										            </div>
										        </div>
										    </div>
										    <div class="card">
										        <div class="card-header" id="headingTwelve">
										            <h2 class="mb-0">
										                <button class="btn btn-link btn-block text-left collapsed" type="button" data-toggle="collapse" data-target="#collapseTwelve" aria-expanded="false" aria-controls="collapseThree">
										                Downloading your content
										                </button>
										            </h2>
										        </div>
										        <div id="collapseTwelve" class="collapse" aria-labelledby="headingTwelve" data-parent="#accordionExample">
										            <div class="card-body">
														Go to the album you want to download and select ‘Download Album’ RadTriads will download to your pc in an RAR folder.  You can also download individual files by going to their page.
										            </div>
										        </div>
										    </div>
										    <div class="card">
										        <div class="card-header" id="headingThirteen">
										            <h2 class="mb-0">
										                <button class="btn btn-link btn-block text-left collapsed" type="button" data-toggle="collapse" data-target="#collapseThirteen" aria-expanded="false" aria-controls="collapseThree">
										                Editing & Enhancing your Images
										                </button>
										            </h2>
										        </div>
										        <div id="collapseThirteen" class="collapse" aria-labelledby="headingThirteen" data-parent="#accordionExample">
										            <div class="card-body">
													- Open the image up and select Edit.<br><br>
													Here you will be able to: <br>
													- Enhance your image with various filters<br>
													- Resize <br>
													- Crop <br>
													- Transform/Rotate<br>
													- Draw<br>
													- Add Text<br>
													- Add Shapes<br>
													- Add Stickers<br>
													- Frame<br>
													- Select Corners<br>
													- Add Backgrounds<br>
													- Merge<br>

										            </div>
										        </div>
										    </div>
										    <div class="card">
										        <div class="card-header" id="headingFourtenn">
										            <h2 class="mb-0">
										                <button class="btn btn-link btn-block text-left collapsed" type="button" data-toggle="collapse" data-target="#collapseFiveteen" aria-expanded="false" aria-controls="collapseThree">
										                Changing your Password
										                </button>
										            </h2>
										        </div>
										        <div id="collapseFiveteen" class="collapse" aria-labelledby="headingFourtenn" data-parent="#accordionExample">
										            <div class="card-body">
														Go to My Account > Password > Change your password here. A confirmation will be sent to your email
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
            </div>
        </div>
    </div>
</div>

<?php
// -- Include the footer template
include("templates/footers/global_footer.php");	
?>
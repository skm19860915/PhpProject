<?php
date_default_timezone_set('Europe/London');

// Show or Hide the errors
define("DEBUG_MODE", false);

// Show or Hide the errors
define("TEST_MODE", false);

// MAIN URL
define("URL", "https://www.radtriads.com");

// DB Host
define("DB_HOST", "localhost");

// DB Username
define("DB_USERNAME", "root");

// DB Password
define("DB_PASSWORD", "");

// DB Name
define("DB_NAME", "radtriads_main");

// FREE PLAN
define("FREE_PLAN", "4");

// PLATINUM PLAN
define("PLATINUM_PLAN", "3");

// GOLD PLAN
define("GOLD_PLAN", "2");

// SILVER PLAN
define("SILVER_PLAN", "1");

/*
TEST MODE	
*/
/*
// STRIPE KEY
define("STRIPE_KEY", "");

// STRIPE KEY
define("STRIPE_SECRET_KEY", "");

// STRIPE WEBHOOK KEY
define("STRIPE_WEBHOOK_KEY", "");

// PLAN 1 ID
define("STRIPE_PLAN_1", "");

// PLAN 2 ID
define("STRIPE_PLAN_2", "");

// PLAN 3 ID
define("STRIPE_PLAN_3", "");

// PLAN 4 ID
define("STRIPE_PLAN_4", "");

// PLAN 5 ID
define("STRIPE_PLAN_5", "");

// PLAN 6 ID
define("STRIPE_PLAN_6", "");
*/

/*
PROD MODE
*/
// STRIPE KEY
define("STRIPE_KEY", "");

// STRIPE KEY
define("STRIPE_SECRET_KEY", "");

// STRIPE WEBHOOK KEY
define("STRIPE_WEBHOOK_KEY", "");

// PLAN 1 ID
define("STRIPE_PLAN_1", "");

// PLAN 2 ID
define("STRIPE_PLAN_2", "");

// PLAN 3 ID
define("STRIPE_PLAN_3", "");

// PLAN 4 ID
define("STRIPE_PLAN_4", "");

// PLAN 5 ID
define("STRIPE_PLAN_5", "");

// PLAN 6 ID
define("STRIPE_PLAN_6", "");

// STACKPATH CDN
define("STACKPATH_URL", "https://radtriads.com");

// GOOGLE API KEY
define("GOOGLE_API_KEY", "");

if(DEBUG_MODE) {
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);
}	

?>
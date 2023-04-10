<?php
include("templates/headers/inc.php");
include("templates/headers/calculate_usage.php");

// Set the cookie for this URL in case of a redirection on the taxes page
setcookie("TAX_REDIRECT_URL", $_SERVER["REQUEST_URI"], time() + (86400 * 30), "/");

if(!$_SESSION) {
	header("Location: index.php?action=forbidden");
	exit;
}

if(!isset($_GET["plan"])) {
	header("Location: index.php?action=forbidden");
	exit;	
}

$plan = $_GET["plan"];
$plan_id = $_GET["plan_id"];
$user_id = $_SESSION["USER_ID"];
$good_plan = false;

// Get the user config
$user_tax_infos = $dbh->prepare("SELECT * FROM user WHERE id = :user_id");
$user_tax_infos->bindParam(":user_id", $user_id);
$user_tax_infos->execute();

$user_tax_infos = $user_tax_infos->fetch();

$user_country = $user_tax_infos["country"];
$user_state = $user_tax_infos["state"];

if($user_country == "" || $user_country == NULL || $user_state == NULL || $user_state == "") {
	header("Location: tax-infos.php");
}

// Silver plan
if($plan != STRIPE_PLAN_4 && $plan != STRIPE_PLAN_1) {
	$good_plan = true;
} 

// Gold plan
if($plan != STRIPE_PLAN_2 && $plan != STRIPE_PLAN_5) {
	$good_plan = true;		
} 

// Platinum plan
if($plan != STRIPE_PLAN_3 && $plan != STRIPE_PLAN_6) {
	$good_plan = true;		
} 

if(!$good_plan) {
	header("Location: index.php?action=forbidden");
	exit;	
}

$tax_rates = array();

if($user_country == "Canada" && $user_state == "BC") {
	
	$tax_rates[] = "txr_1HWio4I8XlJR7K1GVY56xlnu";
	$tax_rates[] = "txr_1HWipBI8XlJR7K1GC0S6JkM3";
	
} else if($user_country == "Canada") {
	
	$tax_rates[] = "txr_1HWinLI8XlJR7K1G8kg3bbHb";
		
}

\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);
$stripe_plan = \Stripe\Checkout\Session::create([
		'customer_email' => $_SESSION["EMAIL"],
		'client_reference_id' => $_SESSION["USER_ID"],
		'success_url' => URL . '/payment-success.php?plan=' . $plan . "&plan_id=" . $plan_id,
		'cancel_url' => URL . '/payment-cancel.php',
		'payment_method_types' => ['card'],
		'metadata' => [
			"plan_id" => $plan_id
		],
		'subscription_data' => [
			'items' => [
				[
					'plan' => $plan,
					'tax_rates' => $tax_rates,
				],
			],
		],
]);

?>

<html>
	<head>
		
	</head>
	<body>
		
		<script src="https://js.stripe.com/v3/"></script>
		<script>
			
			var stripe = Stripe("<?php echo STRIPE_KEY; ?>");
			
			stripe.redirectToCheckout({
				sessionId: '<?php echo $stripe_plan->id; ?>'
			}).then(function (result) {
			
			});	
			
		</script>
		
	</body>
</html>
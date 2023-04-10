<?php
include("templates/headers/inc.php");
include("templates/headers/calculate_usage.php");

if(!$_SESSION) {
	header("Location: index.php?action=forbidden");
	exit;
}

$user_id = $_SESSION["USER_ID"];

$user_infos = $dbh->prepare("SELECT * FROM user WHERE id = :user_id");
$user_infos->bindParam(":user_id", $user_id);
$user_infos->execute();

$user = $user_infos->fetch();

$user_country = $user["country"];
$user_state = $user["state"];

if($user_country == "" || $user_country == NULL || $user_state == NULL || $user_state == "") {
	header("Location: tax-infos.php");
}

$plan_id = $user["plan_id"];
$plan = $user["stripe_plan"];

// For updating the Stripe card
\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

$tax_rates = array();

if($user_country == "Canada" && $user_state == "BC") {
	
	$tax_rates[] = "txr_1HWio4I8XlJR7K1GVY56xlnu";
	$tax_rates[] = "txr_1HWipBI8XlJR7K1GC0S6JkM3";
	
} else if($user_country == "Canada") {
	
	$tax_rates[] = "txr_1HWinLI8XlJR7K1G8kg3bbHb";
		
}

$session = \Stripe\Checkout\Session::create([
	'customer_email' => $_SESSION["EMAIL"],
	'client_reference_id' => $_SESSION["USER_ID"],
	'payment_method_types' => ['card'],
	'mode' => 'setup',
	'setup_intent_data' => [
		'metadata' => [
		  'customer_id' => $user["stripe_customer_id"],
		  'subscription_id' => $user["stripe_subscription_id"]
		],
	],
	'success_url' => URL . '/payment-update-success.php?session_id={CHECKOUT_SESSION_ID}',
	'cancel_url' => URL . '/payment-cancel.php',
	'metadata' => [
		"plan_id" => $plan_id
	]
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
				sessionId: '<?php echo $session->id; ?>'
			}).then(function (result) {
			
			});	
			
		</script>
		
	</body>
</html>
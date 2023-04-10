<?php
include("templates/headers/inc.php");
include("templates/headers/calculate_usage.php");

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

if($plan == "free") {
	
	// -- Update the user
	$stmt = $dbh->prepare("	UPDATE user 
							SET 
							first_pay = 1,
							stripe_plan = 'free_plan',
							stripe_subscription_id = NULL,
							stripe_customer_id = NULL,
							plan_id = 1
							WHERE id = :user_id");
							
	$stmt->bindParam(':user_id', $user_id);
	$stmt->execute();
	
	header("Location: payment-success.php?plan=" . $plan . "&plan_id=" . $plan_id);
	
	exit;
}

if($plan != "price_1GzekbI8XlJR7K1GPh8g1FOO" && $plan != "price_1Gzek2I8XlJR7K1GK6F2HHz0" && $plan != "price_1GzeeUI8XlJR7K1Gy8JVMvAn") {
	header("Location: index.php?action=forbidden");
	exit;		
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
			'trial_period_days' => 30,
			'items' => [
				[
					'plan' => $plan,
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
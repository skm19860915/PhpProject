<?php
include("templates/headers/inc.php");

if(!$_SESSION) {
	header("Location: index.php?action=forbidden");
	exit;
}
/*
if(!isset($_GET["session_id"])) {
	
	header("Location: index.php?action=forbidden");
	exit;
	
}

$session_id = $_GET["session_id"];
$user_id = $_SESSION["USER_ID"];

$user_infos = $dbh->prepare("SELECT * FROM user WHERE id = :user_id");
$user_infos->bindParam(":user_id", $user_id);
$user_infos->execute();

$user = $user_infos->fetch();

\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

$session = \Stripe\Checkout\Session::retrieve($session_id);
$setup_intent = $session->setup_intent;

$intent = \Stripe\SetupIntent::retrieve($setup_intent);


$subscription_id = $intent->metadata->subscription_id;
$customer_id = $intent->metadata->customer_id;
$pm = $intent->payment_method;

$payment_method = \Stripe\PaymentMethod::retrieve(
 	$pm
);

$payment_method->attach([
  'customer' => $customer_id,
]);

$updated = \Stripe\Customer::update(
  $customer_id,
  [
    'invoice_settings' => ['default_payment_method' => $payment_method],
  ]
);

// -- Update the user
$stmt = $dbh->prepare("	UPDATE user 
						SET 
						first_pay = 1,
						stripe_subscription_id = :sub_id,
						stripe_customer_id = :cus_id
						WHERE id = :user_id");
						
$stmt->bindParam(':sub_id', $subscription_id);
$stmt->bindParam(':cus_id', $customer_id);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
*/

header("Location: my-account.php?tab=plan&action_payment=success");
?>
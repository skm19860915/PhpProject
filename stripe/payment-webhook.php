<?php
include("../includes/config.php");
include("../includes/db_connect.php");
include("../includes/functions.php");

require_once '../vendor/autoload.php';

// Set your secret key: remember to change this to your live secret key in production
// See your keys here: https://dashboard.stripe.com/account/apikeys
\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

// You can find your endpoint's secret in your webhook settings
$endpoint_secret = STRIPE_WEBHOOK_KEY;

$payload = @file_get_contents('php://input');
$sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
$event = null;

try {
    $event = \Stripe\Webhook::constructEvent(
        $payload, $sig_header, $endpoint_secret
    );
} catch(\UnexpectedValueException $e) {
    // Invalid payload
    http_response_code(400); // PHP 5.4 or greater
    exit();
} catch(\Stripe\Error\SignatureVerification $e) {
    // Invalid signature
    http_response_code(400); // PHP 5.4 or greater
    exit();
}

// Handle the checkout.session.completed event
if ($event->type == 'checkout.session.completed') {
    $session = $event->data->object;
   
    if($session) {
	    
	    $user_id = $session->client_reference_id;
	    
	    // Check if the user already has a subscription active
	    $user_subscription_infos = $dbh->prepare("SELECT * FROM user WHERE id = :user_id");
		$user_subscription_infos->bindParam(":user_id", $user_id);
		$user_subscription_infos->execute();
		
		$user_subscription_i = $user_subscription_infos->fetch();
		
		// Cancel the old subscription if we have one
		if($user_subscription_i["stripe_subscription_id"] != "") {
			
			$user_subscription_active = $user_subscription_i["stripe_subscription_id"];
			
			$active_stripe_subscription = \Stripe\Subscription::retrieve($user_subscription_active);
			$active_stripe_subscription->cancel();
			
			echo "CANCELLING PLAN $user_subscription_active";
			
		} else {
			echo "OLD SUBSCRIPTION NOT FOUND";
		}
		
	    $customer_id = $session->customer;
	    $subscription_id = $session->subscription;
	    $stripe_plan = $session->display_items[0]->plan->id;
	    $plan_id = $session->metadata->plan_id;
	    
	    if($session->mode == "setup") {
		    
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
		    
	    } else {
	    
		    // -- Update the user
			$stmt = $dbh->prepare("	UPDATE user 
									SET 
									first_pay = 1,
									stripe_subscription_id = :sub_id,
									stripe_customer_id = :cus_id,
									stripe_plan = :stripe_plan,
									plan_id = :plan_id,
									stripe_plan_admin = NULL,
									first_pay_admin = 0
									WHERE id = :user_id");
									
			$stmt->bindParam(':sub_id', $subscription_id);
			$stmt->bindParam(':cus_id', $customer_id);
			$stmt->bindParam(':user_id', $user_id);
			$stmt->bindParam(':stripe_plan', $stripe_plan);
			$stmt->bindParam(':plan_id', $plan_id);
			$stmt->execute();
		
		}
    }
    
}

http_response_code(200);
?>
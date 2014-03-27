<?php
## WHMCS Gateway module for Dwolla
## Copyright (c) 2014, Sabrienix Communications

include("../../../dbconnect.php");
include("../../../includes/functions.php");
include("../../../includes/gatewayfunctions.php");
include("../../../includes/invoicefunctions.php");

$gatewaymodule = "dwolla";
$GATEWAY = getGatewayVariables($gatewaymodule);
if (!$GATEWAY["type"]) die("Module Not Activated");

# Helper function to verify signature
function verifyGatewaySignature($proposedSignature, $checkoutId, $amount, $secret) {
    $amount = number_format($amount, 2);
    $signature = hash_hmac("sha1", "{$checkoutId}&{$amount}", $secret);

    return $signature == $proposedSignature;
}

# Decode JSON callback request
$dwolla = json_decode(file_get_contents('php://input'));

# Check a few things in WHMCS, and halt if they're not correct
$invoiceid = checkCbInvoiceID($dwolla->OrderId,$GATEWAY["name"]);
checkCbTransID($dwolla->TransactionId);

# Check signature
# Ripped from: https://developers.dwolla.com/dev/pages/gateway#checkout-workflow
if (verifyGatewaySignature($dwolla->Signature, $dwolla->CheckoutId, $dwolla->Amount, $GATEWAY['apiSecret']) != TRUE) {
	logTransaction($GATEWAY["name"],print_r($dwolla, true),"Unsuccessful: Bad Signature");
	exit();
}

# Check payment status
if ($dwolla->Status == "Completed") {
	# Add payment to account
	addInvoicePayment($dwolla->OrderId,$dwolla->TransactionId,$dwolla->Amount, 0,$gatewaymodule);
	
	# Log transaction
	logTransaction($GATEWAY["name"],print_r($dwolla, true),"Successful");
} else {
	logTransaction($GATEWAY["name"],print_r($dwolla, true),"Unsuccessful");
}

?>
<?php
## WHMCS Gateway module for Dwolla
## Copyright (c) 2014, Sabrienix Communications

include("../../../dbconnect.php");
include("../../../includes/functions.php");
include("../../../includes/gatewayfunctions.php");
include("../../../includes/invoicefunctions.php");

$gatewaymodule = "bitpay";
$GATEWAY = getGatewayVariables($gatewaymodule);
if (!$GATEWAY["type"]) die("Module Not Activated");

# Helper function to verify signature
function verifyGatewaySignature($proposedSignature, $checkoutId, $amount) {
    $amount = number_format($amount, 2);
    $signature = hash_hmac("sha1", "{$checkoutId}&{$amount}", $apiSecret);

    return $signature == $proposedSignature;
}

# Check a few things in WHMCS, and halt if they're not correct
$invoiceid = checkCbInvoiceID($invoiceid,$GATEWAY["name"]);
checkCbTransID($transid);

# Decode JSON callback request
# Todo: This came from Dwolla's API site, I feel like there ought to be
# a better way than file_get_contents()
$dwolla = json_decode(file_get_contents('php://input'));

# Check signature
# Ripped from: https://developers.dwolla.com/dev/pages/gateway#checkout-workflow
if (verifyGatewaySignature($dwolla->Signature, $dwolla->TransactionId, $dwolla->Amount)) {
	logTransaction($GATEWAY["name"],$_POST,"Unsuccessful: Bad Signature");
	exit();
}

# Check payment status
if ($dwolla->Status == "Completed") {
	# Add payment to account
	addInvoicePayment($dwolla->OrderId,$dwolla->TransactionId,$dwolla->Amount, 0,$gatewaymodule);
	
	# Log transaction
	logTransaction($GATEWAY["name"],$_POST,"Successful");
} else {
	logTransaction($GATEWAY["name"],$_POST,"Unsuccessful");
}

?>
<?php
## WHMCS Gateway module for Dwolla
## Copyright (c) 2014, Sabrienix Communications

ini_set("display_errors", 1);

function dwolla_config() {
	$configarray = array(
		"FriendlyName" => array("Type" => "System", "Value"=>"Dwolla"),
		"dwollaId" => array("FriendlyName" => "Dwolla ID for your account.", "Type" => "text"),
		"apiKey" => array("FriendlyName" => "API Key from your Dwolla account.", "Type" => "text"),
		"apiSecret" => array("FriendlyName" => "API Secret from your Dwolla account.", "Type" => "text"),
	);
	
	return $configarray;
}

function dwolla_link($params) {
	$timestamp = time();
	
	$post = array(
		'key' => $params['apiKey'],
		'signature' => hash_hmac('sha1', "{$params['apiKey']}&{$timestamp}&{$params['invoiceid']}", $params['apiSecret']),
		'timestamp' => $timestamp,
		'callback' => $params['systemurl'].'/modules/gateways/callback/dwolla.php',
		'redirect' => $params['systemurl'].'/viewinvoice.php?id='.$params['invoiceid'],
		'orderId' => $params['invoiceid'],
		'destinationId' => $params['dwollaId'],
		'amount' => $params['amount'],
		'shipping' => 0,
		'tax' => 0,
		'name' => 'Invoice #' . $params['invoiceid'],
		'description' => $params['description'],
		
		# Uncomment this to test the module.
		'test' => 'true'
	);
	
	$form = '<form accept-charset="UTF-8"  action="https://www.dwolla.com/payment/pay" method="post">';
	
	foreach($post as $key => $value)
		$form .= '<input type="hidden" name="' . $key . '" value="' . $value . '" />';
	
	$form .= '<button type="submit">Pay with Dwolla</button>';
	$form .= '</form>';
	
	return $form;
}
?>
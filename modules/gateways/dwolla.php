<?php
## WHMCS Gateway module for Dwolla
## Copyright (c) 2014, Sabrienix Communications

function dwolla_config() {
	$configarray = array(
		"FriendlyName" => array("Type" => "System", "Value"=>"Dwolla"),
		"apiKey" => array('FriendlyName' => 'API Key from your bitpay.com account.', 'Type' => 'text'),
	);
	
	return $configarray;
}

function dwolla_link($params) {
	$Dwolla = new DwollaRestClient($apiKey, $apiSecret, 'http://localhost:8888/offsiteGateway.php');
}
?>
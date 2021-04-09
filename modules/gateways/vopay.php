<?php
if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

function vopay_MetaData(){
    return array(
        'DisplayName' => 'درگاه پرداخت تست وهاب آنلاین',
        'APIVersion' => '1.1',
        'DisableLocalCreditCardInput' => false,
        'TokenisedStorage' => false,
    );
}


function vopay_config(){
    return array(
		'FriendlyName' => array(
            'Type' => 'System',
            'Value' => 'درگاه پرداخت تست وهاب آنلاین',
        ),
        'token' => array(
            'FriendlyName' => 'توکن',
            'Type' => 'text',
            'Size' => '25',
        ),
		'currencies' => array(
            'FriendlyName' => 'واحد پولی',
            'Type' => 'dropdown',
            'Options' => array(
                'IRT' => 'تومان',
                'IRR' => 'ریال',
            ),
        ),
    );
}

function vopay_link($params){
	$token = $params['token'];
	$currencies = $params['currencies'];
	$invoiceId = $params['invoiceid'];
	$systemurl = $params['systemurl'];
	$callback = $systemurl . 'modules/gateways/callback/vopay.php?invoice_id='.$invoiceId;
	$amount = str_replace('.00','',$params['amount']);
	if($currencies == 'IRT'){
		$amount = $amount * 10;
	}
	if ($_SERVER['REQUEST_METHOD'] == 'POST'){
		$data = array(
			'amount' => $amount,
			'description' => 'InvoiceID : #'.$invoiceId,
			'callbackURL' => $callback,
		);
		$header = array("token: $token");
		$ch = curl_init("https://my.vahabonline.ir/webservice/banktest/send.json");
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch,CURLOPT_POSTFIELDS,$data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$response1 = curl_exec($ch);
		curl_close($ch);

		$json = json_decode($response1,true);
		var_dump($json);
		if($json['status'] == 'OK'){
			header("Location: https://my.vahabonline.ir/webservice/banktest/?req_id=".$json['id']);
			die();
		}else{
			return $json['error'];
		}
	}
	return '<form method="post"><button type="submit" class="btn btn-success">پرداخت تست وهاب آنلاین</button></form>';
}

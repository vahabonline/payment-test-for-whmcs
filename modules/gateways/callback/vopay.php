<?php
require_once __DIR__ . '/../../../init.php';
use Illuminate\Database\Capsule\Manager as Capsule;
require_once __DIR__ . '/../../../includes/gatewayfunctions.php';
require_once __DIR__ . '/../../../includes/invoicefunctions.php';
$gatewayModuleName = basename(__FILE__, '.php');
$gatewayParams = getGatewayVariables($gatewayModuleName);
if (!$gatewayParams['type']) {
    die("Module Not Activated");
}
$Authority = $_GET["Authority"];
$token = $gatewayParams['token'];
$currencies = $gatewayParams['currencies'];
		$data = array(
			'Authority' => $_POST['Authority'],
		);
		$header = array("token: $token");
		$ch = curl_init("https://my.vahabonline.ir/webservice/banktest/callback.json");
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch,CURLOPT_POSTFIELDS,$data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$response1 = curl_exec($ch);
		curl_close($ch);
		$json = json_decode($response1,true);

$success = true;

$transactionStatus = $success ? 'Success' : 'Failure';
$amount = $json['amount'];
if($currencies == 'IRT'){
	$amount = $json['amount'] / 10;
}
$invoiceId = $_GET['invoice_id'];
$transactionId = $json['SaleOrderId'];
$SaleReferenceId = $json['SaleReferenceId'];

if($json['status'] == '100'){
	$status = 'success';
}
if($json['status'] == '101'){
	$status = 'unsuccess';
	$success = false;
}
if($json['status'] == '0'){
	$status = 'Canceled';
	$success = false;
}

if ($_POST['hash'] != md5($SaleReferenceId . $transactionId . $json['amount'] . $token)) {
    $transactionStatus = 'Hash Verification Failure';
    $success = false;
}

$invoiceId = checkCbInvoiceID($invoiceId, $gatewayParams['name']);
checkCbTransID($transactionId);
logTransaction($gatewayParams['name'], $_POST, $status);

if($success) {
    addInvoicePayment(
        $invoiceId,
        $transactionId,
        $amount,
        '',
        $gatewayModuleName
    );
}
$url = $CONFIG['SystemURL'].'/viewinvoice.php?id='.$invoiceId;
header("Location: $url");
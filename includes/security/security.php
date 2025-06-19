<?php
/**
 * BarclaysWooCommercePaymentGateway - Security Functions 
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


function getSecretKey()
{
	global $woocommerce;
	$gateways = $woocommerce->payment_gateways->payment_gateways();
	return $gateways['cybersource_barclays']->get_secret_key();
}

define ('HMAC_SHA256', 'sha256');
// define ('SECRET_KEY', getSecretKey());
define ('SECRET_KEY', 'e69c7144735f42ed80fe7b25ffd675f84e231f4da51f447089066044f35221ef12c9d660603f4035b6525839d179996f873af338626946aa921047b22e39e8dfb29ac3a287314e83915e15b36b96486bf28a4e2e1d1b471fbf078fcb1ce3f812c21ef250547e4245823de53d532cace6db730dd73cac4faa9a1afab90eaf6324');

function sign ($params)
{
  return signData(buildDataToSign($params), SECRET_KEY);
}

function signData($data, $secretKey)
{
    return base64_encode(hash_hmac('sha256', $data, $secretKey, true));
}

function buildDataToSign($params)
{
        $signedFieldNames = explode(",",$params["signed_field_names"]);

		foreach ($signedFieldNames as $field)
		{
           $dataToSign[] = $field . "=" . $params[$field];
        }
        return commaSeparate($dataToSign);
}

function commaSeparate ($dataToSign)
{
    return implode(",",$dataToSign);
}
?>

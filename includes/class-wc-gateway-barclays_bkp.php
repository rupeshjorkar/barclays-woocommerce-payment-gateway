<?php
/**
 * WooCommerce CyberSource Barclays Class
 *
 */
define ('HMAC_SHA256', 'sha256');
define ('SECRET_KEY', 'e69c7144735f42ed80fe7b25ffd675f84e231f4da51f447089066044f35221ef12c9d660603f4035b6525839d179996f873af338626946aa921047b22e39e8dfb29ac3a287314e83915e15b36b96486bf28a4e2e1d1b471fbf078fcb1ce3f812c21ef250547e4245823de53d532cace6db730dd73cac4faa9a1afab90eaf6324');

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


class WC_Gateway_Cybersource_Barclays extends WC_Payment_Gateway {
    
	// CyberSource Barclays Standard Transaction Endpoints
	private $test_url = "https://testsecureacceptance.cybersource.com/pay";
	private $live_url = "https://secureacceptance.cybersource.com/pay";

	private $test_org_id = '';
	private $live_org_id = '';

	private $card_type_options;

	public function __construct()
	{

		global $woocommerce;

		$this->plugin_path = untrailingslashit( plugin_dir_path( __FILE__ ) );

		$this->id                 = WC_Cybersource_Barclays::GATEWAY_ID;
		$this->method_title       = __( 'Barclays Payment Gateway', WC_Cybersource_Barclays::TEXT_DOMAIN );
		$this->method_description = __( 'Barclays Payment Gateway for WooCommerce shop.', WC_Cybersource_Barclays::TEXT_DOMAIN );
		$this->icon               = apply_filters( 'woocommerce_cybersource_barclays_icon', '' );
		
		$default_card_type_options = array(
			'001' => 'Visa',
			'002' => 'MasterCard',
			'003' => 'American Express',
			'033' => 'Visa Electron',
			'042' => 'Maestro Int\'l'
		);

		$this->has_fields = true;
	
		$this->card_type_options = apply_filters( 'woocommerce_cybersource_barclays_card_types', $default_card_type_options );

		$this->init_form_fields();
		$this->init_settings();

		// Define required variables
		$this->enabled          		= $this->settings['enabled'];
		$this->title            		= $this->settings['title'];
		$this->description      		= $this->settings['description'];
		$this->checkout_processing	= $this->settings['checkout_processing'];
		$this->testmode         		= $this->settings['testmode'];
		$this->log              		= $this->settings['log'];
		$this->device_finger_print	= $this->settings['device_finger_print'];
		$this->transaction_type			= $this->settings['transaction_type'];
		$this->locale					      = $this->settings['locale'];
		//$this->currency				    	= $this->settings['currency'];
		$this->currency = get_woocommerce_currency();
		$this->card_type        		= $this->settings['card_type'];
		$this->merchant_id			  	= $this->settings['merchant_id'];
		$this->profile_keys			  	= $this->settings['profile_keys'];
		$this->live_org_id       		= $this->settings['live_org_id'];
		$this->test_org_id  	    	= $this->settings['test_org_id'];
		$this->profile_id       		= $this->settings['profile_id'];
		$this->profile_id_test  		= $this->settings['profile_id_test'];
		$this->secret_key	    	  	= $this->settings['secret_key'];
		$this->secret_key_test  		= $this->settings['secret_key_test'];
		$this->access_key	    	  	= $this->settings['access_key'];
		$this->access_key_test  		= $this->settings['access_key_test'];	
		$this->banklogo         		= isset( $this->settings['banklogo'] ) ? $this->settings['banklogo'] : 'no';

		if ( $this->is_test_mode() ) $this->description . ' ' . __( 'TEST MODE ENABLED', WC_Cybersource_Barclays::TEXT_DOMAIN );


		add_action( 'woocommerce_receipt_' . $this->id, array( $this, 'payment_page' ) );
		
		// Autocomplete Orders hook
		add_action( 'init', array( $this,'autocompleteOrders' ), 0 );

		if ( is_admin() )
		{
			add_action( 'woocommerce_update_options_payment_gateways',              array( $this, 'process_admin_options' ) );  // WC < 2.0
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );  // WC >= 2.0
		}

		// Override Checkout Button Description
		$this->order_button_text = __( 'Credit OR Debit Card', 'woocommerce' );

	} // End Construct

	/**
	 * Initialise Gateway Settings Form Fields
	 */
	public function init_form_fields()
	{
		$this->form_fields = include( 'settings-barclays.php' );
	}
    public function sign ($params) {
      return $this->signData($this->buildDataToSign($params), SECRET_KEY);
    }
    public function signData($data, $secretKey) {
        return base64_encode(hash_hmac('sha256', $data, $secretKey, true));
    }
    public function buildDataToSign($params) {
        $signedFieldNames = explode(",",$params["signed_field_names"]);
        foreach ($signedFieldNames as $field) {
           $dataToSign[] = $field . "=" . $params[$field];
        }
        return $this->commaSeparate($dataToSign);
    }
    public function commaSeparate ($dataToSign) {
        return implode(",",$dataToSign);
    }
	/**
	 * get_icon function.
	 *
	 * @access public
	 * @return string
	 */
	function get_icon()
	{
		global $woocommerce, $wc_cybersource_barclays;

		$icon = '';
		if ( $this->icon )
		{
			// default behavior
				$icon = '<img src="' . WC_HTTPS::force_https_url( $this->icon ) . '" alt="' . $this->title . '" />';

		} elseif ( $this->card_type )
		{
			// display icons for the selected card types
			$icon = '<br>';
			foreach ( $this->card_type as $card_type )
			{
				if ( file_exists( $wc_cybersource_barclays->plugin_path() . '/images/card-' . strtolower( $card_type ) . '.png' ) )
				{
						$icon .= '<img src="' . WC_HTTPS::force_https_url( $wc_cybersource_barclays->plugin_url() . '/images/card-' . strtolower( $card_type ) . '.png' ) . '" width="42px;" alt="' . strtolower( $card_type ) . '" style="margin-right:2px;" />';
				}
			}
		}
		return apply_filters( 'woocommerce_gateway_icon', $icon, $this->id );
	}


	function payment_fields()
	{
		parent::payment_fields();
		?>
		<style type="text/css">#payment ul.payment_methods li label[for='payment_method_cybersource_barclays'] img:nth-child(n+2) { margin-left:1px; }</style>
<?php
	}


	/**
	 * User is redirected to a 'payment' page which contains the form that collects the actual payment information .
	 */
	public function process_payment( $order_id )
	{
		parent::process_payment( $order_id );
		$order = wc_get_order( $order_id );

		do_action( 'woocommerce_' . $this->id . '_process_payment', $this->id, $order_id );

		return array(
			'result'   => 'success',
			'redirect' => $this->get_payment_page_url( $order )
		);
	}


	/**
	 * Payment page for showing the payment form which sends data to cybersource.	
	 */
	public function payment_page( $order_id )
	{

		global $woocommerce, $wc_cybersource_barclays;

		// Include the Security file.
		require_once( 'security/security.php' );

		$order = wc_get_order( $order_id );
		/**
		 * Generate a unique reference. 
		 */
		function getmicrotime()
		{ 
			list( $usec, $sec ) = explode( " ",microtime() );
			$usec = ( int )( ( float )$usec * 1000 );
			
			while (strlen($usec) < 3)			
			{
				$usec = "0" . $usec;
			}
			
			return $sec . $usec;
		}
		
	
	    $amt=  str_replace(",", "", $order->get_total());
		// Array for Order and to be used for building Request Fields.
		/* $data_array = array(
			'access_key' => $this->get_access_key(),
			'profile_id' => $this->get_profile_id(),
			'transaction_uuid' => $order->order_key,
			'signed_field_names'=>'access_key,profile_id,transaction_uuid,signed_field_names,unsigned_field_names,signed_date_time,locale,transaction_type,reference_number,amount,currency,bill_to_forename,bill_to_surname,bill_to_email,bill_to_address_line1,bill_to_address_city,bill_to_address_state,bill_to_address_country,bill_to_address_postal_code',
			'unsigned_field_names' => 'card_number,card_expiry_date,card_type,card_cvn',
		//	'unsigned_field_names' => 'card_number,card_expiry_date,card_type,card_cvn',
			'signed_date_time' => gmdate("Y-m-d\TH:i:s\Z"),
			'locale' => $this->locale,
			'transaction_type' => $this->transaction_type,
			'reference_number' => $order->id,
		//	'amount' => $order->get_total(),
		    'amount' => $amt,
			'currency' => $this->currency,
			'payment_method' => 'card', 
			'bill_to_forename' => $order->billing_first_name,
			'bill_to_surname' => $order->billing_last_name,
			'bill_to_email' => $order->billing_email,
			'bill_to_phone' => $order->billing_phone,
			'bill_to_address_line1' => $order->billing_address_1,
			'bill_to_address_line2' => $order->billing_address_2,
			'bill_to_address_city' => $order->billing_city,
			'bill_to_address_state' => $order->billing_state,
			'bill_to_address_country' => $order->billing_country,
			'bill_to_address_postal_code' => $order->billing_postcode,
			'bill_to_company_name' => $order->billing_company,
			'customer_ip_address' => $this->get_ip_address(),
			'device_fingerprint_id' => $this->device_finger_print( $order_id ),
		); */
		 $data_array = array(
			'access_key' => $this->get_access_key(),
			'profile_id' => $this->get_profile_id(),
			'transaction_uuid' => $order->order_key,
			'signed_field_names'=>'access_key,profile_id,transaction_uuid,signed_field_names,unsigned_field_names,signed_date_time,locale,transaction_type,reference_number,amount,currency',
			'unsigned_field_names' => '',
			'signed_date_time' => gmdate("Y-m-d\TH:i:s\Z"),
			'locale' => 'en',
			'transaction_type' => 'sale',
			'reference_number' => uniqid().$order->get_ID(),
		    'amount' => $amt,
			'currency' => 'KES'
		);
        
		// Add Signature field to the array before POSTing to payment gateway
		// $data_to_post = array_merge( $data_to_merge, array( "signature" => sign( $params ) ) );

		// Get the order
		$order = wc_get_order( $order_id );

		echo wpautop( __( 'Enter your Card details below.', WC_Cybersource_Barclays::TEXT_DOMAIN ) );
		
		if ( $this->is_test_mode() )
		{
			// echo "<p style=\"color: #FF0000;\"><strong>" . __( 'TEST MODE ENABLED', WC_Cybersource_Barclays::TEXT_DOMAIN ) . "</strong></p>\n";
		}		
		/**
		 * Build form name value pairs from Checkout page.		 
		 */
		 
?>
<form id="payment_confirmation" action="https://secureacceptance.cybersource.com/pay" method="post"/>
<fieldset id="confirmation">
    <legend>Review Payment Details</legend>
    <div>
        <?php
            foreach($data_array as $name => $value) {
                echo "<div>";
                echo "<span class=\"fieldName\">" . $name . "</span><span class=\"fieldValue\">" . $value . "</span>";
                echo "</div>\n";
            }
        ?>
    </div>
</fieldset>
    <?php
        foreach($data_array as $name => $value) {
            echo "<input type=\"hidden\" id=\"" . $name . "\" name=\"" . $name . "\" value=\"" . $value . "\"/>\n";
        }
        echo "<input type=\"hidden\" id=\"signature\" name=\"signature\" value=\"" . $this->sign($data_array) . "\"/>\n";
     ?>
<input type="submit" id="submit" value="Confirm"/>
</form>

<?php /* ?>	<form action="<?php echo $this->get_action_url(); ?>" method="POST" class="checkout_cybersource_barclays" >
    <fieldset id="test_confirmation">
        <legend>Review Payment Details</legend>
        <div>
            <?php
                foreach($data_to_post as $name => $value) {
                    // echo "<div>";
                    // echo "<span class=\"fieldName\">" . $name . "</span><span class=\"fieldValue\">   ----  " . $value . "</span>";
                    // echo "</div>\n"; 
                }
            ?>
        </div>
    </fieldset>

<?php
		
			foreach( $data_to_post as $name => $value )
			{
				echo "<input type=\"hidden\" id=\"" . $name . "\" name=\"" . $name . "\" value=\"" . $value . "\"/>\n";
			}

?>
		
		<div id="payment" class="barclays_payment_cardinfo">
		    
		<input type="submit" name="confirm_pay" value="<?php _e( 'Proceed To Pay', WC_Cybersource_Barclays::TEXT_DOMAIN ) ?>" class="button alt" />
		</div>
 
	</form> <?php */ ?>
    <?php
	}

		/**
	 * Relay response - handles return data from CyberSource and does redirects
	 */
	public function cybersource_response()
	{
	   
		global $woocommerce, $wc_cybersource_barclays;
		
		// Array for CyberSource Reason Codes sent with every transaction request.
		$reasonCodes = array(
			'100' => 'Card transaction was processed successfully.',
			'102' => 'One or more fields in the request contain invalid data. <p style=\"color: #FF0000;\"><strong>Possible Action: see the reply fields invalid_fields for which fields are invalid. Resend the request with the correct information.</strong></p>',
			'104' => 'The access_key and transaction_uuid fields for this authorization request matches the access_key and transaction_uuid of another authorization request that you sent within the past 15 minutes. <p style=\"color: #FF0000;\"><strong>Possible Action: resend the request with a unique access_key and transaction_uuid fields.</strong></p>',
			'110' => 'Only a partial amount was approved.',
			'150' => 'Error - General system failure. <p style=\"color: #FF0000;\"><strong>Possible Action: See the documentation for your CyberSource client (SDK) for information about how to handle retries in the case of system errors.</strong></p>',
			'200' => 'The authorization request was approved by the issuing bank but declined by CyberSource because it did not pass Address Verification System (AVS) check. <p style=\"color: #FF0000;\"><strong>Possible Action: you can capture the authorization, but consider reviewing the order for the possibility of fraud.</strong></p>',
			'201' => 'The issuing bank has questions about the request. You do not receive an authorization code programmatically, but you might receive one verbally by calling the processor. <p style=\"color: #FF0000;\"><strong>Possible Action: call your processor to possibly receive a verbal authorization. For contact phone numbers, refer to Barclays Bank, Card Centre.</strong></p>',
			'202' => 'Expired card. You might also receive this value if the expiration date you provided does not match the date the issuing bank has on file. <p style=\"color: #FF0000;\"><strong>Possible Action: request a different card or other form of payment.</strong></p>',
			'203' => 'General decline of the card. No other information was provided by the issuing bank. <p style=\"color: #FF0000;\"><strong>Possible Action: request a different card or other form of payment.</strong></p>',
			'204' => 'Insufficient funds in the account. <p style=\"color: #FF0000;\"><strong>Possible Action: request a different card or other form of payment.</strong></p>',
			'205' => 'Stolen or lost card. <p style=\"color: #FF0000;\"><strong>Possible Action: review this transaction manually to ensure that you submitted the correct information.</strong></p>',
			'207' => 'Issuing bank unavailable. <p style=\"color: #FF0000;\"><strong>Possible Action: wait a few minutes and resend the request.</strong></p>',
			'208' => 'Inactive card or card not authorized for card-not-present transactions. <p style=\"color: #FF0000;\"><strong>Possible Action: request a different card or other form of payment.</strong></p>',
			'210' => 'The card has reached the credit limit. <p style=\"color: #FF0000;\"><strong>Possible Action: request a different card or other form of payment.</strong></p>',
			'211' => 'Invalid CVN. <p style=\"color: #FF0000;\"><strong>Possible Action: request a different card or other form of payment.</strong></p>',
			'221' => 'The customer matched an entry on the processor\'s negative file.',
			'222' => 'Account frozen or closed.',
			'230' => 'The authorization request was approved by the issuing bank but declined by CyberSource because it did not pass the Card Verification Number (CVN) check. <p style=\"color: #FF0000;\"><strong>Possible Action: you can capture the authorization, but consider reviewing the order for the possibility of fraud.</strong></p>',
			'231' => 'Invalid account number. <p style=\"color: #FF0000;\"><strong>Possible Action: request a different card or other form of payment.</strong></p>',
			'232' => 'The card type is not accepted by the payment processor. <p style=\"color: #FF0000;\"><strong>Possible Action: contact Barclays Bank, Card Centre to confirm that your account is set up to receive the card in question.</strong></p>',
			'233' => 'General decline by processor. <p style=\"color: #FF0000;\"><strong>Possible Action: request a different card or other form of payment.</strong></p>',
			'234' => 'There is a problem with the information in your CyberSource account. <p style=\"color: #FF0000;\"><strong>Possible Action: do not resend the request. Contact Barclays Bank, Card Centre to correct the information in your account.</strong></p>',
			'236' => 'Processor failure. <p style=\"color: #FF0000;\"><strong>Possible Action: wait a few minutes and resend the request.</strong></p>',
			'240' => 'The card type is invalid or does not correlate with the credit card number. <p style=\"color: #FF0000;\"><strong>Possible Action: Possible Action: confirm that the card type correlates with the credit card number specified in the request, then resend the request.</strong></p>',
			'475' => 'The cardholder is enrolled for payer authentication. <p style=\"color: #FF0000;\"><strong>Possible Action: authenticate cardholder before proceeding.</strong></p>',
			'476' => 'Payer authentication could not be authenticated.',
			'481' => 'The order has been rejected by Decision Manager.',
			'520' => 'The authorization request was approved by the issuing bank but declined by CyberSource based on your legacy Smart authorization settings. <p style=\"color: #FF0000;\"><strong>Possible Action: review the authorization request.</strong></p>',
		);

		// the api url is shared with the SOP echeck plugin, so make sure this is a card transaction before going any further
		if ( ! isset( $_POST['req_payment_method'] ) || 'card' != $_POST['req_payment_method'] ) return;

		// Include the Security file that is used to sign the API fields
		require_once( 'security/security.php' );

		// Log the Response received from CyberSource - Used for Troubleshooting purposes
		//if ( $this->log_enabled() ) $this->log_request( "CyberSource Barclays Response Fields: " );

		// Loop through the $_POST Array
		foreach( $_POST as $name => $value )
		{
			$params[$name] = $value;
		}
      
		// Verify the Signature before processing any further. This will ensure that no fields have been tampered with.
		if (strcmp($params["signature"], sign($params))==0)
		{

			/**
			 * The following ORDER statuses are used:			
			 */
		//	$order_id = explode('_', $_POST['req_reference_number']);
			$order_id = $_POST['req_reference_number'];
		
	//		$order_id = (int)$order_id[0];

			$order = wc_get_order( $order_id );
          //  print_r($order);
           
			// If the payment got sent twice somehow and completed successfully add a note and, redirect to the 'thank you' page
			if ( 'completed' == $order->status || 'processing' == $order->status )
			{
				// Log the transaction details to the LOG file.
			//	if ( $this->log_enabled() ) $wc_cybersource_barclays->log( sprintf( "Possible Duplicate, Order %s has already been processed", $order->id ) );

			
				$order->add_order_note( 'Duplicate transaction received' );

				//wp_redirect( $this->get_return_url( $order ) );
				exit;
			}

			/**
			 * Handle the transaction response according to
			 * the Decision that has been sent.
			 */
			$decision = $_POST['decision'];
		
			SWITCH ($decision)
			{

				CASE "ACCEPT":

                    $response_arr= json_encode($_POST);
				//	if ( $this->log_enabled() ) $wc_cybersource_barclays->log( sprintf( "Order %s has being processed successfully.", $order->id ) );
				// Add a note that goes with the transaction from CyberSource
					$msg_status = "Order has being processed successfully";
					$order->add_order_note($msg_status);
					$order->add_order_note('Transaction_id'.$_POST['transaction_id'].'<br>Request payment method: '.$_POST['req_payment_method'].'<br>Reason Code: '.$_POST['reason_code'].'<br>Barclays Decision: '.$decision );
                    $order-> add_order_note('message: '.$_POST['message']);
                    $order_note = $reasonCodes[$_POST['reason_code']] ;
                    $order->add_order_note( $order_note );
                    $order->add_order_note('Response: '.$response_arr );
					// Payment complete
					 $order->payment_complete();
                    $order->update_status( 'completed', $order_note );
                    
                    $mailer = WC()->mailer();
                    $mails = $mailer->get_emails();
                    if ( ! empty( $mails ) ) {
                        foreach ( $mails as $mail ) {
                            if ( $mail->id == 'customer_completed_order' ) {
                               $mail->trigger( $order->id );
                            }
                         }
                    }
                    
					// Remove cart
					$woocommerce->cart->empty_cart();

					// Redirect to the Thank You page
				//	wp_redirect( $this->get_return_url( $order ), 302 );
				return $msg_status;

					exit;

					break;

				CASE "DECLINE":

					//if ( $this->log_enabled() ) $wc_cybersource_barclays->log( sprintf( "Order %s has being DECLINED by Decision Manager.", $order->id ) );
					$msg_status = "Order has being DECLINED by Decision Manager";
						$order->add_order_note($msg_status);
	                $order->add_order_note('Transaction_id'.$_POST['transaction_id'].'<br>Request payment method: '.$_POST['req_payment_method'].'<br>Reason Code: '.$_POST['reason_code'].'<br>Barclays Decision: '.$decision );
	                 $order->add_order_note('message: '.$_POST['message']);
	                   $order->add_order_note('Response: '.$response_arr );
                    
					// Place on-hold for the Admin to Review
					$order_note = $reasonCodes[$_POST['reason_code']] ;

					if( 'failed' != $order->status )
					{
						$order->update_status( 'failed', $order_note );

					}else					
					{

						// Otherwise, make sure we add the order note so we can detect when someone fails to check out multiple times
						$order->add_order_note( $order_note );
					}

					// Redirect to the Thank You page
				//	wp_redirect( $this->get_return_url( $order ), 302 );
				return $msg_status;
					exit;

					break;

				CASE "REVIEW":

				//	if ( $this->log_enabled() ) $wc_cybersource_barclays->log( sprintf( "Order %s has being placed on-hold because Decision Manager has marked it for REVIEW.", $order->id ) );
			        
			        $msg_status = "Order has being placed on-hold because Decision Manager has marked it for REVIEW.";
			    	$order->add_order_note($msg_status);
			    	
					// Place on-hold for the Admin to Review
				//	$order_note = sprintf( __( $reasonCodes[$this->get_post( 'reason_code' )], WC_Cybersource_Barclays::TEXT_DOMAIN ) );
                    $order_note = $reasonCodes[$_POST['reason_code']] ;
                    $order->add_order_note('Transaction_id'.$_POST['transaction_id'].'<br>Request payment method: '.$_POST['req_payment_method'].'<br>Reason Code: '.$_POST['reason_code'].'<br>Barclays Decision: '.$decision );
	                 $order->add_order_note('message: '.$_POST['message']);
	                  $order->add_order_note('Response: '.$response_arr );
                    
					if ( 'on-hold' != $order->status )
					{

						$order->update_status( 'on-hold', $order_note );

					} else					
					{

						// Otherwise, make sure we add the order note so we can detect when someone fails to check out multiple times
						$order->add_order_note( $order_note );
					}

					// Redirect to the Thank You page
				//	wp_redirect( $this->get_return_url( $order ), 302 );
				return $msg_status;
					exit;

					break;

				CASE "ERROR":

				//	if ( $this->log_enabled() ) $wc_cybersource_barclays->log( sprintf( "Possible Error in processing, Order %s . Please check the Cybersource settings.", $order->id ) );
					// Place on Failed for the Admin to Review
					
					 $msg_status = "Possible Error in processing Order.";
					$order->add_order_note($msg_status);
					
					$order_note = $reasonCodes[$_POST['reason_code']] ;
                    $order->add_order_note('Transaction_id'.$_POST['transaction_id'].'<br>Request payment method: '.$_POST['req_payment_method'].'<br>Reason Code: '.$_POST['reason_code'].'<br>Barclays Decision: '.$decision );
	                 $order->add_order_note('message: '.$_POST['message']);
	                   $order->add_order_note('Response: '.$response_arr );
	                 
				//	$order_note = sprintf( __( 'Access denied, page not found, or internal server error: code %s%s', WC_Cybersource_Barclays::TEXT_DOMAIN ), $this->get_post( 'reason_code' ), $error_message );

					if ( 'failed' != $order->status )
					{

						$order->update_status( 'failed', $order_note );
					} else					
					{

						// Otherwise, make sure we add the order note so we can detect when someone fails to check out multiple times
						$order->add_order_note( $order_note );
					}

					// Redirect to the Thank You page
				//	wp_redirect( $this->get_return_url( $order ), 302 );
				return $msg_status;
					exit;

					break;

				CASE "CANCEL":

				//	if ( $this->log_enabled() ) $wc_cybersource_barclays->log( sprintf( "The Order has been cancelled by the customer, Order %s ", $order->id ) );
					// Place on Cancelled for the Admin to Review
					
					$msg_status = "The Order has been cancelled by the customer.";
					$order->add_order_note($msg_status);
					
					$order_note = $reasonCodes[$_POST['reason_code']];
					$order->add_order_note('message: '.$_POST['message']);
                    $order->add_order_note('Transaction_id'.$_POST['transaction_id'].'<br>Request payment method: '.$_POST['req_payment_method'].'<br>Reason Code: '.$_POST['reason_code'].'<br>Barclays Decision: '.$decision );
	                  $order->add_order_note('Response: '.$response_arr );
	                 
				//	$order_note = sprintf( __( 'The Order has been cancelled by the customer: code %s%s', WC_Cybersource_Barclays::TEXT_DOMAIN ), $this->get_post( 'reason_code' ), $error_message );

					if ( 'Cancelled' != $order->status )
					{

						$order->update_status( 'cancelled', $order_note );

					} else
					
					{

						// Otherwise, make sure we add the order note so we can detect when someone fails to check out multiple times
						$order->add_order_note( $order_note);
					}

					// Redirect to the Thank You page
				//	wp_redirect( $this->get_return_url( $order ), 302 );
				return $msg_status;
					exit;

					break;

				DEFAULT:

					// Log this when a UNKNOWN DECISION is sent through
				//	if ( $this->log_enabled() ) $wc_cybersource_barclays->log( sprintf( "Unknown Decision sent for Order %s, it has been placed on-hold for investigation", $order->id ) );
                    
                    $msg_status = "Unknown Decision Received from Gateway.";
                    $order_note = $reasonCodes[$_POST['message']];
					$order->add_order_note('message: '.$_POST['message']);
					$order->add_order_note($msg_status);
					  $order->add_order_note('Response: '.$response_arr );
				
					// Place order on-hold for the Admin
			
				//	$order_note = sprintf( __( 'Unknown Decision Received from Gateway: ' . $_POST['decision']. ' with reason code %s%s, Please contact the Barclays Bank, Card Centre for Investigations.', WC_Cybersource_Barclays::TEXT_DOMAIN ), $this->get_post( 'reason_code' ), $error_message );

					if ( 'on-hold' != $order->status )
					{

						$order->update_status( 'on-hold', $order_note );
					} else					
					{
			
						$order->add_order_note( $order_note );
					}

					// Redirect to the Thank You page
					return $msg_status;
				//	wp_redirect( $this->get_return_url( $order ), 302 );
					exit;

			} // End of SWITCH Statement

		} else
		
		{

			// Signature Verification failed, response was not properly signed by CyberSource
		//	if ( $this->log_enabled() ) $wc_cybersource_barclays->log( sprintf( "Signature Verification failed for this Order " . $params['req_reference_number'] . ", please check CyberSource Settings. Generated signature by this Gateway is: " . sign($params), $order->id ) );
		
		   $msg_status = "Error - invalid transaction signature. check CyberSource settings.  Please contact the merchant and provide them with this message.";
		   $order->add_order_note($msg_status);
		   
			$order->add_order_note('Transaction_id'.$_POST['transaction_id'].'<br>Request payment method: '.$_POST['req_payment_method'].'<br>Reason Code: '.$_POST['reason_code'].'<br>Barclays Decision: '.$decision );
            $order-> add_order_note('message: '.$_POST['message']);
            $order_note = $reasonCodes[$_POST['reason_code']] ;
            $order->add_order_note( $order_note );
              $order->add_order_note('Response: '.$response_arr );
            
            
		//	echo __( "Error - invalid transaction signature (check CyberSource settings).  Please contact the merchant and provide them with this message.", WC_Cybersource_Barclays::TEXT_DOMAIN );

			// Redirect to the Thank You page
		//	wp_redirect( $this->get_return_url( $order ), 302 );
			exit;
		}
	}

	/**	 
	 * Get Available Profile ID, Access Key and Secret Key to be used for the request	
	 */
	function is_available()
	{

		// proper configuration
		if ( ! $this->get_profile_id() || ! $this->get_access_key() || ! $this->get_secret_key() || ! $this->get_merchant_id() ) return false;

		return parent::is_available();
	}


	/**
	 * Add a button to the order actions meta box to view the order in CyberSource
	 *
	 * @param WC_Order $order the order object
	 */
	public function order_meta_box_transaction_link( $order )
	{
		if ( $url = $this->get_transaction_url( $order ) )
		{
			?>
			<li class="wide" style="text-align: center;">
				<a class="button tips" href="<?php echo esc_url( $url ); ?>" target="_blank" data-tip="<?php _e( 'View this transaction in the CyberSource Business Center', WC_Cybersource_Barclays::TEXT_DOMAIN ); ?>" style="cursor: pointer !important;"><?php _e( 'View in CyberSource', WC_Cybersource_Barclays::TEXT_DOMAIN ); ?></a>
			</li>
			<?php
		}
	}

	private function log_request( $title )
	{
		global $wc_cybersource_barclays;

		$response = $_POST;
		unset( $response['wc-api'] );
		$wc_cybersource_barclays->log( $title . "\n" . print_r( $response, true ) );
	}

	// Safely get post data if set
	public function get_post( $name )
	{
		if ( isset( $_POST[ $name ] ) )
		{
			return trim( $_POST[ $name ] );
		}
		return null;
	}


	/**
	 * Returns the URL to the payment page	
	 */
	private function get_payment_page_url( $order, $choose_payment_page = false )
	{

	//	$payment_page = get_permalink( woocommerce_get_page_id( 'checkout' ) );
		
			$payment_page = get_permalink( wc_get_page_id( 'checkout' ) );

		// make ssl if needed
		if ( is_ssl() || 'yes' == get_option( 'woocommerce_force_ssl_checkout' ) ) $payment_page = str_replace( 'http:', 'https:', $payment_page );

		// URL for the choose payment method page
		if ( $choose_payment_page ) return add_query_arg( array( 'order_id' => $order->id, 'order-pay' => $order->order_key, 'pay_for_order' => true ), $payment_page );
	
		return add_query_arg( array( 'order-pay' => $order->id, 'key' => $order->order_key ), $payment_page );
	}


	/**
	 * Return the all profile related information
	 */
	public function get_profile_id()
	{
		return $this->is_test_mode() ? $this->profile_id_test : $this->profile_id;
	}

	private function get_action_url()
	{
		return $this->is_test_mode() ? $this->test_url : $this->live_url;
	}
	
	public function get_secret_key()
	{
		return $this->is_test_mode() ? $this->secret_key_test : $this->secret_key;
	}
	
	public function get_access_key()
	{
		return $this->is_test_mode() ? $this->access_key_test : $this->access_key;
	}
	
	public function get_org_id()
	{
		return $this->is_test_mode() ? $this->test_org_id : $this->live_org_id;
	}

	public function get_merchant_id()
	{
		return $this->merchant_id;
	}

	// Get Client GEO location
	private function get_ip_address()
	{
		$url = "http://ipinfo.io/";
		$json = file_get_contents($url);
		$data = json_decode($json);
		
		return $data->ip;
	}

	/**
	 * Is test mode enabled?
	 *
	 * @return boolean true if test mode is enabled
	 */
	private function is_test_mode()
	{
		return "yes" == $this->testmode;
	}
	
	private function is_debug_mode()
	{
		//return "yes" == $this->debug;
		return "no";
	}
	
	public function log_enabled()
	{
		return "yes";
	}

	/**
	 * Is Device Finger Print on?	
	 */
	private function is_device_finger_print_enabled()
	{
		return "yes" == $this->device_finger_print;
	}

	/**
	 * Should the Bank Logo be displayed on the Pay page?
	 *
	 * @return boolean true if the logo should be displayed; false otherwise
	 */
	private function display_bank_logo()
	{
		return "yes" == $this->banklogo;
	}

	/**
	 * The fields will only be part of the form if Device Finger Print
	 * has been enabled in the Admin Settings for this Plugin in WooCommerce.
	 */
	private function device_finger_print( $order_id )
	{	
		if( $this->is_device_finger_print_enabled() )
		{
			global $woocommerce, $wc_cybersource_barclays;
			$order = wc_get_order( $order_id );
			return $order->id . substr( $order->order_key,9 );
		}
	}


	// Return Device Fingerprinting code segments
	private function html_device_finger_print( $order_id )
	{
		$js_html  =			'var org_ID = \'' . $this->get_org_id() . '\';';
		$js_html .=			'var session_ID = \'' . $this->device_finger_print( $order_id ) . '\';';
		$js_html .=			'var merchant_ID = \'' . $this->get_merchant_id() . '\';';

		$js_html .=			'function device_finger_print()';
		$js_html .=			'{';
		
		// The code segments for implementing Device Fingerprinting i.e. PNG Image, Flash Code and JavaScript Code respectively
		$js_html .=				'var str_img = \'<p style="background:url(https://h.online-metrix.net/fp/clear.png?org_id=\' + org_ID + \'&amp;session_id=\' + merchant_ID + session_ID + \'&amp;m=1)"></p><img src="https://h.online-metrix.net/fp/clear.png?org_id=\' + org_ID + \'&amp;session_id=\' + merchant_ID + session_ID + \'&amp;m=2" alt="">\';';
		$js_html .=				'var str_obj = \'<object type="application/x-shockwave-flash" data="https://h.online-metrix.net/fp/fp.swf?org_id=\' + org_ID + \'&amp;session_id=\' + merchant_ID + session_ID + \'" width="1" height="1" id="thm_fp"><param name="movie" value="https://h.online-metrix.net/fp/fp.swf?org_id=\' + org_ID + \'&amp;session_id=\' + merchant_ID + session_ID + \'" /><div></div></object>\';';
		$js_html .=				'var str_script = \'<script src="https://h.online-metrix.net/fp/check.js?org_id=\' + org_ID + \'&amp;session_id=\' + merchant_ID + session_ID + \'" type="text/javascript">\';';

		$js_html .=				'return str_img + str_obj + str_script ;';

		$js_html .=			'}'; //End of device_finger_print function

		$js_html .=			'$("body").prepend( device_finger_print() )'; // Prepend Device Fingerprinting code segments to body tag

		return $js_html;
	}


	/**
	 * Build the URL for the current page that will be used to post to self.	
	 */
	private function self_URL()
	{
		$ret = substr( strtolower($_SERVER['SERVER_PROTOCOL']),0,strpos( strtolower($_SERVER['SERVER_PROTOCOL']),"/")); // Add protocol (like HTTP)
		$ret .= ( empty($_SERVER['HTTPS']) ? NULL : (($_SERVER['HTTPS'] == "on") ? "s" : NULL )); // Add 's' if protocol is secure HTTPS
		$ret .= "://" . $_SERVER['SERVER_NAME']; // Add domain name/IP address
		$ret .= ( $_SERVER['SERVER_PORT'] == 80 ? "" : ":".$_SERVER['SERVER_PORT']); // Add port directive if port is not 80 (default WWW port)
		$ret .= $_SERVER['REQUEST_URI']; // Add the rest of the URL
		
		return $ret; // Return the value
	}


	/**
	 * autocompleteOrders 
	 * Autocomplete Orders
	 * @return void
	 */
	function autocompleteOrders()
	{
		$mode = get_option('wc_'.$this->id.'_mode');
		if ($mode == 'all')
		{
			add_action('woocommerce_thankyou', 'autocompleteAllOrders');
			/**
			 * autocompleteAllOrders 
			 * Register custom tabs Post Type
			 * @return void
			 */
			function autocompleteAllOrders($order_id)
			{
				global $woocommerce;

				if (!$order_id)
					return;
				$order = new WC_Order($order_id);
				$order->update_status('completed');
			}
		} elseif ($mode == 'paid') {
			add_filter('woocommerce_payment_complete_order_status', 'autocompletePaidOrders', 10, 2);
			/**
			 * autocompletePaidOrders 
			 * Register custom tabs Post Type
			 * @return void
			 */
			function autocompletePaidOrders($order_status, $order_id)
			{
				$order = new WC_Order($order_id);
				if ($order_status == 'processing' && ($order->status == 'on-hold' || $order->status == 'pending' || $order->status == 'failed')) 
				{
					return 'completed';
				}
				return $order_status;
			}
		} elseif ($mode == 'virtual') {
			add_filter('woocommerce_payment_complete_order_status', 'autocompleteVirtualOrders', 10, 2);
			/**
			 * autocompleteVirtualOrders 
			 * Register custom tabs Post Type
			 * @return void
			 */
			function autocompleteVirtualOrders($order_status, $order_id)
			{
				$order = new WC_Order($order_id);
				if ('processing' == $order_status && ('on-hold' == $order->status || 'pending' == $order->status || 'failed' == $order->status)) 
				{
					$virtual_order = null;
					if (count($order->get_items()) > 0 ) 
					{
						foreach ($order->get_items() as $item) 
						{
							if ('line_item' == $item['type']) 
							{
								$_product = $order->get_product_from_item($item);
								if (!$_product->is_virtual()) 
								{
									$virtual_order = false;
									break;
								} else {
									$virtual_order = true;
								}
							}
						}
					}
					if ($virtual_order) 
					{
						return 'completed';
					}
				}
				return $order_status;
			}
		}
	}


} // End of Class

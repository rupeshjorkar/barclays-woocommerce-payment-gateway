<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Settings for Barclays Payment Gateway
 */
return array(
	'enabled' => array(
		'title'       => __( 'Enable', WC_Cybersource_Barclays::TEXT_DOMAIN ),
		'label'       => __( 'Enable Barclays Payment Gateway', WC_Cybersource_Barclays::TEXT_DOMAIN ),
		'type'        => 'checkbox',
		'description' => '',
		'default'     => 'no',
		'desc_tip'      => true,
	),
	'title' => array(
		'title'       => __( 'Title', WC_Cybersource_Barclays::TEXT_DOMAIN ),
		'type'        => 'text',
		'description' => __( 'Payment method is the title that the customer will see on your website.', WC_Cybersource_Barclays::TEXT_DOMAIN ),
		'default'     => __( 'Barclays Credit/Debit Card', WC_Cybersource_Barclays::TEXT_DOMAIN ),
		'desc_tip'      => true,
	),	
	'description' => array(
		'title'       => __( 'Description', WC_Cybersource_Barclays::TEXT_DOMAIN ),
		'type'        => 'textarea',
		'description' => __( 'Payment method description that the customer will see on payment Page.', WC_Cybersource_Barclays::TEXT_DOMAIN ),
		'default'     => __( 'Pay Securely with your Credit or Debit Card by using CyberSource provided by Barclays Bank PLC.', WC_Cybersource_Barclays::TEXT_DOMAIN ),
		'desc_tip'      => true,
	),
	'checkout_processing' => array(
		'title'       => __( 'Description on Checkout', WC_Cybersource_Barclays::TEXT_DOMAIN ),
		'type'        => 'textarea',
		'description' => __( 'This is the message the customer sees when the payment is processing as the page is submitted.', WC_Cybersource_Barclays::TEXT_DOMAIN ),
		'default'     => __( 'Thank you for your order.  Please DO NOT refresh your browser or click "BACK" while we are processing your payment otherwise you may be charged twice.', WC_Cybersource_Barclays::TEXT_DOMAIN ),
		'desc_tip'      => true,
	),
	'testmode' => array(
		'title'       => __( 'Test Mode', WC_Cybersource_Barclays::TEXT_DOMAIN ),
		'label'       => __( 'Enable Test Mode', WC_Cybersource_Barclays::TEXT_DOMAIN ),
		'type'        => 'checkbox',
		'description' => __( 'Enable the payment gateway in test mode.', WC_Cybersource_Barclays::TEXT_DOMAIN ),
		'default'     => 'yes',
		'desc_tip'      => true,
	),			
	'transaction_type' => array(
		'title'       => __( 'Transaction Type', WC_Cybersource_Barclays::TEXT_DOMAIN ),
		'type'        => 'select',
		'description' => __( 'Select which Transaction Type to use.', WC_Cybersource_Barclays::TEXT_DOMAIN ),
		'options'     => array( 'authorization' => __( 'Authorize Only', WC_Cybersource_Barclays::TEXT_DOMAIN ), 'sale' => __( 'Authorize &amp; Capture (sale)', WC_Cybersource_Barclays::TEXT_DOMAIN ) ),
		'default'     => 'authorization',
		'desc_tip'      => true,
	),
	'card_type'	=> array(
		'title'       => __( 'Accepted Cards', WC_Cybersource_Barclays::TEXT_DOMAIN ),
		'type'        => 'multiselect',
		'description' => __( 'Select which card types to accept.  Ensure these are enabled in your CyberSource Business Center Account by going to Tools &amp; Settings &gt; Profiles &gt; Profile Name &gt; Payment Settings', WC_Cybersource_Barclays::TEXT_DOMAIN ),
		'default'     => '',
		'options'     => $this->card_type_options,
		'desc_tip'      => true,
	),
	'banklogo' => array(
		'title'       => __( 'Display Bank Logo', WC_Cybersource_Barclays::TEXT_DOMAIN ),
		'label'       => __( 'Display the Bank Logo image on the Payment page.', WC_Cybersource_Barclays::TEXT_DOMAIN ),
		'type'        => 'checkbox',
		'description' => __( 'This gives comfort to the customer that it is a system provided by a well established bank.', WC_Cybersource_Barclays::TEXT_DOMAIN ),
		'default'     => 'no',
		'desc_tip'      => true,
	),
	'locale' => array(
		'title'       => __( 'Locale', WC_Cybersource_Barclays::TEXT_DOMAIN ),
		'type'        => 'select',
		'description' => __( 'Indicates the language to use for customer facing content.', WC_Cybersource_Barclays::TEXT_DOMAIN ),
		'options'     => array( 'ar-XN' => __( 'Arabic', WC_Cybersource_Barclays::TEXT_DOMAIN ),
								'km-KH' => __( 'Cambodia', WC_Cybersource_Barclays::TEXT_DOMAIN ),
								'zh-HK' => __( 'Chinese', WC_Cybersource_Barclays::TEXT_DOMAIN ),
								'cz-CZ' => __( 'Czech', WC_Cybersource_Barclays::TEXT_DOMAIN ),
								'nl-nl' => __( 'Dutch', WC_Cybersource_Barclays::TEXT_DOMAIN ),
								'en-GB' => __( 'English', WC_Cybersource_Barclays::TEXT_DOMAIN ), 
								'fr-FR' => __( 'French', WC_Cybersource_Barclays::TEXT_DOMAIN ), 
								'de-DE' => __( 'German', WC_Cybersource_Barclays::TEXT_DOMAIN ),
								'hu-HU' => __( 'Hungary', WC_Cybersource_Barclays::TEXT_DOMAIN ),
								'it-IT' => __( 'Italian', WC_Cybersource_Barclays::TEXT_DOMAIN ),
								'ja-JP' => __( 'Japanese', WC_Cybersource_Barclays::TEXT_DOMAIN ),
								'ko-KR' => __( 'Korean', WC_Cybersource_Barclays::TEXT_DOMAIN ),
								'pl-PL' => __( 'Polish', WC_Cybersource_Barclays::TEXT_DOMAIN ),
								'ru-RU' => __( 'Russian', WC_Cybersource_Barclays::TEXT_DOMAIN ),
								'es-ES' => __( 'Spanish', WC_Cybersource_Barclays::TEXT_DOMAIN )),
		'default'     => 'en',
		'desc_tip'      => true,
	),
		'currency' => array(
		'title'       => __( '', WC_Cybersource_Barclays::TEXT_DOMAIN ),
		'type'        => 'hidden',
		'description' => '',
		'default'     => 'USD',		
	),	
		'profile_keys'	=> array(
		'title'			=> __('CyberSource Barclays Profile Keys',WC_Cybersource_Barclays::TEXT_DOMAIN ),
		'type'			=> 'title',
		'description'	=> '',
		'desc_tip'		=> true,
	),	
	'merchant_id' => array(
		'title'       => __( 'Merchant ID', WC_Cybersource_Barclays::TEXT_DOMAIN ),
		'type'        => 'text',
		'description' => __('Your CyberSource Merchant Id.', WC_Cybersource_Barclays::TEXT_DOMAIN ),
		'default'     => '',
		'desc_tip'      => true,
	),		
	'test_org_id' => array(
		'title'       => __( 'Test Organization ID', WC_Cybersource_Barclays::TEXT_DOMAIN ),
		'type'        => 'text',
		'description' => __('Test Organization ID.', WC_Cybersource_Barclays::TEXT_DOMAIN ),
		'default'     => '',
		'desc_tip'      => true,
	),

	'live_org_id' => array(
		'title'       => __( 'Live Organization ID', WC_Cybersource_Barclays::TEXT_DOMAIN ),
		'type'        => 'text',
		'description' => __('Live Organization ID.', WC_Cybersource_Barclays::TEXT_DOMAIN ),
		'default'     => '',
		'desc_tip'      => true,
	),		
	'profile_id' => array(
		'title'       => __( 'Profile ID', WC_Cybersource_Barclays::TEXT_DOMAIN ),
		'type'        => 'text',
		'description' => __('The Profile Id for your live account. ', WC_Cybersource_Barclays::TEXT_DOMAIN ),
		'default'     => '',
		'desc_tip'      => true,
	),
	'profile_id_test' => array(
		'title'       => __( 'TEST Profile ID', WC_Cybersource_Barclays::TEXT_DOMAIN ),
		'type'        => 'text',
		'description' => __('The Profile Id for your test account.  ', WC_Cybersource_Barclays::TEXT_DOMAIN ),
		'default'     => '',
		'desc_tip'      => true,
	),
	'secret_key' => array(
		'title'       => __( 'Secret Key', WC_Cybersource_Barclays::TEXT_DOMAIN ),
		'type'        => 'password',
		'description' => __("The Secret Key for your live account.", WC_Cybersource_Barclays::TEXT_DOMAIN ),
		'default'     => '',
		'desc_tip'      => true,
	),
	'secret_key_test' => array(
		'title'       => __( 'TEST Secret Key', WC_Cybersource_Barclays::TEXT_DOMAIN ),
		'type'        => 'password',
		'description' => __("The Secret Key for your test account.", WC_Cybersource_Barclays::TEXT_DOMAIN ),
		'default'     => '',
		'desc_tip'      => true,
	),
	'access_key' => array(
		'title'       => __( 'Access Key', WC_Cybersource_Barclays::TEXT_DOMAIN ),
		'type'        => 'text',
		'description' => __("The Access Key for your live account.", WC_Cybersource_Barclays::TEXT_DOMAIN ),
		'default'     => '',
		'desc_tip'      => true,
	),
	'access_key_test' => array(
		'title'       => __( 'TEST Access Key', WC_Cybersource_Barclays::TEXT_DOMAIN ),
		'type'        => 'text',
		'description' => __("The Access Key for your test account.", WC_Cybersource_Barclays::TEXT_DOMAIN ),
		'default'     => '',
		'desc_tip'      => true,
	),
/*	'debug' => array(
		'title'       => '',
		'label'       => '',
		'type'        => 'hidden',
		'default'     => 'no',	
		'class'				=>'hidden_field_div',
	), */
	'log' => array(
		'title'       => '',
		'label'       => '',
		'type'        => 'hidden',
		'default'     => 'no',	
		'class'				=>'hidden_field_div',	
	),
	'device_finger_print' => array(
		'title'       => '',
		'label'       =>'',
		'type'        => 'hidden',	
		'default'     => 'no',
		'class'				=>'hidden_field_div',
	),
);

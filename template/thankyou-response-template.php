<?php 
/* Template Name: Thankyou Response Page Template */ 


ob_start();
get_header();

/* code for thank you page after redirect */
echo "<center><h1>Thank You</h1></center><br>";

$status_msg = WC_Gateway_Cybersource_Barclays::cybersource_response();
//print_r($_POST);
?>
<p style="width: 100%;text-align: center;font-size: 20px;font-weight: 600;"> <?php echo $status_msg; ?></p>

<?php
get_footer();
?>
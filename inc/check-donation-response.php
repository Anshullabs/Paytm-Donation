<?php 
/**
 * Check Donation(Payment) Response
 *
 * @package : Paytm Donation
 * @author  : Anshul G.
 * @version : 1.0
 * @link    : http://anshullabs.xyz
 */

/**
 * This function use for chceck payment status
 * and redirect with payment status msg.
 */
add_action('init', 'pd_paytm_donation_response');
function pd_paytm_donation_response(){
	
	if(! empty($_POST) && isset($_POST['ORDERID'])){

		global $wpdb;
		extract(
					array(
						'paytm_merchant_id' => trim(get_option('paytm_merchant_id')),
						'paytm_merchant_key' => trim(get_option('paytm_merchant_key')),
						'paytm_website' => trim(get_option('paytm_website')),
						'paytm_industry_type_id' => trim(get_option('paytm_industry_type_id')),
						'paytm_channel_id' => trim(get_option('paytm_channel_id')),
						'paytm_mode' => get_option('paytm_mode'),
						'paytm_callback' => trim(get_option('paytm_callback')),
						'paytm_amount' => trim(get_option('paytm_amount')),		
						'paytm_content' => trim(get_option('paytm_content'))						
					)
				);

		if( verifychecksum_e( $_POST, $paytm_merchant_key, $_POST['CHECKSUMHASH'] ) === "TRUE" ){
			if($_POST['RESPCODE'] =="01"){
				// Create an array having all required parameters for status query.
				$requestParamList = array("MID" => $paytm_merchant_id , "ORDERID" => $_POST['ORDERID']);
				$StatusCheckSum = getChecksumFromArray($requestParamList, $paytm_merchant_key);
				$requestParamList['CHECKSUMHASH'] = $StatusCheckSum;

				// Call the PG's getTxnStatus() function for verifying the transaction status.
				$check_status_url = 'https://securegw-stage.paytm.in/merchant-status/getTxnStatus';
				if( $paytm_mode == 'LIVE' ){
					$check_status_url = 'https://securegw.paytm.in/merchant-status/getTxnStatus';
				}
				
				$responseParamList = callNewAPI($check_status_url, $requestParamList);
				if($responseParamList['STATUS']=='TXN_SUCCESS' && $responseParamList['TXNAMOUNT']==$_POST['TXNAMOUNT']){
					$wpdb->query($wpdb->prepare("UPDATE FROM " . $wpdb->prefix . "paytm_donations WHERE order_id = %d", $_POST['ORDERID']));
					$wpdb->query($wpdb->prepare("UPDATE ".$wpdb->prefix . "paytm_donations SET payment_status = 'Complete Payment' WHERE  order_id = %d", $_POST['ORDERID']));
					$msg = "Thank you for your donation . Your transaction has been successful.";
				}
				else{
					$msg= "It seems some issue in server to server communication. Kindly connect with administrator.";
					$wpdb->query($wpdb->prepare("UPDATE ".$wpdb->prefix . "paytm_donations SET payment_status = 'Fraud Payment' WHERE  order_id = %d", $_POST['ORDERID']));
				}
			}else{
				$msg= "Thank You. However, the transaction has been Failed For Reason  : "  . sanitize_text_field($_POST['RESPMSG']);
				$wpdb->query($wpdb->prepare("UPDATE ".$wpdb->prefix . "paytm_donations SET payment_status = 'Canceled Payment' WHERE  order_id = %d", $_POST['ORDERID']));
			}
		}else{
			$msg= "Security error!";
			$wpdb->query($wpdb->prepare("UPDATE ".$wpdb->prefix . "paytm_donations SET payment_status = 'Payment Error' WHERE  order_id = %d", $_POST['ORDERID']));
		}
		$redirect_url = get_site_url() . '/' . get_permalink( get_the_ID() );//echo $redirect_url ."<br />";
		$redirect_url = add_query_arg( array( 'donation_msg'=> urlencode($msg), 'pstatus' => $_POST['STATUS'] ));
		wp_redirect( $redirect_url, 301 );exit;
	}
}
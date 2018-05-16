<?php 
/**
 * Paytm Donation Checkout Page 
 *
 * @package : Paytm Donation
 * @author  : Anshul G.
 * @version : 1.0
 * @link    : http://anshullabs.xyz
 */



/**
 * This function use for chceck validation then insert donner details in database
 * and redirect to paytm payment page. 
 *
 * @return html
 */
function pd_paytm_donation_checkout(){
	if( !empty($_POST) && isset($_POST['donate-submit']) && $_GET['pd_donation'] == 'true'  ){
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
		
		$valid = true;
		$html='';
		$msg='<div class="alert alert-warning">';

		if( $_POST['donor_name'] != ''){
			$donor_name = $_POST['donor_name'];
		}
		else{
			$valid = false;
			$msg  .= 'Name is required </br>';
		}
	
		if( $_POST['donor_email'] != ''){
			$donor_email = $_POST['donor_email'];
			if( preg_match("/([\w\-]+\@[\w\-]+\.[\w\-]+)/" , $donor_email)){}
			else{
				$valid = false;
				$msg .= 'Invalid email format </br>';
			}
		}
		else{
			$valid = false;
			$msg .= 'E-mail is required </br>';
		}
		
		if( $_POST['donor_amount'] != ''){
			$donor_amount = $_POST['donor_amount'];

			if( (is_numeric($donor_amount)) ){
				if( (strlen($donor_amount) > '1') || (strlen($donor_amount) == '1') ){
				}
				else{
					$valid = false;
					$msg .= 'Amount cannot be less then Rs.1</br>';
				}
			}
			else{
				$valid = false;
				$msg .= 'Amount must be numeric</br>';
			} 
		}
		else{
			$valid = false;
			$msg .= 'Amount is required </br>';
		}

		$msg .= '</div>';
		
		// check validation 
		if($valid){
			$table_name = $wpdb->prefix . "paytm_donations";
			$order_id = "ORDS" . rand(10000,99999999);
			$data = array(
                        'order_id' 	=> sanitize_text_field($order_id),
                        'name' 		=> sanitize_text_field($_POST['donor_name']),
                        'phone' 	=> sanitize_text_field($_POST['donor_phone']),
                        'email' 	=> sanitize_text_field($_POST['donor_email']), 
                        'address' 	=> sanitize_text_field($_POST['donor_address']),
                        'city' 		=> sanitize_text_field($_POST['donor_city']),
                        'state' 	=> sanitize_text_field($_POST['donor_state']),
                        'zip' 		=> sanitize_text_field($_POST['donor_postal_code']),
                        'country' 	=> sanitize_text_field($_POST['donor_country']),
                        'amount' 	=> sanitize_text_field($_POST['donor_amount']),
                        'pan_no' 	=> sanitize_text_field($_POST['donor_pancard']),
                        'post_id' 	=> sanitize_text_field($_POST['pd_postID']),
                        'payment_status' => 'Pending Payment',
                        'date' 		=>	date('Y-m-d H:i:s'),
					);
			// insert record in database 
			$wpdb->insert($table_name, $data);

			// payment array
			$post_params = array(
						'MID' => $paytm_merchant_id,
						'ORDER_ID' => $order_id,
						'WEBSITE' => $paytm_website,
						'CHANNEL_ID' => $paytm_channel_id,
						'INDUSTRY_TYPE_ID' => $paytm_industry_type_id,
						'TXN_AMOUNT' => $_POST['donor_amount'],
						'CUST_ID' => $_POST['donor_email'],
						'EMAIL' => $_POST['donor_email'],
					);

			if($paytm_callback=='YES'){
				$post_params["CALLBACK_URL"] = get_permalink();
			}

			// create check sum for payment process 
			$checkSum 	 = getChecksumFromArray ($post_params,$paytm_merchant_key);
			$callbackURL = get_permalink($_POST['pd_postID']);
			
			// Create action url base on payment mode.
			$action_url="https://securegw-stage.paytm.in/theia/processTransaction?orderid=$order_id";
			if($paytm_mode == 'LIVE'){
				$action_url="https://securegw.paytm.in/theia/processTransaction?orderid=$order_id";
			}

			if($paytm_callback=='YES'){
				$html= <<<EOF
						<div id="paytmd-checkout-wrap" class="pd-wrap">
							<div class="container" style="max-width: 680px;">
								<h1>Please do not refresh this page...</h1>
								<p class="lead">Please do not refresh the page and wait while we are processing your payment. This can take a few minutes.</p>
							</div>
							<form method="post" action="$action_url" name="f1">
								<input type="hidden" name="MID" value="$paytm_merchant_id">
								<input type="hidden" name="WEBSITE" value="$paytm_website">
								<input type="hidden" name="CHANNEL_ID" value="$paytm_channel_id">
								<input type="hidden" name="ORDER_ID" value="$order_id">
								<input type="hidden" name="INDUSTRY_TYPE_ID" value="$paytm_industry_type_id">
								<input type="hidden" name="TXN_AMOUNT" value="{$_POST['donor_amount']}">
								<input type="hidden" name="CUST_ID" value="{$_POST['donor_email']}">
								<input type="hidden" name="EMAIL" value="{$_POST['donor_email']}">
								<input type="hidden" name="CALLBACK_URL" value="$callbackURL">
								<input type="hidden" name="CHECKSUMHASH" value="$checkSum">
								<script type="text/javascript">
									document.f1.submit();
								</script>
							</form>
						</div>							
EOF;
			}else{
				$html= <<<EOF
						<div id="paytmd-checkout-wrap" class="pd-wrap">
							<div class="container" style="max-width: 680px;">
								<h1>Please do not refresh this page...</h1>
								<p class="lead">Please do not refresh the page and wait while we are processing your payment. This can take a few minutes.</p>
							</div>
							<form method="post" action="$action_url" name="f1">							
								<input type="hidden" name="MID" value="$paytm_merchant_id">
								<input type="hidden" name="WEBSITE" value="$paytm_website">
								<input type="hidden" name="CHANNEL_ID" value="$paytm_channel_id">
								<input type="hidden" name="ORDER_ID" value="$order_id">
								<input type="hidden" name="INDUSTRY_TYPE_ID" value="$paytm_industry_type_id">
								<input type="hidden" name="TXN_AMOUNT" value="{$_POST['donor_amount']}">
								<input type="hidden" name="CUST_ID" value="{$_POST['donor_email']}">
								<input type="hidden" name="EMAIL" value="{$_POST['donor_email']}">
								<input type="hidden" name="CHECKSUMHASH" value="$checkSum">
								<script type="text/javascript">
									document.f1.submit();
								</script>
							</form>
						</div>
EOF;
			}
			//return paytm hidden form for checkout.
			return $html;
		}else{
			//show error msg
			return $msg;
		}
	}else{
		return;
	}
}
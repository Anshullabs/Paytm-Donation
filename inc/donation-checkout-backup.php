<?php 

//add_action('init', 'pd_paytm_donation_checkout');
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
						'paytm_amount'   => trim(get_option('paytm_amount')),		
						'paytm_content'  => trim(get_option('paytm_content'))						
					)
				);


		//'transaction_url' => get_option('transaction_url'),
		//'transaction_status_url' => get_option('transaction_status_url'),
		if ($_POST['donate-submit'] == $paytm_content) {
			$valid = true;
			$html='';
			$msg='';

			if( $_POST['donor_name'] != ''){
				$donor_name = $_POST['donor_name'];
			}
			else{
				$valid = false;
				$msg.= 'Name is required </br>';
			}
		
			if( $_POST['donor_email'] != ''){
				$donor_email = $_POST['donor_email'];
				if( preg_match("/([\w\-]+\@[\w\-]+\.[\w\-]+)/" , $donor_email)){}
				else{
					$valid = false;
					$msg.= 'Invalid email format </br>';
				}
			}
			else{
				$valid = false;
				$msg.= 'E-mail is required </br>';
			}
			
			if( $_POST['donor_amount'] != ''){
				$donor_amount = $_POST['donor_amount'];
				if( (is_numeric($donor_amount)) && ( (strlen($donor_amount) > '1') || (strlen($donor_amount) == '1')) ){}
				else{
					$valid = false;
					$msg.= 'Amount cannot be less then Rs.1</br>';
				}
			}
			else{
				$valid = false;
				$msg.= 'Amount is required </br>';
			}

			if( $_POST['donor_pancard'] != ''){    
                $pancard = $_POST['donor_pancard']; //PUT YOUR PAN CARD NUMBER HERE
                $pattern = '/^([a-zA-Z]){5}([0-9]){4}([a-zA-Z]){1}?$/';
                $result = preg_match($pattern, $pancard);
                if ($result) {
                    
                } else {
                    $valid = false;    
                    $msg .= "PAN Card is Not Valid";
                }
            }

            //echo $msg;


			// check not get any validation error.
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

				$wpdb->insert($table_name, $data);
				
				$post_params = 	array(
									'MID' => $paytm_merchant_id,
									'ORDER_ID' => $order_id,
									'WEBSITE' => $paytm_website,
									'CHANNEL_ID' => $paytm_channel_id,
									'INDUSTRY_TYPE_ID' => $paytm_industry_type_id,
									'TXN_AMOUNT' => sanitize_text_field($_POST['donor_amount']),
									'CUST_ID' => sanitize_text_field($_POST['donor_email']),
									'EMAIL' => sanitize_text_field($_POST['donor_email']), 
								);
				


				if($paytm_callback == 'YES'){
					$post_params["CALLBACK_URL"] = get_permalink($_POST['pd_postID']);
				}

				// create checksum
				$checkSum = getChecksumFromArray ( $post_params, $paytm_merchant_key);
				
				// set callback url
				$callback_url = get_permalink($_POST['pd_postID']);

				// create action url using transaction_url via check payment mode live or test.
				$transaction_url = "https://securegw-stage.paytm.in/theia/processTransaction";
                if($paytm_mode == 'LIVE'){
                    $transaction_url="https://securegw.paytm.in/theia/processTransaction";
                }

				$action_url=$transaction_url."?orderid=$order_id";

                ob_start();
                ?>
                	
                	<div class="pd-wrap">
                		<div class="row">
	                		<main role="main" class="container" style="max-width: 680px;">
								<h1 class="mt-5">Please do not refresh this page...</h1>
								<p class="lead">Please wait while the system processes your request.<br> Please do not click the refresh, back and stop button until selection page is displayed. <br> It may take up to 60 seconds to process your request. </p>
								
								<form method="post" action="<?php echo $action_url; ?>" name="f1">
									<input type="hidden" name="MID" value="<?php echo $paytm_merchant_id; ?>">
							        <input type="hidden" name="WEBSITE" value="<?php echo $paytm_website;?> ">
							        <input type="hidden" name="CHANNEL_ID" value="<?php echo $paytm_channel_id; ?>">
							        <input type="hidden" name="ORDER_ID" value="<?php echo $order_id; ?>">
							        <input type="hidden" name="INDUSTRY_TYPE_ID" value="<?php echo $paytm_industry_type_id; ?>">									
							        <input type="hidden" name="TXN_AMOUNT" value="<?php echo $donor_amount; ?>">
							        <input type="hidden" name="CUST_ID" value="<?php echo $donor_email; ?>">
							        <input type="hidden" name="EMAIL" value="<?php echo $donor_email; ?>">
							        <input type="hidden" name="CHECKSUMHASH" value="<?php echo $checkSum; ?>">
							        <?php if($paytm_callback == 'YES'): ?>
							        	<input type="hidden" name="CALLBACK_URL" value="<?php echo $callback_url; ?>">							        	
							        <?php endif ?>
								</form>
								<script type="text/javascript">
									//document.f1.submit();
								</script>
						    </main>
	                	</div>
                	</div>				
				<?php
				return ob_get_clean();
			}else{
				return $msg;				
			}
		}
	}
	else{
		return;
	}
}





























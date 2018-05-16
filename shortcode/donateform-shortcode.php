<?php 

// Create Shortcode paytm-donation
// Use the shortcode: [paytm-donation]
add_shortcode( 'paytm-donation', 'create_paytmdonation_shortcode' );
function create_paytmdonation_shortcode() {
	$paytm_amount  = trim( get_option('paytm_amount') );		
	$paytm_content = trim( get_option('paytm_content') );						
				
	// Your Code
	ob_start();
	?>
	<div id="paytmd-wrap" class="pd-wrap">

		<?php if ( isset($_REQUEST['donation_msg']) && isset($_REQUEST['pstatus']) ): ?>			
			<div class="alert alert-<?php echo ($_REQUEST['pstatus'] == 'TXN_SUCCESS' )?'success':'warning';?>">
				<?php echo $_REQUEST['donation_msg']; ?>
			</div>
		<?php endif; ?>
	
		<?php echo pd_paytm_donation_checkout(); ?>

		<div class="row">
			<div id="post-body" class="p-2 col-11 ml-3">
				<form class="needs-validation" name="frmTransaction" method="post" action="<?php the_permalink(); ?>?pd_donation=true">
            		<div class="row">
						<div class="col-md-4 mb-3">
							<label for="donor_amount" class="font-weight-bold">DONATION AMOUNT</label>
							<div class="input-group">
				                <div class="input-group-prepend">
				                	<span class="input-group-text">Rs.</span>
				                </div>
				                <input type="text" class="form-control" id="donor_amount" placeholder="Donation Amount (In Rs.)" name="donor_amount" value="<?php echo $paytm_amount;?>" required>
				        	</div>							
						</div>
						<div class="col-md-8 mb-3">
							<label for="donor_name" class="font-weight-bold">FULL NAME</label>
							<input type="text" class="form-control" id="donor_name" name="donor_name" required>
						</div>
              		</div>
              		<div class="row">
						
						<div class="col-md-4 mb-3">
							<label for="donor_phone" class="font-weight-bold">PHONE NUMBER</label>
							<div class="input-group">
				                <div class="input-group-prepend">
				                	<span class="input-group-text">+91</span>
				                </div>
				                <input type="tel" class="form-control" id="donor_phone" name="donor_phone" maxlength="10" required>
				        	</div>
						</div>
						<div class="col-md-4 mb-3">
							<label for="donor_email" class="font-weight-bold">E-MAIL ADDRESS</label>
							<input type="email" class="form-control" id="donor_email" name="donor_email" required>
						</div>
						<div class="col-md-4 mb-2">
							<label for="donor_pancard" class="font-weight-bold">PAN CARD NUMBER</label>
							<input type="text" class="form-control" id="donor_pancard" name="donor_pancard" required>
						</div>
              		</div>
              		<div class="row">
						<div class="col-md-4 mb-3">
							<label for="donor_address" class="font-weight-bold">ADDRESS</label>
							<input type="text" class="form-control" id="donor_address" name="donor_address">
						</div>
						<div class="col-md-4 mb-3">
							<label for="donor_city" class="font-weight-bold">CITY</label>
							<input type="text" class="form-control" id="donor_city" name="donor_city" required>
						</div>
						<div class="col-md-4 mb-3">
							<label for="donor_state" class="font-weight-bold">STATE</label>
							<input type="text" class="form-control" id="donor_state" name="donor_state" required>
						</div>
              		</div>
              		<div class="row">
						<div class="col-md-4 mb-2">
							<label for="donor_postal_code" class="font-weight-bold">PIN CODE</label>
							<input type="tel" class="form-control" id="donor_postal_code" name="donor_postal_code" required>
						</div>
						<div class="col-md-4 mb-2">
							<label for="donor_country" class="font-weight-bold">COUNTRY</label>
							<input type="text" class="form-control" id="donor_country" name="donor_country" required>
						</div>						
              		</div>              		
            		<hr class="mb-4">
            		<input type="hidden" name="pd_postID" value="<?php echo get_the_ID(); ?>">
            		<input class="btn btn-primary btn-lg btn-block" name="donate-submit" type="submit" value="<?php echo $paytm_content; ?>" />
          		</form>
			</div>
		</div>
	</div>
	<?php
	return ob_get_clean();
}
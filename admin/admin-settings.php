<?php 
 /**
  * Paytm Donation Setting Page
  *
  * @package : Paytm Donation
  * @author  : Anshul G.
  * @version : 1.0
  */
?>

<div class="wrap">
	<h2>Paytm Configuarations</h2>
	<div id="paytmd-wrap" class="pd-wrap">
		<div class="row">			
			<div id="post-body" class="p-4 col-6 bg-white rounded border ml-3 mt-2">				
				<form method="post" action="options.php">
					<?php wp_nonce_field('update-options'); ?>
					<?php $pd_settings = pd_paytm_settings_list(); ?>
					<?php foreach ($pd_settings as $setting) : ?>
						<div class="form-group row">
						    <label for="inputEmail3" class="col-3 col-form-label font-weight-bold"><?php echo $setting['display']; ?></label>
							<div class="col-9">
								<?php 
								if ($setting['type']=='radio') {
									echo $setting['yes'].' <input type="'.$setting['type'].'" name="'.$setting['name'].'" value="1" ';
									if (get_option($setting['name'])==1) { echo 'checked="checked" />'; } else { echo ' />'; }
									echo $setting['no'].' <input type="'.$setting['type'].'" name="'.$setting['name'].'" value="0" ';
									if (get_option($setting['name'])==0) { echo 'checked="checked" />'; } else { echo ' />'; }
								} elseif ($setting['type']=='select') {
									$values=$setting['values'];
									echo '<select class="form-control" name="'.$setting['name'].'">';
									foreach ($values as $value=>$name) {
										echo '<option value="'.$value.'" ';
										if (get_option($setting['name'])==$value) { echo ' selected="selected" ';}
										echo '>'.$name.'</option>';
									}
									echo '</select>';
								} else { echo '<input type="'.$setting['type'].'" name="'.$setting['name'].'" value="'.get_option($setting['name']).'" class="form-control" placeholder="'.$setting['display'].'" />'; }
								?>
								<small id="emailHelp" class="form-text text-muted"><?php echo $setting['hint']; ?></small>							
							</div>
						</div>	
					<?php endforeach ?>
					<div class="form-group row">
					    <div class="col-3 offset-3">
					      <input type="submit" class="btn btn-primary" value="Save Changes" />
					      <input type="hidden" name="action" value="update" />
					      <input type="hidden" name="page_options" value="<?php foreach ($pd_settings as $setting) { echo $setting['name'].','; } ?>" />
					    </div>
				  	</div>
				</form>
			</div><!-- /#post-body -->
		</div>
	</div><!-- /.pd-wrap -->
</div>s
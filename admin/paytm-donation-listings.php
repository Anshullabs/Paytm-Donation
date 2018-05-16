<?php 
 /**
  * Paytm Donation Donation Listing Page
  *
  * @package : Paytm Donation
  * @author  : Anshul G.
  * @version : 1.0
  */
?>


<div class="wrap">
    <?php echo "<h2>" . __( 'Paytm Donation Details' )."</h2>"; ?>
    <?php 
        $wp_list_table = new PayTM_Donation_List_Table();
        $wp_list_table->prepare_items();
        $TotalListRecord = $wp_list_table->prepare_items();
        $wp_list_table->display();
    ?>
</div>
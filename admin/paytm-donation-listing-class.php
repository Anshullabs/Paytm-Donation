<?php 
 /**
  * Paytm Donation Donation Listing Class
  *
  * @package : Paytm Donation
  * @author  : Anshul G.
  * @version : 1.0
  */
?>

<?php

// WP_List_Table is not loaded automatically so we need to load it in our application
if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * Create a new table class that will extend the WP_List_Table
 */
class PayTM_Donation_List_Table extends WP_List_Table
{
	function __construct() {
		parent::__construct( array(
			'singular'=> 'Paytm Donation Details', //Singular label
			'plural' => 'Paytm Donation Details', //plural label, also this well be one of the table css class
			'ajax'	=> false //We won't support Ajax for this table
		) );
	}
	
	/**
     * Override the parent columns method. Defines the columns to use in your listing table
     *
     * @return Array
     */
	public function get_columns() {
		return $columns= array(
			'order_id'		  =>  __('Order Id'),
			'name'			  =>  __('Name'),
			'phone'			  =>  __('Phone'),
			'email'			  =>  __('Email'),
			'address'		  =>  __('Address'), 
			'city'		  	  =>  __('City'), 
			'state'		  	  =>  __('State'), 
			'zip'		  	  =>  __('Zip'), 
			'country'		  =>  __('Country'), 
			'amount'		  =>  __('Donation (In Rs.)'),
            'pan_no'		  =>  __('PAN Card'),
			'payment_status'  =>  __('Payment Status'),			
			'date'			  =>  __('Date'),
		);
	}
	
	
	/**
     * Override the parent sortable method. Defines the sortable columns to use in your listing table
     *
     * @return Array
     */
	public function get_sortable_columns() {
        $sortable_columns = array(
            'order_id'  => array('order_id', false),
            'name' 		=> array('name', false), 
            'date' 		=> array('date', false)
        );
        return $sortable_columns;
    }


    /**
     * Prepare the items for the table to process
     *
     * @return Void
     */
	public function prepare_items(){
        global $wpdb, $_wp_column_headers;
        $screen   = get_current_screen();
        $columns  = $this->get_columns();
        $hidden   = array();
        $sortable = $this->get_sortable_columns();               
        $this->_column_headers = array($columns, $hidden, $sortable);    

        $table_data = $wpdb->prefix."paytm_donations";
        $query = "SELECT * FROM $table_data";
		
        /* -- Ordering parameters -- */
	    //Parameters that are going to be used to order the result
	    $orderby = !empty($_GET["orderby"]) ? mysql_real_escape_string($_GET["orderby"]) : 'date';
	    $order   = !empty($_GET["order"]) ? mysql_real_escape_string($_GET["order"]) : 'DESC';
	    if(!empty($orderby) & !empty($order)){ 
	    	$query.=' ORDER BY '.$orderby.' '.$order; 
	    }
		
        /* -- Pagination parameters -- */
        //Number of elements in your table?
        $totalitems = $wpdb->query($query); //return the total number of affected rows

        //How many to display per page?
        $perpage = 10;

        // Which page is this?
        $paged = !empty($_GET["paged"]) ? mysql_real_escape_string($_GET["paged"]) : '';

        // Page Number
        if(empty($paged) || !is_numeric($paged) || $paged<=0 ){ $paged = 1; }

        // How many pages do we have in total?
        $totalpages = ceil($totalitems/$perpage);

        //adjust the query to take pagination into account
	    if(!empty($paged) && !empty($perpage)){
		    $offset = ($paged-1) * $perpage;
    		$query .= ' LIMIT '.(int)$offset.','.(int)$perpage;
	    }
            
        /* -- Register the pagination -- */
        $this->set_pagination_args( array(
            "total_items" => $totalitems,
            "total_pages" => $totalpages,
            "per_page" => $perpage,
        ) );

        //The pagination links are automatically built according to those parameters
        /* -- Register the Columns -- */
        $columns = $this->get_columns();
        $_wp_column_headers[$screen->id] = $columns;

        /* -- Fetch the items -- */
        $data = $wpdb->get_results($query, ARRAY_A);

        $this->items = $data;

        return count($this->items);
	}
	
    /**
     * Define what data to show on each column of the table
     *
     * @param  Array $item        Data
     * @param  String $column_name - Current column name
     *
     * @return Mixed
     */
	public function column_default( $item, $column_name ){
		return $item[ $column_name ];
	}
	
} //class PayTM_Donation_List_Table

?>
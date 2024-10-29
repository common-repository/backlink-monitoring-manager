<?php
/**
 * Administration API: WP_List_Table class
 *
 * @package WordPress
 * @subpackage List_Table
 * @since 3.1.0
 */

/**
 * Base class for displaying a list of items in an ajaxified HTML table.
 *
 * @since 3.1.0
 * @access private
 */

class Back_Link_Monitoring_Manager_Child_WP_List_Table extends Back_Link_Monitoring_Manager_Parent_WP_List_Table {
    
	private $plugin_name;
    
	public function __contruct( $plugin_name ) {
        
        global $status, $page;
		//Set parent defaults
		parent::__construct(
			array(
				'singular'	=> 'link',
				'plural'	=> 'links',
				'ajax'		=> true
			)
		);
		$this->plugin_name = $plugin_name;
    }

	public function column_default( $item, $column_name ) {

        switch ( $column_name ) {
			case 'id':
            case 'date':
            case 'toLink':
			case 'fromLink':
			case 'anchor_text':
            case 'do_follow':
            case 'status':
				return $item[ $column_name ];
			 case 'blm_remove_link':
				return '<a href="#" class="blm-delete-link" id="' . $item['id'] .'"><span class="dashicons dashicons-trash"></span></a>';
			default:
				return print_r( $item, true );
		}
	}

	public function get_columns() {

		return $columns = array(
			'date'  	  	  => __('Date', $this->plugin_name ),
			'toLink'   	      => __('Link To', $this->plugin_name ),
			'fromLink'    	  => __('Link From', $this->plugin_name ),
			'anchor_text' 	  => __('Anchor Text', $this->plugin_name ),
			'do_follow'   	  => __('Do Follow', $this->plugin_name ),
			'status' 		  => __('Status', $this->plugin_name ),
			'blm_remove_link' => '',
		);
	}

	public function get_sortable_columns()
	{
		return array(
			'date'  => array( 'date', true ),
			'toLink'  => array( 'toLink', true ),
			'fromLink'  => array( 'fromLink', true ),
			'anchor_text'  => array( 'anchor_text', true ),
			'do_follow'  => array( 'do_follow', true ),
            'status'  => array( 'status', true ),
		);
	}

	public function prepare_items() {

		global $wpdb;
		$per_page = 20;
		$columns = $this->get_columns();
		$hidden = array();
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );
		$this->process_bulk_action();
		$data = ( get_option( 'backlink_monitoring_manager_links' ) ) ? get_option( 'backlink_monitoring_manager_links' ) : false;
		$temp_data = array();
		$search_data = array();
		$main_search_data = array();

		if( $data ) {

			if ( ! empty( $_REQUEST['search_field'] ) && ! empty( $_REQUEST['search_column'] ) && ! isset( $_REQUEST['deletion_id'] ) ) {

				$search_text = sanitize_text_field( $_REQUEST['search_field'] );
				$search_column = sanitize_text_field( $_REQUEST['search_column'] );

				foreach ( $data as $key => $value ) {
					foreach ( $value as $column => $column_value ) {
						if ( $search_column == $column ) {
							$search_string = strpos( $column_value, $search_text );
							if ( $search_string !== false ) {

								array_push( $search_data, array( 
										'id' => $value['id'], 
										'date' => $value['date'], 
										'toLink' => $value['toLink'],
										'fromLink' => $value['fromLink'],
										'anchor_text' => $value['anchor_text'],
										'do_follow' => $value['do_follow'],
										'status' => $value['status'],
									) 
								);
							}
						}
					}
				}
				$main_search_data = $search_data;
			} elseif ( isset( $_REQUEST['deletion_id'] ) && ! empty( $_REQUEST['deletion_id'] ) ) {
				$deletion_id = sanitize_text_field( $_REQUEST['deletion_id'] );
				foreach ( $data as $key => $value ) {
					foreach ( $value as $column => $column_value ) {
						if ( $deletion_id == $column_value ) {
							unset( $data[ $key ] );
							break;
						}
					}
				}
				update_option( 'backlink_monitoring_manager_links', $data );
				$main_search_data = $data;
			} else {
				$main_search_data = $data;
			}
		}

		function usort_reorder( $a, $b ) {
			$orderby = ( ! empty( $_REQUEST['orderby'] ) ) ? $_REQUEST['orderby'] : 'date';
			$order = ( ! empty( $_REQUEST['order'] ) ) ? $_REQUEST['order'] : 'asc';
			$result = strcmp( $a[ $orderby ], $b[ $orderby ] );
			return ( 'asc' === $order ) ? $result : -$result; 
		}
		if(  $main_search_data ) {
			usort( $main_search_data, 'usort_reorder' );
		}

		$current_page = $this->get_pagenum();
		$total_items = count( $main_search_data );
		
		if ( $main_search_data ) {
			$main_search_data = array_slice( $main_search_data,( ( $current_page-1 ) * $per_page ), $per_page );
		}

		$this->set_pagination_args(
			array(
				'total_items'	=> $total_items,
				'per_page'	=> $per_page,
				'total_pages'	=> ceil( $total_items / $per_page ),
				'orderby'	=> ! empty( $_REQUEST['orderby'] ) && '' != $_REQUEST['orderby'] ? $_REQUEST['orderby'] : 'date',
				'order'		=> ! empty( $_REQUEST['order'] ) && '' != $_REQUEST['order'] ? $_REQUEST['order'] : 'asc'
			)
		);
		$this->items = $main_search_data;
	}

	public function display() {

		echo '<input type="hidden" id="order" name="order" value="' . $this->_pagination_args['order'] . '" />';
		echo '<input type="hidden" id="orderby" name="orderby" value="' . $this->_pagination_args['orderby'] . '" />';
		parent::display();
	}

	public function ajax_response() {

		check_ajax_referer( 'blm-ajax-custom-list-nonce', 'blm_ajax_custom_list_nonce' );

		$this->prepare_items();

		extract( $this->_args );
		extract( $this->_pagination_args, EXTR_SKIP );

		ob_start();
		if ( ! empty( $_REQUEST['no_placeholder'] ) )
			$this->display_rows();
		else
			$this->display_rows_or_placeholder();
		$rows = ob_get_clean();

		ob_start();
		$this->print_column_headers();
		$headers = ob_get_clean();

		ob_start();
		$this->pagination('top');
		$pagination_top = ob_get_clean();

		ob_start();
		$this->pagination('bottom');
		$pagination_bottom = ob_get_clean();

		$response = array( 'rows' => $rows );
		$response['pagination']['top'] = $pagination_top;
		$response['pagination']['bottom'] = $pagination_bottom;
		$response['column_headers'] = $headers;

		if ( isset( $total_items ) )
			$response['total_items_i18n'] = sprintf( _n( '1 item', '%s items', $total_items ), number_format_i18n( $total_items ) );

		if ( isset( $total_pages ) ) {
			$response['total_pages'] = $total_pages;
			$response['total_pages_i18n'] = number_format_i18n( $total_pages );
		}

		wp_die( json_encode( $response ) );
	}
}
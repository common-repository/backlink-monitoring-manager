<?php

class Backlink_Monitoring_Manager_Admin {
  
  private $version;
  private $plugin_name;
  private $option_name;

  public function __construct( $version, $plugin_name ) { 
    $this->version = $version;
    $this->plugin_name = $plugin_name;
    $this->option_name = 'backlink_monitoring_manager_links';
  }

  public function admin_enqueue_scripts( $hook ) {
    
    if ( 'toplevel_page_backlink-monitoring' === $hook ) {
      
      wp_enqueue_script(
        'backlins_monitoring_admin_scripts',
        plugin_dir_url( __FILE__ ) . 'js/backlink-monitoring-admin.js',
        array(
          'jquery'
          ),
          $this->version,
          true
      );

      wp_localize_script(
        'backlins_monitoring_admin_scripts', 
        'BLMP_Plugin_Localize', 
        array(
          'ajax_url' => admin_url( 'admin-ajax.php' ),
          'blm_ajax_nonce_name' => wp_create_nonce( 'blm-ajax-custom-list-nonce' ),
          'content' => __( "Loading Links....", $this->plugin_name ),
          'imgsrc'  => plugin_dir_url(__FILE__).'/img/loading.gif',
        )
      );
      wp_enqueue_script( 'st-jquery-confirm', plugin_dir_url( __FILE__ ) . 'js/backlink-monitoring-jquery-confirm.js', array('jquery'), $this->version, true );
    }
  }

  public function admin_enqueue_styles( $hook ) {
    
    if ( 'toplevel_page_backlink-monitoring' === $hook ) {
      wp_enqueue_style(
        'backlins_monitoring_admin_styles',
        BLMM_PLUGIN_ABSOLUTE_PATH . '/admin/css/backlink-monitoring-admin-style.css',
        array(),
        $this->version,
        'all'
      );
      wp_enqueue_style( 
        'st-admin-confirm-css', 
        plugin_dir_url( __FILE__ ) . 'css/backlink-monitoring-admin-confirm.css', array(), $this->version, 'screen' );			 
    }
  }

  public function backlink_monitoring_admin_menu() {

    global $submenu;
		add_menu_page(
			__('Backlink Monitoring', $this->plugin_name ),
			__('Backlink Monitoring', $this->plugin_name ),
			'manage_options',
			'backlink-monitoring',
			array( $this, 'display_plugin_partial_page'),
			'dashicons-admin-links'
		);

  }

  public function display_plugin_partial_page() {
      include_once 'partials/backlink-monitoring-manager.php';
  }

  public function backlink_monitoring_manager_add_link() {

    if (
			check_admin_referer('backlink-monitoring-manager-add-link', 'backlink-monitoring-manager-add-link-nonce') && 
			! empty( $_POST )			
		) {

      $toLink = ( isset( $_POST['tolink'] ) ? esc_url( $_POST['tolink'] ) : '' );
      $fromLink = ( isset( $_POST['fromlink'] ) ? esc_url( $_POST['fromlink'] ) : '' );
      $validateLink = Backlink_Monitoring_Manager_Loader_Validator::link_validator( $toLink, $fromLink );
      $do_follow = '';
      $do_status = '';
      $mod_to_link = '';
      $mod_from_link = '';
      $linkText = '';
      $to_host_url = wp_parse_url( $toLink );
      $from_host_url = wp_parse_url( $fromLink );

      if( 'dofollow' === $validateLink['rel'] || empty( $validateLink['rel'] ) && isset( $validateLink['text'] ) && false !== $validateLink ) {

        $do_follow = 'Yes';
        $do_status = 'Live';
        $linkText = $validateLink['text'];
        $mod_to_link = sprintf( '<a href="%s" target="_blank">%s</a>', $validateLink['href'], $to_host_url['host'] );
        $mod_from_link = sprintf( '<a href="%s" target="_blank">%s</a>', $fromLink, $from_host_url['host'] );

      } else if ( 'noopener' == $validateLink['rel'] || 'noreferrer' == $validateLink['rel'] || 'noopener noreferrer' == $validateLink['rel'] && isset( $validateLink['text'] ) && false !== $validateLink ) {
         
        $do_follow = 'Yes';
        $do_status = 'Live';
        $linkText = $validateLink['text'];
        $mod_to_link = sprintf( '<a href="%s" target="_blank">%s</a>', $validateLink['href'], $to_host_url['host'] );
        $mod_from_link = sprintf( '<a href="%s" target="_blank">%s</a>', $fromLink, $from_host_url['host'] );

      } else if( 'nofollow' === $validateLink['rel'] && false !== $validateLink ) {

        $do_follow = 'No';
        $do_status = 'Live';
        $linkText = $validateLink['text'];
        $mod_to_link = sprintf( '<a href="%s" target="_blank">%s</a>', $validateLink['href'], $to_host_url['host'] );
        $mod_from_link = sprintf( '<a href="%s" target="_blank">%s</a>', $fromLink, $from_host_url['host'] );

      } elseif ( empty( $validateLink['href'] ) && empty( $validateLink['text'] ) ) {

        $do_follow = 'No';
        $do_status = 'Ko';
        $linkText = '';
        $mod_to_link = sprintf( '<a href="%s" target="_blank">%s</a>', $toLink, $to_host_url['host'] );
        $mod_from_link = sprintf( '<a href="%s" target="_blank">%s</a>', $fromLink, $from_host_url['host'] );

      } else {

        $do_follow = 'No';
        $do_status = 'Ko';
        $linkText = '';
        $mod_to_link = sprintf( '<a href="%s" target="_blank">%s</a>', $toLink, $to_host_url['host'] );
        $mod_from_link = sprintf( '<a href="%s" target="_blank">%s</a>', $fromLink, $from_host_url['host'] );

      }

      $link_id = 1;

      if ( get_option( $this->option_name ) ) {
          $data_array = get_option( $this->option_name );
          $last_index = end( $data_array );
          $id = (int) $last_index['id'];
          $link_id = $id + (int) $link_id;
      }

      $array_multi_array = array(
        array(
          'id' => $link_id,
          'date' => date('d-m-Y g:i A'),
          'toLink' => $mod_to_link,
          'fromLink' => $mod_from_link,
          'anchor_text' => $linkText,
          'do_follow' => $do_follow,
          'status' => $do_status,
        )
      );

      if ( get_option( $this->option_name ) ) {

        $updated_array = get_option( $this->option_name );
        $merged_array = array_merge( $updated_array, $array_multi_array );
        update_option( $this->option_name, $merged_array );
        wp_redirect( admin_url( 'admin.php?page=backlink-monitoring&msg=success' ) );

      } else {

        update_option( $this->option_name, $array_multi_array );
        wp_redirect( admin_url( 'admin.php?page=backlink-monitoring&msg=success' ) );
        
      }
    }
  }

  public function backlink_monitoring_list_table_ajax() {

    $wp_list_table = new Back_Link_Monitoring_Manager_Child_WP_List_Table( $this->plugin_name );
		$wp_list_table->ajax_response();

  }
}

<?php

class Backlink_Monitoring_Manager {

  protected $loader;
  protected $plugin_slug;
  protected $version;

  public function __construct() {

    $this->plugin_slug = 'backlink-monitoring-manager';
    $this->version = '0.1.0';
    $this->load_dependencies();
    $this->define_admin_hooks();

  }

  private function load_dependencies() {

    require_once plugin_dir_path( dirname( __FILE__) ) . 'admin/class-backlink-monitoring-manager-admin.php';
    require_once plugin_dir_path( dirname( __FILE__) ) . 'includes/class-backlink-monitoring-manager-loader.php';
    require_once plugin_dir_path( dirname( __FILE__) ) . 'admin/inc/class-backlink-monitoring-manager-parent-list-table.php';
    require_once plugin_dir_path( dirname( __FILE__) ) . 'admin/inc/class-backlink-monitoring-manager-child-list-table.php';
    require_once plugin_dir_path( dirname( __FILE__) ) . 'includes/class-backlink-monitoring-manager-validator.php';
    
    $this->loader = new Backlink_Monitoring_Manager_Loader();
  }

  private function define_admin_hooks() {

    $admin = new Backlink_Monitoring_Manager_Admin( $this->get_version(), $this->plugin_slug );
    /* enqueue scripts and styles */  
    $this->loader->add_action( 'admin_enqueue_scripts', $admin, 'admin_enqueue_scripts', 999 );
    $this->loader->add_action( 'admin_enqueue_scripts', $admin, 'admin_enqueue_styles', 999 );

    /* add menu */
		$this->loader->add_action( 'admin_menu', $admin, 'backlink_monitoring_admin_menu' );
    $this->loader->add_action( 'wp_ajax_bl_monitoring_list_table_ajax', $admin, 'backlink_monitoring_list_table_ajax' );
    $this->loader->add_action( 'admin_post_backlink_monitoring_manager_add_link', $admin, 'backlink_monitoring_manager_add_link' );

  }

  public function run() {
    $this->loader->run();
  }

  public function get_version() {
    return $this->version;
  }

}

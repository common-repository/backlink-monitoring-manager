<?php
/**
 * The file responsible for starting the Monitor Backlinks Manager Plugin
 *
 * Monitor Backlinks is a WordPress plugin that lets you track your Link
 * Building campaign. Add your link and check if it is dofollow or nofollow,
 * live or not.
 *
 * @package BLM
 *
 * Plugin Name:       Backlink Monitoring Manager
 * Plugin URI:        https://www.syedfakharabbas.com
 * Description:       Backlink Monitoring Manager check the link is live or not.
 * Version:           0.1.3
 * Author:            Syed Fakhar Abbas
 * Author URI:        https://www.syedfakharabbas.com
 * Text Domain:       backlink-monitoring-manager
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

define( 'BLMM_PLUGIN_ABSOLUTE_PATH', untrailingslashit( plugins_url( '', __FILE__  ) ) );

require_once plugin_dir_path( __FILE__ ) . 'includes/class-backlink-monitoring-manager.php';

function run_backlink_monitoring_manager() {

  $spmm = new Backlink_Monitoring_Manager();
  $spmm->run();
}

run_backlink_monitoring_manager();

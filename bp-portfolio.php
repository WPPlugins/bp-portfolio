<?php
/**
 * Plugin Name: BP Portfolio
 * Plugin URI:  https://wordpress.org/plugins/bp-portfolio/
 * Description: Let BuddyPress members create Portfolios to showcase their photos and artwork.
 * Author:      BuddyBoss
 * Author URI:  http://buddyboss.com
 * Version:     1.0.3
 */
// Exit if accessed directly
if (!defined('ABSPATH'))
  exit;

/**
 * ========================================================================
 * CONSTANTS
 * ========================================================================
 */
// Codebase version
if (!defined( 'BP_PORTFOLIO_PLUGIN_VERSION' ) ) {
  define( 'BP_PORTFOLIO_PLUGIN_VERSION', '1.0.3' );
}

// Database version
if (!defined( 'BP_PORTFOLIO_PLUGIN_DB_VERSION' ) ) {
  define( 'BP_PORTFOLIO_PLUGIN_DB_VERSION', 1 );
}

// Directory
if (!defined( 'BP_PORTFOLIO_PLUGIN_DIR' ) ) {
  define( 'BP_PORTFOLIO_PLUGIN_DIR', trailingslashit( plugin_dir_path( __FILE__ ) ) );
}

// Url
if (!defined( 'BP_PORTFOLIO_PLUGIN_URL' ) ) {
  $plugin_url = plugin_dir_url( __FILE__ );

  // If we're using https, update the protocol. Workaround for WP13941, WP15928, WP19037.
  if ( is_ssl() )
    $plugin_url = str_replace( 'http://', 'https://', $plugin_url );

  define( 'BP_PORTFOLIO_PLUGIN_URL', $plugin_url );
}

// File
if (!defined( 'BP_PORTFOLIO_PLUGIN_FILE' ) ) {
  define( 'BP_PORTFOLIO_PLUGIN_FILE', __FILE__ );
}

/**
 * ========================================================================
 * MAIN FUNCTIONS
 * ========================================================================
 */

/**
 * Main
 *
 * @return void
 */
function BP_PORTFOLIO_init()
{
  global $bp, $BUDDYPRESS_CREATIVE_PORTFOLIO;
  
  if ( !$bp ) {
		add_action('admin_notices','bpc_bp_admin_notice');
		return;
	}

  $main_include  = BP_PORTFOLIO_PLUGIN_DIR  . 'includes/main-class.php';

  try
  {
    if ( file_exists( $main_include ) )
    {
      require( $main_include );
    }
    else{
      $msg = sprintf( __( "Couldn't load main class at:<br/>%s", 'bp-portfolio' ), $main_include );
      throw new Exception( $msg, 404 );
    }
  }
  catch( Exception $e )
  {
    $msg = sprintf( __( "<h1>Fatal error:</h1><hr/><pre>%s</pre>", 'bp-portfolio' ), $e->getMessage() );
    echo $msg;
  }

  $BUDDYPRESS_CREATIVE_PORTFOLIO = BP_Portfolio_Plugin::instance();
  
}
add_action( 'plugins_loaded', 'BP_PORTFOLIO_init' );

/**
 * Must be called after hook 'plugins_loaded'
 * @return BP Portfolio Plugin main controller object
 */
function bp_portfolio()
{
  global $BUDDYPRESS_CREATIVE_PORTFOLIO;

  return $BUDDYPRESS_CREATIVE_PORTFOLIO;
}

register_activation_hook( __FILE__, 'bpcp_pro_enable_portfolio_component' );
/**
 * Enable portfolio component
 * Runs on plugin activation.
 */
function bpcp_pro_enable_portfolio_component() {

    // Flush rules after install
    flush_rewrite_rules();

    $bpcp_option_settings = array('bpcp-projects-enable' => 'on', 'enabled' => '');
    update_option('bp_portfolio_plugin_settings', $bpcp_option_settings);
}

function bpc_bp_admin_notice() {
	echo "<div class='error'><p>BP Portfolio needs BuddyPress activated</p></div>";
}

/**
 * Register BuddyBoss Menu Page
 */
if ( !function_exists( 'register_buddyboss_menu_page' ) ) {

	function register_buddyboss_menu_page() {
		// Set position with odd number to avoid confict with other plugin/theme.
		add_menu_page( 'BuddyBoss', 'BuddyBoss', 'manage_options', 'buddyboss-settings', '', bp_portfolio()->assets_url . '/images/logo.svg', 60 );

		// To remove empty parent menu item.
		add_submenu_page( 'buddyboss-settings', 'BuddyBoss', 'BuddyBoss', 'manage_options', 'buddyboss-settings' );
		remove_submenu_page( 'buddyboss-settings', 'buddyboss-settings' );
	}

	add_action( 'admin_menu', 'register_buddyboss_menu_page' );
}
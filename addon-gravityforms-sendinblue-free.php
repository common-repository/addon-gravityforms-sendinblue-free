<?php
/**
 * Plugin Name: Add-on Brevo for Gravity Forms
 * Plugin URI: https://wpconnect.co
 * Description: Create Brevo contacts directly from your Gravity Forms forms.
 * Author: WP connect
 * Author URI: https://wpconnect.co/
 * Text Domain: addon-gravityforms-sendinblue-free
 * Domain Path: /languages/
 * Version: 2.3.0
 */

namespace DK_GF_SIB_FREE;

use GFAddOn;

defined( 'ABSPATH' ) || exit;

/**
 * Define plugin constants
 */
define( 'DKGFSIB_FREE_VERSION', '2.3.0' );
define( 'DKGFSIB_FREE_URL', plugin_dir_url( __FILE__ ) );
define( 'DKGFSIB_FREE_DIR', plugin_dir_path( __FILE__ ) );
define( 'DKGFSIB_FREE_PLUGIN_DIRNAME', basename( rtrim( dirname( __FILE__ ), '/' ) ) );
define( 'DKGFSIB_FREE_BASENAME', plugin_basename( __FILE__ ) );
define( 'DKGFSIB_FREE_OPTIONS_PREFIX', 'dkgfsib-free_' );
define( 'DKGFSIB_FREE_WPC_PRODUCT_ID', 3377 );

/**
 * Check for requirements and (maybe) load the plugin vital files.
 *
 * @return void
 */
function init() {
	// Bail early if requirements are not met.
	if ( ! meets_requirements() && ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
		add_action( 'admin_notices', __NAMESPACE__ . '\\notice_for_missing_requirements' );
		return;
	}

	// Stop here if the pro version is enabled
	if ( is_pro_enabled() ) {
		return;
	}

	// Register vital files.
	require_once DKGFSIB_FREE_DIR . '/includes/functions.php';
	require_once DKGFSIB_FREE_DIR . '/includes/helpers.php';
	require_once DKGFSIB_FREE_DIR . '/includes/hooks.php';
	require_once DKGFSIB_FREE_DIR . '/includes/options.php';
	require_once DKGFSIB_FREE_DIR . '/includes/classes/api-sendinblue.php';
	require_once DKGFSIB_FREE_DIR . '/includes/classes/gf-addon.php';
}
add_action( 'plugins_loaded', __NAMESPACE__ . '\\init' );

/**
 * Does this WP install meet minimum requirements?
 *
 * @return boolean
 */
function meets_requirements() {
	global $wp_version;

	return (
		version_compare( PHP_VERSION, '7.0', '>=' ) &&
		version_compare( $wp_version, '5.5', '>=' ) &&
		class_exists( 'GFForms' )
	);
}

/**
 * Display a notice if requirements are not met.
 *
 * @return void
 */
function notice_for_missing_requirements() {
	printf(
		'<div class="notice notice-error"><p>%1$s</p></div>',
		esc_html__( 'The "Gravity Forms Brevo Add-on" plugin is inactive because the minimal requirements are not met.', 'addon-gravityforms-sendinblue-free' )
	);
}

/**
 * Trigger a custom action when activating the plugin.
 *
 * @param string  $plugin
 * @param boolean $network
 * @return void
 */
function on_plugin_activation( $plugin, $network ) {
	if ( $plugin !== DKGFSIB_FREE_BASENAME ) {
		return;
	}

	init();
	do_action( 'dk-gf-sib-free/plugin-activated', (bool) $network );
}
add_action( 'activate_plugin', __NAMESPACE__ . '\\on_plugin_activation', 10, 2 );

/**
 * Trigger a custom action when de-activating the plugin.
 *
 * @return void
 */
function on_plugin_deactivation( $plugin, $network ) {
	if ( $plugin !== DKGFSIB_FREE_BASENAME ) {
		return;
	}

	init();
	do_action( 'dk-gf-sib-free/plugin-deactivated', (bool) $network );
}
add_action( 'deactivate_plugin', __NAMESPACE__ . '\\on_plugin_deactivation', 10, 2 );

/**
 * Register our add-on.
 *
 * @return void
 */
function register_addon() {
	if ( ! method_exists( 'GFForms', 'include_addon_framework' ) ) {
		return;
	}

	// Stop here if the pro version is enabled
	if ( is_pro_enabled() ) {
		return;
	}

	init();
	GFAddOn::register( 'DK_GF_SIB_FREE\GF_Addon' );
}
add_action( 'gform_loaded', __NAMESPACE__ . '\\register_addon', 5 );

/**
 * Translations.
 *
 * @return void
 */
function load_translations() {
	load_plugin_textdomain( 'addon-gravityforms-sendinblue-free', false, DKGFSIB_FREE_PLUGIN_DIRNAME . '/languages/' );
}
add_action( 'plugins_loaded', __NAMESPACE__ . '\\load_translations' );

/**
 * Settings Link.
 *
 * @param array $links The array of plugin settings links.
 * @return array
 */
function add_settings_link( $links ) {
	$settings_link[] = "<a href='https://wpconnect.co/gravity-forms-sendinblue-add-on/' target='_blank'><b>" . esc_html( __( 'Upgrade to PRO', 'addon-gravityforms-sendinblue-free' ) ) . '</b></a>';
	$links           = array_merge( $settings_link, $links );
	return $links;
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), __NAMESPACE__ . '\\add_settings_link' );


/**
 * Return true if the pro version of the add-on is enabled.
 *
 * @return bool
 */
function is_pro_enabled() {
	return defined( 'DKGFSIB_VERSION' ) || defined( 'WPCONNECT_GF_SIB_VERSION' );
}

/**
 * Checks if another version of Gravity Forms to Sendinblue Free/Pro is active and deactivates it.
 * Hooked on `activated_plugin` so other plugin is deactivated when current plugin is activated.
 *
 * @param string $plugin The plugin being activated.
 * @return
 */
function deactivate_other_instances( $plugin ) {
    if ( ! in_array( $plugin, array( 'addon-gravityforms-sendinblue-free/addon-gravityforms-sendinblue-free.php', 'wpconnect-gf-sendinblue/wpconnect-gf-sendinblue.php' ), true ) ) {
        return;
    }

    $plugin_to_deactivate  = 'addon-gravityforms-sendinblue-free/addon-gravityforms-sendinblue-free.php';
    $deactivated_notice_id = '1';


    // If we just activated the Free version, deactivate the Pro version.
    if ( $plugin === $plugin_to_deactivate ) {
        $plugin_to_deactivate  = 'wpconnect-gf-sendinblue/wpconnect-gf-sendinblue.php';
        $deactivated_notice_id = '2';
    }

    if ( is_multisite() && is_network_admin() ) {
        $active_plugins = (array) get_site_option( 'active_sitewide_plugins', array() );
        $active_plugins = array_keys( $active_plugins );
    } else {
        $active_plugins = (array) get_option( 'active_plugins', array() );
    }

    foreach ( $active_plugins as $plugin_basename ) {
        if ( $plugin_to_deactivate === $plugin_basename ) {
            set_transient( 'wpconnectgfsib_deactivated_notice_id', $deactivated_notice_id, 1 * HOUR_IN_SECONDS );
            deactivate_plugins( $plugin_basename );
            return;
        }
    }
}
add_action( 'activated_plugin', __NAMESPACE__ . '\\deactivate_other_instances', 10, 1 );



/**
 * Displays a notice when either Gravity Forms to Sendinblue Free or Gravity Forms to Sendinblue Pro is automatically deactivated.
 * 
 * @return void
 */
function plugin_deactivated_notice() {
    $deactivated_notice_id = (int) get_transient( 'wpconnectgfsib_deactivated_notice_id' );

    if ( ! in_array( $deactivated_notice_id, array( 1, 2 ), true ) ) {
        return;
    }

    $message = __( "Add-on Brevo for Gravity Forms Free and Gravity Forms to Brevo Add-On should not be active at the same time. We've automatically deactivated Gravity Forms to Brevo Add-On", 'wpc-gf-sib' );
    if ( 2 === $deactivated_notice_id ) {
        $message = __( "Gravity Forms to Brevo Add-On and Add-on Brevo for Gravity Forms Free should not be active at the same time. We've automatically deactivated Gravity Forms to Brevo Add-On.", 'wpc-gf-sib' );
    }

    ?>
    <div class="notice notice-warning">
        <p><?php echo esc_html( $message ); ?></p>
    </div>
    <?php

    delete_transient( 'wpconnectgfsib_deactivated_notice_id' );
}
add_action( 'pre_current_active_plugins', __NAMESPACE__ . '\\plugin_deactivated_notice' );

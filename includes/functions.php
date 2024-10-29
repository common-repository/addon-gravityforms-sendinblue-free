<?php

/**
 * No namespace, these globally-available-functions are prefixed.
 */

use DK_GF_SIB_FREE\GF_Addon;
use DK_GF_SIB_FREE\API_Sendinblue;
use DK_GF_SIB_FREE\Options;

defined( 'ABSPATH' ) || exit;

/**
 * Get an instance of the GF add-on class.
 *
 * @return GF_Addon
 */
function dkgfsib_free_get_addon() {
	return GF_Addon::get_instance();
}

/**
 * Get an instance of the Sendinblue API.
 *
 * @param string $key
 * @return API_Sendinblue
 */
function dkgfsib_free_get_api( $key = null ) {
	$addon = dkgfsib_free_get_addon();

	return new API_Sendinblue( is_null( $key ) ? $addon->get_plugin_setting( 'sib-api-key' ) : $key );
}

/**
 * Is the API Key valid?
 *
 * @return boolean
 */
function dkgfsib_free_api_key_is_valid() {
	return Options\get_plugin_option( 'sib_api_key_is_valid' ) > 0;
}

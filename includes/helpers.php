<?php

namespace DK_GF_SIB_FREE\Helpers;

use DK_GF_SIB_FREE\Options;

defined( 'ABSPATH' ) || exit;

/**
 * Process the Sendinblue /account response.
 *
 * @param object           $pretty_response
 * @param object|\WP_Error $response
 * @return void
 */
function process_sendinblue_account_response( $pretty_response, $response ) {
	if ( is_wp_error( $response ) ) {
		Options\update_plugin_option(
			'sib_api_error',
			array(
				'code'    => $response->get_error_code(),
				'message' => $response->get_error_message(),
			)
		);
		Options\delete_plugin_option( 'sib_account_email' );
		Options\delete_plugin_option( 'sib_api_key_is_valid' );
	} elseif ( ! is_wp_error( $response ) && isset( $pretty_response->email ) ) {
		Options\update_plugin_option( 'sib_account_email', sanitize_email( $pretty_response->email ) );
		Options\update_plugin_option( 'sib_api_key_is_valid', time() );
		Options\delete_plugin_option( 'sib_api_error' );
	}
}

/**
 * Get a cached version of Sendinblue contact lists.
 *
 * @param boolean $bypass_cache
 * @return array
 */
function get_sendinblue_contact_lists( $bypass_cache = false ) {
	$transient_name = sprintf( '%1$ssib_lists', DKGFSIB_FREE_OPTIONS_PREFIX );
	$result         = get_transient( $transient_name );

	if ( $result === false || $bypass_cache ) {
		$api    = dkgfsib_free_get_api();
		$result = $api->get_lists();

		if ( ! is_wp_error( $result ) ) {
			set_transient( $transient_name, $result, 1 * MINUTE_IN_SECONDS );
		}
	}

	return $result;
}

/**
 * Get a cached version of Sendinblue attributes.
 *
 * @param boolean $bypass_cache
 * @return array
 */
function get_sendinblue_attributes( $bypass_cache = false ) {
	$transient_name = sprintf( '%1$ssib_attributes', DKGFSIB_FREE_OPTIONS_PREFIX );
	$result         = get_transient( $transient_name );

	if ( $result === false || $bypass_cache ) {
		$api    = dkgfsib_free_get_api();
		$result = $api->get_attributes();

		if ( ! is_wp_error( $result ) ) {
			set_transient( $transient_name, $result, 1 * MINUTE_IN_SECONDS );
		}
	}

	return $result;
}


/**
 * Find the first e-mail address in a mapped fields array.
 *
 * @param array $mapped_fields
 * @return string|null
 */
function find_email_in_mapped_fields( $fields = array() ) {
	foreach ( $fields as $field ) {
		if ( strtolower( $field->key ) === 'email' ) {
			return $field->value;
		}
	}

	return null;
}

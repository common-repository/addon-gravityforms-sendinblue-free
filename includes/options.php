<?php

namespace DK_GF_SIB_FREE\Options;

defined( 'ABSPATH' ) || exit;

/**
 * Get the full option key (prefixed).
 *
 * @param string $key
 * @return string
 */
function get_option_key( $key ) {
	return sprintf( '%1$s%2$s', DKGFSIB_FREE_OPTIONS_PREFIX, $key );
}

/**
 * Get a specific option value.
 *
 * @param string $key
 * @param mixed  $default
 * @return mixed
 */
function get_plugin_option( $key, $default = null ) {
	return get_option( get_option_key( $key ), $default );
}

/**
 * Update a specific option.
 *
 * @param string  $key
 * @param mixed   $value
 * @param boolean $autoload
 * @return void
 */
function update_plugin_option( $key, $value, $autoload = false ) {
	update_option( get_option_key( $key ), $value, $autoload );
}

/**
 * Delete a specific option.
 *
 * @param string $key
 * @return void
 */
function delete_plugin_option( $key ) {
	delete_option( get_option_key( $key ) );
}

/**
 * Do we already have a value for a specific option?
 *
 * @param string $key
 * @return boolean
 */
function has_plugin_option( $key ) {
	return ! empty( get_plugin_option( $key ) );
}

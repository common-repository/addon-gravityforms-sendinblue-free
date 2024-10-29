<?php

namespace DK_GF_SIB_FREE;

use \DK_GF_SIB_FREE\Options;

defined( 'ABSPATH' ) || exit;

/**
 * This class is in charge of communicating with the Sendinblue API.
 */
class API_Sendinblue {
	/**
	 * API key.
	 *
	 * @var string
	 */
	protected $api_key = null;

	/**
	 * API response, raw.
	 *
	 * @var mixed
	 */
	protected $response = null;

	/**
	 * Instanciate this class by passing the API key.
	 *
	 * @param string $api_key
	 */
	public function __construct( $api_key = null ) {
		$this->api_key = $api_key;
	}

	// =========================
	//
	// ###    #####   ##
	// ## ##   ##  ##  ##
	// ##   ##  #####   ##
	// #######  ##      ##
	// ##   ##  ##      ##
	//
	// =========================

	/**
	 * Send a manual request to the Sendinblue API without using the SDK.
	 *
	 * @param string $route
	 * @param array  $body
	 * @param string $method
	 * @return object|\WP_Error
	 */
	protected function request( $route = '', $body = array(), $method = 'POST' ) {
		if ( $method !== 'GET' ) {
			$body = wp_json_encode( $body );
		}

		// Construct headers.
		$headers = array(
			'Content-Type' => 'application/json',
			'api-key'      => $this->api_key,
		);

		$args = apply_filters(
			'dk-gf-sib-free/sendinblue-api/request-args',
			array(
				'timeout' => 15,
				'body'    => $body,
				'method'  => $method,
				'headers' => $headers,
			)
		);

		$url             = sprintf( '%1$s%2$s', 'https://api.sendinblue.com/v3/', $route );
		$this->response  = wp_remote_request( $url, $args );
		$pretty_response = json_decode( wp_remote_retrieve_body( $this->response ) );

		$response_code = (int) wp_remote_retrieve_response_code($this->response);
		
		if ($response_code >= 400) {
	
			$message = __('The Sendinblue API returned an error', 'addon-gravityforms-sendinblue-free');
			$message_details = '';
			
			if ($pretty_response && in_array($pretty_response->code, array('missing_parameter', 'invalid_parameter'), true)) {
				$message_details = $pretty_response->message;
			}
			if (!empty($message_details)) {
				$message = $message . ': ' . $message_details;
			}
	
			$this->response = new \WP_Error(
				$response_code,
				$message,
				['url' => $url, 'body' => $body, 'pretty_response' => $pretty_response, 'response' => $this->response,]
			);

		}

		do_action( 'dk-gf-sib-free/sendinblue-api/response', $route, $pretty_response, $this->response, $body );

		return is_wp_error( $this->response ) ? $this->response : $pretty_response;
	}

	// ============================
	//
	// ###     ####  ######
	// ## ##   ##       ##
	// ##   ##  ##       ##
	// #######  ##       ##
	// ##   ##   ####    ##
	//
	// ============================

	/**
	 * Create a contact with custom attributes (and maybe add it to one or many lists).
	 *
	 * @param string $email
	 * @param array  $attributes
	 * @param array  $list_ids
	 * @return integer|\WP_Error
	 */
	public function create_contact( $email, $attributes = array(), $list_ids = array() ) {
		$parameters = array(
			'email'         => $email,
			'attributes'    => $attributes,
			'updateEnabled' => true,
			'emailBlacklisted' => false
		);

		if ( ! empty( $list_ids ) ) {
			$parameters['listIds'] = array_map( 'absint', $list_ids );
		}

		$response = $this->request(
			'contacts/',
			$parameters,
			'POST'
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		// Weird case: if contact is updated, response body is empty but status code is 204.
		if ( (int) wp_remote_retrieve_response_code( $this->response ) === 204 ) {
			return (object) array( 'status' => 'updated' );
		}

		return (object) array(
			'status' => 'created',
			'id'     => $response->id,
		);
	}

	/**
	 * Get a contact by e-mail or ID.
	 *
	 * @param string|integer $identifier
	 * @return object|null
	 */
	public function get_contact( $identifier ) {
		$response = $this->request(
			"contacts/{$identifier}",
			array(),
			'GET'
		);

		return isset( $response->id ) ? $response : null;
	}

	/**
	 * Get a list of contact attributes.
	 *
	 * @param boolean $calculated
	 * @return array
	 */
	public function get_attributes( $calculated = false ) {
		$response = $this->request( 'contacts/attributes', array(), 'GET' );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$attributes = $response->attributes;

		if ( ! $calculated ) {
			return array_values(
				array_filter(
					$attributes,
					function( $attribute ) {
						return ! isset( $attribute->calculatedValue );
					}
				)
			);
		}

		return $attributes;
	}

	/**
	 * Get account details.
	 *
	 * @return object|\WP_Error
	 */
	public function get_account() {
		return $this->request( 'account', array(), 'GET' );
	}

	/**
	 * Get all contact lists.
	 *
	 * @param integer $limit
	 * @param string  $sort
	 * @return array|\WP_Error
	 */
	public function get_lists( $limit = 50, $sort = 'asc' ) {
		$lists = array();

		do {
			$response = $this->request(
				'contacts/lists/',
				array(
					'limit'  => $limit,
					'offset' => count( $lists ),
					'sort'   => $sort,
				),
				'GET'
			);

			if ( is_wp_error( $response ) ) {
				return $response;
			}

			$lists = array_merge( $lists, isset( $response->lists ) ? (array) $response->lists : array() );

			sleep( 0.25 );

			$total_fetched = count( $lists );
		} while (
			! is_wp_error( $response )
			&& isset( $response->count )
			&& (int) $response->count > $total_fetched
		);

		return $lists;
	}

}

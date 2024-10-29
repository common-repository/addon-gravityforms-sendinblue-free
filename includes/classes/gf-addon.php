<?php

namespace DK_GF_SIB_FREE;

use GFForms;
use GFFeedAddOn;
use DK_GF_SIB_FREE\Helpers;

defined( 'ABSPATH' ) || exit;

GFForms::include_feed_addon_framework();

define( 'DKGFSIB_FREE_ADDON_CACHE_BUSTER', 1 );

/**
 * This class is in charge of the GF add-on.
 */
class GF_Addon extends GFFeedAddOn {
	protected $_version                  = DKGFSIB_FREE_VERSION;
	protected $_min_gravityforms_version = '1.9';
	protected $_slug                     = DKGFSIB_FREE_PLUGIN_DIRNAME;
	protected $_path                     = DKGFSIB_FREE_BASENAME;
	protected $_full_path                = DKGFSIB_FREE_DIR . DKGFSIB_FREE_PLUGIN_DIRNAME . '.php';
	protected $_title                    = 'Gravity Forms Brevo Add-On';
	protected $_short_title              = 'Brevo';
	protected $_multiple_feeds           = false;

	/**
	 * Singleton holder: if available, contains an instance of this class.
	 *
	 * @var GF_Addon|null
	 */
	private static $_instance = null;

	/**
     * Defines the capability needed to access the Add-On form settings page.
     *
     * @since  1.2.0
     * @access protected
     * @var    string $_capabilities_form_settings The capability needed to access the Add-On form settings page.
     */
    protected $_capabilities_form_settings = 'gravityforms_edit_forms';
	
    /**
     * Defines the capability needed to access the Add-On settings page.
     *
     * @since  1.2.0
     * @access protected
     * @var    string $_capabilities_settings_page The capability needed to access the Add-On settings page.
     */
    protected $_capabilities_settings_page = 'gravityforms_edit_settings';

	/**
	 * Returns an instance of this class, and stores it in the $_instance property.
	 *
	 * @return GF_Addon $_instance An instance of this class.
	 */
	public static function get_instance() {
		if ( self::$_instance == null ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	// ================================================
	//
	// ###    ####    ###    ###  ##  ##     ##
	// ## ##   ##  ##  ## #  # ##  ##  ####   ##
	// ##   ##  ##  ##  ##  ##  ##  ##  ##  ## ##
	// #######  ##  ##  ##      ##  ##  ##    ###
	// ##   ##  ####    ##      ##  ##  ##     ##
	//
	// ================================================

	/**
	 * Enqueue admin styles.
	 *
	 * @return array
	 */
	public function styles() {
		$styles = array(
			array(
				'handle'  => 'dkgfsib-free-admin-style',
				'src'     => sprintf( '%1$s/assets/css/%2$s', DKGFSIB_FREE_URL, defined( 'WPC_USE_UNMINIFIED_ASSETS' ) && WPC_USE_UNMINIFIED_ASSETS ? 'admin.css' : 'admin.min.css' ),
				'version' => $this->_version . '~' . DKGFSIB_FREE_ADDON_CACHE_BUSTER,
				'enqueue' => array( 'admin_page' => array( 'form_settings', 'plugin_settings' ) ),
			),
		);

		return array_merge( parent::styles(), $styles );
	}

	public function scripts() {
		$scripts = array(
			array(
				'handle'    => 'dkgfsib-free-admin-script',
				'src'       => sprintf( '%1$s/assets/js/%2$s', DKGFSIB_FREE_URL, defined( 'WPC_USE_UNMINIFIED_ASSETS' ) && WPC_USE_UNMINIFIED_ASSETS ? 'admin.js' : 'admin.min.js' ),
				'version'   => $this->_version . '~' . DKGFSIB_FREE_ADDON_CACHE_BUSTER,
				'deps'      => array( 'jquery', 'wp-i18n' ),
				'in_footer' => false,
				'callback'  => array( $this, 'localize_scripts' ),
				'enqueue'   => array( 'admin_page' => array( 'form_settings', 'plugin_settings' ) ),
			),
		);

		return array_merge( parent::scripts(), $scripts );
	}

	/**
	 * The the add-on settings icon.
	 *
	 * @return string
	 */
	public function get_menu_icon() {
		// assets/images/brevo-logo.svg
		return '<?xml version="1.0" encoding="utf-8"?> <svg version="1.1" id="Calque_1" x="0px" y="0px" viewBox="0 0 10 10" style="enable-background:new 0 0 10 10;" xml:space="preserve"> <style type="text/css"> .st0{fill:#242748;} </style> <path class="st0" d="M7.1,4.1c0.4-0.4,0.7-0.9,0.7-1.4C7.7,1.6,6.8,1,5.5,1h-3v7.9H5c2,0,3.4-1.1,3.4-2.6C8.4,5.3,8,4.6,7.1,4.1 L7.1,4.1z M3.6,1.9h2c0.7,0,1.1,0.4,1.1,0.9c0,0.6-0.5,1.1-1.7,1.5C4.2,4.5,3.8,4.6,3.6,4.9l0,0C3.6,4.7,3.6,1.9,3.6,1.9z M5,7.6 H3.6V6.4c0-0.5,0.5-1.1,1.2-1.4c0.5-0.1,1.1-0.3,1.5-0.4C6.8,4.9,7.2,5.4,7.2,6C7.2,7.1,6.2,7.6,5,7.6L5,7.6z"/> </svg>';
	}

	/**
	 * Global plugin settings.
	 *
	 * @return array
	 */
	public function plugin_settings_fields() {
		return array(
			array(
				'title'  => esc_html__( 'Brevo Add-On Settings', 'addon-gravityforms-sendinblue-free' ),
				'fields' => array(
					array(
						'name'  => 'license-key-field',
						'label' => esc_html__( 'License Activation', 'addon-gravityforms-sendinblue-free' ),
						'type'  => 'wpc_license_field',
					),
					array(
						'name'              => 'sib-api-key',
						/* translators: %1$s: Sendinblue account API URL */
						'tooltip'           => sprintf( esc_attr__( 'Visit the <a href="%1$s" target="_blank">"Settings > SMTP & API"</a> page in your Brevo account to generate an API key.', 'addon-gravityforms-sendinblue-free' ), 'https://account.sendinblue.com/advanced/api' ),
						'label'             => esc_html__( 'Brevo API Key', 'addon-gravityforms-sendinblue-free' ),
						'type'              => 'text',
						'class'             => 'small',
						'feedback_callback' => array( $this, 'validate_sendinblue_api_key_field' ),
					),
				),
			),
		);
	}

	/**
	 * Check/activate the plugin license key.
	 *
	 * @param string $value
	 * @return boolean
	 */
	public function validate_license_key_field( $value ) {
		if ( empty( $value ) ) {
			return false;
		}

		return true;

		// return Helpers\activate_plugin_license_key( $value );
	}

	/**
	 * Check the Sendinblue API key validity.
	 *
	 * @param string $value
	 * @return boolean
	 */
	public function validate_sendinblue_api_key_field( $value ) {
		if ( empty( $value ) ) {
			return false;
		}

		$transient_name = sprintf( '%1$ssib_api_key_%2$s', DKGFSIB_FREE_OPTIONS_PREFIX, substr( md5( sanitize_text_field( $value ) ), 0, 12 ) );
		$result         = get_transient( $transient_name );

		if ( $result === false ) {
			$api    = dkgfsib_free_get_api( $value );
			$result = $api->get_account();

			set_transient( $transient_name, $result, 5 * MINUTE_IN_SECONDS );
		}

		Helpers\process_sendinblue_account_response(
			! is_wp_error( $result ) ? $result : null,
			$result
		);

		return ! is_wp_error( $result );
	}

	/**
	 * Define feeds table columns.
	 *
	 * @return array
	 */
	public function feed_list_columns() {
		return array(
			'name'           => esc_html__( 'Name', 'addon-gravityforms-sendinblue-free' ),
			'lists'          => esc_html__( 'List(s)', 'addon-gravityforms-sendinblue-free' ),
			'has_conditions' => esc_html__( 'Condition(s)', 'addon-gravityforms-sendinblue-free' ),
		);
	}

	/**
	 * Get column: has_conditions.
	 *
	 * @param array $feed
	 * @return void
	 */
	public function get_column_value_has_conditions( $feed ) {
		if ( isset( $feed['meta'], $feed['meta']['feed_condition_conditional_logic'] ) && (int) $feed['meta']['feed_condition_conditional_logic'] === 1 ) {
			echo '<span class="icon-yes dashicons dashicons-yes"></span>';
		} else {
			echo '<span class="icon-no dashicons dashicons-no-alt"></span>';
		}
	}

	/**
	 * Can we create a feed?
	 *
	 * @return boolean
	 */
	public function can_create_feed() {
		return dkgfsib_free_api_key_is_valid();
	}

	/**
	 * Per-form settings.
	 *
	 * @return array
	 */
	public function feed_settings_fields() {
		if ( ! dkgfsib_free_api_key_is_valid() ) {
			$message = sprintf(
				/* translators: %1$s: Gravity Form Sendinblue settings page URL */
				__( 'Please visit the <a href="%1$s">plugin settings page</a> and enter a valid Sendinblue API key.', 'addon-gravityforms-sendinblue-free' ),
				admin_url( 'admin.php?page=gf_settings&subview=dk-gravityforms-sendinblue' )
			);

			$fields = array(
				array(
					'name'  => 'sib-api-key-invalid',
					'label' => esc_html__( 'API key missing or invalid', 'addon-gravityforms-sendinblue-free' ),
					'type'  => 'html',
					'html'  => sprintf( '<p>%1$s</p>', $message ),
				),
			);
		} else {
			$attributes = Helpers\get_sendinblue_attributes();
			$lists      = Helpers\get_sendinblue_contact_lists();

			$is_enabled_dependency = array(
				'live'     => true,
				'operator' => 'ALL',
				'fields'   => array(
					array(
						'field'  => 'is-enabled',
						'values' => array( '1', true ),
					),
				),
			);
			$attributes            = is_array( $attributes ) ? $attributes : array();

            $attributes_choices = array_map(
				function( $attribute ) {
                    // Allow only boolean (opt-in like field)
					if (isset($attribute->type) && $attribute->type === 'boolean'){
                        return array(
                            'label' => sanitize_text_field( $attribute->name ),
                            'value' => sanitize_text_field( $attribute->name ),
                        );
                    }
					return array(
						'label' => sanitize_text_field( $attribute->name ) . ' ' . esc_html__( '(Pro version)', 'addon-gravityforms-sendinblue-free' ),
						'value' => '***disabled***', // We can't add disable attribute here so we change the value and make the option disabled in JS (@See admin.js)
					);
				},
                $attributes
			);

            uasort($attributes_choices, function ($a, $b) {
                return $a['value'] !== '***disabled***' ? -1 : 1;
            });

			array_unshift(
				$attributes_choices,
				array(
					'label' => esc_html__( 'E-mail address', 'addon-gravityforms-sendinblue-free' ),
					'value' => 'EMAIL',
				)
			);

			$fields = array(
				array(
					'name'  => 'is-enabled',
					'label' => esc_html__( 'Enabled', 'addon-gravityforms-sendinblue-free' ),
					'type'  => 'toggle',
				),
				array(
					'name'       => 'name',
					'label'      => esc_html__( 'Feed name', 'addon-gravityforms-sendinblue-free' ),
					'type'       => 'text',
					'required'   => true,
					'class'      => 'medium',
					'tooltip'    => esc_html__( 'Enter a feed name to uniquely identify it.', 'addon-gravityforms-sendinblue-free' ),
					'dependency' => $is_enabled_dependency,
				),
				array(
					'name'              => 'mapped-fields',
					'label'             => esc_html__( 'Fields mapping', 'addon-gravityforms-sendinblue-free' ),
					'tooltip'           => esc_html__( 'Add and select the Sendinblue attributes for which to send data, then choose the form fields that match.', 'addon-gravityforms-sendinblue-free' ),
					'type'              => 'dynamic_field_map',
					'required'          => true,
					'enable_custom_key' => false,
					'value_field'       => array( 'title' => esc_html__( 'Form fields', 'addon-gravityforms-sendinblue-free' ) ),
					'key_field'         => array(
						'title'   => esc_html__( 'Brevo attributes', 'addon-gravityforms-sendinblue-free' ),
						'choices' => $attributes_choices,
					),
					'dependency'        => $is_enabled_dependency,
					'fields'            => array(
						array(
							'name'  => 'sib-free-mapping-notice',
							'label' => '',
							'type'  => 'html',
							'html'  => '<p>' .
								sprintf(
									/* translators: %1$s Pro version link */
									esc_html__( 'Free version only includes E-mail and Consent fields ("OPT_IN" boolean in Brevo), while the %1$s is unlimited.', 'addon-gravityforms-sendinblue-free' ) . '</p>',
									sprintf(
										'<a href="%s" target="_blank">%s</a>',
										'https://wpconnect.co/gravity-forms-sendinblue-add-on',
										esc_html__( 'Pro version', 'addon-gravityforms-sendinblue-free' )
									)
								),

						),
					),
				),

			);

			if ( is_array( $lists ) && ! empty( $lists ) ) {
				$lists_field = array(
					'name'       => 'add-to-list',
					'label'      => esc_html__( 'Add to list', 'addon-gravityforms-sendinblue-free' ),
					'tooltip'    => esc_html__( 'All your Brevo lists are displayed below. You can choose which one(s) to save each new contact to.', 'addon-gravityforms-sendinblue-free' ),
					'type'       => 'radio',
					'horizontal' => true,
					'choices'    => array_merge(
						array(
							array(
								'label' => esc_html__( 'None', 'addon-gravityforms-sendinblue-free' ),
								'name'  => 'add-to-list-none',
								'value' => 0,
							),
						),
						array_map(
							function( $list ) {
								return array(
									'label' => sanitize_text_field( $list->name ),
									'name'  => sprintf( 'add-to-list', sanitize_text_field( $list->id ) ),
									'value' => sanitize_text_field( $list->id ),
								);
							},
							$lists
						)
					),
					'dependency' => $is_enabled_dependency,
				);

				if ( count( $lists ) > 1 ) {
					$lists_field['fields'] = array(
						array(
							'name'  => 'sib-free-lists-notice',
							'label' => '',
							'type'  => 'html',
							'html'  => '<p>' .
								sprintf(
									/* translators: %1$s Pro version link */
									esc_html__( 'Free version only allows you to select one list at a time, while the %1$s is unlimited.', 'addon-gravityforms-sendinblue-free' ) . '</p>',
									sprintf(
										'<a href="%s" target="_blank">%s</a>',
										'https://wpconnect.co/gravity-forms-sendinblue-add-on',
										esc_html__( 'Pro version', 'addon-gravityforms-sendinblue-free' )
									)
								),

						),
					);
				}

				$fields[] = $lists_field;

				$fields[] = array(
					'name'       => 'use-double-optin',
					'label'      => esc_html__( 'Double opt-in (Pro version)', 'addon-gravityforms-sendinblue-free' ),
					'type'       => 'checkbox',
					'disabled'   => true,
					'choices'    => array(
						array(
							'label' => esc_html__( 'Enable double opt-in', 'addon-gravityforms-sendinblue-free' ) . ' ' . esc_html__( '(Pro version)', 'addon-gravityforms-sendinblue-free' ),
							'name'  => 'double-optin-enabled',
						),
					),
					'tooltip'    => esc_html__( 'By enabling this, Brevo will send a confirmation email to the user before adding them to your list(s).', 'addon-gravityforms-sendinblue-free' ),
					'dependency' => $is_enabled_dependency,
				);
			}
		}

		return array(
			array(
				'title'  => esc_html__( 'Integration with Brevo', 'addon-gravityforms-sendinblue-free' ),
				'fields' => $fields,
				'class'  => 'wpco-sib-integration-settings',
				'id'     => 'wpco-sib-integration-settings',
			),
			array(
				'title'  => esc_html__( 'Conditional logic', 'addon-gravityforms-sendinblue-free' ),
				'class'  => 'wpco-sib-conditional-logic',
				'id'     => 'wpco-sib-conditional-logic',
				'fields' => array(
					array(
						'type'           => 'feed_condition',
						'name'           => 'feed-condition',
						'label'          => esc_html__( 'Conditions', 'addon-gravityforms-sendinblue-free' ),
						'checkbox_label' => esc_html__( 'Enable conditional processing', 'addon-gravityforms-sendinblue-free' ),
						'instructions'   => esc_html__( 'Create a contact in Brevo only if', 'addon-gravityforms-sendinblue-free' ),
					),
				),
			),
		);
	}

	// =================================================
	//
	// #####  ##     ##  ######  #####    ##    ##
	// ##     ####   ##    ##    ##  ##    ##  ##
	// #####  ##  ## ##    ##    #####      ####
	// ##     ##    ###    ##    ##  ##      ##
	// #####  ##     ##    ##    ##   ##     ##
	//
	// =================================================

	/**
	 * Initiate processing the feed.
	 *
	 * @since  1.0
	 *
	 * @param array $feed  The Feed object to be processed.
	 * @param array $entry The Entry object currently being processed.
	 * @param array $form  The Form object currently being processed.
	 */
	public function process_feed( $feed, $entry, $form ) {
		$settings = $feed['meta'];

		if (
			! (bool) $feed['is_active']
			|| ! isset( $settings['mapped-fields'] )
			|| ! (bool) $settings['is-enabled']
		) {
			return;
		}

		$mapped_fields = array_map(
			function( $conf ) use ( $entry, $form ) {
				$value = isset( $entry[ $conf['value'] ] ) ? sanitize_text_field( $entry[ $conf['value'] ] ) : null;
				$key   = ( $conf['key'] === 'gf_custom' ) ? sanitize_text_field( $conf['custom_key'] ) : sanitize_text_field( $conf['key'] );

				if ( $conf['value'] === 'form_title' ) {
					$value = sanitize_text_field( $form['title'] );
				}

				return array(
					'key'   => $key,
					'value' => $value,
				);
			},
			$settings['mapped-fields']
		);

		$mapped_fields          = $this->get_dynamic_field_map_fields( $feed, 'mapped-fields' );
		$enhanced_mapped_fields = array();

		foreach ( $mapped_fields as $key => $field_id ) {
			if ( rgblank( $field_id ) ) {
				continue;
			}
            $field_value = $this->get_field_value( $form, $entry, $field_id );
            $field = \GFFormsModel::get_field( $form, $field_id );

            if ( is_object( $field ) ) {
                $input_type = $field->get_input_type();
                if ($input_type === 'consent' && in_array($field_value, array(esc_html__( 'Not Checked', 'gravityforms' ), esc_html__( 'Checked', 'gravityforms' )))) {
                    $field_value = $field_value === esc_html__( 'Checked', 'gravityforms' );
                }
            }

			$enhanced_mapped_fields[ $key ] = (object) array(
				'key'   => $key,
				'value' => $field_value,
			);
		}

		$list_id = ( isset( $settings['add-to-list'] ) ? (int) $settings['add-to-list'] : -1 );
		if ( $list_id > 0 ) {
			$lists = array( $list_id );
		} else {
			$lists = array();
		}

		// Filter variables for external modifications.
		$mapped_fields = apply_filters( 'dk-gf-sib-free/entry/mapped-fields', $enhanced_mapped_fields, $feed, $entry, $form, $settings, $this );
		$lists         = apply_filters( 'dk-gf-sib-free/entry/lists', $lists, $feed, $entry, $form, $settings, $this );

		if ( ! apply_filters( 'dk-gf-sib-free/disable-form-entry-processing', false, $feed, $entry, $form, $settings, $this ) ) {
			do_action(
				'dk-gf-sib-free/process-form-entry',
				$mapped_fields,
				$lists,
				false,
				null,
				null,
				$feed,
				$entry,
				$form,
				$settings,
				$this
			);
		}
	}

	/**
	 * Get all feeds API responses for a specific entry.
	 *
	 * @param integer $entry_id
	 * @return array
	 */
	public function get_entry_addon_metadata( $entry_id ) {
		$metas = array();

		$feeds = $this->get_feeds_by_entry( $entry_id );

		if ( ! is_array( $feeds ) ) {
			return $metas;
		}

		foreach ( $this->get_feeds_by_entry( $entry_id ) as $feed_id ) {
			$metas[ $feed_id ] = gform_get_meta( $entry_id, sprintf( 'sib_contact_api_response:%1$d', $feed_id ) );
		}

		return $metas;
	}
}

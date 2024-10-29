<?php

namespace DK_GF_SIB_FREE\Hooks;

use DK_GF_SIB_FREE\Options;
use DK_GF_SIB_FREE\Helpers;

defined('ABSPATH') || exit;



/**
 * On plugin activation, keep the current plugin version in an option.
 *
 * @return void
 */
function save_plugin_version_on_activation()
{
	Options\update_plugin_option('version', DKGFSIB_FREE_VERSION);
}
add_action('dk-gf-sib-free/plugin-activated', __NAMESPACE__ . '\\save_plugin_version_on_activation', 10, 1);


/**
 * When checking the /account Sendinblue route, save response in option.
 *
 * @param string           $route
 * @param object           $pretty_response
 * @param object|\WP_Error $response
 * @param array            $body
 * @return void
 */
function save_sendinblue_api_key_status($route, $pretty_response, $response, $body)
{
	if ($route !== 'account') {
		return;
	}

	Helpers\process_sendinblue_account_response($pretty_response, $response);
}
add_action('dk-gf-sib-free/sendinblue-api/response', __NAMESPACE__ . '\\save_sendinblue_api_key_status', 10, 4);

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
 * Create a contact in Sendinblue after a GF form entry has been created.
 *
 * @param array $mapped_fields
 * @param array $lists
 * @param array $entry
 * @return void
 */
function create_sendinblue_contact_after_form_submission($mapped_fields, $lists, $double_optin, $optin_template, $optin_redirect, $feed, $entry)
{
	// Force array to be an array, see https://core.trac.wordpress.org/ticket/55133.
	if (!is_array($mapped_fields)) {
		$mapped_fields = array($mapped_fields);
	}

	$api   = dkgfsib_free_get_api();
	$email = Helpers\find_email_in_mapped_fields($mapped_fields);

	if (is_null($email)) {
		$response = new \WP_Error('no_email_found', __('No e-mail address found in form values.', 'addon-gravityforms-sendinblue-free'));
		gform_update_meta($entry['id'], sprintf('sib_contact_api_response:%1$d', $feed['id']), $response);
		return;
	}

	$response = $api->create_contact(
		sanitize_email($email),
		array_combine(wp_list_pluck($mapped_fields, 'key'), wp_list_pluck($mapped_fields, 'value')),
		$lists
	);

	gform_update_meta($entry['id'], sprintf('sib_contact_api_response:%1$d', $feed['id']), $response);
}
add_action('dk-gf-sib-free/process-form-entry', __NAMESPACE__ . '\\create_sendinblue_contact_after_form_submission', 10, 8);

/**
 * Register the GF Entry metabox.
 *
 * @param array $metaboxes
 * @param array $entry
 * @param array $form
 * @return array
 */
function register_entry_metabox($metaboxes, $entry, $form)
{
	$metaboxes['dk-gravityforms-sendinblue'] = array(
		'title'    => esc_html__('Brevo', 'addon-gravityforms-sendinblue-free'),
		'callback' => __NAMESPACE__ . '\\render_entry_metabox',
		'context'  => 'side',
	);

	return $metaboxes;
}
add_action('gform_entry_detail_meta_boxes', __NAMESPACE__ . '\\register_entry_metabox', 10, 3);

/**
 * Render the metabox content.
 *
 * @param array $args
 * @return void
 */
function render_entry_metabox($args)
{
	$addon      = dkgfsib_free_get_addon();
	$feeds_data = $addon->get_entry_addon_metadata($args['entry']['id']);

	// Weird format.
	if (empty($feeds_data) || !is_array($feeds_data)) {
		printf(
			'<p class="no-data">%1$s</p>',
			esc_html__('No data.', 'addon-gravityforms-sendinblue-free')
		);
	}

	foreach ($feeds_data as $feed_id => $feed_data) {
		if (count($feeds_data) > 1) {
			printf(
				'<h4 class="feed-title">%1$s</h4>',
				/* translators: %1$d: Feed id */
				sprintf(esc_html__('Feed #%1$d', 'addon-gravityforms-sendinblue-free'), (int) $feed_id)
			);
		}

		// API error.
		if (is_wp_error($feed_data)) {
			$error_message = $feed_data->get_error_message();

			printf(
				'<p class="error">%1$s %2$s</p>',
				esc_html__('Error:', 'wpc-gf-sib'),
				esc_html($error_message)
			);

			$full_error_response = '';
			$error_data = $feed_data->get_error_data();

			if (isset($error_data['pretty_response']) && isset($error_data['pretty_response']->code) && isset($error_data['pretty_response']->message)) {
				$full_error_response = sprintf(
					'<strong>' . esc_html__('Error code: ', 'wpc-gf-sib') . '</strong>%s',
					esc_html($error_data['pretty_response']->code)
				);
				$full_error_response .= '<br />';
				$full_error_response .= sprintf(
					'<strong>' . esc_html__('Error message: ', 'wpc-gf-sib') . '</strong>%s',
					esc_html($error_data['pretty_response']->message)
				);
			}

			if (!empty($full_error_response)) {
				echo esc_html__('Full error response:', 'wpc-gf-sib');
				echo '<br />';
				echo $full_error_response;
			}
		}

		// Creation.
		elseif (isset($feed_data->status) && $feed_data->status === 'created') {
			if (isset($feed_data->id)) {
				printf(
					'<p class="success contact-created">%1$s</p>',
					/* translators: %1$d: Feed entry id */
					sprintf(esc_html__('New contact created (ID #%1$d).', 'addon-gravityforms-sendinblue-free'), esc_html($feed_data->id))
				);
			} else {
				printf(
					'<p class="success contact-created">%1$s</p>',
					esc_html__('New contact created.', 'addon-gravityforms-sendinblue-free')
				);
			}
		}

		// Update.
		elseif (isset($feed_data->status) && $feed_data->status === 'updated') {
			printf(
				'<p class="success contact-updated">%1$s</p>',
				esc_html__('Existing contact updated.', 'addon-gravityforms-sendinblue-free')
			);
		} else {
			// Weird case?
			printf(
				'<p class="no-data">%1$s</p>',
				esc_html__('No data.', 'addon-gravityforms-sendinblue-free')
			);
		}
	}
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
 * Indent checkboxes values in the mapping dropdown
 *
 * @param array             $fields The value and label properties for each choice.
 * @param int               $form_id             The ID of the form currently being configured.
 * @param null|array        $field_type          Null or the field types to be included in the drop down.
 * @param null|array|string $exclude_field_types Null or the field type(s) to be excluded from the drop down.
 */
function gform_field_map_choices_indent_checkboxes_values(array $fields, int $form_id, $field_type, $exclude_field_types)
{
	if (rgar($_GET, 'page') !== 'gf_edit_forms' || rgar($_GET, 'subview') !== 'gravity-forms-to-sendinblue-free') {
		return $fields;
	}
	if (isset($fields['fields']['choices'])) {
		$last_is_checkbox = false;
		foreach ($fields['fields']['choices'] as $key => $field) {
			if ($field['type'] === 'checkbox') {
				$field['label'] = str_replace(' (' . esc_html__('Selected', 'gravityforms') . ')', '', $field['label']);

				// Checkbox choices ?
				if ($last_is_checkbox) {
					$field['label'] = ' - ' . $field['label'];
				}
				$fields['fields']['choices'][$key] = $field;
				$last_is_checkbox                    = true;
			} else {
				$last_is_checkbox = false;
			}
		}
	}
	return $fields;
}
add_filter('gform_field_map_choices', __NAMESPACE__ . '\\gform_field_map_choices_indent_checkboxes_values', 10, 4);


/**
 * Disable fields not available in the free version
 *
 * @param array             $fields The value and label properties for each choice.
 * @param int               $form_id             The ID of the form currently being configured.
 * @param null|array        $field_type          Null or the field types to be included in the drop down.
 * @param null|array|string $exclude_field_types Null or the field type(s) to be excluded from the drop down.
 */
function gform_field_map_choices_disabled_pro_fields(array $fields, int $form_id, $field_type, $exclude_field_types)
{
	if (rgar($_GET, 'page') !== 'gf_edit_forms' || rgar($_GET, 'subview') !== 'gravity-forms-to-sendinblue-free') {
		return $fields;
	}
	if (isset($fields['fields']['choices'])) {
		$last_is_consent = false;
		foreach ($fields['fields']['choices'] as $key => $field) {
			if ($field['type'] !== 'email') {
				if ($field['type'] === 'consent') {
					// Allow only one consent field
					if ($last_is_consent) {
						$field['value'] = '***disabled***';
					}
					$last_is_consent = true;
				} else {
					$field['value']  = '***disabled***';
					$last_is_consent = false;
				}
				$fields['fields']['choices'][$key] = $field;
			}
		}
	}

	foreach ($fields as $index => $field_group) {
		if (isset($field_group['label']) && $field_group['label'] === esc_html__('Entry Properties', 'gravityforms')) {
			foreach ($field_group['choices'] as $key => $field) {
				$field['value']                      = '***disabled***';
				$fields[$index]['choices'][$key] = $field;
			}
		}
	}
	return $fields;
}
add_filter('gform_field_map_choices', __NAMESPACE__ . '\\gform_field_map_choices_disabled_pro_fields', 10, 4);

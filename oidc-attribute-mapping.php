<?php
/**
 * Plugin Name: OpenID Connect Client Customizations
 * Description: Provides customizations for the OpenID Connect Client plugin.
 *
 * @package  OpenidConnectGeneric_MuPlugin
 *
 * @link     https://github.com/daggerhart/openid-connect-generic
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Modifies the OIDC login button text.
 *
 * @link https://github.com/daggerhart/openid-connect-generic#openid-connect-generic-login-button-text
 *
 * @param string $text The button text.
 *
 * @return string
 */
function oidc_attribute_login_button_text( $text ) {

	// @var array<mixed> $settings
	$settings = get_option( 'openid_connect_generic_settings', array() );

	$text = ( ! empty( $settings['oidc_login_button_text'] ) ) ? strval( $settings['oidc_login_button_text'] ) : __( 'Login with Keycloak', 'oidc-attribute-mu-plugin' );

	return $text;

}
add_filter( 'openid-connect-generic-login-button-text', 'oidc_attribute_login_button_text', 10, 1 );

/**
 * Adds a new setting that allows an Administrator to set the button text from
 * the plugin settings screen.
 *
 * @link https://github.com/daggerhart/openid-connect-generic#openid-connect-generic-settings-fields
 *
 * @param array<mixed> $fields The array of settings fields.
 *
 * @return array<mixed>
 */
function oidc_attribute_add_login_button_text_setting( $fields ) {

	// @var array<mixed> $field_array
	$field_array = array(
		'oidc_login_button_text' => array(
			'title'       => __( 'Login Button Text', 'oidc-attribute-mu-plugin' ),
			'description' => __( 'Set the login button label text.', 'oidc-attribute-mu-plugin' ),
			'type'        => 'text',
			'section'     => 'client_settings',
		),
	);

	// Prepend the field array with the new field to push it to the top of the settings screen.
	return $field_array + $fields;

}
add_filter( 'openid-connect-generic-settings-fields', 'oidc_attribute_add_login_button_text_setting', 10, 1 );

/**
 * Setting to indicate whether an IDP role mapping is required for user creation.
 *
 * @link https://github.com/daggerhart/openid-connect-generic#openid-connect-generic-settings-fields
 *
 * @param array<mixed> $fields The array of settings fields.
 *
 * @return array<mixed>
 */
function oidc_attribute_add_require_idp_role_setting( $fields ) {

	$fields['require_idp_user_role'] = array(
		'title'       => __( 'Valid IDP User Role Required', 'oidc-attribute-mu-plugin' ),
		'description' => __( 'When enabled, this will prevent users from being created if they don\'t have a valid mapped IDP to WordPress role.', 'oidc-attribute-mu-plugin' ),
		'type'        => 'checkbox',
		'section'     => 'user_settings',
	);

	return $fields;

}
add_filter( 'openid-connect-generic-settings-fields', 'oidc_attribute_add_require_idp_role_setting', 10, 1 );

/**
 * Adds a new setting that allows configuration of the default role assigned
 * to users when no IDP role is provided.
 *
 * @link https://github.com/daggerhart/openid-connect-generic#openid-connect-generic-settings-fields
 *
 * @param array<mixed> $fields The array of settings fields.
 *
 * @return array<mixed>
 */
function oidc_attribute_add_default_role_setting( $fields ) {

	// @var WP_Roles $wp_roles_obj
	$wp_roles_obj = wp_roles();
	// @var array<string> $roles
	$roles = $wp_roles_obj->get_names();
	// Prepend a blank role as the default.
	array_unshift( $roles, '-- None --' );

	// Setting to specify default user role when no role is provided by the IDP.
	$fields['default_user_role'] = array(
		'title'       => __( 'Default New User Role', 'oidc-attribute-mu-plugin' ),
		'description' => __( 'Set the default role assigned to users when the IDP doesn\'t provide a role.', 'oidc-attribute-mu-plugin' ),
		'type'        => 'select',
		'options'     => $roles,
		'section'     => 'user_settings',
	);

	return $fields;

}
add_filter( 'openid-connect-generic-settings-fields', 'oidc_attribute_add_default_role_setting', 10, 1 );

/**
 * Adds new settings that allows mapping IDP roles to WordPress roles.
 *
 * @link https://github.com/daggerhart/openid-connect-generic#openid-connect-generic-settings-fields
 *
 * @param array<mixed> $fields The array of settings fields.
 *
 * @return array<mixed>
 */
function oidc_attribute_role_mapping_setting( $fields ) {

	// @var WP_Roles $wp_roles_obj
	$wp_roles_obj = wp_roles();
	// @var array<string> $roles
	$roles = $wp_roles_obj->get_names();

	foreach ( $roles as $role ) {
		$fields[ 'oidc_idp_' . strtolower( $role ) . '_roles' ] = array(
			'title'       => sprintf( __( 'IDP Role for WordPress %ss', 'oidc-attribute-mu-plugin' ), $role ),
			'description' => sprintf(
				__( 'Semi-colon(;) separated list of IDP roles to map to the %s WordPress role', 'oidc-attribute-mu-plugin' ),
				$role
			),
			'type'        => 'text',
			'section'     => 'user_settings',
		);
	}

	return $fields;

}
add_filter( 'openid-connect-generic-settings-fields', 'oidc_attribute_role_mapping_setting', 10, 1 );

/**
 * Adds new settings that allows mapping IDP attribute to WordPress user metadata.
 *
 * @link https://github.com/daggerhart/openid-connect-generic#openid-connect-generic-settings-fields
 *
 * @param array<mixed> $fields The array of settings fields.
 *
 * @return array<mixed>
 */
function oidc_attribute_mapping_setting( $fields ) {

	// configuration field for attribute
	$fields[ 'oidc_idp_attribute_field' ] = array(
		'title'       => sprintf( __( 'IDP attribute to map', 'oidc-attribute-mu-plugin' ), $role ),

		'type'        => 'text',
		'section'     => 'user_settings',
	);

	// configuration field for user metadata
	$fields[ 'oidc_idp_usermetadata_field' ] = array(
		'title'       => sprintf( __( 'User Metadata field', 'oidc-attribute-mu-plugin' ), $role ),
		'description' => sprintf(
			__( 'field name for user metadata', 'oidc-attribute-mu-plugin' ),
			$role
		),
		'type'        => 'text',
		'section'     => 'user_settings',
	);

	return $fields;

}
add_filter( 'openid-connect-generic-settings-fields', 'oidc_attribute_mapping_setting', 10, 1 );


/**
 * Determine whether user should be created using plugin settings & IDP identity.
 *
 * @param bool         $result     The plugin user creation test flag.
 * @param array<mixed> $user_claim The authenticated user's IDP Identity Token user claim.
 *
 * @return bool
 */
function oidc_attribute_user_creation_test( $result, $user_claim ) {

	// @var array<mixed> $settings
	$settings = get_option( 'openid_connect_generic_settings', array() );

	// If the custom IDP role requirement setting is enabled validate user claim.
	if ( ! empty( $settings['require_idp_user_role'] ) && boolval( $settings['require_idp_user_role'] ) ) {
		// The default is to not create an account unless a mapping is found.
		$result = false;
		// @var WP_Roles $wp_roles_obj
		$wp_roles_obj = wp_roles();
		// @var array<string> $roles
		$roles = $wp_roles_obj->get_names();

		// Check the user claim for the `user-realm-role` key to lookup the WordPress role mapping.
		if ( ! empty( $settings ) && ! empty( $user_claim['grous'] ) ) {
			foreach ( $user_claim['groups'] as $idp_role ) {
				foreach ( $roles as $role_id => $role_name ) {
					if ( ! empty( $settings[ 'oidc_idp_' . strtolower( $role_name ) . '_roles' ] ) ) {
						if ( in_array( $idp_role, explode( ';', $settings[ 'oidc_idp_' . strtolower( $role_name ) . '_roles' ] ) ) ) {
							$result = true;
						}
					}
				}
			}
		}
	}

	return $result;

}
add_filter( 'openid-connect-generic-user-creation-test', 'oidc_attribute_user_creation_test', 10, 2 );

/**
 * Set user role on based on IDP role after authentication.
 *
 * @param WP_User      $user       The authenticated user's WP_User object.
 * @param array<mixed> $user_claim The IDP provided Identity Token user claim array.
 *
 * @return void
 */
function oidc_attribute_map_user_role( $user, $user_claim ) {

	// @var WP_Roles $wp_roles_obj
	$wp_roles_obj = wp_roles();
	// @var array<string> $roles
	$roles = $wp_roles_obj->get_names();
	// @var array<mixed> $settings
	$settings = get_option( 'openid_connect_generic_settings', array() );

	// Check the user claim for the `user-realm-role` key to lookup the WordPress role for mapping.
	// mapping OIDC groups to Wordpress roles
	if ( ! empty( $settings ) && ! empty( $user_claim['groups'] ) ) {
		// @var int $role_count
		$role_count = 0;

		foreach ( $user_claim['groups'] as $idp_role ) {
			foreach ( $roles as $role_id => $role_name ) {
				if ( ! empty( $settings[ 'oidc_idp_' . strtolower( $role_name ) . '_roles' ] ) ) {
					if ( in_array( $idp_role, explode( ';', $settings[ 'oidc_idp_' . strtolower( $role_name ) . '_roles' ] ) ) ) {
						$user->add_role( $role_id );
						$role_count++;
					}
				}
			}
		}

		if ( intval( $role_count ) == 0 && ! empty( $settings['default_user_role'] ) ) {
			if ( boolval( $settings['default_user_role'] ) ) {
				$user->set_role( $settings['default_user_role'] );
			}
		}
	}

}
// update role when user reconnectes (update use is already existing)
add_action( 'openid-connect-generic-update-user-using-current-claim', 'oidc_attribute_map_user_role', 10, 2 );

// add role upon user creation (at first login))
add_action( 'openid-connect-generic-user-create', 'oidc_attribute_map_user_role', 10, 2 );


/**
 * Set user metadata on based on IDP attribute after authentication.
 *
 * @param WP_User      $user       The authenticated user's WP_User object.
 * @param array<mixed> $user_claim The IDP provided Identity Token user claim array.
 *
 * @return void
 */
function oidc_attribute_map_user_field( $user, $user_claim ) {


	// @var array<mixed> $settings
	$settings = get_option( 'openid_connect_generic_settings', array() );

	if ( ! empty( $settings[ 'oidc_idp_attribute_field' ] ) ) {
		$attribute = $settings[ 'oidc_idp_attribute_field' ];
	}
	if ( ! empty( $settings[ 'oidc_idp_usermetadata_field' ] ) ) {
		$usermetadata_field = $settings[ 'oidc_idp_usermetadata_field' ];
	}
	

	// Check the user claim for the attribute key to lookup the WordPress field for mapping.
	// mapping OIDC attrributes to Wordpress metadata
	// if ( ! empty( $settings ) && ! empty( $user_claim['children'] ) ) {
		update_user_meta( $user->id, $usermetadata_field, $user_claim[$attribute] );	
		// $user->set( 'children', 'attr. '.$attribute.'='.$user_claim[$attribute].'-> usermeta field:'. $usermetadata_field);

	// }


}
// update role when user reconnectes (update use is already existing)
add_action( 'openid-connect-generic-update-user-using-current-claim', 'oidc_attribute_map_user_field', 10, 2 );

// add role upon user creation (at first login))
add_action( 'openid-connect-generic-user-create', 'oidc_attribute_map_user_field', 10, 2 );

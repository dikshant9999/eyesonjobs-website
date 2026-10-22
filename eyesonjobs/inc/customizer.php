<?php
/**
 * Customizer: contact details and social links, editable from
 * Appearance → Customize → Eyes on Jobs details.
 *
 * @package EyesOnJobs
 */

defined( 'ABSPATH' ) || exit;

/**
 * Default values, taken from the current eyesonjobs.com site.
 */
function eoj_defaults() {
	return array(
		'eoj_phone'        => '+91 99991 27911',
		'eoj_phone_2'      => '+91 124 4079570',
		'eoj_whatsapp'     => '919999127911',
		'eoj_email'        => 'career@eyesonjobs.com',
		'eoj_email_2'      => 'vikas@eyesonjobs.com',
		'eoj_address'      => "Bank of Baroda Building, Old Railway Road,\n285/9, Shivpuri Extension, Sector 7,\nGurugram, Haryana 122006",
		'eoj_hours'        => 'Monday to Saturday, 10:00 AM to 7:00 PM',
		'eoj_tagline'      => 'One search engine for more jobs. Trusted recruitment partner for employers and professionals across India.',
		'eoj_facebook'     => 'https://www.facebook.com/eyesonjobs/',
		'eoj_twitter'      => 'https://twitter.com/EyesJobs',
		'eoj_linkedin'     => 'https://www.linkedin.com/company/www-eyesonjobs-com/',
		'eoj_instagram'    => '',
		'eoj_pinterest'    => 'https://in.pinterest.com/eyesonnjobs/',
		'eoj_show_wa'      => true,
		'eoj_announcement' => 'New: Complete Job Preparation Package at 50% off. ₹3,540 only.',
		'eoj_announce_url' => '/job-preparation-package/',
	);
}

/**
 * Read a theme option with its default.
 *
 * @param string $key Option key.
 * @return mixed
 */
function eoj_opt( $key ) {
	$defaults = eoj_defaults();
	return get_theme_mod( $key, isset( $defaults[ $key ] ) ? $defaults[ $key ] : '' );
}

/**
 * Register Customizer controls.
 *
 * @param WP_Customize_Manager $wp_customize Manager.
 */
function eoj_customize_register( $wp_customize ) {
	$wp_customize->add_section(
		'eoj_details',
		array(
			'title'       => __( 'Eyes on Jobs details', 'eyesonjobs' ),
			'description' => __( 'Phone numbers, emails, address and social links shown in the header, footer and contact page.', 'eyesonjobs' ),
			'priority'    => 30,
		)
	);

	$fields = array(
		'eoj_announcement' => array( __( 'Top announcement bar text (leave empty to hide)', 'eyesonjobs' ), 'text' ),
		'eoj_announce_url' => array( __( 'Announcement link', 'eyesonjobs' ), 'text' ),
		'eoj_phone'        => array( __( 'Main phone', 'eyesonjobs' ), 'text' ),
		'eoj_phone_2'      => array( __( 'Office phone', 'eyesonjobs' ), 'text' ),
		'eoj_whatsapp'     => array( __( 'WhatsApp number (country code + number, digits only)', 'eyesonjobs' ), 'text' ),
		'eoj_show_wa'      => array( __( 'Show floating WhatsApp button', 'eyesonjobs' ), 'checkbox' ),
		'eoj_email'        => array( __( 'Main email', 'eyesonjobs' ), 'email' ),
		'eoj_email_2'      => array( __( 'Second email', 'eyesonjobs' ), 'email' ),
		'eoj_address'      => array( __( 'Office address', 'eyesonjobs' ), 'textarea' ),
		'eoj_hours'        => array( __( 'Office hours', 'eyesonjobs' ), 'text' ),
		'eoj_tagline'      => array( __( 'Footer description', 'eyesonjobs' ), 'textarea' ),
		'eoj_facebook'     => array( 'Facebook URL', 'url' ),
		'eoj_twitter'      => array( 'X / Twitter URL', 'url' ),
		'eoj_linkedin'     => array( 'LinkedIn URL', 'url' ),
		'eoj_instagram'    => array( 'Instagram URL', 'url' ),
		'eoj_pinterest'    => array( 'Pinterest URL', 'url' ),
	);

	$defaults = eoj_defaults();
	foreach ( $fields as $id => $field ) {
		list( $label, $type ) = $field;
		$sanitize             = 'sanitize_text_field';
		if ( 'url' === $type ) {
			$sanitize = 'esc_url_raw';
		} elseif ( 'email' === $type ) {
			$sanitize = 'sanitize_email';
		} elseif ( 'textarea' === $type ) {
			$sanitize = 'sanitize_textarea_field';
		} elseif ( 'checkbox' === $type ) {
			$sanitize = 'wp_validate_boolean';
		}
		$wp_customize->add_setting(
			$id,
			array(
				'default'           => $defaults[ $id ],
				'sanitize_callback' => $sanitize,
			)
		);
		$wp_customize->add_control(
			$id,
			array(
				'label'   => $label,
				'section' => 'eoj_details',
				'type'    => $type,
			)
		);
	}
}
add_action( 'customize_register', 'eoj_customize_register' );

<?php
/**
 * Plugin Name:       Eyes on Jobs Core
 * Plugin URI:        https://eyesonjobs.com/
 * Description:       Jobs, candidate and employer accounts, enquiry and resume forms with Google reCAPTCHA v3, and the Job Preparation Package registration with Razorpay payments (UPI, cards, net banking, wallets). Every submission is emailed to your team and saved under Submissions.
 * Version:           1.0.0
 * Requires at least: 6.4
 * Requires PHP:      7.4
 * Author:            Eyes on Jobs
 * License:           GPL-2.0-or-later
 * Text Domain:       eyesonjobs-core
 *
 * @package EyesOnJobsCore
 */

defined( 'ABSPATH' ) || exit;

define( 'EOJ_CORE_VERSION', '1.0.0' );
define( 'EOJ_CORE_FILE', __FILE__ );
define( 'EOJ_CORE_DIR', plugin_dir_path( __FILE__ ) );
define( 'EOJ_CORE_URL', plugin_dir_url( __FILE__ ) );

require EOJ_CORE_DIR . 'includes/settings.php';
require EOJ_CORE_DIR . 'includes/helpers.php';
require EOJ_CORE_DIR . 'includes/entries.php';
require EOJ_CORE_DIR . 'includes/fields.php';
require EOJ_CORE_DIR . 'includes/forms.php';
require EOJ_CORE_DIR . 'includes/jobs.php';
require EOJ_CORE_DIR . 'includes/accounts.php';
require EOJ_CORE_DIR . 'includes/jpp.php';

/**
 * Activation: roles, default settings, job terms, rewrite rules.
 */
function eoj_core_activate() {
	eoj_register_roles();
	eoj_register_job_types();
	eoj_register_entry_type();
	eoj_seed_job_terms();
	if ( ! get_option( 'eoj_settings' ) ) {
		update_option( 'eoj_settings', eoj_default_settings() );
	}
	eoj_private_upload_dir();
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'eoj_core_activate' );

register_deactivation_hook(
	__FILE__,
	function () {
		flush_rewrite_rules();
	}
);

/**
 * Front-end script for forms, reCAPTCHA, account tabs and Razorpay.
 */
function eoj_core_assets() {
	wp_register_script( 'eoj-forms', EOJ_CORE_URL . 'assets/forms.js', array(), EOJ_CORE_VERSION, array( 'in_footer' => true ) );
	wp_register_script( 'eoj-qr', EOJ_CORE_URL . 'assets/vendor/qrcode.js', array(), '1.4.4', array( 'in_footer' => true ) );
	wp_localize_script(
		'eoj-forms',
		'eojForms',
		array(
			'recaptchaKey' => eoj_recaptcha_enabled() ? eoj_setting( 'recaptcha_site' ) : '',
			'maxUpload'    => eoj_max_upload_bytes(),
			'i18n'         => array(
				'sending'   => __( 'Sending…', 'eyesonjobs-core' ),
				'tooBig'    => __( 'This file is too large. Please upload a file under 5 MB.', 'eyesonjobs-core' ),
				'qrWait'    => __( 'Generating QR…', 'eyesonjobs-core' ),
				'qrError'   => __( 'We could not create a QR code. Please try again.', 'eyesonjobs-core' ),
				'qrExpired' => __( 'This QR code has expired.', 'eyesonjobs-core' ),
				'qrExpires' => __( 'Expires in', 'eyesonjobs-core' ),
				'qrApps'    => __( 'On your phone, tap your app:', 'eyesonjobs-core' ),
				'qrAlt'     => __( 'UPI QR code', 'eyesonjobs-core' ),
				'upiId'     => __( 'UPI ID', 'eyesonjobs-core' ),
				'cardTitle' => __( 'Pay with card', 'eyesonjobs-core' ),
				'payFailed' => __( 'Payment was not completed. You can try again, or choose another payment method.', 'eyesonjobs-core' ),
			),
		)
	);
	if ( ! wp_style_is( 'eoj-main', 'registered' ) && ! wp_style_is( 'eoj-main', 'enqueued' ) ) {
		wp_register_style( 'eoj-forms', EOJ_CORE_URL . 'assets/forms.css', array(), EOJ_CORE_VERSION );
	}
}
add_action( 'wp_enqueue_scripts', 'eoj_core_assets', 20 );

/**
 * Load the form script (and reCAPTCHA) only on pages that render a form.
 */
function eoj_enqueue_form_assets() {
	wp_enqueue_script( 'eoj-forms' );
	if ( wp_style_is( 'eoj-forms', 'registered' ) ) {
		wp_enqueue_style( 'eoj-forms' );
	}
	if ( eoj_recaptcha_enabled() ) {
		wp_enqueue_script( 'google-recaptcha', 'https://www.google.com/recaptcha/api.js?render=' . rawurlencode( eoj_setting( 'recaptcha_site' ) ), array(), null, array( 'in_footer' => true ) ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
	}
}

/**
 * Settings link on the Plugins screen.
 */
add_filter(
	'plugin_action_links_' . plugin_basename( __FILE__ ),
	function ( $links ) {
		array_unshift( $links, '<a href="' . esc_url( admin_url( 'options-general.php?page=eoj-settings' ) ) . '">' . esc_html__( 'Settings', 'eyesonjobs-core' ) . '</a>' );
		return $links;
	}
);

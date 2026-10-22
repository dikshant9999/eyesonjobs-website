<?php
/**
 * Settings → Eyes on Jobs.
 *
 * @package EyesOnJobsCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Defaults.
 */
function eoj_default_settings() {
	return array(
		'notify_emails'           => 'career@eyesonjobs.com, vikas@eyesonjobs.com',
		'from_name'               => 'Eyes on Jobs',
		'allow_registration'      => 1,
		'recaptcha_site'          => '',
		'recaptcha_secret'        => '',
		'recaptcha_threshold'     => '0.5',
		'razorpay_key_id'         => '',
		'razorpay_key_secret'     => '',
		'razorpay_webhook_secret' => '',
		'pay_upi_id'               => '',
		'pay_upi_name'             => 'Eyes on Jobs',
		'pay_paypal'               => '',
		'jpp_name'                => 'Complete Job Preparation Package',
		'jpp_mrp'                 => '7080',
		'jpp_price'               => '3540',
	);
}

/**
 * Read one setting.
 *
 * @param string $key Key.
 * @return string
 */
function eoj_setting( $key ) {
	$all = wp_parse_args( (array) get_option( 'eoj_settings', array() ), eoj_default_settings() );
	return isset( $all[ $key ] ) ? $all[ $key ] : '';
}

/**
 * Field definitions for the settings screen.
 */
function eoj_settings_fields() {
	return array(
		'eoj_section_email'    => array(
			'title'  => __( 'Where submissions are sent', 'eyesonjobs-core' ),
			'intro'  => __( 'Every form on the site (contact, resume, job applications, employer requirements, registrations and payments) is emailed to these addresses and also saved under Submissions in the dashboard.', 'eyesonjobs-core' ),
			'fields' => array(
				'notify_emails'      => array( __( 'Notification emails', 'eyesonjobs-core' ), 'text', __( 'Separate several addresses with commas.', 'eyesonjobs-core' ) ),
				'from_name'          => array( __( 'Sender name', 'eyesonjobs-core' ), 'text', '' ),
				'allow_registration' => array( __( 'Allow candidate and employer sign-up', 'eyesonjobs-core' ), 'checkbox', '' ),
			),
		),
		'eoj_section_captcha'  => array(
			'title'  => __( 'Google reCAPTCHA v3 (spam protection)', 'eyesonjobs-core' ),
			'intro'  => __( 'Get free keys at https://www.google.com/recaptcha/admin/create : choose “Score based (v3)” and add your domain (eyesonjobs.com). Leave empty to use only the built-in honeypot protection.', 'eyesonjobs-core' ),
			'fields' => array(
				'recaptcha_site'      => array( __( 'Site key', 'eyesonjobs-core' ), 'text', '' ),
				'recaptcha_secret'    => array( __( 'Secret key', 'eyesonjobs-core' ), 'password', '' ),
				'recaptcha_threshold' => array( __( 'Minimum score (0.1 to 0.9)', 'eyesonjobs-core' ), 'text', __( '0.5 is a good default. Lower it if real visitors are being blocked.', 'eyesonjobs-core' ) ),
			),
		),
		'eoj_section_razorpay' => array(
			'title'  => __( 'Razorpay payments', 'eyesonjobs-core' ),
			'intro'  => __( 'Sign up at https://razorpay.com and complete KYC (Razorpay collects your bank account details securely and settles payments there). Then go to Account & Settings → API Keys. Use Test keys (rzp_test_…) first, then switch to Live keys (rzp_live_…). Until keys are added, registrations are still emailed and the candidate is told you will send payment details.', 'eyesonjobs-core' ),
			'fields' => array(
				'razorpay_key_id'         => array( __( 'Key ID', 'eyesonjobs-core' ), 'text', '' ),
				'razorpay_key_secret'     => array( __( 'Key secret', 'eyesonjobs-core' ), 'password', '' ),
				'razorpay_webhook_secret' => array( __( 'Webhook secret (recommended)', 'eyesonjobs-core' ), 'password', '' ),
			),
		),
		'eoj_section_manual'   => array(
			'title'  => __( 'Direct payments (UPI and PayPal)', 'eyesonjobs-core' ),
			'intro'  => __( 'Optional. Used when Razorpay is not connected, or as an extra option. UPI shows a QR code that is valid for five minutes, plus PhonePe, Google Pay, Paytm and BHIM buttons on phones. After paying, the customer enters their transaction reference and you confirm it under Submissions. Your bank account number is never shown to customers. Card payments go through Razorpay above, so card numbers never touch this website.', 'eyesonjobs-core' ),
			'fields' => array(
				'pay_upi_id'       => array( __( 'UPI ID', 'eyesonjobs-core' ), 'text', __( 'For example eyesonjobs@okhdfcbank', 'eyesonjobs-core' ) ),
				'pay_upi_name'     => array( __( 'Name shown on UPI', 'eyesonjobs-core' ), 'text', '' ),
				'pay_paypal'       => array( __( 'PayPal.me link', 'eyesonjobs-core' ), 'text', __( 'For example https://paypal.me/yourname . Best for customers outside India.', 'eyesonjobs-core' ) ),
			),
		),
		'eoj_section_jpp'      => array(
			'title'  => __( 'Job Preparation Package', 'eyesonjobs-core' ),
			'intro'  => __( 'Prices in rupees, inclusive of all taxes. The original price is shown struck through.', 'eyesonjobs-core' ),
			'fields' => array(
				'jpp_name'  => array( __( 'Package name', 'eyesonjobs-core' ), 'text', '' ),
				'jpp_mrp'   => array( __( 'Original price (₹)', 'eyesonjobs-core' ), 'number', '' ),
				'jpp_price' => array( __( 'Offer price (₹)', 'eyesonjobs-core' ), 'number', '' ),
			),
		),
	);
}

add_action(
	'admin_menu',
	function () {
		add_options_page( __( 'Eyes on Jobs', 'eyesonjobs-core' ), __( 'Eyes on Jobs', 'eyesonjobs-core' ), 'manage_options', 'eoj-settings', 'eoj_settings_screen' );
	}
);

add_action(
	'admin_init',
	function () {
		register_setting(
			'eoj_settings',
			'eoj_settings',
			array(
				'type'              => 'array',
				'sanitize_callback' => 'eoj_sanitize_settings',
				'default'           => eoj_default_settings(),
			)
		);
		foreach ( eoj_settings_fields() as $section_id => $section ) {
			add_settings_section(
				$section_id,
				$section['title'],
				function () use ( $section ) {
					echo '<p>' . wp_kses_post( make_clickable( esc_html( $section['intro'] ) ) ) . '</p>';
				},
				'eoj-settings'
			);
			foreach ( $section['fields'] as $key => $field ) {
				add_settings_field(
					$key,
					$field[0],
					'eoj_settings_field',
					'eoj-settings',
					$section_id,
					array(
						'key'       => $key,
						'type'      => $field[1],
						'help'      => $field[2],
						'label_for' => 'eoj-' . $key,
					)
				);
			}
		}
	}
);

/**
 * Render one settings field.
 *
 * @param array $args Args.
 */
function eoj_settings_field( $args ) {
	$value = eoj_setting( $args['key'] );
	$name  = 'eoj_settings[' . $args['key'] . ']';
	$id    = 'eoj-' . $args['key'];
	if ( 'checkbox' === $args['type'] ) {
		printf( '<input type="checkbox" id="%1$s" name="%2$s" value="1" %3$s>', esc_attr( $id ), esc_attr( $name ), checked( (int) $value, 1, false ) );
	} else {
		printf(
			'<input type="%1$s" id="%2$s" name="%3$s" value="%4$s" class="regular-text" autocomplete="off" %5$s>',
			esc_attr( $args['type'] ),
			esc_attr( $id ),
			esc_attr( $name ),
			esc_attr( $value ),
			'number' === $args['type'] ? 'min="0" step="1"' : ''
		);
	}
	if ( $args['help'] ) {
		echo '<p class="description">' . esc_html( $args['help'] ) . '</p>';
	}
}

/**
 * Sanitize settings.
 *
 * @param array $input Raw.
 * @return array
 */
function eoj_sanitize_settings( $input ) {
	$input = (array) $input;
	$out   = eoj_default_settings();
	foreach ( $out as $key => $default ) {
		$raw = isset( $input[ $key ] ) ? $input[ $key ] : '';
		switch ( $key ) {
			case 'notify_emails':
				$emails = array_filter( array_map( 'sanitize_email', array_map( 'trim', explode( ',', $raw ) ) ), 'is_email' );
				$out[ $key ] = $emails ? implode( ', ', $emails ) : get_option( 'admin_email' );
				break;
			case 'allow_registration':
				$out[ $key ] = empty( $raw ) ? 0 : 1;
				break;
			case 'recaptcha_threshold':
				$out[ $key ] = (string) min( 0.9, max( 0.1, (float) $raw ? (float) $raw : 0.5 ) );
				break;
			case 'pay_paypal':
				$out[ $key ] = esc_url_raw( trim( $raw ), array( 'https' ) );
				break;
			case 'pay_upi_id':
				$out[ $key ] = preg_replace( '/[^A-Za-z0-9._\-@]/', '', $raw );
				break;
			case 'jpp_mrp':
			case 'jpp_price':
				$out[ $key ] = (string) absint( $raw );
				break;
			default:
				$out[ $key ] = sanitize_text_field( $raw );
		}
	}
	return $out;
}

/**
 * Settings screen.
 */
function eoj_settings_screen() {
	$webhook = rest_url( 'eoj/v1/razorpay-webhook' );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Eyes on Jobs settings', 'eyesonjobs-core' ); ?></h1>
		<form method="post" action="options.php">
			<?php
			settings_fields( 'eoj_settings' );
			do_settings_sections( 'eoj-settings' );
			?>
			<h2><?php esc_html_e( 'Razorpay webhook (optional, recommended)', 'eyesonjobs-core' ); ?></h2>
			<p><?php esc_html_e( 'In the Razorpay Dashboard go to Account & Settings → Webhooks → Add new webhook. Use this URL, tick the events payment.captured, order.paid and qr_code.credited, choose a secret and paste the same secret above. This confirms payments even if a customer closes the browser right after paying.', 'eyesonjobs-core' ); ?></p>
			<p><code><?php echo esc_html( $webhook ); ?></code></p>
			<?php submit_button(); ?>
		</form>
		<p><?php esc_html_e( 'Tip: install the free “WP Mail SMTP” plugin and connect it to your email provider so notification emails never land in spam.', 'eyesonjobs-core' ); ?></p>
	</div>
	<?php
}

/**
 * Remind the admin which keys are still missing.
 */
add_action(
	'admin_notices',
	function () {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$screen = get_current_screen();
		if ( ! $screen || ! in_array( $screen->id, array( 'dashboard', 'settings_page_eoj-settings', 'plugins' ), true ) ) {
			return;
		}
		$missing = array();
		if ( ! eoj_recaptcha_enabled() ) {
			$missing[] = 'Google reCAPTCHA';
		}
		if ( ! eoj_razorpay_enabled() ) {
			$missing[] = 'Razorpay';
		}
		if ( $missing ) {
			printf(
				'<div class="notice notice-warning"><p>%1$s <a href="%2$s">%3$s</a></p></div>',
				/* translators: %s list of services */
				esc_html( sprintf( __( 'Eyes on Jobs: add your %s keys to switch on spam protection and online payments.', 'eyesonjobs-core' ), implode( ' and ', $missing ) ) ),
				esc_url( admin_url( 'options-general.php?page=eoj-settings' ) ),
				esc_html__( 'Open settings', 'eyesonjobs-core' )
			);
		}
	}
);

/**
 * Whether reCAPTCHA keys are set.
 */
function eoj_recaptcha_enabled() {
	return eoj_setting( 'recaptcha_site' ) && eoj_setting( 'recaptcha_secret' );
}

/**
 * Whether any direct payment detail is set.
 */
function eoj_manual_pay_enabled() {
	return eoj_setting( 'pay_upi_id' ) || eoj_setting( 'pay_paypal' );
}

/**
 * Whether Razorpay keys are set.
 */
function eoj_razorpay_enabled() {
	return eoj_setting( 'razorpay_key_id' ) && eoj_setting( 'razorpay_key_secret' );
}

<?php
/**
 * Complete Job Preparation Package: registration, Razorpay payment,
 * signature verification, webhook and offline "mark as paid".
 *
 * @package EyesOnJobsCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Package prices from Settings → Eyes on Jobs.
 *
 * @return array { mrp: float, price: float }
 */
function eoj_core_prices() {
	return array(
		'mrp'   => (float) eoj_setting( 'jpp_mrp' ),
		'price' => (float) eoj_setting( 'jpp_price' ),
	);
}

/**
 * Format rupees with Indian digit grouping.
 *
 * @param float $amount Amount.
 */
function eoj_core_inr( $amount ) {
	$amount = (string) round( $amount );
	$last3  = substr( $amount, -3 );
	$rest   = substr( $amount, 0, -3 );
	if ( '' !== $rest ) {
		$rest  = preg_replace( '/\B(?=(\d{2})+(?!\d))/', ',', $rest );
		$last3 = $rest . ',' . $last3;
	}
	return '₹' . $last3;
}

/**
 * Registration form fields.
 */
function eoj_jpp_fields() {
	return array(
		array( 'name' => 'name', 'label' => __( 'Full name', 'eyesonjobs-core' ), 'type' => 'text', 'required' => true, 'autocomplete' => 'name' ),
		array( 'name' => 'email', 'label' => __( 'Email', 'eyesonjobs-core' ), 'type' => 'email', 'required' => true, 'half' => true, 'autocomplete' => 'email' ),
		array( 'name' => 'phone', 'label' => __( 'Mobile number', 'eyesonjobs-core' ), 'type' => 'tel', 'required' => true, 'half' => true, 'autocomplete' => 'tel', 'placeholder' => '+91 98765 43210' ),
		array( 'name' => 'city', 'label' => __( 'City', 'eyesonjobs-core' ), 'type' => 'text', 'required' => true, 'half' => true, 'autocomplete' => 'address-level2' ),
		array( 'name' => 'current_role', 'label' => __( 'Current role', 'eyesonjobs-core' ), 'type' => 'text', 'required' => true, 'half' => true, 'placeholder' => __( 'e.g. Sales Executive, or “Student”', 'eyesonjobs-core' ) ),
		array( 'name' => 'experience', 'label' => __( 'Total experience', 'eyesonjobs-core' ), 'type' => 'select', 'required' => true, 'half' => true, 'options' => eoj_experience_options() ),
		array( 'name' => 'target_role', 'label' => __( 'Role you are targeting', 'eyesonjobs-core' ), 'type' => 'text', 'required' => true, 'half' => true ),
		array( 'name' => 'current_ctc', 'label' => __( 'Current CTC (optional)', 'eyesonjobs-core' ), 'type' => 'text', 'half' => true, 'placeholder' => __( 'e.g. 6 LPA', 'eyesonjobs-core' ) ),
		array(
			'name'     => 'mode',
			'label'    => __( 'Preferred session mode', 'eyesonjobs-core' ),
			'type'     => 'select',
			'required' => true,
			'half'     => true,
			'options'  => array(
				'Online'          => __( 'Online (video call)', 'eyesonjobs-core' ),
				'Phone'           => __( 'Phone call', 'eyesonjobs-core' ),
				'Gurugram office' => __( 'Gurugram office', 'eyesonjobs-core' ),
			),
		),
		array( 'name' => 'resume', 'label' => __( 'Current resume (optional)', 'eyesonjobs-core' ), 'type' => 'file' ),
		array( 'name' => 'notes', 'label' => __( 'Anything we should know? (optional)', 'eyesonjobs-core' ), 'type' => 'textarea', 'rows' => 3 ),
		array(
			'name'     => 'consent',
			'label'    => __( 'I understand this package covers career preparation only and does not guarantee placement, interview calls, selection, an offer letter or employment.', 'eyesonjobs-core' ),
			'type'     => 'checkbox',
			'required' => true,
		),
	);
}

add_filter(
	'eoj_form_handlers',
	function ( $handlers ) {
		$handlers['eoj_jpp'] = 'eoj_submit_jpp';
		return $handlers;
	}
);

/**
 * The page URL without our status and payment arguments.
 */
function eoj_jpp_base_url() {
	// phpcs:ignore WordPress.Security.NonceVerification.Missing
	$back = isset( $_POST['eoj_back'] ) ? esc_url_raw( wp_unslash( $_POST['eoj_back'] ) ) : '';
	$back = wp_validate_redirect( $back, eoj_page_url( 'job-preparation-package/register', '/job-preparation-package/register/' ) );
	return remove_query_arg( array( 'eoj_form', 'eoj_status', 'eoj_msg', 'eoj_t', 'reg', 'k' ), $back );
}

/**
 * Save a registration and move the visitor to the payment step.
 *
 * @param string $form Form id.
 */
function eoj_submit_jpp( $form ) {
	$check = eoj_check_request( $form );
	if ( true !== $check ) {
		eoj_redirect_back( $form, false, $check );
	}
	$fields = eoj_jpp_fields();
	$result = eoj_collect_fields( $fields );
	if ( $result['errors'] ) {
		eoj_fail_with_fields( $form, $fields, $result );
	}

	$v      = $result['values'];
	$prices = eoj_core_prices();
	$key    = wp_generate_password( 24, false );
	$rows   = array_merge(
		array(
			__( 'Package', 'eyesonjobs-core' ) => eoj_setting( 'jpp_name' ),
			__( 'Amount', 'eyesonjobs-core' )  => eoj_core_inr( $prices['price'] ) . ' ' . __( '(inclusive of all taxes)', 'eyesonjobs-core' ),
		),
		eoj_labelled_values( $fields, $v )
	);
	$file   = isset( $result['files']['resume'] ) ? $result['files']['resume'] : '';

	$meta = array(
		'name'   => $v['name'],
		'email'  => $v['email'],
		'phone'  => $v['phone'],
		'file'   => $file,
		'status' => 'pending',
		'amount' => $prices['price'],
		'key'    => $key,
	);
	if ( is_user_logged_in() ) {
		$meta['user'] = get_current_user_id();
	}
	$id = eoj_save_entry( 'jpp', $rows, $meta );
	if ( ! $id ) {
		eoj_discard_files( $result['files'] );
		eoj_redirect_back( $form, false, 'error' );
	}

	eoj_mail(
		eoj_notify_emails(),
		sprintf( /* translators: %s name */ __( 'New registration (payment pending): %s', 'eyesonjobs-core' ), $v['name'] ),
		sprintf( /* translators: %s link */ __( 'Someone registered for the Job Preparation Package. Payment is pending. <a href="%s">Open the submission</a>.', 'eyesonjobs-core' ), esc_url( admin_url( 'post.php?post=' . $id . '&action=edit' ) ) ),
		$rows,
		$file ? array( $file ) : array(),
		$v['email']
	);

	$pay_line = eoj_razorpay_enabled() ? __( 'Please complete the secure online payment to confirm your place.', 'eyesonjobs-core' ) : __( 'Our team will email you the payment details within one working day.', 'eyesonjobs-core' );
	eoj_mail(
		$v['email'],
		__( 'We received your registration', 'eyesonjobs-core' ),
		'<p>' . esc_html( sprintf( /* translators: %s name */ __( 'Hi %s,', 'eyesonjobs-core' ), $v['name'] ) ) . '</p><p>' . esc_html__( 'Thank you for registering for the Complete Job Preparation Package.', 'eyesonjobs-core' ) . ' ' . esc_html( $pay_line ) . '</p>'
	);

	wp_safe_redirect(
		add_query_arg(
			array(
				'reg' => $id,
				'k'   => $key,
			),
			eoj_jpp_base_url()
		) . '#pay'
	);
	exit;
}

/**
 * Look up a registration by id and access key.
 *
 * @param int    $id  Entry.
 * @param string $key Key.
 * @return WP_Post|null
 */
function eoj_jpp_find( $id, $key ) {
	$entry = $id ? get_post( $id ) : null;
	if ( ! $entry || 'eoj_entry' !== $entry->post_type || 'jpp' !== get_post_meta( $id, '_eoj_type', true ) ) {
		return null;
	}
	$stored = (string) get_post_meta( $id, '_eoj_key', true );
	return $stored && hash_equals( $stored, (string) $key ) ? $entry : null;
}

/**
 * Create (or reuse) the Razorpay order for a registration.
 *
 * @param int $id Entry.
 * @return string|WP_Error Order id.
 */
function eoj_jpp_get_order( $id ) {
	$paise    = (int) round( (float) get_post_meta( $id, '_eoj_amount', true ) * 100 );
	$existing = (string) get_post_meta( $id, '_eoj_order_id', true );
	if ( $existing && (int) get_post_meta( $id, '_eoj_order_amount', true ) === $paise ) {
		return $existing;
	}
	$response = wp_remote_post(
		'https://api.razorpay.com/v1/orders',
		array(
			'timeout' => 20,
			'headers' => array(
				'Authorization' => 'Basic ' . base64_encode( eoj_setting( 'razorpay_key_id' ) . ':' . eoj_setting( 'razorpay_key_secret' ) ), // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
				'Content-Type'  => 'application/json',
			),
			'body'    => wp_json_encode(
				array(
					'amount'   => $paise,
					'currency' => 'INR',
					'receipt'  => 'EOJ-JPP-' . $id,
					'notes'    => array(
						'entry_id' => (string) $id,
						'email'    => (string) get_post_meta( $id, '_eoj_email', true ),
					),
				)
			),
		)
	);
	if ( is_wp_error( $response ) ) {
		return $response;
	}
	$body = json_decode( wp_remote_retrieve_body( $response ), true );
	if ( 200 !== (int) wp_remote_retrieve_response_code( $response ) || empty( $body['id'] ) ) {
		$detail = isset( $body['error']['description'] ) ? $body['error']['description'] : 'Unexpected response';
		return new WP_Error( 'eoj_razorpay', sanitize_text_field( $detail ) );
	}
	update_post_meta( $id, '_eoj_order_id', sanitize_text_field( $body['id'] ) );
	update_post_meta( $id, '_eoj_order_amount', $paise );
	return $body['id'];
}

/**
 * The no-guarantee note, shown on every step.
 */
function eoj_jpp_disclaimer() {
	return '<p class="eoj-note eoj-note--small">' . esc_html__( 'This package covers career preparation only. It does not guarantee placement, interview calls, selection, an offer letter or employment.', 'eyesonjobs-core' ) . '</p>';
}

/**
 * [eoj_jpp_form]
 */
add_shortcode(
	'eoj_jpp_form',
	function () {
		eoj_enqueue_form_assets();
		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- the access key is the authorisation.
		$reg = isset( $_GET['reg'] ) ? absint( $_GET['reg'] ) : 0;
		$key = isset( $_GET['k'] ) ? sanitize_text_field( wp_unslash( $_GET['k'] ) ) : '';
		// phpcs:enable
		if ( $reg && $key ) {
			$entry = eoj_jpp_find( $reg, $key );
			if ( $entry ) {
				return eoj_jpp_payment_step( $entry, $key );
			}
		}

		$html  = '<form id="eoj_jpp" class="eoj-form" method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" enctype="multipart/form-data" data-recaptcha="' . esc_attr( eoj_recaptcha_action( 'eoj_jpp' ) ) . '">';
		$html .= eoj_form_hidden( 'eoj_jpp' ) . eoj_form_notice( 'eoj_jpp' );
		$html .= eoj_render_fields( 'eoj_jpp', eoj_jpp_fields(), eoj_user_defaults() );
		$html .= '<button type="submit" class="eoj-btn eoj-btn--primary eoj-btn--block eoj-btn--glow">' . esc_html__( 'Continue to payment', 'eyesonjobs-core' ) . '</button>';
		$html .= eoj_jpp_disclaimer() . eoj_recaptcha_note() . '</form>';
		return $html;
	}
);

/**
 * Step two: order summary and payment, or a status message.
 *
 * @param WP_Post $entry Registration.
 * @param string  $key   Access key.
 */
function eoj_jpp_payment_step( $entry, $key ) {
	$id     = $entry->ID;
	$name   = (string) get_post_meta( $id, '_eoj_name', true );
	$email  = (string) get_post_meta( $id, '_eoj_email', true );
	$phone  = (string) get_post_meta( $id, '_eoj_phone', true );
	$status = (string) get_post_meta( $id, '_eoj_status', true );
	$amount = (float) get_post_meta( $id, '_eoj_amount', true );
	$prices = eoj_core_prices();

	$html  = '<div id="pay" class="eoj-paybox">' . eoj_form_notice( 'eoj_jpp' );
	$html .= '<ul class="eoj-summary">';
	$html .= '<li><span>' . esc_html__( 'Package', 'eyesonjobs-core' ) . '</span><strong>' . esc_html( eoj_setting( 'jpp_name' ) ) . '</strong></li>';
	$html .= '<li><span>' . esc_html__( 'Name', 'eyesonjobs-core' ) . '</span><strong>' . esc_html( $name ) . '</strong></li>';
	$html .= '<li><span>' . esc_html__( 'Email', 'eyesonjobs-core' ) . '</span><strong>' . esc_html( $email ) . '</strong></li>';
	$html .= '<li><span>' . esc_html__( 'Reference', 'eyesonjobs-core' ) . '</span><strong>EOJ-JPP-' . (int) $id . '</strong></li></ul>';

	if ( 'paid' === $status ) {
		$html .= '<div class="eoj-alert eoj-alert--success" role="status"><span>' . esc_html__( 'Payment received. Thank you! Your consultant will contact you within one working day to schedule your sessions. A receipt has been emailed to you.', 'eyesonjobs-core' ) . '</span></div>';
		return $html . eoj_jpp_disclaimer() . '</div>';
	}

	$online = eoj_razorpay_enabled();
	$upi    = $online || eoj_setting( 'pay_upi_id' );
	$paypal = eoj_setting( 'pay_paypal' );
	if ( ! $online && ! $upi && ! $paypal ) {
		$html .= '<div class="eoj-alert eoj-alert--info" role="status"><span>' . esc_html__( 'Registration received. Our team will email you payment details within one working day.', 'eyesonjobs-core' ) . '</span></div>';
		return $html . eoj_jpp_disclaimer() . '</div>';
	}

	// Price.
	$html .= '<p class="eoj-price__amount">';
	if ( $prices['mrp'] > $amount ) {
		$html .= '<del aria-label="' . esc_attr__( 'Original price', 'eyesonjobs-core' ) . '">' . esc_html( eoj_core_inr( $prices['mrp'] ) ) . '</del>';
	}
	$html .= '<strong>' . esc_html( eoj_core_inr( $amount ) ) . '</strong></p>';
	$html .= '<p class="eoj-price__tax">' . esc_html__( 'Inclusive of all taxes', 'eyesonjobs-core' ) . '</p>';

	$claimed_ref = (string) get_post_meta( $id, '_eoj_claimed_ref', true );
	if ( $claimed_ref ) {
		$html .= '<div class="eoj-alert eoj-alert--info" role="status"><span>' . esc_html( sprintf( /* translators: %s payment reference */ __( 'We have your payment reference (%s). Our team will confirm it within one working day and email you a receipt.', 'eyesonjobs-core' ), $claimed_ref ) ) . '</span></div>';
	}

	// Card checkout (Razorpay window).
	$card_html = '';
	$verify    = '';
	if ( $online ) {
		$order = eoj_jpp_get_order( $id );
		if ( is_wp_error( $order ) ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( 'Eyes on Jobs Razorpay order failed: ' . $order->get_error_message() ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			}
			$card_html = '<div class="eoj-alert eoj-alert--error" role="alert"><span>' . esc_html__( 'We could not start the card payment just now. Please refresh the page and try again, or choose another method.', 'eyesonjobs-core' ) . '</span></div>';
		} else {
			$checkout  = array(
				'key'         => eoj_setting( 'razorpay_key_id' ),
				'order_id'    => $order,
				'amount'      => (int) round( $amount * 100 ),
				'currency'    => 'INR',
				'name'        => eoj_setting( 'from_name' ) ? eoj_setting( 'from_name' ) : get_bloginfo( 'name' ),
				'description' => eoj_setting( 'jpp_name' ),
				'method'      => 'card',
				'prefill'     => array(
					'name'    => $name,
					'email'   => $email,
					'contact' => $phone,
				),
				'theme'       => array( 'color' => '#1E3A8A' ),
			);
			$card_html = '<p class="eoj-muted">' . esc_html__( 'Click below and enter your credit or debit card details in Razorpay’s secure window. Your card details go straight to the bank’s payment network and are never stored on this website.', 'eyesonjobs-core' ) . '</p>';
			$card_html .= '<div data-eoj-pay-msg aria-live="polite"></div>';
			$card_html .= '<p><button type="button" class="eoj-btn eoj-btn--primary eoj-btn--block eoj-btn--glow" data-eoj-pay="' . esc_attr( wp_json_encode( $checkout ) ) . '">' . esc_html( sprintf( /* translators: %s amount */ __( 'Enter card details and pay %s', 'eyesonjobs-core' ), eoj_core_inr( $amount ) ) ) . '</button></p>';
			$card_html .= '<ul class="eoj-paymethods"><li>Visa</li><li>Mastercard</li><li>RuPay</li><li>American Express</li></ul>';

			$verify  = '<form id="eoj-verify" method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" hidden>';
			$verify .= '<input type="hidden" name="action" value="eoj_jpp_verify"><input type="hidden" name="reg" value="' . (int) $id . '"><input type="hidden" name="k" value="' . esc_attr( $key ) . '">';
			$verify .= '<input type="hidden" name="eoj_back" value="' . esc_attr( eoj_jpp_current_base() ) . '">';
			$verify .= '<input type="hidden" name="razorpay_payment_id" value=""><input type="hidden" name="razorpay_order_id" value=""><input type="hidden" name="razorpay_signature" value=""></form>';
		}
	}

	// Method picker.
	$choices = array();
	$choices['card'] = array( __( 'Credit / Debit card', 'eyesonjobs-core' ), __( 'Visa, Mastercard, RuPay, Amex', 'eyesonjobs-core' ) );
	if ( ! $online ) {
		$card_html = '<div class="eoj-alert eoj-alert--info" role="status"><span>' . esc_html__( 'Card payments are not available right now. Please choose another method.', 'eyesonjobs-core' ) . '</span></div>';
		if ( current_user_can( 'manage_options' ) ) {
			$card_html .= '<p class="eoj-muted"><small>' . wp_kses_post( sprintf( /* translators: %s settings link */ __( 'Only you can see this: add your Razorpay keys under %s to turn on card payments.', 'eyesonjobs-core' ), '<a href="' . esc_url( admin_url( 'options-general.php?page=eoj-settings' ) ) . '">Settings → Eyes on Jobs</a>' ) ) . '</small></p>';
		}
	}
	if ( $upi ) {
		$choices['upi'] = array( 'UPI', __( 'PhonePe, Google Pay, Paytm, BHIM', 'eyesonjobs-core' ) );
	}
	if ( $paypal ) {
		$choices['paypal'] = array( 'PayPal', __( 'Best for payers outside India', 'eyesonjobs-core' ) );
	}

	$html .= '<h3 class="eoj-paybox__title">' . esc_html__( 'How would you like to pay?', 'eyesonjobs-core' ) . '</h3>';
	$html .= '<div class="eoj-methods" role="group" aria-label="' . esc_attr__( 'Payment method', 'eyesonjobs-core' ) . '">';
	foreach ( $choices as $slug => $choice ) {
		$html .= '<button type="button" class="eoj-methodbtn" data-eoj-method="' . esc_attr( $slug ) . '" aria-pressed="false"><strong>' . esc_html( $choice[0] ) . '</strong><small>' . esc_html( $choice[1] ) . '</small></button>';
	}
	$html .= '</div>';

	if ( isset( $choices['card'] ) ) {
		$html .= '<div class="eoj-panel" data-eoj-panel="card" hidden>' . $card_html . '</div>';
	}

	if ( isset( $choices['upi'] ) ) {
		$html .= '<div class="eoj-panel" data-eoj-panel="upi" hidden data-eoj-qr-url="' . esc_url( rest_url( 'eoj/v1/upi-qr' ) ) . '" data-eoj-status-url="' . esc_url( rest_url( 'eoj/v1/pay-status' ) ) . '" data-reg="' . (int) $id . '" data-k="' . esc_attr( $key ) . '">';
		$html .= '<p class="eoj-muted">' . esc_html__( 'Scan the QR code with any UPI app. For your security the code is valid for 5 minutes; you can generate a new one at any time.', 'eyesonjobs-core' ) . '</p>';
		$html .= '<div class="eoj-qrbox"><div class="eoj-qr" data-eoj-qr-target aria-live="polite"><span class="eoj-qr__wait">' . esc_html__( 'Generating QR…', 'eyesonjobs-core' ) . '</span></div>';
		$html .= '<div class="eoj-qrinfo"><p class="eoj-qr-timer" data-eoj-timer></p><div class="eoj-qr-bar"><span data-eoj-bar></span></div>';
		$html .= '<p class="eoj-upiapps" data-eoj-apps hidden></p><p data-eoj-upiid hidden></p>';
		$html .= '<button type="button" class="eoj-btn eoj-btn--outline eoj-btn--small" data-eoj-qr-new hidden>' . esc_html__( 'Generate new QR', 'eyesonjobs-core' ) . '</button></div></div>';
		$html .= '<p class="eoj-alert eoj-alert--error" role="alert" data-eoj-qr-msg hidden></p></div>';
	}

	if ( isset( $choices['paypal'] ) ) {
		$html .= '<div class="eoj-panel" data-eoj-panel="paypal" hidden><p class="eoj-muted">' . esc_html__( 'You will pay on PayPal’s website. Come back here afterwards and confirm your payment below.', 'eyesonjobs-core' ) . '</p>';
		$html .= '<p><a class="eoj-btn eoj-btn--primary eoj-btn--block" target="_blank" rel="noopener" href="' . esc_url( trailingslashit( $paypal ) . ( floor( $amount ) == $amount ? (int) $amount : number_format( $amount, 2, '.', '' ) ) . 'INR' ) . '">' . esc_html( sprintf( /* translators: %s amount */ __( 'Pay %s with PayPal', 'eyesonjobs-core' ), eoj_core_inr( $amount ) ) ) . '</a></p></div>';
	}

	// "I have paid" form for payments we cannot detect automatically (PayPal, plain UPI).
	if ( isset( $choices['paypal'] ) || ( $upi && ! $online ) ) {
		$fields = array(
			array( 'name' => 'ref', 'label' => __( 'Transaction ID / UTR / reference number', 'eyesonjobs-core' ), 'type' => 'text', 'required' => true, 'help' => __( 'You will find it in your payment app or PayPal receipt.', 'eyesonjobs-core' ) ),
		);
		$html  .= '<form id="eoj_jpp_confirm" class="eoj-form eoj-confirm" method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" data-recaptcha="' . esc_attr( eoj_recaptcha_action( 'eoj_jpp_confirm' ) ) . '" data-eoj-confirm hidden>';
		$html  .= eoj_form_hidden( 'eoj_jpp_confirm' ) . '<input type="hidden" name="reg" value="' . (int) $id . '"><input type="hidden" name="k" value="' . esc_attr( $key ) . '"><input type="hidden" name="eoj_method" value="">';
		$html  .= '<h4>' . esc_html__( 'Already paid? Confirm your payment', 'eyesonjobs-core' ) . '</h4>' . eoj_render_fields( 'eoj_jpp_confirm', $fields );
		$html  .= '<button type="submit" class="eoj-btn eoj-btn--primary eoj-btn--block">' . esc_html__( 'I have paid, confirm', 'eyesonjobs-core' ) . '</button></form>';
	}

	$html .= '<p class="eoj-price__secure">' . esc_html__( 'Your card and bank login details are never stored on this website.', 'eyesonjobs-core' ) . '</p>';
	$html .= eoj_jpp_disclaimer() . $verify;
	wp_enqueue_script( 'eoj-qr' );
	return $html . '</div>';
}

/**
 * Build the UPI payment link for a registration.
 *
 * @param int    $id  Entry.
 * @param string $tr  Unique transaction reference for this QR.
 * @return string Query string.
 */
function eoj_jpp_upi_query( $id, $tr ) {
	return http_build_query(
		array(
			'pa' => eoj_setting( 'pay_upi_id' ),
			'pn' => eoj_setting( 'pay_upi_name' ),
			'am' => number_format( (float) get_post_meta( $id, '_eoj_amount', true ), 2, '.', '' ),
			'cu' => 'INR',
			'tn' => 'EOJ-JPP-' . $id,
			'tr' => $tr,
		),
		'',
		'&',
		PHP_QUERY_RFC3986
	);
}

add_action(
	'rest_api_init',
	function () {
		register_rest_route(
			'eoj/v1',
			'/upi-qr',
			array(
				'methods'             => 'POST',
				'callback'            => 'eoj_jpp_rest_qr',
				'permission_callback' => '__return_true', // Authorised by the registration access key.
			)
		);
		register_rest_route(
			'eoj/v1',
			'/pay-status',
			array(
				'methods'             => 'GET',
				'callback'            => 'eoj_jpp_rest_status',
				'permission_callback' => '__return_true', // Authorised by the registration access key.
			)
		);
	}
);

/**
 * Create a fresh UPI QR that is valid for five minutes.
 *
 * With Razorpay connected this is a single-use dynamic QR that Razorpay itself
 * closes after five minutes and that confirms the payment automatically.
 * Otherwise it is a UPI link for the business UPI ID with a unique reference.
 *
 * @param WP_REST_Request $request Request.
 */
function eoj_jpp_rest_qr( $request ) {
	$id    = absint( $request->get_param( 'reg' ) );
	$entry = eoj_jpp_find( $id, (string) $request->get_param( 'k' ) );
	if ( ! $entry ) {
		return new WP_REST_Response( array( 'error' => 'not_found' ), 404 );
	}
	if ( 'paid' === get_post_meta( $id, '_eoj_status', true ) ) {
		return new WP_REST_Response( array( 'paid' => true ), 200 );
	}
	$limit_key = 'eoj_qr_' . $id;
	$count     = (int) get_transient( $limit_key );
	if ( $count >= 12 ) {
		return new WP_REST_Response( array( 'error' => 'rate' ), 429 );
	}
	set_transient( $limit_key, $count + 1, HOUR_IN_SECONDS );

	$ttl    = 300;
	$amount = (float) get_post_meta( $id, '_eoj_amount', true );

	if ( eoj_razorpay_enabled() ) {
		$response = wp_remote_post(
			'https://api.razorpay.com/v1/payments/qr_codes',
			array(
				'timeout' => 20,
				'headers' => array(
					'Authorization' => 'Basic ' . base64_encode( eoj_setting( 'razorpay_key_id' ) . ':' . eoj_setting( 'razorpay_key_secret' ) ), // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
					'Content-Type'  => 'application/json',
				),
				'body'    => wp_json_encode(
					array(
						'type'           => 'upi_qr',
						'name'           => 'EOJ-JPP-' . $id,
						'usage'          => 'single_use',
						'fixed_amount'   => true,
						'payment_amount' => (int) round( $amount * 100 ),
						'description'    => eoj_setting( 'jpp_name' ),
						'close_by'       => time() + $ttl + 30,
						'notes'          => array( 'entry_id' => (string) $id ),
					)
				),
			)
		);
		if ( ! is_wp_error( $response ) ) {
			$body = json_decode( wp_remote_retrieve_body( $response ), true );
			if ( 200 === (int) wp_remote_retrieve_response_code( $response ) && ! empty( $body['id'] ) && ! empty( $body['image_url'] ) ) {
				add_post_meta( $id, '_eoj_qr_id', sanitize_text_field( $body['id'] ) );
				return new WP_REST_Response(
					array(
						'mode'       => 'razorpay',
						'image_url'  => esc_url_raw( $body['image_url'] ),
						'expires_in' => $ttl,
					),
					200
				);
			}
		}
		// Fall through to the plain UPI QR when Razorpay QR codes are not available on the account.
	}

	if ( eoj_setting( 'pay_upi_id' ) ) {
		$tr    = 'EOJ' . $id . strtoupper( wp_generate_password( 6, false ) );
		$query = eoj_jpp_upi_query( $id, $tr );
		return new WP_REST_Response(
			array(
				'mode'       => 'upi',
				'uri'        => 'upi://pay?' . $query,
				'apps'       => array(
					'PhonePe'    => 'phonepe://pay?' . $query,
					'Google Pay' => 'tez://upi/pay?' . $query,
					'Paytm'      => 'paytmmp://pay?' . $query,
					'BHIM'       => 'upi://pay?' . $query,
				),
				'upi_id'     => eoj_setting( 'pay_upi_id' ),
				'expires_in' => $ttl,
			),
			200
		);
	}
	return new WP_REST_Response( array( 'error' => 'unavailable' ), 503 );
}

/**
 * Lets the page notice a confirmed payment while the QR is on screen.
 *
 * @param WP_REST_Request $request Request.
 */
function eoj_jpp_rest_status( $request ) {
	$id    = absint( $request->get_param( 'reg' ) );
	$entry = eoj_jpp_find( $id, (string) $request->get_param( 'k' ) );
	if ( ! $entry ) {
		return new WP_REST_Response( array( 'error' => 'not_found' ), 404 );
	}
	return new WP_REST_Response( array( 'paid' => 'paid' === get_post_meta( $id, '_eoj_status', true ) ), 200 );
}

add_filter(
	'eoj_form_handlers',
	function ( $handlers ) {
		$handlers['eoj_jpp_confirm'] = 'eoj_submit_jpp_confirm';
		return $handlers;
	}
);

/**
 * The customer says they paid directly: store the reference and tell the team.
 *
 * @param string $form Form id.
 */
function eoj_submit_jpp_confirm( $form ) {
	// phpcs:disable WordPress.Security.NonceVerification.Missing -- authorised by the entry key; spam checks run below.
	$id  = isset( $_POST['reg'] ) ? absint( $_POST['reg'] ) : 0;
	$key = isset( $_POST['k'] ) ? sanitize_text_field( wp_unslash( $_POST['k'] ) ) : '';
	// phpcs:enable
	$entry = eoj_jpp_find( $id, $key );
	if ( ! $entry ) {
		wp_safe_redirect( home_url( '/' ) );
		exit;
	}
	$check = eoj_check_request( $form );
	if ( true !== $check ) {
		eoj_jpp_finish( $id, false, $check );
	}
	if ( 'paid' === get_post_meta( $id, '_eoj_status', true ) ) {
		eoj_jpp_finish( $id, true, 'paid' );
	}
	$methods = array();
	foreach ( array( 'UPI', 'PayPal' ) as $m ) {
		$methods[ $m ] = $m;
	}
	$fields = array(
		array( 'name' => 'method', 'label' => 'method', 'type' => 'select', 'required' => true, 'options' => $methods ),
		array( 'name' => 'ref', 'label' => 'ref', 'type' => 'text', 'required' => true ),
	);
	$result = eoj_collect_fields( $fields );
	$ref    = isset( $result['values']['ref'] ) ? preg_replace( '/[^A-Za-z0-9\-_\/ ]/', '', $result['values']['ref'] ) : '';
	if ( $result['errors'] || strlen( $ref ) < 6 ) {
		eoj_jpp_finish( $id, false, 'invalid' );
	}
	$method = $result['values']['method'];
	update_post_meta( $id, '_eoj_claimed_at', time() );
	update_post_meta( $id, '_eoj_claimed_ref', $ref );
	update_post_meta( $id, '_eoj_claimed_method', $method );

	$name  = (string) get_post_meta( $id, '_eoj_name', true );
	$email = (string) get_post_meta( $id, '_eoj_email', true );
	eoj_mail(
		eoj_notify_emails(),
		sprintf( /* translators: %s name */ __( 'Payment submitted, please verify: %s', 'eyesonjobs-core' ), $name ),
		sprintf( /* translators: %s link */ __( 'A customer says they paid directly. Check your bank or UPI app for this reference, then click “Mark as paid” on the <a href="%s">submission</a>.', 'eyesonjobs-core' ), esc_url( admin_url( 'post.php?post=' . $id . '&action=edit' ) ) ),
		array(
			__( 'Name', 'eyesonjobs-core' )              => $name,
			__( 'Email', 'eyesonjobs-core' )             => $email,
			__( 'Phone', 'eyesonjobs-core' )             => (string) get_post_meta( $id, '_eoj_phone', true ),
			__( 'Amount', 'eyesonjobs-core' )            => eoj_core_inr( (float) get_post_meta( $id, '_eoj_amount', true ) ),
			__( 'Paid by', 'eyesonjobs-core' )           => $method,
			__( 'Transaction reference', 'eyesonjobs-core' ) => $ref,
			__( 'Our reference', 'eyesonjobs-core' )     => 'EOJ-JPP-' . $id,
		),
		array(),
		$email
	);
	eoj_mail(
		$email,
		__( 'We received your payment details', 'eyesonjobs-core' ),
		'<p>' . esc_html( sprintf( /* translators: %s name */ __( 'Hi %s,', 'eyesonjobs-core' ), $name ) ) . '</p><p>' . esc_html__( 'Thank you! We have your payment reference. Our team will confirm the payment within one working day and email you a receipt.', 'eyesonjobs-core' ) . '</p>'
	);
	eoj_jpp_finish( $id, true, 'claimed' );
}

/**
 * Current page URL without status arguments, kept for the verify redirect.
 */
function eoj_jpp_current_base() {
	return remove_query_arg( array( 'eoj_form', 'eoj_status', 'eoj_msg', 'eoj_t', 'reg', 'k' ), eoj_current_url() );
}

/**
 * Redirect to the payment step with a message.
 *
 * @param int    $id   Entry.
 * @param bool   $ok   Success.
 * @param string $code Message code.
 */
function eoj_jpp_finish( $id, $ok, $code ) {
	$args = array(
		'reg'        => $id,
		'k'          => (string) get_post_meta( $id, '_eoj_key', true ),
		'eoj_form'   => 'eoj_jpp',
		'eoj_status' => $ok ? 'ok' : 'err',
		'eoj_msg'    => $code,
	);
	wp_safe_redirect( add_query_arg( $args, eoj_jpp_base_url() ) . '#pay' );
	exit;
}

/**
 * Browser callback after Razorpay Checkout: verify the signature.
 */
function eoj_jpp_verify() {
	// phpcs:disable WordPress.Security.NonceVerification.Missing -- authorised by the entry key and the Razorpay signature.
	$id         = isset( $_POST['reg'] ) ? absint( $_POST['reg'] ) : 0;
	$key        = isset( $_POST['k'] ) ? sanitize_text_field( wp_unslash( $_POST['k'] ) ) : '';
	$payment_id = isset( $_POST['razorpay_payment_id'] ) ? sanitize_text_field( wp_unslash( $_POST['razorpay_payment_id'] ) ) : '';
	$order_id   = isset( $_POST['razorpay_order_id'] ) ? sanitize_text_field( wp_unslash( $_POST['razorpay_order_id'] ) ) : '';
	$signature  = isset( $_POST['razorpay_signature'] ) ? sanitize_text_field( wp_unslash( $_POST['razorpay_signature'] ) ) : '';
	// phpcs:enable

	$entry = eoj_jpp_find( $id, $key );
	if ( ! $entry ) {
		wp_safe_redirect( home_url( '/' ) );
		exit;
	}
	$stored_order = (string) get_post_meta( $id, '_eoj_order_id', true );
	$expected     = hash_hmac( 'sha256', $order_id . '|' . $payment_id, eoj_setting( 'razorpay_key_secret' ) );
	if ( ! $payment_id || ! $stored_order || ! hash_equals( $stored_order, $order_id ) || ! hash_equals( $expected, $signature ) ) {
		eoj_jpp_finish( $id, false, 'pay_failed' );
	}
	eoj_jpp_mark_paid( $id, $payment_id );
	eoj_jpp_finish( $id, true, 'paid' );
}
add_action( 'admin_post_nopriv_eoj_jpp_verify', 'eoj_jpp_verify' );
add_action( 'admin_post_eoj_jpp_verify', 'eoj_jpp_verify' );

/**
 * Mark a registration as paid. Safe to call more than once.
 *
 * @param int    $id         Entry.
 * @param string $payment_id Razorpay payment id.
 * @return bool True when this call made the change.
 */
function eoj_jpp_mark_paid( $id, $payment_id = '' ) {
	if ( 'paid' === get_post_meta( $id, '_eoj_status', true ) ) {
		return false;
	}
	// add_post_meta() with $unique is our lock: only one request wins.
	if ( ! add_post_meta( $id, '_eoj_paid_lock', time(), true ) ) {
		return false;
	}
	update_post_meta( $id, '_eoj_status', 'paid' );
	update_post_meta( $id, '_eoj_payment_id', $payment_id );
	update_post_meta( $id, '_eoj_paid_at', time() );

	$name   = (string) get_post_meta( $id, '_eoj_name', true );
	$email  = (string) get_post_meta( $id, '_eoj_email', true );
	$phone  = (string) get_post_meta( $id, '_eoj_phone', true );
	$amount = eoj_core_inr( (float) get_post_meta( $id, '_eoj_amount', true ) );
	$rows   = array(
		__( 'Name', 'eyesonjobs-core' )       => $name,
		__( 'Email', 'eyesonjobs-core' )      => $email,
		__( 'Phone', 'eyesonjobs-core' )      => $phone,
		__( 'Amount paid', 'eyesonjobs-core' ) => $amount . ' ' . __( '(inclusive of all taxes)', 'eyesonjobs-core' ),
		__( 'Payment ID', 'eyesonjobs-core' ) => $payment_id,
		__( 'Order ID', 'eyesonjobs-core' )   => (string) get_post_meta( $id, '_eoj_order_id', true ),
		__( 'Reference', 'eyesonjobs-core' )  => 'EOJ-JPP-' . $id,
	);
	eoj_mail(
		eoj_notify_emails(),
		sprintf( /* translators: %s name */ __( 'PAID: Job Preparation Package · %s', 'eyesonjobs-core' ), $name ),
		sprintf( /* translators: %s link */ __( 'Payment received. <a href="%s">Open the submission</a>.', 'eyesonjobs-core' ), esc_url( admin_url( 'post.php?post=' . $id . '&action=edit' ) ) ),
		$rows,
		array(),
		$email
	);
	if ( $email ) {
		eoj_mail(
			$email,
			__( 'Payment receipt: Complete Job Preparation Package', 'eyesonjobs-core' ),
			'<p>' . esc_html( sprintf( /* translators: %s name */ __( 'Hi %s,', 'eyesonjobs-core' ), $name ) ) . '</p><p>' . esc_html__( 'Thank you! We have received your payment. Your consultant will contact you within one working day to schedule your sessions.', 'eyesonjobs-core' ) . '</p><p style="font-size:13px;color:#64748b">' . esc_html__( 'This package covers career preparation only. It does not guarantee placement, interview calls, selection, an offer letter or employment.', 'eyesonjobs-core' ) . '</p>',
			array(
				__( 'Package', 'eyesonjobs-core' )     => eoj_setting( 'jpp_name' ),
				__( 'Amount paid', 'eyesonjobs-core' ) => $amount . ' ' . __( '(inclusive of all taxes)', 'eyesonjobs-core' ),
				__( 'Payment ID', 'eyesonjobs-core' )  => $payment_id,
				__( 'Receipt no.', 'eyesonjobs-core' ) => 'EOJ-JPP-' . $id,
				__( 'Date', 'eyesonjobs-core' )        => wp_date( 'j M Y, g:i a' ),
			)
		);
	}
	return true;
}

/**
 * Razorpay webhook: confirms payments even if the browser was closed.
 */
add_action(
	'rest_api_init',
	function () {
		register_rest_route(
			'eoj/v1',
			'/razorpay-webhook',
			array(
				'methods'             => 'POST',
				'callback'            => 'eoj_jpp_webhook',
				'permission_callback' => '__return_true', // Authenticated by the signature below.
			)
		);
	}
);

/**
 * Handle a webhook call.
 *
 * @param WP_REST_Request $request Request.
 */
function eoj_jpp_webhook( $request ) {
	$secret = eoj_setting( 'razorpay_webhook_secret' );
	if ( ! $secret ) {
		return new WP_REST_Response( array( 'error' => 'Webhook secret not configured.' ), 503 );
	}
	$body      = $request->get_body();
	$signature = (string) $request->get_header( 'x_razorpay_signature' );
	if ( ! $signature || ! hash_equals( hash_hmac( 'sha256', $body, $secret ), $signature ) ) {
		return new WP_REST_Response( array( 'error' => 'Bad signature.' ), 400 );
	}
	$data  = json_decode( $body, true );
	$event = isset( $data['event'] ) ? $data['event'] : '';
	if ( ! in_array( $event, array( 'payment.captured', 'order.paid', 'qr_code.credited' ), true ) ) {
		return new WP_REST_Response( array( 'ignored' => true ), 200 );
	}
	$payment  = isset( $data['payload']['payment']['entity'] ) ? $data['payload']['payment']['entity'] : array();
	$order_id = '';
	if ( 'order.paid' === $event && isset( $data['payload']['order']['entity']['id'] ) ) {
		$order_id = $data['payload']['order']['entity']['id'];
	} elseif ( isset( $payment['order_id'] ) ) {
		$order_id = $payment['order_id'];
	}
	$order_id = sanitize_text_field( (string) $order_id );
	$qr_id    = '';
	if ( isset( $data['payload']['qr_code']['entity']['id'] ) ) {
		$qr_id = sanitize_text_field( (string) $data['payload']['qr_code']['entity']['id'] );
	} elseif ( isset( $payment['qr_code_id'] ) ) {
		$qr_id = sanitize_text_field( (string) $payment['qr_code_id'] );
	}
	if ( ! $order_id && ! $qr_id ) {
		return new WP_REST_Response( array( 'ignored' => true ), 200 );
	}
	$found = array();
	if ( $order_id ) {
		$found = get_posts(
			array(
				'post_type'      => 'eoj_entry',
				'post_status'    => 'any',
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'meta_key'       => '_eoj_order_id', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'meta_value'     => $order_id, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			)
		);
	}
	if ( ! $found && $qr_id ) {
		$found = get_posts(
			array(
				'post_type'      => 'eoj_entry',
				'post_status'    => 'any',
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'meta_key'       => '_eoj_qr_id', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'meta_value'     => $qr_id, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			)
		);
		// A QR payment must match the amount due.
		if ( $found && isset( $payment['amount'] ) && (int) $payment['amount'] !== (int) round( (float) get_post_meta( $found[0], '_eoj_amount', true ) * 100 ) ) {
			return new WP_REST_Response( array( 'ignored' => true ), 200 );
		}
	}
	if ( ! $found ) {
		return new WP_REST_Response( array( 'ignored' => true ), 200 );
	}
	eoj_jpp_mark_paid( (int) $found[0], isset( $payment['id'] ) ? sanitize_text_field( $payment['id'] ) : '' );
	return new WP_REST_Response( array( 'ok' => true ), 200 );
}

/**
 * Mark an entry as paid by hand (money received offline).
 */
add_action(
	'admin_post_eoj_mark_paid',
	function () {
		$id = isset( $_GET['entry'] ) ? absint( $_GET['entry'] ) : 0;
		check_admin_referer( 'eoj_mark_paid_' . $id );
		if ( ! $id || ! current_user_can( 'edit_post', $id ) || 'eoj_entry' !== get_post_type( $id ) ) {
			wp_die( esc_html__( 'Not allowed.', 'eyesonjobs-core' ), '', array( 'response' => 403 ) );
		}
		$claimed = (string) get_post_meta( $id, '_eoj_claimed_ref', true );
		eoj_jpp_mark_paid( $id, $claimed ? sprintf( /* translators: %s reference */ __( 'Direct payment, ref %s (confirmed by staff)', 'eyesonjobs-core' ), $claimed ) : __( 'Offline payment (marked by staff)', 'eyesonjobs-core' ) );
		wp_safe_redirect( get_edit_post_link( $id, 'raw' ) );
		exit;
	}
);

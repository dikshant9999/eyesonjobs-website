<?php
/**
 * Shared helpers: spam checks, reCAPTCHA, uploads, email, redirects.
 *
 * @package EyesOnJobsCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Notification recipients.
 *
 * @return string[]
 */
function eoj_notify_emails() {
	$emails = array_filter( array_map( 'trim', explode( ',', eoj_setting( 'notify_emails' ) ) ), 'is_email' );
	return $emails ? $emails : array( get_option( 'admin_email' ) );
}

/**
 * Visitor IP (best effort, for rate limiting only).
 */
function eoj_ip() {
	return isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '0.0.0.0';
}

/**
 * Hidden fields every public form carries.
 *
 * @param string $form Form id.
 */
function eoj_form_hidden( $form ) {
	$out  = '<input type="hidden" name="action" value="eoj_submit">';
	$out .= '<input type="hidden" name="eoj_form" value="' . esc_attr( $form ) . '">';
	$out .= '<input type="hidden" name="eoj_ts" value="' . esc_attr( time() ) . '">';
	$out .= '<input type="hidden" name="eoj_recaptcha" value="">';
	$out .= '<input type="hidden" name="eoj_back" value="' . esc_attr( eoj_current_url() ) . '">';
	$out .= '<div class="eoj-hp" aria-hidden="true"><label>Leave this empty <input type="text" name="eoj_website" tabindex="-1" autocomplete="off"></label></div>';
	if ( is_user_logged_in() ) {
		$out .= wp_nonce_field( 'eoj_' . $form, '_eojnonce', false, false );
	}
	return $out;
}

/**
 * Current URL without our status query args.
 */
function eoj_current_url() {
	$uri = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '/';
	return remove_query_arg( array( 'eoj_status', 'eoj_msg', 'eoj_t' ), home_url( $uri ) );
}

/**
 * Spam and security checks shared by all forms.
 *
 * @param string $form Form id (also the reCAPTCHA action).
 * @return true|string True or an error code.
 */
function eoj_check_request( $form ) {
	// phpcs:disable WordPress.Security.NonceVerification.Missing -- nonce checked below for logged-in users; public forms rely on reCAPTCHA + honeypot so cached pages keep working.
	if ( ! empty( $_POST['eoj_website'] ) ) {
		return 'spam';
	}
	$ts = isset( $_POST['eoj_ts'] ) ? absint( $_POST['eoj_ts'] ) : 0;
	$login = ( 'eoj_login' === $form );
	if ( ! $ts || ( ! $login && time() - $ts < 2 ) ) {
		return 'spam';
	}
	if ( is_user_logged_in() ) {
		$nonce = isset( $_POST['_eojnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_eojnonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'eoj_' . $form ) ) {
			return 'expired';
		}
	}

	$key   = 'eoj_rl_' . md5( eoj_ip() . $form );
	$count = (int) get_transient( $key );
	if ( $count >= ( $login ? 30 : 15 ) ) {
		return 'rate';
	}
	set_transient( $key, $count + 1, 10 * MINUTE_IN_SECONDS );

	if ( eoj_recaptcha_enabled() ) {
		$token = isset( $_POST['eoj_recaptcha'] ) ? sanitize_text_field( wp_unslash( $_POST['eoj_recaptcha'] ) ) : '';
		if ( ! eoj_verify_recaptcha( $token, $form ) ) {
			return 'captcha';
		}
	}
	// phpcs:enable
	return true;
}

/**
 * Verify a reCAPTCHA v3 token with Google.
 *
 * @param string $token  Token.
 * @param string $action Expected action.
 */
function eoj_verify_recaptcha( $token, $action ) {
	if ( ! $token ) {
		return false;
	}
	$response = wp_remote_post(
		'https://www.google.com/recaptcha/api/siteverify',
		array(
			'timeout' => 10,
			'body'    => array(
				'secret'   => eoj_setting( 'recaptcha_secret' ),
				'response' => $token,
				'remoteip' => eoj_ip(),
			),
		)
	);
	if ( is_wp_error( $response ) ) {
		// Do not lose genuine leads if Google cannot be reached.
		return true;
	}
	$body = json_decode( wp_remote_retrieve_body( $response ), true );
	if ( empty( $body['success'] ) ) {
		return false;
	}
	if ( isset( $body['action'] ) && eoj_recaptcha_action( $action ) !== $body['action'] ) {
		return false;
	}
	return isset( $body['score'] ) ? (float) $body['score'] >= (float) eoj_setting( 'recaptcha_threshold' ) : true;
}

/**
 * reCAPTCHA action names allow only letters, slashes and underscores.
 *
 * @param string $form Form id.
 */
function eoj_recaptcha_action( $form ) {
	return preg_replace( '/[^A-Za-z_\/]/', '_', $form );
}

/**
 * Human-readable messages for status codes.
 *
 * @param string $code Code.
 */
function eoj_message( $code ) {
	$messages = array(
		'sent'           => __( 'Thank you! Your message has been sent. Our team will get back to you within one working day.', 'eyesonjobs-core' ),
		'resume'         => __( 'Thank you! Your resume has been received. A recruiter will contact you when a suitable opening comes up.', 'eyesonjobs-core' ),
		'applied'        => __( 'Application sent! The recruiter for this role will review your profile and contact you if it is a match.', 'eyesonjobs-core' ),
		'requirement'    => __( 'Thank you! Your requirement has been received. A consultant will call you within one working day.', 'eyesonjobs-core' ),
		'spam'           => __( 'Your submission looked automated. Please wait a few seconds and try again.', 'eyesonjobs-core' ),
		'expired'        => __( 'Your session expired. Please refresh the page and try again.', 'eyesonjobs-core' ),
		'rate'           => __( 'Too many attempts. Please wait ten minutes and try again.', 'eyesonjobs-core' ),
		'captcha'        => __( 'We could not confirm you are human. Please refresh the page and try again.', 'eyesonjobs-core' ),
		'invalid'        => __( 'Please check the highlighted fields and try again.', 'eyesonjobs-core' ),
		'upload'         => __( 'Please upload a PDF, DOC or DOCX file under 5 MB.', 'eyesonjobs-core' ),
		'login_failed'   => __( 'The email or password is incorrect.', 'eyesonjobs-core' ),
		'email_exists'   => __( 'An account with this email already exists. Please log in instead.', 'eyesonjobs-core' ),
		'weak_password'  => __( 'Please choose a password of at least 8 characters.', 'eyesonjobs-core' ),
		'mismatch'       => __( 'The two passwords do not match.', 'eyesonjobs-core' ),
		'closed'         => __( 'New sign-ups are paused right now. Please contact us.', 'eyesonjobs-core' ),
		'profile'        => __( 'Your profile has been updated.', 'eyesonjobs-core' ),
		'welcome'        => __( 'Welcome aboard! Your account is ready.', 'eyesonjobs-core' ),
		'paid'           => __( 'Payment successful! A confirmation has been emailed to you.', 'eyesonjobs-core' ),
		'claimed'         => __( 'Thank you! We have your payment reference. Our team will confirm it within one working day and email you a receipt.', 'eyesonjobs-core' ),
		'pay_failed'     => __( 'We could not verify your payment. If money was deducted, please contact us with your payment ID and we will sort it out.', 'eyesonjobs-core' ),
		'error'          => __( 'Something went wrong. Please try again, or contact us by phone.', 'eyesonjobs-core' ),
	);
	return isset( $messages[ $code ] ) ? $messages[ $code ] : $messages['error'];
}

/**
 * Status alert from the URL, shown at the top of a form.
 *
 * @param string $form Only show for this form.
 */
function eoj_form_notice( $form ) {
	// phpcs:disable WordPress.Security.NonceVerification.Recommended
	if ( empty( $_GET['eoj_msg'] ) || empty( $_GET['eoj_form'] ) || $form !== $_GET['eoj_form'] ) {
		return '';
	}
	$status = isset( $_GET['eoj_status'] ) && 'ok' === $_GET['eoj_status'] ? 'success' : 'error';
	$code   = sanitize_key( wp_unslash( $_GET['eoj_msg'] ) );
	// phpcs:enable
	return '<div class="eoj-alert eoj-alert--' . esc_attr( $status ) . '" role="' . ( 'success' === $status ? 'status' : 'alert' ) . '" tabindex="-1" data-eoj-focus>' . esc_html( eoj_message( $code ) ) . '</div>';
}

/**
 * Redirect back to the form with a status. Keeps typed values on error.
 *
 * @param string $form   Form id.
 * @param bool   $ok     Success.
 * @param string $code   Message code.
 * @param array  $values Values to refill on error.
 * @param array  $extra  Extra query args.
 */
function eoj_redirect_back( $form, $ok, $code, $values = array(), $extra = array() ) {
	// phpcs:ignore WordPress.Security.NonceVerification.Missing
	$back = isset( $_POST['eoj_back'] ) ? esc_url_raw( wp_unslash( $_POST['eoj_back'] ) ) : home_url( '/' );
	$back = wp_validate_redirect( $back, home_url( '/' ) );
	$args = array_merge(
		array(
			'eoj_form'   => $form,
			'eoj_status' => $ok ? 'ok' : 'err',
			'eoj_msg'    => $code,
		),
		$extra
	);
	if ( ! $ok && $values ) {
		$token = wp_generate_password( 12, false );
		set_transient( 'eoj_old_' . $token, $values, 10 * MINUTE_IN_SECONDS );
		$args['eoj_t'] = $token;
	}
	wp_safe_redirect( add_query_arg( $args, $back ) . '#' . $form );
	exit;
}

/**
 * Previously typed value after a failed submission.
 *
 * @param string $name Field.
 * @return string
 */
function eoj_old( $name ) {
	static $old = null;
	if ( null === $old ) {
		$old = array();
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$token = isset( $_GET['eoj_t'] ) ? sanitize_key( wp_unslash( $_GET['eoj_t'] ) ) : '';
		if ( $token ) {
			$old = (array) get_transient( 'eoj_old_' . $token );
		}
	}
	return isset( $old[ $name ] ) ? (string) $old[ $name ] : '';
}

/**
 * Max upload size (5 MB).
 */
function eoj_max_upload_bytes() {
	return min( 5 * MB_IN_BYTES, wp_max_upload_size() );
}

/**
 * Private folder for resumes, blocked from direct web access.
 *
 * @return string Path.
 */
function eoj_private_upload_dir() {
	$uploads = wp_upload_dir( null, false );
	$dir     = trailingslashit( $uploads['basedir'] ) . 'eoj-private';
	if ( ! is_dir( $dir ) ) {
		wp_mkdir_p( $dir );
	}
	if ( ! file_exists( $dir . '/.htaccess' ) ) {
		file_put_contents( $dir . '/.htaccess', "Require all denied\nDeny from all\n" ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
		file_put_contents( $dir . '/index.php', "<?php // Silence is golden.\n" ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
	}
	return $dir;
}

/**
 * Store an uploaded resume privately.
 *
 * @param string $field    $_FILES key.
 * @param bool   $required Required.
 * @return string|false|null Path, false on error, null when no file.
 */
function eoj_handle_upload( $field, $required = false ) {
	// phpcs:disable WordPress.Security.NonceVerification.Missing
	if ( empty( $_FILES[ $field ] ) || empty( $_FILES[ $field ]['name'] ) || UPLOAD_ERR_NO_FILE === (int) $_FILES[ $field ]['error'] ) {
		return $required ? false : null;
	}
	$file = $_FILES[ $field ]; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	// phpcs:enable
	if ( UPLOAD_ERR_OK !== (int) $file['error'] || (int) $file['size'] > eoj_max_upload_bytes() || ! is_uploaded_file( $file['tmp_name'] ) ) {
		return false;
	}
	$allowed = array(
		'pdf'  => 'application/pdf',
		'doc'  => 'application/msword',
		'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
	);
	$check   = wp_check_filetype_and_ext( $file['tmp_name'], $file['name'], $allowed );
	$ext     = strtolower( pathinfo( $file['name'], PATHINFO_EXTENSION ) );
	if ( ! isset( $allowed[ $ext ] ) || ( $check['ext'] && ! isset( $allowed[ $check['ext'] ] ) ) ) {
		return false;
	}
	$dir  = eoj_private_upload_dir() . '/' . gmdate( 'Y/m' );
	wp_mkdir_p( $dir );
	$base = sanitize_file_name( pathinfo( $file['name'], PATHINFO_FILENAME ) );
	$dest = $dir . '/' . wp_generate_password( 10, false ) . '-' . ( $base ? $base : 'resume' ) . '.' . $ext;
	if ( ! move_uploaded_file( $file['tmp_name'], $dest ) ) {
		return false;
	}
	return $dest;
}

/**
 * Send a branded HTML email.
 *
 * @param string|array $to          Recipients.
 * @param string       $subject     Subject.
 * @param string       $intro       Intro paragraph (HTML allowed).
 * @param array        $rows        Label => value table.
 * @param array        $attachments Files.
 * @param string       $reply_to    Reply-to address.
 */
function eoj_mail( $to, $subject, $intro, $rows = array(), $attachments = array(), $reply_to = '' ) {
	$table = '';
	foreach ( $rows as $label => $value ) {
		if ( '' === (string) $value ) {
			continue;
		}
		$table .= '<tr><td style="padding:10px 14px;border-bottom:1px solid #e2e8f0;color:#64748b;font-size:14px;width:38%;vertical-align:top">' . esc_html( $label ) . '</td><td style="padding:10px 14px;border-bottom:1px solid #e2e8f0;color:#0b1f4d;font-size:15px;font-weight:600">' . nl2br( esc_html( $value ) ) . '</td></tr>';
	}
	$body  = '<div style="background:#f4f7fd;padding:30px 12px;font-family:Segoe UI,Arial,sans-serif">';
	$body .= '<div style="max-width:620px;margin:0 auto;background:#fff;border-radius:16px;overflow:hidden;border:1px solid #e2e8f0">';
	$body .= '<div style="background:#0b1f4d;padding:22px 28px"><span style="font-size:24px;font-weight:800;color:#fff">eyes<span style="color:#ff5a4e">On</span>Jobs</span></div>';
	$body .= '<div style="padding:28px"><h2 style="margin:0 0 12px;color:#0b1f4d;font-size:20px">' . esc_html( $subject ) . '</h2>';
	$body .= '<div style="color:#334155;font-size:15px;line-height:1.6">' . wp_kses_post( $intro ) . '</div>';
	if ( $table ) {
		$body .= '<table style="width:100%;border-collapse:collapse;margin-top:18px">' . $table . '</table>';
	}
	$body .= '</div><div style="padding:16px 28px;background:#f8fafc;color:#94a3b8;font-size:12px">' . esc_html( get_bloginfo( 'name' ) ) . ' · ' . esc_html( home_url() ) . '</div></div></div>';

	$headers = array( 'Content-Type: text/html; charset=UTF-8' );
	if ( $reply_to && is_email( $reply_to ) ) {
		$headers[] = 'Reply-To: ' . $reply_to;
	}
	$from_name = function () {
		return eoj_setting( 'from_name' ) ? eoj_setting( 'from_name' ) : get_bloginfo( 'name' );
	};
	add_filter( 'wp_mail_from_name', $from_name );
	$sent = wp_mail( $to, $subject, $body, $headers, array_filter( (array) $attachments ) );
	remove_filter( 'wp_mail_from_name', $from_name );
	return $sent;
}

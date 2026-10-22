<?php
/**
 * Candidate and employer accounts: roles, [eoj_account], login, sign-up,
 * profile, and keeping these users out of wp-admin.
 *
 * @package EyesOnJobsCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register the two front-end roles. They can only read, so they have no
 * access to the dashboard.
 */
function eoj_register_roles() {
	add_role( 'eoj_candidate', __( 'Candidate', 'eyesonjobs-core' ), array( 'read' => true ) );
	add_role( 'eoj_employer', __( 'Employer', 'eyesonjobs-core' ), array( 'read' => true ) );
}

/**
 * Make sure the roles exist even if the plugin was copied in without activation.
 */
add_action(
	'init',
	function () {
		if ( ! get_role( 'eoj_candidate' ) || ! get_role( 'eoj_employer' ) ) {
			eoj_register_roles();
		}
	}
);

/**
 * Whether a user is only a candidate or employer (no staff role).
 *
 * @param WP_User|null $user User; current user by default.
 */
function eoj_is_member( $user = null ) {
	$user = $user ? $user : wp_get_current_user();
	if ( ! $user || ! $user->exists() || ! $user->roles ) {
		return false;
	}
	return ! array_diff( $user->roles, array( 'eoj_candidate', 'eoj_employer' ) );
}

add_filter(
	'show_admin_bar',
	function ( $show ) {
		return is_user_logged_in() && eoj_is_member() ? false : $show;
	}
);

add_action(
	'admin_init',
	function () {
		global $pagenow;
		if ( ! is_user_logged_in() || ! eoj_is_member() || wp_doing_ajax() || 'admin-post.php' === $pagenow ) {
			return;
		}
		wp_safe_redirect( eoj_core_account_url() );
		exit;
	}
);

add_filter(
	'login_redirect',
	function ( $redirect_to, $requested, $user ) {
		if ( $user instanceof WP_User && eoj_is_member( $user ) ) {
			return eoj_core_account_url();
		}
		return $redirect_to;
	},
	10,
	3
);

/**
 * Form field definitions.
 */
function eoj_login_fields() {
	return array(
		array( 'name' => 'login', 'label' => __( 'Email or username', 'eyesonjobs-core' ), 'type' => 'text', 'required' => true, 'autocomplete' => 'username' ),
		array( 'name' => 'password', 'label' => __( 'Password', 'eyesonjobs-core' ), 'type' => 'password', 'required' => true, 'autocomplete' => 'current-password' ),
		array( 'name' => 'remember', 'label' => __( 'Keep me logged in', 'eyesonjobs-core' ), 'type' => 'checkbox' ),
	);
}

function eoj_register_fields() {
	/* translators: %s terms link */
	$terms = sprintf( __( 'I agree to the %1$s and the %2$s.', 'eyesonjobs-core' ), '<a href="' . esc_url( eoj_page_url( 'terms-and-conditions' ) ) . '" target="_blank" rel="noopener">' . esc_html__( 'Terms & Conditions', 'eyesonjobs-core' ) . '</a>', '<a href="' . esc_url( eoj_page_url( 'privacy-policy' ) ) . '" target="_blank" rel="noopener">' . esc_html__( 'Privacy Policy', 'eyesonjobs-core' ) . '</a>' );
	return array(
		array( 'name' => 'name', 'label' => __( 'Full name', 'eyesonjobs-core' ), 'type' => 'text', 'required' => true, 'autocomplete' => 'name' ),
		array( 'name' => 'email', 'label' => __( 'Email', 'eyesonjobs-core' ), 'type' => 'email', 'required' => true, 'half' => true, 'autocomplete' => 'email' ),
		array( 'name' => 'phone', 'label' => __( 'Mobile number', 'eyesonjobs-core' ), 'type' => 'tel', 'required' => true, 'half' => true, 'autocomplete' => 'tel' ),
		array( 'name' => 'password', 'label' => __( 'Password', 'eyesonjobs-core' ), 'type' => 'password', 'required' => true, 'half' => true, 'autocomplete' => 'new-password', 'help' => __( 'At least 8 characters.', 'eyesonjobs-core' ) ),
		array( 'name' => 'password2', 'label' => __( 'Confirm password', 'eyesonjobs-core' ), 'type' => 'password', 'required' => true, 'half' => true, 'autocomplete' => 'new-password' ),
		array(
			'name'     => 'role',
			'label'    => __( 'I want to', 'eyesonjobs-core' ),
			'type'     => 'radio',
			'required' => true,
			'options'  => array(
				'candidate' => __( 'Find a job', 'eyesonjobs-core' ),
				'employer'  => __( 'Hire talent', 'eyesonjobs-core' ),
			),
		),
		array( 'name' => 'company', 'label' => __( 'Company name', 'eyesonjobs-core' ), 'type' => 'text', 'required_if' => array( 'role', 'employer' ), 'show_for' => 'employer', 'autocomplete' => 'organization' ),
		array( 'name' => 'consent', 'label' => $terms, 'type' => 'checkbox', 'required' => true ),
	);
}

function eoj_profile_fields( $employer = false ) {
	$fields = array(
		array( 'name' => 'name', 'label' => __( 'Full name', 'eyesonjobs-core' ), 'type' => 'text', 'required' => true, 'half' => true, 'autocomplete' => 'name' ),
		array( 'name' => 'phone', 'label' => __( 'Mobile number', 'eyesonjobs-core' ), 'type' => 'tel', 'half' => true, 'autocomplete' => 'tel' ),
	);
	if ( $employer ) {
		$fields[] = array( 'name' => 'company', 'label' => __( 'Company name', 'eyesonjobs-core' ), 'type' => 'text', 'autocomplete' => 'organization' );
	}
	$fields[] = array( 'name' => 'new_password', 'label' => __( 'New password (optional)', 'eyesonjobs-core' ), 'type' => 'password', 'autocomplete' => 'new-password', 'help' => __( 'Leave empty to keep your current password.', 'eyesonjobs-core' ) );
	return $fields;
}

/**
 * Role label shown in the sidebar.
 *
 * @param WP_User $user User.
 */
function eoj_role_label( $user ) {
	$names = wp_roles()->get_names();
	$role  = $user->roles ? reset( $user->roles ) : '';
	return isset( $names[ $role ] ) ? translate_user_role( $names[ $role ] ) : '';
}

/**
 * [eoj_account]
 */
add_shortcode(
	'eoj_account',
	function () {
		eoj_enqueue_form_assets();
		return is_user_logged_in() ? eoj_account_dashboard() : eoj_account_auth();
	}
);

/**
 * Wrap a form for the account pages.
 *
 * @param string $form   Form id.
 * @param array  $fields Fields.
 * @param string $submit Button label.
 * @param string $extra  Markup placed after the fields.
 */
function eoj_account_form( $form, $fields, $submit, $extra = '' ) {
	$html  = '<form id="' . esc_attr( $form ) . '" class="eoj-form" method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" data-recaptcha="' . esc_attr( eoj_recaptcha_action( $form ) ) . '">';
	$html .= eoj_form_hidden( $form ) . eoj_form_notice( $form );
	$html .= eoj_render_fields( $form, $fields, eoj_user_defaults() );
	$html .= $extra;
	$html .= '<button type="submit" class="eoj-btn eoj-btn--primary eoj-btn--block">' . esc_html( $submit ) . '</button>';
	$html .= eoj_recaptcha_note() . '</form>';
	return $html;
}

/**
 * Logged-out view: Log in / Create account tabs.
 */
function eoj_account_auth() {
	// phpcs:disable WordPress.Security.NonceVerification.Recommended
	$open_register = ( isset( $_GET['tab'] ) && 'register' === $_GET['tab'] ) || ( isset( $_GET['eoj_form'] ) && 'eoj_register' === $_GET['eoj_form'] );
	// phpcs:enable
	$can_register  = (bool) eoj_setting( 'allow_registration' );
	$open_register = $open_register && $can_register;

	$login_extra = '<p class="eoj-muted"><a href="' . esc_url( wp_lostpassword_url( eoj_core_account_url() ) ) . '">' . esc_html__( 'Forgot password?', 'eyesonjobs-core' ) . '</a></p>';

	$html  = '<div class="eoj-auth eoj-card eoj-formcard" data-eoj-tabs>';
	$html .= '<noscript><style>.eoj-tabpanel[hidden]{display:block}.eoj-tabpanel+.eoj-tabpanel{margin-top:32px}</style></noscript>';
	if ( $can_register ) {
		$html .= '<div class="eoj-tabs" role="tablist" aria-label="' . esc_attr__( 'Account', 'eyesonjobs-core' ) . '">';
		$html .= '<button type="button" role="tab" id="eoj-tab-login" aria-controls="eoj-panel-login" aria-selected="' . ( $open_register ? 'false' : 'true' ) . '" data-eoj-tab="login">' . esc_html__( 'Log in', 'eyesonjobs-core' ) . '</button>';
		$html .= '<button type="button" role="tab" id="eoj-tab-register" aria-controls="eoj-panel-register" aria-selected="' . ( $open_register ? 'true' : 'false' ) . '" data-eoj-tab="register">' . esc_html__( 'Create account', 'eyesonjobs-core' ) . '</button>';
		$html .= '</div>';
	}
	$html .= '<div class="eoj-tabpanel" role="tabpanel" id="eoj-panel-login" aria-labelledby="eoj-tab-login"' . ( $open_register ? ' hidden' : '' ) . '>';
	$html .= eoj_account_form( 'eoj_login', eoj_login_fields(), __( 'Log in', 'eyesonjobs-core' ), $login_extra );
	$html .= '</div>';
	if ( $can_register ) {
		$html .= '<div class="eoj-tabpanel" role="tabpanel" id="eoj-panel-register" aria-labelledby="eoj-tab-register"' . ( $open_register ? '' : ' hidden' ) . '>';
		$html .= eoj_account_form( 'eoj_register', eoj_register_fields(), __( 'Create my account', 'eyesonjobs-core' ) );
		$html .= '</div>';
	}
	return $html . '</div>';
}

/**
 * The visitor's Job Prep Package registrations.
 *
 * @param WP_User $user User.
 * @return WP_Post[]
 */
function eoj_user_registrations( $user ) {
	return get_posts(
		array(
			'post_type'      => 'eoj_entry',
			'post_status'    => 'any',
			'posts_per_page' => 20,
			'meta_query'     => array(
				'relation' => 'AND',
				array(
					'key'   => '_eoj_type',
					'value' => 'jpp',
				),
				array(
					'relation' => 'OR',
					array(
						'key'   => '_eoj_user',
						'value' => $user->ID,
					),
					array(
						'key'   => '_eoj_email',
						'value' => $user->user_email,
					),
				),
			),
		)
	);
}

/**
 * Logged-in view.
 */
function eoj_account_dashboard() {
	$user     = wp_get_current_user();
	$employer = in_array( 'eoj_employer', $user->roles, true );
	$jobs     = get_post_type_archive_link( 'eoj_job' );
	$initial  = strtoupper( mb_substr( $user->display_name ? $user->display_name : $user->user_login, 0, 1 ) );

	if ( $employer ) {
		$actions = array(
			array( __( 'Share a requirement', 'eyesonjobs-core' ), __( 'Tell us who you need and get a shortlist of pre-screened candidates.', 'eyesonjobs-core' ), home_url( '/employers/' ), __( 'Post requirement', 'eyesonjobs-core' ) ),
			array( __( 'Our services', 'eyesonjobs-core' ), __( 'Executive search, background verification and campus hiring.', 'eyesonjobs-core' ), home_url( '/services/' ), __( 'View services', 'eyesonjobs-core' ) ),
		);
	} else {
		$actions = array(
			array( __( 'Browse jobs', 'eyesonjobs-core' ), __( 'Fresh openings from verified employers across India.', 'eyesonjobs-core' ), $jobs, __( 'Find jobs', 'eyesonjobs-core' ) ),
			array( __( 'Submit your resume', 'eyesonjobs-core' ), __( 'Share your profile once and get matched with suitable roles.', 'eyesonjobs-core' ), home_url( '/submit-resume/' ), __( 'Upload resume', 'eyesonjobs-core' ) ),
			array( __( 'Job Preparation Package', 'eyesonjobs-core' ), __( 'ATS-friendly resume, mock interview and salary guidance.', 'eyesonjobs-core' ), home_url( '/job-preparation-package/' ), __( 'Explore', 'eyesonjobs-core' ) ),
		);
	}

	$html  = '<div class="eoj-account">';
	$html .= '<aside class="eoj-account__nav"><div class="eoj-account__avatar" aria-hidden="true">' . esc_html( $initial ) . '</div>';
	$html .= '<h3>' . esc_html( $user->display_name ) . '</h3><p>' . esc_html( eoj_role_label( $user ) ) . '</p><ul>';
	$html .= '<li><a href="#eoj-overview">' . esc_html__( 'Overview', 'eyesonjobs-core' ) . '</a></li>';
	$html .= '<li><a href="#eoj-registrations">' . esc_html__( 'My registrations', 'eyesonjobs-core' ) . '</a></li>';
	$html .= '<li><a href="#eoj_profile">' . esc_html__( 'Profile', 'eyesonjobs-core' ) . '</a></li>';
	$html .= '<li><a href="' . esc_url( $jobs ) . '">' . esc_html__( 'Browse jobs', 'eyesonjobs-core' ) . '</a></li>';
	if ( current_user_can( 'edit_posts' ) ) {
		$html .= '<li><a href="' . esc_url( admin_url() ) . '">' . esc_html__( 'Dashboard', 'eyesonjobs-core' ) . '</a></li>';
	}
	$html .= '<li><a href="' . esc_url( wp_logout_url( eoj_core_account_url() ) ) . '">' . esc_html__( 'Log out', 'eyesonjobs-core' ) . '</a></li></ul></aside>';

	$html .= '<div class="eoj-account__main">';
	$html .= eoj_form_notice( 'eoj_register' );
	$html .= '<section id="eoj-overview" class="eoj-card"><h2 class="eoj-h3">' . esc_html( sprintf( /* translators: %s first name */ __( 'Welcome, %s', 'eyesonjobs-core' ), $user->first_name ? $user->first_name : $user->display_name ) ) . '</h2><div class="eoj-grid">';
	foreach ( $actions as $action ) {
		$html .= '<div class="eoj-card"><h3>' . esc_html( $action[0] ) . '</h3><p>' . esc_html( $action[1] ) . '</p><a class="eoj-btn eoj-btn--small eoj-btn--primary" href="' . esc_url( $action[2] ) . '">' . esc_html( $action[3] ) . '</a></div>';
	}
	$html .= '</div></section>';

	// Registrations.
	$html .= '<section id="eoj-registrations" class="eoj-card"><h2 class="eoj-h3">' . esc_html__( 'Job Preparation Package registrations', 'eyesonjobs-core' ) . '</h2>';
	$regs  = eoj_user_registrations( $user );
	if ( $regs ) {
		$html .= '<div style="overflow-x:auto"><table class="eoj-table"><thead><tr><th>' . esc_html__( 'Package', 'eyesonjobs-core' ) . '</th><th>' . esc_html__( 'Date', 'eyesonjobs-core' ) . '</th><th>' . esc_html__( 'Amount', 'eyesonjobs-core' ) . '</th><th>' . esc_html__( 'Status', 'eyesonjobs-core' ) . '</th><th></th></tr></thead><tbody>';
		foreach ( $regs as $reg ) {
			$status = get_post_meta( $reg->ID, '_eoj_status', true );
			$key    = get_post_meta( $reg->ID, '_eoj_key', true );
			$link   = '';
			if ( 'paid' !== $status && $key ) {
				$link = '<a class="eoj-link" href="' . esc_url( add_query_arg( array( 'reg' => $reg->ID, 'k' => $key ), eoj_page_url( 'job-preparation-package/register', '/job-preparation-package/register/' ) ) . '#pay' ) . '">' . esc_html__( 'Complete payment', 'eyesonjobs-core' ) . '</a>';
			}
			$html .= '<tr><td>' . esc_html( eoj_setting( 'jpp_name' ) ) . '</td><td>' . esc_html( get_the_date( 'j M Y', $reg ) ) . '</td><td>' . esc_html( eoj_core_inr( (float) get_post_meta( $reg->ID, '_eoj_amount', true ) ) ) . '</td><td><span class="eoj-status eoj-status--' . ( 'paid' === $status ? 'paid' : 'pending' ) . '">' . esc_html( 'paid' === $status ? __( 'Paid', 'eyesonjobs-core' ) : __( 'Payment pending', 'eyesonjobs-core' ) ) . '</span></td><td>' . $link . '</td></tr>';
		}
		$html .= '</tbody></table></div>';
	} else {
		$html .= '<p class="eoj-muted">' . esc_html__( 'You have not registered for the package yet.', 'eyesonjobs-core' ) . ' <a class="eoj-link" href="' . esc_url( home_url( '/job-preparation-package/' ) ) . '">' . esc_html__( 'See what is included', 'eyesonjobs-core' ) . '</a></p>';
	}
	$html .= '</section>';

	// Profile.
	$html .= '<section class="eoj-card"><h2 class="eoj-h3">' . esc_html__( 'Your profile', 'eyesonjobs-core' ) . '</h2>';
	$html .= '<p class="eoj-muted">' . esc_html( $user->user_email ) . '</p>';
	$html .= eoj_account_form( 'eoj_profile', eoj_profile_fields( $employer ), __( 'Save profile', 'eyesonjobs-core' ) );
	return $html . '</section></div></div>';
}

/**
 * Handlers.
 */
add_filter(
	'eoj_form_handlers',
	function ( $handlers ) {
		$handlers['eoj_login']    = 'eoj_submit_login';
		$handlers['eoj_register'] = 'eoj_submit_register';
		$handlers['eoj_profile']  = 'eoj_submit_profile';
		return $handlers;
	}
);

/**
 * Log in with email or username.
 *
 * @param string $form Form id.
 */
function eoj_submit_login( $form ) {
	if ( is_user_logged_in() ) {
		wp_safe_redirect( eoj_core_account_url() );
		exit;
	}
	$check = eoj_check_request( $form );
	if ( true !== $check ) {
		eoj_redirect_back( $form, false, $check );
	}
	$fields = eoj_login_fields();
	$result = eoj_collect_fields( $fields );
	if ( $result['errors'] ) {
		eoj_fail_with_fields( $form, $fields, $result );
	}
	$user = wp_signon(
		array(
			'user_login'    => $result['values']['login'],
			'user_password' => $result['values']['password'],
			'remember'      => (bool) $result['values']['remember'],
		),
		is_ssl()
	);
	if ( is_wp_error( $user ) ) {
		$result['errors'] = array(
			'login'    => '',
			'password' => '',
		);
		eoj_fail_with_fields( $form, $fields, $result, array(), 'login_failed' );
	}
	wp_safe_redirect( eoj_is_member( $user ) || ! user_can( $user, 'edit_posts' ) ? eoj_core_account_url() : admin_url() );
	exit;
}

/**
 * Create a candidate or employer account.
 *
 * @param string $form Form id.
 */
function eoj_submit_register( $form ) {
	$tab = array( 'tab' => 'register' );
	if ( is_user_logged_in() ) {
		wp_safe_redirect( eoj_core_account_url() );
		exit;
	}
	if ( ! eoj_setting( 'allow_registration' ) ) {
		eoj_redirect_back( $form, false, 'closed', array(), $tab );
	}
	$check = eoj_check_request( $form );
	if ( true !== $check ) {
		eoj_redirect_back( $form, false, $check, array(), $tab );
	}
	$fields = eoj_register_fields();
	$result = eoj_collect_fields( $fields );
	if ( $result['errors'] ) {
		eoj_fail_with_fields( $form, $fields, $result, $tab );
	}
	$v = $result['values'];
	if ( strlen( $v['password'] ) < 8 ) {
		$result['errors']['password'] = 'weak';
		eoj_fail_with_fields( $form, $fields, $result, $tab, 'weak_password' );
	}
	if ( $v['password'] !== $v['password2'] ) {
		$result['errors']['password2'] = 'mismatch';
		eoj_fail_with_fields( $form, $fields, $result, $tab, 'mismatch' );
	}
	$login = sanitize_user( $v['email'], true );
	if ( email_exists( $v['email'] ) || username_exists( $login ) ) {
		$result['errors']['email'] = 'exists';
		eoj_fail_with_fields( $form, $fields, $result, $tab, 'email_exists' );
	}

	$parts   = explode( ' ', $v['name'], 2 );
	$role    = 'employer' === $v['role'] ? 'eoj_employer' : 'eoj_candidate';
	$user_id = wp_insert_user(
		array(
			'user_login'   => $login,
			'user_email'   => $v['email'],
			'user_pass'    => $v['password'],
			'display_name' => $v['name'],
			'first_name'   => $parts[0],
			'last_name'    => isset( $parts[1] ) ? $parts[1] : '',
			'role'         => $role,
		)
	);
	if ( is_wp_error( $user_id ) ) {
		eoj_redirect_back( $form, false, 'error', array(), $tab );
	}
	update_user_meta( $user_id, 'eoj_phone', $v['phone'] );
	if ( 'eoj_employer' === $role ) {
		update_user_meta( $user_id, 'eoj_company', $v['company'] );
	}

	$user = get_user_by( 'id', $user_id );
	wp_set_current_user( $user_id );
	wp_set_auth_cookie( $user_id, true );
	do_action( 'wp_login', $user->user_login, $user );

	$account = eoj_core_account_url();
	eoj_mail(
		$v['email'],
		__( 'Welcome to Eyes on Jobs', 'eyesonjobs-core' ),
		'<p>' . esc_html( sprintf( /* translators: %s name */ __( 'Hi %s,', 'eyesonjobs-core' ), $v['name'] ) ) . '</p><p>' . esc_html__( 'Your account is ready. You can log in any time with this email address to browse jobs, share your resume or send us a hiring requirement.', 'eyesonjobs-core' ) . '</p><p><a href="' . esc_url( $account ) . '">' . esc_html__( 'Go to my account', 'eyesonjobs-core' ) . '</a></p>'
	);
	eoj_mail(
		eoj_notify_emails(),
		/* translators: %s role */
		sprintf( __( 'New %s account', 'eyesonjobs-core' ), 'eoj_employer' === $role ? 'employer' : 'candidate' ),
		__( 'Someone created an account on the website.', 'eyesonjobs-core' ),
		array(
			__( 'Name', 'eyesonjobs-core' )    => $v['name'],
			__( 'Email', 'eyesonjobs-core' )   => $v['email'],
			__( 'Phone', 'eyesonjobs-core' )   => $v['phone'],
			__( 'Role', 'eyesonjobs-core' )    => 'eoj_employer' === $role ? __( 'Employer', 'eyesonjobs-core' ) : __( 'Candidate', 'eyesonjobs-core' ),
			__( 'Company', 'eyesonjobs-core' ) => $v['company'],
		),
		array(),
		$v['email']
	);

	wp_safe_redirect(
		add_query_arg(
			array(
				'eoj_form'   => 'eoj_register',
				'eoj_status' => 'ok',
				'eoj_msg'    => 'welcome',
			),
			$account
		)
	);
	exit;
}

/**
 * Update the logged-in user's profile.
 *
 * @param string $form Form id.
 */
function eoj_submit_profile( $form ) {
	if ( ! is_user_logged_in() ) {
		wp_safe_redirect( eoj_core_account_url() );
		exit;
	}
	$check = eoj_check_request( $form );
	if ( true !== $check ) {
		eoj_redirect_back( $form, false, $check );
	}
	$user   = wp_get_current_user();
	$fields = eoj_profile_fields( in_array( 'eoj_employer', $user->roles, true ) );
	$result = eoj_collect_fields( $fields );
	if ( $result['errors'] ) {
		eoj_fail_with_fields( $form, $fields, $result );
	}
	$v = $result['values'];
	if ( '' !== $v['new_password'] && strlen( $v['new_password'] ) < 8 ) {
		$result['errors']['new_password'] = 'weak';
		eoj_fail_with_fields( $form, $fields, $result, array(), 'weak_password' );
	}
	$parts = explode( ' ', $v['name'], 2 );
	$args  = array(
		'ID'           => $user->ID,
		'display_name' => $v['name'],
		'first_name'   => $parts[0],
		'last_name'    => isset( $parts[1] ) ? $parts[1] : '',
	);
	if ( '' !== $v['new_password'] ) {
		$args['user_pass'] = $v['new_password'];
	}
	$updated = wp_update_user( $args );
	if ( is_wp_error( $updated ) ) {
		eoj_redirect_back( $form, false, 'error' );
	}
	update_user_meta( $user->ID, 'eoj_phone', $v['phone'] );
	if ( isset( $v['company'] ) ) {
		update_user_meta( $user->ID, 'eoj_company', $v['company'] );
	}
	if ( '' !== $v['new_password'] ) {
		// Changing the password ends the session, so sign the user straight back in.
		wp_set_current_user( $user->ID );
		wp_set_auth_cookie( $user->ID, true );
	}
	eoj_redirect_back( $form, true, 'profile' );
}

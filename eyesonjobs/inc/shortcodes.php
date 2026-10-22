<?php
/**
 * Presentation shortcodes used inside page content. Each one can be
 * moved, removed or re-added from the block editor with a Shortcode block.
 *
 * @package EyesOnJobs
 */

defined( 'ABSPATH' ) || exit;

/**
 * Inline illustration from assets/img. [eoj_art name="hero"]
 *
 * @param string $name Illustration name.
 * @return string
 */
function eoj_art( $name ) {
	$allowed = array( 'hero', 'executive', 'verify', 'prep', 'csr', 'contact', 'search', 'team' );
	if ( ! in_array( $name, $allowed, true ) ) {
		return '';
	}
	$file = EOJ_THEME_DIR . '/assets/img/' . $name . '.svg';
	if ( ! is_readable( $file ) ) {
		return '';
	}
	return '<div class="eoj-art eoj-art--' . esc_attr( $name ) . '">' . file_get_contents( $file ) . '</div>'; // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
}

add_shortcode(
	'eoj_art',
	function ( $atts ) {
		$atts = shortcode_atts( array( 'name' => 'hero' ), $atts );
		return eoj_art( sanitize_key( $atts['name'] ) );
	}
);

/**
 * Icon badge for cards. [eoj_icon name="shield"]
 */
add_shortcode(
	'eoj_icon',
	function ( $atts ) {
		$atts = shortcode_atts( array( 'name' => 'star' ), $atts );
		return '<span class="eoj-iconbox">' . eoj_icon( sanitize_key( $atts['name'] ) ) . '</span>';
	}
);

/**
 * Rotating word. [eoj_rotator words="IT|Sales|Finance"]
 */
add_shortcode(
	'eoj_rotator',
	function ( $atts ) {
		$atts  = shortcode_atts( array( 'words' => 'IT|Sales|Finance|Marketing|HR' ), $atts );
		$words = array_filter( array_map( 'trim', explode( '|', html_entity_decode( $atts['words'], ENT_QUOTES, 'UTF-8' ) ) ) );
		if ( ! $words ) {
			return '';
		}
		return '<span class="eoj-rotator" data-words="' . esc_attr( wp_json_encode( array_values( $words ) ) ) . '">' . esc_html( reset( $words ) ) . '</span>';
	}
);

/**
 * Animated counters. [eoj_stats items="11+|Industries served;3|Hiring levels"]
 */
add_shortcode(
	'eoj_stats',
	function ( $atts ) {
		$atts  = shortcode_atts( array( 'items' => '' ), $atts );
		$items = array_filter( array_map( 'trim', explode( ';', $atts['items'] ) ) );
		$out   = '<div class="eoj-stats">';
		foreach ( $items as $item ) {
			$parts = array_map( 'trim', explode( '|', $item, 2 ) );
			$value = $parts[0];
			$label = isset( $parts[1] ) ? $parts[1] : '';
			if ( preg_match( '/^(\D*)([\d,.]+)(.*)$/u', $value, $m ) ) {
				$num   = str_replace( ',', '', $m[2] );
				$value = esc_html( $m[1] ) . '<span class="eoj-count" data-target="' . esc_attr( $num ) . '">0</span>' . esc_html( $m[3] );
			} else {
				$value = esc_html( $value );
			}
			$out .= '<div class="eoj-stat" data-reveal><strong>' . $value . '</strong><span>' . esc_html( $label ) . '</span></div>';
		}
		return $out . '</div>';
	}
);

/**
 * Terms for a job taxonomy, or a fallback list before the plugin is active.
 *
 * @param string $taxonomy Taxonomy.
 * @param array  $fallback Names.
 * @return array slug => name
 */
function eoj_term_options( $taxonomy, $fallback ) {
	if ( taxonomy_exists( $taxonomy ) ) {
		$terms = get_terms(
			array(
				'taxonomy'   => $taxonomy,
				'hide_empty' => false,
			)
		);
		if ( $terms && ! is_wp_error( $terms ) ) {
			return wp_list_pluck( $terms, 'name', 'slug' );
		}
	}
	$out = array();
	foreach ( $fallback as $name ) {
		$out[ sanitize_title( $name ) ] = $name;
	}
	return $out;
}

/**
 * Job search box. [eoj_job_search]
 */
add_shortcode(
	'eoj_job_search',
	function () {
		$action = post_type_exists( 'eoj_job' ) ? get_post_type_archive_link( 'eoj_job' ) : home_url( '/jobs/' );
		$locs   = eoj_term_options( 'eoj_job_location', array( 'Delhi NCR', 'Gurugram', 'Noida', 'Mumbai', 'Bengaluru', 'Pune', 'Hyderabad', 'Chennai', 'Pan India' ) );
		$cats   = eoj_term_options( 'eoj_job_category', array( 'IT & Software', 'Sales', 'Finance & Accounts', 'Marketing', 'HR', 'Fashion & Design' ) );
		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		$k   = isset( $_GET['k'] ) ? sanitize_text_field( wp_unslash( $_GET['k'] ) ) : '';
		$loc = isset( $_GET['loc'] ) ? sanitize_key( wp_unslash( $_GET['loc'] ) ) : '';
		$cat = isset( $_GET['cat'] ) ? sanitize_key( wp_unslash( $_GET['cat'] ) ) : '';
		// phpcs:enable
		ob_start();
		?>
		<form class="eoj-jobsearch" action="<?php echo esc_url( $action ); ?>" method="get" role="search">
			<label class="eoj-jobsearch__field">
				<?php echo eoj_icon( 'search' ); // phpcs:ignore ?>
				<span class="screen-reader-text"><?php esc_html_e( 'Job title or skill', 'eyesonjobs' ); ?></span>
				<input type="text" name="k" value="<?php echo esc_attr( $k ); ?>" placeholder="<?php esc_attr_e( 'Job title, skill or company', 'eyesonjobs' ); ?>">
			</label>
			<label class="eoj-jobsearch__field">
				<?php echo eoj_icon( 'pin' ); // phpcs:ignore ?>
				<span class="screen-reader-text"><?php esc_html_e( 'Location', 'eyesonjobs' ); ?></span>
				<select name="loc">
					<option value=""><?php esc_html_e( 'Any location', 'eyesonjobs' ); ?></option>
					<?php foreach ( $locs as $slug => $name ) : ?>
						<option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $loc, $slug ); ?>><?php echo esc_html( $name ); ?></option>
					<?php endforeach; ?>
				</select>
			</label>
			<label class="eoj-jobsearch__field">
				<?php echo eoj_icon( 'briefcase' ); // phpcs:ignore ?>
				<span class="screen-reader-text"><?php esc_html_e( 'Industry', 'eyesonjobs' ); ?></span>
				<select name="cat">
					<option value=""><?php esc_html_e( 'All industries', 'eyesonjobs' ); ?></option>
					<?php foreach ( $cats as $slug => $name ) : ?>
						<option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $cat, $slug ); ?>><?php echo esc_html( $name ); ?></option>
					<?php endforeach; ?>
				</select>
			</label>
			<button class="eoj-btn eoj-btn--primary" type="submit"><?php esc_html_e( 'Search jobs', 'eyesonjobs' ); ?></button>
		</form>
		<?php
		return ob_get_clean();
	}
);

/**
 * Latest openings. [eoj_latest_jobs count="4"]
 */
add_shortcode(
	'eoj_latest_jobs',
	function ( $atts ) {
		if ( ! post_type_exists( 'eoj_job' ) ) {
			return '';
		}
		$atts = shortcode_atts( array( 'count' => 4 ), $atts );
		$q    = new WP_Query(
			array(
				'post_type'      => 'eoj_job',
				'posts_per_page' => absint( $atts['count'] ),
				'no_found_rows'  => true,
			)
		);
		ob_start();
		if ( $q->have_posts() ) {
			echo '<div class="eoj-joblist">';
			while ( $q->have_posts() ) {
				$q->the_post();
				eoj_job_card();
			}
			echo '</div>';
		} else {
			echo '<div class="eoj-empty eoj-card"><p>' . esc_html__( 'New openings are being added. Share your resume and be the first to hear about them.', 'eyesonjobs' ) . '</p><a class="eoj-btn eoj-btn--primary" href="' . esc_url( home_url( '/submit-resume/' ) ) . '">' . esc_html__( 'Submit your resume', 'eyesonjobs' ) . '</a></div>';
		}
		wp_reset_postdata();
		return ob_get_clean();
	}
);

/**
 * Job Preparation Package price settings (shared with the plugin).
 *
 * @return array
 */
function eoj_jpp_prices() {
	$s = get_option( 'eoj_settings', array() );
	return array(
		'mrp'   => isset( $s['jpp_mrp'] ) && $s['jpp_mrp'] ? (float) $s['jpp_mrp'] : 7080,
		'price' => isset( $s['jpp_price'] ) && $s['jpp_price'] ? (float) $s['jpp_price'] : 3540,
	);
}

/**
 * Format rupees, Indian grouping.
 *
 * @param float $amount Amount.
 */
function eoj_inr( $amount ) {
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
 * Pricing card. [eoj_jpp_price button="Register now"]
 */
add_shortcode(
	'eoj_jpp_price',
	function ( $atts ) {
		$atts   = shortcode_atts(
			array(
				'button' => __( 'Register now', 'eyesonjobs' ),
				'url'    => '/job-preparation-package/register/',
			),
			$atts
		);
		$p      = eoj_jpp_prices();
		$off    = $p['mrp'] > 0 ? round( ( 1 - $p['price'] / $p['mrp'] ) * 100 ) : 0;
		$points = array(
			__( 'ATS-friendly resume, professionally written', 'eyesonjobs' ),
			__( 'HR screening and role-specific interview prep', 'eyesonjobs' ),
			__( 'Live mock interview with detailed feedback', 'eyesonjobs' ),
			__( 'Salary negotiation and joining-stage guidance', 'eyesonjobs' ),
		);
		ob_start();
		?>
		<div class="eoj-price" data-reveal>
			<?php if ( $off > 0 ) : ?>
				<span class="eoj-price__ribbon"><?php echo esc_html( $off ); ?>% <?php esc_html_e( 'OFF', 'eyesonjobs' ); ?></span>
			<?php endif; ?>
			<p class="eoj-price__name"><?php esc_html_e( 'Complete Job Preparation Package', 'eyesonjobs' ); ?></p>
			<p class="eoj-price__amount">
				<?php if ( $p['mrp'] > $p['price'] ) : ?>
					<del aria-label="<?php esc_attr_e( 'Original price', 'eyesonjobs' ); ?>"><?php echo esc_html( eoj_inr( $p['mrp'] ) ); ?></del>
				<?php endif; ?>
				<strong><?php echo esc_html( eoj_inr( $p['price'] ) ); ?></strong>
			</p>
			<p class="eoj-price__tax"><?php esc_html_e( 'One-time fee · Inclusive of all taxes', 'eyesonjobs' ); ?></p>
			<ul class="eoj-checklist">
				<?php foreach ( $points as $point ) : ?>
					<li><?php echo eoj_icon( 'check' ); // phpcs:ignore ?><?php echo esc_html( $point ); ?></li>
				<?php endforeach; ?>
			</ul>
			<a class="eoj-btn eoj-btn--primary eoj-btn--block eoj-btn--glow" href="<?php echo esc_url( home_url( $atts['url'] ) ); ?>"><?php echo esc_html( $atts['button'] ); ?> <?php echo eoj_icon( 'arrow' ); // phpcs:ignore ?></a>
			<p class="eoj-price__secure"><?php echo eoj_icon( 'lock' ); // phpcs:ignore ?> <?php esc_html_e( 'Secure payment by UPI, cards, net banking or wallets', 'eyesonjobs' ); ?></p>
		</div>
		<?php
		return ob_get_clean();
	}
);

/**
 * Inline price, e.g. inside a paragraph. [eoj_jpp_inline]
 */
add_shortcode(
	'eoj_jpp_inline',
	function () {
		$p = eoj_jpp_prices();
		return '<span class="eoj-inlineprice"><del>' . esc_html( eoj_inr( $p['mrp'] ) ) . '</del> <strong>' . esc_html( eoj_inr( $p['price'] ) ) . '</strong></span>';
	}
);

/**
 * Contact detail cards from the Customizer. [eoj_contact_cards]
 */
add_shortcode(
	'eoj_contact_cards',
	function () {
		$phones = array_filter( array( eoj_opt( 'eoj_phone' ), eoj_opt( 'eoj_phone_2' ) ) );
		$emails = array_filter( array( eoj_opt( 'eoj_email' ), eoj_opt( 'eoj_email_2' ) ) );
		ob_start();
		?>
		<div class="eoj-contactcards">
			<div class="eoj-card" data-reveal>
				<span class="eoj-iconbox"><?php echo eoj_icon( 'phone' ); // phpcs:ignore ?></span>
				<h3><?php esc_html_e( 'Call us', 'eyesonjobs' ); ?></h3>
				<?php foreach ( $phones as $phone ) : ?>
					<p><a href="<?php echo esc_attr( eoj_tel( $phone ) ); ?>"><?php echo esc_html( $phone ); ?></a></p>
				<?php endforeach; ?>
			</div>
			<div class="eoj-card" data-reveal>
				<span class="eoj-iconbox"><?php echo eoj_icon( 'mail' ); // phpcs:ignore ?></span>
				<h3><?php esc_html_e( 'Email us', 'eyesonjobs' ); ?></h3>
				<?php foreach ( $emails as $email ) : ?>
					<p><a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a></p>
				<?php endforeach; ?>
			</div>
			<div class="eoj-card" data-reveal>
				<span class="eoj-iconbox"><?php echo eoj_icon( 'pin' ); // phpcs:ignore ?></span>
				<h3><?php esc_html_e( 'Visit us', 'eyesonjobs' ); ?></h3>
				<p><?php echo nl2br( esc_html( eoj_opt( 'eoj_address' ) ) ); ?></p>
			</div>
			<div class="eoj-card" data-reveal>
				<span class="eoj-iconbox"><?php echo eoj_icon( 'clock' ); // phpcs:ignore ?></span>
				<h3><?php esc_html_e( 'Office hours', 'eyesonjobs' ); ?></h3>
				<p><?php echo esc_html( eoj_opt( 'eoj_hours' ) ); ?></p>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}
);

/**
 * Google map of the office address. [eoj_map]
 */
add_shortcode(
	'eoj_map',
	function () {
		$q = rawurlencode( str_replace( "\n", ' ', eoj_opt( 'eoj_address' ) ) );
		return '<div class="eoj-map" data-reveal><iframe title="' . esc_attr__( 'Office location map', 'eyesonjobs' ) . '" src="https://www.google.com/maps?q=' . esc_attr( $q ) . '&amp;output=embed" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe></div>';
	}
);

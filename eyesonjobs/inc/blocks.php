<?php
/**
 * Tiny helpers that write valid core-block markup, so starter pages open
 * cleanly in the block editor and stay fully editable.
 *
 * @package EyesOnJobs
 */

defined( 'ABSPATH' ) || exit;

/**
 * Block attributes JSON.
 *
 * @param array $attrs Attributes.
 */
function eoj_b_attrs( $attrs ) {
	$attrs = array_filter( $attrs );
	return $attrs ? ' ' . wp_json_encode( $attrs, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) : '';
}

/**
 * Class attribute.
 *
 * @param string $base  Base class.
 * @param string $extra Extra class.
 */
function eoj_b_class( $base, $extra ) {
	return trim( $base . ' ' . $extra );
}

/** Paragraph. */
function eoj_b_p( $html, $class = '' ) {
	$attr = $class ? ' class="' . esc_attr( $class ) . '"' : '';
	return '<!-- wp:paragraph' . eoj_b_attrs( array( 'className' => $class ) ) . ' --><p' . $attr . '>' . $html . '</p><!-- /wp:paragraph -->';
}

/** Heading. */
function eoj_b_h( $html, $level = 2, $class = '' ) {
	$attrs = array(
		'level'     => 2 === $level ? null : $level,
		'className' => $class,
	);
	return '<!-- wp:heading' . eoj_b_attrs( $attrs ) . ' --><h' . $level . ' class="' . esc_attr( eoj_b_class( 'wp-block-heading', $class ) ) . '">' . $html . '</h' . $level . '><!-- /wp:heading -->';
}

/** Group (section wrapper). */
function eoj_b_group( $inner, $class = '', $tag = 'div' ) {
	$attrs = array(
		'tagName'   => 'div' === $tag ? null : $tag,
		'className' => $class,
	);
	return '<!-- wp:group' . eoj_b_attrs( $attrs ) . ' --><' . $tag . ' class="' . esc_attr( eoj_b_class( 'wp-block-group', $class ) ) . '">' . implode( '', (array) $inner ) . '</' . $tag . '><!-- /wp:group -->';
}

/** Section = full-width group with an inner container. */
function eoj_b_section( $inner, $class = '' ) {
	return eoj_b_group( eoj_b_group( $inner, 'eoj-wrap' ), eoj_b_class( 'eoj-section', $class ), 'section' );
}

/** Columns. Each item is inner markup, or array( inner, class ). */
function eoj_b_cols( $cols, $class = '' ) {
	$out = '';
	foreach ( $cols as $col ) {
		$col_class = '';
		if ( is_array( $col ) ) {
			list( $col, $col_class ) = $col;
		}
		$out .= '<!-- wp:column' . eoj_b_attrs( array( 'className' => $col_class ) ) . ' --><div class="' . esc_attr( eoj_b_class( 'wp-block-column', $col_class ) ) . '">' . implode( '', (array) $col ) . '</div><!-- /wp:column -->';
	}
	return '<!-- wp:columns' . eoj_b_attrs( array( 'className' => $class ) ) . ' --><div class="' . esc_attr( eoj_b_class( 'wp-block-columns', $class ) ) . '">' . $out . '</div><!-- /wp:columns -->';
}

/** List. */
function eoj_b_list( $items, $class = '' ) {
	$out = '';
	foreach ( $items as $item ) {
		$out .= '<!-- wp:list-item --><li>' . $item . '</li><!-- /wp:list-item -->';
	}
	return '<!-- wp:list' . eoj_b_attrs( array( 'className' => $class ) ) . ' --><ul class="' . esc_attr( eoj_b_class( 'wp-block-list', $class ) ) . '">' . $out . '</ul><!-- /wp:list -->';
}

/** Buttons: array of array( label, url, 'outline'|'' ). */
function eoj_b_btns( $buttons, $class = '' ) {
	$out = '';
	foreach ( $buttons as $btn ) {
		$style = ! empty( $btn[2] ) ? 'is-style-' . $btn[2] : '';
		$out  .= '<!-- wp:button' . eoj_b_attrs( array( 'className' => $style ) ) . ' --><div class="' . esc_attr( eoj_b_class( 'wp-block-button', $style ) ) . '"><a class="wp-block-button__link wp-element-button" href="' . esc_url( $btn[1] ) . '">' . esc_html( $btn[0] ) . '</a></div><!-- /wp:button -->';
	}
	return '<!-- wp:buttons' . eoj_b_attrs( array( 'className' => $class ) ) . ' --><div class="' . esc_attr( eoj_b_class( 'wp-block-buttons', $class ) ) . '">' . $out . '</div><!-- /wp:buttons -->';
}

/** Shortcode block. */
function eoj_b_sc( $code ) {
	return '<!-- wp:shortcode -->' . $code . '<!-- /wp:shortcode -->';
}

/** Quote (testimonial). */
function eoj_b_quote( $text, $cite, $class = '' ) {
	return '<!-- wp:quote' . eoj_b_attrs( array( 'className' => $class ) ) . ' --><blockquote class="' . esc_attr( eoj_b_class( 'wp-block-quote', $class ) ) . '">' . eoj_b_p( $text ) . '<cite>' . $cite . '</cite></blockquote><!-- /wp:quote -->';
}

/** Details (FAQ). */
function eoj_b_faq( $q, $a ) {
	return '<!-- wp:details {"className":"eoj-faq"} --><details class="wp-block-details eoj-faq"><summary>' . $q . '</summary>' . eoj_b_p( $a ) . '</details><!-- /wp:details -->';
}

/** Section heading trio: eyebrow, title, intro. */
function eoj_b_head( $eyebrow, $title, $intro = '', $center = true ) {
	$wrap = $center ? 'eoj-head eoj-head--center' : 'eoj-head';
	$out  = array( eoj_b_p( $eyebrow, 'eoj-eyebrow' ), eoj_b_h( $title, 2, 'eoj-title' ) );
	if ( $intro ) {
		$out[] = eoj_b_p( $intro, 'eoj-lead' );
	}
	return eoj_b_group( $out, $wrap );
}

/** Card: icon, title, text, optional link. */
function eoj_b_card( $icon, $title, $text, $link = array(), $class = 'eoj-card' ) {
	$inner = array();
	if ( $icon ) {
		$inner[] = eoj_b_sc( '[eoj_icon name="' . $icon . '"]' );
	}
	$inner[] = eoj_b_h( $title, 3 );
	$inner[] = eoj_b_p( $text );
	if ( $link ) {
		$inner[] = eoj_b_p( '<a href="' . esc_url( $link[1] ) . '">' . esc_html( $link[0] ) . '</a>', 'eoj-link' );
	}
	return eoj_b_group( $inner, $class );
}

/** Grid of cards. */
function eoj_b_grid( $cards, $class = 'eoj-grid-3' ) {
	return eoj_b_group( $cards, 'eoj-grid ' . $class );
}

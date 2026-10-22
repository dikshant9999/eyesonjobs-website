<?php
/**
 * Site header.
 *
 * @package EyesOnJobs
 */

?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="theme-color" content="#0B1F4D">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link screen-reader-text" href="#content"><?php esc_html_e( 'Skip to content', 'eyesonjobs' ); ?></a>

<?php $eoj_announcement = eoj_opt( 'eoj_announcement' ); ?>
<?php if ( $eoj_announcement ) : ?>
	<div class="eoj-announce">
		<a href="<?php echo esc_url( home_url( eoj_opt( 'eoj_announce_url' ) ) ); ?>">
			<span class="eoj-announce__badge"><?php esc_html_e( 'New', 'eyesonjobs' ); ?></span>
			<span><?php echo esc_html( preg_replace( '/^New:\s*/', '', $eoj_announcement ) ); ?></span>
			<?php echo eoj_icon( 'arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</a>
	</div>
<?php endif; ?>

<header class="eoj-header" id="top">
	<div class="eoj-container eoj-header__inner">
		<?php eoj_logo(); ?>

		<nav class="eoj-nav" id="eoj-nav" aria-label="<?php esc_attr_e( 'Main menu', 'eyesonjobs' ); ?>">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'container'      => false,
					'menu_class'     => 'eoj-menu',
					'fallback_cb'    => 'eoj_fallback_menu',
					'depth'          => 2,
				)
			);
			?>
			<div class="eoj-nav__actions">
				<?php if ( is_user_logged_in() ) : ?>
					<a class="eoj-btn eoj-btn--ghost" href="<?php echo esc_url( eoj_account_url() ); ?>"><?php echo eoj_icon( 'user' ); // phpcs:ignore ?> <?php esc_html_e( 'My account', 'eyesonjobs' ); ?></a>
				<?php else : ?>
					<a class="eoj-btn eoj-btn--ghost" href="<?php echo esc_url( eoj_account_url() ); ?>"><?php esc_html_e( 'Log in', 'eyesonjobs' ); ?></a>
					<a class="eoj-btn eoj-btn--primary" href="<?php echo esc_url( add_query_arg( 'tab', 'register', eoj_account_url() ) ); ?>"><?php esc_html_e( 'Register', 'eyesonjobs' ); ?></a>
				<?php endif; ?>
			</div>
		</nav>

		<button class="eoj-burger" type="button" aria-controls="eoj-nav" aria-expanded="false">
			<span class="screen-reader-text"><?php esc_html_e( 'Open menu', 'eyesonjobs' ); ?></span>
			<span class="eoj-burger__bars" aria-hidden="true"></span>
		</button>
	</div>
</header>

<?php if ( eoj_has_page_hero() ) : ?>
	<?php list( $eoj_title, $eoj_sub, $eoj_eyebrow ) = eoj_hero_text(); ?>
	<section class="eoj-pagehero">
		<div class="eoj-pagehero__shapes" aria-hidden="true"><span></span><span></span><span></span></div>
		<div class="eoj-container eoj-pagehero__inner">
			<?php if ( $eoj_eyebrow ) : ?>
				<p class="eoj-eyebrow eoj-eyebrow--light"><?php echo esc_html( $eoj_eyebrow ); ?></p>
			<?php endif; ?>
			<h1 class="eoj-pagehero__title"><?php echo wp_kses_post( $eoj_title ); ?></h1>
			<?php if ( $eoj_sub ) : ?>
				<p class="eoj-pagehero__sub"><?php echo esc_html( $eoj_sub ); ?></p>
			<?php endif; ?>
		</div>
		<svg class="eoj-pagehero__wave" viewBox="0 0 1440 80" preserveAspectRatio="none" aria-hidden="true"><path d="M0 40c240 40 480 40 720 20s480-50 720-20v40H0z"/></svg>
	</section>
<?php endif; ?>

<main id="content" class="eoj-main">

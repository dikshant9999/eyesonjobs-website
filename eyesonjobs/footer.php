<?php
/**
 * Site footer.
 *
 * @package EyesOnJobs
 */

?>
</main>

<footer class="eoj-footer">
	<div class="eoj-container eoj-footer__grid">
		<div class="eoj-footer__brand">
			<?php eoj_logo( 'dark' ); ?>
			<p><?php echo esc_html( eoj_opt( 'eoj_tagline' ) ); ?></p>
			<?php eoj_social_links(); ?>
		</div>

		<div>
			<h3 class="eoj-footer__title"><?php esc_html_e( 'Quick links', 'eyesonjobs' ); ?></h3>
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'footer',
					'container'      => false,
					'menu_class'     => 'eoj-footer__links',
					'depth'          => 1,
					'fallback_cb'    => 'eoj_fallback_menu',
				)
			);
			?>
		</div>

		<div>
			<h3 class="eoj-footer__title"><?php esc_html_e( 'Services', 'eyesonjobs' ); ?></h3>
			<ul class="eoj-footer__links">
				<li><a href="<?php echo esc_url( home_url( '/executive-search/' ) ); ?>"><?php esc_html_e( 'Executive Search', 'eyesonjobs' ); ?></a></li>
				<li><a href="<?php echo esc_url( home_url( '/background-verification/' ) ); ?>"><?php esc_html_e( 'Background Verification', 'eyesonjobs' ); ?></a></li>
				<li><a href="<?php echo esc_url( home_url( '/job-preparation-package/' ) ); ?>"><?php esc_html_e( 'Job Preparation Package', 'eyesonjobs' ); ?></a></li>
				<li><a href="<?php echo esc_url( home_url( '/services/' ) ); ?>"><?php esc_html_e( 'Campus & Volume Hiring', 'eyesonjobs' ); ?></a></li>
				<li><a href="<?php echo esc_url( home_url( '/submit-resume/' ) ); ?>"><?php esc_html_e( 'Submit Your Resume', 'eyesonjobs' ); ?></a></li>
			</ul>
		</div>

		<div>
			<h3 class="eoj-footer__title"><?php esc_html_e( 'Get in touch', 'eyesonjobs' ); ?></h3>
			<ul class="eoj-footer__contact">
				<li><?php echo eoj_icon( 'phone' ); // phpcs:ignore ?><a href="<?php echo esc_attr( eoj_tel( eoj_opt( 'eoj_phone' ) ) ); ?>"><?php echo esc_html( eoj_opt( 'eoj_phone' ) ); ?></a></li>
				<?php if ( eoj_opt( 'eoj_phone_2' ) ) : ?>
					<li><?php echo eoj_icon( 'building' ); // phpcs:ignore ?><a href="<?php echo esc_attr( eoj_tel( eoj_opt( 'eoj_phone_2' ) ) ); ?>"><?php echo esc_html( eoj_opt( 'eoj_phone_2' ) ); ?></a></li>
				<?php endif; ?>
				<li><?php echo eoj_icon( 'mail' ); // phpcs:ignore ?><a href="mailto:<?php echo esc_attr( eoj_opt( 'eoj_email' ) ); ?>"><?php echo esc_html( eoj_opt( 'eoj_email' ) ); ?></a></li>
				<li><?php echo eoj_icon( 'pin' ); // phpcs:ignore ?><span><?php echo nl2br( esc_html( eoj_opt( 'eoj_address' ) ) ); ?></span></li>
			</ul>
		</div>
	</div>

	<div class="eoj-footer__bottom">
		<div class="eoj-container eoj-footer__bottom-inner">
			<p>&copy; <?php echo esc_html( wp_date( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?>. <?php esc_html_e( 'All rights reserved.', 'eyesonjobs' ); ?></p>
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'legal',
					'container'      => false,
					'menu_class'     => 'eoj-footer__legal',
					'depth'          => 1,
					'fallback_cb'    => false,
				)
			);
			?>
		</div>
	</div>
</footer>

<?php if ( eoj_opt( 'eoj_show_wa' ) && eoj_opt( 'eoj_whatsapp' ) ) : ?>
	<a class="eoj-wa" href="<?php echo esc_url( 'https://wa.me/' . preg_replace( '/\D/', '', eoj_opt( 'eoj_whatsapp' ) ) ); ?>" target="_blank" rel="noopener" aria-label="<?php esc_attr_e( 'Chat with us on WhatsApp', 'eyesonjobs' ); ?>">
		<?php echo eoj_icon( 'whatsapp' ); // phpcs:ignore ?>
	</a>
<?php endif; ?>

<a class="eoj-totop" href="#top" aria-label="<?php esc_attr_e( 'Back to top', 'eyesonjobs' ); ?>"><?php echo eoj_icon( 'arrow' ); // phpcs:ignore ?></a>

<?php wp_footer(); ?>
</body>
</html>

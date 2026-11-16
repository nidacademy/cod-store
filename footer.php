</main>
<footer class="hx-footer">
	<div class="hx-wrap hx-footer__inner">
		<?php if ( has_nav_menu( 'footer' ) ) : ?>
			<nav class="hx-footer__nav" aria-label="Footer">
				<?php wp_nav_menu( array( 'theme_location' => 'footer', 'container' => false, 'menu_class' => 'hx-footer__menu', 'depth' => 1, 'fallback_cb' => false ) ); ?>
			</nav>
		<?php endif; ?>
		<p class="hx-footer__copy">&copy; <?php echo esc_html( date_i18n( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?>. <?php echo esc_html( get_theme_mod( 'hx_copyright', 'All rights are reserved' ) ); ?></p>
	</div>
</footer>
<?php
$hx_wa = preg_replace( '/\D/', '', (string) get_option( 'hx_whatsapp', '' ) );
if ( $hx_wa ) :
	?>
	<a class="hx-wa" href="<?php echo esc_url( 'https://wa.me/' . $hx_wa ); ?>" target="_blank" rel="noopener" aria-label="WhatsApp">
		<svg viewBox="0 0 24 24" width="34" height="34" aria-hidden="true"><path fill="#fff" d="M12.04 2a9.9 9.9 0 0 0-8.5 15l-1.4 5.1 5.24-1.37A9.9 9.9 0 1 0 12.04 2zm0 18.1a8.2 8.2 0 0 1-4.18-1.15l-.3-.18-3.1.82.83-3.03-.2-.31a8.2 8.2 0 1 1 6.95 3.85zm4.5-6.15c-.25-.12-1.46-.72-1.69-.8-.23-.08-.39-.12-.55.12-.16.25-.63.8-.78.97-.14.16-.29.18-.53.06-.25-.12-1.04-.38-1.98-1.22-.73-.65-1.23-1.46-1.37-1.7-.14-.25-.02-.38.11-.5.11-.11.25-.29.37-.43.12-.15.16-.25.25-.41.08-.17.04-.31-.02-.43-.06-.12-.55-1.33-.76-1.82-.2-.48-.4-.41-.55-.42h-.47a.9.9 0 0 0-.65.31c-.23.25-.86.84-.86 2.05 0 1.2.88 2.37 1 2.53.12.16 1.73 2.64 4.2 3.7.59.26 1.05.41 1.4.52.6.19 1.13.16 1.56.1.48-.07 1.46-.6 1.67-1.18.2-.58.2-1.07.14-1.18-.06-.1-.22-.16-.47-.29z"/></svg>
	</a>
<?php endif; ?>
<?php wp_footer(); ?>
</body>
</html>
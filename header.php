<!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<?php if ( function_exists( 'hx_hb_active' ) && hx_hb_active() ) : hx_hb_render(); else : ?>
<?php $hx_logo_html = hx_logo(); ?>
<div class="hx-topbar<?php echo $hx_logo_html ? ' hx-topbar--logo' : ''; ?>" style="--hx-logo-h: <?php echo (int) max( 20, min( 70, get_theme_mod( 'hx_logo_height', 40 ) ) ); ?>px">
	<?php echo $hx_logo_html; ?>
	<div class="hx-tools"><?php echo hx_lang_switcher( 'bar' ); ?><?php echo function_exists( 'hx_cur_switcher' ) ? hx_cur_switcher() : ''; ?></div>
	<button type="button" class="hx-burger" data-hx-menu-open aria-controls="hx-menu" aria-expanded="false" aria-label="القائمة"><span></span><span></span><span></span></button>
<?php echo hx_notice_html(); // phpcs:ignore WordPress.Security.EscapeOutput ?>
</div>
<?php endif; ?>
<div class="hx-menu-overlay" data-hx-menu-close hidden></div>
<nav class="hx-menu" id="hx-menu" dir="rtl" aria-label="القائمة الرئيسية" aria-hidden="true">
	<div class="hx-menu__head">
		<?php echo hx_logo( 'hx-menu__logo' ) ?: '<a class="hx-menu__brand" href="' . esc_url( home_url( '/' ) ) . '">' . esc_html( get_bloginfo( 'name' ) ) . '</a>'; ?>
		<button type="button" class="hx-menu__close" data-hx-menu-close aria-label="إغلاق">&times;</button>
	</div>
	<?php
	if ( has_nav_menu( 'primary' ) ) {
		wp_nav_menu( array( 'theme_location' => 'primary', 'container' => false, 'menu_class' => 'hx-menu__list', 'depth' => 2, 'fallback_cb' => false ) );
	}
	echo function_exists( 'hx_social_icons' ) ? hx_social_icons() : '';
	echo function_exists( 'hx_menu_switchers' ) ? hx_menu_switchers() : '';
	?>
</nav>
<main class="hx-main" id="main">
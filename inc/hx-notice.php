<?php
/**
 * Store notice bar (Customize > Header > Store notice bar).
 *
 * The notice text, link and colours are set in the Customizer (no Elementor template).
 *
 * @package hx-store
 */

defined( 'ABSPATH' ) || exit;

/** Default notice text: whatever the store showed before. */
function hx_notice_default_text() {
	return (string) get_option( 'hx_announcement', defined( 'HX_DEFAULT_ANNOUNCEMENT' ) ? HX_DEFAULT_ANNOUNCEMENT : '' );
}

/** The old Elementor "Store Notice" template is no longer used. */
function hx_notice_uses_elementor() {
	return false;
}

/** Markup of the notice (used by the original top bar and by the Header builder). */
function hx_notice_html() {
	if ( hx_notice_uses_elementor() ) {
		$id = (int) get_option( 'hx_notice_template' );
		$c  = \Elementor\Plugin::$instance->frontend->get_builder_content_for_display( $id, true );
		return '<div class="hx-notice">' . ( function_exists( 'hx_notice_translate' ) ? hx_notice_translate( $c, $id ) : $c ) . '</div>';
	}
	$text = trim( (string) get_theme_mod( 'hx_ntc_text', hx_notice_default_text() ) );
	if ( '' === $text ) {
		return is_customize_preview() ? '<div class="hx-announce" dir="auto"></div>' : '';
	}
	$link = trim( (string) get_theme_mod( 'hx_ntc_link', home_url( '/' ) ) );
	if ( '' !== $link ) {
		return '<a class="hx-announce" href="' . esc_url( $link ) . '" dir="auto">' . esc_html( $text ) . '</a>';
	}
	return '<div class="hx-announce" dir="auto">' . esc_html( $text ) . '</div>';
}

add_action(
	'customize_register',
	function ( $wp_customize ) {
		$sec = 'hx_hdr_notice';
		$wp_customize->add_setting( 'hx_ntc_text', array( 'default' => hx_notice_default_text(), 'sanitize_callback' => 'sanitize_text_field', 'transport' => 'postMessage' ) );
		$wp_customize->add_control( 'hx_ntc_text', array( 'label' => 'Notice text', 'description' => 'Emojis work too, e.g. 🚚. Leave empty to hide the notice.', 'section' => $sec, 'type' => 'text', 'priority' => 2 ) );
		$wp_customize->add_setting( 'hx_ntc_link', array( 'default' => home_url( '/' ), 'sanitize_callback' => 'esc_url_raw' ) );
		$wp_customize->add_control( 'hx_ntc_link', array( 'label' => 'Link (empty = no link)', 'section' => $sec, 'type' => 'url', 'priority' => 3 ) );
		$wp_customize->add_setting( 'hx_ntc_bg', array( 'default' => '', 'sanitize_callback' => 'sanitize_hex_color', 'transport' => 'postMessage' ) );
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'hx_ntc_bg', array( 'label' => 'Background colour (empty = main colour)', 'section' => $sec, 'priority' => 4 ) ) );
		$wp_customize->add_setting( 'hx_ntc_fg', array( 'default' => '#ffffff', 'sanitize_callback' => 'sanitize_hex_color', 'transport' => 'postMessage' ) );
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'hx_ntc_fg', array( 'label' => 'Text colour', 'section' => $sec, 'priority' => 5 ) ) );
		$wp_customize->add_setting( 'hx_ntc_h', array( 'default' => 67, 'sanitize_callback' => 'absint' ) );
		$wp_customize->add_control( 'hx_ntc_h', array( 'label' => 'Bar height - desktop (px)', 'section' => $sec, 'type' => 'number', 'priority' => 6, 'input_attrs' => array( 'min' => 30, 'max' => 160 ) ) );
		$wp_customize->add_setting( 'hx_ntc_h_m', array( 'default' => 67, 'sanitize_callback' => 'absint' ) );
		$wp_customize->add_control( 'hx_ntc_h_m', array( 'label' => 'Bar height - mobile (px)', 'section' => $sec, 'type' => 'number', 'priority' => 7, 'input_attrs' => array( 'min' => 30, 'max' => 140 ) ) );
	},
	30
);

/* Section description and clearer size labels. */
add_action(
	'customize_register',
	function ( $wp_customize ) {
		$s = $wp_customize->get_section( 'hx_hdr_notice' );
		if ( $s ) {
			$s->description = 'The text shown in the top bar. When the notice is placed with the Header builder, the row colours are used instead of the colours here.';
		}
		foreach ( array( 'hx_text_fs' => 'Notice text size - desktop (px, 0 = automatic)', 'hx_text_fs_m' => 'Notice text size - mobile (px, 0 = automatic)' ) as $id => $label ) {
			$c = $wp_customize->get_control( $id );
			if ( $c ) {
				$c->label = $label;
			}
		}
	},
	1002
);

/* Look of the notice in the original top bar. Sizes set in "Notice text size" still win (they use !important). */
add_action(
	'wp_head',
	function () {
		if ( hx_notice_uses_elementor() ) {
			return;
		}
		$bg  = sanitize_hex_color( get_theme_mod( 'hx_ntc_bg', '' ) );
		$fg  = sanitize_hex_color( get_theme_mod( 'hx_ntc_fg', '#ffffff' ) );
		$h   = max( 30, min( 160, (int) get_theme_mod( 'hx_ntc_h', 67 ) ) );
		$hm  = max( 30, min( 140, (int) get_theme_mod( 'hx_ntc_h_m', 67 ) ) );
		$css = '.hx-topbar .hx-announce{display:flex;align-items:center;justify-content:center;box-sizing:border-box;min-height:' . $h . 'px;background:' . ( $bg ? $bg : 'var(--hx-primary)' ) . ';color:' . ( $fg ? $fg : '#fff' ) . ';font-family:"Cairo",var(--hx-font);font-size:22px;font-weight:700;line-height:1.4;letter-spacing:0;text-align:center;text-decoration:none;}'
			. '.hx-topbar a.hx-announce:hover{color:' . ( $fg ? $fg : '#fff' ) . ';opacity:.92;}'
			. '@media (max-width:749px){.hx-topbar .hx-announce{min-height:' . $hm . 'px;font-size:16px;line-height:1.6;}}';
		echo '<style id="hx-notice">' . $css . '</style>' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput
	},
	101
);

/* Live preview while typing or picking colours. */
add_action(
	'customize_preview_init',
	function () {
		wp_add_inline_script(
			'customize-preview',
			"( function ( api ) {
				function all( sel, fn ) { document.querySelectorAll( sel ).forEach( fn ); }
				api( 'hx_ntc_text', function ( v ) { v.bind( function ( t ) { all( '.hx-announce', function ( a ) { a.textContent = t; } ); } ); } );
				api( 'hx_ntc_bg', function ( v ) { v.bind( function ( c ) { all( '.hx-topbar .hx-announce', function ( a ) { a.style.background = c || ''; } ); } ); } );
				api( 'hx_ntc_fg', function ( v ) { v.bind( function ( c ) { all( '.hx-topbar .hx-announce', function ( a ) { a.style.color = c || ''; } ); } ); } );
			}( wp.customize ) );"
		);
	}
);

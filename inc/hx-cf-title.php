<?php
/**
 * Form title look (Customize > COD Form > Text): font, weight, mobile size and alignment.
 * The title text, colour and size are the existing "Form title" settings in the same section.
 *
 * @package hx-store
 */

defined( 'ABSPATH' ) || exit;

function hx_cf_title_fonts() {
	return array(
		''        => array( 'Same as the form', '' ),
		'cairo'   => array( 'Cairo', '"Cairo", var(--hx-font)' ),
		'tahoma'  => array( 'Tahoma / Segoe UI', 'Tahoma, "Segoe UI", Arial, sans-serif' ),
		'system'  => array( 'Phone / computer font', 'system-ui, -apple-system, "Segoe UI", Roboto, sans-serif' ),
	);
}

add_action(
	'customize_register',
	function ( $wp_customize ) {
		$sec   = 'hx_cf_text';
		$fonts = array();
		foreach ( hx_cf_title_fonts() as $k => $f ) {
			$fonts[ $k ] = $f[0];
		}
		$sel = function ( $id, $label, $choices, $prio ) use ( $wp_customize, $sec ) {
			$wp_customize->add_setting( $id, array( 'default' => '', 'sanitize_callback' => function ( $v ) use ( $choices ) { return array_key_exists( (string) $v, $choices ) ? (string) $v : ''; } ) );
			$wp_customize->add_control( $id, array( 'label' => $label, 'section' => $sec, 'type' => 'select', 'choices' => $choices, 'priority' => $prio ) );
		};
		$sel( 'hx_cf_title_font', 'Form title font', $fonts, 3 );
		$sel( 'hx_cf_title_weight', 'Form title thickness', array( '' => 'Default (extra bold)', '600' => 'Semi bold', '700' => 'Bold', '800' => 'Extra bold' ), 3 );
		$sel( 'hx_cf_title_align', 'Form title alignment', array( '' => 'Center (default)', 'right' => 'Right', 'left' => 'Left' ), 3 );
		$wp_customize->add_setting( 'hx_cf_title_size_m', array( 'default' => '', 'sanitize_callback' => function ( $v ) { return '' === $v ? '' : (string) max( 12, min( 40, (int) $v ) ); } ) );
		$wp_customize->add_control( 'hx_cf_title_size_m', array( 'label' => 'Form title size - mobile (px, empty = automatic)', 'section' => $sec, 'type' => 'number', 'priority' => 3, 'input_attrs' => array( 'min' => 12, 'max' => 40 ) ) );
	},
	40
);

/* Design-tab controls for the Customizer tabs (font, thickness and alignment are looks, not content). */
add_filter(
	'hx_cui_design_ids',
	function ( $ids ) {
		return array_merge( (array) $ids, array( 'hx_cf_title_font', 'hx_cf_title_weight', 'hx_cf_title_align' ) );
	}
);

add_action(
	'wp_head',
	function () {
		if ( ! ( function_exists( 'is_product' ) && is_product() ) ) {
			return;
		}
		$sel   = 'body.single-product .hx-order .hx-order__title';
		$fonts = hx_cf_title_fonts();
		$f     = (string) get_theme_mod( 'hx_cf_title_font', '' );
		$w     = (string) get_theme_mod( 'hx_cf_title_weight', '' );
		$a     = (string) get_theme_mod( 'hx_cf_title_align', '' );
		$m     = (string) get_theme_mod( 'hx_cf_title_size_m', '' );
		$css   = '';
		if ( isset( $fonts[ $f ] ) && '' !== $fonts[ $f ][1] ) {
			$css .= 'font-family:' . $fonts[ $f ][1] . ';';
		}
		if ( in_array( $w, array( '600', '700', '800' ), true ) ) {
			$css .= 'font-weight:' . $w . ';';
		}
		if ( in_array( $a, array( 'right', 'left' ), true ) ) {
			$css .= 'text-align:' . $a . ';';
		}
		$out = $css ? $sel . '{' . $css . '}' : '';
		if ( '' !== $m ) {
			$out .= '@media (max-width:749px){' . $sel . '{font-size:' . max( 12, min( 40, (int) $m ) ) . 'px;}}';
		}
		if ( in_array( $a, array( 'right', 'left' ), true ) ) {
			$out .= 'body.single-product .hx-order .hx-order__head{text-align:' . $a . ';}';
		}
		if ( $out ) {
			echo '<style id="hx-cf-title">' . $out . '</style>' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput
		}
	},
	121
);

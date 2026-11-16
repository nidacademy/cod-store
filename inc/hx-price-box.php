<?php
/**
 * Price box on the product page (Customize > Product page > Price):
 * price, crossed-out price, discount badge, "you save" line and a countdown to a real end date.
 *
 * Countdown end date: the product's own sale end date (WooCommerce > Product > Sale price > Schedule),
 * otherwise the store-wide end date set in the Customizer. Hidden when there is no future date.
 *
 * @package hx-store
 */

defined( 'ABSPATH' ) || exit;

/** Timestamp the countdown runs to, or 0. */
function hx_pb_end_ts( $product ) {
	$now = time();
	$ts  = 0;
	if ( $product ) {
		$ids = $product->is_type( 'variable' ) ? $product->get_children() : array( $product->get_id() );
		foreach ( $ids as $id ) {
			$p = $id === $product->get_id() ? $product : wc_get_product( $id );
			$d = $p ? $p->get_date_on_sale_to() : null;
			if ( $d && $d->getTimestamp() > $now && ( ! $ts || $d->getTimestamp() < $ts ) ) {
				$ts = $d->getTimestamp();
			}
		}
	}
	if ( ! $ts ) {
		$g = trim( (string) get_theme_mod( 'hx_pb_end', '' ) );
		if ( '' !== $g ) {
			try {
				$dt = new DateTime( str_replace( 'T', ' ', $g ), wp_timezone() );
				if ( $dt->getTimestamp() > $now ) {
					$ts = $dt->getTimestamp();
				}
			} catch ( Exception $e ) {
				$ts = 0;
			}
		}
	}
	return $ts;
}

/** Print the price box (or the plain price when "Simple" is chosen). */
function hx_render_price_box( $product, $price, $compare ) {
	$pp = '<div class="hx-pp" dir="rtl" data-hx-pp>' . hx_price_block( $price, $compare ) . '</div>';
	if ( function_exists( 'hx_rv_stars_line' ) ) {
		ob_start();
		hx_rv_stars_line( $product );
		$hx_st = ob_get_clean();
		if ( $hx_st ) {
			$pp = '<div class="hx-pbox__row">' . $pp . $hx_st . '</div>';
		}
	}
	if ( 'simple' === get_theme_mod( 'hx_pb_style', 'urgent' ) ) {
		echo $pp; // phpcs:ignore WordPress.Security.EscapeOutput
		return;
	}
	$save = ( $compare > $price && $compare > 0 ) ? round( $compare - $price, 2 ) : 0;
	$txt  = str_replace( '%s', '{amount}', (string) get_theme_mod( 'hx_pb_save_txt', 'وفّر {amount} اليوم' ) );
	if ( false === strpos( $txt, '{amount}' ) ) {
		$txt .= ' {amount}';
	}
	$parts = explode( '{amount}', $txt, 2 );
	$amt   = '<b><span class="hx-m notranslate" data-mad="' . esc_attr( $save ) . '" data-hx-save-amt>' . esc_html( hx_num( $save ) ) . '</span> ' . hx_c() . '</b>';
	$icon  = '<svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><path fill="currentColor" d="M21.4 11.6 12.4 2.6A2 2 0 0 0 11 2H4a2 2 0 0 0-2 2v7c0 .55.22 1.05.59 1.42l9 9a2 2 0 0 0 2.82 0l7-7a2 2 0 0 0 0-2.82zM6.5 8A1.5 1.5 0 1 1 6.5 5a1.5 1.5 0 0 1 0 3z"/></svg>';

	echo '<div class="hx-pbox' . ( 'urgent' === get_theme_mod( 'hx_pb_style', 'compact' ) ? '' : ' hx-pbox--compact' ) . '" dir="rtl" data-hx-pbox>';
	echo $pp; // phpcs:ignore WordPress.Security.EscapeOutput
	if ( 'hide' !== get_theme_mod( 'hx_pb_save', '' ) ) {
		echo '<div class="hx-pbox__save" data-hx-save' . ( $save > 0.5 ? '' : ' hidden' ) . '>' . $icon . '<span>' . esc_html( trim( $parts[0] ) ) . ' ' . $amt . ' ' . esc_html( trim( $parts[1] ) ) . '</span></div>'; // phpcs:ignore WordPress.Security.EscapeOutput
	}
	$end = hx_pb_end_ts( $product );
	if ( $end && 'hide' !== get_theme_mod( 'hx_pb_cd', '' ) ) {
		$units = array( 'd' => 'يوم', 'h' => 'ساعة', 'm' => 'دقيقة', 's' => 'ثانية' );
		echo '<div class="hx-pbox__cd" data-hx-cd="' . (int) $end . '">';
		echo '<span class="hx-pbox__cdl"><svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><circle cx="12" cy="13" r="8"/><path d="M12 9v4l2.5 2M9 2h6"/></svg>' . esc_html( (string) get_theme_mod( 'hx_pb_cd_txt', 'ينتهي العرض خلال' ) ) . '</span>';
		echo '<span class="hx-pbox__t">';
		foreach ( $units as $k => $l ) {
			echo '<span class="hx-pbox__u" data-u="' . $k . '"><b class="notranslate">00</b><small>' . esc_html( $l ) . '</small></span>'; // phpcs:ignore WordPress.Security.EscapeOutput
		}
		echo '</span></div>';
	}
	echo '</div>';
}

add_action(
	'customize_register',
	function ( $wp_customize ) {
		$sec = $wp_customize->get_section( 'hx_pp_price' ) ? 'hx_pp_price' : 'hx_pp_layout';
		$sel = function ( $id, $label, $choices, $prio, $def = '' ) use ( $wp_customize, $sec ) {
			$wp_customize->add_setting( $id, array( 'default' => $def, 'sanitize_callback' => function ( $v ) use ( $choices, $def ) { return array_key_exists( (string) $v, $choices ) ? (string) $v : $def; } ) );
			$wp_customize->add_control( $id, array( 'label' => $label, 'section' => $sec, 'type' => 'select', 'choices' => $choices, 'priority' => $prio ) );
		};
		$sel( 'hx_pb_style', 'Price design', array( 'compact' => 'Clean & compact', 'urgent' => 'Box with savings and countdown', 'simple' => 'Simple (price only)' ), 1, 'compact' );
		$sel( 'hx_pb_save', '"You save" line', array( '' => 'Show (default)', 'hide' => 'Hide' ), 2 );
		$wp_customize->add_setting( 'hx_pb_save_txt', array( 'default' => 'وفّر {amount} اليوم', 'sanitize_callback' => 'sanitize_text_field' ) );
		$wp_customize->add_control( 'hx_pb_save_txt', array( 'label' => '"You save" text', 'description' => '{amount} is replaced by the amount, e.g. وفّر {amount} اليوم', 'section' => $sec, 'type' => 'text', 'priority' => 3 ) );
		$sel( 'hx_pb_cd', 'Countdown', array( '' => 'Show when there is an end date (default)', 'hide' => 'Hide' ), 4 );
		$wp_customize->add_setting( 'hx_pb_cd_txt', array( 'default' => 'ينتهي العرض خلال', 'sanitize_callback' => 'sanitize_text_field' ) );
		$wp_customize->add_control( 'hx_pb_cd_txt', array( 'label' => 'Countdown text', 'section' => $sec, 'type' => 'text', 'priority' => 5 ) );
		$wp_customize->add_setting(
			'hx_pb_end',
			array(
				'default'           => '',
				'sanitize_callback' => function ( $v ) {
					$v = trim( (string) $v );
					return preg_match( '/^\d{4}-\d{2}-\d{2}([T ]\d{2}:\d{2})?$/', $v ) ? $v : '';
				},
			)
		);
		$wp_customize->add_control(
			'hx_pb_end',
			array(
				'label'       => 'Offer end date (all products)',
				'description' => 'The countdown runs to this date and disappears after it. A product with its own sale end date (Products > Edit > Sale price > Schedule) uses that date instead. Empty = no countdown.',
				'section'     => $sec,
				'type'        => 'datetime-local',
				'priority'    => 6,
			)
		);
	},
	40
);

add_filter(
	'hx_cui_design_ids',
	function ( $ids ) {
		return array_merge( (array) $ids, array( 'hx_pb_style' ) );
	}
);

add_action(
	'wp_enqueue_scripts',
	function () {
		if ( ! ( function_exists( 'is_product' ) && is_product() ) || 'simple' === get_theme_mod( 'hx_pb_style', 'urgent' ) ) {
			return;
		}
		wp_enqueue_style( 'hx-price-box', get_theme_file_uri( 'assets/hx-price-box.css' ), array( 'hx' ), defined( 'HX_VER' ) ? HX_VER : '1' );
		wp_enqueue_script( 'hx-price-box', get_theme_file_uri( 'assets/hx-price-box.js' ), array(), defined( 'HX_VER' ) ? HX_VER : '1', true );
	},
	25
);

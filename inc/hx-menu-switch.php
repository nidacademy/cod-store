<?php
/**
 * Language + currency inside the hamburger menu (phones and desktop):
 * two compact rows showing the current choice; tapping a row opens its list.
 * Language options drive GTranslate; currency options use assets/hx-currency.js.
 */
defined( 'ABSPATH' ) || exit;

function hx_menu_switchers() {
	$langs = array();
	$def   = 'ar';
	if ( shortcode_exists( 'gtranslate' ) ) {
		$o     = (array) get_option( 'GTranslate', array() );
		$langs = ! empty( $o['incl_langs'] ) ? (array) $o['incl_langs'] : array( 'ar', 'en', 'fr' );
		$def   = ! empty( $o['default_language'] ) ? $o['default_language'] : 'ar';
		if ( ! in_array( $def, $langs, true ) ) {
			array_unshift( $langs, $def );
		}
	}
	$curs = function_exists( 'hx_fx_enabled' ) && hx_fx_enabled() ? hx_fx_active_list() : array();
	if ( count( $langs ) < 2 && ! $curs ) {
		return '';
	}
	$names = array(
		'ar'    => 'العربية',
		'en'    => 'English',
		'fr'    => 'Français',
		'es'    => 'Español',
		'de'    => 'Deutsch',
		'it'    => 'Italiano',
		'pt'    => 'Português',
		'nl'    => 'Nederlands',
		'ru'    => 'Русский',
		'zh-CN' => '中文',
		'zh-TW' => '中文 (繁體)',
		'tr'    => 'Türkçe',
		'ja'    => '日本語',
		'ko'    => '한국어',
	);
	$flag  = plugins_url( 'gtranslate/flags/svg/' );
	$chev  = '<svg class="hx-msw__chev" viewBox="0 0 24 24" width="16" height="16" aria-hidden="true"><path d="M6 9l6 6 6-6" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
	$check = '<svg class="hx-msw__check" viewBox="0 0 24 24" width="16" height="16" aria-hidden="true"><path d="M5 12.5l4.5 4.5L19 7.5" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"/></svg>';
	$globe = '<svg class="hx-msw__ico" viewBox="0 0 24 24" width="20" height="20" aria-hidden="true"><g fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c2.5 2.6 3.8 5.6 3.8 9s-1.3 6.4-3.8 9M12 3C9.5 5.6 8.2 8.6 8.2 12s1.3 6.4 3.8 9"/></g></svg>';
	$coins = '<svg class="hx-msw__ico" viewBox="0 0 24 24" width="20" height="20" aria-hidden="true"><g fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="2.5" y="6" width="19" height="12" rx="2.5"/><circle cx="12" cy="12" r="2.6"/><path d="M6 9.5v.01M18 14.5v.01"/></g></svg>';

	$row = function ( $id, $ico, $label, $vflag, $vtxt ) use ( $chev ) {
		return '<button type="button" class="hx-msw__row" data-hx-msw-row aria-expanded="false" aria-controls="hx-msw-' . $id . '">' . $ico
			. '<span class="hx-msw__label notranslate" data-hx-msw-label="' . $id . '">' . esc_html( $label ) . '</span>'
			. '<span class="hx-msw__val notranslate"><img src="' . esc_url( $vflag ) . '" width="20" height="20" alt="" data-hx-msw-vflag><b data-hx-msw-vtxt>' . esc_html( $vtxt ) . '</b></span>' . $chev . '</button>';
	};

	$h = '<div class="hx-msw" data-hx-msw data-default="' . esc_attr( $def ) . '">';
	if ( count( $langs ) > 1 ) {
		$h .= '<div class="hx-msw__group" data-hx-msw-group="lang">' . $row( 'lang', $globe, 'اللغة', $flag . $def . '.svg', isset( $names[ $def ] ) ? $names[ $def ] : strtoupper( $def ) );
		$h .= '<div class="hx-msw__panel" id="hx-msw-lang" hidden><ul class="hx-msw__list">';
		foreach ( $langs as $l ) {
			$n  = isset( $names[ $l ] ) ? $names[ $l ] : strtoupper( $l );
			$h .= '<li><a href="#" class="hx-msw__opt" data-hx-mlang="' . esc_attr( $l ) . '" data-flag="' . esc_url( $flag . $l . '.svg' ) . '" data-txt="' . esc_attr( $n ) . '"><img src="' . esc_url( $flag . $l . '.svg' ) . '" width="22" height="22" alt="" loading="lazy"><span class="notranslate">' . esc_html( $n ) . '</span>' . $check . '</a></li>';
		}
		$h .= '</ul></div></div>';
	}
	if ( count( $curs ) > 1 ) {
		$h .= '<div class="hx-msw__group" data-hx-msw-group="cur">' . $row( 'cur', $coins, 'العملة', hx_fx_flag( 'MAD' ), 'MAD' );
		$h .= '<div class="hx-msw__panel" id="hx-msw-cur" hidden><ul class="hx-msw__list">';
		foreach ( $curs as $code => $c ) {
			$h .= '<li><a href="#" class="hx-msw__opt" data-hx-mcur="' . esc_attr( $code ) . '" data-flag="' . esc_url( hx_fx_flag( $code ) ) . '" data-txt="' . esc_attr( $code ) . '"><img src="' . esc_url( hx_fx_flag( $code ) ) . '" width="22" height="22" alt="" loading="lazy"><b class="notranslate">' . esc_html( $code ) . '</b><span>' . esc_html( $c[3] ) . '</span>' . $check . '</a></li>';
		}
		$h .= '</ul></div></div>';
	}
	return $h . '</div>';
}

add_action(
	'wp_enqueue_scripts',
	function () {
		wp_enqueue_script( 'hx-menu-switch', get_theme_file_uri( 'assets/hx-menu-switch.js' ), array(), HX_VER, true );
	},
	40
);

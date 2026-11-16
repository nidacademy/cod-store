<?php
/**
 * Arabic-only store. The multi-language feature was removed; these few helpers keep
 * the rest of the theme working exactly as before in Arabic.
 *
 * @package hx-store
 */

defined( 'ABSPATH' ) || exit;

function hx_lang() {
	return 'ar';
}
function hx_is_default_lang() {
	return true;
}
function hx_is_rtl() {
	return true;
}
function hx_t( $s ) {
	$fix = array( 'Shop' => 'المتجر' ); // WooCommerce's own English page title.
	return isset( $fix[ $s ] ) ? $fix[ $s ] : $s;
}
function hx_translate_html( $html ) {
	return $html;
}
function hx_tr_post( $post_id, $field ) {
	return null;
}
/* GTranslate language dropdown in the top bar (our own design; GTranslate does the translating). */
function hx_lang_switcher( $variant = 'bar' ) {
	if ( 'bar' !== $variant || ! shortcode_exists( 'gtranslate' ) ) {
		return '';
	}
	$o     = (array) get_option( 'GTranslate', array() );
	$langs = ! empty( $o['incl_langs'] ) ? (array) $o['incl_langs'] : array( 'ar', 'en', 'fr' );
	$def   = ! empty( $o['default_language'] ) ? $o['default_language'] : 'ar';
	if ( ! in_array( $def, $langs, true ) ) {
		array_unshift( $langs, $def );
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
		'hi'    => 'हिन्दी',
		'fa'    => 'فارسی',
		'ur'    => 'اردو',
		'iw'    => 'עברית',
		'pl'    => 'Polski',
		'sv'    => 'Svenska',
		'id'    => 'Bahasa Indonesia',
		'ms'    => 'Bahasa Melayu',
		'uk'    => 'Українська',
		'el'    => 'Ελληνικά',
		'ro'    => 'Română',
		'th'    => 'ไทย',
		'vi'    => 'Tiếng Việt',
	);
	$flag  = plugins_url( 'gtranslate/flags/svg/' );
	$chev  = '<svg class="hx-gt__chev" viewBox="0 0 24 24" width="14" height="14" aria-hidden="true"><path d="M6 9l6 6 6-6" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg>';
	$check = '<svg class="hx-gt__check" viewBox="0 0 24 24" width="16" height="16" aria-hidden="true"><path d="M5 12.5l4.5 4.5L19 7.5" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"/></svg>';

	$h  = '<div class="hx-gt notranslate" data-hx-gt data-default="' . esc_attr( $def ) . '">';
	$h .= '<button type="button" class="hx-gt__btn" data-hx-gt-btn aria-haspopup="true" aria-expanded="false" aria-label="Language / اللغة">';
	$h .= '<img class="hx-gt__flag" data-hx-gt-flag src="' . esc_url( $flag . $def . '.svg' ) . '" width="22" height="22" alt="">';
	$h .= '<span class="hx-gt__code" data-hx-gt-code>' . esc_html( strtoupper( substr( $def, 0, 2 ) ) ) . '</span>' . $chev . '</button>';
	$h .= '<div class="hx-gt__menu" data-hx-gt-menu hidden><div class="hx-gt__head">Language · اللغة</div><ul class="hx-gt__list">';
	foreach ( $langs as $l ) {
		$n  = isset( $names[ $l ] ) ? $names[ $l ] : strtoupper( $l );
		$h .= '<li><a href="#" class="hx-gt__opt" data-lang="' . esc_attr( $l ) . '"><img src="' . esc_url( $flag . $l . '.svg' ) . '" width="24" height="24" alt="" loading="lazy"><span dir="auto">' . esc_html( $n ) . '</span>' . $check . '</a></li>';
	}
	$h .= '</ul></div>';
	$h .= '<div class="hx-gt__engine" aria-hidden="true">' . do_shortcode( '[gtranslate]' ) . '</div>';
	$h .= '</div>';
	return $h;
}
function hx_notice_translate( $html, $template_id ) {
	return $html;
}
if ( ! function_exists( 'hx_tr_attr' ) ) {
	function hx_tr_attr( $text ) {
		return $text;
	}
}

/* <html lang="ar"> without dir, so the layout stays exactly as designed (product image on the left). */
add_filter(
	'language_attributes',
	function ( $out ) {
		return is_admin() ? $out : 'lang="ar"';
	},
	99
);
add_filter(
	'body_class',
	function ( $c ) {
		if ( ! is_admin() ) {
			$c[] = 'hx-lang-ar';
			$c[] = 'hx-rtl';
		}
		return $c;
	}
);

/* Old language addresses (/fr/…, /en/…) and ?hx_set_lang=… go to the same page (301). */
add_action(
	'init',
	function () {
		if ( is_admin() || wp_doing_ajax() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
			return;
		}
		$uri  = isset( $_SERVER['REQUEST_URI'] ) ? (string) wp_unslash( $_SERVER['REQUEST_URI'] ) : '/';
		$base = untrailingslashit( (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH ) );
		$path = $base && 0 === strpos( $uri, $base ) ? substr( $uri, strlen( $base ) ) : $uri;
		$new  = null;
		if ( preg_match( '#^/(fr|en|es|mr|de|it|pt)(?=/|\?|$)(.*)$#', $path, $m ) ) {
			$new = $m[2];
		}
		if ( false !== strpos( $uri, 'hx_set_lang=' ) ) {
			$new = remove_query_arg( 'hx_set_lang', null === $new ? $path : $new );
		}
		if ( null !== $new ) {
			$new = '' === $new || '?' === $new[0] ? '/' . $new : $new;
			wp_safe_redirect( home_url( $new ), 301 );
			exit;
		}
	},
	0
);

/* GTranslate: switch text to left-to-right for English, French, etc. (layout unchanged). */
add_action(
	'wp_enqueue_scripts',
	function () {
		if ( shortcode_exists( 'gtranslate' ) ) {
			wp_enqueue_script( 'hx-ltr', get_theme_file_uri( 'assets/hx-ltr.js' ), array(), HX_VER, true );
		}
	},
	30
);

<?php
/**
 * Currencies - display only (orders are still saved and paid in dirhams).
 * Appearance > Customize > Currency switcher: which currencies are offered, automatic or manual
 * rates, extra margin, rounding, and which currency each language gets.
 */
defined( 'ABSPATH' ) || exit;

/** A price number the currency switcher can convert (data-mad = amount in dirhams). */
function hx_m( $amount ) {
	return '<span class="hx-m notranslate" data-mad="' . esc_attr( round( (float) $amount, 2 ) ) . '">' . esc_html( hx_num( $amount ) ) . '</span>';
}

/** The currency word next to a price (becomes $ / € / ر.س ... when switched). */
function hx_c( $tag = 'span' ) {
	return '<' . $tag . ' class="hx-c">درهم</' . $tag . '>';
}

/**
 * All currencies: code => array( symbol on Arabic pages, symbol on other languages, decimals for "smart" rounding, name, flag, backup rate (DH for 1 unit) ).
 */
function hx_fx_list() {
	return array(
		'MAD' => array( 'درهم', 'DH', 0, 'درهم مغربي', 'ma', 1 ),
		'USD' => array( '$', '$', 0, 'دولار أمريكي', 'us', 10 ),
		'EUR' => array( '€', '€', 0, 'يورو', 'eu', 11 ),
		'SAR' => array( 'ر.س', 'SAR', 0, 'ريال سعودي', 'sa', 2.66 ),
		'AED' => array( 'د.إ', 'AED', 0, 'درهم إماراتي', 'ae', 2.72 ),
		'QAR' => array( 'ر.ق', 'QAR', 0, 'ريال قطري', 'qa', 2.74 ),
		'KWD' => array( 'د.ك', 'KWD', 2, 'دينار كويتي', 'kw', 32.5 ),
		'BHD' => array( 'د.ب', 'BHD', 2, 'دينار بحريني', 'bh', 26.5 ),
		'OMR' => array( 'ر.ع', 'OMR', 2, 'ريال عماني', 'om', 26 ),
	);
}

/** Customizer key of a currency's manual / backup rate (USD and EUR keep their old keys). */
function hx_fx_rate_key( $code ) {
	return 'USD' === $code ? 'hx_fx_usd' : ( 'EUR' === $code ? 'hx_fx_eur' : 'hx_fx_rate_' . strtolower( $code ) );
}

/** Currencies switched on in the Customizer (DH is always on). */
function hx_fx_active_list() {
	$out = array();
	foreach ( hx_fx_list() as $code => $c ) {
		if ( 'MAD' === $code || get_theme_mod( 'hx_fx_use_' . strtolower( $code ), true ) ) {
			$out[ $code ] = $c;
		}
	}
	return $out;
}

/** Flag image of a currency. */
function hx_fx_flag( $code ) {
	$l = hx_fx_list();
	return get_theme_file_uri( 'assets/flags/' . ( isset( $l[ $code ] ) ? $l[ $code ][4] : 'ma' ) . '.svg' );
}

function hx_fx_enabled() {
	return (bool) get_theme_mod( 'hx_fx_on', true );
}

/** Manual rates: 1 DH = x of each currency. */
function hx_fx_manual() {
	$out = array();
	foreach ( hx_fx_list() as $code => $c ) {
		if ( 'MAD' === $code ) {
			continue;
		}
		$dh           = (float) str_replace( ',', '.', (string) get_theme_mod( hx_fx_rate_key( $code ), $c[5] ) );
		$out[ $code ] = $dh > 0 ? 1 / $dh : 1 / $c[5];
	}
	return $out;
}

/** Fetch today's rates (1 DH = x of each currency). Returns null on failure. */
function hx_fx_fetch() {
	$res = wp_remote_get( 'https://open.er-api.com/v6/latest/MAD', array( 'timeout' => 10 ) );
	if ( is_wp_error( $res ) || 200 !== wp_remote_retrieve_response_code( $res ) ) {
		return null;
	}
	$j   = json_decode( wp_remote_retrieve_body( $res ), true );
	$out = array();
	foreach ( array_keys( hx_fx_list() ) as $code ) {
		if ( 'MAD' !== $code && isset( $j['rates'][ $code ] ) && (float) $j['rates'][ $code ] > 0 ) {
			$out[ $code ] = (float) $j['rates'][ $code ];
		}
	}
	if ( empty( $out['USD'] ) || empty( $out['EUR'] ) ) {
		return null;
	}
	$out['time'] = time();
	return $out;
}

/** Refresh the saved rates (daily cron, and when the Customizer is saved). Purges the page cache when they change. */
function hx_fx_refresh() {
	$new = hx_fx_fetch();
	if ( ! $new ) {
		return;
	}
	$old     = get_option( 'hx_fx_rates' );
	$changed = ! is_array( $old );
	foreach ( $new as $k => $v ) {
		if ( 'time' !== $k && ( ! isset( $old[ $k ] ) || round( (float) $old[ $k ], 5 ) !== round( $v, 5 ) ) ) {
			$changed = true;
		}
	}
	update_option( 'hx_fx_rates', $new, false );
	if ( $changed ) {
		do_action( 'litespeed_purge_all' );
	}
}
add_action( 'hx_fx_refresh', 'hx_fx_refresh' );
add_action(
	'init',
	function () {
		if ( ! wp_next_scheduled( 'hx_fx_refresh' ) ) {
			wp_schedule_event( time() + 300, 'daily', 'hx_fx_refresh' );
		}
	}
);
add_action(
	'customize_save_after',
	function () {
		hx_fx_refresh();
		do_action( 'litespeed_purge_all' );
	}
);

/** Rates used on the site (1 DH = x), before the extra margin. */
function hx_fx_base_rates() {
	$manual = hx_fx_manual();
	if ( 'manual' === get_theme_mod( 'hx_fx_mode', 'auto' ) ) {
		return $manual;
	}
	$auto  = get_option( 'hx_fx_rates' );
	$fresh = is_array( $auto ) && ! empty( $auto['time'] ) && time() - (int) $auto['time'] < 3 * DAY_IN_SECONDS;
	$out   = array();
	foreach ( $manual as $code => $r ) {
		$out[ $code ] = ( $fresh && ! empty( $auto[ $code ] ) ) ? (float) $auto[ $code ] : $r;
	}
	return $out;
}

/** Rates used on the site, with the extra margin. Only switched-on currencies. */
function hx_fx_rates() {
	$m   = 1 + max( -50, min( 100, (float) str_replace( ',', '.', (string) get_theme_mod( 'hx_fx_margin', 0 ) ) ) ) / 100;
	$out = array();
	$on  = hx_fx_active_list();
	foreach ( hx_fx_base_rates() as $code => $r ) {
		if ( isset( $on[ $code ] ) ) {
			$out[ $code ] = $r * $m;
		}
	}
	return $out;
}

/** Decimals shown for a currency (Rounding setting). */
function hx_fx_decimals( $code ) {
	$mode = get_theme_mod( 'hx_fx_round', 'smart' );
	if ( 'whole' === $mode ) {
		return 0;
	}
	if ( '2' === (string) $mode ) {
		return 2;
	}
	$l = hx_fx_list();
	return isset( $l[ $code ] ) ? (int) $l[ $code ][2] : 0;
}

/** Languages offered by GTranslate. */
function hx_fx_languages() {
	$o     = (array) get_option( 'GTranslate', array() );
	$langs = ! empty( $o['incl_langs'] ) ? (array) $o['incl_langs'] : array( 'ar', 'en', 'fr' );
	return array_values( array_unique( array_merge( array( 'ar' ), $langs ) ) );
}

/** Default currency for a language. */
function hx_fx_lang_default( $lang ) {
	$l = strtolower( substr( $lang, 0, 2 ) );
	if ( 'ar' === $l ) {
		return 'MAD';
	}
	return in_array( $l, array( 'fr', 'es', 'de', 'it', 'pt', 'nl', 'el', 'fi', 'sk', 'sl', 'et', 'lv', 'lt', 'ga', 'mt', 'hr' ), true ) ? 'EUR' : 'USD';
}

/** Switcher markup (sits next to the language button; hidden on phones). Flag + code + name per row. */
function hx_cur_switcher() {
	if ( ! hx_fx_enabled() ) {
		return '';
	}
	$list = hx_fx_active_list();
	if ( count( $list ) < 2 ) {
		return '';
	}
	$chev  = '<svg class="hx-cur__chev" viewBox="0 0 24 24" width="14" height="14" aria-hidden="true"><path d="M6 9l6 6 6-6" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg>';
	$check = '<svg class="hx-cur__check" viewBox="0 0 24 24" width="16" height="16" aria-hidden="true"><path d="M5 12.5l4.5 4.5L19 7.5" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"/></svg>';
	$h     = '<div class="hx-cur" data-hx-cur>';
	$h    .= '<button type="button" class="hx-cur__btn notranslate" data-hx-cur-btn aria-haspopup="true" aria-expanded="false" aria-label="Currency / العملة">';
	$h    .= '<img class="hx-cur__bflag" data-hx-cur-flag src="' . esc_url( hx_fx_flag( 'MAD' ) ) . '" width="22" height="22" alt="">';
	$h    .= '<span class="hx-cur__sym" data-hx-cur-sym>MAD</span>' . $chev . '</button>';
	$h    .= '<div class="hx-cur__menu" data-hx-cur-menu hidden><div class="hx-cur__head notranslate">Currency · العملة</div><ul class="hx-cur__list">';
	foreach ( $list as $code => $c ) {
		$h .= '<li><a href="#" class="hx-cur__opt' . ( 'MAD' === $code ? ' is-current' : '' ) . '" data-cur="' . esc_attr( $code ) . '" data-flag="' . esc_url( hx_fx_flag( $code ) ) . '">';
		$h .= '<img class="hx-cur__flag" src="' . esc_url( hx_fx_flag( $code ) ) . '" width="24" height="24" alt="" loading="lazy">';
		$h .= '<b class="hx-cur__code notranslate">' . esc_html( $code ) . '</b>';
		$h .= '<span class="hx-cur__name">' . esc_html( $c[3] ) . '</span>' . $check . '</a></li>';
	}
	$h .= '</ul></div></div>';
	return $h;
}

add_action(
	'wp_enqueue_scripts',
	function () {
		if ( ! hx_fx_enabled() ) {
			return;
		}
		wp_enqueue_script( 'hx-currency', get_theme_file_uri( 'assets/hx-currency.js' ), array(), HX_VER, true );
		$list = array();
		foreach ( hx_fx_active_list() as $code => $c ) {
			$list[ $code ] = array(
				'ar'  => $c[0],
				'sym' => $c[1],
				'dec' => hx_fx_decimals( $code ),
			);
		}
		$langs = array();
		foreach ( hx_fx_languages() as $l ) {
			$langs[ strtolower( substr( $l, 0, 2 ) ) ] = (string) get_theme_mod( 'hx_fx_lang_' . str_replace( '-', '_', strtolower( $l ) ), hx_fx_lang_default( $l ) );
		}
		$cfg = array(
			'rates' => hx_fx_rates(),
			'cur'   => $list,
			'langs' => $langs,
		);
		wp_add_inline_script( 'hx-currency', 'window.HXFX=' . wp_json_encode( $cfg ) . ';', 'before' );
	},
	30
);

/* ---------- Customizer: Currency switcher ---------- */
add_action(
	'customize_register',
	function ( $wp_customize ) {
		$list = hx_fx_list();
		$auto = get_option( 'hx_fx_rates' );
		$base = hx_fx_base_rates();
		$mode = get_theme_mod( 'hx_fx_mode', 'auto' );

		/* Rates table */
		$tbl = '<table style="width:100%;border-collapse:collapse;margin:6px 0 4px;font-size:12px"><tr style="color:#646970"><th style="text-align:start;padding:3px 0">Currency</th><th style="text-align:end;padding:3px 0">1 unit = DH</th></tr>';
		foreach ( $base as $code => $r ) {
			$tbl .= '<tr style="border-top:1px solid #dcdcde"><td style="padding:3px 0"><strong>' . esc_html( $code ) . '</strong></td><td style="text-align:end;padding:3px 0">' . esc_html( number_format( 1 / $r, 2 ) ) . '</td></tr>';
		}
		$tbl .= '</table>';
		$when = is_array( $auto ) && ! empty( $auto['time'] ) ? wp_date( 'j M Y, H:i', (int) $auto['time'] ) : 'never';
		$src  = 'manual' === $mode ? 'Your manual rates (below).' : 'Automatic, updated once a day (last update: ' . esc_html( $when ) . '). Rates are also refreshed every time you click Publish.';

		$wp_customize->add_section(
			'hx_fx',
			array(
				'title'       => 'Currency switcher',
				'priority'    => 29,
				'description' => '<ul style="margin:0 0 8px 16px;list-style:disc"><li><strong>Desktop:</strong> currency button next to the language button.</li><li><strong>Menu (phones and desktop):</strong> Currency row in the hamburger menu.</li><li>Prices are converted <strong>for display only</strong> - orders are still in dirhams.</li></ul>'
					. '<p style="margin:0 0 2px"><strong>Rates in use now</strong> - ' . $src . '</p>' . $tbl,
			)
		);

		if ( ! class_exists( 'HX_FX_Heading_Control' ) ) {
			class HX_FX_Heading_Control extends WP_Customize_Control {
				public $type = 'hx_fx_heading';
				public function render_content() {
					echo '<span style="display:block;margin:10px 0 0;padding-top:14px;border-top:1px solid #c3c4c7;font-size:14px;font-weight:600;color:#1d2327">' . esc_html( $this->label ) . '</span>';
					if ( $this->description ) {
						echo '<span class="description customize-control-description" style="display:block;margin-top:4px">' . esc_html( $this->description ) . '</span>';
					}
				}
			}
		}
		$heading = function ( $id, $title, $text = '' ) use ( $wp_customize ) {
			$wp_customize->add_control( new HX_FX_Heading_Control( $wp_customize, $id, array( 'section' => 'hx_fx', 'label' => $title, 'description' => $text, 'settings' => array() ) ) );
		};
		$choice = function ( $id, $label, $choices, $default, $desc = '' ) use ( $wp_customize ) {
			$wp_customize->add_setting(
				$id,
				array(
					'default'           => $default,
					'sanitize_callback' => function ( $v ) use ( $choices, $default ) { return array_key_exists( (string) $v, $choices ) ? (string) $v : $default; },
				)
			);
			$wp_customize->add_control( $id, array( 'label' => $label, 'description' => $desc, 'section' => 'hx_fx', 'type' => 'select', 'choices' => $choices ) );
		};
		$decimal = function ( $v, $default, $min, $max ) {
			$v = str_replace( ',', '.', trim( (string) $v ) );
			if ( '' === $v || ! is_numeric( $v ) ) {
				return $default;
			}
			return (string) max( $min, min( $max, (float) $v ) );
		};

		/* 1. General */
		$wp_customize->add_setting( 'hx_fx_on', array( 'default' => true, 'sanitize_callback' => 'wp_validate_boolean' ) );
		$wp_customize->add_control( 'hx_fx_on', array( 'label' => 'Show currency choices (desktop button and menu row)', 'section' => 'hx_fx', 'type' => 'checkbox' ) );

		/* 2. Currencies offered */
		$heading( 'hx_fx_h_list', 'Currencies offered', 'Untick a currency to hide it everywhere. DH is always available.' );
		foreach ( $list as $code => $c ) {
			if ( 'MAD' === $code ) {
				continue;
			}
			$id = 'hx_fx_use_' . strtolower( $code );
			$wp_customize->add_setting( $id, array( 'default' => true, 'sanitize_callback' => 'wp_validate_boolean' ) );
			$wp_customize->add_control( $id, array( 'label' => $code . ' - ' . $c[3], 'section' => 'hx_fx', 'type' => 'checkbox' ) );
		}

		/* 3. Exchange rates */
		$heading( 'hx_fx_h_rates', 'Exchange rates' );
		$choice(
			'hx_fx_mode',
			'Rate source',
			array(
				'auto'   => 'Automatic - updated every day (recommended)',
				'manual' => 'Manual - use my rates below',
			),
			'auto',
			'Automatic: the manual rates below are only used if the daily update fails for 3 days.'
		);
		foreach ( $list as $code => $c ) {
			if ( 'MAD' === $code ) {
				continue;
			}
			$key  = hx_fx_rate_key( $code );
			$now  = ! empty( $auto[ $code ] ) ? number_format( 1 / $auto[ $code ], 2, '.', '' ) : '';
			$def  = $c[5];
			$wp_customize->add_setting(
				$key,
				array(
					'default'           => $def,
					'sanitize_callback' => function ( $v ) use ( $decimal, $def ) { return $decimal( $v, $def, 0.0001, 100000 ); },
				)
			);
			$wp_customize->add_control(
				$key,
				array(
					'label'       => '1 ' . $code . ' = ? DH',
					'description' => $now ? 'Today\'s automatic rate: ' . $now . ' DH' : '',
					'section'     => 'hx_fx',
					'type'        => 'text',
					'input_attrs' => array( 'inputmode' => 'decimal', 'placeholder' => $now ? $now : (string) $def, 'style' => 'max-width:140px' ),
				)
			);
		}

		/* 4. Price display */
		$heading( 'hx_fx_h_display', 'Converted prices' );
		$wp_customize->add_setting(
			'hx_fx_margin',
			array(
				'default'           => '0',
				'sanitize_callback' => function ( $v ) use ( $decimal ) { return $decimal( $v, '0', -50, 100 ); },
			)
		);
		$wp_customize->add_control(
			'hx_fx_margin',
			array(
				'label'       => 'Extra margin on converted prices (%)',
				'description' => 'e.g. 3 = converted prices are 3% higher, to cover exchange fees. 0 = exact conversion. DH prices never change.',
				'section'     => 'hx_fx',
				'type'        => 'text',
				'input_attrs' => array( 'inputmode' => 'decimal', 'placeholder' => '0', 'style' => 'max-width:100px' ),
			)
		);
		$choice(
			'hx_fx_round',
			'Rounding',
			array(
				'smart' => 'Smart - whole numbers, 2 decimals for KWD / BHD / OMR (recommended)',
				'whole' => 'Whole numbers for all (e.g. 10 $)',
				'2'     => '2 decimals for all (e.g. 9.97 $)',
			),
			'smart'
		);

		/* 5. Language -> currency */
		$heading( 'hx_fx_h_lang', 'Currency for each language', 'When a visitor picks a language (first-visit popup, language button or menu), the currency switches to the one chosen here. They can still change it afterwards.' );
		$names   = array(
			'ar' => 'العربية (Arabic)', 'en' => 'English', 'fr' => 'Français', 'es' => 'Español', 'de' => 'Deutsch', 'it' => 'Italiano',
			'pt' => 'Português', 'nl' => 'Nederlands', 'ru' => 'Русский', 'zh-cn' => '中文 (Chinese)',
		);
		$options = array();
		foreach ( $list as $code => $c ) {
			$options[ $code ] = $code . ' - ' . $c[3];
		}
		foreach ( hx_fx_languages() as $l ) {
			$lk = strtolower( $l );
			$choice( 'hx_fx_lang_' . str_replace( '-', '_', $lk ), isset( $names[ $lk ] ) ? $names[ $lk ] : strtoupper( $l ), $options, hx_fx_lang_default( $l ) );
		}
	}
);

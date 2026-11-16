<?php
/**
 * Header builder (Customize > Header > Header builder).
 *
 * Three rows (top, main, bottom), each with a left, center and right zone, set separately for
 * desktop and for tablet / mobile. Items are dragged in a builder shown under the preview.
 * While the layout is the original one, header.php keeps printing the original top bar.
 *
 * @package hx-store
 */

defined( 'ABSPATH' ) || exit;

/** Items: key => array( label, section opened by the gear button ('' = none), dashicon ). */
function hx_hb_items() {
	return array(
		'logo'     => array( 'Logo', 'title_tagline', 'format-image' ),
		'menu'     => array( 'Menu button', 'hx_header', 'menu' ),
		'links'    => array( 'Menu links', 'hx_hb_links', 'editor-ul' ),
		'search'   => array( 'Search', 'hx_hb_search', 'search' ),
		'notice'   => array( 'Store notice', 'hx_hdr_notice', 'megaphone' ),
		'lang'     => array( 'Language', '', 'translation' ),
		'currency' => array( 'Currency', 'hx_fx', 'money-alt' ),
		'social'   => array( 'Social icons', 'hx_social', 'share' ),
		'whatsapp' => array( 'WhatsApp', 'hx_hb_wa', 'whatsapp' ),
		'button'   => array( 'Button', 'hx_hb_btn', 'button' ),
		'html'     => array( 'HTML / text', 'hx_hb_html', 'editor-code' ),
	);
}

/** Items printed once and moved into the visible layout (they keep their state and scripts). */
function hx_hb_single() {
	return array( 'notice', 'lang', 'currency' );
}

/** Rows: key => array( label, background, text colour, height desktop, height mobile, side padding desktop, side padding mobile, line under ). */
function hx_hb_row_defs() {
	return array(
		'top'    => array( 'Top row', '', '', 36, 32, 20, 8, false ),
		'main'   => array( 'Main row', '', '', 64, 56, 20, 8, false ),
	);
}

/** The original header, as a layout. */
function hx_hb_default() {
	$e = array( array(), array(), array() );
	return array(
		'desktop' => array(
			'top'    => $e,
			'main'   => array( array( 'logo' ), array( 'notice' ), array( 'lang', 'currency', 'menu' ) ),
		),
		'mobile'  => array(
			'top'    => $e,
			'main'   => array( array( 'lang' ), array( 'notice' ), array( 'menu' ) ),
		),
	);
}

/** Clean a layout (JSON or array): known items only, each at most once per device. */
function hx_hb_normalize( $v ) {
	if ( is_string( $v ) ) {
		$v = json_decode( $v, true );
	}
	if ( ! is_array( $v ) || ! isset( $v['desktop'], $v['mobile'] ) ) {
		return hx_hb_default();
	}
	$items = hx_hb_items();
	$out   = array();
	foreach ( array( 'desktop', 'mobile' ) as $d ) {
		$seen = array();
		foreach ( array_keys( hx_hb_row_defs() ) as $r ) {
			$out[ $d ][ $r ] = array( array(), array(), array() );
			for ( $z = 0; $z < 3; $z++ ) {
				$list = isset( $v[ $d ][ $r ][ $z ] ) && is_array( $v[ $d ][ $r ][ $z ] ) ? $v[ $d ][ $r ][ $z ] : array();
				foreach ( $list as $k ) {
					$k = is_string( $k ) ? $k : '';
					if ( isset( $items[ $k ] ) && ! isset( $seen[ $k ] ) ) {
						$seen[ $k ]                = true;
						$out[ $d ][ $r ][ $z ][] = $k;
					}
				}
			}
		}
	}
	return $out;
}

function hx_hb_sanitize( $v ) {
	return wp_json_encode( hx_hb_normalize( $v ) );
}

function hx_hb_layout() {
	return hx_hb_normalize( get_theme_mod( 'hx_hb_layout', '' ) );
}

/** True when the builder layout differs from the original header. */
function hx_hb_active() {
	$raw = get_theme_mod( 'hx_hb_layout', '' );
	if ( ! is_string( $raw ) || '' === $raw ) {
		return false;
	}
	return hx_hb_layout() !== hx_hb_default();
}

function hx_hb_num( $key, $def, $min, $max ) {
	return max( $min, min( $max, (int) get_theme_mod( $key, $def ) ) );
}

/* ---------------------------------------------------------------- Customizer */

add_action(
	'customize_register',
	function ( $wp_customize ) {
		if ( ! class_exists( 'HX_HB_Control' ) ) {
			/** Short note + button; the builder itself is drawn under the preview by assets/hx-hb-customizer.js. */
			class HX_HB_Control extends WP_Customize_Control {
				public $type = 'hx_hb';
				public function render_content() {
					echo '<p class="description">Drag items between the rows at the bottom of the preview. The <span class="dashicons dashicons-admin-generic"></span> button opens an item\'s settings and the <span class="dashicons dashicons-no-alt"></span> button removes it. Desktop and tablet / mobile have their own layout.</p>';
					echo '<p><button type="button" class="button button-primary hx-hb-show">Show the header builder</button></p>';
					echo '<p class="description">Rows with no items are not shown. "Reset" in the builder brings back the original header.</p>';
				}
			}
		}

		// No section of its own: the builder opens under the preview as soon as the Header panel is opened.
		$wp_customize->add_setting(
			'hx_hb_layout',
			array(
				'default'           => '',
				'sanitize_callback' => 'hx_hb_sanitize',
				'transport'         => 'refresh',
			)
		);

		$color = function ( $id, $label, $section, $prio, $def = '' ) use ( $wp_customize ) {
			$wp_customize->add_setting( $id, array( 'default' => $def, 'sanitize_callback' => 'sanitize_hex_color' ) );
			$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, $id, array( 'label' => $label, 'section' => $section, 'priority' => $prio ) ) );
		};
		$number = function ( $id, $label, $section, $prio, $def, $min, $max ) use ( $wp_customize ) {
			$wp_customize->add_setting( $id, array( 'default' => $def, 'sanitize_callback' => 'absint' ) );
			$wp_customize->add_control( $id, array( 'label' => $label, 'section' => $section, 'type' => 'number', 'priority' => $prio, 'input_attrs' => array( 'min' => $min, 'max' => $max, 'step' => 1 ) ) );
		};
		$text = function ( $id, $label, $section, $prio, $def, $type = 'text', $desc = '' ) use ( $wp_customize ) {
			$wp_customize->add_setting( $id, array( 'default' => $def, 'sanitize_callback' => 'textarea' === $type ? 'wp_kses_post' : ( 'url' === $type ? 'esc_url_raw' : 'sanitize_text_field' ) ) );
			$wp_customize->add_control( $id, array( 'label' => $label, 'section' => $section, 'type' => $type, 'priority' => $prio, 'description' => $desc ) );
		};

		/* Rows */
		$i = 0;
		foreach ( hx_hb_row_defs() as $r => $d ) {
			$sec = 'hx_hb_row_' . $r;
			$wp_customize->add_section(
				$sec,
				array(
					'title'       => $d[0],
					'panel'       => 'hx_g_header',
					'priority'    => 60 + $i++,
					'description' => 'Used when this row has items in the Header builder.',
				)
			);
			$color( 'hx_hb_' . $r . '_bg', 'Background colour (empty = automatic)', $sec, 10, $d[1] );
			$color( 'hx_hb_' . $r . '_fg', 'Text and icon colour (empty = automatic)', $sec, 11, $d[2] );
			$number( 'hx_hb_' . $r . '_h', 'Row height - desktop (px)', $sec, 20, $d[3], 24, 200 );
			$number( 'hx_hb_' . $r . '_h_m', 'Row height - mobile (px)', $sec, 21, $d[4], 24, 200 );
			$number( 'hx_hb_' . $r . '_px', 'Side padding - desktop (px)', $sec, 22, $d[5], 0, 200 );
			$number( 'hx_hb_' . $r . '_px_m', 'Side padding - mobile (px)', $sec, 23, $d[6], 0, 80 );
			$wp_customize->add_setting( 'hx_hb_' . $r . '_line', array( 'default' => $d[7], 'sanitize_callback' => 'wp_validate_boolean' ) );
			$wp_customize->add_control( 'hx_hb_' . $r . '_line', array( 'label' => 'Thin line under this row', 'section' => $sec, 'type' => 'checkbox', 'priority' => 30 ) );
		}

		/* Menu links */
		$wp_customize->add_section( 'hx_hb_links', array( 'title' => 'Header: menu links', 'panel' => 'hx_g_header', 'priority' => 70, 'description' => 'Links shown in the header bar. Edit the links themselves in Appearance > Menus.' ) );
		$menus = array( 0 => 'Same as the hamburger menu' );
		foreach ( wp_get_nav_menus() as $m ) {
			$menus[ $m->term_id ] = $m->name;
		}
		$wp_customize->add_setting( 'hx_hb_links_menu', array( 'default' => 0, 'sanitize_callback' => 'absint' ) );
		$wp_customize->add_control( 'hx_hb_links_menu', array( 'label' => 'Menu', 'section' => 'hx_hb_links', 'type' => 'select', 'choices' => $menus, 'priority' => 10 ) );
		$color( 'hx_hb_links_c', 'Link colour (empty = row text colour)', 'hx_hb_links', 20 );
		$number( 'hx_hb_links_fs', 'Text size (px)', 'hx_hb_links', 21, 15, 10, 30 );
		$number( 'hx_hb_links_gap', 'Space between links (px)', 'hx_hb_links', 22, 22, 0, 80 );

		/* Search */
		$wp_customize->add_section( 'hx_hb_search', array( 'title' => 'Header: search', 'panel' => 'hx_g_header', 'priority' => 71 ) );
		$wp_customize->add_setting( 'hx_hb_search_style', array( 'default' => 'box', 'sanitize_callback' => 'sanitize_key' ) );
		$wp_customize->add_control( 'hx_hb_search_style', array( 'label' => 'Style', 'section' => 'hx_hb_search', 'type' => 'select', 'priority' => 10, 'choices' => array( 'box' => 'Search box', 'icon' => 'Icon (opens a box)' ) ) );
		$text( 'hx_hb_search_ph', 'Placeholder text', 'hx_hb_search', 11, 'ابحث عن منتج...' );
		$number( 'hx_hb_search_w', 'Box width - desktop (px)', 'hx_hb_search', 20, 240, 120, 600 );

		/* WhatsApp */
		$wp_customize->add_section( 'hx_hb_wa', array( 'title' => 'Header: WhatsApp button', 'panel' => 'hx_g_header', 'priority' => 72 ) );
		$text( 'hx_hb_wa_num', 'WhatsApp number', 'hx_hb_wa', 10, '', 'text', 'With country code, digits only. Empty = the number set in COD Form > WhatsApp.' );
		$text( 'hx_hb_wa_label', 'Button text (empty = icon only)', 'hx_hb_wa', 11, 'واتساب' );
		$text( 'hx_hb_wa_msg', 'Pre-filled message (optional)', 'hx_hb_wa', 12, '' );

		/* Button */
		$wp_customize->add_section( 'hx_hb_btn', array( 'title' => 'Header: button', 'panel' => 'hx_g_header', 'priority' => 73 ) );
		$text( 'hx_hb_btn_text', 'Button text', 'hx_hb_btn', 10, 'تسوق الآن' );
		$text( 'hx_hb_btn_url', 'Link (empty = shop page)', 'hx_hb_btn', 11, '', 'url' );
		$color( 'hx_hb_btn_bg', 'Background (empty = accent colour)', 'hx_hb_btn', 20 );
		$color( 'hx_hb_btn_fg', 'Text colour', 'hx_hb_btn', 21 );

		/* HTML */
		$wp_customize->add_section( 'hx_hb_html', array( 'title' => 'Header: HTML / text', 'panel' => 'hx_g_header', 'priority' => 74 ) );
		$text( 'hx_hb_html', 'Content', 'hx_hb_html', 10, '', 'textarea', 'Text, links, simple HTML or a shortcode.' );

		/* Social icons in the header */
		$number( 'hx_hb_soc_size', 'Icon size in the header (px)', 'hx_social', 200, 32, 20, 60 );
	},
	30
);

add_action(
	'customize_register',
	function ( $wp_customize ) {
		$s = $wp_customize->get_section( 'hx_header' );
		if ( $s ) {
			$s->description = trim( $s->description . ' When the Header builder is used, only the menu button size and the notice text size apply here.' );
		}
		$soc = $wp_customize->get_section( 'hx_social' );
		if ( $soc ) {
			$soc->title = 'Social icons';
		}
		$p = $wp_customize->get_panel( 'hx_g_header' );
		if ( $p ) {
			$p->description = 'Header builder (drag items into 3 rows), logo, store notice, menu button, social icons and currency button.';
		}
	},
	1001
);

/** Sections that keep the builder visible under the preview. */
function hx_hb_sections() {
	$s = array( 'hx_hb' );
	foreach ( array_keys( hx_hb_row_defs() ) as $r ) {
		$s[] = 'hx_hb_row_' . $r;
	}
	foreach ( hx_hb_items() as $it ) {
		if ( $it[1] ) {
			$s[] = $it[1];
		}
	}
	return array_values( array_unique( $s ) );
}

add_action(
	'customize_controls_enqueue_scripts',
	function () {
		$ver = defined( 'HX_VER' ) ? HX_VER : '1';
		wp_enqueue_style( 'hx-hb-customizer', get_theme_file_uri( 'assets/hx-hb-customizer.css' ), array( 'dashicons' ), $ver );
		wp_enqueue_script( 'hx-hb-customizer', get_theme_file_uri( 'assets/hx-hb-customizer.js' ), array( 'customize-controls', 'jquery-ui-sortable' ), $ver, true );
		$items = array();
		foreach ( hx_hb_items() as $k => $it ) {
			$items[ $k ] = array( 'label' => $it[0], 'sec' => $it[1], 'icon' => $it[2] );
		}
		$rows = array();
		foreach ( hx_hb_row_defs() as $r => $d ) {
			$rows[ $r ] = $d[0];
		}
		wp_localize_script(
			'hx-hb-customizer',
			'HXHB',
			array(
				'items' => $items,
				'order' => array_keys( $items ),
				'rows'  => $rows,
				'def'   => hx_hb_default(),
				'panel' => 'hx_g_header',
				'secs'  => hx_hb_sections(),
			)
		);
	}
);

/* ---------------------------------------------------------------- Front end */

/** Inline SVG icons. */
function hx_hb_svg( $name ) {
	if ( 'search' === $name ) {
		return '<svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.6-3.6"/></svg>';
	}
	$n = function_exists( 'hx_social_networks' ) ? hx_social_networks() : array();
	return '<svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true">' . ( isset( $n['whatsapp'][4] ) ? $n['whatsapp'][4] : '' ) . '</svg>';
}

/** Placeholder shown only in the Customizer preview for an item that has nothing to show yet. */
function hx_hb_ph( $text ) {
	return is_customize_preview() ? '<span class="hx-hb__ph">' . esc_html( $text ) . '</span>' : '';
}

/** Markup of one item (single items get an empty slot that the real element is moved into). */
function hx_hb_item_html( $k ) {
	if ( in_array( $k, hx_hb_single(), true ) ) {
		return '<div class="hx-hb__it hx-hb__it--' . $k . '" data-hx-hb-slot="' . $k . '"></div>';
	}
	$h = '';
	switch ( $k ) {
		case 'logo':
			$h = hx_logo( 'hx-logo' );
			$h = $h ? $h : hx_hb_ph( 'Logo: none set' );
			break;
		case 'menu':
			$h = '<button type="button" class="hx-burger" data-hx-menu-open aria-controls="hx-menu" aria-expanded="false" aria-label="القائمة"><span></span><span></span><span></span></button>';
			break;
		case 'links':
			$menu = (int) get_theme_mod( 'hx_hb_links_menu', 0 );
			$args = array(
				'container'   => false,
				'menu_class'  => 'hx-hb-links',
				'items_wrap'  => '<ul class="%2$s" dir="auto">%3$s</ul>',
				'depth'       => 1,
				'fallback_cb' => false,
				'echo'        => false,
			);
			if ( $menu ) {
				$args['menu'] = $menu;
			} elseif ( has_nav_menu( 'primary' ) ) {
				$args['theme_location'] = 'primary';
			} else {
				$args = null;
			}
			if ( $args ) {
				add_filter( 'nav_menu_item_id', '__return_empty_string' );
				$h = (string) wp_nav_menu( $args );
				remove_filter( 'nav_menu_item_id', '__return_empty_string' );
			}
			$h = $h ? $h : hx_hb_ph( 'Menu links: no menu yet' );
			break;
		case 'search':
			$ph   = (string) get_theme_mod( 'hx_hb_search_ph', 'ابحث عن منتج...' );
			$form = '<form class="hx-hb-search__f" role="search" method="get" action="' . esc_url( home_url( '/' ) ) . '">'
				. '<input class="hx-hb-search__in" type="search" name="s" dir="auto" placeholder="' . esc_attr( $ph ) . '" aria-label="' . esc_attr( $ph ) . '">'
				. '<input type="hidden" name="post_type" value="product">'
				. '<button class="hx-hb-search__go" type="submit" aria-label="بحث">' . hx_hb_svg( 'search' ) . '</button></form>';
			if ( 'icon' === get_theme_mod( 'hx_hb_search_style', 'box' ) ) {
				$h = '<details class="hx-hb-search hx-hb-search--icon"><summary class="hx-hb-search__ic" aria-label="بحث">' . hx_hb_svg( 'search' ) . '</summary>' . $form . '</details>';
			} else {
				$h = '<div class="hx-hb-search hx-hb-search--box">' . $form . '</div>';
			}
			break;
		case 'social':
			$h = function_exists( 'hx_social_icons' ) ? hx_social_icons() : '';
			$h = $h ? $h : hx_hb_ph( 'Social icons: add links' );
			break;
		case 'whatsapp':
			$num = preg_replace( '/\D/', '', (string) get_theme_mod( 'hx_hb_wa_num', '' ) );
			if ( '' === $num ) {
				$num = preg_replace( '/\D/', '', (string) ( function_exists( 'hx_pp_val' ) ? hx_pp_val( 'pp_wa_number' ) : get_theme_mod( 'pp_wa_number', '' ) ) );
			}
			if ( '' === $num ) {
				$h = hx_hb_ph( 'WhatsApp: add a number' );
				break;
			}
			$msg   = trim( (string) get_theme_mod( 'hx_hb_wa_msg', '' ) );
			$url   = 'https://wa.me/' . $num . ( '' !== $msg ? '?text=' . rawurlencode( $msg ) : '' );
			$label = trim( (string) get_theme_mod( 'hx_hb_wa_label', 'واتساب' ) );
			$h     = '<a class="hx-hb-wa' . ( '' === $label ? ' hx-hb-wa--icon' : '' ) . '" href="' . esc_url( $url ) . '" target="_blank" rel="noopener" aria-label="WhatsApp">' . hx_hb_svg( 'wa' ) . ( '' !== $label ? '<span dir="auto">' . esc_html( $label ) . '</span>' : '' ) . '</a>';
			break;
		case 'button':
			$t   = trim( (string) get_theme_mod( 'hx_hb_btn_text', 'تسوق الآن' ) );
			$url = trim( (string) get_theme_mod( 'hx_hb_btn_url', '' ) );
			if ( '' === $url ) {
				$url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' );
			}
			$h = '' !== $t ? '<a class="hx-hb-btn" href="' . esc_url( $url ) . '" dir="auto">' . esc_html( $t ) . '</a>' : hx_hb_ph( 'Button: add a text' );
			break;
		case 'html':
			$c = trim( (string) get_theme_mod( 'hx_hb_html', '' ) );
			$h = '' !== $c ? '<div class="hx-hb-html" dir="auto">' . do_shortcode( wp_kses_post( $c ) ) . '</div>' : hx_hb_ph( 'HTML / text: add content' );
			break;
	}
	return '' !== $h ? '<div class="hx-hb__it hx-hb__it--' . $k . '">' . $h . '</div>' : '';
}

/** The notice bar content (Elementor template or the plain announcement). */
function hx_hb_notice_html() {
	if ( function_exists( 'hx_notice_html' ) ) {
		return hx_notice_html();
	}
	$id = (int) get_option( 'hx_notice_template' );
	if ( $id && did_action( 'elementor/loaded' ) && 'publish' === get_post_status( $id ) ) {
		$c = \Elementor\Plugin::$instance->frontend->get_builder_content_for_display( $id, true );
		return '<div class="hx-notice">' . ( function_exists( 'hx_notice_translate' ) ? hx_notice_translate( $c, $id ) : $c ) . '</div>';
	}
	return '<a class="hx-announce" href="' . esc_url( home_url( '/' ) ) . '" dir="rtl">' . esc_html( get_option( 'hx_announcement', defined( 'HX_DEFAULT_ANNOUNCEMENT' ) ? HX_DEFAULT_ANNOUNCEMENT : '' ) ) . '</a>';
}

/** Print the builder header. */
function hx_hb_render() {
	$layout = hx_hb_layout();
	$rows   = hx_hb_row_defs();
	$used   = array();
	$auto   = array(
		'top'    => array( 'var(--hx-primary-dark)', '#fff' ),
		'main'   => array( 'var(--hx-primary)', '#fff' ),
		'bottom' => array( '#fff', 'var(--hx-ink)' ),
	);
	$logo_h = (int) max( 20, min( 70, get_theme_mod( 'hx_logo_height', 40 ) ) );
	$out    = '';
	foreach ( array( 'desktop' => 'd', 'mobile' => 'm' ) as $dev => $sfx ) {
		$m     = 'm' === $sfx ? '_m' : '';
		$style = '--hb-bs:' . ( function_exists( 'hx_hmod' ) ? hx_hmod( 'hx_burger_size' . $m ) : 42 ) . 'px;';
		$html  = '';
		foreach ( $layout[ $dev ] as $r => $zones ) {
			if ( ! $zones[0] && ! $zones[1] && ! $zones[2] ) {
				continue;
			}
			$d   = $rows[ $r ];
			$bg  = sanitize_hex_color( get_theme_mod( 'hx_hb_' . $r . '_bg', $d[1] ) );
			$fg  = sanitize_hex_color( get_theme_mod( 'hx_hb_' . $r . '_fg', $d[2] ) );
			$rs  = '--bg:' . ( $bg ? $bg : $auto[ $r ][0] ) . ';--fg:' . ( $fg ? $fg : $auto[ $r ][1] ) . ';';
			$rs .= '--h:' . hx_hb_num( 'hx_hb_' . $r . '_h' . $m, 'm' === $sfx ? $d[4] : $d[3], 24, 200 ) . 'px;';
			$rs .= '--px:' . hx_hb_num( 'hx_hb_' . $r . '_px' . $m, 'm' === $sfx ? $d[6] : $d[5], 0, 200 ) . 'px;';
			$cls = 'hx-hb__row hx-hb__row--' . $r . ( $zones[1] ? '' : ' hx-hb__row--nc' ) . ( get_theme_mod( 'hx_hb_' . $r . '_line', $d[7] ) ? ' hx-hb__row--line' : '' );
			$html .= '<div class="' . $cls . '" style="' . esc_attr( $rs ) . '">';
			foreach ( array( 'l', 'c', 'r' ) as $zi => $zn ) {
				if ( 'c' === $zn && ! $zones[1] ) {
					continue;
				}
				$html .= '<div class="hx-hb__z hx-hb__z--' . $zn . '">';
				foreach ( $zones[ $zi ] as $k ) {
					$used[ $k ] = true;
					$html      .= hx_hb_item_html( $k );
				}
				$html .= '</div>';
			}
			$html .= '</div>';
		}
		$out .= '<div class="hx-hb__layout hx-hb__layout--' . $sfx . '" style="' . esc_attr( $style ) . '">' . $html . '</div>';
	}

	// Single items: printed once, moved into the visible layout by the script below.
	// The language switcher is always printed (its translation engine is needed by the language pop-up).
	$hold = '<div class="hx-hb__n" data-hx-hb-node="lang">' . ( function_exists( 'hx_lang_switcher' ) ? hx_lang_switcher( 'bar' ) : '' ) . '</div>';
	if ( ! empty( $used['currency'] ) && function_exists( 'hx_cur_switcher' ) ) {
		$hold .= '<div class="hx-hb__n" data-hx-hb-node="currency">' . hx_cur_switcher() . '</div>';
	}
	if ( ! empty( $used['notice'] ) ) {
		$hold .= '<div class="hx-hb__n" data-hx-hb-node="notice">' . hx_hb_notice_html() . '</div>';
	}

	echo '<header class="hx-hb" id="hx-hb" style="--hx-logo-h:' . $logo_h . 'px">' . $out . '<div class="hx-hb__hold" hidden>' . $hold . '</div></header>' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput
	?>
<script>
(function () {
	var h = document.getElementById('hx-hb');
	if (!h) { return; }
	var mq = window.matchMedia('(min-width: 750px)'), hold = h.querySelector('.hx-hb__hold');
	function place() {
		var d = mq.matches ? 'd' : 'm';
		h.querySelectorAll('[data-hx-hb-node]').forEach(function (n) {
			var s = h.querySelector('.hx-hb__layout--' + d + ' [data-hx-hb-slot="' + n.getAttribute('data-hx-hb-node') + '"]') || hold;
			if (n.parentNode !== s) { s.appendChild(n); }
		});
	}
	place();
	if (mq.addEventListener) { mq.addEventListener('change', place); } else if (mq.addListener) { mq.addListener(place); }
	document.addEventListener('click', function (e) {
		h.querySelectorAll('details.hx-hb-search[open]').forEach(function (x) { if (!x.contains(e.target)) { x.removeAttribute('open'); } });
	});
})();
</script>
	<?php
}

/** Styles of the builder header (only when it is used). */
add_action(
	'wp_enqueue_scripts',
	function () {
		if ( ! hx_hb_active() ) {
			return;
		}
		wp_enqueue_style( 'hx-header-builder', get_theme_file_uri( 'assets/hx-header-builder.css' ), array(), defined( 'HX_VER' ) ? HX_VER : '1' );
		$css  = '.hx-hb{--hb-lc:' . ( sanitize_hex_color( get_theme_mod( 'hx_hb_links_c', '' ) ) ? sanitize_hex_color( get_theme_mod( 'hx_hb_links_c', '' ) ) : 'inherit' ) . ';';
		$css .= '--hb-lfs:' . hx_hb_num( 'hx_hb_links_fs', 15, 10, 30 ) . 'px;--hb-lg:' . hx_hb_num( 'hx_hb_links_gap', 22, 0, 80 ) . 'px;';
		$css .= '--hb-sw:' . hx_hb_num( 'hx_hb_search_w', 240, 120, 600 ) . 'px;--hb-soc:' . hx_hb_num( 'hx_hb_soc_size', 32, 20, 60 ) . 'px;';
		$bbg  = sanitize_hex_color( get_theme_mod( 'hx_hb_btn_bg', '' ) );
		$bfg  = sanitize_hex_color( get_theme_mod( 'hx_hb_btn_fg', '' ) );
		$css .= '--hb-bbg:' . ( $bbg ? $bbg : 'var(--hx-accent)' ) . ';--hb-bfg:' . ( $bfg ? $bfg : '#111827' ) . ';}';
		if ( function_exists( 'hx_hmod' ) ) {
			foreach ( array( 'd' => '', 'm' => '_m' ) as $sfx => $m ) {
				$fs = hx_hmod( 'hx_text_fs' . $m );
				if ( $fs ) {
					$css .= '.hx-hb__layout--' . $sfx . ' .hx-notice .elementor-heading-title,.hx-hb__layout--' . $sfx . ' .hx-announce{font-size:' . $fs . 'px !important;}';
				}
			}
		}
		wp_add_inline_style( 'hx-header-builder', $css );
	},
	30
);

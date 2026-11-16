<?php
/**
 * Customizer look & organisation (Appearance > Customize):
 * - theme sections grouped into panels (Header, Home & shop, Product page, COD Form...)
 * - sections and panels shown as cards
 * - General / Design tabs inside a section
 * - desktop / phone options follow the device buttons at the bottom
 * Only the Customizer screen changes: every setting keeps its ID and value.
 *
 * @package hx-store
 */

defined( 'ABSPATH' ) || exit;

/* ---------- Grouping ---------- */
add_action(
	'customize_register',
	function ( $wp_customize ) {
		$wp_customize->add_panel( 'hx_g_header', array( 'title' => 'Header', 'description' => 'Logo, top bar, store notice, social icons and currency button.', 'priority' => 20 ) );
		$wp_customize->add_panel( 'hx_g_home', array( 'title' => 'Home & shop', 'description' => 'Home page banner, product cards and which page is the home page.', 'priority' => 24 ) );

		// A separate card for the store notice options of "Header layout".
		$wp_customize->add_section( 'hx_hdr_notice', array( 'title' => 'Store notice bar', 'panel' => 'hx_g_header', 'priority' => 30, 'description' => 'The notice text itself is edited in Templates > Store Notice (Elementor).' ) );

		$sections = array(
			// id => array( panel, priority, new title or '' ).
			'title_tagline'     => array( 'hx_g_header', 10, 'Logo & site identity' ),
			'hx_header'         => array( 'hx_g_header', 20, 'Top bar layout' ),
			'hx_social'         => array( 'hx_g_header', 40, 'Social icons (menu)' ),
			'hx_fx'             => array( 'hx_g_header', 50, 'Currency switcher' ),
			'hx_hero'           => array( 'hx_g_home', 10, 'Hero banner' ),
			'hx_cards'          => array( 'hx_g_home', 20, 'Product cards' ),
			'static_front_page' => array( 'hx_g_home', 30, '' ),
			'hx_colors'         => array( '', 22, 'Colours' ),
			'hx_blog'           => array( '', 33, '' ),
			'hx_footer'         => array( '', 34, '' ),
		);
		foreach ( $sections as $id => $s ) {
			$sec = $wp_customize->get_section( $id );
			if ( ! $sec ) {
				continue;
			}
			$sec->panel    = $s[0];
			$sec->priority = $s[1];
			if ( $s[2] ) {
				$sec->title = $s[2];
			}
		}
		foreach ( array( 'hx_text_pl', 'hx_text_pl_m', 'hx_text_pr', 'hx_text_pr_m', 'hx_text_fs', 'hx_text_fs_m' ) as $i => $id ) {
			$c = $wp_customize->get_control( $id );
			if ( $c ) {
				$c->section  = 'hx_hdr_notice';
				$c->priority = $i + 1;
			}
		}
		// WhatsApp message templates (their old section no longer exists).
		foreach ( array( 'hx_wa_tpl_ar' => 10, 'hx_wa_tpl' => 11 ) as $id => $p ) {
			$c = $wp_customize->get_control( $id );
			if ( $c ) {
				$c->section  = 'hx_cf_wa';
				$c->priority = $p;
			}
		}
	},
	1000
);

/* ---------- Tabs and devices for each control ---------- */

/** Sections handled by the new look. */
function hx_cui_section_ok( $id ) {
	return 0 === strpos( $id, 'hx_' ) || in_array( $id, array( 'title_tagline', 'static_front_page' ), true );
}

/** [tab, device] for a control: tab general|design, device ''|desktop|mobile. */
function hx_cui_meta( $c ) {
	$id    = $c->id;
	$label = wp_strip_all_tags( (string) $c->label );
	$cls   = get_class( $c );

	$design_ids = array(
		'pp_t_font', 'pp_t_weight', 'pp_t_align', 'pp_t_align_m', 'pp_p_align', 'pp_d_font', 'pp_d_lh', 'pp_d_align',
		'pp_img_fit', 'pp_img_side', 'pp_sticky', 'hx_soc_style', 'hx_soc_shape', 'hx_soc_align', 'hx_soc_hover',
		'hx_cf_font', 'hx_cf_shadow', 'hx_cf_cols', 'hx_cf_anim', 'hx_cf_labels', 'hx_cf_icons', 'hx_cf_btn_icon',
		'hx_c_notice_sync', 'hx_pc_lines', 'hx_hero_full', 'hx_hero_full_m', 'hx_logo_text_color',
	);
	$design_ids = (array) apply_filters( 'hx_cui_design_ids', $design_ids );
	$tab = 'general';
	if ( in_array( $id, $design_ids, true ) || 'color' === $c->type || 'hx_range' === $c->type || 'number' === $c->type
		|| 'HX_Preset_Control' === $cls || 'WP_Customize_Color_Control' === $cls ) {
		$tab = 'design';
	}

	$dev = '';
	if ( preg_match( '/\s-\s*desktop\b|on desktop\b/i', $label ) ) {
		$dev = 'desktop';
	} elseif ( preg_match( '/\s-\s*mobile\b|on phones\b|for phones\b|on mobile\b/i', $label ) || preg_match( '/_m$|_m_\d+$/', $id ) ) {
		$dev = 'mobile';
	}
	return array( $tab, $dev );
}

add_action(
	'customize_controls_print_footer_scripts',
	function () {
		global $wp_customize;
		if ( ! $wp_customize ) {
			return;
		}
		$meta = array();
		foreach ( $wp_customize->controls() as $c ) {
			if ( ! hx_cui_section_ok( (string) $c->section ) ) {
				continue;
			}
			$meta[ $c->id ] = hx_cui_meta( $c );
		}
		?>
		<script>
		( function ( api, $ ) {
			var META = <?php echo wp_json_encode( $meta ); ?>;
			var STRIP = /\s*-\s*(desktop|mobile)\b/i;
			var sections = {};

			function deviceOf( id ) { return META[ id ] ? META[ id ][1] : ''; }
			function tabOf( id ) { return META[ id ] ? META[ id ][0] : 'general'; }

			function apply( secId ) {
				var s = sections[ secId ], dev = api.previewedDevice.get();
				if ( ! s ) { return; }
				s.controls.forEach( function ( c ) {
					var d = deviceOf( c.id ), offDev = ( 'desktop' === d && 'desktop' !== dev ) || ( 'mobile' === d && 'desktop' === dev );
					c.container.toggleClass( 'hx-off-tab', s.tabs && tabOf( c.id ) !== s.tab );
					c.container.toggleClass( 'hx-off-dev', !! offDev );
				} );
				if ( s.bar ) {
					s.bar.find( '[data-tab]' ).each( function () {
						var on = this.getAttribute( 'data-tab' ) === s.tab;
						this.classList.toggle( 'is-on', on );
						this.setAttribute( 'aria-selected', on ? 'true' : 'false' );
					} );
				}
				if ( s.note ) {
					s.note.find( '[data-dev]' ).each( function () {
						var d = this.getAttribute( 'data-dev' );
						this.classList.toggle( 'is-on', 'desktop' === d ? 'desktop' === dev : 'desktop' !== dev );
					} );
				}
			}

			function setDevice( d ) {
				api.previewedDevice.set( d );
			}

			api.bind( 'ready', function () {
				api.section.each( function ( sec ) {
					var ctrls = sec.controls().filter( function ( c ) { return META.hasOwnProperty( c.id ); } );
					if ( ! ctrls.length ) { return; }
					var hasGen = false, hasDes = false, hasDev = false;
					ctrls.forEach( function ( c ) {
						if ( 'design' === tabOf( c.id ) ) { hasDes = true; } else { hasGen = true; }
						if ( deviceOf( c.id ) ) { hasDev = true; }
					} );
					var s = { controls: ctrls, tabs: hasGen && hasDes, tab: 'general' };
					sections[ sec.id ] = s;
					var anchor = sec.contentContainer.find( '.customize-section-description-container' ).first();

					if ( hasDev ) {
						s.note = $( '<li class="hx-cui-dev" role="group" aria-label="Settings for device"><button type="button" data-dev="desktop" title="Settings for computers"><span class="dashicons dashicons-desktop"></span>Computer</button><button type="button" data-dev="mobile" title="Settings for tablets and phones"><span class="dashicons dashicons-smartphone"></span>Phone</button></li>' );
						s.note.on( 'click', '[data-dev]', function () { setDevice( 'desktop' === this.getAttribute( 'data-dev' ) ? 'desktop' : 'mobile' ); } );
						anchor.after( s.note );
					}
					if ( s.tabs ) {
						s.bar = $( '<li class="hx-cui-tabs" role="tablist"><button type="button" role="tab" data-tab="general">General</button><button type="button" role="tab" data-tab="design">Design</button></li>' );
						s.bar.on( 'click', '[data-tab]', function () { s.tab = this.getAttribute( 'data-tab' ); apply( sec.id ); } );
						anchor.after( s.bar );
					}

					// Labels: drop "- desktop" / "- mobile" and show a small device icon instead.
					ctrls.forEach( function ( c ) {
						var d = deviceOf( c.id );
						if ( ! d ) { return; }
						var t = c.container.find( '.customize-control-title' ).first();
						if ( ! t.length ) { t = c.container.find( 'label' ).first(); }
						t.contents().filter( function () { return 3 === this.nodeType; } ).each( function () {
							this.nodeValue = this.nodeValue.replace( STRIP, '' );
						} );
						t.prepend( '<span class="hx-cui-ico dashicons dashicons-' + ( 'desktop' === d ? 'desktop' : 'smartphone' ) + '" title="' + ( 'desktop' === d ? 'Computer' : 'Tablet & phone' ) + '"></span>' );
					} );
					apply( sec.id );
				} );
				api.previewedDevice.bind( function () {
					Object.keys( sections ).forEach( apply );
				} );
			} );
		}( wp.customize, jQuery ) );
		</script>
		<?php
	}
);

/* ---------- Look ---------- */
add_action(
	'customize_controls_print_styles',
	function () {
		?>
		<style id="hx-customizer-ui">
			/* Cards for panels and sections */
			#customize-theme-controls .customize-pane-parent,
			#customize-theme-controls .customize-pane-child { background: #f0f0f1; }
			#customize-theme-controls li.accordion-section:not(#accordion-section-themes) > h3.accordion-section-title {
				position: relative; margin: 0 12px 10px; padding: 0; border: 0; background: transparent;
			}
			#customize-theme-controls li.accordion-section:not(#accordion-section-themes) > h3.accordion-section-title > button.accordion-trigger {
				width: 100%; padding: 15px 40px 15px 16px; border: 1px solid #e2e4e7; border-radius: 8px; background: #fff;
				box-shadow: 0 1px 2px rgba(0, 0, 0, .04); color: #1d2327; font-size: 14px; font-weight: 600; line-height: 1.4;
				transition: border-color .15s, box-shadow .15s, color .15s;
			}
			#customize-theme-controls li.accordion-section:not(#accordion-section-themes) > h3.accordion-section-title > button.accordion-trigger:hover,
			#customize-theme-controls li.accordion-section:not(#accordion-section-themes) > h3.accordion-section-title > button.accordion-trigger:focus {
				border-color: #2271b1; box-shadow: 0 2px 6px rgba(34, 113, 177, .12); background: #fff; color: #2271b1;
			}
			#customize-theme-controls li.accordion-section:not(#accordion-section-themes) > h3.accordion-section-title:after { top: 50%; right: 14px; margin-top: -10px; color: #8c8f94; }
			#customize-theme-controls .customize-pane-parent > li.accordion-section.control-section:not(#accordion-section-themes):first-of-type,
			#customize-theme-controls .customize-pane-child > li.accordion-section.control-section:first-of-type { margin-top: 12px; }

			/* General / Design tabs */
			.hx-cui-tabs { display: flex; margin: 0 -12px 14px; border-bottom: 1px solid #dcdcde; background: #fff; list-style: none; }
			.hx-cui-tabs button { flex: 1; padding: 14px 8px 12px; border: 0; border-bottom: 3px solid transparent; background: transparent;
				color: #50575e; font-size: 13px; font-weight: 600; letter-spacing: .06em; text-transform: uppercase; cursor: pointer; }
			.hx-cui-tabs button:hover { color: #1d2327; }
			.hx-cui-tabs button.is-on { border-bottom-color: #2271b1; background: #f6f7f7; color: #1d2327; }
			.hx-cui-tabs button:focus-visible { outline: 2px solid #2271b1; outline-offset: -2px; }

			/* Device switch inside a section: one compact segmented control */
			.hx-cui-dev { display: flex; margin: 0 0 14px; padding: 3px; border: 1px solid #dcdcde; border-radius: 8px; background: #fff; list-style: none; }
			.hx-cui-dev button { flex: 1; display: inline-flex; align-items: center; justify-content: center; gap: 6px; padding: 6px 8px; border: 0; border-radius: 6px; background: transparent; color: #50575e; font-size: 12px; font-weight: 600; line-height: 1.2; cursor: pointer; transition: background .15s, color .15s; }
			.hx-cui-dev button:hover { color: #1d2327; background: #f6f7f7; }
			.hx-cui-dev button .dashicons { width: 16px; height: 16px; font-size: 16px; }
			.hx-cui-dev button.is-on { background: #2271b1; color: #fff; box-shadow: 0 1px 2px rgba(0, 0, 0, .12); }
			.hx-cui-dev button:focus-visible { outline: 2px solid #2271b1; outline-offset: 1px; }
			.hx-cui-ico { width: 16px; height: 16px; margin-right: 4px; font-size: 16px; color: #2271b1; vertical-align: -3px; }

			.hx-off-tab, .hx-off-dev { display: none !important; }

			/* Controls in the theme sections as light cards */
			#customize-theme-controls .customize-pane-child .customize-control { margin-bottom: 14px; }
		</style>
		<?php
	}
);

/* ---------- Opening a section shows the matching part of the store in the preview ---------- */

/** Pages the preview can jump to. */
function hx_cui_urls() {
	$product = '';
	$ids     = get_posts( array( 'post_type' => 'product', 'post_status' => 'publish', 'numberposts' => 1, 'fields' => 'ids', 'meta_key' => '_hx_offers' ) ); // phpcs:ignore WordPress.DB.SlowDBQuery
	if ( ! $ids ) {
		$ids = get_posts( array( 'post_type' => 'product', 'post_status' => 'publish', 'numberposts' => 1, 'fields' => 'ids' ) );
	}
	if ( $ids ) {
		$product = get_permalink( $ids[0] );
	}
	$blog = (int) get_option( 'page_for_posts' ) ? get_permalink( (int) get_option( 'page_for_posts' ) ) : home_url( '/' );
	return array(
		'home'    => home_url( '/' ),
		'product' => $product,
		'blog'    => $blog,
	);
}

add_action(
	'customize_controls_print_footer_scripts',
	function () {
		// section or panel id => [page or '' (stay on the current page), CSS selector or '' (top)].
		$sections = array(
			'title_tagline'     => array( '', '.hx-hb, .hx-topbar' ),
			'hx_hb'             => array( '', '.hx-hb, .hx-topbar' ),
			'hx_hb_row_top'     => array( '', '.hx-hb__row--top, .hx-hb, .hx-topbar' ),
			'hx_hb_row_main'    => array( '', '.hx-hb__row--main, .hx-hb, .hx-topbar' ),
			'hx_hb_row_bottom'  => array( '', '.hx-hb__row--bottom, .hx-hb, .hx-topbar' ),
			'hx_hb_links'       => array( '', '.hx-hb-links, .hx-hb, .hx-topbar' ),
			'hx_hb_search'      => array( '', '.hx-hb-search, .hx-hb, .hx-topbar' ),
			'hx_hb_wa'          => array( '', '.hx-hb-wa, .hx-hb, .hx-topbar' ),
			'hx_hb_btn'         => array( '', '.hx-hb-btn, .hx-hb, .hx-topbar' ),
			'hx_hb_html'        => array( '', '.hx-hb-html, .hx-hb, .hx-topbar' ),
			'hx_header'         => array( '', '.hx-hb, .hx-topbar' ),
			'hx_hdr_notice'     => array( '', '.hx-notice' ),
			'hx_social'         => array( '', '@menu' ),
			'hx_fx'             => array( '', '.hx-hb .hx-cur, .hx-tools' ),
			'hx_hero'           => array( 'home', '.hx-hero' ),
			'hx_cards'          => array( 'home', '.hx-grid' ),
			'static_front_page' => array( 'home', '' ),
			'hx_blog'           => array( 'blog', '' ),
			'hx_footer'         => array( '', '.hx-footer' ),
			'hx_pp_layout'      => array( 'product', '.hx-product' ),
			'hx_pp_images'      => array( 'product', '.hx-product__media' ),
			'hx_pp_title'       => array( 'product', '.hx-product__title' ),
			'hx_pp_price'       => array( 'product', '.hx-pp' ),
			'hx_pp_desc'        => array( 'product', '.hx-product__desc' ),
			'hx_pp_related'     => array( 'product', '.hx-rel' ),
			'hx_cf_tpl'         => array( 'product', '.hx-order' ),
			'hx_cf_fields'      => array( 'product', '.hx-cf-fields' ),
			'hx_cf_layout'      => array( 'product', '.hx-order' ),
			'hx_cf_text'        => array( 'product', '.hx-order' ),
			'hx_cf_button'      => array( 'product', '.hx-order__actions' ),
			'hx_cf_wa'          => array( 'product', '.hx-order__actions' ),
		);
		$panels   = array(
			'hx_g_header'     => array( '', '.hx-hb, .hx-topbar' ),
			'hx_g_home'       => array( 'home', '' ),
			'hx_product_page' => array( 'product', '.hx-product' ),
			'hx_cod_form'     => array( 'product', '.hx-order' ),
		);
		?>
		<script>
		( function ( api ) {
			var URLS = <?php echo wp_json_encode( hx_cui_urls() ); ?>,
				SEC = <?php echo wp_json_encode( $sections ); ?>,
				PAN = <?php echo wp_json_encode( $panels ); ?>,
				pending = null;

			function path( u ) {
				try { var x = new URL( u, location.href ); var p = x.pathname; try { p = decodeURIComponent( p ); } catch ( e2 ) {} return x.origin + p.replace( /\/+$/, '' ); } catch ( e ) { return u; }
			}
			function go( t ) {
				if ( ! t ) { return; }
				var url = t[0] ? URLS[ t[0] ] : '', sel = t[1] || '@top';
				if ( url && path( api.previewer.previewUrl.get() ) !== path( url ) ) {
					pending = sel;
					api.previewer.previewUrl.set( url );
				} else {
					api.previewer.send( 'hx-show', sel );
				}
			}
			api.bind( 'ready', function () {
				api.previewer.bind( 'ready', function () {
					if ( pending ) { api.previewer.send( 'hx-show', pending ); pending = null; }
				} );
				api.section.each( function ( s ) {
					s.expanded.bind( function ( open ) {
						if ( open ) { go( SEC[ s.id ] ); } else if ( SEC[ s.id ] && '@menu' === SEC[ s.id ][1] ) { api.previewer.send( 'hx-show', '@menu-close' ); }
					} );
				} );
				api.panel.each( function ( p ) {
					p.expanded.bind( function ( open ) { if ( open ) { go( PAN[ p.id ] ); } } );
				} );
			} );
		}( wp.customize ) );
		</script>
		<?php
	},
	20
);

add_action(
	'customize_preview_init',
	function () {
		wp_add_inline_script(
			'customize-preview',
			"( function ( api ) {
				api.bind( 'preview-ready', function () {
					api.preview.bind( 'hx-show', function ( sel ) {
						var open = document.querySelector( '[data-hx-menu-open]' ), close = document.querySelector( '.hx-menu__close' );
						if ( '@menu' === sel ) { if ( open ) { open.click(); } return; }
						if ( '@menu-close' === sel ) { if ( close && document.querySelector( '.hx-menu[aria-hidden=\"false\"]' ) ) { close.click(); } return; }
						if ( '@top' === sel ) { window.scrollTo( { top: 0, behavior: 'smooth' } ); return; }
						var el = document.querySelector( sel );
						if ( ! el ) { window.scrollTo( { top: 0, behavior: 'smooth' } ); return; }
						var top = el.getBoundingClientRect().top + window.pageYOffset - 70;
						window.scrollTo( { top: Math.max( 0, top ), behavior: 'smooth' } );
						el.classList.remove( 'hx-cz-flash' );
						void el.offsetWidth;
						el.classList.add( 'hx-cz-flash' );
						setTimeout( function () { el.classList.remove( 'hx-cz-flash' ); }, 1800 );
					} );
				} );
			}( wp.customize ) );"
		);
		add_action(
			'wp_head',
			function () {
				echo '<style id="hx-cz-flash">@keyframes hx-cz-flash{0%,60%{box-shadow:0 0 0 3px #2271b1,0 0 0 9px rgba(34,113,177,.25)}100%{box-shadow:0 0 0 3px rgba(34,113,177,0),0 0 0 9px rgba(34,113,177,0)}}.hx-cz-flash{animation:hx-cz-flash 1.8s ease-out}</style>' . "\n";
			},
			200
		);
	}
);

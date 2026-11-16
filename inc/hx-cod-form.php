<?php
/**
 * COD Form: Appearance > Customize > COD Form.
 * Ready-made form designs (templates) + options for fields, layout, colours & text,
 * button & badges and WhatsApp ordering. The old "Product page > Order form & button"
 * options are moved here (same settings, values kept).
 *
 * @package hx-store
 */

defined( 'ABSPATH' ) || exit;

/* ---------- Templates ---------- */

/** Templates: key => [name, description, defaults (labels, icons, cols, anim)]. */
function hx_cf_templates() {
	return array(
		'classic'  => array( 'Classic', 'The current store form: boxed, icons in every field.', array( 'labels' => 'inside', 'icons' => 'show', 'cols' => '1', 'anim' => 'shake' ) ),
		'urgent'   => array( 'Bold & urgent', 'Coloured header, 2 fields per row on computers, big pulsing button, badges as pills.', array( 'labels' => 'inside', 'icons' => 'hide', 'cols' => '2d', 'anim' => 'pulse' ) ),
		'checkout' => array( 'Clean checkout', 'Labels above the fields, calm white card. Feels like a trusted checkout.', array( 'labels' => 'above', 'icons' => 'hide', 'cols' => '1', 'anim' => 'none' ) ),
		'trust'    => array( 'Trust card', 'Thick top border, 3 guarantee boxes above a green order button.', array( 'labels' => 'inside', 'icons' => 'show', 'cols' => '1', 'anim' => 'pulse' ) ),
		'soft'     => array( 'Soft rounded', 'Tinted background, pill-shaped fields and button, soft shadow.', array( 'labels' => 'inside', 'icons' => 'show', 'cols' => '1', 'anim' => 'shake' ) ),
		'minimal'  => array( 'Minimal', 'No box: underlined fields with small labels, simple button.', array( 'labels' => 'above', 'icons' => 'hide', 'cols' => '1', 'anim' => 'none' ) ),
	);
}

function hx_cf_tpl() {
	$t = (string) get_theme_mod( 'hx_cf_tpl', 'classic' );
	return array_key_exists( $t, hx_cf_templates() ) ? $t : 'classic';
}

/** An option chosen in COD Form, or the current template's own default. */
function hx_cf_opt( $key ) {
	$v = (string) get_theme_mod( 'hx_cf_' . $key, '' );
	if ( '' !== $v ) {
		return $v;
	}
	if ( 'anim' === $key && 'off' === (string) get_theme_mod( 'pp_f_btn_anim', '' ) ) {
		return 'none'; // Old "shake animation: Off" setting.
	}
	$tpls = hx_cf_templates();
	return $tpls[ hx_cf_tpl() ][2][ $key ];
}

/** CSS classes for the <form>. */
function hx_cf_classes() {
	$c = array(
		'hx-cf',
		'hx-cf--' . hx_cf_tpl(),
		'hx-cf--labels-' . hx_cf_opt( 'labels' ),
		'hx-cf--icons-' . hx_cf_opt( 'icons' ),
		'hx-cf--cols-' . hx_cf_opt( 'cols' ),
		'hx-cf--anim-' . hx_cf_opt( 'anim' ),
	);
	if ( 'hide' === get_theme_mod( 'hx_cf_btn_icon', '' ) ) {
		$c[] = 'hx-cf--btnicon-hide';
	}
	return implode( ' ', $c );
}

/* ---------- Fields ---------- */

function hx_cf_icon( $k ) {
	$pin   = '<svg viewBox="0 0 24 24" width="20"><path fill="currentColor" d="M12 2a7 7 0 0 0-7 7c0 5.25 7 13 7 13s7-7.75 7-13a7 7 0 0 0-7-7zm0 9.5a2.5 2.5 0 1 1 0-5 2.5 2.5 0 0 1 0 5z"/></svg>';
	$icons = array(
		'name'    => '<svg viewBox="0 0 24 24" width="20"><path fill="currentColor" d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20zm0 2a8 8 0 0 1 6.32 12.9C16.9 15.1 14.6 14 12 14s-4.9 1.1-6.32 2.9A8 8 0 0 1 12 4zm0 2a3.5 3.5 0 1 0 0 7 3.5 3.5 0 0 0 0-7z"/></svg>',
		'phone'   => '<svg viewBox="0 0 24 24" width="20"><path fill="currentColor" d="M6.62 10.79a15.05 15.05 0 0 0 6.59 6.59l2.2-2.2a1 1 0 0 1 1.02-.24c1.12.37 2.33.57 3.57.57a1 1 0 0 1 1 1V20a1 1 0 0 1-1 1A17 17 0 0 1 3 4a1 1 0 0 1 1-1h3.5a1 1 0 0 1 1 1c0 1.25.2 2.45.57 3.57a1 1 0 0 1-.25 1.02l-2.2 2.2z"/></svg>',
		'city'    => $pin,
		'address' => $pin,
	);
	return $icons[ $k ];
}

/** Field definitions: key => [label, placeholder, input type, autocomplete, can be hidden]. */
function hx_cf_field_defs() {
	return array(
		'name'    => array( 'الإسم الكامل', 'الإسم الكامل - Nom complet', 'text', 'name', false ),
		'phone'   => array( 'رقم الهاتف', 'رقم الهاتف - Téléphone', 'tel', 'tel', false ),
		'city'    => array( 'المدينة', 'المدينة - Ville', 'text', 'address-level2', true ),
		'address' => array( 'العنوان الكامل', 'العنوان الكامل - Adresse', 'text', 'street-address', true ),
	);
}

/** Fields as shown on the form, in order: [key, label, placeholder, type, autocomplete, required]. */
function hx_cf_fields() {
	$out = array();
	$i   = 0;
	foreach ( hx_cf_field_defs() as $k => $d ) {
		$i++;
		$show = ! $d[4] || '0' !== (string) get_theme_mod( 'hx_cf_' . $k . '_show', '1' );
		if ( ! $show ) {
			continue;
		}
		$label = trim( (string) get_theme_mod( 'hx_cf_' . $k . '_label', '' ) );
		$ph    = trim( (string) get_theme_mod( 'hx_cf_' . $k . '_ph', '' ) );
		$pos   = (int) get_theme_mod( 'hx_cf_' . $k . '_pos', 0 );
		$out[] = array(
			'key'   => $k,
			'label' => '' !== $label ? $label : $d[0],
			'ph'    => '' !== $ph ? $ph : $d[1],
			'type'  => $d[2],
			'ac'    => $d[3],
			'req'   => ! $d[4] || '0' !== (string) get_theme_mod( 'hx_cf_' . $k . '_req', '1' ),
			'sort'  => ( $pos >= 1 && $pos <= 4 ? $pos : $i ) * 10 + $i,
		);
	}
	usort(
		$out,
		function ( $a, $b ) {
			return $a['sort'] - $b['sort'];
		}
	);
	return $out;
}

/** Which of city / address the server must check (shown and required). */
function hx_cf_required() {
	$r = array( 'city' => false, 'address' => false );
	foreach ( hx_cf_fields() as $f ) {
		if ( isset( $r[ $f['key'] ] ) ) {
			$r[ $f['key'] ] = $f['req'];
		}
	}
	return $r;
}

function hx_cf_render_fields( $sfx = '' ) {
	$above = 'above' === hx_cf_opt( 'labels' );
	echo '<div class="hx-cf-fields">';
	foreach ( hx_cf_fields() as $f ) {
		$id = 'hx-f-' . $f['key'] . $sfx;
		echo '<div class="hx-cf-item hx-cf-item--' . esc_attr( $f['key'] ) . '">';
		if ( $above ) {
			echo '<label class="hx-cf-label" for="' . esc_attr( $id ) . '">' . esc_html( $f['label'] ) . ( $f['req'] ? ' <b aria-hidden="true">*</b>' : '' ) . '</label>';
		}
		echo '<label class="hx-field">';
		echo '<span class="hx-field__icon" aria-hidden="true">' . hx_cf_icon( $f['key'] ) . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput -- fixed SVG.
		printf(
			'<input class="hx-field__input" id="%1$s" type="%2$s" name="%3$s" placeholder="%4$s" autocomplete="%5$s"%6$s%7$s%8$s>',
			esc_attr( $id ),
			esc_attr( $f['type'] ),
			esc_attr( $f['key'] ),
			esc_attr( $f['ph'] ),
			esc_attr( $f['ac'] ),
			'tel' === $f['type'] ? ' inputmode="tel"' : '',
			$above ? '' : ' aria-label="' . esc_attr( $f['label'] ) . '"',
			$f['req'] ? ' required' : ''
		);
		echo '</label></div>';
	}
	echo '</div>';
}

/* ---------- Trust badges ---------- */

function hx_cf_badges() {
	$def = array( '💵 الدفع عند الاستلام', '📞 تأكيد الطلب بالهاتف', '🔒 معلوماتك آمنة' );
	$out = array();
	foreach ( $def as $i => $d ) {
		$v = trim( (string) get_theme_mod( 'hx_cf_b' . ( $i + 1 ), '' ) );
		if ( '-' === $v ) {
			continue; // A single dash hides this badge.
		}
		$out[] = '' !== $v ? $v : $d;
	}
	return $out;
}

function hx_cf_render_trust() {
	$b = hx_cf_badges();
	if ( ! $b ) {
		return;
	}
	echo '<ul class="hx-order__trust">';
	foreach ( $b as $t ) {
		// First "word" made only of symbols/emoji becomes the icon.
		if ( preg_match( '/^(\X)\s+(.+)$/u', $t, $m ) && ! preg_match( '/[\p{L}\p{N}]/u', $m[1] ) ) {
			echo '<li><span aria-hidden="true">' . esc_html( $m[1] ) . '</span> ' . esc_html( $m[2] ) . '</li>';
		} else {
			echo '<li>' . esc_html( $t ) . '</li>';
		}
	}
	echo '</ul>';
}

/* ---------- Front end: template CSS + your options ---------- */

add_action(
	'wp_enqueue_scripts',
	function () {
		if ( function_exists( 'is_product' ) && is_product() ) {
			wp_enqueue_style( 'hx-cod-form', get_theme_file_uri( 'assets/hx-cod-form.css' ), array( 'hx' ), HX_VER );
		}
	},
	25
);

add_action(
	'wp_head',
	function () {
		if ( ! ( function_exists( 'is_product' ) && is_product() ) ) {
			return;
		}
		$s   = 'body.single-product .hx-order.hx-cf';
		$css = '';
		$m   = function ( $k ) {
			return (string) get_theme_mod( 'hx_cf_' . $k, '' );
		};
		$col = function ( $k ) use ( $m ) {
			return sanitize_hex_color( $m( $k ) );
		};
		if ( (int) $m( 'width' ) > 0 ) {
			$css .= $s . '{max-width:' . (int) $m( 'width' ) . 'px;margin-inline:auto}';
		}
		$shadows = array( 'none' => 'none', 'soft' => '0 6px 20px rgba(0,0,0,.08)', 'strong' => '0 16px 40px rgba(0,0,0,.2)' );
		if ( isset( $shadows[ $m( 'shadow' ) ] ) ) {
			$css .= $s . '{box-shadow:' . $shadows[ $m( 'shadow' ) ] . '}';
		}
		$fonts = array(
			'cairo'     => '"Cairo", var(--hx-font)',
			'assistant' => '"Assistant", var(--hx-font)',
			'system'    => 'system-ui, -apple-system, "Segoe UI", Roboto, sans-serif',
			'tahoma'    => 'Tahoma, "Segoe UI", Arial, sans-serif',
		);
		if ( isset( $fonts[ $m( 'font' ) ] ) ) {
			$f    = $fonts[ $m( 'font' ) ];
			$css .= $s . ',' . $s . ' .hx-field__input,' . $s . ' .hx-order__btn,' . $s . ' .hx-order__title,' . $s . ' .hx-cf-label{font-family:' . $f . '}';
		}
		if ( $v = $col( 'field_bg' ) ) {
			$css .= $s . ' .hx-field__input,' . $s . '.hx-cf--soft .hx-field{background:' . $v . '}';
		}
		if ( $v = $col( 'field_border' ) ) {
			$css .= $s . ' .hx-field__input,' . $s . ' .hx-field__icon,' . $s . '.hx-cf--soft .hx-field{border-color:' . $v . '}';
		}
		if ( $v = $col( 'field_text' ) ) {
			$css .= $s . ' .hx-field__input{color:' . $v . '}';
		}
		if ( $v = $col( 'ph' ) ) {
			$css .= $s . ' .hx-field__input::placeholder{color:' . $v . '}';
		}
		if ( $v = $col( 'icon_bg' ) ) {
			$css .= $s . ' .hx-field__icon{background:' . $v . '}';
		}
		if ( $v = $col( 'icon_col' ) ) {
			$css .= $s . ' .hx-field__icon{color:' . $v . '}';
		}
		if ( $v = $col( 'label_col' ) ) {
			$css .= $s . ' .hx-cf-label{color:' . $v . '}';
		}
		if ( (int) $m( 'field_fs' ) > 0 ) {
			$css .= $s . ' .hx-field__input{font-size:' . (int) $m( 'field_fs' ) . 'px}';
		}
		if ( $v = $col( 'trust_col' ) ) {
			$css .= $s . ' .hx-order__trust{color:' . $v . '}';
		}
		$r = (string) get_theme_mod( 'pp_f_field_r', '' );
		if ( '' !== $r && 'hide' === hx_cf_opt( 'icons' ) ) {
			$css .= $s . ' .hx-field__input{border-radius:' . (int) $r . 'px}';
		}
		if ( '' !== $css ) {
			echo '<style id="hx-cod-form">' . $css . '</style>' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput
		}
	},
	120
);

/* ---------- Customizer ---------- */

/** Small picture of each template for the picker. */
function hx_cf_thumb( $t ) {
	$p   = '#111827';
	$a   = '#d4a017';
	$f   = '<rect x="%d" y="%d" width="%d" height="9" rx="%s" fill="%s" stroke="%s"/>';
	$svg = '';
	switch ( $t ) {
		case 'urgent':
			$svg .= '<rect x="4" y="4" width="112" height="82" rx="8" fill="#fff" stroke="#e5e7eb"/><rect x="4" y="4" width="112" height="18" rx="8" fill="' . $p . '"/><rect x="30" y="10" width="60" height="5" rx="2" fill="#fff"/>';
			foreach ( array( 28, 41 ) as $y ) {
				$svg .= sprintf( $f, 12, $y, 46, 2, '#fff', '#cbd5e1' ) . sprintf( $f, 62, $y, 46, 2, '#fff', '#cbd5e1' );
			}
			$svg .= '<rect x="12" y="56" width="96" height="14" rx="4" fill="' . $a . '"/><rect x="14" y="75" width="28" height="6" rx="3" fill="#eef2f7"/><rect x="46" y="75" width="28" height="6" rx="3" fill="#eef2f7"/><rect x="78" y="75" width="28" height="6" rx="3" fill="#eef2f7"/>';
			break;
		case 'checkout':
			$svg .= '<rect x="4" y="4" width="112" height="82" rx="8" fill="#fff" stroke="#e5e7eb"/><rect x="35" y="9" width="50" height="5" rx="2" fill="' . $p . '"/>';
			foreach ( array( 19, 37 ) as $y ) {
				$svg .= '<rect x="78" y="' . $y . '" width="30" height="3" rx="1" fill="#6b7280"/>' . sprintf( $f, 12, $y + 5, 96, 3, '#fff', '#cbd5e1' );
			}
			$svg .= '<rect x="12" y="57" width="96" height="13" rx="4" fill="' . $p . '"/><line x1="12" y1="76" x2="108" y2="76" stroke="#e5e7eb" stroke-dasharray="3 2"/><rect x="30" y="79" width="60" height="4" rx="2" fill="#cbd5e1"/>';
			break;
		case 'trust':
			$svg .= '<rect x="4" y="4" width="112" height="82" rx="8" fill="#fff" stroke="' . $p . '" stroke-width="1.5"/><rect x="4" y="4" width="112" height="6" rx="3" fill="' . $p . '"/>';
			foreach ( array( 16, 28 ) as $y ) {
				$svg .= sprintf( $f, 12, $y, 96, 2, '#f1f5f9', '#cbd5e1' );
			}
			foreach ( array( 12, 45, 78 ) as $x ) {
				$svg .= '<rect x="' . $x . '" y="42" width="30" height="20" rx="4" fill="#f1f5f9" stroke="#e2e8f0"/><circle cx="' . ( $x + 15 ) . '" cy="49" r="3" fill="' . $a . '"/>';
			}
			$svg .= '<rect x="12" y="68" width="96" height="13" rx="4" fill="#16a34a"/>';
			break;
		case 'soft':
			$svg .= '<rect x="4" y="4" width="112" height="82" rx="14" fill="#eef1f6"/><rect x="35" y="10" width="50" height="5" rx="2" fill="' . $p . '"/>';
			foreach ( array( 21, 34, 47 ) as $y ) {
				$svg .= sprintf( $f, 12, $y, 96, 4.5, '#fff', '#d6dce6' ) . '<circle cx="101" cy="' . ( $y + 4.5 ) . '" r="2.5" fill="' . $p . '"/>';
			}
			$svg .= '<rect x="12" y="63" width="96" height="14" rx="7" fill="' . $p . '"/>';
			break;
		case 'minimal':
			$svg .= '<rect x="70" y="8" width="40" height="5" rx="2" fill="' . $p . '"/>';
			foreach ( array( 20, 36, 52 ) as $y ) {
				$svg .= '<rect x="84" y="' . $y . '" width="24" height="3" rx="1" fill="#9ca3af"/><line x1="12" y1="' . ( $y + 11 ) . '" x2="108" y2="' . ( $y + 11 ) . '" stroke="#9ca3af" stroke-width="1.5"/>';
			}
			$svg .= '<rect x="12" y="70" width="96" height="13" rx="3" fill="' . $p . '"/>';
			break;
		default: // classic.
			$svg .= '<rect x="4" y="4" width="112" height="82" rx="8" fill="#fff" stroke="' . $p . '" stroke-width="1.5"/><rect x="35" y="9" width="50" height="5" rx="2" fill="' . $p . '"/>';
			foreach ( array( 19, 31, 43 ) as $y ) {
				$svg .= sprintf( $f, 12, $y, 84, 2, '#f8fafc', '#cbd5e1' ) . '<rect x="96" y="' . $y . '" width="12" height="9" rx="2" fill="#e5e7eb" stroke="#cbd5e1"/>';
			}
			$svg .= '<rect x="12" y="57" width="96" height="14" rx="4" fill="' . $p . '"/><rect x="25" y="76" width="70" height="4" rx="2" fill="#cbd5e1"/>';
	}
	return '<svg viewBox="0 0 120 90" width="120" height="90" aria-hidden="true">' . $svg . '</svg>';
}

add_action(
	'customize_register',
	function ( $wp_customize ) {
		if ( ! class_exists( 'HX_CF_Template_Control' ) ) {
			/** Template picker: picture cards. */
			class HX_CF_Template_Control extends WP_Customize_Control {
				public $type = 'hx_cf_template';
				public function render_content() {
					echo '<span class="customize-control-title">' . esc_html( $this->label ) . '</span>';
					if ( $this->description ) {
						echo '<span class="description customize-control-description">' . esc_html( $this->description ) . '</span>';
					}
					echo '<div class="hx-cf-pick">';
					foreach ( hx_cf_templates() as $k => $t ) {
						echo '<label class="hx-cf-pick__card"><input type="radio" name="_customize-radio-' . esc_attr( $this->id ) . '" value="' . esc_attr( $k ) . '" ';
						$this->link();
						checked( $this->value(), $k );
						echo '><span class="hx-cf-pick__img">' . hx_cf_thumb( $k ) . '</span><strong>' . esc_html( $t[0] ) . '</strong><small>' . esc_html( $t[1] ) . '</small></label>'; // phpcs:ignore WordPress.Security.EscapeOutput -- fixed SVG.
					}
					echo '</div>';
				}
			}
			/** Plain heading between groups of options. */
			class HX_CF_Heading_Control extends WP_Customize_Control {
				public $type = 'hx_cf_heading';
				public function render_content() {
					echo '<h3 class="hx-cf-h">' . esc_html( $this->label ) . '</h3>';
					if ( $this->description ) {
						echo '<p class="description">' . esc_html( $this->description ) . '</p>';
					}
				}
			}
		}

		$wp_customize->add_panel(
			'hx_cod_form',
			array(
				'title'       => 'COD Form',
				'description' => 'The order form on product pages. Choose a design, then adjust anything you like. Options left empty follow the chosen design.',
				'priority'    => 28,
			)
		);
		$sections = array(
			'tpl'    => 'Form design (templates)',
			'fields' => 'Fields',
			'layout' => 'Layout',
			'text'   => 'Colours & text',
			'button' => 'Button & badges',
			'wa'     => 'WhatsApp ordering',
		);
		$p = 0;
		foreach ( $sections as $id => $title ) {
			$wp_customize->add_section( 'hx_cf_' . $id, array( 'title' => $title, 'panel' => 'hx_cod_form', 'priority' => $p += 10 ) );
		}

		$prio = 0;
		$add  = function ( $key, $section, $label, $type, $choices = array(), $desc = '' ) use ( $wp_customize, &$prio ) {
			$id   = 'hx_cf_' . $key;
			$prio++;
			$args = array( 'label' => $label, 'section' => 'hx_cf_' . $section, 'priority' => $prio );
			if ( $desc ) {
				$args['description'] = $desc;
			}
			if ( 'color' === $type ) {
				$wp_customize->add_setting( $id, array( 'default' => '', 'sanitize_callback' => 'sanitize_hex_color' ) );
				$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, $id, $args ) );
				return;
			}
			if ( 'select' === $type ) {
				$wp_customize->add_setting(
					$id,
					array(
						'default'           => '',
						'sanitize_callback' => function ( $v ) use ( $choices ) {
							return array_key_exists( (string) $v, $choices ) ? (string) $v : '';
						},
					)
				);
				$wp_customize->add_control( $id, $args + array( 'type' => 'select', 'choices' => $choices ) );
				return;
			}
			if ( 'number' === $type ) {
				$wp_customize->add_setting(
					$id,
					array(
						'default'           => '',
						'sanitize_callback' => function ( $v ) use ( $choices ) {
							return '' === trim( (string) $v ) ? '' : (string) max( $choices[0], min( $choices[1], (int) $v ) );
						},
					)
				);
				$wp_customize->add_control( $id, $args + array( 'type' => 'number', 'input_attrs' => array( 'min' => $choices[0], 'max' => $choices[1], 'placeholder' => 'Design default' ) ) );
				return;
			}
			if ( 'checkbox' === $type ) {
				$wp_customize->add_setting(
					$id,
					array(
						'default'           => '1',
						'sanitize_callback' => function ( $v ) {
							return $v ? '1' : '0';
						},
					)
				);
				$wp_customize->add_control( $id, $args + array( 'type' => 'checkbox' ) );
				return;
			}
			if ( 'heading' === $type ) {
				$wp_customize->add_setting( $id, array( 'sanitize_callback' => '__return_empty_string' ) );
				$wp_customize->add_control( new HX_CF_Heading_Control( $wp_customize, $id, $args ) );
				return;
			}
			$wp_customize->add_setting( $id, array( 'default' => '', 'sanitize_callback' => 'sanitize_text_field' ) );
			$wp_customize->add_control( $id, $args + array( 'type' => 'text', 'input_attrs' => array( 'placeholder' => $choices ? (string) $choices[0] : '' ) ) );
		};

		/* Templates */
		$wp_customize->add_setting(
			'hx_cf_tpl',
			array(
				'default'           => 'classic',
				'sanitize_callback' => function ( $v ) {
					return array_key_exists( (string) $v, hx_cf_templates() ) ? (string) $v : 'classic';
				},
			)
		);
		$wp_customize->add_control(
			new HX_CF_Template_Control(
				$wp_customize,
				'hx_cf_tpl',
				array(
					'label'       => 'Choose a form design',
					'description' => 'Each design uses your store colours. Colours or sizes you set in the other COD Form sections stay on top of the design.',
					'section'     => 'hx_cf_tpl',
				)
			)
		);

		$dflt = array( '' => 'Design default' );

		/* Fields */
		$add( 'labels', 'fields', 'Field names', 'select', $dflt + array( 'inside' => 'Inside the field (placeholder)', 'above' => 'Above the field' ) );
		$add( 'icons', 'fields', 'Field icons', 'select', $dflt + array( 'show' => 'Show', 'hide' => 'Hide' ) );
		$names = array( 'name' => 'Full name', 'phone' => 'Phone', 'city' => 'City', 'address' => 'Address' );
		foreach ( hx_cf_field_defs() as $k => $d ) {
			$add( $k . '_h', 'fields', $names[ $k ] . ' field', 'heading', array(), $d[4] ? '' : 'Always shown and required (needed to confirm the order).' );
			if ( $d[4] ) {
				$add( $k . '_show', 'fields', 'Show this field', 'checkbox' );
				$add( $k . '_req', 'fields', 'Required', 'checkbox' );
			}
			$add( $k . '_label', 'fields', 'Name (shown above the field)', 'text', array( $d[0] ) );
			$add( $k . '_ph', 'fields', 'Placeholder (inside the field)', 'text', array( $d[1] ) );
			$add( $k . '_pos', 'fields', 'Position (1 = first)', 'select', array( '' => 'Default' ) + array( '1' => '1', '2' => '2', '3' => '3', '4' => '4' ) );
		}

		/* Layout */
		$add( 'cols', 'layout', 'Fields per row', 'select', $dflt + array( '1' => '1 per row', '2d' => '2 per row on computers, 1 on phones', '2' => '2 per row everywhere' ) );
		$add( 'width', 'layout', 'Form max width (px)', 'number', array( 280, 900 ) );
		$add( 'shadow', 'layout', 'Shadow', 'select', $dflt + array( 'none' => 'None', 'soft' => 'Soft', 'strong' => 'Strong' ) );

		/* Colours & text */
		$add( 'font', 'text', 'Form font', 'select', $dflt + array( 'cairo' => 'Cairo (Arabic)', 'assistant' => 'Assistant', 'system' => 'System font', 'tahoma' => 'Tahoma / Segoe UI' ) );
		$add( 'field_bg', 'text', 'Field background', 'color' );
		$add( 'field_border', 'text', 'Field border colour', 'color' );
		$add( 'field_text', 'text', 'Text typed in fields', 'color' );
		$add( 'ph', 'text', 'Placeholder colour', 'color' );
		$add( 'icon_bg', 'text', 'Icon box colour', 'color' );
		$add( 'icon_col', 'text', 'Icon colour', 'color' );
		$add( 'label_col', 'text', 'Field names colour (when above)', 'color' );
		$add( 'field_fs', 'text', 'Field text size (px)', 'number', array( 12, 24 ) );

		/* Button & badges */
		$add( 'anim', 'button', 'Button animation', 'select', $dflt + array( 'shake' => 'Shake', 'pulse' => 'Pulse', 'none' => 'None' ) );
		$add( 'btn_icon', 'button', 'Cart icon in the button', 'select', array( '' => 'Show', 'hide' => 'Hide' ) );
		$add( 'badges_h', 'button', 'Trust badges', 'heading', array(), 'Start with an emoji to use it as the icon. Type a single - to hide a badge.' );
		$add( 'b1', 'button', 'Badge 1', 'text', array( '💵 الدفع عند الاستلام' ) );
		$add( 'b2', 'button', 'Badge 2', 'text', array( '📞 تأكيد الطلب بالهاتف' ) );
		$add( 'b3', 'button', 'Badge 3', 'text', array( '🔒 معلوماتك آمنة' ) );
		$add( 'trust_col', 'button', 'Badges text colour', 'color' );
	},
	10
);

/* Move the old "Product page > Order form & button" options into COD Form (same settings, values kept). */
add_action(
	'customize_register',
	function ( $wp_customize ) {
		$map = array(
			'pp_f_title_txt'  => array( 'text', 1 ),
			'pp_f_title_size' => array( 'text', 2 ),
			'pp_f_title_col'  => array( 'text', 3 ),
			'pp_f_sub_txt'    => array( 'text', 4 ),
			'pp_f_sub'        => array( 'text', 5 ),
			'pp_f_bg'         => array( 'layout', 50 ),
			'pp_f_border_c'   => array( 'layout', 51 ),
			'pp_f_border_w'   => array( 'layout', 52 ),
			'pp_f_radius'     => array( 'layout', 53 ),
			'pp_f_pad_d'      => array( 'layout', 54 ),
			'pp_f_pad_m'      => array( 'layout', 55 ),
			'pp_f_gap'        => array( 'layout', 56 ),
			'pp_f_field_h'    => array( 'layout', 57 ),
			'pp_f_field_r'    => array( 'layout', 58 ),
			'pp_f_btn_txt'    => array( 'button', 1 ),
			'pp_f_btn_bg'     => array( 'button', 2 ),
			'pp_f_btn_col'    => array( 'button', 3 ),
			'pp_f_btn_size'   => array( 'button', 4 ),
			'pp_f_btn_h'      => array( 'button', 5 ),
			'pp_f_btn_r'      => array( 'button', 6 ),
			'pp_f_trust'      => array( 'button', 90 ),
			'pp_order_mode'   => array( 'wa', 1 ),
			'pp_wa_number'    => array( 'wa', 2 ),
			'pp_wa_txt'       => array( 'wa', 3 ),
		);
		foreach ( $map as $id => $to ) {
			$c = $wp_customize->get_control( $id );
			if ( $c ) {
				$c->section  = 'hx_cf_' . $to[0];
				$c->priority = $to[1];
			}
		}
		$wp_customize->remove_control( 'pp_f_btn_anim' ); // Replaced by "Button animation".
		$wp_customize->remove_section( 'hx_pp_form' );
	},
	30
);

add_action(
	'customize_controls_print_styles',
	function () {
		echo '<style>.hx-cf-pick{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:8px}.hx-cf-pick__card{display:flex;flex-direction:column;gap:3px;padding:8px;border:2px solid #dcdcde;border-radius:8px;background:#fff;cursor:pointer}.hx-cf-pick__card:hover{border-color:#8c8f94}.hx-cf-pick__card:has(input:checked){border-color:#2271b1;box-shadow:0 0 0 1px #2271b1}.hx-cf-pick__card input{position:absolute;opacity:0}.hx-cf-pick__img svg{display:block;width:100%;height:auto;border-radius:4px;background:#f6f7f7}.hx-cf-pick__card strong{font-size:13px}.hx-cf-pick__card small{font-size:11px;line-height:1.35;color:#646970}.hx-cf-h{margin:18px 0 0;padding-top:12px;border-top:1px solid #dcdcde;font-size:14px}</style>';
	}
);

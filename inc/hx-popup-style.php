<?php
/**
 * Offer popup: look of each part (Customize > Offer popup > Design tab).
 * Font, size (desktop / mobile), thickness and colours of the title, text, buttons, code, label and wheel.
 * Empty = the look of the chosen design.
 *
 * @package hx-store
 */

defined( 'ABSPATH' ) || exit;

/** Fonts for the popup: key => [label, CSS family, Google Fonts family or ''] */
function hx_popt_fonts() {
	return array(
		''        => array( 'Same as the store (Cairo)', '', '' ),
		'tajawal' => array( 'Tajawal', '"Tajawal", "Cairo", sans-serif', 'Tajawal:wght@400;500;700;800;900' ),
		'almarai' => array( 'Almarai', '"Almarai", "Cairo", sans-serif', 'Almarai:wght@400;700;800' ),
		'changa'  => array( 'Changa', '"Changa", "Cairo", sans-serif', 'Changa:wght@400;500;600;700;800' ),
		'readex'  => array( 'Readex Pro', '"Readex Pro", "Cairo", sans-serif', 'Readex+Pro:wght@400;500;600;700' ),
		'tahoma'  => array( 'Tahoma / Segoe UI', 'Tahoma, "Segoe UI", Arial, sans-serif', '' ),
		'system'  => array( 'Phone / computer font', 'system-ui, -apple-system, "Segoe UI", Roboto, sans-serif', '' ),
	);
}

/** Thickness choices. */
function hx_popt_weights() {
	return array( '' => 'Design default', '400' => 'Normal', '600' => 'Semi bold', '700' => 'Bold', '800' => 'Extra bold', '900' => 'Black' );
}

/**
 * All the style settings: id => [type, label, extra].
 * type: head | font | weight | color | size (extra = [min, max, shown default]).
 */
function hx_popt_fields() {
	return array(
		'hx_popt_h_all'     => array( 'head', 'Popup text style', 'Leave a setting empty (or press ↺) to keep the look of the chosen design.' ),
		'hx_popt_font'      => array( 'font', 'Font of the whole popup' ),
		'hx_popt_radius'    => array( 'size', 'Popup corner roundness (px)', array( 0, 40, 28 ) ),

		'hx_popt_h_title'   => array( 'head', 'Title' ),
		'hx_popt_t_size'    => array( 'size', 'Title size - desktop (px)', array( 16, 56, 30 ) ),
		'hx_popt_t_size_m'  => array( 'size', 'Title size - mobile (px)', array( 14, 44, 25 ) ),
		'hx_popt_t_weight'  => array( 'weight', 'Title thickness' ),
		'hx_popt_t_align'   => array( 'align', 'Title alignment - desktop' ),
		'hx_popt_t_align_m' => array( 'align', 'Title alignment - mobile' ),
		'hx_popt_t_color'   => array( 'color', 'Title colour' ),
		'hx_popt_t_em'      => array( 'color', 'Coloured word colour (*word*)' ),
		'hx_popt_t_hl'      => array( 'color', 'Title box colour (behind the title)' ),

		'hx_popt_h_text'    => array( 'head', 'Text' ),
		'hx_popt_x_size'    => array( 'size', 'Text size - desktop (px)', array( 11, 26, 16 ) ),
		'hx_popt_x_size_m'  => array( 'size', 'Text size - mobile (px)', array( 11, 24, 15 ) ),
		'hx_popt_x_weight'  => array( 'weight', 'Text thickness' ),
		'hx_popt_x_align'   => array( 'align', 'Text alignment - desktop' ),
		'hx_popt_x_align_m' => array( 'align', 'Text alignment - mobile' ),
		'hx_popt_x_color'   => array( 'color', 'Text colour' ),

		'hx_popt_h_btn'     => array( 'head', 'Main button' ),
		'hx_popt_b_size'    => array( 'size', 'Button text size - desktop (px)', array( 12, 26, 17 ) ),
		'hx_popt_b_size_m'  => array( 'size', 'Button text size - mobile (px)', array( 12, 24, 17 ) ),
		'hx_popt_b_weight'  => array( 'weight', 'Button text thickness' ),
		'hx_popt_b_color'   => array( 'color', 'Button text colour' ),
		'hx_popt_b_bg'      => array( 'color', 'Button colour' ),
		'hx_popt_b_h'       => array( 'size', 'Button height (px)', array( 40, 72, 58 ) ),
		'hx_popt_b_r'       => array( 'size', 'Button roundness (px)', array( 0, 36, 12 ) ),

		'hx_popt_h_no'      => array( 'head', '"No thanks" button' ),
		'hx_popt_n_size'    => array( 'size', '"No thanks" text size (px)', array( 11, 22, 16 ) ),
		'hx_popt_n_color'   => array( 'color', '"No thanks" text colour' ),
		'hx_popt_n_bg'      => array( 'color', '"No thanks" background' ),

		'hx_popt_h_code'    => array( 'head', 'Coupon code box' ),
		'hx_popt_c_size'    => array( 'size', 'Code size - desktop (px)', array( 12, 32, 20 ) ),
		'hx_popt_c_size_m'  => array( 'size', 'Code size - mobile (px)', array( 12, 28, 18 ) ),
		'hx_popt_c_color'   => array( 'color', 'Code colour' ),
		'hx_popt_c_border'  => array( 'color', 'Code border colour' ),
		'hx_popt_c_bg'      => array( 'color', 'Code box background' ),

		'hx_popt_h_badge'   => array( 'head', 'Small label' ),
		'hx_popt_g_size'    => array( 'size', 'Label text size (px)', array( 10, 20, 13 ) ),
		'hx_popt_g_color'   => array( 'color', 'Label text colour' ),
		'hx_popt_g_bg'      => array( 'color', 'Label background' ),

		'hx_popt_h_wheel'   => array( 'head', 'Spin wheel' ),
		'hx_popt_w_size'    => array( 'size', 'Wheel text size (px)', array( 10, 26, 17 ) ),
		'hx_popt_w_c1'      => array( 'color', 'Wheel slices colour 1' ),
		'hx_popt_w_c2'      => array( 'color', 'Wheel slices colour 2' ),
		'hx_popt_w_stage'   => array( 'color', 'Background behind the wheel' ),
	);
}

/** Alignment choices (the popup is right-to-left, so Right is the start). */
function hx_popt_aligns() {
	return array( '' => 'Design default', 'right' => 'Right', 'center' => 'Center', 'left' => 'Left' );
}

function hx_popt( $id ) {
	return (string) get_theme_mod( $id, '' );
}

/* Load the chosen Google font (only when it's not the store font). */
add_action(
	'wp_enqueue_scripts',
	function () {
		if ( ! function_exists( 'hx_pop_active' ) || ! hx_pop_active() ) {
			return;
		}
		$f = hx_popt_fonts()[ hx_popt( 'hx_popt_font' ) ] ?? null;
		if ( $f && '' !== $f[2] ) {
			wp_enqueue_style( 'hx-pop-font', 'https://fonts.googleapis.com/css2?family=' . $f[2] . '&display=swap', array(), null ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
		}
	},
	31
);

/* The CSS for the settings that are filled in. */
add_action(
	'wp_head',
	function () {
		if ( ! function_exists( 'hx_pop_active' ) || ! hx_pop_active() || hx_pop_excluded() ) {
			return;
		}
		$p   = '.hx-pop.hx-pop';
		$d   = array();
		$m   = array();
		$add = function ( &$bag, $sel, $decl ) {
			$bag[ $sel ] = ( $bag[ $sel ] ?? '' ) . $decl;
		};
		$px  = function ( $id ) {
			$v = hx_popt( $id );
			return '' === $v ? null : (int) $v;
		};
		$col = function ( $id ) {
			return sanitize_hex_color( hx_popt( $id ) );
		};

		$f = hx_popt_fonts()[ hx_popt( 'hx_popt_font' ) ] ?? null;
		if ( $f && '' !== $f[1] ) {
			$add( $d, "$p, $p .hx-pop__t, $p .hx-pop__btn, $p .hx-pop__no, $p .hx-pop__code, $p .hx-w__t", 'font-family:' . $f[1] . ';' );
		}
		if ( null !== ( $v = $px( 'hx_popt_radius' ) ) ) { // phpcs:ignore Generic.CodeAnalysis.AssignmentInCondition
			$add( $d, "$p .hx-pop__box", 'border-radius:' . $v . 'px;' );
		}
		// Title.
		if ( null !== ( $v = $px( 'hx_popt_t_size' ) ) ) { // phpcs:ignore Generic.CodeAnalysis.AssignmentInCondition
			$add( $d, "$p .hx-pop__t", 'font-size:' . $v . 'px;' );
		}
		if ( null !== ( $v = $px( 'hx_popt_t_size_m' ) ) ) { // phpcs:ignore Generic.CodeAnalysis.AssignmentInCondition
			$add( $m, "$p .hx-pop__t", 'font-size:' . $v . 'px;' );
		}
		if ( isset( hx_popt_weights()[ hx_popt( 'hx_popt_t_weight' ) ] ) && '' !== hx_popt( 'hx_popt_t_weight' ) ) {
			$add( $d, "$p .hx-pop__t", 'font-weight:' . (int) hx_popt( 'hx_popt_t_weight' ) . ';' );
		}
		if ( $c = $col( 'hx_popt_t_color' ) ) { // phpcs:ignore Generic.CodeAnalysis.AssignmentInCondition
			$add( $d, "$p .hx-pop__t", 'color:' . $c . ';' );
		}
		if ( $c = $col( 'hx_popt_t_em' ) ) { // phpcs:ignore Generic.CodeAnalysis.AssignmentInCondition
			$add( $d, "$p .hx-pop__t em", 'color:' . $c . ';' );
		}
		if ( $c = $col( 'hx_popt_t_hl' ) ) { // phpcs:ignore Generic.CodeAnalysis.AssignmentInCondition
			$add( $d, $p, '--pop-hl:' . $c . ';' );
		}
		foreach ( array( 'hx_popt_t_align' => 'd', 'hx_popt_t_align_m' => 'm' ) as $aid => $where ) {
			$a = hx_popt( $aid );
			if ( '' !== $a && isset( hx_popt_aligns()[ $a ] ) ) {
				if ( 'd' === $where ) {
					$add( $d, "$p .hx-pop__t", 'text-align:' . $a . ';' );
				} else {
					$add( $m, "$p .hx-pop__t", 'text-align:' . $a . ';' );
				}
			}
		}
		// Text.
		foreach ( array( 'hx_popt_x_align' => 'd', 'hx_popt_x_align_m' => 'm' ) as $aid => $where ) {
			$a = hx_popt( $aid );
			if ( '' !== $a && isset( hx_popt_aligns()[ $a ] ) ) {
				if ( 'd' === $where ) {
					$add( $d, "$p .hx-pop__txt", 'text-align:' . $a . ';' );
				} else {
					$add( $m, "$p .hx-pop__txt", 'text-align:' . $a . ';' );
				}
			}
		}
		if ( null !== ( $v = $px( 'hx_popt_x_size' ) ) ) { // phpcs:ignore Generic.CodeAnalysis.AssignmentInCondition
			$add( $d, "$p .hx-pop__txt", 'font-size:' . $v . 'px;' );
		}
		if ( null !== ( $v = $px( 'hx_popt_x_size_m' ) ) ) { // phpcs:ignore Generic.CodeAnalysis.AssignmentInCondition
			$add( $m, "$p .hx-pop__txt", 'font-size:' . $v . 'px;' );
		}
		if ( '' !== hx_popt( 'hx_popt_x_weight' ) && isset( hx_popt_weights()[ hx_popt( 'hx_popt_x_weight' ) ] ) ) {
			$add( $d, "$p .hx-pop__txt", 'font-weight:' . (int) hx_popt( 'hx_popt_x_weight' ) . ';' );
		}
		if ( $c = $col( 'hx_popt_x_color' ) ) { // phpcs:ignore Generic.CodeAnalysis.AssignmentInCondition
			$add( $d, "$p .hx-pop__txt", 'color:' . $c . ';opacity:1;' );
		}
		// Main button.
		$btn = "$p .hx-pop__btn";
		if ( null !== ( $v = $px( 'hx_popt_b_size' ) ) ) { // phpcs:ignore Generic.CodeAnalysis.AssignmentInCondition
			$add( $d, $btn, 'font-size:' . $v . 'px;' );
		}
		if ( null !== ( $v = $px( 'hx_popt_b_size_m' ) ) ) { // phpcs:ignore Generic.CodeAnalysis.AssignmentInCondition
			$add( $m, $btn, 'font-size:' . $v . 'px;' );
		}
		if ( '' !== hx_popt( 'hx_popt_b_weight' ) && isset( hx_popt_weights()[ hx_popt( 'hx_popt_b_weight' ) ] ) ) {
			$add( $d, $btn, 'font-weight:' . (int) hx_popt( 'hx_popt_b_weight' ) . ';' );
		}
		if ( $c = $col( 'hx_popt_b_color' ) ) { // phpcs:ignore Generic.CodeAnalysis.AssignmentInCondition
			$add( $d, $btn, 'color:' . $c . ';' );
		}
		if ( $c = $col( 'hx_popt_b_bg' ) ) { // phpcs:ignore Generic.CodeAnalysis.AssignmentInCondition
			$add( $d, $btn, 'background:' . $c . ';' );
		}
		if ( null !== ( $v = $px( 'hx_popt_b_h' ) ) ) { // phpcs:ignore Generic.CodeAnalysis.AssignmentInCondition
			$add( $d, $btn, 'min-height:' . $v . 'px;' );
			$add( $m, $btn, 'min-height:' . $v . 'px;' );
		}
		if ( null !== ( $v = $px( 'hx_popt_b_r' ) ) ) { // phpcs:ignore Generic.CodeAnalysis.AssignmentInCondition
			$add( $d, "$btn, $p .hx-pop__no, $p .hx-pop__code", 'border-radius:' . $v . 'px;' );
		}
		// No thanks.
		if ( null !== ( $v = $px( 'hx_popt_n_size' ) ) ) { // phpcs:ignore Generic.CodeAnalysis.AssignmentInCondition
			$add( $d, "$p .hx-pop__no", 'font-size:' . $v . 'px;' );
		}
		if ( $c = $col( 'hx_popt_n_color' ) ) { // phpcs:ignore Generic.CodeAnalysis.AssignmentInCondition
			$add( $d, "$p .hx-pop__no", 'color:' . $c . ';' );
		}
		if ( $c = $col( 'hx_popt_n_bg' ) ) { // phpcs:ignore Generic.CodeAnalysis.AssignmentInCondition
			$add( $d, "$p .hx-pop__no", 'background:' . $c . ';' );
		}
		// Code box.
		$code = "$p .hx-pop__code, $p .hx-pop__win .hx-pop__code";
		if ( null !== ( $v = $px( 'hx_popt_c_size' ) ) ) { // phpcs:ignore Generic.CodeAnalysis.AssignmentInCondition
			$add( $d, $code, 'font-size:' . $v . 'px;' );
		}
		if ( null !== ( $v = $px( 'hx_popt_c_size_m' ) ) ) { // phpcs:ignore Generic.CodeAnalysis.AssignmentInCondition
			$add( $m, $code, 'font-size:' . $v . 'px;' );
		}
		if ( $c = $col( 'hx_popt_c_color' ) ) { // phpcs:ignore Generic.CodeAnalysis.AssignmentInCondition
			$add( $d, $code, 'color:' . $c . ';' );
		}
		if ( $c = $col( 'hx_popt_c_border' ) ) { // phpcs:ignore Generic.CodeAnalysis.AssignmentInCondition
			$add( $d, $code, 'border-color:' . $c . ';' );
		}
		if ( $c = $col( 'hx_popt_c_bg' ) ) { // phpcs:ignore Generic.CodeAnalysis.AssignmentInCondition
			$add( $d, $code, 'background:' . $c . ';' );
		}
		// Small label.
		if ( null !== ( $v = $px( 'hx_popt_g_size' ) ) ) { // phpcs:ignore Generic.CodeAnalysis.AssignmentInCondition
			$add( $d, "$p .hx-pop__badge", 'font-size:' . $v . 'px;' );
		}
		if ( $c = $col( 'hx_popt_g_color' ) ) { // phpcs:ignore Generic.CodeAnalysis.AssignmentInCondition
			$add( $d, "$p .hx-pop__badge", 'color:' . $c . ';' );
		}
		if ( $c = $col( 'hx_popt_g_bg' ) ) { // phpcs:ignore Generic.CodeAnalysis.AssignmentInCondition
			$add( $d, "$p .hx-pop__badge", 'background:' . $c . ';' );
		}
		// Wheel.
		if ( null !== ( $v = $px( 'hx_popt_w_size' ) ) ) { // phpcs:ignore Generic.CodeAnalysis.AssignmentInCondition
			$add( $d, "$p .hx-w__t", 'font-size:' . $v . 'px;' );
		}
		if ( $c = $col( 'hx_popt_w_c1' ) ) { // phpcs:ignore Generic.CodeAnalysis.AssignmentInCondition
			$add( $d, "$p .hx-w__s--a", 'fill:' . $c . ';' );
			$add( $d, "$p .hx-w__lab--b text", 'fill:' . $c . ';' );
		}
		if ( $c = $col( 'hx_popt_w_c2' ) ) { // phpcs:ignore Generic.CodeAnalysis.AssignmentInCondition
			$add( $d, "$p .hx-w__s--b", 'fill:' . $c . ';' );
			$add( $d, "$p .hx-w__lab--a text, $p .hx-w__lab--c text", 'fill:' . $c . ';' );
		}
		if ( $c = $col( 'hx_popt_w_stage' ) ) { // phpcs:ignore Generic.CodeAnalysis.AssignmentInCondition
			$add( $d, "$p .hx-pop__wheel", 'background-color:' . $c . ';background-image:radial-gradient(rgba(255,255,255,.16) 1.2px,transparent 1.6px),radial-gradient(90% 75% at 50% 40%,color-mix(in srgb,' . $c . ' 70%,#fff) 0%,' . $c . ' 55%,color-mix(in srgb,' . $c . ' 80%,#000) 100%);' );
		}

		$css = '';
		foreach ( $d as $sel => $decl ) {
			$css .= $sel . '{' . $decl . '}';
		}
		if ( $m ) {
			$css .= '@media (max-width:760px){';
			foreach ( $m as $sel => $decl ) {
				$css .= $sel . '{' . $decl . '}';
			}
			$css .= '}';
		}
		if ( $css ) {
			echo '<style id="hx-pop-style">' . $css . '</style>' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput
		}
	},
	103
);

/* Customizer controls (Design tab of "Offer popup"). */
add_action(
	'customize_register',
	function ( $wp_customize ) {
		if ( ! class_exists( 'HX_Popt_Head_Control' ) ) {
			/** A small heading between groups of settings. */
			class HX_Popt_Head_Control extends WP_Customize_Control {
				public $type = 'hx_popt_head';
				public function render_content() {
					echo '<h3 style="margin:18px 0 0;padding:10px 0 0;border-top:1px solid #dcdcde;font-size:14px;font-weight:700">' . esc_html( $this->label ) . '</h3>';
					if ( $this->description ) {
						echo '<p class="description" style="margin:4px 0 0">' . esc_html( $this->description ) . '</p>';
					}
				}
			}
		}
		$sec   = 'hx_popup';
		$prio  = 70;
		$fonts = array_map(
			function ( $f ) {
				return $f[0];
			},
			hx_popt_fonts()
		);
		foreach ( hx_popt_fields() as $id => $f ) {
			$prio++;
			$type = $f[0];
			if ( 'head' === $type ) {
				$wp_customize->add_setting( $id, array( 'default' => '', 'sanitize_callback' => '__return_empty_string' ) );
				$wp_customize->add_control( new HX_Popt_Head_Control( $wp_customize, $id, array( 'label' => $f[1], 'description' => $f[2] ?? '', 'section' => $sec, 'priority' => $prio ) ) );
				continue;
			}
			if ( 'font' === $type || 'weight' === $type || 'align' === $type ) {
				$choices = 'font' === $type ? $fonts : ( 'align' === $type ? hx_popt_aligns() : hx_popt_weights() );
				$wp_customize->add_setting( $id, array( 'default' => '', 'sanitize_callback' => function ( $v ) use ( $choices ) { return array_key_exists( (string) $v, $choices ) ? (string) $v : ''; } ) );
				$wp_customize->add_control( $id, array( 'label' => $f[1], 'section' => $sec, 'type' => 'select', 'choices' => $choices, 'priority' => $prio ) );
				continue;
			}
			if ( 'color' === $type ) {
				$wp_customize->add_setting( $id, array( 'default' => '', 'sanitize_callback' => 'sanitize_hex_color' ) );
				$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, $id, array( 'label' => $f[1], 'section' => $sec, 'priority' => $prio ) ) );
				continue;
			}
			// Size: slider that can go back to "design default" (empty).
			list( $min, $max, $show ) = $f[2];
			$wp_customize->add_setting(
				$id,
				array(
					'default'           => '',
					'sanitize_callback' => function ( $v ) use ( $min, $max ) {
						return '' === (string) $v ? '' : (string) max( $min, min( $max, (int) $v ) );
					},
				)
			);
			if ( class_exists( 'HX_Range_Control' ) ) {
				$c             = new HX_Range_Control( $wp_customize, $id, array( 'label' => $f[1], 'section' => $sec, 'priority' => $prio, 'settings' => $id, 'input_attrs' => array( 'min' => $min, 'max' => $max, 'step' => 1 ) ) );
				$c->hx_default = $show;
				$c->hx_empty   = true;
				$wp_customize->add_control( $c );
			} else {
				$wp_customize->add_control( $id, array( 'label' => $f[1], 'section' => $sec, 'type' => 'number', 'priority' => $prio, 'input_attrs' => array( 'min' => $min, 'max' => $max ) ) );
			}
		}
	},
	41
);

/* All of these belong to the Design tab. */
add_filter(
	'hx_cui_design_ids',
	function ( $ids ) {
		return array_merge( (array) $ids, array_keys( hx_popt_fields() ) );
	}
);

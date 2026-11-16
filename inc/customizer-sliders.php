<?php
/**
 * Slider controls for every number option in the Customizer (theme sections).
 */
defined( 'ABSPATH' ) || exit;

function hx_slider_defaults() {
	return array(
		'pp_max_width' => 1400, 'pp_pad_x_d' => 80, 'pp_pad_x_m' => 24, 'pp_pad_top_d' => 32, 'pp_pad_top_m' => 24,
		'pp_img_col' => 51, 'pp_img_top_d' => 0, 'pp_img_top_m' => 0, 'pp_gap' => 56, 'pp_info_max' => 600, 'pp_img_h_d' => 760, 'pp_img_h_m' => 500,
		'pp_img_radius' => 14, 'pp_img_pad' => 0, 'pp_img_space' => 10, 'pp_t_size_d' => 30, 'pp_t_size_m' => 22,
		'pp_t_space' => 0, 'pp_p_size_d' => 40, 'pp_p_size_m' => 32, 'pp_p_space' => 22, 'pp_f_title_size' => 22,
		'pp_f_border_w' => 2, 'pp_f_radius' => 16, 'pp_f_pad_d' => 28, 'pp_f_pad_m' => 18, 'pp_f_gap' => 14,
		'pp_f_field_h' => 54, 'pp_f_field_r' => 10, 'pp_f_btn_size' => 21, 'pp_f_btn_h' => 58, 'pp_f_btn_r' => 12,
		'pp_d_size_d' => 19, 'pp_d_size_m' => 17, 'pp_d_lead' => 22, 'pp_d_space' => 8,
	);
}

add_action( 'customize_register', function ( $wp_customize ) {
	if ( ! class_exists( 'HX_Range_Control' ) ) {
		class HX_Range_Control extends WP_Customize_Control {
			public $type = 'hx_range';
			public $hx_default = 0;
			public $hx_empty = false;
			public function render_content() {
				$a    = wp_parse_args( $this->input_attrs, array( 'min' => 0, 'max' => 100, 'step' => 1 ) );
				$val  = $this->value();
				$set  = '' !== (string) $val && null !== $val;
				$show = $set ? $val : $this->hx_default;
				$unit = false !== stripos( $this->label, '%' ) ? '%' : 'px';
				$lab  = preg_replace( '/\s*\((px|%)[^)]*\)/', '', $this->label );
				?>
				<div class="hx-rc<?php echo $set ? ' is-set' : ''; ?>" data-setting="<?php echo esc_attr( $this->setting->id ); ?>" data-default="<?php echo esc_attr( $this->hx_default ); ?>" data-empty="<?php echo $this->hx_empty ? '1' : '0'; ?>">
					<div class="hx-rc__top">
						<span class="customize-control-title"><?php echo esc_html( $lab ); ?></span>
						<span class="hx-rc__val"><span class="hx-rc__num"><?php echo esc_html( $show ); ?></span><?php echo esc_html( $unit ); ?><em class="hx-rc__def"><?php echo $set ? '' : ' (default)'; ?></em></span>
					</div>
					<div class="hx-rc__row">
						<input type="range" class="hx-rc__range" min="<?php echo esc_attr( $a['min'] ); ?>" max="<?php echo esc_attr( $a['max'] ); ?>" step="<?php echo esc_attr( $a['step'] ); ?>" value="<?php echo esc_attr( $show ); ?>">
						<button type="button" class="hx-rc__reset" title="Reset to default" aria-label="Reset to default">&#8634;</button>
					</div>
				</div>
				<?php
			}
		}
	}
}, 1 );

/* Swap the theme's number boxes for sliders. */
add_action( 'customize_register', function ( $wp_customize ) {
	$defs = hx_slider_defaults();
	foreach ( $wp_customize->controls() as $id => $control ) {
		if ( 'number' !== $control->type ) {
			continue;
		}
		if ( 0 !== strpos( $id, 'pp_' ) && 0 !== strpos( $id, 'hx_' ) ) {
			continue;
		}
		$setting = $wp_customize->get_setting( $id );
		$attrs   = $control->input_attrs;
		$empty   = 0 === strpos( $id, 'pp_' );
		if ( isset( $defs[ $id ] ) ) {
			$def = $defs[ $id ];
		} elseif ( $setting && '' !== (string) $setting->default ) {
			$def = (int) $setting->default;
		} else {
			$def = (int) ( ( ( $attrs['min'] ?? 0 ) + ( $attrs['max'] ?? 100 ) ) / 2 );
		}
		$args = array(
			'label'       => $control->label,
			'description' => $control->description,
			'section'     => $control->section,
			'priority'    => $control->priority,
			'input_attrs' => $attrs,
			'settings'    => $id,
		);
		$wp_customize->remove_control( $id );
		$new             = new HX_Range_Control( $wp_customize, $id, $args );
		$new->hx_default = $def;
		$new->hx_empty   = $empty;
		$wp_customize->add_control( $new );
	}
}, 999 );

add_action( 'customize_controls_print_styles', function () {
	?>
	<style>
	.hx-rc{padding:2px 0 6px}
	.hx-rc__top{display:flex;align-items:baseline;justify-content:space-between;gap:8px}
	.hx-rc__top .customize-control-title{margin:0;flex:1}
	.hx-rc__val{font-weight:600;color:#1d2327;white-space:nowrap;font-size:12px;background:#f0f0f1;border-radius:4px;padding:2px 6px}
	.hx-rc__def{font-style:normal;color:#8c8f94;font-weight:400}
	.hx-rc.is-set .hx-rc__val{background:#e5efff;color:#2271b1}
	.hx-rc__row{display:flex;align-items:center;gap:10px;margin-top:10px}
	.hx-rc__range{flex:1;-webkit-appearance:none;appearance:none;height:6px;border-radius:999px;background:linear-gradient(to right,#4054e6 0,#4054e6 var(--p,50%),#ddd var(--p,50%),#ddd 100%);outline:none;margin:0}
	.hx-rc__range::-webkit-slider-thumb{-webkit-appearance:none;width:20px;height:20px;border-radius:50%;background:#4054e6;border:0;box-shadow:0 1px 4px rgba(0,0,0,.25);cursor:pointer}
	.hx-rc__range::-moz-range-thumb{width:20px;height:20px;border-radius:50%;background:#4054e6;border:0;box-shadow:0 1px 4px rgba(0,0,0,.25);cursor:pointer}
	.hx-rc:not(.is-set) .hx-rc__range{filter:grayscale(1);opacity:.7}
	.hx-rc__reset{border:1px solid #dcdcde;background:#fff;border-radius:50%;width:26px;height:26px;line-height:22px;padding:0;cursor:pointer;color:#50575e;font-size:14px}
	.hx-rc__reset:hover{color:#2271b1;border-color:#2271b1}
	</style>
	<?php
} );

add_action( 'customize_controls_print_footer_scripts', function () {
	?>
	<script>
	( function ( api, $ ) {
		function paint( range ) {
			var min = +range.min, max = +range.max, v = +range.value;
			range.style.setProperty( '--p', ( ( v - min ) / ( max - min || 1 ) * 100 ) + '%' );
		}
		function init( el ) {
			var $el = $( el ), id = $el.data( 'setting' ), def = $el.data( 'default' ), canEmpty = $el.data( 'empty' ) === 1 || $el.data( 'empty' ) === '1';
			var range = $el.find( '.hx-rc__range' )[0], num = $el.find( '.hx-rc__num' ), tag = $el.find( '.hx-rc__def' );
			if ( ! range || $el.data( 'ready' ) ) { return; }
			$el.data( 'ready', 1 );
			paint( range );
			var t;
			range.addEventListener( 'input', function () {
				num.text( range.value ); tag.text( '' ); $el.addClass( 'is-set' ); paint( range );
				clearTimeout( t );
				t = setTimeout( function () { api( id ).set( String( range.value ) ); }, 250 );
			} );
			$el.find( '.hx-rc__reset' ).on( 'click', function () {
				range.value = def; num.text( def ); paint( range );
				if ( canEmpty ) { $el.removeClass( 'is-set' ); tag.text( ' (default)' ); api( id ).set( '' ); }
				else { api( id ).set( String( def ) ); }
			} );
		}
		api.bind( 'ready', function () {
			$( '.hx-rc' ).each( function () { init( this ); } );
			$( document ).on( 'expanded', function () { $( '.hx-rc' ).each( function () { init( this ); } ); } );
			setInterval( function () { $( '.hx-rc' ).each( function () { init( this ); } ); }, 1500 );
		} );
	} )( wp.customize, jQuery );
	</script>
	<?php
} );
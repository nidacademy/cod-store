<?php
/**
 * Product page design controls: Appearance > Customize > Product page.
 * Every option is optional: leave it empty to keep the theme's default.
 */
defined( 'ABSPATH' ) || exit;

function hx_pp_fields() {
	$fonts   = array( '' => 'Default', 'cairo' => 'Cairo (Arabic)', 'assistant' => 'Assistant', 'system' => 'System font', 'tajawal-like' => 'Tahoma / Segoe UI' );
	$weights = array( '' => 'Default', '400' => 'Regular', '500' => 'Medium', '600' => 'Semi-bold', '700' => 'Bold', '800' => 'Extra-bold' );
	$align   = array( '' => 'Default', 'right' => 'Right', 'center' => 'Center', 'left' => 'Left' );
	$show    = array( '' => 'Show (default)', 'hide' => 'Hide' );
	// key => [section, label, type, choices|range, css rule(s)]
	// css: array of [media, selector, declaration with {v}]  media: a=all d=desktop(>=990) t=tablet+(>=750) m=mobile(<750)
	return array(
		/* Layout */
		'pp_max_width'   => array( 'layout', 'Page max width (px)', 'number', array( 900, 1800 ), array( array( 'a', '.hx-product.hx-wrap', 'max-width:{v}px' ) ) ),
		'pp_pad_x_d'     => array( 'layout', 'Side padding - desktop (px)', 'number', array( 0, 160 ), array( array( 't', '.hx-product.hx-wrap', 'padding-left:{v}px;padding-right:{v}px' ) ) ),
		'pp_pad_x_m'     => array( 'layout', 'Side padding - mobile (px)', 'number', array( 0, 60 ), array( array( 'm', '.hx-product.hx-wrap', 'padding-left:{v}px;padding-right:{v}px' ) ) ),
		'pp_pad_top_d'   => array( 'layout', 'Space above content - desktop (px)', 'number', array( 0, 160 ), array( array( 'd', '.hx-product', 'padding-top:{v}px' ) ) ),
		'pp_pad_top_m'   => array( 'layout', 'Space above content - mobile (px)', 'number', array( 0, 100 ), array( array( 'm', '.hx-product', 'padding-top:{v}px' ) ) ),
		'pp_img_col'     => array( 'layout', 'Image column width - desktop (%)', 'number', array( 30, 70 ), array( array( 'd', '.hx-product', 'grid-template-columns:minmax(0,{v}fr) minmax(0,{rest}fr)' ) ) ),
		'pp_gap'         => array( 'layout', 'Space between image and info - desktop (px)', 'number', array( 0, 160 ), array( array( 'd', '.hx-product', 'column-gap:{v}px' ) ) ),
		'pp_info_max'    => array( 'layout', 'Info column max width - desktop (px)', 'number', array( 320, 1000 ), array( array( 'd', '.hx-product__info', 'max-width:{v}px' ) ) ),
		'pp_img_side'    => array( 'layout', 'Image position - desktop', 'select', array( '' => 'Left (default)', 'right' => 'Right' ), array() ),
		'pp_sticky'      => array( 'layout', 'Image stays visible while scrolling - desktop', 'select', array( '' => 'Yes (default)', 'no' => 'No' ), array() ),

		/* Images */
		'pp_img_top_d'   => array( 'images', 'Space above the image - desktop (px)', 'number', array( 0, 160 ), array( array( 't', '.hx-gallery', 'margin-top:{v}px' ) ) ),
		'pp_img_top_m'   => array( 'images', 'Space above the image - mobile (px)', 'number', array( 0, 120 ), array( array( 'm', '.hx-gallery', 'margin-top:{v}px' ) ) ),
		'pp_img_h_d'     => array( 'images', 'Image max height - desktop (px)', 'number', array( 200, 1200 ), array( array( 't', '.hx-gallery__slide img', 'max-height:{v}px' ) ) ),
		'pp_img_h_m'     => array( 'images', 'Image max height - mobile (px)', 'number', array( 150, 900 ), array( array( 'm', '.hx-gallery__slide img', 'max-height:{v}px' ) ) ),
		'pp_img_fit'     => array( 'images', 'Image fit', 'select', array( '' => 'Show whole image (default)', 'cover' => 'Fill the frame (may crop)' ), array( array( 'a', '.hx-gallery__slide img', 'object-fit:{v};aspect-ratio:1/1' ) ) ),
		'pp_img_radius'  => array( 'images', 'Image corner radius (px)', 'number', array( 0, 60 ), array( array( 'a', '.hx-gallery__track', 'border-radius:{v}px' ) ) ),
		'pp_img_pad'     => array( 'images', 'Padding inside the image frame (px)', 'number', array( 0, 80 ), array( array( 'a', '.hx-gallery__slide', 'padding:{v}px;box-sizing:border-box' ) ) ),
		'pp_img_out_d'   => array( 'images', 'Padding outside the image frame - desktop (px)', 'number', array( 0, 120 ), array( array( 't', '.hx-gallery', 'padding:{v}px;box-sizing:border-box' ) ) ),
		'pp_img_out_m'   => array( 'images', 'Padding outside the image frame - mobile (px)', 'number', array( 0, 60 ), array( array( 'm', '.hx-gallery', 'padding:{v}px;box-sizing:border-box' ) ) ),
		'pp_img_bg'      => array( 'images', 'Image frame background', 'color', null, array( array( 'a', '.hx-gallery__track', 'background:{v}' ) ) ),
		'pp_img_border'  => array( 'images', 'Image frame border color', 'color', null, array( array( 'a', '.hx-gallery__track', 'border:1px solid {v}' ) ) ),
		'pp_img_nav'     => array( 'images', 'Arrows and image counter (1/4) - shown when thumbnails are hidden', 'select', $show, array( array( 'a', '.hx-gallery__nav', 'display:none' ) ) ),
		'pp_img_space'   => array( 'images', 'Space under the image (px)', 'number', array( 0, 80 ), array( array( 'a', '.hx-gallery__nav', 'margin-top:{v}px' ), array( 'a', '.hx-gallery__thumbs', 'margin-top:{v}px' ) ) ),
		'pp_th_show'     => array( 'images', 'Thumbnails under the image', 'select', $show, array() ),
		'pp_th_size_d'   => array( 'images', 'Thumbnail size - desktop (px)', 'number', array( 40, 200 ), array( array( 't', '.hx-gallery__thumb', 'width:{v}px;height:{v}px' ) ) ),
		'pp_th_size_m'   => array( 'images', 'Thumbnail size - mobile (px)', 'number', array( 40, 160 ), array( array( 'm', '.hx-gallery__thumb', 'width:{v}px;height:{v}px' ) ) ),
		'pp_th_gap'      => array( 'images', 'Space between thumbnails (px)', 'number', array( 0, 40 ), array( array( 'a', '.hx-gallery__thumbs', 'gap:{v}px' ) ) ),
		'pp_th_radius'   => array( 'images', 'Thumbnail corner radius (px)', 'number', array( 0, 60 ), array( array( 'a', '.hx-gallery__thumb', 'border-radius:{v}px' ) ) ),
		'pp_th_active'   => array( 'images', 'Selected thumbnail border color', 'color', null, array( array( 'a', '.hx-gallery__thumb.is-active', 'border-color:{v}' ) ) ),

		/* Title */
		'pp_t_font'      => array( 'title', 'Title font', 'select', $fonts, array( array( 'a', '.hx-product__title', 'font-family:{font}' ) ) ),
		'pp_t_size_d'    => array( 'title', 'Title size - desktop (px)', 'number', array( 14, 72 ), array( array( 't', '.hx-product__title', 'font-size:{v}px' ) ) ),
		'pp_t_size_m'    => array( 'title', 'Title size - mobile (px)', 'number', array( 14, 48 ), array( array( 'm', '.hx-product__title', 'font-size:{v}px' ) ) ),
		'pp_t_weight'    => array( 'title', 'Title weight', 'select', $weights, array( array( 'a', '.hx-product__title', 'font-weight:{v}' ) ) ),
		'pp_t_color'     => array( 'title', 'Title color', 'color', null, array( array( 'a', '.hx-product__title', 'color:{v}' ) ) ),
		'pp_t_align'     => array( 'title', 'Title alignment - desktop', 'select', $align, array( array( 't', '.hx-product__title', 'text-align:{v}' ) ) ),
		'pp_t_align_m'   => array( 'title', 'Title alignment - mobile', 'select', $align, array( array( 'm', '.hx-product__title', 'text-align:{v}' ) ) ),
		'pp_t_space'     => array( 'title', 'Space under the title (px)', 'number', array( 0, 60 ), array( array( 'a', '.hx-product__title', 'margin-bottom:{v}px' ) ) ),

		/* Price */
		'pp_p_size_d'    => array( 'price', 'Price size - desktop (px)', 'number', array( 16, 80 ), array( array( 't', '.hx-pp__num', 'font-size:{v}px' ) ) ),
		'pp_p_size_m'    => array( 'price', 'Price size - mobile (px)', 'number', array( 16, 60 ), array( array( 'm', '.hx-pp__num', 'font-size:{v}px' ) ) ),
		'pp_p_color'     => array( 'price', 'Price color', 'color', null, array( array( 'a', '.hx-pp__now', 'color:{v}' ) ) ),
		'pp_p_old'       => array( 'price', 'Old (crossed-out) price', 'select', $show, array( array( 'a', '.hx-pp__old', 'display:none' ) ) ),
		'pp_p_badge'     => array( 'price', 'Discount badge', 'select', $show, array( array( 'a', '.hx-pp__badge', 'display:none' ) ) ),
		'pp_p_align'     => array( 'price', 'Price alignment', 'select', array( '' => 'Same as the title (default)', 'flex-start' => 'Right', 'center' => 'Center', 'flex-end' => 'Left' ), array( array( 'a', '.hx-pp', 'justify-content:{v}' ) ) ),
		'pp_p_space'     => array( 'price', 'Space under the price (px)', 'number', array( 0, 80 ), array( array( 'a', '.hx-pp', 'margin-bottom:{v}px' ) ) ),

		/* Order form */
		'pp_f_title_txt' => array( 'form', 'Form title text', 'text', null, array() ),
		'pp_f_sub_txt'   => array( 'form', 'Form subtitle text (leave empty for default)', 'text', null, array() ),
		'pp_f_sub'       => array( 'form', 'Form subtitle', 'select', $show, array( array( 'a', '.hx-order__sub', 'display:none' ) ) ),
		'pp_f_title_size'=> array( 'form', 'Form title size (px)', 'number', array( 12, 40 ), array( array( 'a', '.hx-order__title', 'font-size:{v}px' ) ) ),
		'pp_f_title_col' => array( 'form', 'Form title color', 'color', null, array( array( 'a', '.hx-order__title', 'color:{v}' ) ) ),
		'pp_f_bg'        => array( 'form', 'Form background', 'color', null, array( array( 'a', '.hx-order', 'background:{v}' ) ) ),
		'pp_f_border_c'  => array( 'form', 'Form border color', 'color', null, array( array( 'a', '.hx-order', 'border-color:{v}' ) ) ),
		'pp_f_border_w'  => array( 'form', 'Form border width (px)', 'number', array( 0, 6 ), array( array( 'a', '.hx-order', 'border-width:{v}px;border-style:solid' ) ) ),
		'pp_f_radius'    => array( 'form', 'Form corner radius (px)', 'number', array( 0, 40 ), array( array( 'a', '.hx-order', 'border-radius:{v}px' ) ) ),
		'pp_f_pad_d'     => array( 'form', 'Form inner padding - desktop (px)', 'number', array( 0, 80 ), array( array( 't', '.hx-order', 'padding:{v}px' ) ) ),
		'pp_f_pad_m'     => array( 'form', 'Form inner padding - mobile (px)', 'number', array( 0, 60 ), array( array( 'm', '.hx-order', 'padding:{v}px' ) ) ),
		'pp_f_gap'       => array( 'form', 'Space between fields (px)', 'number', array( 0, 40 ), array( array( 'a', '.hx-order', 'gap:{v}px' ) ) ),
		'pp_f_field_h'   => array( 'form', 'Field height (px)', 'number', array( 36, 80 ), array( array( 'a', '.hx-order .hx-field', 'height:{v}px' ) ) ),
		'pp_f_field_r'   => array( 'form', 'Field corner radius (px)', 'number', array( 0, 30 ), array( array( 'a', '.hx-order .hx-field__icon', 'border-start-start-radius:{v}px;border-end-start-radius:{v}px' ), array( 'a', '.hx-order .hx-field__input', 'border-start-end-radius:{v}px;border-end-end-radius:{v}px' ) ) ),
		'pp_f_btn_txt'   => array( 'form', 'Order button text', 'text', null, array() ),
		'pp_f_btn_bg'    => array( 'form', 'Order button color', 'color', null, array( array( 'a', '.hx-order__btn', 'background:{v}' ) ) ),
		'pp_f_btn_col'   => array( 'form', 'Order button text color', 'color', null, array( array( 'a', '.hx-order__btn', 'color:{v}' ) ) ),
		'pp_f_btn_size'  => array( 'form', 'Order button text size (px)', 'number', array( 12, 36 ), array( array( 'a', '.hx-order__btn', 'font-size:{v}px' ) ) ),
		'pp_f_btn_h'     => array( 'form', 'Order button height (px)', 'number', array( 36, 90 ), array( array( 'a', '.hx-order__btn', 'min-height:{v}px' ) ) ),
		'pp_f_btn_r'     => array( 'form', 'Order button corner radius (px)', 'number', array( 0, 50 ), array( array( 'a', '.hx-order__btn', 'border-radius:{v}px' ) ) ),
		'pp_f_btn_anim'  => array( 'form', 'Order button shake animation', 'select', array( '' => 'On (default)', 'off' => 'Off' ), array( array( 'a', '.hx-order__btn', 'animation:none' ) ) ),
		'pp_order_mode'  => array( 'form', 'Order buttons', 'select', array( '' => 'Activate form (default)', 'whatsapp' => 'Activate WhatsApp', 'both' => 'Activate both' ), array() ),
		'pp_wa_number'   => array( 'form', 'WhatsApp number (with country code, e.g. 212612345678) - needed for "Activate WhatsApp" and "Activate both"', 'text', null, array() ),
		'pp_wa_txt'      => array( 'form', 'WhatsApp button text (default: واتساب)', 'text', null, array() ),
		'pp_f_trust'     => array( 'form', 'Trust line under the button', 'select', $show, array( array( 'a', '.hx-order__trust', 'display:none' ) ) ),

		/* Description */
		'pp_d_show'      => array( 'desc', 'Product description', 'select', $show, array( array( 'a', '.hx-product__desc', 'display:none' ) ) ),
		'pp_d_font'      => array( 'desc', 'Description font', 'select', $fonts, array( array( 'a', '.hx-product__desc', 'font-family:{font}' ) ) ),
		'pp_d_size_d'    => array( 'desc', 'Text size - desktop (px)', 'number', array( 12, 30 ), array( array( 't', '.hx-product__desc', 'font-size:{v}px' ) ) ),
		'pp_d_size_m'    => array( 'desc', 'Text size - mobile (px)', 'number', array( 12, 26 ), array( array( 'm', '.hx-product__desc', 'font-size:{v}px' ) ) ),
		'pp_d_lead'      => array( 'desc', 'First paragraph size (px)', 'number', array( 12, 36 ), array( array( 'a', '.hx-product__desc > p:first-child', 'font-size:{v}px' ) ) ),
		'pp_d_color'     => array( 'desc', 'Text color', 'color', null, array( array( 'a', '.hx-product__desc', 'color:{v}' ) ) ),
		'pp_d_lh'        => array( 'desc', 'Line spacing', 'select', array( '' => 'Default', '1.5' => 'Tight (1.5)', '1.7' => 'Normal (1.7)', '1.9' => 'Comfortable (1.9)', '2.2' => 'Airy (2.2)' ), array( array( 'a', '.hx-product__desc', 'line-height:{v}' ) ) ),
		'pp_d_align'     => array( 'desc', 'Text alignment', 'select', $align, array( array( 'a', '.hx-product__desc', 'text-align:{v}' ) ) ),
		'pp_d_space'     => array( 'desc', 'Space above the description (px)', 'number', array( 0, 100 ), array( array( 'a', '.hx-product__desc', 'margin-top:{v}px' ) ) ),
	);
}

function hx_pp_val( $key ) {
	$v = get_theme_mod( $key, '' );
	return ( null === $v || false === $v ) ? '' : $v;
}

function hx_pp_text( $key, $default ) {
	$v = trim( (string) hx_pp_val( $key ) );
	return '' !== $v ? $v : $default;
}

add_action( 'customize_register', function ( $wp_customize ) {
	$wp_customize->add_panel( 'hx_product_page', array(
		'title'       => 'Product page',
		'description' => 'Design the product page. Leave any option empty to keep the default look.',
		'priority'    => 27,
	) );
	$sections = array(
		'layout' => 'Layout & spacing',
		'images' => 'Images',
		'title'  => 'Title',
		'price'  => 'Price',
		'form'   => 'Order form & button',
		'desc'   => 'Description',
	);
	$p = 10;
	foreach ( $sections as $id => $title ) {
		$wp_customize->add_section( 'hx_pp_' . $id, array( 'title' => $title, 'panel' => 'hx_product_page', 'priority' => $p += 10 ) );
	}
	$i = 0;
	foreach ( hx_pp_fields() as $key => $f ) {
		list( $sec, $label, $type, $opt ) = $f;
		if ( 'color' === $type ) {
			$wp_customize->add_setting( $key, array( 'default' => '', 'sanitize_callback' => 'sanitize_hex_color' ) );
			$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, $key, array( 'label' => $label, 'section' => 'hx_pp_' . $sec, 'priority' => ++$i ) ) );
			continue;
		}
		if ( 'number' === $type ) {
			$wp_customize->add_setting( $key, array(
				'default'           => '',
				'sanitize_callback' => function ( $v ) use ( $opt ) {
					if ( '' === trim( (string) $v ) ) {
						return '';
					}
					return (string) max( $opt[0], min( $opt[1], (int) $v ) );
				},
			) );
			$wp_customize->add_control( $key, array(
				'label'       => $label,
				'section'     => 'hx_pp_' . $sec,
				'type'        => 'number',
				'input_attrs' => array( 'min' => $opt[0], 'max' => $opt[1], 'step' => 1, 'placeholder' => 'Default' ),
				'priority'    => ++$i,
			) );
			continue;
		}
		if ( 'select' === $type ) {
			$wp_customize->add_setting( $key, array(
				'default'           => '',
				'sanitize_callback' => function ( $v ) use ( $opt ) { return array_key_exists( (string) $v, $opt ) ? (string) $v : ''; },
			) );
			$wp_customize->add_control( $key, array( 'label' => $label, 'section' => 'hx_pp_' . $sec, 'type' => 'select', 'choices' => $opt, 'priority' => ++$i ) );
			continue;
		}
		$wp_customize->add_setting( $key, array( 'default' => '', 'sanitize_callback' => 'sanitize_text_field' ) );
		$wp_customize->add_control( $key, array( 'label' => $label, 'section' => 'hx_pp_' . $sec, 'type' => 'text', 'priority' => ++$i ) );
	}
} );

add_action( 'wp_head', function () {
	if ( ! ( function_exists( 'is_product' ) && is_product() ) ) {
		return;
	}
	$fonts = array(
		'cairo'        => '"Cairo", var(--hx-font)',
		'assistant'    => '"Assistant", var(--hx-font)',
		'system'       => 'system-ui, -apple-system, "Segoe UI", Roboto, sans-serif',
		'tajawal-like' => 'Tahoma, "Segoe UI", Arial, sans-serif',
	);
	$mq     = array( 'a' => '', 'd' => '(min-width: 990px)', 't' => '(min-width: 750px)', 'm' => '(max-width: 749px)' );
	$out    = array( 'a' => '', 'd' => '', 't' => '', 'm' => '' );
	$scope  = 'body.single-product ';
	foreach ( hx_pp_fields() as $key => $f ) {
		$v = hx_pp_val( $key );
		if ( '' === $v || empty( $f[4] ) ) {
			continue;
		}
		if ( 'pp_t_font' === $key || 'pp_d_font' === $key ) {
			if ( ! isset( $fonts[ $v ] ) ) {
				continue;
			}
		}
		foreach ( $f[4] as $rule ) {
			$decl = str_replace(
				array( '{v}', '{rest}', '{font}' ),
				array( $v, (string) ( 100 - (int) $v ), $fonts[ $v ] ?? '' ),
				$rule[2]
			);
			$out[ $rule[0] ] .= $scope . $rule[1] . '{' . $decl . '}';
		}
	}
	if ( 'right' === hx_pp_val( 'pp_img_side' ) ) {
		$out['d'] .= $scope . '.hx-product__media{order:2}' . $scope . '.hx-product__info{order:1;justify-self:start}';
		$col = hx_pp_val( 'pp_img_col' );
		if ( '' !== $col ) {
			$out['d'] .= $scope . '.hx-product{grid-template-columns:minmax(0,' . ( 100 - (int) $col ) . 'fr) minmax(0,' . (int) $col . 'fr)}';
		} else {
			$out['d'] .= $scope . '.hx-product{grid-template-columns:minmax(0,1fr) minmax(0,1.05fr)}';
		}
	}
	if ( 'no' === hx_pp_val( 'pp_sticky' ) ) {
		$out['d'] .= $scope . '.hx-product__media{position:static}';
	}
	if ( 'cover' === hx_pp_val( 'pp_img_fit' ) ) {
		$out['a'] .= $scope . '.hx-gallery__slide img{height:auto;width:100%}';
	}
	if ( 'center' === hx_pp_val( 'pp_t_align_m' ) ) {
		$out['m'] .= $scope . '.hx-product__title{text-wrap:balance}';
	}
	// Price follows the title alignment (per device) unless "Price alignment" is set.
	if ( '' === hx_pp_val( 'pp_p_align' ) ) {
		$map = array( 'right' => 'flex-start', 'center' => 'center', 'left' => 'flex-end' );
		foreach ( array( 't' => 'pp_t_align', 'm' => 'pp_t_align_m' ) as $mq_k => $al_k ) {
			$al = hx_pp_val( $al_k );
			if ( isset( $map[ $al ] ) ) {
				$out[ $mq_k ] .= $scope . '.hx-pp{justify-content:' . $map[ $al ] . '}';
			}
		}
	}
	$css = $out['a'];
	foreach ( array( 't', 'd', 'm' ) as $k ) {
		if ( '' !== $out[ $k ] ) {
			$css .= '@media ' . $mq[ $k ] . '{' . $out[ $k ] . '}';
		}
	}
	if ( '' !== $css ) {
		echo '<style id="hx-product-page">' . $css . '</style>' . "\n";
	}
}, 101 );
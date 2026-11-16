<?php
defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/inc/hx-lang-stub.php';
require_once __DIR__ . '/inc/hx-product-parts.php';
require_once __DIR__ . '/inc/hx-offers.php';
require_once __DIR__ . '/inc/hx-order-modes.php';
require_once __DIR__ . '/inc/hx-currency.php';
require_once __DIR__ . '/inc/hx-menu-switch.php';
require_once __DIR__ . '/inc/hx-social.php';
require_once get_template_directory() . '/inc/hx-blog.php';
require_once get_template_directory() . '/inc/hx-variations.php';
require_once get_template_directory() . '/inc/hx-search.php';
require_once get_template_directory() . '/inc/hx-hero.php';
require_once get_template_directory() . '/inc/hx-updater.php';

define( 'HX_VER', '5.7.7' );
define( 'HX_DEFAULT_ANNOUNCEMENT', 'إلى ما عجبكش المنتوج، تقدر ترجّعو لينا خلال 7 أيام من تاريخ التوصيل.' );

add_action( 'after_setup_theme', function () {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'woocommerce' );
	add_theme_support( 'custom-logo', array( 'height' => 120, 'width' => 360, 'flex-height' => true, 'flex-width' => true ) );
	add_theme_support( 'html5', array( 'style', 'script' ) );
} );

add_action( 'wp_enqueue_scripts', function () {
	wp_enqueue_style( 'hx-fonts', get_theme_file_uri( 'assets/fonts/fonts.css' ), array(), HX_VER );
	wp_enqueue_style( 'hx', get_theme_file_uri( 'assets/hx.css' ), array( 'hx-fonts' ), HX_VER );
	if ( function_exists( 'is_product' ) && is_product() ) {
		wp_enqueue_script( 'hx-product', get_theme_file_uri( 'assets/product.js' ), array(), HX_VER, true );
		wp_localize_script( 'hx-product', 'HX', array( 'ajax' => admin_url( 'admin-ajax.php' ), 'msgInvalid' => hx_t( 'المرجو ملء جميع الخانات بشكل صحيح' ), 'msgError' => hx_t( 'حدث خطأ، المرجو المحاولة مرة أخرى' ) ) );
	}
}, 20 );

add_filter( 'woocommerce_enqueue_styles', '__return_empty_array' );

function hx_money( $amount, $currency_first = true ) {
	$amount = (float) $amount;
	$num    = ( floor( $amount ) == $amount ) ? number_format( $amount, 0, '.', ' ' ) : number_format( $amount, 2, '.', ' ' );
	return $currency_first ? 'درهم ' . $num : $num . ' درهم';
}

function hx_prices( $product ) {
	if ( $product->is_type( 'variable' ) ) {
		$price   = (float) $product->get_variation_price( 'min' );
		$regular = (float) $product->get_variation_regular_price( 'min' );
	} else {
		$price   = (float) $product->get_price();
		$regular = (float) $product->get_regular_price();
	}
	return array( $price, ( $regular > $price ) ? $regular : 0 );
}

function hx_price_html( $product, $class = '' ) {
	list( $price, $compare ) = hx_prices( $product );
	$out = '<div class="hx-price ' . esc_attr( $class ) . '">';
	if ( $compare ) {
		$out .= '<s class="hx-price__compare" data-hx-compare>' . hx_m( $compare ) . ' ' . hx_c() . '</s>';
	} else {
		$out .= '<s class="hx-price__compare" data-hx-compare hidden></s>';
	}
	$out .= '<span class="hx-price__now" data-hx-price>' . hx_c() . ' ' . hx_m( $price ) . '</span>';
	return $out . '</div>';
}

function hx_card( $product ) {
	$link = get_permalink( $product->get_id() );
	$opt  = hx_card_opts();
	?>
	<li class="hx-card<?php echo $opt['button'] ? ' hx-card--btn' : ''; ?>" data-pid="<?php echo (int) $product->get_id(); ?>">
		<a class="hx-card__link" href="<?php echo esc_url( $link ); ?>">
			<div class="hx-card__media">
				<?php
				if ( $opt['badge'] ) {
					echo hx_card_badge( $product );
				}
				echo $product->get_image_id()
					? wp_get_attachment_image( $product->get_image_id(), 'woocommerce_thumbnail', false, array( 'loading' => 'lazy', 'alt' => esc_attr( $product->get_name() ) ) )
					: wc_placeholder_img( 'woocommerce_thumbnail' );
				?>
			</div>
			<div class="hx-card__body" dir="rtl">
				<?php
				if ( $opt['category'] ) {
					$terms = get_the_terms( $product->get_id(), 'product_cat' );
					if ( $terms && ! is_wp_error( $terms ) ) {
						echo '<span class="hx-card__cat">' . esc_html( $terms[0]->name ) . '</span>';
					}
				}
				if ( $opt['title'] ) {
					echo '<h3 class="hx-card__title" dir="auto" style="-webkit-line-clamp:' . (int) $opt['lines'] . ';min-height:' . ( 1.5 * (int) $opt['lines'] ) . 'em">' . esc_html( $product->get_name() ) . '</h3>';
				}
				if ( $opt['price'] ) {
					echo hx_card_price( $product, $opt['old_price'] );
				}
				if ( $opt['button'] ) {
					echo '<span class="hx-card__btn">' . esc_html( $opt['button_text'] ) . '</span>';
				}
				?>
			</div>
		</a>
	</li>
	<?php
}

function hx_grid( $products ) {
	echo '<ul class="hx-grid">';
	foreach ( $products as $p ) {
		hx_card( $p );
	}
	echo '</ul>';
}

add_action( 'wp_ajax_hx_order', 'hx_handle_order' );
add_action( 'wp_ajax_nopriv_hx_order', 'hx_handle_order' );

function hx_handle_order() {
	$fail = function ( $msg, $code = 400 ) {
		wp_send_json_error( array( 'message' => hx_t( $msg ) ), $code );
	};
	if ( ! empty( $_POST['website'] ) ) {
		$fail( 'Error' );
	}
	$ip  = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	$key = 'hx_rl_' . md5( $ip );
	$n   = (int) get_transient( $key );
	if ( $n >= 6 ) {
		$fail( 'المرجو المحاولة لاحقاً', 429 );
	}
	$pid     = absint( $_POST['product_id'] ?? 0 );
	$vid     = absint( $_POST['variation_id'] ?? 0 );
	$qty     = max( 1, min( 20, absint( $_POST['qty'] ?? 1 ) ) );
	$name    = sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) );
	$phone   = preg_replace( '/[^0-9+]/', '', wp_unslash( $_POST['phone'] ?? '' ) );
	$city    = sanitize_text_field( wp_unslash( $_POST['city'] ?? '' ) );
	$address = sanitize_text_field( wp_unslash( $_POST['address'] ?? '' ) );
	$hx_need = function_exists( 'hx_cf_required' ) ? hx_cf_required() : array( 'city' => true, 'address' => true ); // COD Form: hidden or optional fields.
	if ( mb_strlen( $name ) < 2 || strlen( preg_replace( '/\D/', '', $phone ) ) < 9 || ( $hx_need['city'] && mb_strlen( $city ) < 2 ) || ( $hx_need['address'] && mb_strlen( $address ) < 3 ) ) {
		$fail( 'المرجو ملء جميع الخانات بشكل صحيح' );
	}
	$parent = wc_get_product( $pid );
	if ( ! $parent || 'publish' !== $parent->get_status() ) {
		$fail( 'المنتج غير متوفر' );
	}
	$product = $parent;
	if ( $parent->is_type( 'variable' ) ) {
		$product = $vid ? wc_get_product( $vid ) : null;
		if ( ! $product || $product->get_parent_id() !== $pid ) {
			wp_send_json_error( array( 'message' => hx_t( 'المرجو اختيار جميع الخيارات' ) ), 400 );
		}
	}
	if ( ! $product->is_purchasable() || ! $product->is_in_stock() ) {
		$fail( 'المنتج غير متوفر حالياً' );
	}
	// Order rules from plugins (COD Manager: blacklist, repeat orders, phone check).
	$hx_block = (string) apply_filters( 'hx_order_block', '', array( 'phone' => $phone, 'product_id' => $pid, 'name' => $name, 'city' => $city ) );
	if ( '' !== $hx_block ) {
		$fail( $hx_block, 400 );
	}
	$hx_customer_lang = hx_lang();
	$GLOBALS['hx_lang'] = 'ar'; // store the order in the store's main language
	$order = wc_create_order( array( 'created_via' => 'hx_form' ) );
	if ( is_wp_error( $order ) ) {
		$fail( 'Error', 500 );
	}
	$order->add_product( $product, $qty );
	$addr = array(
		'first_name' => $name,
		'phone'      => $phone,
		'city'       => $city,
		'address_1'  => $address,
		'country'    => 'MA',
	);
	$order->set_address( $addr, 'billing' );
	$order->set_address( $addr, 'shipping' );
	$order->set_payment_method( 'cod' );
	$order->set_payment_method_title( 'الدفع عند الاستلام' );
	$order->set_customer_ip_address( $ip );
	$order->calculate_totals();
	$order->update_meta_data( '_hx_lang', $hx_customer_lang );
	// Where the customer came from (saved by assets/hx-src.js). Always saved, empty = direct visit.
	$hx_attr = array();
	if ( ! empty( $_COOKIE['hx_src'] ) ) {
		$hx_raw = json_decode( wp_unslash( $_COOKIE['hx_src'] ), true ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		if ( is_array( $hx_raw ) ) {
			foreach ( array( 'utm_source', 'utm_medium', 'utm_campaign', 'fbclid', 'ttclid', 'gclid', 'gbraid', 'wbraid', 'ScCid', 'msclkid', 'ref', 'land', 't' ) as $hx_k ) {
				if ( isset( $hx_raw[ $hx_k ] ) && is_scalar( $hx_raw[ $hx_k ] ) && '' !== (string) $hx_raw[ $hx_k ] ) {
					$hx_attr[ $hx_k ] = substr( sanitize_text_field( (string) $hx_raw[ $hx_k ] ), 0, 150 );
				}
			}
		}
	}
	$order->update_meta_data( '_hx_attr', $hx_attr );
	do_action( 'hx_order_before_save', $order );
	$order->update_status( (string) apply_filters( 'hx_order_initial_status', 'processing', $order ), 'Cash-on-delivery order from the product page form. Customer language: ' . strtoupper( $hx_customer_lang ) . '.' );
	$GLOBALS['hx_lang'] = $hx_customer_lang;
	set_transient( $key, $n + 1, 10 * MINUTE_IN_SECONDS );
	wp_send_json_success( array(
		'redirect' => add_query_arg(
			array( 'order' => $order->get_id(), 'key' => $order->get_order_key() ),
			home_url( '/thank-you/' )
		),
	) );
}

/* ---------- Elementor: products grid widget ---------- */
add_action( 'elementor/widgets/register', function ( $widgets_manager ) {
	require_once get_theme_file_path( 'inc/hx-products-widget.php' );
	$widgets_manager->register( new HX_Products_Widget() );
} );

/* ---------- Footer: menu location + copyright setting ---------- */
add_action( 'after_setup_theme', function () {
	register_nav_menus( array( 'footer' => 'Footer menu', 'primary' => 'Main menu (hamburger)' ) );
} );

add_action( 'customize_register', function ( $wp_customize ) {
	$wp_customize->add_section( 'hx_footer', array(
		'title'    => 'Footer',
		'priority' => 160,
	) );
	$wp_customize->add_setting( 'hx_copyright', array(
		'default'           => 'All rights are reserved',
		'sanitize_callback' => 'sanitize_text_field',
		'transport'         => 'refresh',
	) );
	$wp_customize->add_control( 'hx_copyright', array(
		'label'       => 'Copyright text',
		'description' => 'Shown after the year and store name. The footer links are managed in Appearance > Menus (Footer Menu).',
		'section'     => 'hx_footer',
		'type'        => 'text',
	) );
} );
/* ---------- Product page price block (RTL) ---------- */
function hx_num( $amount ) {
	$amount = (float) $amount;
	return ( floor( $amount ) == $amount ) ? number_format( $amount, 0, '.', ' ' ) : number_format( $amount, 2, '.', ' ' );
}

function hx_price_block( $price, $compare = 0 ) {
	$html = '<span class="hx-pp__now"><span class="hx-pp__num hx-m notranslate" data-mad="' . esc_attr( round( (float) $price, 2 ) ) . '">' . esc_html( hx_num( $price ) ) . '</span> <span class="hx-pp__cur hx-c">درهم</span></span>';
	if ( $compare > $price && $compare > 0 ) {
		$pct   = (int) round( ( 1 - $price / $compare ) * 100 );
		$html .= '<s class="hx-pp__old">' . hx_m( $compare ) . ' ' . hx_c() . '</s>';
		if ( $pct > 0 ) {
			$html .= '<span class="hx-pp__badge">خصم ' . $pct . '%</span>';
		}
	}
	return $html;
}
/* ---------- Products grid: shared query + Load more + Filters ---------- */
function hx_products_args( $orderby, $category, $limit, $exclude = array(), $extra = array() ) {
	$args = array(
		'status'     => 'publish',
		'limit'      => max( 1, min( 100, (int) $limit ) ),
		'visibility' => 'catalog',
	);
	switch ( $orderby ) {
		case 'date':
			$args['orderby'] = 'date';
			$args['order']   = 'DESC';
			break;
		case 'price':
		case 'price-desc':
			$args['orderby']  = 'meta_value_num';
			$args['meta_key'] = '_price';
			$args['order']    = 'price' === $orderby ? 'ASC' : 'DESC';
			break;
		case 'rand':
			$args['orderby'] = 'rand';
			break;
		default:
			$args['orderby'] = array( 'menu_order' => 'ASC', 'date' => 'DESC' );
	}
	$cats = array_filter( array_map( 'sanitize_title', is_array( $category ) ? $category : array( $category ) ) );
	if ( $cats ) {
		$args['category'] = array_values( $cats );
	}
	if ( $exclude ) {
		$args['exclude'] = array_map( 'absint', (array) $exclude );
	}
	$min     = isset( $extra['min_price'] ) && '' !== $extra['min_price'] ? (float) $extra['min_price'] : null;
	$max     = isset( $extra['max_price'] ) && '' !== $extra['max_price'] ? (float) $extra['max_price'] : null;
	$include = null;
	if ( null !== $min || null !== $max ) {
		global $wpdb;
		$include = array_map( 'absint', $wpdb->get_col( $wpdb->prepare(
			"SELECT product_id FROM {$wpdb->wc_product_meta_lookup} WHERE max_price >= %f AND min_price <= %f",
			null !== $min ? $min : 0,
			null !== $max ? $max : 100000000
		) ) );
	}
	if ( ! empty( $extra['on_sale'] ) ) {
		$sale    = array_map( 'absint', wc_get_product_ids_on_sale() );
		$include = null === $include ? $sale : array_values( array_intersect( $include, $sale ) );
	}
	if ( isset( $extra['search'] ) && '' !== trim( (string) $extra['search'] ) && function_exists( 'hx_search_product_ids' ) ) {
		$found   = hx_search_product_ids( $extra['search'] );
		$include = null === $include ? $found : array_values( array_intersect( $include, $found ) );
	}
	if ( null !== $include ) {
		$args['include'] = $include ? $include : array( 0 );
	}
	return $args;
}

function hx_products_total( $args ) {
	unset( $args['exclude'] );
	$args['limit']    = 1;
	$args['paginate'] = true;
	$args['return']   = 'ids';
	$res              = wc_get_products( $args );
	return (int) $res->total;
}

add_action( 'wp_ajax_hx_more', 'hx_more_products' );
add_action( 'wp_ajax_nopriv_hx_more', 'hx_more_products' );
function hx_more_products() {
	$in       = wp_unslash( $_POST );
	$limit    = isset( $in['limit'] ) ? absint( $in['limit'] ) : 12;
	$orderby  = isset( $in['orderby'] ) ? sanitize_key( $in['orderby'] ) : 'menu_order';
	$category = array();
	if ( ! empty( $in['categories'] ) ) {
		$category = explode( ',', (string) $in['categories'] );
	} elseif ( ! empty( $in['category'] ) ) {
		$category = array( (string) $in['category'] );
	}
	$exclude = ! empty( $in['exclude'] ) ? array_slice( array_filter( array_map( 'absint', explode( ',', (string) $in['exclude'] ) ) ), 0, 2000 ) : array();
	$extra   = array(
		'min_price' => isset( $in['min_price'] ) ? preg_replace( '/[^0-9.]/', '', (string) $in['min_price'] ) : '',
		'max_price' => isset( $in['max_price'] ) ? preg_replace( '/[^0-9.]/', '', (string) $in['max_price'] ) : '',
		'on_sale'   => ! empty( $in['on_sale'] ),
		'search'    => isset( $in['search'] ) ? mb_substr( sanitize_text_field( (string) $in['search'] ), 0, 80 ) : '',
	);
	$args     = hx_products_args( $orderby, $category, $limit + 1, $exclude, $extra );
	$products = wc_get_products( $args );
	$has_more = count( $products ) > $limit;
	$products = array_slice( $products, 0, $limit );
	ob_start();
	foreach ( $products as $p ) {
		hx_card( $p );
	}
	$out = array( 'html' => hx_translate_html( ob_get_clean() ), 'has_more' => $has_more );
	if ( ! empty( $in['want_total'] ) ) {
		$out['total'] = hx_products_total( $args );
	}
	wp_send_json_success( $out );
}

add_action( 'wp_enqueue_scripts', function () {
	wp_register_script( 'hx-load-more', get_theme_file_uri( 'assets/load-more.js' ), array(), HX_VER, true );
	wp_localize_script( 'hx-load-more', 'HXMORE', array( 'ajax' => admin_url( 'admin-ajax.php' ) ) );
	wp_register_script( 'hx-filters', get_theme_file_uri( 'assets/filters.js' ), array( 'hx-load-more' ), HX_VER, true );
}, 21 );

/** Price bounds of published products, for the filter placeholders. */
function hx_price_bounds() {
	global $wpdb;
	$row = $wpdb->get_row( "SELECT MIN(l.min_price) AS mn, MAX(l.max_price) AS mx FROM {$wpdb->wc_product_meta_lookup} l INNER JOIN {$wpdb->posts} p ON p.ID = l.product_id WHERE p.post_status = 'publish' AND p.post_type = 'product'" );
	return array( $row ? (int) floor( (float) $row->mn ) : 0, $row ? (int) ceil( (float) $row->mx ) : 0 );
}

add_action( 'wp_enqueue_scripts', function () {
	wp_enqueue_script( 'hx-menu', get_theme_file_uri( 'assets/menu.js' ), array(), HX_VER, true );
}, 22 );

/* ---------- Logo (image or text) ---------- */
add_action( 'customize_register', function ( $wp_customize ) {
	$wp_customize->add_setting( 'hx_logo_type', array(
		'default'           => 'image',
		'sanitize_callback' => function ( $v ) { return in_array( $v, array( 'image', 'text', 'none' ), true ) ? $v : 'image'; },
	) );
	$wp_customize->add_control( 'hx_logo_type', array(
		'label'    => 'Logo type',
		'section'  => 'title_tagline',
		'type'     => 'radio',
		'choices'  => array(
			'image' => 'Image logo (uses the Logo above)',
			'text'  => 'Text logo',
			'none'  => 'No logo',
		),
		'priority' => 8,
	) );
	$wp_customize->add_setting( 'hx_logo_height', array(
		'default'           => 40,
		'sanitize_callback' => 'absint',
	) );
	$wp_customize->add_control( 'hx_logo_height', array(
		'label'       => 'Image logo height (px)',
		'description' => 'Height of the image logo in the top bar (20 to 70).',
		'section'     => 'title_tagline',
		'type'        => 'number',
		'input_attrs' => array( 'min' => 20, 'max' => 70, 'step' => 1 ),
		'priority'    => 9,
	) );
	$wp_customize->add_setting( 'hx_logo_text', array(
		'default'           => '',
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'hx_logo_text', array(
		'label'       => 'Text logo',
		'description' => 'Leave empty to use the Site Title.',
		'section'     => 'title_tagline',
		'type'        => 'text',
		'priority'    => 9,
	) );
	$wp_customize->add_setting( 'hx_logo_text_size', array(
		'default'           => 22,
		'sanitize_callback' => 'absint',
	) );
	$wp_customize->add_control( 'hx_logo_text_size', array(
		'label'       => 'Text logo size (px)',
		'section'     => 'title_tagline',
		'type'        => 'number',
		'input_attrs' => array( 'min' => 14, 'max' => 40, 'step' => 1 ),
		'priority'    => 9,
	) );
	$wp_customize->add_setting( 'hx_logo_text_color', array(
		'default'           => '#ffffff',
		'sanitize_callback' => 'sanitize_hex_color',
	) );
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'hx_logo_text_color', array(
		'label'    => 'Text logo color',
		'section'  => 'title_tagline',
		'priority' => 9,
	) ) );
} );

/** Logo markup: image, text, or nothing (per Customizer "Logo type"). */
function hx_logo( $class = 'hx-logo' ) {
	$type = get_theme_mod( 'hx_logo_type', 'image' );
	$home = esc_url( home_url( '/' ) );
	$name = get_bloginfo( 'name' );
	if ( 'text' === $type ) {
		$text  = trim( (string) get_theme_mod( 'hx_logo_text', '' ) );
		$text  = '' !== $text ? $text : $name;
		$size  = max( 14, min( 40, (int) get_theme_mod( 'hx_logo_text_size', 22 ) ) );
		$color = sanitize_hex_color( get_theme_mod( 'hx_logo_text_color', '#ffffff' ) ) ?: '#ffffff';
		return '<a class="' . esc_attr( $class ) . ' ' . esc_attr( $class ) . '--text" href="' . $home . '" dir="auto" style="--hx-logo-fs:' . $size . 'px;--hx-logo-color:' . esc_attr( $color ) . '">' . esc_html( $text ) . '</a>';
	}
	if ( 'image' !== $type ) {
		return '';
	}
	$id = (int) get_theme_mod( 'custom_logo' );
	if ( ! $id ) {
		return '';
	}
	$img = wp_get_attachment_image( $id, 'medium', false, array( 'class' => $class . '__img', 'alt' => $name, 'loading' => 'eager', 'decoding' => 'async' ) );
	return $img ? '<a class="' . esc_attr( $class ) . '" href="' . $home . '" aria-label="' . esc_attr( $name ) . '">' . $img . '</a>' : '';
}

/* ---------- Header layout controls (Customizer > Header layout) ---------- */
function hx_header_fields() {
	return array(
		'hx_bar_py'        => array( 'Bar top/bottom padding - desktop (px)', 12, 0, 60 ),
		'hx_bar_py_m'      => array( 'Bar top/bottom padding - mobile (px)', 12, 0, 60 ),
		'hx_logo_x'        => array( 'Logo distance from left edge - desktop (px)', 20, 0, 120 ),
		'hx_logo_x_m'      => array( 'Logo distance from left edge - mobile (px)', 10, 0, 80 ),
		'hx_burger_x'      => array( 'Menu button distance from right edge - desktop (px)', 20, 0, 120 ),
		'hx_burger_x_m'    => array( 'Menu button distance from right edge - mobile (px)', 8, 0, 80 ),
		'hx_burger_size'   => array( 'Menu button size - desktop (px)', 44, 28, 72 ),
		'hx_burger_size_m' => array( 'Menu button size - mobile (px)', 40, 28, 64 ),
		'hx_text_pl'       => array( 'Notice text: space on the left - desktop (px, 0 = automatic)', 0, 0, 400 ),
		'hx_text_pl_m'     => array( 'Notice text: space on the left - mobile (px, 0 = automatic)', 0, 0, 200 ),
		'hx_text_pr'       => array( 'Notice text: space on the right - desktop (px, 0 = automatic)', 0, 0, 400 ),
		'hx_text_pr_m'     => array( 'Notice text: space on the right - mobile (px, 0 = automatic)', 0, 0, 200 ),
		'hx_text_fs'       => array( 'Notice text size - desktop (px, 0 = Elementor setting)', 0, 0, 40 ),
		'hx_text_fs_m'     => array( 'Notice text size - mobile (px, 0 = Elementor setting)', 0, 0, 32 ),
	);
}

function hx_hmod( $key ) {
	$fields = hx_header_fields();
	$def    = $fields[ $key ];
	$v      = get_theme_mod( $key, $def[1] );
	return max( $def[2], min( $def[3], (int) $v ) );
}

add_action( 'customize_register', function ( $wp_customize ) {
	$wp_customize->add_section( 'hx_header', array(
		'title'       => 'Header layout',
		'description' => 'Spacing of the top bar: logo, notice text and menu button. Desktop and mobile are set separately.',
		'priority'    => 30,
	) );
	$i = 0;
	foreach ( hx_header_fields() as $key => $f ) {
		$wp_customize->add_setting( $key, array(
			'default'           => $f[1],
			'sanitize_callback' => 'absint',
		) );
		$wp_customize->add_control( $key, array(
			'label'       => $f[0],
			'section'     => 'hx_header',
			'type'        => 'number',
			'input_attrs' => array( 'min' => $f[2], 'max' => $f[3], 'step' => 1 ),
			'priority'    => 10 + $i++,
		) );
	}
} );

add_action( 'wp_head', function () {
	$t  = '.hx-topbar .hx-notice .e-con, .hx-topbar .hx-announce, .hx-topbar.hx-topbar--logo .hx-notice .e-con, .hx-topbar.hx-topbar--logo .hx-announce';
	$tx = '.hx-topbar .hx-notice .elementor-heading-title, .hx-topbar .hx-announce, .hx-topbar.hx-topbar--logo .elementor-heading-title';
	$css = '';
	foreach ( array( '' => '(min-width: 750px)', '_m' => '(max-width: 749px)' ) as $sfx => $mq ) {
		$s     = hx_hmod( 'hx_burger_size' . $sfx );
		$rules = $t . '{padding-top:' . hx_hmod( 'hx_bar_py' . $sfx ) . 'px !important;padding-bottom:' . hx_hmod( 'hx_bar_py' . $sfx ) . 'px !important;';
		if ( hx_hmod( 'hx_text_pl' . $sfx ) ) {
			$rules .= 'padding-left:' . hx_hmod( 'hx_text_pl' . $sfx ) . 'px !important;';
		}
		if ( hx_hmod( 'hx_text_pr' . $sfx ) ) {
			$rules .= 'padding-right:' . hx_hmod( 'hx_text_pr' . $sfx ) . 'px !important;';
		}
		$rules .= '}';
		$rules .= '.hx-topbar .hx-logo{left:' . hx_hmod( 'hx_logo_x' . $sfx ) . 'px;}';
		$rules .= '.hx-topbar .hx-burger{right:' . hx_hmod( 'hx_burger_x' . $sfx ) . 'px;width:' . $s . 'px;height:' . $s . 'px;}';
		$rules .= '.hx-topbar{--hx-burger-right:' . hx_hmod( 'hx_burger_x' . $sfx ) . 'px;--hx-burger-size:' . $s . 'px;}';
		if ( hx_hmod( 'hx_text_fs' . $sfx ) ) {
			$rules .= $tx . '{font-size:' . hx_hmod( 'hx_text_fs' . $sfx ) . 'px !important;}';
		}
		$css .= '@media ' . $mq . '{' . $rules . '}';
	}
	echo '<style id="hx-header-layout">' . $css . '</style>' . "\n";
}, 100 );

/* ---------- Store colors (Customizer > Store colors) ---------- */
function hx_color_fields() {
	return array(
		'hx_c_primary' => array( 'Main color (buttons, top bar, footer, borders)', '#0f5754' ),
		'hx_c_accent'  => array( 'Accent color (highlights, focus outline)', '#ea9302' ),
		'hx_c_price'   => array( 'Price color (product page)', '#0f5754' ),
		'hx_c_badge'   => array( 'Discount badge color', '#c62828' ),
		'hx_c_text'    => array( 'Headings & main text color', '#121212' ),
		'hx_c_bg'      => array( 'Page background', '#ffffff' ),
	);
}

function hx_hex_rgb( $hex ) {
	$hex = ltrim( (string) $hex, '#' );
	if ( 3 === strlen( $hex ) ) {
		$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
	}
	return array( hexdec( substr( $hex, 0, 2 ) ), hexdec( substr( $hex, 2, 2 ) ), hexdec( substr( $hex, 4, 2 ) ) );
}

/** Mix a color with white ($t > 0) or black ($t < 0); $t from -1 to 1. */
function hx_mix( $hex, $t ) {
	$rgb = hx_hex_rgb( $hex );
	$to  = $t > 0 ? 255 : 0;
	$a   = abs( $t );
	return sprintf( '#%02x%02x%02x', round( $rgb[0] + ( $to - $rgb[0] ) * $a ), round( $rgb[1] + ( $to - $rgb[1] ) * $a ), round( $rgb[2] + ( $to - $rgb[2] ) * $a ) );
}

function hx_color( $key ) {
	$f = hx_color_fields();
	$v = sanitize_hex_color( get_theme_mod( $key, $f[ $key ][1] ) );
	return $v ? $v : $f[ $key ][1];
}

add_action( 'customize_register', function ( $wp_customize ) {
	$wp_customize->add_section( 'hx_colors', array(
		'title'       => 'Store colors',
		'description' => 'Global colors for the whole store. Light shades (backgrounds, borders) are generated automatically from the main color.',
		'priority'    => 25,
	) );
	$i = 0;
	foreach ( hx_color_fields() as $key => $field ) {
		$wp_customize->add_setting( $key, array( 'default' => $field[1], 'sanitize_callback' => 'sanitize_hex_color' ) );
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, $key, array(
			'label'    => $field[0],
			'section'  => 'hx_colors',
			'priority' => 10 + $i++,
		) ) );
	}
	$wp_customize->add_setting( 'hx_c_notice_sync', array( 'default' => true, 'sanitize_callback' => 'wp_validate_boolean' ) );
	$wp_customize->add_control( 'hx_c_notice_sync', array(
		'label'       => 'Top bar (store notice) uses the main color',
		'description' => 'Untick to keep the background color set in the Elementor "Store Notice" template.',
		'section'     => 'hx_colors',
		'type'        => 'checkbox',
		'priority'    => 50,
	) );
} );

add_action( 'wp_head', function () {
	$p   = hx_color( 'hx_c_primary' );
	$rgb = implode( ', ', hx_hex_rgb( $p ) );
	$css = ':root{'
		. '--hx-primary:' . $p . ';'
		. '--hx-primary-rgb:' . $rgb . ';'
		. '--hx-primary-dark:' . hx_mix( $p, -0.18 ) . ';'
		. '--hx-primary-soft:' . hx_mix( $p, 0.9 ) . ';'
		. '--hx-primary-softer:' . hx_mix( $p, 0.95 ) . ';'
		. '--hx-primary-border:' . hx_mix( $p, 0.72 ) . ';'
		. '--hx-line:' . hx_mix( $p, 0.88 ) . ';'
		. '--hx-accent:' . hx_color( 'hx_c_accent' ) . ';'
		. '--hx-price:' . hx_color( 'hx_c_price' ) . ';'
		. '--hx-badge:' . hx_color( 'hx_c_badge' ) . ';'
		. '--hx-badge-bg:' . hx_mix( hx_color( 'hx_c_badge' ), 0.9 ) . ';'
		. '--hx-ink:' . hx_color( 'hx_c_text' ) . ';'
		. '--hx-bg:' . hx_color( 'hx_c_bg' ) . ';'
		. '}';
	if ( get_theme_mod( 'hx_c_notice_sync', true ) ) {
		$css .= '.hx-notice .e-con.e-parent{background-color:var(--hx-primary) !important;}';
	}
	echo '<style id="hx-colors">' . $css . '</style>' . "\n";
}, 99 );

/* ---------- Ready-made color presets (Customizer > Store colors) ---------- */
function hx_color_presets() {
	// primary, accent, price, badge, text, background
	return array(
		'emerald'  => array( 'أخضر زمرّدي - Emerald (default)', '#0f5754', '#ea9302', '#0f5754', '#c62828', '#121212', '#ffffff' ),
		'ocean'    => array( 'أزرق المحيط - Ocean Blue', '#1d4ed8', '#f59e0b', '#1d4ed8', '#dc2626', '#0f172a', '#ffffff' ),
		'midnight' => array( 'ليلي فاخر - Midnight Luxe', '#111827', '#d4a017', '#111827', '#b91c1c', '#111827', '#ffffff' ),
		'rose'     => array( 'وردي ناعم - Rose Blush', '#be185d', '#f59e0b', '#be185d', '#e11d48', '#1f2937', '#fff7fb' ),
		'royal'    => array( 'بنفسجي ملكي - Royal Purple', '#6d28d9', '#f59e0b', '#6d28d9', '#dc2626', '#1e1b4b', '#ffffff' ),
		'sunset'   => array( 'برتقالي حيوي - Sunset Orange', '#c2410c', '#0f766e', '#c2410c', '#b91c1c', '#1c1917', '#fffaf5' ),
		'desert'   => array( 'رمال الصحراء - Desert Sand', '#8b5e34', '#d97706', '#8b5e34', '#b91c1c', '#292524', '#fdf8f1' ),
		'maroc'    => array( 'أحمر مغربي - Moroccan Red', '#9f1d20', '#c9a227', '#9f1d20', '#dc2626', '#1a1a1a', '#fffdf8' ),
		'olive'    => array( 'زيتوني طبيعي - Natural Olive', '#4d6b2f', '#e0a100', '#4d6b2f', '#c62828', '#1c1f17', '#fbfcf7' ),
		'teal'     => array( 'تركوازي منعش - Fresh Teal', '#0e7490', '#f97316', '#0e7490', '#dc2626', '#0f172a', '#ffffff' ),
		'beauty' => array( 'نود ناعم - Nude Beauty', '#9d4b5b', '#f2b8a0', '#9d4b5b', '#c2185b', '#2a1f22', '#fffaf8' ),
		'lavender' => array( 'لافندر هادئ - Soft Lavender', '#6b4fa0', '#f4c2d7', '#6b4fa0', '#c2255c', '#241d33', '#faf8ff' ),
		'berry' => array( 'توتي جذاب - Berry', '#86198f', '#fde047', '#86198f', '#e11d48', '#1f1023', '#fdf8ff' ),
		'coral' => array( 'مرجاني دافئ - Warm Coral', '#c43d52', '#ffd166', '#c43d52', '#b42318', '#1f1f1f', '#fffaf9' ),
		'cherry' => array( 'أحمر جريء - Bold Red', '#b91c1c', '#facc15', '#b91c1c', '#7f1d1d', '#1c1917', '#ffffff' ),
		'saffron' => array( 'زعفراني - Saffron', '#b45309', '#fcd34d', '#b45309', '#b91c1c', '#231a0f', '#fffbf3' ),
		'coffee' => array( 'بني القهوة - Coffee', '#5b3a29', '#e9b872', '#5b3a29', '#c62828', '#2b1d16', '#fbf7f2' ),
		'mint' => array( 'أخضر صحي - Health Mint', '#047857', '#fbbf24', '#047857', '#dc2626', '#0b1f17', '#f7fdfa' ),
		'navy' => array( 'كحلي كلاسيكي - Classic Navy', '#1e3a5f', '#e0b84a', '#1e3a5f', '#c62828', '#15202b', '#ffffff' ),
		'tech' => array( 'أزرق تقني - Tech Blue', '#0b3d91', '#22d3ee', '#0b3d91', '#e11d48', '#0f172a', '#f8fafc' ),
		'graphite' => array( 'رمادي عصري - Modern Graphite', '#374151', '#10b981', '#374151', '#dc2626', '#111827', '#f9fafb' ),
		'mono' => array( 'أبيض وأسود - Minimal Mono', '#000000', '#ffd60a', '#000000', '#e00000', '#111111', '#ffffff' ),
	);
}

add_action( 'customize_register', function ( $wp_customize ) {
	if ( ! class_exists( 'HX_Preset_Control' ) ) {
		class HX_Preset_Control extends WP_Customize_Control {
			public $type = 'hx_preset';
			public function render_content() {
				echo '<span class="customize-control-title">' . esc_html( $this->label ) . '</span>';
				if ( $this->description ) {
					echo '<span class="description customize-control-description">' . esc_html( $this->description ) . '</span>';
				}
				echo '<div class="hx-presets">';
				foreach ( hx_color_presets() as $key => $p ) {
					echo '<label class="hx-preset"><input type="radio" name="' . esc_attr( '_customize-radio-' . $this->id ) . '" value="' . esc_attr( $key ) . '" ';
					$this->link();
					checked( $this->value(), $key );
					echo '><span class="hx-preset__sw">';
					foreach ( array( 1, 2, 4, 6 ) as $i ) {
						echo '<i style="background:' . esc_attr( $p[ $i ] ) . '"></i>';
					}
					echo '</span><span class="hx-preset__name">' . esc_html( $p[0] ) . '</span></label>';
				}
				echo '</div>';
			}
		}
	}
	$wp_customize->add_setting( 'hx_c_preset', array(
		'default'           => 'emerald',
		'sanitize_callback' => function ( $v ) { return array_key_exists( $v, hx_color_presets() ) ? $v : 'emerald'; },
	) );
	$wp_customize->add_control( new HX_Preset_Control( $wp_customize, 'hx_c_preset', array(
		'label'       => 'Ready-made color themes',
		'description' => 'Click a theme to apply all its colors at once. You can still fine-tune each color below.',
		'section'     => 'hx_colors',
		'priority'    => 1,
	) ) );
} );

add_action( 'customize_controls_print_styles', function () {
	echo '<style>.hx-presets{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:8px;margin-top:8px}.hx-preset{position:relative;display:flex;flex-direction:column;align-items:flex-start;gap:6px;padding:8px;border:1px solid #dcdcde;border-radius:8px;background:#fff;cursor:pointer}.hx-preset:hover{border-color:#2271b1}.hx-preset input{position:absolute;opacity:0;pointer-events:none}.hx-preset:has(input:checked){border-color:#2271b1;box-shadow:0 0 0 1px #2271b1;background:#f0f6fc}.hx-preset:has(input:focus-visible){outline:2px solid #2271b1;outline-offset:2px}.hx-preset input:checked+.hx-preset__sw{outline:2px solid #2271b1;outline-offset:2px}.hx-preset__sw{display:flex;width:100%;border-radius:6px;overflow:hidden;box-shadow:0 0 0 1px rgba(0,0,0,.12);flex:0 0 auto}.hx-preset__sw i{display:block;flex:1;height:24px}.hx-preset__name{font-size:12px;line-height:1.35}</style>';
} );

add_action( 'customize_controls_print_footer_scripts', function () {
	$map  = array();
	$keys = array( 'hx_c_primary', 'hx_c_accent', 'hx_c_price', 'hx_c_badge', 'hx_c_text', 'hx_c_bg' );
	foreach ( hx_color_presets() as $k => $p ) {
		$map[ $k ] = array_combine( $keys, array_slice( $p, 1 ) );
	}
	?>
	<script>
	( function ( api ) {
		var presets = <?php echo wp_json_encode( $map ); ?>;
		api.bind( 'ready', function () {
			api( 'hx_c_preset', function ( setting ) {
				setting.bind( function ( val ) {
					var p = presets[ val ];
					if ( ! p ) { return; }
					Object.keys( p ).forEach( function ( id ) {
						if ( api( id ) ) { api( id ).set( p[ id ] ); }
						var ctrl = api.control( id );
						if ( ctrl && ctrl.container ) {
							var input = ctrl.container.find( '.color-picker-hex' );
							if ( input.length && input.wpColorPicker ) { input.wpColorPicker( 'color', p[ id ] ); }
						}
					} );
				} );
			} );
		} );
	} )( wp.customize );
	</script>
	<?php
} );

/* ---------- Product card price + badge (home / shop grids) ---------- */
function hx_card_price( $product, $show_old = true ) {
	list( $price, $compare ) = hx_prices( $product );
	$html = '<div class="hx-cp"><span class="hx-cp__now"><span class="hx-cp__num hx-m notranslate" data-mad="' . esc_attr( round( (float) $price, 2 ) ) . '">' . esc_html( hx_num( $price ) ) . '</span> <span class="hx-cp__cur hx-c">درهم</span></span>';
	if ( $compare && $show_old ) {
		$html .= '<s class="hx-cp__old">' . hx_m( $compare ) . ' ' . hx_c() . '</s>';
	}
	return $html . '</div>';
}

function hx_card_badge( $product ) {
	list( $price, $compare ) = hx_prices( $product );
	if ( ! $compare || $compare <= $price ) {
		return '';
	}
	$pct = (int) round( ( 1 - $price / $compare ) * 100 );
	return $pct > 0 ? '<span class="hx-card__badge" dir="ltr">-' . $pct . '%</span>' : '';
}

/* ---------- Product card display options (Customizer > Product cards) ---------- */
function hx_card_fields() {
	return array(
		'hx_pc_badge'     => array( 'Discount badge on the photo (on sale)', true ),
		'hx_pc_category'  => array( 'Category name', false ),
		'hx_pc_title'     => array( 'Product title', true ),
		'hx_pc_price'     => array( 'Price', true ),
		'hx_pc_old_price' => array( 'Old (crossed-out) price', true ),
		'hx_pc_button'    => array( 'Order button', false ),
	);
}

function hx_card_opts() {
	static $o = null;
	if ( null !== $o ) {
		return $o;
	}
	$o = array();
	foreach ( hx_card_fields() as $k => $f ) {
		$o[ substr( $k, 6 ) ] = (bool) get_theme_mod( $k, $f[1] );
	}
	$o['lines']       = max( 1, min( 3, (int) get_theme_mod( 'hx_pc_lines', 2 ) ) );
	$t                = trim( (string) get_theme_mod( 'hx_pc_button_text', '' ) );
	$o['button_text'] = '' !== $t ? $t : 'أطلب الآن';
	return $o;
}

add_action( 'customize_register', function ( $wp_customize ) {
	$wp_customize->add_section( 'hx_cards', array(
		'title'       => 'Product cards',
		'description' => 'Choose what each product shows in the product grids: home page, shop, categories and load more.',
		'priority'    => 26,
	) );
	$i = 0;
	foreach ( hx_card_fields() as $key => $f ) {
		$wp_customize->add_setting( $key, array( 'default' => $f[1], 'sanitize_callback' => 'wp_validate_boolean' ) );
		$wp_customize->add_control( $key, array( 'label' => $f[0], 'section' => 'hx_cards', 'type' => 'checkbox', 'priority' => 10 + $i++ ) );
	}
	$wp_customize->add_setting( 'hx_pc_button_text', array( 'default' => '', 'sanitize_callback' => 'sanitize_text_field' ) );
	$wp_customize->add_control( 'hx_pc_button_text', array(
		'label'       => 'Order button text',
		'description' => 'Default: أطلب الآن. The button opens the product page, where the order form is.',
		'section'     => 'hx_cards',
		'type'        => 'text',
		'priority'    => 30,
	) );
	$wp_customize->add_setting( 'hx_pc_lines', array( 'default' => 2, 'sanitize_callback' => 'absint' ) );
	$wp_customize->add_control( 'hx_pc_lines', array(
		'label'   => 'Title lines before …',
		'section' => 'hx_cards',
		'type'    => 'select',
		'choices' => array( 1 => '1 line', 2 => '2 lines', 3 => '3 lines' ),
		'priority' => 31,
	) );
} );

require_once get_theme_file_path( 'inc/product-page-customizer.php' );
require_once get_theme_file_path( 'inc/hx-cod-form.php' );
require_once get_theme_file_path( 'inc/hx-customizer-ui.php' );
require_once get_theme_file_path( 'inc/hx-header-builder.php' );
require_once get_theme_file_path( 'inc/hx-notice.php' );
require_once get_theme_file_path( 'inc/hx-cf-title.php' );
require_once get_theme_file_path( 'inc/hx-price-box.php' );
require_once get_theme_file_path( 'inc/hx-pixels.php' );
require_once get_theme_file_path( 'inc/hx-sticky-buy.php' );
require_once get_theme_file_path( 'inc/hx-reviews.php' );
require_once get_theme_file_path( 'inc/hx-related.php' );
require_once get_theme_file_path( 'inc/hx-ty-offer.php' );
require_once get_theme_file_path( 'inc/hx-popup.php' );
require_once get_theme_file_path( 'inc/hx-popup-style.php' );

require_once get_theme_file_path( 'inc/customizer-sliders.php' );


/* ---------- Page titles ---------- */
add_filter( 'woocommerce_page_title', function ( $t ) { return hx_t( $t ); } );
add_filter( 'post_type_archive_title', function ( $t ) { return hx_t( $t ); } );
add_filter( 'document_title_parts', function ( $parts ) {
	if ( isset( $parts['title'] ) ) {
		$parts['title'] = hx_t( $parts['title'] );
	}
	return $parts;
} );

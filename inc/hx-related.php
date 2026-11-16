<?php
/**
 * Related products on product pages.
 * Customize > Product page > Related products: show/hide, title, position, which products,
 * how many, columns on computers and the phone layout.
 *
 * @package hx-store
 */

defined( 'ABSPATH' ) || exit;

function hx_rel_opt( $key ) {
	$def = array(
		'hx_rel_show'  => 'show',
		'hx_rel_title' => 'قد يعجبك أيضاً',
		'hx_rel_pos'   => 'form',
		'hx_rel_src'   => 'category',
		'hx_rel_count' => 8,
		'hx_rel_cols'  => 3,
		'hx_rel_m'     => 'row',
	);
	return get_theme_mod( $key, $def[ $key ] );
}

/** Product IDs ordered by a simple rule, excluding some. */
function hx_rel_query( $orderby, $n, $exclude ) {
	$args = array(
		'status'     => 'publish',
		'visibility' => 'catalog',
		'limit'      => $n,
		'exclude'    => $exclude,
		'return'     => 'ids',
	);
	if ( 'best' === $orderby ) {
		$args['orderby']  = 'meta_value_num';
		$args['meta_key'] = 'total_sales'; // phpcs:ignore WordPress.DB.SlowDBQuery
		$args['order']    = 'DESC';
	} elseif ( 'random' === $orderby ) {
		$args['orderby'] = 'rand';
	} else {
		$args['orderby'] = 'date';
		$args['order']   = 'DESC';
	}
	return wc_get_products( $args );
}

/** Up to $n other products to suggest. */
function hx_rel_products( $product, $n ) {
	$id  = $product->get_id();
	$src = (string) hx_rel_opt( 'hx_rel_src' );
	$ids = array();
	if ( 'upsells' === $src ) {
		$ids = $product->get_upsell_ids();
	}
	if ( in_array( $src, array( 'category', 'upsells' ), true ) && count( $ids ) < $n && function_exists( 'wc_get_related_products' ) ) {
		$ids = array_merge( $ids, wc_get_related_products( $id, $n, $ids ) );
	}
	if ( in_array( $src, array( 'best', 'new', 'random' ), true ) ) {
		$ids = hx_rel_query( $src, $n, array( $id ) );
	}
	if ( count( $ids ) < $n ) {
		$ids = array_merge( $ids, hx_rel_query( 'best', $n, array_merge( array( $id ), $ids ) ) );
	}
	$out = array();
	foreach ( array_unique( array_map( 'intval', $ids ) ) as $pid ) {
		if ( $pid === $id ) {
			continue;
		}
		$p = wc_get_product( $pid );
		if ( $p && $p->is_visible() && 'publish' === $p->get_status() ) {
			$out[] = $p;
		}
		if ( count( $out ) >= $n ) {
			break;
		}
	}
	return $out;
}

/** Print the section if $where is the chosen position. */
function hx_render_related( $product, $where = 'form' ) {
	if ( ! $product || 'hide' === hx_rel_opt( 'hx_rel_show' ) || $where !== hx_rel_opt( 'hx_rel_pos' ) || ! function_exists( 'hx_card' ) ) {
		return;
	}
	$n     = max( 2, min( 12, (int) hx_rel_opt( 'hx_rel_count' ) ) );
	$items = hx_rel_products( $product, $n );
	if ( ! $items ) {
		return;
	}
	$title = trim( (string) hx_rel_opt( 'hx_rel_title' ) );
	$cls   = 'hx-rel hx-rel--' . sanitize_html_class( $where ) . ( 'grid' === hx_rel_opt( 'hx_rel_m' ) ? ' hx-rel--mgrid' : '' );
	if ( 'bottom' === $where ) {
		echo '<div class="hx-wrap">';
	}
	echo '<section class="' . esc_attr( $cls ) . '" dir="rtl" aria-label="' . esc_attr( '' !== $title ? $title : 'Related products' ) . '">';
	if ( '' !== $title ) {
		echo '<h2 class="hx-rel__title">' . esc_html( $title ) . '</h2>';
	}
	echo '<ul class="hx-grid hx-rel__list">';
	foreach ( $items as $p ) {
		hx_card( $p );
	}
	echo '</ul></section>';
	if ( 'bottom' === $where ) {
		echo '</div>';
	}
}

add_action(
	'customize_register',
	function ( $wp_customize ) {
		$wp_customize->add_section(
			'hx_pp_related',
			array(
				'title'    => 'Related products',
				'panel'    => $wp_customize->get_panel( 'hx_product_page' ) ? 'hx_product_page' : '',
				'priority' => 90,
			)
		);
		$sel = function ( $id, $label, $choices, $def, $prio ) use ( $wp_customize ) {
			$wp_customize->add_setting( $id, array( 'default' => $def, 'sanitize_callback' => function ( $v ) use ( $choices, $def ) { return array_key_exists( (string) $v, $choices ) ? (string) $v : $def; } ) );
			$wp_customize->add_control( $id, array( 'label' => $label, 'section' => 'hx_pp_related', 'type' => 'select', 'choices' => $choices, 'priority' => $prio ) );
		};
		$sel( 'hx_rel_show', 'Related products', array( 'show' => 'Show', 'hide' => 'Hide' ), 'show', 10 );
		$wp_customize->add_setting( 'hx_rel_title', array( 'default' => 'قد يعجبك أيضاً', 'sanitize_callback' => 'sanitize_text_field' ) );
		$wp_customize->add_control( 'hx_rel_title', array( 'label' => 'Title', 'section' => 'hx_pp_related', 'type' => 'text', 'priority' => 20 ) );
		$sel(
			'hx_rel_pos',
			'Position',
			array(
				'form'    => 'Under the order form',
				'desc'    => 'Under the description',
				'reviews' => 'Under the reviews',
				'bottom'  => 'Bottom of the page (full width)',
			),
			'form',
			30
		);
		$sel(
			'hx_rel_src',
			'Which products',
			array(
				'category' => 'Same category (then best sellers)',
				'upsells'  => 'Chosen per product (Linked products > Upsells)',
				'best'     => 'Best sellers',
				'new'      => 'Newest',
				'random'   => 'Random',
			),
			'category',
			40
		);
		$sel( 'hx_rel_count', 'How many products', array_combine( range( 2, 12 ), range( 2, 12 ) ), '8', 50 );
		$sel( 'hx_rel_cols', 'Columns - desktop', array( '2' => '2', '3' => '3', '4' => '4', '5' => '5', '6' => '6' ), '3', 60 );
		$sel( 'hx_rel_m', 'Layout - mobile', array( 'row' => 'One row you can swipe', 'grid' => '2 columns' ), 'row', 70 );
	},
	40
);

add_action(
	'wp_head',
	function () {
		if ( ! ( function_exists( 'is_product' ) && is_product() ) || 'hide' === hx_rel_opt( 'hx_rel_show' ) ) {
			return;
		}
		$cols = max( 2, min( 6, (int) hx_rel_opt( 'hx_rel_cols' ) ) );
		echo '<style id="hx-related">'
			. '.hx-rel{margin:4px 0 28px;font-family:"Cairo",var(--hx-font);letter-spacing:0}'
			. '.hx-rel--desc,.hx-rel--reviews{margin-top:28px}'
			. '.hx-rel--bottom{margin:48px 0 32px;padding-top:28px;border-top:1px solid #eceef1}'
			. '.hx-rel__title{margin:0 0 12px;color:var(--hx-ink);font-size:20px;font-weight:800;line-height:1.4;text-align:right}'
			. '.hx-rel--bottom .hx-rel__title{font-size:24px;margin-bottom:16px}'
			. '.hx-rel .hx-rel__list{display:grid;grid-template-columns:repeat(' . $cols . ',minmax(0,1fr));gap:18px 12px;margin:0;padding:0;list-style:none}'
			. '@media (max-width:749px){'
			. '.hx-rel__title,.hx-rel--bottom .hx-rel__title{font-size:18px;text-align:center}'
			. '.hx-rel .hx-rel__list{grid-template-columns:repeat(2,minmax(0,1fr));gap:14px 10px}'
			. '.hx-rel:not(.hx-rel--mgrid) .hx-rel__list{display:flex;gap:10px;overflow-x:auto;scroll-snap-type:x proximity;scrollbar-width:none;margin:0 -16px;padding:0 16px 4px;-webkit-overflow-scrolling:touch}'
			. '.hx-rel:not(.hx-rel--mgrid) .hx-rel__list::-webkit-scrollbar{display:none}'
			. '.hx-rel:not(.hx-rel--mgrid) .hx-rel__list .hx-card{flex:0 0 42%;scroll-snap-align:start}'
			. '}'
			. '</style>' . "\n";
	},
	130
);

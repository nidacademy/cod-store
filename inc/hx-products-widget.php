<?php
defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;

class HX_Products_Widget extends \Elementor\Widget_Base {

	public function get_name() { return 'hx-products'; }
	public function get_title() { return 'Products Grid (Store)'; }
	public function get_icon() { return 'eicon-products'; }
	public function get_categories() { return array( 'general' ); }
	public function get_keywords() { return array( 'products', 'woocommerce', 'grid', 'shop' ); }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => 'Products' ) );

		$this->add_control( 'limit', array(
			'label'   => 'Number of products',
			'type'    => Controls_Manager::NUMBER,
			'min'     => 1,
			'max'     => 100,
			'default' => 12,
		) );

		$this->add_responsive_control( 'columns', array(
			'label'          => 'Columns',
			'type'           => Controls_Manager::SELECT,
			'options'        => array( '1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5', '6' => '6' ),
			'default'        => '4',
			'tablet_default' => '3',
			'mobile_default' => '2',
			'selectors'      => array( '{{WRAPPER}} .hx-grid' => 'grid-template-columns: repeat({{VALUE}}, minmax(0, 1fr));' ),
		) );

		$cats = array( '' => 'All categories' );
		foreach ( get_terms( array( 'taxonomy' => 'product_cat', 'hide_empty' => false ) ) as $t ) {
			if ( ! is_wp_error( $t ) && is_object( $t ) ) {
				$cats[ $t->slug ] = $t->name;
			}
		}
		$this->add_control( 'category', array(
			'label'   => 'Category',
			'type'    => Controls_Manager::SELECT,
			'options' => $cats,
			'default' => '',
		) );

		$this->add_control( 'orderby', array(
			'label'   => 'Order by',
			'type'    => Controls_Manager::SELECT,
			'options' => array(
				'menu_order' => 'Manual order',
				'date'       => 'Newest first',
				'price'      => 'Price: low to high',
				'price-desc' => 'Price: high to low',
				'rand'       => 'Random',
			),
			'default' => 'menu_order',
		) );

		$this->add_control( 'show_compare', array(
			'label'        => 'Show old (crossed-out) price',
			'type'         => Controls_Manager::SWITCHER,
			'return_value' => 'yes',
			'default'      => 'yes',
		) );

		$this->add_control( 'ratio', array(
			'label'     => 'Image shape',
			'type'      => Controls_Manager::SELECT,
			'options'   => array( '1 / 1' => 'Square', '4 / 5' => 'Portrait 4:5', '3 / 4' => 'Portrait 3:4', '4 / 3' => 'Landscape 4:3' ),
			'default'   => '1 / 1',
			'selectors' => array( '{{WRAPPER}} .hx-card__media' => 'aspect-ratio: {{VALUE}};' ),
		) );

		$this->add_control( 'load_more', array(
			'label'        => 'Load more button',
			'type'         => Controls_Manager::SWITCHER,
			'return_value' => 'yes',
			'default'      => 'yes',
			'separator'    => 'before',
		) );
		$this->add_control( 'load_more_text', array(
			'label'     => 'Button text',
			'type'      => Controls_Manager::TEXT,
			'default'   => 'عرض المزيد من المنتجات',
			'condition' => array( 'load_more' => 'yes' ),
		) );
		$this->add_control( 'load_more_bg', array(
			'label'     => 'Button color',
			'type'      => Controls_Manager::COLOR,
			'condition' => array( 'load_more' => 'yes' ),
			'selectors' => array( '{{WRAPPER}} .hx-more__btn' => 'background-color: {{VALUE}}; border-color: {{VALUE}};' ),
		) );
		$this->add_control( 'load_more_color', array(
			'label'     => 'Button text color',
			'type'      => Controls_Manager::COLOR,
			'condition' => array( 'load_more' => 'yes' ),
			'selectors' => array( '{{WRAPPER}} .hx-more__btn' => 'color: {{VALUE}};' ),
		) );

		$this->add_control( 'search_bar', array(
			'label'        => 'Search bar + Filters button',
			'type'         => Controls_Manager::SWITCHER,
			'return_value' => 'yes',
			'default'      => 'yes',
			'separator'    => 'before',
		) );
		$this->add_control( 'search_placeholder', array(
			'label'       => 'Search placeholder',
			'type'        => Controls_Manager::TEXT,
			'default'     => 'ما الذي تبحث عنه؟',
			'description' => 'Keep the default text to have it translated automatically.',
			'condition'   => array( 'search_bar' => 'yes' ),
		) );

		$this->end_controls_section();

		$this->start_controls_section( 'style_grid', array( 'label' => 'Grid', 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_responsive_control( 'col_gap', array(
			'label'          => 'Column gap',
			'type'           => Controls_Manager::SLIDER,
			'range'          => array( 'px' => array( 'min' => 0, 'max' => 60 ) ),
			'default'        => array( 'unit' => 'px', 'size' => 8 ),
			'mobile_default' => array( 'unit' => 'px', 'size' => 4 ),
			'selectors'      => array( '{{WRAPPER}} .hx-grid' => 'column-gap: {{SIZE}}{{UNIT}};' ),
		) );
		$this->add_responsive_control( 'row_gap', array(
			'label'          => 'Row gap',
			'type'           => Controls_Manager::SLIDER,
			'range'          => array( 'px' => array( 'min' => 0, 'max' => 100 ) ),
			'default'        => array( 'unit' => 'px', 'size' => 36 ),
			'mobile_default' => array( 'unit' => 'px', 'size' => 28 ),
			'selectors'      => array( '{{WRAPPER}} .hx-grid' => 'row-gap: {{SIZE}}{{UNIT}};' ),
		) );
		$this->add_control( 'img_radius', array(
			'label'     => 'Image corner radius',
			'type'      => Controls_Manager::SLIDER,
			'range'     => array( 'px' => array( 'min' => 0, 'max' => 40 ) ),
			'selectors' => array( '{{WRAPPER}} .hx-card__media' => 'border-radius: {{SIZE}}{{UNIT}};' ),
		) );
		$this->end_controls_section();

		$this->start_controls_section( 'style_text', array( 'label' => 'Title & price', 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_control( 'title_color', array(
			'label'     => 'Title color',
			'type'      => Controls_Manager::COLOR,
			'selectors' => array( '{{WRAPPER}} .hx-card__title' => 'color: {{VALUE}};' ),
		) );
		$this->add_group_control( Group_Control_Typography::get_type(), array( 'name' => 'title_typo', 'label' => 'Title typography', 'selector' => '{{WRAPPER}} .hx-card__title' ) );
		$this->add_control( 'title_align', array(
			'label'     => 'Text alignment',
			'type'      => Controls_Manager::CHOOSE,
			'options'   => array(
				'left'   => array( 'title' => 'Left', 'icon' => 'eicon-text-align-left' ),
				'center' => array( 'title' => 'Center', 'icon' => 'eicon-text-align-center' ),
				'right'  => array( 'title' => 'Right', 'icon' => 'eicon-text-align-right' ),
			),
			'selectors' => array(
				'{{WRAPPER}} .hx-card__title' => 'text-align: {{VALUE}};',
				'{{WRAPPER}} .hx-cp'         => 'justify-content: {{VALUE}};',
			),
		) );
		$this->add_control( 'price_color', array(
			'label'     => 'Price color',
			'type'      => Controls_Manager::COLOR,
			'selectors' => array( '{{WRAPPER}} .hx-cp__now' => 'color: {{VALUE}};' ),
		) );
		$this->add_group_control( Group_Control_Typography::get_type(), array( 'name' => 'price_typo', 'label' => 'Price typography', 'selector' => '{{WRAPPER}} .hx-cp__now' ) );
		$this->add_control( 'compare_color', array(
			'label'     => 'Old price color',
			'type'      => Controls_Manager::COLOR,
			'selectors' => array( '{{WRAPPER}} .hx-cp__old' => 'color: {{VALUE}};' ),
		) );
		$this->end_controls_section();
	}

	protected function render() {
		if ( ! function_exists( 'wc_get_products' ) ) {
			echo '<p>WooCommerce is not active.</p>';
			return;
		}
		$s        = $this->get_settings_for_display();
		$limit    = max( 1, (int) ( $s['limit'] ?? 12 ) );
		$orderby  = $s['orderby'] ?? 'menu_order';
		$category = $s['category'] ?? '';
		$products = wc_get_products( hx_products_args( $orderby, $category, $limit + 1 ) );
		$has_more = count( $products ) > $limit;
		$products = array_slice( $products, 0, $limit );
		if ( 'yes' !== ( $s['show_compare'] ?? 'yes' ) ) {
			echo '<style>.elementor-element-' . esc_attr( $this->get_id() ) . ' .hx-cp__old,.elementor-element-' . esc_attr( $this->get_id() ) . ' .hx-card__badge{display:none}</style>';
		}
		if ( ! $products ) {
			echo '<p>No products found.</p>';
			return;
		}
		$lm   = 'yes' === ( $s['load_more'] ?? 'yes' );
		$more = $lm && $has_more;
		$bar  = 'yes' === ( $s['search_bar'] ?? 'yes' ) && function_exists( 'hx_home_search_bar' );
		$cfg  = array( 'limit' => $limit, 'orderby' => $orderby, 'category' => $category );
		if ( $more || $bar ) {
			wp_enqueue_script( 'hx-load-more' );
		}
		if ( function_exists( 'hx_home_hero' ) && is_front_page() ) {
			hx_home_hero();
		}
		if ( $bar ) {
			wp_enqueue_script( 'hx-home-search' );
			hx_home_search_bar( $this->get_id(), $cfg, $s['search_placeholder'] ?? '' );
		}
		printf(
			'<div class="hx-products"%s data-hx-more="%s">',
			$bar ? ' data-hx-home' : '',
			esc_attr( wp_json_encode( $cfg ) )
		);
		hx_grid( $products );
		if ( $bar ) {
			echo '<p class="hx-empty" data-hx-empty hidden>لا توجد منتجات تطابق هذا البحث.</p>';
		}
		if ( $lm && ( $more || $bar ) ) {
			$text = ! empty( $s['load_more_text'] ) ? $s['load_more_text'] : 'عرض المزيد من المنتجات';
			echo '<div class="hx-more"' . ( $more ? '' : ' hidden' ) . '><button type="button" class="hx-more__btn" data-hx-more-btn><span class="hx-more__label">' . esc_html( $text ) . '</span><span class="hx-more__spin" aria-hidden="true"></span></button></div>';
		}
		echo '</div>';
	}
}
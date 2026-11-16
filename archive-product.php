<?php
get_header();
$hx_paged_mode = ! ( is_shop() || is_product_category() ) || is_search();

if ( $hx_paged_mode ) :
	?>
	<section class="hx-wrap hx-home">
		<?php
		$hx_products = array();
		while ( have_posts() ) {
			the_post();
			$p = wc_get_product( get_the_ID() );
			if ( $p ) {
				$hx_products[] = $p;
			}
		}
		hx_grid( $hx_products );
		echo '<div class="hx-pagination">';
		the_posts_pagination();
		echo '</div>';
		?>
	</section>
	<?php
else :
	$hx_limit    = 12;
	$hx_orderby  = 'menu_order';
	$hx_current  = is_product_category() ? get_queried_object()->slug : '';
	$hx_cats_sel = $hx_current ? array( $hx_current ) : array();
	$hx_args     = hx_products_args( $hx_orderby, $hx_cats_sel, $hx_limit + 1 );
	$hx_products = wc_get_products( $hx_args );
	$hx_total    = hx_products_total( $hx_args );
	$hx_has_more = count( $hx_products ) > $hx_limit;
	$hx_products = array_slice( $hx_products, 0, $hx_limit );
	list( $hx_pmin, $hx_pmax ) = hx_price_bounds();
	$hx_terms = get_terms( array( 'taxonomy' => 'product_cat', 'hide_empty' => true, 'exclude' => array( (int) get_option( 'default_product_cat' ) ) ) );
	wp_enqueue_script( 'hx-load-more' );
	wp_enqueue_script( 'hx-filters' );
	$hx_sorts = array(
		'menu_order' => 'الترتيب',
		'date'       => 'الأحدث',
		'price'      => 'الأرخص أولاً',
		'price-desc' => 'الأغلى أولاً',
	);
	?>
	<section class="hx-wrap hx-home hx-shop">
		<?php if ( is_product_category() ) : ?>
			<h1 class="hx-archive__title" dir="auto"><?php single_term_title(); ?></h1>
		<?php endif; ?>

		<div class="hx-shop__layout">
			<aside class="hx-filters" id="hx-filters" dir="rtl" aria-label="فلترة المنتجات">
				<div class="hx-filters__head">
					<h2 class="hx-filters__title">فلترة المنتجات</h2>
					<button type="button" class="hx-filters__close" data-hx-filters-close aria-label="إغلاق">&times;</button>
				</div>
				<form id="hx-filter-form" class="hx-filters__form" onsubmit="return false">
					<fieldset class="hx-fgroup">
						<legend class="hx-fgroup__title">الفئات</legend>
						<?php foreach ( (array) $hx_terms as $t ) : ?>
							<label class="hx-check">
								<input type="checkbox" name="cat[]" value="<?php echo esc_attr( $t->slug ); ?>" <?php checked( in_array( $t->slug, $hx_cats_sel, true ) ); ?>>
								<span class="hx-check__label"><?php echo esc_html( $t->name ); ?></span>
								<span class="hx-check__n"><?php echo (int) $t->count; ?></span>
							</label>
						<?php endforeach; ?>
					</fieldset>
					<fieldset class="hx-fgroup">
						<legend class="hx-fgroup__title">السعر (درهم)</legend>
						<div class="hx-range">
							<input type="number" name="min_price" min="0" inputmode="numeric" placeholder="من <?php echo (int) $hx_pmin; ?>" aria-label="أقل سعر">
							<span class="hx-range__sep">-</span>
							<input type="number" name="max_price" min="0" inputmode="numeric" placeholder="إلى <?php echo (int) $hx_pmax; ?>" aria-label="أعلى سعر">
						</div>
					</fieldset>
					<fieldset class="hx-fgroup">
						<legend class="hx-fgroup__title">العروض</legend>
						<label class="hx-check">
							<input type="checkbox" name="on_sale" value="1">
							<span class="hx-check__label">المنتجات المخفّضة فقط</span>
						</label>
					</fieldset>
					<div class="hx-filters__actions">
						<button type="button" class="hx-filters__reset" data-hx-filters-reset>مسح الفلاتر</button>
						<button type="button" class="hx-filters__apply" data-hx-filters-close>عرض النتائج</button>
					</div>
				</form>
			</aside>
			<div class="hx-filters__overlay" data-hx-filters-close hidden></div>

			<div class="hx-shop__main">
				<div class="hx-toolbar" dir="rtl">
					<button type="button" class="hx-toolbar__filter" data-hx-filters-open aria-controls="hx-filters">
						<svg viewBox="0 0 24 24" width="18" aria-hidden="true"><path fill="currentColor" d="M3 5h18v2l-7 7v5l-4 2v-7L3 7z"/></svg>
						فلترة
					</button>
					<span class="hx-toolbar__count"><span data-hx-count><?php echo (int) $hx_total; ?></span> منتج</span>
					<label class="hx-toolbar__sort">
						<span class="screen-reader-text">ترتيب</span>
						<select name="orderby" form="hx-filter-form">
							<?php foreach ( $hx_sorts as $k => $label ) : ?>
								<option value="<?php echo esc_attr( $k ); ?>"><?php echo esc_html( $label ); ?></option>
							<?php endforeach; ?>
						</select>
					</label>
				</div>

				<?php
				printf( '<div class="hx-products" data-hx-more="%s">', esc_attr( wp_json_encode( array( 'limit' => $hx_limit, 'orderby' => $hx_orderby, 'categories' => $hx_cats_sel ) ) ) );
				hx_grid( $hx_products );
				echo '<p class="hx-empty" data-hx-empty' . ( $hx_products ? ' hidden' : '' ) . '>لا توجد منتجات تطابق هذا البحث.</p>';
				echo '<div class="hx-more"' . ( $hx_has_more ? '' : ' hidden' ) . '><button type="button" class="hx-more__btn" data-hx-more-btn><span class="hx-more__label">عرض المزيد من المنتجات</span><span class="hx-more__spin" aria-hidden="true"></span></button></div>';
				echo '</div>';
				?>
			</div>
		</div>
	</section>
	<?php
endif;
get_footer();
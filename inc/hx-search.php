<?php
/**
 * Home page search bar + filter drawer (used by the "Products Grid (Store)" Elementor widget).
 * Search matches product titles, short descriptions, SKUs, categories and every translation.
 *
 * @package hx-store
 */

defined( 'ABSPATH' ) || exit;

/** Normalise text for matching (case, accents, Arabic letter forms, diacritics). */
function hx_search_norm( $s ) {
	$s = mb_strtolower( wp_strip_all_tags( (string) $s ), 'UTF-8' );
	$s = remove_accents( $s );
	$s = preg_replace( '/[\x{064B}-\x{065F}\x{0670}\x{0640}]/u', '', $s );
	$s = strtr(
		$s,
		array(
			'أ' => 'ا',
			'إ' => 'ا',
			'آ' => 'ا',
			'ٱ' => 'ا',
			'ة' => 'ه',
			'ى' => 'ي',
			'ؤ' => 'و',
			'ئ' => 'ي',
		)
	);
	$s = preg_replace( '/[^\p{L}\p{N}]+/u', ' ', $s );
	return trim( preg_replace( '/\s+/u', ' ', $s ) );
}

/** Searchable text of every published product, cached for a day. */
function hx_search_index() {
	static $index = null;
	if ( null !== $index ) {
		return $index;
	}
	$index = get_transient( 'hx_search_index' );
	if ( is_array( $index ) ) {
		return $index;
	}
	global $wpdb;
	$index = array();
	$rows  = $wpdb->get_results( "SELECT ID, post_title, post_excerpt FROM {$wpdb->posts} WHERE post_type = 'product' AND post_status = 'publish'" );
	foreach ( $rows as $r ) {
		$parts = array( $r->post_title, $r->post_excerpt, get_post_meta( $r->ID, '_sku', true ) );
		$tr    = get_post_meta( $r->ID, '_hx_i18n', true );
		if ( is_string( $tr ) ) {
			$tr = json_decode( $tr, true );
		}
		if ( is_array( $tr ) ) {
			foreach ( $tr as $fields ) {
				if ( is_array( $fields ) ) {
					$parts[] = isset( $fields['title'] ) ? $fields['title'] : '';
					$parts[] = isset( $fields['excerpt'] ) ? $fields['excerpt'] : '';
				}
			}
		}
		$terms = get_the_terms( $r->ID, 'product_cat' );
		if ( is_array( $terms ) ) {
			foreach ( $terms as $t ) {
				$parts[] = $t->name;
				$tt      = get_term_meta( $t->term_id, '_hx_i18n', true );
				if ( is_string( $tt ) ) {
					$tt = json_decode( $tt, true );
				}
				if ( is_array( $tt ) ) {
					foreach ( $tt as $f ) {
						$parts[] = is_array( $f ) ? implode( ' ', array_filter( $f, 'is_string' ) ) : ( is_string( $f ) ? $f : '' );
					}
				}
			}
		}
		$index[ (int) $r->ID ] = ' ' . hx_search_norm( implode( ' ', array_filter( $parts, 'is_string' ) ) ) . ' ';
	}
	set_transient( 'hx_search_index', $index, DAY_IN_SECONDS );
	return $index;
}

/** IDs of products matching every word of the query. */
function hx_search_product_ids( $q ) {
	$words = array_values( array_filter( explode( ' ', hx_search_norm( $q ) ), 'strlen' ) );
	if ( ! $words ) {
		return array();
	}
	$out = array();
	foreach ( hx_search_index() as $id => $hay ) {
		$ok = true;
		foreach ( $words as $w ) {
			if ( false === mb_strpos( $hay, $w ) ) {
				$ok = false;
				break;
			}
		}
		if ( $ok ) {
			$out[] = (int) $id;
		}
	}
	return $out;
}

/* Rebuild the index whenever products, categories or translations change. */
function hx_search_flush() {
	delete_transient( 'hx_search_index' );
}
foreach ( array( 'save_post_product', 'woocommerce_update_product', 'woocommerce_new_product', 'trashed_post', 'deleted_post', 'untrashed_post', 'edited_product_cat', 'created_product_cat', 'delete_product_cat' ) as $hx_hook ) {
	add_action( $hx_hook, 'hx_search_flush' );
}
add_action(
	'updated_post_meta',
	function ( $mid, $pid, $key ) {
		if ( '_hx_i18n' === $key || '_sku' === $key ) {
			hx_search_flush();
		}
	},
	10,
	3
);

add_action(
	'wp_enqueue_scripts',
	function () {
		wp_register_script( 'hx-home-search', get_theme_file_uri( 'assets/home-search.js' ), array( 'hx-load-more' ), HX_VER, true );
	},
	22
);

/** Search bar + filter drawer markup. */
function hx_home_search_bar( $uid, $base, $placeholder = '' ) {
	$placeholder = '' !== trim( (string) $placeholder ) ? $placeholder : 'ما الذي تبحث عنه؟';
	list( $pmin, $pmax ) = hx_price_bounds();
	$terms = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => true,
			'exclude'    => array( (int) get_option( 'default_product_cat' ) ),
		)
	);
	$sorts = array(
		'menu_order' => 'الترتيب الافتراضي',
		'date'       => 'الأحدث',
		'price'      => 'الأرخص أولاً',
		'price-desc' => 'الأغلى أولاً',
	);
	$cur   = isset( $base['orderby'] ) ? $base['orderby'] : 'menu_order';
	$did   = 'hx-hs-' . $uid;
	?>
	<div class="hx-hs" dir="rtl" data-hx-hs="<?php echo esc_attr( $did ); ?>">
		<form class="hx-hs__search" role="search" action="#" data-hx-hs-form>
			<svg class="hx-hs__icon" viewBox="0 0 24 24" width="20" height="20" aria-hidden="true"><circle cx="11" cy="11" r="7" fill="none" stroke="currentColor" stroke-width="2"/><path d="M20 20l-4.2-4.2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
			<input class="hx-hs__input" type="search" name="q" placeholder="<?php echo esc_attr( $placeholder ); ?>" aria-label="<?php echo esc_attr( $placeholder ); ?>" autocomplete="off" enterkeyhint="search" dir="auto">
			<button type="button" class="hx-hs__clear" data-hx-hs-clear aria-label="مسح البحث" hidden><svg viewBox="0 0 24 24" width="14" height="14" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"/></svg></button>
			<button type="submit" class="hx-sr">بحث</button>
		</form>
		<button type="button" class="hx-hs__filter" data-hx-hs-open aria-controls="<?php echo esc_attr( $did ); ?>" aria-label="فلترة" title="فلترة">
			<svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" d="M4 7h9M17 7h3M4 17h3M11 17h9"/><circle cx="15" cy="7" r="2.2" fill="none" stroke="currentColor" stroke-width="2"/><circle cx="9" cy="17" r="2.2" fill="none" stroke="currentColor" stroke-width="2"/></svg>
			<span class="hx-hs__badge" data-hx-hs-badge hidden>0</span>
		</button>
	</div>
	<?php if ( empty( $base['category'] ) && $terms && ! is_wp_error( $terms ) ) : ?>
	<div class="hx-chips" dir="rtl" data-hx-chips>
		<button type="button" class="hx-chip is-active" data-cat="" aria-pressed="true">الكل</button>
		<?php foreach ( $terms as $t ) : ?>
			<button type="button" class="hx-chip" data-cat="<?php echo esc_attr( $t->slug ); ?>" aria-pressed="false"><?php echo esc_html( $t->name ); ?></button>
		<?php endforeach; ?>
	</div>
	<?php endif; ?>
	<aside class="hx-filters hx-filters--drawer" id="<?php echo esc_attr( $did ); ?>" dir="rtl" aria-label="فلترة المنتجات">
		<div class="hx-filters__head">
			<h2 class="hx-filters__title">فلترة المنتجات</h2>
			<button type="button" class="hx-filters__close" data-hx-hs-close aria-label="إغلاق">&times;</button>
		</div>
		<form class="hx-filters__form" data-hx-hs-filters onsubmit="return false">
			<fieldset class="hx-fgroup">
				<legend class="hx-fgroup__title">ترتيب</legend>
				<div class="hx-hs-sort">
					<select name="orderby" aria-label="ترتيب">
						<?php foreach ( $sorts as $k => $label ) : ?>
							<option value="<?php echo esc_attr( $k ); ?>" <?php selected( $cur, $k ); ?>><?php echo esc_html( $label ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
			</fieldset>
			<?php if ( $terms && ! is_wp_error( $terms ) ) : ?>
			<fieldset class="hx-fgroup">
				<legend class="hx-fgroup__title">الفئات</legend>
				<?php foreach ( $terms as $t ) : ?>
					<label class="hx-check">
						<input type="checkbox" name="cat[]" value="<?php echo esc_attr( $t->slug ); ?>">
						<span class="hx-check__label"><?php echo esc_html( $t->name ); ?></span>
						<span class="hx-check__n"><?php echo (int) $t->count; ?></span>
					</label>
				<?php endforeach; ?>
			</fieldset>
			<?php endif; ?>
			<fieldset class="hx-fgroup">
				<legend class="hx-fgroup__title">السعر (درهم)</legend>
				<div class="hx-range">
					<input type="number" name="min_price" min="0" inputmode="numeric" placeholder="من <?php echo (int) $pmin; ?>" aria-label="أقل سعر">
					<span class="hx-range__sep">-</span>
					<input type="number" name="max_price" min="0" inputmode="numeric" placeholder="إلى <?php echo (int) $pmax; ?>" aria-label="أعلى سعر">
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
				<button type="button" class="hx-filters__reset" data-hx-hs-reset>مسح الفلاتر</button>
				<button type="button" class="hx-filters__apply" data-hx-hs-close>عرض النتائج</button>
			</div>
		</form>
	</aside>
	<div class="hx-filters__overlay hx-hs-overlay" data-hx-hs-close hidden></div>
	<?php
}


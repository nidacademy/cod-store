<?php
/**
 * Customer reviews on product pages (WooCommerce reviews).
 *  - Stars + count under the product title.
 *  - "آراء العملاء" section under the description: average, 5→1 breakdown, review cards, "add your review" form.
 *  - Add reviews yourself in Products > Edit > "Add a customer review" (name, city, stars, text, photo).
 *  - Reviews sent by customers wait for approval in Products > Reviews.
 *
 * @package hx-store
 */

defined( 'ABSPATH' ) || exit;

function hx_rv_on() {
	return 'hide' !== get_theme_mod( 'hx_rv_show', 'show' );
}

/** Star icons; $rating 0–5 (fractions allowed). */
function hx_rv_stars( $rating, $class = '' ) {
	$p    = max( 0, min( 100, round( (float) $rating / 5 * 100, 1 ) ) );
	$star = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2.6l2.9 6 6.5.8-4.8 4.5 1.2 6.5L12 17.3l-5.8 3.1 1.2-6.5L2.6 9.4l6.5-.8z"/></svg>';
	$five = str_repeat( $star, 5 );
	return '<span class="hx-stars ' . esc_attr( $class ) . '" role="img" aria-label="' . esc_attr( number_format( (float) $rating, 1 ) . ' / 5' ) . '"><span class="hx-stars__bg">' . $five . '</span><span class="hx-stars__fg" style="width:' . $p . '%">' . $five . '</span></span>';
}

/** Approved reviews of a product, newest first. */
function hx_rv_get( $product_id ) {
	return get_comments(
		array(
			'post_id' => (int) $product_id,
			'status'  => 'approve',
			'type'    => 'review',
			'orderby' => 'comment_date_gmt',
			'order'   => 'DESC',
			'number'  => 200,
		)
	);
}

/** Line under the title: ★★★★★ 4.8 (23 تقييم). */
function hx_rv_stars_line( $product ) {
	if ( ! hx_rv_on() || ! $product ) {
		return;
	}
	$n = (int) $product->get_review_count();
	if ( $n < 1 ) {
		return;
	}
	$avg = (float) $product->get_average_rating();
	echo '<a class="hx-rv-line" href="#hx-reviews" dir="rtl">' . hx_rv_stars( $avg ) . '<b>' . esc_html( number_format( $avg, 1 ) ) . '</b><span>(' . (int) $n . ' تقييم)</span></a>'; // phpcs:ignore WordPress.Security.EscapeOutput
}

/** Reviews section under the description. */
function hx_rv_section( $product ) {
	if ( ! hx_rv_on() || ! $product ) {
		return;
	}
	$reviews = hx_rv_get( $product->get_id() );
	$n       = count( $reviews );
	$counts  = array( 5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0 );
	$sum     = 0;
	foreach ( $reviews as $c ) {
		$r = max( 1, min( 5, (int) get_comment_meta( $c->comment_ID, 'rating', true ) ) );
		$counts[ $r ]++;
		$sum += $r;
	}
	$avg = $n ? $sum / $n : 0;
	?>
	<section class="hx-rv" id="hx-reviews" dir="rtl" data-hx-rv>
		<h2 class="hx-rv__title">آراء العملاء</h2>
		<div class="hx-rv__sum">
			<?php if ( $n ) : ?>
				<div class="hx-rv__avg">
					<b><?php echo esc_html( number_format( $avg, 1 ) ); ?></b>
					<?php echo hx_rv_stars( $avg ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					<span><?php echo esc_html( 'بناءً على ' . $n . ' تقييم' ); ?></span>
				</div>
				<ul class="hx-rv__bars">
					<?php foreach ( $counts as $s => $c ) : ?>
						<li><span><?php echo (int) $s; ?> ★</span><i><em style="width:<?php echo esc_attr( $n ? round( $c / $n * 100 ) : 0 ); ?>%"></em></i><span><?php echo (int) $c; ?></span></li>
					<?php endforeach; ?>
				</ul>
			<?php else : ?>
				<p class="hx-rv__empty">لا توجد تقييمات بعد. كن أول من يقيّم هذا المنتج.</p>
			<?php endif; ?>
			<button type="button" class="hx-rv__add" data-hx-rv-open>أضف تقييمك</button>
		</div>

		<form class="hx-rv__form" data-hx-rv-form hidden novalidate>
			<input type="hidden" name="action" value="hx_review">
			<input type="hidden" name="product_id" value="<?php echo (int) $product->get_id(); ?>">
			<input type="text" name="website" class="hx-hp" tabindex="-1" autocomplete="off" aria-hidden="true">
			<div class="hx-rv__pick" role="radiogroup" aria-label="التقييم">
				<?php for ( $i = 5; $i >= 1; $i-- ) : ?>
					<input type="radio" id="hx-rv-s<?php echo (int) $i; ?>" name="rating" value="<?php echo (int) $i; ?>"<?php checked( 5, $i ); ?>><label for="hx-rv-s<?php echo (int) $i; ?>" title="<?php echo (int) $i; ?>">★</label>
				<?php endfor; ?>
			</div>
			<input class="hx-rv__in" type="text" name="name" placeholder="الإسم" maxlength="40" required>
			<input class="hx-rv__in" type="text" name="city" placeholder="المدينة (اختياري)" maxlength="40">
			<textarea class="hx-rv__in" name="text" rows="4" placeholder="رأيك في المنتج" maxlength="1000" required></textarea>
			<button type="submit" class="hx-rv__send">إرسال التقييم</button>
			<p class="hx-rv__msg" data-hx-rv-msg hidden></p>
		</form>

		<?php if ( $n ) : ?>
			<div class="hx-rv__list">
				<?php
				foreach ( $reviews as $i => $c ) :
					$r     = max( 1, min( 5, (int) get_comment_meta( $c->comment_ID, 'rating', true ) ) );
					$city  = (string) get_comment_meta( $c->comment_ID, 'hx_city', true );
					$photo = (int) get_comment_meta( $c->comment_ID, 'hx_photo', true );
					$name  = trim( (string) $c->comment_author );
					$name  = '' !== $name ? $name : 'زبون';
					?>
					<article class="hx-rv__card"<?php echo $i >= 4 ? ' hidden data-hx-rv-more' : ''; ?>>
						<header>
							<span class="hx-rv__av" aria-hidden="true"><?php echo esc_html( mb_substr( $name, 0, 1 ) ); ?></span>
							<span class="hx-rv__who"><b dir="auto"><?php echo esc_html( $name ); ?></b><?php echo '' !== $city ? '<small dir="auto">' . esc_html( $city ) . '</small>' : ''; ?></span>
							<?php echo hx_rv_stars( $r, 'hx-stars--sm' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
						</header>
						<p dir="auto"><?php echo nl2br( esc_html( $c->comment_content ) ); ?></p>
						<?php if ( $photo && wp_attachment_is_image( $photo ) ) : ?>
							<a class="hx-rv__img" href="<?php echo esc_url( wp_get_attachment_url( $photo ) ); ?>" target="_blank" rel="noopener"><?php echo wp_get_attachment_image( $photo, 'medium', false, array( 'loading' => 'lazy', 'alt' => '' ) ); ?></a>
						<?php endif; ?>
					</article>
				<?php endforeach; ?>
			</div>
			<?php if ( $n > 4 ) : ?>
				<button type="button" class="hx-rv__more" data-hx-rv-showmore>عرض المزيد من التقييمات</button>
			<?php endif; ?>
		<?php endif; ?>
	</section>
	<?php
}

/* ---------- Customer review form (pending until approved) ---------- */
function hx_rv_submit() {
	$fail = function ( $m ) {
		wp_send_json_error( array( 'message' => $m ), 400 );
	};
	if ( ! empty( $_POST['website'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		$fail( 'Error' );
	}
	// No nonce: product pages are cached (LiteSpeed). Spam is limited by the hidden field, the per-visitor limit and approval.
	// phpcs:disable WordPress.Security.NonceVerification
	$pid    = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
	$name   = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
	$city   = isset( $_POST['city'] ) ? sanitize_text_field( wp_unslash( $_POST['city'] ) ) : '';
	$text   = isset( $_POST['text'] ) ? sanitize_textarea_field( wp_unslash( $_POST['text'] ) ) : '';
	$rating = isset( $_POST['rating'] ) ? absint( $_POST['rating'] ) : 0;
	if ( ! $pid || 'product' !== get_post_type( $pid ) || mb_strlen( $name ) < 2 || mb_strlen( $text ) < 5 || $rating < 1 || $rating > 5 ) {
		$fail( 'المرجو كتابة الإسم ورأيك في المنتج' );
	}
	$ip  = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	$key = 'hx_rv_' . md5( $ip );
	$cnt = (int) get_transient( $key );
	if ( $cnt >= 3 ) {
		$fail( 'لقد أرسلت عدة تقييمات، المرجو المحاولة لاحقاً' );
	}
	$id = wp_insert_comment(
		array(
			'comment_post_ID'      => $pid,
			'comment_author'       => mb_substr( $name, 0, 40 ),
			'comment_author_email' => '',
			'comment_content'      => mb_substr( $text, 0, 1000 ),
			'comment_type'         => 'review',
			'comment_approved'     => 0,
			'comment_author_IP'    => $ip,
			'comment_agent'        => isset( $_SERVER['HTTP_USER_AGENT'] ) ? substr( sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ), 0, 250 ) : '',
		)
	);
	if ( ! $id ) {
		$fail( 'حدث خطأ، المرجو المحاولة مرة أخرى' );
	}
	update_comment_meta( $id, 'rating', $rating );
	update_comment_meta( $id, 'verified', 0 );
	if ( '' !== $city ) {
		update_comment_meta( $id, 'hx_city', mb_substr( $city, 0, 40 ) );
	}
	set_transient( $key, $cnt + 1, HOUR_IN_SECONDS );
	wp_send_json_success( array( 'message' => 'شكراً! سيظهر تقييمك بعد المراجعة.' ) );
}
add_action( 'wp_ajax_hx_review', 'hx_rv_submit' );
add_action( 'wp_ajax_nopriv_hx_review', 'hx_rv_submit' );

/* ---------- Admin: add a review from the product edit page ---------- */
add_action(
	'add_meta_boxes',
	function () {
		add_meta_box( 'hx_rv_add', 'Add a customer review', 'hx_rv_box', 'product', 'normal', 'default' );
	}
);

function hx_rv_box( $post ) {
	wp_nonce_field( 'hx_rv_add', 'hx_rv_nonce' );
	$p     = function_exists( 'wc_get_product' ) ? wc_get_product( $post->ID ) : null;
	$count = $p ? (int) $p->get_review_count() : 0;
	echo '<p>Add a review you received (for example on WhatsApp). It is published when you click <strong>Update</strong>. This product has <strong>' . (int) $count . '</strong> published review(s) — edit or delete them in <a href="' . esc_url( admin_url( 'edit.php?post_type=product&page=product-reviews' ) ) . '">Products &gt; Reviews</a>.</p>';
	echo '<table class="form-table" role="presentation"><tbody>';
	echo '<tr><th><label for="hx_rv_name">Name</label></th><td><input type="text" id="hx_rv_name" name="hx_rv_name" class="regular-text" dir="auto" placeholder="مثال: فاطمة"></td></tr>';
	echo '<tr><th><label for="hx_rv_city">City (optional)</label></th><td><input type="text" id="hx_rv_city" name="hx_rv_city" class="regular-text" dir="auto" placeholder="الدار البيضاء"></td></tr>';
	echo '<tr><th><label for="hx_rv_rating">Stars</label></th><td><select id="hx_rv_rating" name="hx_rv_rating">';
	for ( $i = 5; $i >= 1; $i-- ) {
		echo '<option value="' . (int) $i . '">' . str_repeat( '★', $i ) . ' (' . (int) $i . ')</option>'; // phpcs:ignore WordPress.Security.EscapeOutput
	}
	echo '</select></td></tr>';
	echo '<tr><th><label for="hx_rv_text">Review</label></th><td><textarea id="hx_rv_text" name="hx_rv_text" rows="4" class="large-text" dir="auto"></textarea></td></tr>';
	echo '<tr><th>Photo (optional)</th><td><input type="hidden" id="hx_rv_photo" name="hx_rv_photo" value=""><button type="button" class="button" id="hx_rv_photo_btn">Choose photo</button> <span id="hx_rv_photo_name"></span></td></tr>';
	echo '</tbody></table>';
	?>
	<script>
	jQuery( function ( $ ) {
		var frame;
		$( '#hx_rv_photo_btn' ).on( 'click', function ( e ) {
			e.preventDefault();
			if ( ! window.wp || ! wp.media ) { return; }
			frame = frame || wp.media( { title: 'Review photo', library: { type: 'image' }, multiple: false } );
			frame.off( 'select' ).on( 'select', function () {
				var a = frame.state().get( 'selection' ).first().toJSON();
				$( '#hx_rv_photo' ).val( a.id );
				$( '#hx_rv_photo_name' ).text( a.filename || a.title || ( '#' + a.id ) );
			} );
			frame.open();
		} );
	} );
	</script>
	<?php
}

add_action(
	'admin_enqueue_scripts',
	function ( $hook ) {
		if ( in_array( $hook, array( 'post.php', 'post-new.php' ), true ) && 'product' === get_post_type() ) {
			wp_enqueue_media();
		}
	}
);

add_action(
	'save_post_product',
	function ( $post_id ) {
		if ( ! isset( $_POST['hx_rv_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['hx_rv_nonce'] ) ), 'hx_rv_add' ) ) {
			return;
		}
		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || wp_is_post_revision( $post_id ) || ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		$text = isset( $_POST['hx_rv_text'] ) ? sanitize_textarea_field( wp_unslash( $_POST['hx_rv_text'] ) ) : '';
		if ( '' === trim( $text ) ) {
			return;
		}
		$name   = isset( $_POST['hx_rv_name'] ) ? sanitize_text_field( wp_unslash( $_POST['hx_rv_name'] ) ) : '';
		$city   = isset( $_POST['hx_rv_city'] ) ? sanitize_text_field( wp_unslash( $_POST['hx_rv_city'] ) ) : '';
		$rating = isset( $_POST['hx_rv_rating'] ) ? max( 1, min( 5, absint( $_POST['hx_rv_rating'] ) ) ) : 5;
		$photo  = isset( $_POST['hx_rv_photo'] ) ? absint( $_POST['hx_rv_photo'] ) : 0;
		unset( $_POST['hx_rv_text'] ); // only once per save.
		$id = wp_insert_comment(
			array(
				'comment_post_ID'  => $post_id,
				'comment_author'   => '' !== $name ? $name : 'زبون',
				'comment_content'  => $text,
				'comment_type'     => 'review',
				'comment_approved' => 1,
			)
		);
		if ( $id ) {
			update_comment_meta( $id, 'rating', $rating );
			update_comment_meta( $id, 'verified', 0 );
			if ( '' !== $city ) {
				update_comment_meta( $id, 'hx_city', $city );
			}
			if ( $photo && wp_attachment_is_image( $photo ) ) {
				update_comment_meta( $id, 'hx_photo', $photo );
			}
			if ( class_exists( 'WC_Comments' ) ) {
				WC_Comments::clear_transients( $post_id );
			}
		}
	},
	30
);

/* ---------- Customizer: show / hide ---------- */
add_action(
	'customize_register',
	function ( $wp_customize ) {
		$sec = $wp_customize->get_section( 'hx_pp_desc' ) ? 'hx_pp_desc' : 'hx_pp_layout';
		$wp_customize->add_setting( 'hx_rv_show', array( 'default' => 'show', 'sanitize_callback' => function ( $v ) { return 'hide' === $v ? 'hide' : 'show'; } ) );
		$wp_customize->add_control( 'hx_rv_show', array( 'label' => 'Customer reviews', 'section' => $sec, 'type' => 'select', 'priority' => 200, 'choices' => array( 'show' => 'Show', 'hide' => 'Hide' ) ) );
	},
	40
);

add_action(
	'wp_enqueue_scripts',
	function () {
		if ( ! hx_rv_on() || ! ( function_exists( 'is_product' ) && is_product() ) ) {
			return;
		}
		$v = defined( 'HX_VER' ) ? HX_VER : '1';
		wp_enqueue_style( 'hx-reviews', get_theme_file_uri( 'assets/hx-reviews.css' ), array( 'hx' ), $v );
		wp_enqueue_script( 'hx-reviews', get_theme_file_uri( 'assets/hx-reviews.js' ), array(), $v, true );
		wp_localize_script( 'hx-reviews', 'HXRV', array( 'ajax' => admin_url( 'admin-ajax.php' ) ) );
	},
	25
);

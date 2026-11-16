<?php
/**
 * Template Name: Thank you (order received)
 */
get_header();
$hx_order = null;
if ( isset( $_GET['order'], $_GET['key'] ) && function_exists( 'wc_get_order' ) ) {
	$o = wc_get_order( absint( $_GET['order'] ) );
	if ( $o && hash_equals( $o->get_order_key(), sanitize_text_field( wp_unslash( $_GET['key'] ) ) ) ) {
		$hx_order = $o;
	}
}
?>
<section class="hx-wrap hx-thanks" dir="rtl">
	<div class="hx-thanks__box">
		<div class="hx-thanks__check" aria-hidden="true">✓</div>
		<h1>شكراً لك! تم تسجيل طلبك بنجاح</h1>
		<p>سنتصل بك قريباً عبر الهاتف لتأكيد الطلب.</p>
		<?php if ( $hx_order ) : ?>
			<ul class="hx-thanks__summary">
				<li><span>رقم الطلب</span><strong>#<?php echo esc_html( $hx_order->get_order_number() ); ?></strong></li>
				<?php foreach ( $hx_order->get_items() as $item ) : ?>
					<li><span dir="auto"><?php echo esc_html( $item->get_product() ? $item->get_product()->get_name() : $item->get_name() ); ?> × <?php echo (int) $item->get_quantity(); ?></span><strong><?php echo esc_html( hx_money( $item->get_total() ) ); ?></strong></li>
				<?php endforeach; ?>
				<li><span>المجموع</span><strong><?php echo esc_html( hx_money( $hx_order->get_total() ) ); ?></strong></li>
				<li><span>طريقة الدفع</span><strong>الدفع عند الاستلام</strong></li>
			</ul>
		<?php endif; ?>
		<?php if ( function_exists( 'hx_ty_offer' ) ) { hx_ty_offer( $hx_order ); } ?>
		<a class="hx-order__btn hx-thanks__home" href="<?php echo esc_url( home_url( '/' ) ); ?>">متابعة التسوق</a>
	</div>
</section>
<?php
get_footer();
<?php
/**
 * Home hero banner / slider (Customizer > Home hero).
 * Shown above the search bar of the "Products Grid (Store)" widget on the home page.
 *
 * @package hx-store
 */

defined( 'ABSPATH' ) || exit;

function hx_hero_max() {
	return 5;
}

add_action(
	'customize_register',
	function ( $wp_customize ) {
		$wp_customize->add_section(
			'hx_hero',
			array(
				'title'       => 'Home hero (banner)',
				'priority'    => 31,
				'description' => 'Images shown at the top of the home page, above the search bar. Add 1 image for a banner, or several for a slider. Recommended size: 1600 × 600 px for desktop, 800 × 800 px for phones.',
			)
		);
		$wp_customize->add_setting( 'hx_hero_on', array( 'default' => false, 'sanitize_callback' => 'wp_validate_boolean' ) );
		$wp_customize->add_control( 'hx_hero_on', array( 'section' => 'hx_hero', 'type' => 'checkbox', 'label' => 'Show the hero on the home page' ) );

		$wp_customize->add_setting( 'hx_hero_autoplay', array( 'default' => true, 'sanitize_callback' => 'wp_validate_boolean' ) );
		$wp_customize->add_control( 'hx_hero_autoplay', array( 'section' => 'hx_hero', 'type' => 'checkbox', 'label' => 'Slide automatically (when there are 2+ images)' ) );

		$wp_customize->add_setting( 'hx_hero_speed', array( 'default' => 5, 'sanitize_callback' => 'absint' ) );
		$wp_customize->add_control( 'hx_hero_speed', array( 'section' => 'hx_hero', 'type' => 'number', 'label' => 'Seconds per slide', 'input_attrs' => array( 'min' => 2, 'max' => 20 ) ) );

		$wp_customize->add_setting( 'hx_hero_radius', array( 'default' => 16, 'sanitize_callback' => 'absint' ) );
		$wp_customize->add_control( 'hx_hero_radius', array( 'section' => 'hx_hero', 'type' => 'number', 'label' => 'Corner roundness (px)', 'input_attrs' => array( 'min' => 0, 'max' => 40 ) ) );

		$wp_customize->add_setting( 'hx_hero_full', array( 'default' => false, 'sanitize_callback' => 'wp_validate_boolean' ) );
		$wp_customize->add_control( 'hx_hero_full', array( 'section' => 'hx_hero', 'type' => 'checkbox', 'label' => 'Full width on desktop (edge to edge)', 'description' => 'Corners become square and the space above the hero is removed.' ) );
		$wp_customize->add_setting( 'hx_hero_full_m', array( 'default' => (bool) get_theme_mod( 'hx_hero_full', false ), 'sanitize_callback' => 'wp_validate_boolean' ) );
		$wp_customize->add_control( 'hx_hero_full_m', array( 'section' => 'hx_hero', 'type' => 'checkbox', 'label' => 'Full width on phones (edge to edge)' ) );

		for ( $i = 1; $i <= hx_hero_max(); $i++ ) {
			$wp_customize->add_setting( "hx_hero_img_$i", array( 'default' => '', 'sanitize_callback' => 'absint' ) );
			$wp_customize->add_control(
				new WP_Customize_Media_Control(
					$wp_customize,
					"hx_hero_img_$i",
					array(
						'section'   => 'hx_hero',
						'label'     => "Slide $i — image",
						'mime_type' => 'image',
					)
				)
			);
			$wp_customize->add_setting( "hx_hero_img_m_$i", array( 'default' => '', 'sanitize_callback' => 'absint' ) );
			$wp_customize->add_control(
				new WP_Customize_Media_Control(
					$wp_customize,
					"hx_hero_img_m_$i",
					array(
						'section'     => 'hx_hero',
						'label'       => "Slide $i — image for phones (optional)",
						'description' => 'Leave empty to use the image above.',
						'mime_type'   => 'image',
					)
				)
			);
			$wp_customize->add_setting( "hx_hero_link_$i", array( 'default' => '', 'sanitize_callback' => 'esc_url_raw' ) );
			$wp_customize->add_control( "hx_hero_link_$i", array( 'section' => 'hx_hero', 'type' => 'url', 'label' => "Slide $i — link (optional)", 'description' => 'Product, category or any page address.' ) );
		}
	},
	30
);

add_action(
	'wp_enqueue_scripts',
	function () {
		wp_register_script( 'hx-hero', get_theme_file_uri( 'assets/hero.js' ), array(), HX_VER, true );
	},
	22
);

/** Keep internal links in the visitor's language. */
function hx_hero_link( $url ) {
	$url  = trim( (string) $url );
	$home = untrailingslashit( get_option( 'home' ) );
	if ( '' === $url ) {
		return '';
	}
	if ( 0 === strpos( $url, $home ) ) {
		$path = substr( $url, strlen( $home ) );
		return home_url( '' === $path ? '/' : $path );
	}
	return $url;
}

/** Aspect ratio "w / h" of an attachment, or ''. */
function hx_hero_ratio( $id ) {
	$m = wp_get_attachment_metadata( $id );
	return ( ! empty( $m['width'] ) && ! empty( $m['height'] ) ) ? (int) $m['width'] . ' / ' . (int) $m['height'] : '';
}

function hx_home_hero() {
	static $done = false;
	if ( $done || ! get_theme_mod( 'hx_hero_on', false ) ) {
		return;
	}
	$slides = array();
	for ( $i = 1; $i <= hx_hero_max(); $i++ ) {
		$img = (int) get_theme_mod( "hx_hero_img_$i", 0 );
		if ( ! $img || ! wp_attachment_is_image( $img ) ) {
			continue;
		}
		$mob      = (int) get_theme_mod( "hx_hero_img_m_$i", 0 );
		$slides[] = array(
			'img'  => $img,
			'mob'  => ( $mob && wp_attachment_is_image( $mob ) ) ? $mob : 0,
			'link' => hx_hero_link( get_theme_mod( "hx_hero_link_$i", '' ) ),
		);
	}
	if ( ! $slides ) {
		return;
	}
	$done   = true;
	$multi  = count( $slides ) > 1;
	$ar_d   = hx_hero_ratio( $slides[0]['img'] );
	$ar_m   = $slides[0]['mob'] ? hx_hero_ratio( $slides[0]['mob'] ) : $ar_d;
	$full_d = (bool) get_theme_mod( 'hx_hero_full', false );
	$full_m = (bool) get_theme_mod( 'hx_hero_full_m', $full_d );
	$radius = (int) get_theme_mod( 'hx_hero_radius', 16 );
	$auto   = $multi && get_theme_mod( 'hx_hero_autoplay', true ) ? max( 2, (int) get_theme_mod( 'hx_hero_speed', 5 ) ) : 0;
	$style  = '--hx-hero-r:' . $radius . 'px;' . ( $ar_d ? '--hx-hero-ar:' . $ar_d . ';' : '' ) . ( $ar_m ? '--hx-hero-ar-m:' . $ar_m . ';' : '' );
	if ( $multi ) {
		wp_enqueue_script( 'hx-hero' );
	}
	?>
	<section class="hx-hero<?php echo $multi ? ' is-slider' : ''; ?><?php echo $full_d ? ' is-full-d' : ''; ?><?php echo $full_m ? ' is-full-m' : ''; ?>" style="<?php echo esc_attr( $style ); ?>" data-hx-hero="<?php echo (int) $auto; ?>" aria-roledescription="carousel">
		<div class="hx-hero__track" dir="ltr" data-hx-hero-track>
			<?php
			foreach ( $slides as $n => $s ) :
				$alt   = trim( (string) get_post_meta( $s['img'], '_wp_attachment_image_alt', true ) );
				$attrs = array(
					'class'    => 'hx-hero__img',
					'sizes'    => '(min-width: 1200px) 1200px, 100vw',
					'loading'  => $n ? 'lazy' : 'eager',
					'decoding' => 'async',
					'alt'      => $alt,
				);
				if ( ! $n ) {
					$attrs['fetchpriority'] = 'high';
				}
				$tag = $s['link'] ? 'a' : 'div';
				?>
				<<?php echo $tag; ?> class="hx-hero__slide"<?php echo $s['link'] ? ' href="' . esc_url( $s['link'] ) . '"' : ''; ?> aria-label="<?php echo esc_attr( ( $n + 1 ) . ' / ' . count( $slides ) ); ?>">
					<picture>
						<?php if ( $s['mob'] ) : ?>
							<source media="(max-width: 749px)" srcset="<?php echo esc_attr( wp_get_attachment_image_srcset( $s['mob'], 'full' ) ?: wp_get_attachment_image_url( $s['mob'], 'full' ) ); ?>" sizes="100vw">
						<?php endif; ?>
						<?php echo wp_get_attachment_image( $s['img'], 'full', false, $attrs ); ?>
					</picture>
				</<?php echo $tag; ?>>
			<?php endforeach; ?>
		</div>
		<?php if ( $multi ) : ?>
			<button type="button" class="hx-hero__arrow hx-hero__arrow--prev" data-hx-hero-prev aria-label="Previous"><svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true"><path d="M15 6l-6 6 6 6" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg></button>
			<button type="button" class="hx-hero__arrow hx-hero__arrow--next" data-hx-hero-next aria-label="Next"><svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true"><path d="M9 6l6 6-6 6" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg></button>
			<div class="hx-hero__dots" dir="ltr">
				<?php foreach ( $slides as $n => $s ) : ?>
					<button type="button" class="hx-hero__dot<?php echo $n ? '' : ' is-active'; ?>" data-hx-hero-dot="<?php echo (int) $n; ?>" aria-label="<?php echo esc_attr( ( $n + 1 ) . ' / ' . count( $slides ) ); ?>"></button>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</section>
	<?php if ( $full_d || $full_m ) : ?>
	<script>(function(){function s(){document.documentElement.style.setProperty('--hx-sbw',(window.innerWidth-document.documentElement.clientWidth)+'px');}s();document.addEventListener('DOMContentLoaded',s);window.addEventListener('load',s);window.addEventListener('resize',s);})();</script>
	<?php endif; ?>
	<?php
}

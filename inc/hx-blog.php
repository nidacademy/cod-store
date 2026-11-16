<?php
/**
 * Blog: helpers for the blog archive (home.php / archive.php / search.php), single posts and the blog sidebar.
 */
defined( 'ABSPATH' ) || exit;

/** Blog interface strings per language. */
function hx_b( $key, $n = 0 ) {
	$g                  = isset( $GLOBALS['hx_lang'] ) ? $GLOBALS['hx_lang'] : 'ar';
	$GLOBALS['hx_lang'] = 'ar';
	$r                  = hx_b_src( $key, $n );
	$GLOBALS['hx_lang'] = $g;
	return $r;
}

function hx_b_src( $key, $n = 0 ) {
	$s = array(
		'blog'       => array( 'المدونة', 'Blog', 'Blog', 'Blog' ),
		'blog_sub'   => array( 'نصائح وتجارب حقيقية كتساعدك تختار المنتوج المناسب ليك', 'Conseils et retours d’expérience pour bien choisir vos produits', 'Tips and honest experiences to help you choose the right product', 'Consejos y experiencias reales para elegir el producto adecuado' ),
		'home'       => array( 'الرئيسية', 'Accueil', 'Home', 'Inicio' ),
		'more'       => array( 'اقرأ المقال', 'Lire l’article', 'Read article', 'Leer artículo' ),
		'share'      => array( 'شارك المقال', 'Partager l’article', 'Share this article', 'Compartir el artículo' ),
		'copy'       => array( 'نسخ الرابط', 'Copier le lien', 'Copy link', 'Copiar enlace' ),
		'copied'     => array( 'تم النسخ ✓', 'Copié ✓', 'Copied ✓', 'Copiado ✓' ),
		'products'   => array( 'منتجات قد تعجبك', 'Produits qui pourraient vous plaire', 'Products you may like', 'Productos que te pueden gustar' ),
		'shop'       => array( 'تصفح المتجر', 'Voir la boutique', 'Browse the shop', 'Ver la tienda' ),
		'related'    => array( 'مقالات أخرى', 'Autres articles', 'More articles', 'Más artículos' ),
		'back'       => array( 'العودة إلى المدونة', 'Retour au blog', 'Back to the blog', 'Volver al blog' ),
		'empty'      => array( 'لا توجد مقالات بعد.', 'Aucun article pour le moment.', 'No articles yet.', 'Todavía no hay artículos.' ),
		'noresults'  => array( 'ما لقينا حتى مقال. جرّب كلمة أخرى.', 'Aucun article trouvé. Essayez un autre mot.', 'No articles found. Try another word.', 'No se encontraron artículos. Prueba otra palabra.' ),
		'prev'       => array( 'السابق', 'Précédent', 'Previous', 'Anterior' ),
		'next'       => array( 'التالي', 'Suivant', 'Next', 'Siguiente' ),
		'search'     => array( 'بحث', 'Rechercher', 'Search', 'Buscar' ),
		'search_ph'  => array( 'ابحث في المدونة…', 'Rechercher dans le blog…', 'Search the blog…', 'Buscar en el blog…' ),
		'results'    => array( 'نتائج البحث عن', 'Résultats pour', 'Results for', 'Resultados para' ),
		'cats'       => array( 'التصنيفات', 'Catégories', 'Categories', 'Categorías' ),
		'recent'     => array( 'أحدث المقالات', 'Articles récents', 'Recent articles', 'Artículos recientes' ),
	);
	$hx_mr = array(
		'blog' => 'المدونة', 'blog_sub' => 'نصائح وتجارب حقيقية كتعاونك تختار المنتوج اللي مناسب ليك', 'home' => 'الرئيسية',
		'more' => 'قرا المقال', 'share' => 'شارك المقال', 'copy' => 'نسخ الرابط', 'copied' => 'تنسخ ✓',
		'products' => 'منتوجات غادي يعجبوك', 'shop' => 'شوف المتجر', 'related' => 'مقالات خرين', 'back' => 'رجع للمدونة',
		'empty' => 'ما كاين حتى مقال دابا.', 'noresults' => 'ما لقينا حتى مقال. جرّب كلمة أخرى.', 'prev' => 'اللي قبل', 'next' => 'اللي من بعد',
		'search' => 'قلّب', 'search_ph' => 'قلّب فالمدونة…', 'results' => 'النتائج ديال', 'cats' => 'الأصناف', 'recent' => 'آخر المقالات',
	);
	$hx_ar = array(
		'blog_sub'  => 'نصائح وتجارب حقيقية تساعدك على اختيار المنتج المناسب لك',
		'noresults' => 'لم نجد أي مقال. جرّب كلمة أخرى.',
	);
	if ( 'mr' === hx_lang() ) {
		if ( 'min' === $key ) {
			$n = max( 1, (int) $n );
			return 1 === $n ? 'دقيقة ديال القراية' : ( 2 === $n ? 'جوج دقايق ديال القراية' : $n . ' دقايق ديال القراية' );
		}
		return isset( $hx_mr[ $key ] ) ? $hx_mr[ $key ] : ( isset( $s[ $key ] ) ? $s[ $key ][0] : $key );
	}
	if ( 'ar' === hx_lang() && isset( $hx_ar[ $key ] ) ) {
		return $hx_ar[ $key ];
	}
	$hx_x = array(
		'de' => array( 'blog' => 'Blog', 'blog_sub' => 'Tipps und ehrliche Erfahrungen, die Ihnen helfen, das passende Produkt zu wählen', 'home' => 'Startseite', 'more' => 'Artikel lesen', 'share' => 'Artikel teilen', 'copy' => 'Link kopieren', 'copied' => 'Kopiert ✓', 'products' => 'Produkte, die Ihnen gefallen könnten', 'shop' => 'Zum Shop', 'related' => 'Weitere Artikel', 'back' => 'Zurück zum Blog', 'empty' => 'Noch keine Artikel.', 'noresults' => 'Keine Artikel gefunden. Versuchen Sie ein anderes Wort.', 'prev' => 'Zurück', 'next' => 'Weiter', 'search' => 'Suchen', 'search_ph' => 'Im Blog suchen…', 'results' => 'Ergebnisse für', 'cats' => 'Kategorien', 'recent' => 'Neueste Artikel', 'min' => '%d Min. Lesezeit' ),
		'it' => array( 'blog' => 'Blog', 'blog_sub' => 'Consigli ed esperienze sincere per aiutarti a scegliere il prodotto giusto', 'home' => 'Home', 'more' => 'Leggi l’articolo', 'share' => 'Condividi l’articolo', 'copy' => 'Copia link', 'copied' => 'Copiato ✓', 'products' => 'Prodotti che potrebbero piacerti', 'shop' => 'Vai al negozio', 'related' => 'Altri articoli', 'back' => 'Torna al blog', 'empty' => 'Ancora nessun articolo.', 'noresults' => 'Nessun articolo trovato. Prova un’altra parola.', 'prev' => 'Precedente', 'next' => 'Successivo', 'search' => 'Cerca', 'search_ph' => 'Cerca nel blog…', 'results' => 'Risultati per', 'cats' => 'Categorie', 'recent' => 'Articoli recenti', 'min' => '%d min di lettura' ),
		'pt' => array( 'blog' => 'Blog', 'blog_sub' => 'Dicas e experiências reais para ajudar a escolher o produto certo', 'home' => 'Início', 'more' => 'Ler artigo', 'share' => 'Partilhar artigo', 'copy' => 'Copiar ligação', 'copied' => 'Copiado ✓', 'products' => 'Produtos de que pode gostar', 'shop' => 'Ver a loja', 'related' => 'Mais artigos', 'back' => 'Voltar ao blog', 'empty' => 'Ainda não há artigos.', 'noresults' => 'Nenhum artigo encontrado. Experimente outra palavra.', 'prev' => 'Anterior', 'next' => 'Seguinte', 'search' => 'Pesquisar', 'search_ph' => 'Pesquisar no blog…', 'results' => 'Resultados para', 'cats' => 'Categorias', 'recent' => 'Artigos recentes', 'min' => '%d min de leitura' ),
	);
	if ( isset( $hx_x[ hx_lang() ] ) ) {
		$x = $hx_x[ hx_lang() ];
		if ( 'min' === $key ) {
			return sprintf( $x['min'], max( 1, (int) $n ) );
		}
		return isset( $x[ $key ] ) ? $x[ $key ] : $key;
	}
	$i = array_search( hx_lang(), array( 'ar', 'fr', 'en', 'es' ), true );
	$i = false === $i ? 0 : $i;
	if ( 'min' === $key ) {
		$n = max( 1, (int) $n );
		if ( 0 === $i ) {
			if ( 1 === $n ) {
				return 'دقيقة قراءة';
			}
			if ( 2 === $n ) {
				return 'دقيقتان قراءة';
			}
			return $n . ( $n <= 10 ? ' دقائق قراءة' : ' دقيقة قراءة' );
		}
		$f = array( 1 => '%d min de lecture', 2 => '%d min read', 3 => '%d min de lectura' );
		return sprintf( $f[ $i ], $n );
	}
	return isset( $s[ $key ] ) ? $s[ $key ][ $i ] : $key;
}

function hx_blog_dir() {
	return hx_is_rtl() ? 'rtl' : 'ltr';
}

/** Blog index URL (the page set as "Posts page"). */
function hx_blog_url() {
	$id = (int) get_option( 'page_for_posts' );
	return $id ? get_permalink( $id ) : home_url( '/' );
}

/** Post content in the visitor's language (raw HTML). */
function hx_post_raw( $post ) {
	$post = get_post( $post );
	$t    = hx_tr_post( $post->ID, 'content' );
	return null !== $t ? $t : $post->post_content;
}

function hx_post_date( $post ) {
	$ts = get_post_time( 'U', false, $post );
	$m  = (int) gmdate( 'n', $ts ) - 1;
	$d  = (int) gmdate( 'j', $ts );
	$y  = gmdate( 'Y', $ts );
	switch ( hx_lang() ) {
		case 'fr':
			$months = array( 'janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre' );
			return $d . ' ' . $months[ $m ] . ' ' . $y;
		case 'en':
			$months = array( 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December' );
			return $months[ $m ] . ' ' . $d . ', ' . $y;
		case 'es':
			$months = array( 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre' );
			return $d . ' de ' . $months[ $m ] . ' de ' . $y;
		case 'de':
			$months = array( 'Januar', 'Februar', 'März', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember' );
			return $d . '. ' . $months[ $m ] . ' ' . $y;
		case 'it':
			$months = array( 'gennaio', 'febbraio', 'marzo', 'aprile', 'maggio', 'giugno', 'luglio', 'agosto', 'settembre', 'ottobre', 'novembre', 'dicembre' );
			return $d . ' ' . $months[ $m ] . ' ' . $y;
		case 'pt':
			$months = array( 'janeiro', 'fevereiro', 'março', 'abril', 'maio', 'junho', 'julho', 'agosto', 'setembro', 'outubro', 'novembro', 'dezembro' );
			return $d . ' de ' . $months[ $m ] . ' de ' . $y;
		case 'ar':
			$months = array( 'يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو', 'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر' );
			return $d . ' ' . $months[ $m ] . ' ' . $y;
		default:
			$months = array( 'يناير', 'فبراير', 'مارس', 'أبريل', 'ماي', 'يونيو', 'يوليوز', 'غشت', 'شتنبر', 'أكتوبر', 'نونبر', 'دجنبر' );
			return $d . ' ' . $months[ $m ] . ' ' . $y;
	}
}

function hx_read_minutes( $post ) {
	$text  = trim( wp_strip_all_tags( strip_shortcodes( hx_post_raw( $post ) ) ) );
	$words = '' === $text ? 0 : count( preg_split( '/\s+/u', $text ) );
	return max( 1, (int) ceil( $words / 200 ) );
}

function hx_post_excerpt( $post, $words = 28 ) {
	$post = get_post( $post );
	$t    = hx_tr_post( $post->ID, 'excerpt' );
	if ( null === $t || '' === trim( (string) $t ) ) {
		$t = hx_is_default_lang() && '' !== trim( $post->post_excerpt ) ? $post->post_excerpt : '';
	}
	if ( '' === trim( (string) $t ) ) {
		$raw = preg_replace( '#<h1[^>]*>.*?</h1>#su', '', hx_post_raw( $post ) );
		$t   = wp_strip_all_tags( strip_shortcodes( $raw ) );
	}
	return wp_trim_words( $t, $words, '…' );
}

/** First real (non-default) category of a post, or null. */
function hx_post_cat( $post ) {
	$def = (int) get_option( 'default_category' );
	foreach ( (array) get_the_category( get_post( $post )->ID ) as $c ) {
		if ( (int) $c->term_id !== $def ) {
			return $c;
		}
	}
	return null;
}

function hx_arrow() {
	return '<svg class="hx-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>';
}

function hx_post_meta_html( $post ) {
	return '<time datetime="' . esc_attr( get_post_time( 'c', false, $post ) ) . '">' . esc_html( hx_post_date( $post ) ) . '</time><span class="hx-dot" aria-hidden="true"></span><span>' . esc_html( hx_b( 'min', hx_read_minutes( $post ) ) ) . '</span>';
}

/** Blog card (list style). */
function hx_post_card( $post ) {
	$post  = get_post( $post );
	$link  = get_permalink( $post );
	$title = get_the_title( $post );
	$cat   = hx_post_cat( $post );
	?>
	<article class="hx-post-card">
		<a class="hx-post-card__media" href="<?php echo esc_url( $link ); ?>" tabindex="-1" aria-hidden="true">
			<?php
			if ( has_post_thumbnail( $post ) ) {
				echo get_the_post_thumbnail( $post, 'medium_large', array( 'loading' => 'lazy', 'alt' => esc_attr( $title ) ) );
			} else {
				echo '<span class="hx-post-card__ph"></span>';
			}
			?>
		</a>
		<div class="hx-post-card__body">
			<div class="hx-post-meta">
				<?php if ( $cat ) : ?>
					<a class="hx-post-meta__cat" href="<?php echo esc_url( get_category_link( $cat ) ); ?>"><?php echo esc_html( $cat->name ); ?></a><span class="hx-dot" aria-hidden="true"></span>
				<?php endif; ?>
				<?php echo hx_post_meta_html( $post ); ?>
			</div>
			<h2 class="hx-post-card__title"><a href="<?php echo esc_url( $link ); ?>"><?php echo esc_html( $title ); ?></a></h2>
			<p class="hx-post-card__excerpt"><?php echo esc_html( hx_post_excerpt( $post, 26 ) ); ?></p>
			<a class="hx-post-card__more" href="<?php echo esc_url( $link ); ?>"><?php echo esc_html( hx_b( 'more' ) ); ?><?php echo hx_arrow(); ?></a>
		</div>
	</article>
	<?php
}

/** Breadcrumbs. $items = [ [label, url|null], ... ] */
function hx_crumbs( $items ) {
	$out = array();
	foreach ( $items as $it ) {
		$out[] = $it[1] ? '<a href="' . esc_url( $it[1] ) . '">' . esc_html( $it[0] ) . '</a>' : '<span aria-current="page">' . esc_html( $it[0] ) . '</span>';
	}
	return '<nav class="hx-crumbs" aria-label="Breadcrumb">' . implode( '<span class="hx-crumbs__sep" aria-hidden="true">/</span>', $out ) . '</nav>';
}

/** Blog sidebar: search, categories, recent posts, products. */
function hx_blog_sidebar( $exclude = 0 ) {
	$def  = (int) get_option( 'default_category' );
	$cats = get_categories( array( 'hide_empty' => true, 'exclude' => array( $def ) ) );
	$cur  = is_category() ? (int) get_queried_object_id() : 0;
	if ( ! $cur && is_singular( 'post' ) ) {
		$pc  = hx_post_cat( get_queried_object() );
		$cur = $pc ? (int) $pc->term_id : 0;
	}
	$recent = get_posts( array( 'numberposts' => 4, 'post_status' => 'publish', 'ignore_sticky_posts' => true ) );
	$n      = (int) get_theme_mod( 'hx_blog_products_n', '3' );
	ob_start();
	?>
	<aside class="hx-side" aria-label="<?php echo esc_attr( hx_b( 'blog' ) ); ?>">
		<form class="hx-side__box hx-side-search" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
			<label class="screen-reader-text" for="hx-blog-s"><?php echo esc_html( hx_b( 'search' ) ); ?></label>
			<input type="search" id="hx-blog-s" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php echo esc_attr( hx_b( 'search_ph' ) ); ?>">
			<input type="hidden" name="post_type" value="post">
			<button type="submit" aria-label="<?php echo esc_attr( hx_b( 'search' ) ); ?>"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/></svg></button>
		</form>

		<?php if ( $cats ) : ?>
			<section class="hx-side__box">
				<h3 class="hx-side__title"><?php echo esc_html( hx_b( 'cats' ) ); ?></h3>
				<ul class="hx-side-cats">
					<?php foreach ( $cats as $c ) : ?>
						<li><a class="<?php echo $cur === (int) $c->term_id ? 'is-current' : ''; ?>" href="<?php echo esc_url( get_category_link( $c ) ); ?>"><span><?php echo esc_html( $c->name ); ?></span><em><?php echo (int) $c->count; ?></em></a></li>
					<?php endforeach; ?>
				</ul>
			</section>
		<?php endif; ?>

		<?php if ( $recent ) : ?>
			<section class="hx-side__box">
				<h3 class="hx-side__title"><?php echo esc_html( hx_b( 'recent' ) ); ?></h3>
				<ul class="hx-side-posts">
					<?php foreach ( $recent as $r ) : ?>
						<li class="<?php echo (int) $r->ID === (int) $exclude ? 'is-current' : ''; ?>">
							<a href="<?php echo esc_url( get_permalink( $r ) ); ?>"<?php echo (int) $r->ID === (int) $exclude ? ' aria-current="page"' : ''; ?>>
								<span class="hx-side-posts__img"><?php echo has_post_thumbnail( $r ) ? get_the_post_thumbnail( $r, 'thumbnail', array( 'loading' => 'lazy', 'alt' => '' ) ) : ''; ?></span>
								<span class="hx-side-posts__txt"><strong><?php echo esc_html( get_the_title( $r ) ); ?></strong><small><?php echo esc_html( hx_post_date( $r ) ); ?></small></span>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			</section>
		<?php endif; ?>

		<?php
		if ( $n > 0 && function_exists( 'wc_get_products' ) ) :
			$products = wc_get_products( array( 'status' => 'publish', 'limit' => $n, 'orderby' => 'rand', 'stock_status' => 'instock' ) );
			if ( $products ) :
				?>
				<section class="hx-side__box">
					<h3 class="hx-side__title"><?php echo esc_html( hx_b( 'products' ) ); ?></h3>
					<ul class="hx-side-products">
						<?php
						foreach ( $products as $p ) :
							list( $sale, $reg ) = hx_prices( $p );
							?>
							<li>
								<a href="<?php echo esc_url( get_permalink( $p->get_id() ) ); ?>">
									<span class="hx-side-products__img"><?php echo $p->get_image_id() ? wp_get_attachment_image( $p->get_image_id(), 'woocommerce_thumbnail', false, array( 'loading' => 'lazy', 'alt' => esc_attr( $p->get_name() ) ) ) : ''; ?></span>
									<span class="hx-side-products__txt">
										<strong dir="auto"><?php echo esc_html( $p->get_name() ); ?></strong>
										<span class="hx-side-products__price" dir="rtl"><b><?php echo hx_m( $sale ) . ' ' . hx_c(); ?></b><?php echo ( $reg && $reg > $sale ) ? '<s>' . hx_m( $reg ) . ' ' . hx_c() . '</s>' : ''; ?></span>
									</span>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
					<a class="hx-side__btn" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>"><?php echo esc_html( hx_b( 'shop' ) ); ?><?php echo hx_arrow(); ?></a>
				</section>
				<?php
			endif;
		endif;
		?>
	</aside>
	<?php
	return ob_get_clean();
}

/* Remove the first <h1> inside a post body (the template already prints the title). */
add_filter( 'the_content', function ( $c ) {
	if ( is_singular( 'post' ) && in_the_loop() && is_main_query() ) {
		$c = preg_replace( '#^\s*<h1[^>]*>.*?</h1>\s*#su', '', $c, 1 );
	}
	return $c;
}, 20 );

/* Blog search only returns articles. */
add_action( 'pre_get_posts', function ( $q ) {
	if ( ! is_admin() && $q->is_main_query() && $q->is_search() && 'post' === ( $_GET['post_type'] ?? '' ) ) {
		$q->set( 'post_type', 'post' );
	}
} );

/* Blog options (Customizer > Blog). */
add_action( 'customize_register', function ( $wp ) {
	$wp->add_section( 'hx_blog', array(
		'title'       => 'Blog',
		'priority'    => 33,
		'description' => 'Options for the blog and articles. Posts per page is set in Settings > Reading.',
	) );
	$wp->add_setting( 'hx_blog_products_n', array( 'default' => '3', 'sanitize_callback' => function ( $v ) { return in_array( (string) $v, array( '0', '2', '3', '4', '5' ), true ) ? (string) $v : '3'; } ) );
	$wp->add_control( 'hx_blog_products_n', array(
		'label'   => 'Products in the blog sidebar',
		'section' => 'hx_blog',
		'type'    => 'select',
		'choices' => array( '0' => 'None (hide)', '2' => '2', '3' => '3', '4' => '4', '5' => '5' ),
	) );
	$wp->add_setting( 'hx_blog_share', array( 'default' => true, 'sanitize_callback' => 'wp_validate_boolean' ) );
	$wp->add_control( 'hx_blog_share', array( 'label' => 'Show share buttons (WhatsApp, Facebook, copy link)', 'section' => 'hx_blog', 'type' => 'checkbox' ) );
	$wp->add_setting( 'hx_blog_related', array( 'default' => true, 'sanitize_callback' => 'wp_validate_boolean' ) );
	$wp->add_control( 'hx_blog_related', array( 'label' => 'Show "More articles" under each article', 'section' => 'hx_blog', 'type' => 'checkbox' ) );
	$wp->add_setting( 'hx_blog_sidebar_m', array( 'default' => true, 'sanitize_callback' => 'wp_validate_boolean' ) );
	$wp->add_control( 'hx_blog_sidebar_m', array( 'label' => 'Show the sidebar on mobile (below the content)', 'section' => 'hx_blog', 'type' => 'checkbox' ) );
} );

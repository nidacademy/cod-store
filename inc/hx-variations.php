<?php
/**
 * Variation picker helpers (order form).
 */
defined( 'ABSPATH' ) || exit;

/** Hex color for a common color name (Arabic, Darija, French, English, Spanish…), or '' if unknown. */
function hx_color_hex( $name ) {
	$n = trim( mb_strtolower( wp_strip_all_tags( (string) $name ) ) );
	if ( preg_match( '/^#([0-9a-f]{3}|[0-9a-f]{6})$/', $n ) ) {
		return $n;
	}
	$map = array(
		'#111111' => array( 'أسود', 'اسود', 'كحل', 'كحلة', 'noir', 'noire', 'black', 'negro', 'schwarz', 'nero', 'preto' ),
		'#ffffff' => array( 'أبيض', 'ابيض', 'بيض', 'بيضاء', 'blanc', 'blanche', 'white', 'blanco', 'weiß', 'weiss', 'bianco', 'branco' ),
		'#d32f2f' => array( 'أحمر', 'احمر', 'حمر', 'حمراء', 'rouge', 'red', 'rojo', 'rot', 'rosso', 'vermelho' ),
		'#1e63c6' => array( 'أزرق', 'ازرق', 'زرق', 'زرقاء', 'bleu', 'blue', 'azul', 'blau', 'blu' ),
		'#1f2a5a' => array( 'كحلي', 'أزرق غامق', 'زرق غامق', 'bleu marine', 'marine', 'navy', 'navy blue', 'azul marino' ),
		'#2e7d32' => array( 'أخضر', 'اخضر', 'خضر', 'خضراء', 'vert', 'verte', 'green', 'verde', 'grün', 'grun' ),
		'#f9c80e' => array( 'أصفر', 'اصفر', 'صفر', 'صفراء', 'jaune', 'yellow', 'amarillo', 'gelb', 'giallo', 'amarelo' ),
		'#f06292' => array( 'وردي', 'زهري', 'روز', 'rose', 'pink', 'rosa' ),
		'#7b3fa0' => array( 'بنفسجي', 'موف', 'violet', 'mauve', 'purple', 'morado', 'lila', 'viola', 'roxo' ),
		'#9e9e9e' => array( 'رمادي', 'رمادية', 'رمادي فاتح', 'gris', 'grise', 'grey', 'gray', 'grau', 'grigio', 'cinza' ),
		'#6d4c41' => array( 'بني', 'قهوي', 'marron', 'brun', 'brown', 'marrón', 'braun', 'marrone', 'castanho' ),
		'#e8d8bf' => array( 'بيج', 'beige', 'crème', 'creme', 'cream' ),
		'#fb8c00' => array( 'برتقالي', 'ليموني', 'orange', 'naranja', 'arancione', 'laranja' ),
		'#c9a227' => array( 'ذهبي', 'doré', 'dore', 'or', 'gold', 'golden', 'dorado', 'oro', 'dourado' ),
		'#c0c0c0' => array( 'فضي', 'فضية', 'argent', 'argenté', 'silver', 'plateado', 'silber', 'argento', 'prata' ),
		'#7b1e2b' => array( 'بوردو', 'نبيذي', 'bordeaux', 'burgundy', 'burdeos' ),
		'#8a8f4a' => array( 'كاكي', 'kaki', 'khaki', 'caqui' ),
		'#26c6da' => array( 'تركواز', 'فيروزي', 'turquoise', 'turquesa', 'türkis' ),
	);
	foreach ( $map as $hex => $names ) {
		if ( in_array( $n, $names, true ) ) {
			return $hex;
		}
	}
	return '';
}

/** Is this attribute a color attribute? */
function hx_is_color_attr( $attr_name, $label ) {
	return (bool) preg_match( '/colou?r|couleur|لون|اللون|farbe|colore|cor\b/iu', $attr_name . ' ' . $label );
}
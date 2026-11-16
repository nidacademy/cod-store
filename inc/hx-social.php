<?php
/**
 * Social media icons in the hamburger menu (after the main links).
 * Appearance > Customize > Social media: any number of links (one per line; the network is
 * recognised from the link and shown with its official colour), plus design options.
 */
defined( 'ABSPATH' ) || exit;

/**
 * Known networks: key => array( name, background (official colour, may be a gradient), solid colour, icon colour on the background, svg inner ).
 */
function hx_social_networks() {
	$st = 'fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"';
	return array(
		'facebook'  => array( 'Facebook', '#1877F2', '#1877F2', '#fff', '<path fill="currentColor" d="M14 8.5V6.6c0-.8.2-1.3 1.4-1.3H17V2.2c-.3 0-1.3-.1-2.5-.1-2.5 0-4.2 1.5-4.2 4.3v2.1H7.6v3.4h2.7V22h3.7V11.9h2.8l.4-3.4H14z"/>' ),
		'instagram' => array( 'Instagram', 'radial-gradient(circle at 30% 107%, #fdf497 0%, #fdf497 5%, #fd5949 45%, #d6249f 60%, #285AEB 90%)', '#E4405F', '#fff', '<g ' . $st . '><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4.2"/></g><circle fill="currentColor" cx="17.4" cy="6.6" r="1.2"/>' ),
		'tiktok'    => array( 'TikTok', '#000000', '#000000', '#fff', '<path fill="currentColor" d="M16.6 2h-3.3v13.4a2.9 2.9 0 1 1-2.9-2.9c.3 0 .6 0 .9.1V9.2a6.3 6.3 0 1 0 5.3 6.2V8.6a7.8 7.8 0 0 0 4.4 1.4V6.7a4.5 4.5 0 0 1-4.4-4.7z"/>' ),
		'youtube'   => array( 'YouTube', '#FF0000', '#FF0000', '#fff', '<path fill="currentColor" d="M22 8.2a3 3 0 0 0-2.1-2.1C18 5.6 12 5.6 12 5.6s-6 0-7.9.5A3 3 0 0 0 2 8.2 31 31 0 0 0 1.6 12 31 31 0 0 0 2 15.8a3 3 0 0 0 2.1 2.1c1.9.5 7.9.5 7.9.5s6 0 7.9-.5a3 3 0 0 0 2.1-2.1c.3-1.2.4-2.5.4-3.8s-.1-2.6-.4-3.8zM10 15.1V8.9l5.2 3.1L10 15.1z"/>' ),
		'x'         => array( 'X', '#000000', '#000000', '#fff', '<path fill="currentColor" d="M17.8 3h3.1l-6.8 7.8L22 21h-6.2l-4.9-6.4L5.3 21H2.2l7.3-8.3L2 3h6.4l4.4 5.8L17.8 3zm-1.1 16.2h1.7L7.4 4.7H5.6l11.1 14.5z"/>' ),
		'snapchat'  => array( 'Snapchat', '#FFFC00', '#E6CF00', '#111', '<path fill="currentColor" d="M12 2.5c2.9 0 5 2.2 5 5.2v2.4c.5.2 1.1.1 1.5-.1.5-.2 1 .3.7.8-.4.6-1.4.9-2 1.1.6 1.6 2 3 3.7 3.4.3.1.4.5.1.7-.5.4-1.4.6-2.1.7-.2.4-.2.9-.6 1-.6.1-1.3-.2-2 0-1 .3-1.8 1.6-4.3 1.6s-3.3-1.3-4.3-1.6c-.7-.2-1.4.1-2 0-.4-.1-.4-.6-.6-1-.7-.1-1.6-.3-2.1-.7-.3-.2-.2-.6.1-.7 1.7-.4 3.1-1.8 3.7-3.4-.6-.2-1.6-.5-2-1.1-.3-.5.2-1 .7-.8.4.2 1 .3 1.5.1V7.7c0-3 2.1-5.2 5-5.2z"/>' ),
		'whatsapp'  => array( 'WhatsApp', '#25D366', '#25D366', '#fff', '<path fill="currentColor" d="M12.04 2a9.9 9.9 0 0 0-8.5 15l-1.4 5.1 5.24-1.37A9.9 9.9 0 1 0 12.04 2zm0 18.1a8.2 8.2 0 0 1-4.18-1.15l-.3-.18-3.1.82.83-3.03-.2-.31a8.2 8.2 0 1 1 6.95 3.85zm4.5-6.15c-.25-.12-1.46-.72-1.69-.8-.23-.08-.39-.12-.55.12-.16.25-.63.8-.78.97-.14.16-.29.18-.53.06-.25-.12-1.04-.38-1.98-1.22-.73-.65-1.23-1.46-1.37-1.7-.14-.25-.02-.38.11-.5.11-.11.25-.29.37-.43.12-.15.16-.25.25-.41.08-.17.04-.31-.02-.43-.06-.12-.55-1.33-.76-1.82-.2-.48-.4-.41-.55-.42h-.47a.9.9 0 0 0-.65.31c-.23.25-.86.84-.86 2.05 0 1.2.88 2.37 1 2.53.12.16 1.73 2.64 4.2 3.7.59.26 1.05.41 1.4.52.6.19 1.13.16 1.56.1.48-.07 1.46-.6 1.67-1.18.2-.58.2-1.07.14-1.18-.06-.1-.22-.16-.47-.29z"/>' ),
		'telegram'  => array( 'Telegram', '#26A5E4', '#26A5E4', '#fff', '<path fill="currentColor" d="M21.9 4.3 18.7 19.4c-.2 1.1-.9 1.3-1.8.8l-4.9-3.6-2.4 2.3c-.3.3-.5.5-1 .5l.4-5 9.1-8.2c.4-.4-.1-.6-.6-.2L6.2 13.1l-4.8-1.5c-1-.3-1.1-1 .2-1.5L20.5 2.8c.9-.3 1.7.2 1.4 1.5z"/>' ),
		'pinterest' => array( 'Pinterest', '#E60023', '#E60023', '#fff', '<path fill="currentColor" d="M12 2a10 10 0 0 0-3.6 19.3c-.1-.8-.2-2 0-2.9l1.3-5.5s-.3-.7-.3-1.6c0-1.5.9-2.7 2-2.7.9 0 1.4.7 1.4 1.6 0 1-.6 2.4-.9 3.7-.3 1.1.6 2 1.7 2 2 0 3.5-2.1 3.5-5.2 0-2.7-1.9-4.6-4.7-4.6-3.2 0-5.1 2.4-5.1 4.9 0 1 .4 2 .9 2.6.1.1.1.2.1.3l-.3 1.4c-.1.2-.2.3-.4.2-1.4-.7-2.3-2.7-2.3-4.4 0-3.6 2.6-6.9 7.5-6.9 3.9 0 7 2.8 7 6.6 0 3.9-2.5 7.1-5.9 7.1-1.2 0-2.3-.6-2.6-1.3l-.7 2.7c-.3 1-1 2.2-1.4 3A10 10 0 1 0 12 2z"/>' ),
		'linkedin'  => array( 'LinkedIn', '#0A66C2', '#0A66C2', '#fff', '<path fill="currentColor" d="M4.98 3.5a2.5 2.5 0 1 1 0 5 2.5 2.5 0 0 1 0-5zM3 9.5h4V21H3V9.5zm7 0h3.8v1.6h.1c.5-1 1.8-2 3.7-2 4 0 4.7 2.6 4.7 6V21h-4v-5.2c0-1.2 0-2.8-1.7-2.8s-2 1.3-2 2.7V21h-4V9.5z"/>' ),
		'threads'   => array( 'Threads', '#000000', '#000000', '#fff', '<path fill="currentColor" d="M16.9 11.2l-.3-.1c-.2-3-1.8-4.7-4.6-4.7-1.6 0-3 .7-3.9 2l1.5 1c.6-1 1.6-1.2 2.4-1.2 1 0 1.8.3 2.2.9.3.4.5 1 .6 1.7-.8-.1-1.7-.2-2.6-.1-2.6.2-4.3 1.7-4.2 3.8.1 1.1.6 2 1.5 2.6.8.5 1.8.7 2.8.7 1.4-.1 2.5-.6 3.2-1.5.6-.7.9-1.6 1.1-2.7.7.4 1.2.9 1.4 1.5.5 1 .5 2.7-.9 4.1-1.2 1.2-2.7 1.8-5 1.8-2.5 0-4.4-.8-5.7-2.4-1.2-1.5-1.8-3.6-1.8-6.4s.6-4.9 1.8-6.4C8.4 4 10.3 3.2 12.8 3.2s4.5.8 5.8 2.4c.6.8 1.1 1.8 1.4 2.9l1.8-.5c-.4-1.4-1-2.6-1.8-3.6-1.6-2.1-4-3.1-7.2-3.1-3.1 0-5.5 1-7.1 3-1.4 1.8-2.2 4.4-2.2 7.7s.8 5.9 2.2 7.7c1.6 2 4 3 7.1 3 2.8 0 4.7-.7 6.3-2.3 2.1-2.1 2-4.7 1.3-6.3-.5-1.2-1.5-2.1-2.8-2.7zm-4.8 4.5c-1.1.1-2.2-.4-2.3-1.4 0-.7.5-1.6 2.3-1.7h.6c.7 0 1.3.1 1.8.2-.2 2.4-1.3 2.8-2.4 2.9z"/>' ),
		'messenger' => array( 'Messenger', 'linear-gradient(45deg, #0099FF 0%, #A033FF 60%, #FF5280 90%, #FF7061 100%)', '#0084FF', '#fff', '<path fill="currentColor" d="M12 2C6.4 2 2 6.1 2 11.7c0 2.9 1.2 5.4 3.2 7.2v3.1l3-1.6c.9.2 1.8.4 2.8.4 5.6 0 10-4.1 10-9.7S17.6 2 12 2zm1 13-2.5-2.7-5 2.7 5.5-5.8 2.6 2.7 4.9-2.7L13 15z"/>' ),
		'spotify'   => array( 'Spotify', '#1DB954', '#1DB954', '#fff', '<path fill="currentColor" d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20zm4.6 14.4a.6.6 0 0 1-.9.2c-2.4-1.5-5.4-1.8-9-1a.6.6 0 1 1-.3-1.2c3.9-.9 7.2-.5 9.9 1.1.3.2.4.6.3.9zm1.2-2.7a.8.8 0 0 1-1.1.3c-2.7-1.7-6.9-2.2-10.2-1.2a.8.8 0 1 1-.4-1.5c3.7-1.1 8.3-.6 11.4 1.3.4.2.5.7.3 1.1zm.1-2.8c-3.3-2-8.7-2.1-11.8-1.2a.9.9 0 1 1-.5-1.8c3.6-1.1 9.6-.9 13.3 1.4a.9.9 0 0 1-1 1.6z"/>' ),
		'discord'   => array( 'Discord', '#5865F2', '#5865F2', '#fff', '<path fill="currentColor" d="M20.3 4.4A19.6 19.6 0 0 0 15.4 3l-.6 1.3a18 18 0 0 0-5.6 0L8.6 3a19.6 19.6 0 0 0-4.9 1.5C.6 9.1-.3 13.7.2 18.2a19.8 19.8 0 0 0 6 3l1.3-2.1c-.7-.3-1.4-.6-2-1l.5-.4a14 14 0 0 0 12 0l.5.4c-.6.4-1.3.7-2 1l1.3 2.1a19.7 19.7 0 0 0 6-3c.5-5.2-.9-9.8-3.5-13.8zM8.3 15.5c-1.2 0-2.1-1.1-2.1-2.4s.9-2.4 2.1-2.4 2.2 1.1 2.1 2.4c0 1.3-.9 2.4-2.1 2.4zm7.4 0c-1.2 0-2.1-1.1-2.1-2.4s.9-2.4 2.1-2.4 2.2 1.1 2.1 2.4c0 1.3-.9 2.4-2.1 2.4z"/>' ),
		'email'     => array( 'Email', '#6B7280', '#6B7280', '#fff', '<g ' . $st . '><rect x="3" y="5" width="18" height="14" rx="2.5"/><path d="M4 7l8 6 8-6"/></g>' ),
		'phone'     => array( 'Phone', '#16A34A', '#16A34A', '#fff', '<path fill="currentColor" d="M6.62 10.79a15.05 15.05 0 0 0 6.59 6.59l2.2-2.2a1 1 0 0 1 1.02-.24c1.12.37 2.33.57 3.57.57a1 1 0 0 1 1 1V20a1 1 0 0 1-1 1A17 17 0 0 1 3 4a1 1 0 0 1 1-1h3.5a1 1 0 0 1 1 1c0 1.25.2 2.45.57 3.57a1 1 0 0 1-.25 1.02l-2.2 2.2z"/>' ),
		'website'   => array( 'Website', '#374151', '#374151', '#fff', '<g ' . $st . '><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c2.5 2.6 3.8 5.6 3.8 9s-1.3 6.4-3.8 9M12 3C9.5 5.6 8.2 8.6 8.2 12s1.3 6.4 3.8 9"/></g>' ),
	);
}

/** Which network a link belongs to. */
function hx_social_detect( $url ) {
	$u = strtolower( trim( $url ) );
	if ( 0 === strpos( $u, 'mailto:' ) ) {
		return 'email';
	}
	if ( 0 === strpos( $u, 'tel:' ) ) {
		return 'phone';
	}
	$host = (string) wp_parse_url( $u, PHP_URL_HOST );
	$host = preg_replace( '/^(www\.|m\.|mobile\.|web\.)/', '', $host );
	$map  = array(
		'facebook'  => array( 'facebook.com', 'fb.com', 'fb.me', 'fb.watch' ),
		'messenger' => array( 'm.me', 'messenger.com' ),
		'instagram' => array( 'instagram.com', 'instagr.am' ),
		'tiktok'    => array( 'tiktok.com' ),
		'youtube'   => array( 'youtube.com', 'youtu.be' ),
		'x'         => array( 'x.com', 'twitter.com' ),
		'snapchat'  => array( 'snapchat.com' ),
		'whatsapp'  => array( 'wa.me', 'whatsapp.com', 'api.whatsapp.com', 'chat.whatsapp.com' ),
		'telegram'  => array( 't.me', 'telegram.me', 'telegram.org' ),
		'pinterest' => array( 'pinterest.com', 'pin.it' ),
		'linkedin'  => array( 'linkedin.com', 'lnkd.in' ),
		'threads'   => array( 'threads.net', 'threads.com' ),
		'spotify'   => array( 'spotify.com' ),
		'discord'   => array( 'discord.gg', 'discord.com' ),
	);
	if ( 'm.me' === strtolower( (string) wp_parse_url( $u, PHP_URL_HOST ) ) ) {
		return 'messenger';
	}
	foreach ( $map as $key => $domains ) {
		foreach ( $domains as $d ) {
			if ( $host === $d || substr( $host, -strlen( '.' . $d ) ) === '.' . $d ) {
				return $key;
			}
		}
	}
	if ( preg_match( '/^pinterest\./', $host ) ) {
		return 'pinterest';
	}
	return 'website';
}

/** Saved links (one per line), cleaned. */
function hx_social_links() {
	$raw = (string) get_theme_mod( 'hx_social_links', '' );
	$out = array();
	foreach ( preg_split( '/\R/u', $raw ) as $line ) {
		$line = trim( $line );
		if ( '' === $line ) {
			continue;
		}
		if ( ! preg_match( '#^(https?://|mailto:|tel:)#i', $line ) ) {
			$line = 'https://' . $line;
		}
		$url = esc_url( $line, array( 'http', 'https', 'mailto', 'tel' ) );
		if ( $url ) {
			$out[] = $url;
		}
	}
	return $out;
}

function hx_social_icons() {
	$links = hx_social_links();
	if ( ! $links ) {
		return '';
	}
	$nets   = hx_social_networks();
	$style  = (string) get_theme_mod( 'hx_soc_style', 'brand' );
	$shape  = (string) get_theme_mod( 'hx_soc_shape', 'circle' );
	$size   = max( 28, min( 72, (int) get_theme_mod( 'hx_soc_size', 42 ) ) );
	$isize  = max( 12, min( 40, (int) get_theme_mod( 'hx_soc_icon', 20 ) ) );
	$gap    = max( 0, min( 30, (int) get_theme_mod( 'hx_soc_gap', 10 ) ) );
	$align  = (string) get_theme_mod( 'hx_soc_align', 'start' );
	$radius = 'square' === $shape ? '4px' : ( 'rounded' === $shape ? round( $size * 0.28 ) . 'px' : '50%' );
	$vars   = '--s-size:' . $size . 'px;--s-icon:' . $isize . 'px;--s-gap:' . $gap . 'px;--s-radius:' . $radius . ';--s-align:' . ( 'center' === $align ? 'center' : ( 'end' === $align ? 'flex-end' : 'flex-start' ) ) . ';';
	$fg     = sanitize_hex_color( get_theme_mod( 'hx_soc_fg', '' ) );
	$bg     = sanitize_hex_color( get_theme_mod( 'hx_soc_bg', '' ) );
	if ( $fg ) {
		$vars .= '--s-fg:' . $fg . ';';
	}
	if ( $bg ) {
		$vars .= '--s-bg:' . $bg . ';';
	}
	$cls = 'hx-social hx-social--' . sanitize_html_class( in_array( $style, array( 'brand', 'tint', 'outline', 'icon', 'mono' ), true ) ? $style : 'brand' );
	if ( 'none' !== get_theme_mod( 'hx_soc_hover', 'lift' ) ) {
		$cls .= ' hx-social--lift';
	}
	$h = '';
	foreach ( $links as $url ) {
		$key = hx_social_detect( $url );
		$n   = $nets[ $key ];
		$ext = ( 'email' === $key || 'phone' === $key ) ? '' : ' target="_blank" rel="noopener"';
		$h  .= '<a class="hx-social__a hx-social__a--' . esc_attr( $key ) . '" href="' . esc_url( $url, array( 'http', 'https', 'mailto', 'tel' ) ) . '"' . $ext . ' aria-label="' . esc_attr( $n[0] ) . '" style="--b:' . esc_attr( $n[1] ) . ';--c:' . esc_attr( $n[2] ) . ';--f:' . esc_attr( $n[3] ) . '"><svg viewBox="0 0 24 24" aria-hidden="true">' . $n[4] . '</svg></a>';
	}
	$title = '';
	if ( get_theme_mod( 'hx_soc_title_on', true ) ) {
		$t     = trim( (string) get_theme_mod( 'hx_soc_title', '' ) );
		$title = '<p class="hx-social__t">' . esc_html( '' !== $t ? $t : 'تابعونا' ) . '</p>';
	}
	return '<div class="' . esc_attr( $cls ) . '" style="' . esc_attr( $vars ) . '">' . $title . '<div class="hx-social__list">' . $h . '</div></div>';
}

add_action(
	'customize_register',
	function ( $wp_customize ) {
		$wp_customize->add_section(
			'hx_social',
			array(
				'title'       => 'Social media',
				'priority'    => 30,
				'description' => 'Icons shown in the hamburger menu, under the main links.',
			)
		);
		$text = function ( $v ) {
			return sanitize_textarea_field( $v );
		};
		$wp_customize->add_setting( 'hx_social_links', array( 'default' => '', 'sanitize_callback' => $text ) );
		$wp_customize->add_control(
			'hx_social_links',
			array(
				'label'       => 'Social media links',
				'description' => '<strong>One link per line</strong>, as many as you want, in the order you want them shown.<br>The network is recognised automatically and shown with its official colour:<ul style="margin:6px 0 8px 16px;list-style:disc"><li>Facebook, Instagram, TikTok, YouTube, X (Twitter), Snapchat</li><li>WhatsApp <code>wa.me/...</code>, Telegram <code>t.me/...</code>, Messenger <code>m.me/...</code></li><li>Pinterest, LinkedIn, Threads, Spotify, Discord</li><li>Email <code>mailto:you@mail.com</code> &middot; Phone <code>tel:+212...</code></li></ul>Any other link shows a globe icon.',
				'section'     => 'hx_social',
				'type'        => 'textarea',
				'input_attrs' => array(
					'rows'        => 7,
					'placeholder' => "https://facebook.com/yourpage\nhttps://instagram.com/yourpage\nhttps://tiktok.com/@yourpage",
					'dir'         => 'ltr',
				),
			)
		);
		$sel = function ( $id, $label, $choices, $default ) use ( $wp_customize ) {
			$wp_customize->add_setting(
				$id,
				array(
					'default'           => $default,
					'sanitize_callback' => function ( $v ) use ( $choices, $default ) { return array_key_exists( (string) $v, $choices ) ? (string) $v : $default; },
				)
			);
			$wp_customize->add_control( $id, array( 'label' => $label, 'section' => 'hx_social', 'type' => 'select', 'choices' => $choices ) );
		};
		$num = function ( $id, $label, $min, $max, $default ) use ( $wp_customize ) {
			$wp_customize->add_setting(
				$id,
				array(
					'default'           => $default,
					'sanitize_callback' => function ( $v ) use ( $min, $max, $default ) { return '' === trim( (string) $v ) ? $default : max( $min, min( $max, (int) $v ) ); },
				)
			);
			$wp_customize->add_control( $id, array( 'label' => $label, 'section' => 'hx_social', 'type' => 'number', 'input_attrs' => array( 'min' => $min, 'max' => $max, 'step' => 1 ) ) );
		};
		$sel(
			'hx_soc_style',
			'Icon style',
			array(
				'brand'   => 'Official colour background, white icon (default)',
				'tint'    => 'Light tint of the official colour, coloured icon',
				'outline' => 'Outline in the official colour',
				'icon'    => 'Coloured icon only (no background)',
				'mono'    => 'Single colour (choose below), official colour on hover',
			),
			'brand'
		);
		$sel( 'hx_soc_shape', 'Shape', array( 'circle' => 'Circle', 'rounded' => 'Rounded square', 'square' => 'Square' ), 'circle' );
		$num( 'hx_soc_size', 'Button size (px)', 28, 72, 42 );
		$num( 'hx_soc_icon', 'Icon size (px)', 12, 40, 20 );
		$num( 'hx_soc_gap', 'Space between icons (px)', 0, 30, 10 );
		$sel( 'hx_soc_align', 'Alignment', array( 'start' => 'Start (default)', 'center' => 'Center', 'end' => 'End' ), 'start' );
		$sel( 'hx_soc_hover', 'Hover effect', array( 'lift' => 'Lift + shadow (default)', 'none' => 'None' ), 'lift' );
		foreach ( array( 'hx_soc_fg' => 'Icon colour ("Single colour" style)', 'hx_soc_bg' => 'Background colour ("Single colour" style)' ) as $id => $label ) {
			$wp_customize->add_setting( $id, array( 'default' => '', 'sanitize_callback' => 'sanitize_hex_color' ) );
			$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, $id, array( 'label' => $label, 'section' => 'hx_social' ) ) );
		}
		$wp_customize->add_setting( 'hx_soc_title_on', array( 'default' => true, 'sanitize_callback' => 'wp_validate_boolean' ) );
		$wp_customize->add_control( 'hx_soc_title_on', array( 'label' => 'Show the title above the icons', 'section' => 'hx_social', 'type' => 'checkbox' ) );
		$wp_customize->add_setting( 'hx_soc_title', array( 'default' => '', 'sanitize_callback' => 'sanitize_text_field' ) );
		$wp_customize->add_control( 'hx_soc_title', array( 'label' => 'Title text (default: تابعونا)', 'section' => 'hx_social', 'type' => 'text' ) );
	}
);

<?php
/**
 * Two-language content without a translation plugin (used when TranslatePress is not active).
 *
 * - WordPress & WooCommerce texts follow the visitor's language (cookie jw_lang, switched with ?lang=ar|en).
 * - Page cache (LiteSpeed) keeps one copy per language.
 * - Products, pages, posts: optional Arabic / English title, short description and content (box "الترجمة / Translation").
 * - Product categories, tags and attribute terms: optional Arabic / English name and description.
 * - Menu items: write the label as "عربي | English" and each language shows its own half.
 * Empty fields fall back to the original text, so nothing breaks before you translate.
 */
defined( 'ABSPATH' ) || exit;

/** Current front-end language: 'ar' or 'en'. */
function jw_lang() {
	if ( jw_has_trp() ) {
		return 0 === strpos( get_locale(), 'ar' ) ? 'ar' : 'en';
	}
	return jw_fallback_lang();
}

/** True on visitor-facing requests (including WooCommerce AJAX) where translations should apply. */
function jw_i18n_on() {
	return ! jw_has_trp() && ! is_admin() && empty( $GLOBALS['jw_no_i18n'] );
}

/* ---------- WordPress & WooCommerce strings ----------
 * The theme loads after WordPress has loaded its own texts in the site language, so reload them
 * (and the date/direction settings) in the visitor's language. WooCommerce loads its texts later and
 * already follows the "locale" filter in inc/setup.php.
 */
add_action(
	'after_setup_theme',
	function () {
		if ( is_admin() || jw_has_trp() ) {
			return;
		}
		$want = 'ar' === jw_fallback_lang() ? 'ar' : 'en_US';
		global $wp_locale;
		load_default_textdomain( $want );
		$wp_locale = new WP_Locale(); // phpcs:ignore WordPress.WP.GlobalVariablesOverride
		if ( 'ar' === $want ) {
			$wp_locale->text_direction = 'rtl';
		}
	},
	0
);

/* Page cache: one cached copy per language. */
add_filter(
	'litespeed_vary_cookies',
	function ( $list ) {
		$list[] = 'jw_lang';
		return $list;
	}
);
add_action(
	'send_headers',
	function () {
		if ( ! is_admin() && ! jw_has_trp() && ! headers_sent() ) {
			header( 'X-LiteSpeed-Vary: cookie=jw_lang', false );
		}
	}
);

/* Never store translated product names in orders: orders keep the original names. */
add_action( 'woocommerce_checkout_process', fn() => $GLOBALS['jw_no_i18n'] = true ); // phpcs:ignore
add_action( 'woocommerce_store_api_checkout_update_order_from_request', fn() => $GLOBALS['jw_no_i18n'] = true ); // phpcs:ignore

/* ---------- Posts, pages, products ---------- */
function jw_i18n_post_types() {
	return array( 'product', 'page', 'post' );
}
function jw_tr_meta( $post_id, $field ) {
	$v = get_post_meta( $post_id, '_jw_' . $field . '_' . jw_lang(), true );
	return is_string( $v ) ? trim( $v ) : '';
}

add_filter(
	'the_title',
	function ( $title, $id = 0 ) {
		if ( ! $id || ! jw_i18n_on() || ! in_array( get_post_type( $id ), jw_i18n_post_types(), true ) ) {
			return $title;
		}
		return jw_tr_meta( $id, 'title' ) ?: $title;
	},
	1,
	2
);
add_filter(
	'woocommerce_product_get_name',
	function ( $name, $product ) {
		return jw_i18n_on() ? ( jw_tr_meta( $product->get_id(), 'title' ) ?: $name ) : $name;
	},
	1,
	2
);
add_filter(
	'woocommerce_product_variation_get_name',
	function ( $name, $variation ) {
		if ( ! jw_i18n_on() ) {
			return $name;
		}
		$parent = jw_tr_meta( $variation->get_parent_id(), 'title' );
		if ( ! $parent ) {
			return $name;
		}
		$orig = get_post_field( 'post_title', $variation->get_parent_id() );
		return $orig && 0 === strpos( $name, $orig ) ? $parent . substr( $name, strlen( $orig ) ) : $name;
	},
	1,
	2
);
add_filter(
	'woocommerce_short_description',
	function ( $text ) {
		global $post;
		return jw_i18n_on() && $post ? ( jw_tr_meta( $post->ID, 'excerpt' ) ?: $text ) : $text;
	},
	1
);
add_filter(
	'the_content',
	function ( $content ) {
		$id = get_the_ID();
		if ( ! $id || ! jw_i18n_on() || ! in_array( get_post_type( $id ), jw_i18n_post_types(), true ) ) {
			return $content;
		}
		return jw_tr_meta( $id, 'content' ) ?: $content;
	},
	1
);
add_filter(
	'get_the_excerpt',
	function ( $excerpt, $post = null ) {
		$post = get_post( $post );
		if ( ! $post || ! jw_i18n_on() || ! in_array( $post->post_type, jw_i18n_post_types(), true ) ) {
			return $excerpt;
		}
		return jw_tr_meta( $post->ID, 'excerpt' ) ?: $excerpt;
	},
	1,
	2
);

/* Edit screen box. */
add_action(
	'add_meta_boxes',
	function () {
		foreach ( jw_i18n_post_types() as $pt ) {
			add_meta_box( 'jw_i18n', 'الترجمة / Translation', 'jw_i18n_box', $pt, 'normal', 'high' );
		}
	}
);
function jw_i18n_box( $post ) {
	wp_nonce_field( 'jw_i18n_save', 'jw_i18n_nonce' );
	$is_product = 'product' === $post->post_type;
	echo '<p style="margin-top:0;color:#646970">اترك الحقل فارغاً لاستخدام النص الأصلي. / Leave empty to use the original text.</p>';
	echo '<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">';
	foreach ( array( 'ar' => 'العربية', 'en' => 'English' ) as $l => $label ) {
		$dir = 'ar' === $l ? 'rtl' : 'ltr';
		echo '<div dir="' . esc_attr( $dir ) . '"><h4 style="margin:0 0 8px">' . esc_html( $label ) . '</h4>';
		echo '<p><label><b>' . ( 'ar' === $l ? 'العنوان' : 'Title' ) . '</b><br><input type="text" class="widefat" name="jw_i18n[title_' . esc_attr( $l ) . ']" value="' . esc_attr( get_post_meta( $post->ID, '_jw_title_' . $l, true ) ) . '"></label></p>';
		echo '<p><label><b>' . ( $is_product ? ( 'ar' === $l ? 'الوصف المختصر' : 'Short description' ) : ( 'ar' === $l ? 'المقتطف' : 'Excerpt' ) ) . '</b><br><textarea class="widefat" rows="3" name="jw_i18n[excerpt_' . esc_attr( $l ) . ']">' . esc_textarea( get_post_meta( $post->ID, '_jw_excerpt_' . $l, true ) ) . '</textarea></label></p>';
		echo '<p><label><b>' . ( 'ar' === $l ? 'المحتوى / الوصف الكامل' : 'Content / full description' ) . '</b><br><textarea class="widefat" rows="7" name="jw_i18n[content_' . esc_attr( $l ) . ']">' . esc_textarea( get_post_meta( $post->ID, '_jw_content_' . $l, true ) ) . '</textarea></label></p>';
		echo '</div>';
	}
	echo '</div>';
}
add_action(
	'save_post',
	function ( $post_id ) {
		if ( ! isset( $_POST['jw_i18n_nonce'], $_POST['jw_i18n'] ) || ! wp_verify_nonce( sanitize_key( $_POST['jw_i18n_nonce'] ), 'jw_i18n_save' ) || ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		$in = (array) wp_unslash( $_POST['jw_i18n'] ); // phpcs:ignore
		foreach ( array( 'title', 'excerpt', 'content' ) as $f ) {
			foreach ( array( 'ar', 'en' ) as $l ) {
				$v = isset( $in[ $f . '_' . $l ] ) ? (string) $in[ $f . '_' . $l ] : '';
				$v = 'title' === $f ? sanitize_text_field( $v ) : wp_kses_post( $v );
				if ( '' === trim( $v ) ) {
					delete_post_meta( $post_id, '_jw_' . $f . '_' . $l );
				} else {
					update_post_meta( $post_id, '_jw_' . $f . '_' . $l, $v );
				}
			}
		}
	}
);

/* ---------- Categories, tags, attribute terms ---------- */
function jw_i18n_taxonomies() {
	$t = array( 'product_cat', 'product_tag', 'category' );
	if ( function_exists( 'wc_get_attribute_taxonomy_names' ) ) {
		$t = array_merge( $t, wc_get_attribute_taxonomy_names() );
	}
	return $t;
}
add_filter(
	'get_term',
	function ( $term, $taxonomy ) {
		if ( ! jw_i18n_on() || ! $term instanceof WP_Term || ! in_array( $taxonomy, jw_i18n_taxonomies(), true ) ) {
			return $term;
		}
		$l = jw_lang();
		$n = trim( (string) get_term_meta( $term->term_id, 'jw_name_' . $l, true ) );
		$d = trim( (string) get_term_meta( $term->term_id, 'jw_desc_' . $l, true ) );
		if ( $n ) {
			$term->name = $n;
		}
		if ( $d ) {
			$term->description = $d;
		}
		return $term;
	},
	10,
	2
);
add_action(
	'admin_init',
	function () {
		foreach ( jw_i18n_taxonomies() as $tax ) {
			add_action( "{$tax}_add_form_fields", 'jw_i18n_term_fields_add' );
			add_action( "{$tax}_edit_form_fields", 'jw_i18n_term_fields_edit' );
			add_action( "created_{$tax}", 'jw_i18n_term_save' );
			add_action( "edited_{$tax}", 'jw_i18n_term_save' );
		}
	}
);
function jw_i18n_term_fields_add() {
	wp_nonce_field( 'jw_i18n_term', 'jw_i18n_term_nonce' );
	foreach ( array( 'ar' => 'الاسم بالعربي', 'en' => 'Name in English' ) as $l => $label ) {
		echo '<div class="form-field"><label>' . esc_html( $label ) . '</label><input type="text" name="jw_name_' . esc_attr( $l ) . '" dir="' . ( 'ar' === $l ? 'rtl' : 'ltr' ) . '"></div>';
	}
}
function jw_i18n_term_fields_edit( $term ) {
	wp_nonce_field( 'jw_i18n_term', 'jw_i18n_term_nonce' );
	foreach ( array( 'ar' => array( 'الاسم بالعربي', 'الوصف بالعربي' ), 'en' => array( 'Name in English', 'Description in English' ) ) as $l => $labels ) {
		$dir = 'ar' === $l ? 'rtl' : 'ltr';
		echo '<tr class="form-field"><th scope="row"><label>' . esc_html( $labels[0] ) . '</label></th><td><input type="text" dir="' . esc_attr( $dir ) . '" name="jw_name_' . esc_attr( $l ) . '" value="' . esc_attr( get_term_meta( $term->term_id, 'jw_name_' . $l, true ) ) . '"></td></tr>';
		echo '<tr class="form-field"><th scope="row"><label>' . esc_html( $labels[1] ) . '</label></th><td><textarea dir="' . esc_attr( $dir ) . '" rows="3" name="jw_desc_' . esc_attr( $l ) . '">' . esc_textarea( get_term_meta( $term->term_id, 'jw_desc_' . $l, true ) ) . '</textarea><p class="description">اتركه فارغاً لاستخدام النص الأصلي.</p></td></tr>';
	}
}
function jw_i18n_term_save( $term_id ) {
	if ( ! isset( $_POST['jw_i18n_term_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['jw_i18n_term_nonce'] ), 'jw_i18n_term' ) || ! current_user_can( 'manage_categories' ) ) {
		return;
	}
	foreach ( array( 'ar', 'en' ) as $l ) {
		foreach ( array( 'name', 'desc' ) as $f ) {
			$k = 'jw_' . $f . '_' . $l;
			if ( ! isset( $_POST[ $k ] ) ) {
				continue;
			}
			$v = 'name' === $f ? sanitize_text_field( wp_unslash( $_POST[ $k ] ) ) : wp_kses_post( wp_unslash( $_POST[ $k ] ) );
			if ( '' === trim( $v ) ) {
				delete_term_meta( $term_id, $k );
			} else {
				update_term_meta( $term_id, $k, $v );
			}
		}
	}
}

/* ---------- Menus: "عربي | English" ---------- */
function jw_i18n_split( $text ) {
	if ( ! is_string( $text ) || false === strpos( $text, '|' ) || ! jw_i18n_on() ) {
		return $text;
	}
	$parts = array_map( 'trim', explode( '|', $text, 2 ) );
	$pick  = 'en' === jw_lang() ? $parts[1] : $parts[0];
	return '' !== $pick ? $pick : $text;
}
add_filter( 'nav_menu_item_title', 'jw_i18n_split', 5 );
add_filter(
	'wp_nav_menu_objects',
	function ( $items ) {
		foreach ( $items as $it ) {
			$it->title = jw_i18n_split( $it->title );
			if ( ! empty( $it->attr_title ) ) {
				$it->attr_title = jw_i18n_split( $it->attr_title );
			}
		}
		return $items;
	},
	5
);
/* Site title & tagline can use the same "عربي | English" format. */
add_filter( 'option_blogname', fn( $v ) => is_admin() ? $v : jw_i18n_split( $v ) );
add_filter( 'option_blogdescription', fn( $v ) => is_admin() ? $v : jw_i18n_split( $v ) );
/* Browser tab title of a translated product / page. */
add_filter(
	'single_post_title',
	function ( $title, $post ) {
		return $post && jw_i18n_on() && in_array( $post->post_type, jw_i18n_post_types(), true ) ? ( jw_tr_meta( $post->ID, 'title' ) ?: $title ) : $title;
	},
	1,
	2
);

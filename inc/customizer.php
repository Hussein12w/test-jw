<?php
/** Appearance → Customize → JW Store (texts in Arabic and English). */
defined( 'ABSPATH' ) || exit;

/** Theme setting in the current language (falls back to the other language). */
function jw_mod( $key, $default = '' ) {
	$l = is_rtl() ? 'ar' : 'en';
	$v = get_theme_mod( $key . '_' . $l );
	if ( '' === (string) $v ) {
		$v = get_theme_mod( $key . '_' . ( 'ar' === $l ? 'en' : 'ar' ) );
	}
	return '' === (string) $v ? $default : $v;
}

add_action(
	'customize_register',
	function ( $c ) {
		$c->add_panel( 'jw', array( 'title' => 'JW Store', 'priority' => 25 ) );
		$secs = array(
			'jw_header' => 'الهيدر والتواصل / Header & contact',
			'jw_hero'   => 'السلايدر / Hero slider',
			'jw_home'   => 'أقسام الرئيسية / Home sections',
			'jw_shop'   => 'المتجر / Shop',
			'jw_social' => 'التواصل الاجتماعي / Social',
		);
		foreach ( $secs as $id => $t ) {
			$c->add_section( $id, array( 'title' => $t, 'panel' => 'jw' ) );
		}
		$txt = function ( $id, $label, $sec, $type = 'text', $default = '' ) use ( $c ) {
			$san = array( 'url' => 'esc_url_raw', 'number' => 'absint', 'checkbox' => 'absint', 'textarea' => 'sanitize_textarea_field', 'email' => 'sanitize_email' )[ $type ] ?? 'sanitize_text_field';
			$c->add_setting( $id, array( 'default' => $default, 'sanitize_callback' => $san ) );
			$c->add_control( $id, array( 'label' => $label, 'section' => $sec, 'type' => $type ) );
		};
		$both = function ( $id, $label, $sec, $type = 'text', $ar = '', $en = '' ) use ( $txt ) {
			$txt( $id . '_ar', $label . ' (عربي)', $sec, $type, $ar );
			$txt( $id . '_en', $label . ' (English)', $sec, $type, $en );
		};
		$img = function ( $id, $label, $sec ) use ( $c ) {
			$c->add_setting( $id, array( 'sanitize_callback' => 'esc_url_raw' ) );
			$c->add_control( new WP_Customize_Image_Control( $c, $id, array( 'label' => $label, 'section' => $sec ) ) );
		};

		/* Header & contact */
		$both( 'jw_topbar', 'نص الشريط العلوي', 'jw_header', 'text', 'توصيل سريع لكل الإمارات ودول الخليج', 'Fast delivery across the UAE & GCC' );
		$both( 'jw_notice', 'تنبيه (مثل أوقات مركز التركيب) — اتركه فارغاً لإخفائه', 'jw_header', 'textarea' );
		$img( 'jw_logo_white', 'الشعار الأبيض (للفوتر)', 'jw_header' );
		$txt( 'jw_whatsapp', 'رقم واتساب (مثال 971501234567)', 'jw_header' );
		$txt( 'jw_phone', 'رقم الهاتف', 'jw_header' );
		$txt( 'jw_email', 'البريد الإلكتروني', 'jw_header', 'email' );
		$both( 'jw_address', 'العنوان', 'jw_header', 'text', 'العين، الإمارات العربية المتحدة', 'Al Ain, United Arab Emirates' );
		$txt( 'jw_track_url', 'رابط تتبع الطلب (اختياري)', 'jw_header', 'url' );
		$txt( 'jw_topbar_sc', 'Shortcode في الشريط العلوي (مثل مبدّل العملة)', 'jw_header' );
		$txt( 'jw_default_lang', 'اللغة الافتراضية بدون TranslatePress (ar / en)', 'jw_header', 'text', 'ar' );

		/* Hero */
		for ( $i = 1; $i <= 3; $i++ ) {
			$img( "jw_hero{$i}_img", "الشريحة {$i} — الصورة", 'jw_hero' );
			$both( "jw_hero{$i}_title", "الشريحة {$i} — العنوان", 'jw_hero', 'text', 1 === $i ? 'جهّز سيارتك على ذوقك' : '', 1 === $i ? 'Build your ride your way' : '' );
			$both( "jw_hero{$i}_sub", "الشريحة {$i} — النص", 'jw_hero', 'textarea', 1 === $i ? 'إكسسوارات أصلية ومميزة لجمني وجيتور وديفندر — مع خدمة التركيب' : '', 1 === $i ? 'Premium accessories for Jimny, Jetour & Defender — with installation service' : '' );
			$txt( "jw_hero{$i}_link", "الشريحة {$i} — رابط الزر", 'jw_hero', 'url' );
		}

		/* Home sections */
		for ( $i = 1; $i <= 4; $i++ ) {
			$both( "jw_feat{$i}", "ميزة {$i}", 'jw_home' );
		}
		$txt( 'jw_home_featured', 'إظهار «منتجات مميزة»', 'jw_home', 'checkbox', 1 );
		$txt( 'jw_home_new', 'إظهار «وصل حديثاً»', 'jw_home', 'checkbox', 1 );
		$txt( 'jw_home_sale', 'إظهار «العروض»', 'jw_home', 'checkbox', 1 );
		$txt( 'jw_home_best', 'إظهار «الأكثر مبيعاً»', 'jw_home', 'checkbox', 0 );
		$txt( 'jw_home_cats', 'أقسام الفئات في الرئيسية — روابط الفئات (slug) مفصولة بفواصل. فارغ = كل الفئات الرئيسية', 'jw_home' );
		$txt( 'jw_home_count', 'عدد المنتجات في كل قسم', 'jw_home', 'number', 10 );
		$img( 'jw_offer_img', 'بانر العروض — الصورة', 'jw_home' );
		$both( 'jw_offer_title', 'بانر العروض — العنوان', 'jw_home', 'text', 'عروض خاصة', 'Special offers' );
		$both( 'jw_offer_sub', 'بانر العروض — النص', 'jw_home', 'text', 'خصومات على أفضل الإكسسوارات لفترة محدودة', 'Discounts on top accessories for a limited time' );
		$txt( 'jw_offer_link', 'بانر العروض — الرابط', 'jw_home', 'url' );
		$txt( 'jw_home_steps', 'إظهار «كيف نعمل»', 'jw_home', 'checkbox', 1 );

		/* Shop */
		$txt( 'jw_hide_oos', 'إخفاء المنتجات غير المتوفرة من أقسام الرئيسية', 'jw_shop', 'checkbox', 1 );
		$txt( 'jw_per_page', 'عدد المنتجات في صفحة المتجر', 'jw_shop', 'number', 20 );
		$txt( 'jw_side_cart', 'فتح السلة الجانبية بعد الإضافة', 'jw_shop', 'checkbox', 1 );
		$txt( 'jw_buy_now', 'زر «اشترِ الآن» في صفحة المنتج', 'jw_shop', 'checkbox', 1 );
		$txt( 'jw_free_ship', 'حد الشحن المجاني لشريط التقدّم في السلة (0 = إخفاء)', 'jw_shop', 'number', 0 );
		$img( 'jw_side_banner', 'بانر جانبي في صفحة المتجر', 'jw_shop' );
		$txt( 'jw_side_banner_link', 'رابط البانر الجانبي', 'jw_shop', 'url' );

		/* Social */
		foreach ( array( 'instagram' => 'Instagram', 'tiktok' => 'TikTok', 'snapchat' => 'Snapchat', 'youtube' => 'YouTube', 'facebook' => 'Facebook' ) as $k => $n ) {
			$txt( 'jw_social_' . $k, $n, 'jw_social', 'url' );
		}
	}
);

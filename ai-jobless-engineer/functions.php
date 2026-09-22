<?php
/**
 * AI Jobless Engineer theme setup.
 *
 * @package AIJoblessEngineer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function aje_setup() {
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'editor-styles' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'title-tag' );
	add_editor_style( 'style.css' );

	register_nav_menus(
		array(
			'primary' => __( 'Primary Navigation', 'ai-jobless-engineer' ),
			'social'  => __( 'Social Links', 'ai-jobless-engineer' ),
		)
	);
}
add_action( 'after_setup_theme', 'aje_setup' );

function aje_enqueue_assets() {
	$theme_version = wp_get_theme()->get( 'Version' );

	wp_enqueue_style(
		'aje-style',
		get_stylesheet_uri(),
		array( 'dashicons' ),
		$theme_version
	);

	wp_enqueue_script(
		'aje-theme',
		get_theme_file_uri( 'assets/js/theme.js' ),
		array(),
		$theme_version,
		true
	);
}
add_action( 'wp_enqueue_scripts', 'aje_enqueue_assets' );

function aje_register_product_post_type() {
	register_post_type(
		'product',
		array(
			'labels'       => array(
				'name'          => __( 'Products', 'ai-jobless-engineer' ),
				'singular_name' => __( 'Product', 'ai-jobless-engineer' ),
				'add_new_item'  => __( 'Add New Product', 'ai-jobless-engineer' ),
				'edit_item'     => __( 'Edit Product', 'ai-jobless-engineer' ),
			),
			'public'       => true,
			'show_in_rest' => true,
			'menu_icon'    => 'dashicons-portfolio',
			'has_archive'  => true,
			'rewrite'      => array( 'slug' => 'products' ),
			'supports'     => array( 'title', 'editor', 'excerpt', 'thumbnail', 'custom-fields', 'revisions' ),
			'taxonomies'   => array( 'post_tag' ),
		)
	);
}
add_action( 'init', 'aje_register_product_post_type' );

function aje_register_block_patterns() {
	register_block_pattern_category(
		'aje',
		array( 'label' => __( 'AI Jobless Engineer', 'ai-jobless-engineer' ) )
	);
}
add_action( 'init', 'aje_register_block_patterns' );

function aje_reading_time() {
	$content = wp_strip_all_tags( get_post_field( 'post_content', get_the_ID() ) );
	$content = preg_replace( '/\s+/u', '', $content );
	$count   = function_exists( 'mb_strlen' ) ? mb_strlen( $content ) : strlen( $content );
	$minutes = max( 1, (int) ceil( $count / 500 ) );

	return sprintf(
		/* translators: %d: reading time in minutes */
		_n( '%d min read', '%d min read', $minutes, 'ai-jobless-engineer' ),
		$minutes
	);
}

function aje_reading_time_shortcode() {
	return '<span class="aje-reading">' . esc_html( aje_reading_time() ) . '</span>';
}
add_shortcode( 'aje_reading_time', 'aje_reading_time_shortcode' );

function aje_document_title_parts( $title ) {
	if ( is_front_page() ) {
		$title['tagline'] = __( 'AIで仕事がなくなる。じゃあ、どうする？', 'ai-jobless-engineer' );
	}

	return $title;
}
add_filter( 'document_title_parts', 'aje_document_title_parts' );

function aje_meta_tags() {
	$description = get_bloginfo( 'description' );

	if ( is_singular() ) {
		$post = get_queried_object();
		if ( $post instanceof WP_Post ) {
			$description = has_excerpt( $post )
				? get_the_excerpt( $post )
				: wp_trim_words( wp_strip_all_tags( strip_shortcodes( $post->post_content ) ), 90, '…' );
		}
	}

	if ( ! $description ) {
		$description = __( 'AI時代を生きるエンジニアの、ツール・開発・キャリア・暮らしの実践記録。', 'ai-jobless-engineer' );
	}

	echo '<meta name="description" content="' . esc_attr( $description ) . '">' . "\n";
	echo '<meta property="og:type" content="' . ( is_singular() ? 'article' : 'website' ) . '">' . "\n";
	echo '<meta property="og:title" content="' . esc_attr( wp_get_document_title() ) . '">' . "\n";
	echo '<meta property="og:description" content="' . esc_attr( $description ) . '">' . "\n";
	echo '<meta property="og:url" content="' . esc_url( is_singular() ? get_permalink() : home_url( '/' ) ) . '">' . "\n";

	if ( is_singular() && has_post_thumbnail() ) {
		echo '<meta property="og:image" content="' . esc_url( get_the_post_thumbnail_url( null, 'large' ) ) . '">' . "\n";
	}
}
add_action( 'wp_head', 'aje_meta_tags', 5 );

// Keep the optional Codex invite feature when its files are installed on the site.
$aje_codex_invite = get_theme_file_path( 'inc/codex-invite.php' );
if ( file_exists( $aje_codex_invite ) ) {
	require_once $aje_codex_invite;
}

function aje_breadcrumbs_shortcode() {
	if ( ! is_singular( 'post' ) ) {
		return '';
	}

	$categories = get_the_category();
	$parts      = array( '<a href="' . esc_url( home_url( '/' ) ) . '">ホーム</a>' );

	if ( ! empty( $categories ) ) {
		$parts[] = '<a href="' . esc_url( get_category_link( $categories[0]->term_id ) ) . '">' . esc_html( $categories[0]->name ) . '</a>';
	}

	$parts[] = '<span aria-current="page">' . esc_html( get_the_title() ) . '</span>';

	return '<nav class="aje-breadcrumbs" aria-label="パンくずリスト">' . implode( '<span aria-hidden="true">›</span>', $parts ) . '</nav>';
}
add_shortcode( 'aje_breadcrumbs', 'aje_breadcrumbs_shortcode' );

function aje_share_bar_shortcode() {
	if ( ! is_singular() ) {
		return '';
	}

	$url   = rawurlencode( get_permalink() );
	$title = rawurlencode( get_the_title() );

	return '<div class="aje-share-bar" aria-label="記事をシェア">'
		. '<div class="aje-share-links">'
		. '<a href="https://twitter.com/intent/tweet?url=' . esc_attr( $url ) . '&text=' . esc_attr( $title ) . '" target="_blank" rel="noopener noreferrer" aria-label="Xでシェア">X</a>'
		. '<a href="https://www.facebook.com/sharer/sharer.php?u=' . esc_attr( $url ) . '" target="_blank" rel="noopener noreferrer" aria-label="Facebookでシェア">f</a>'
		. '<a href="https://b.hatena.ne.jp/add?mode=confirm&amp;url=' . esc_attr( $url ) . '" target="_blank" rel="noopener noreferrer" aria-label="はてなブックマーク">B!</a>'
		. '<button type="button" data-aje-copy-url aria-label="記事URLをコピー">↗</button>'
		. '</div><span class="aje-copy-status" aria-live="polite"></span>'
		. '</div>';
}
add_shortcode( 'aje_share_bar', 'aje_share_bar_shortcode' );

function aje_related_posts_shortcode() {
	if ( ! is_singular( 'post' ) ) {
		return '';
	}

	$category_ids = wp_get_post_categories( get_the_ID() );
	$query        = new WP_Query(
		array(
			'post_type'           => 'post',
			'posts_per_page'      => 3,
			'post__not_in'        => array( get_the_ID() ),
			'category__in'        => $category_ids,
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
		)
	);

	if ( ! $query->have_posts() ) {
		return '';
	}

	$html = '<section class="aje-related-box"><h2>関連記事</h2><div class="aje-related-list">';
	while ( $query->have_posts() ) {
		$query->the_post();
		$html .= '<a class="aje-related-item" href="' . esc_url( get_permalink() ) . '">';
		if ( has_post_thumbnail() ) {
			$html .= get_the_post_thumbnail( get_the_ID(), 'thumbnail', array( 'loading' => 'lazy' ) );
		}
		$html .= '<span><strong>' . esc_html( get_the_title() ) . '</strong><small>' . esc_html( get_the_date( 'Y.m.d' ) ) . '</small></span></a>';
	}
	wp_reset_postdata();

	return $html . '</div></section>';
}
add_shortcode( 'aje_related_posts', 'aje_related_posts_shortcode' );

function aje_demo_post_content( $title, $category_name ) {
	$category_copy = array(
		'AIツール'       => 'AIツールは、目的を先に決めて小さく試すと、自分の仕事に合う使い方が見つかります。',
		'開発・プロダクト' => '個人開発では、最初から完璧を目指さず、価値を確かめられる最小単位から作ることが大切です。',
		'キャリア・働き方' => '変化の大きい時代ほど、学び続ける力と自分で選択肢を増やす姿勢が支えになります。',
		'ライフスタイル'   => '良い仕事を続けるためには、体調と生活リズムもプロダクトと同じように整える必要があります。',
	);
	$intro = isset( $category_copy[ $category_name ] ) ? $category_copy[ $category_name ] : $category_copy['AIツール'];

	return '<!-- wp:paragraph --><p>' . esc_html( $intro ) . 'この記事では「' . esc_html( $title ) . '」をテーマに、考え方とすぐに試せる行動を整理します。</p><!-- /wp:paragraph -->'
		. '<!-- wp:quote --><blockquote class="wp-block-quote"><p><strong>変化を恐れるのではなく、変化を使いこなす。</strong></p></blockquote><!-- /wp:quote -->'
		. '<!-- wp:group {"className":"aje-points-box","layout":{"type":"constrained"}} --><div class="wp-block-group aje-points-box"><!-- wp:heading {"level":3} --><h3 class="wp-block-heading">この記事のポイント</h3><!-- /wp:heading --><!-- wp:list --><ul class="wp-block-list"><li>いま起きている変化を整理できる</li><li>具体的な試し方がわかる</li><li>次に取る行動を決められる</li></ul><!-- /wp:list --></div><!-- /wp:group -->'
		. '<!-- wp:heading {"level":2,"className":"aje-numbered-heading"} --><h2 class="wp-block-heading aje-numbered-heading"><span>01</span>まず現在地を知る</h2><!-- /wp:heading -->'
		. '<!-- wp:paragraph --><p>新しい技術や働き方を取り入れる前に、自分の仕事のどこに時間がかかっているかを観察します。繰り返し作業、判断が必要な作業、人にしかできない作業を分けると、改善の入口が見えてきます。</p><!-- /wp:paragraph -->'
		. '<!-- wp:heading {"level":2,"className":"aje-numbered-heading"} --><h2 class="wp-block-heading aje-numbered-heading"><span>02</span>小さく試して記録する</h2><!-- /wp:heading -->'
		. '<!-- wp:paragraph --><p>一度に全部を変えず、ひとつの作業だけで試します。かかった時間、良かった点、困った点を記録すると、自分に合う方法を再現しやすくなります。</p><!-- /wp:paragraph -->'
		. '<!-- wp:heading {"level":2,"className":"aje-numbered-heading"} --><h2 class="wp-block-heading aje-numbered-heading"><span>03</span>自分の強みと組み合わせる</h2><!-- /wp:heading -->'
		. '<!-- wp:paragraph --><p>道具だけで差をつけるのではなく、経験、専門性、伝える力と組み合わせます。今日できる小さな一歩を決め、継続して更新していきましょう。</p><!-- /wp:paragraph -->';
}

function aje_demo_posts() {
	return array(
		array( 'title' => '実際に使ってよかったAIツール10選【2024年版】', 'slug' => 'best-ai-tools-2024', 'category' => 'AIツール', 'category_slug' => 'ai-tools', 'date' => '2026-09-23 09:00:00', 'image' => 'ai-workspace.jpg', 'sticky' => false ),
		array( 'title' => 'ChatGPTはエンジニアの仕事をどう変えるのか？', 'slug' => 'chatgpt-change-engineer-work', 'category' => 'AIツール', 'category_slug' => 'ai-tools', 'date' => '2026-09-16 12:00:00', 'image' => 'hero-night.jpg', 'sticky' => true ),
		array( 'title' => '爆速でWebアプリをつくる AI開発の新常識', 'slug' => 'rapid-web-app-ai-development', 'category' => '開発・プロダクト', 'category_slug' => 'development-product', 'date' => '2026-09-22 09:00:00', 'image' => 'site-designs.jpg', 'sticky' => false ),
		array( 'title' => 'Claudeで筋トレアプリを作ってみた', 'slug' => 'claude-workout-app', 'category' => '開発・プロダクト', 'category_slug' => 'development-product', 'date' => '2026-09-15 12:00:00', 'image' => 'ai-workspace.jpg', 'sticky' => true ),
		array( 'title' => 'エンジニアのメンタル管理術7選', 'slug' => 'engineer-mental-health-seven-tips', 'category' => 'キャリア・働き方', 'category_slug' => 'career-work', 'date' => '2026-09-21 09:00:00', 'image' => 'hero-night.jpg', 'sticky' => false ),
		array( 'title' => '筋トレがエンジニアにおすすめな理由', 'slug' => 'workout-for-engineers', 'category' => 'ライフスタイル', 'category_slug' => 'lifestyle', 'date' => '2026-09-20 09:00:00', 'image' => 'ai-workspace.jpg', 'sticky' => false ),
		array( 'title' => 'AI時代にエンジニアが生き残るために必要なこと', 'slug' => 'survive-as-engineer-in-ai-era', 'category' => 'キャリア・働き方', 'category_slug' => 'career-work', 'date' => '2026-09-14 09:00:00', 'image' => 'career-future.jpg', 'sticky' => true ),
		array( 'title' => 'NotebookLMの使い方と活用アイデア10選', 'slug' => 'notebooklm-ideas-ten', 'category' => 'AIツール', 'category_slug' => 'ai-tools', 'date' => '2026-09-19 09:00:00', 'image' => 'site-designs.jpg', 'sticky' => false ),
		array( 'title' => 'ゼロから始める個人開発ロードマップ', 'slug' => 'indie-development-roadmap', 'category' => '開発・プロダクト', 'category_slug' => 'development-product', 'date' => '2026-09-18 09:00:00', 'image' => 'career-future.jpg', 'sticky' => false ),
	);
}

function aje_import_demo_image( $filename, $title ) {
	$existing = get_posts(
		array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'posts_per_page' => 1,
			'meta_key'       => '_aje_demo_asset',
			'meta_value'     => $filename,
		)
	);
	if ( ! empty( $existing ) ) {
		return $existing[0]->ID;
	}

	$path = get_theme_file_path( 'assets/images/posts/' . $filename );
	if ( ! file_exists( $path ) ) {
		$fallbacks = array(
			'ai-workspace.jpg'  => 'assets/images/hero-editorial-v2.jpg',
			'career-future.jpg' => 'assets/images/cta-final.jpg',
			'site-designs.jpg'  => 'assets/images/hero-final.jpg',
			'hero-night.jpg'    => 'assets/images/hero-final.jpg',
		);
		$fallback = isset( $fallbacks[ $filename ] ) ? $fallbacks[ $filename ] : 'assets/images/hero-final.jpg';
		$path     = get_theme_file_path( $fallback );
		if ( ! file_exists( $path ) ) {
			return 0;
		}
	}

	$upload = wp_upload_bits( $filename, null, file_get_contents( $path ) );
	if ( ! empty( $upload['error'] ) ) {
		return 0;
	}

	$filetype      = wp_check_filetype( $upload['file'] );
	$attachment_id = wp_insert_attachment(
		array(
			'post_mime_type' => $filetype['type'],
			'post_title'     => $title,
			'post_status'    => 'inherit',
		),
		$upload['file']
	);

	if ( is_wp_error( $attachment_id ) ) {
		return 0;
	}

	require_once ABSPATH . 'wp-admin/includes/image.php';
	wp_update_attachment_metadata( $attachment_id, wp_generate_attachment_metadata( $attachment_id, $upload['file'] ) );
	update_post_meta( $attachment_id, '_aje_demo_asset', $filename );

	return $attachment_id;
}

function aje_create_demo_content() {
	$created = 0;
	$updated = 0;

	foreach ( aje_demo_posts() as $item ) {
		$term = get_term_by( 'slug', $item['category_slug'], 'category' );
		if ( $term instanceof WP_Term ) {
			if ( $term->name !== $item['category'] ) {
				wp_update_term( $term->term_id, 'category', array( 'name' => $item['category'] ) );
			}
			$category_id = (int) $term->term_id;
		} else {
			$term = term_exists( $item['category'], 'category' );
			if ( ! $term ) {
				$term = wp_insert_term( $item['category'], 'category', array( 'slug' => $item['category_slug'] ) );
			}
			$category_id = is_array( $term ) ? (int) $term['term_id'] : (int) $term;
		}

		$existing = get_page_by_path( $item['slug'], OBJECT, 'post' );
		if ( ! $existing ) {
			$matches = get_posts(
				array(
					'post_type'      => 'post',
					'post_status'    => 'any',
					'posts_per_page' => 1,
					's'              => $item['title'],
				)
			);
			$existing = ! empty( $matches ) && $matches[0]->post_title === $item['title'] ? $matches[0] : null;
		}

		$excerpt = 'AI時代を前向きに生きるエンジニアのために、' . $item['title'] . 'をわかりやすくまとめます。';
		if ( $existing ) {
			$post_id = $existing->ID;
			wp_set_post_categories( $post_id, array( $category_id ), false );
			$updates = array(
				'ID'        => $post_id,
				'post_date' => $item['date'],
			);
			if ( ! has_excerpt( $post_id ) ) {
				$updates['post_excerpt'] = $excerpt;
			}
			if ( '' === trim( $existing->post_content ) ) {
				$updates['post_content'] = aje_demo_post_content( $item['title'], $item['category'] );
			}
			wp_update_post( $updates );
			++$updated;
		} else {
			$post_id = wp_insert_post(
				array(
					'post_title'    => $item['title'],
					'post_name'     => $item['slug'],
					'post_content'  => aje_demo_post_content( $item['title'], $item['category'] ),
					'post_excerpt'  => $excerpt,
					'post_status'   => 'publish',
					'post_type'     => 'post',
					'post_date'     => $item['date'],
					'post_category' => array( $category_id ),
				)
			);
			++$created;
		}

		if ( $post_id && ! is_wp_error( $post_id ) ) {
			if ( ! has_post_thumbnail( $post_id ) ) {
				$image_id = aje_import_demo_image( $item['image'], $item['title'] );
				if ( $image_id ) {
					set_post_thumbnail( $post_id, $image_id );
				}
			}
			if ( $item['sticky'] ) {
				stick_post( $post_id );
			}
		}
	}

	return array( 'created' => $created, 'updated' => $updated );
}

function aje_register_setup_page() {
	add_theme_page(
		__( 'AI Jobless Engineer Setup', 'ai-jobless-engineer' ),
		__( 'テーマ初期設定', 'ai-jobless-engineer' ),
		'manage_options',
		'aje-setup',
		'aje_render_setup_page'
	);
}
add_action( 'admin_menu', 'aje_register_setup_page' );

function aje_render_setup_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$result = null;
	if ( isset( $_POST['aje_create_demo_content'] ) ) {
		check_admin_referer( 'aje_create_demo_content' );
		$result = aje_create_demo_content();
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'AI Jobless Engineer テーマ初期設定', 'ai-jobless-engineer' ); ?></h1>
		<p><?php esc_html_e( 'ホーム画面の見本にある9記事と4カテゴリーを作成します。すでに同名の記事がある場合は本文を上書きしません。', 'ai-jobless-engineer' ); ?></p>
		<?php if ( $result ) : ?>
			<div class="notice notice-success is-dismissible"><p><?php echo esc_html( sprintf( '完了しました。新規作成: %1$d件、既存記事を確認: %2$d件', $result['created'], $result['updated'] ) ); ?></p></div>
		<?php endif; ?>
		<form method="post">
			<?php wp_nonce_field( 'aje_create_demo_content' ); ?>
			<?php submit_button( '見本記事を作成する', 'primary', 'aje_create_demo_content' ); ?>
		</form>
	</div>
	<?php
}

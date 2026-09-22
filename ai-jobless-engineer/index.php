<?php
/**
 * Classic-template fallback for installers that require a root index.php.
 * Supported WordPress versions render templates/index.html instead.
 *
 * @package AIJoblessEngineer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<div class="aje-main-with-sidebar">
	<aside class="aje-sidebar">
		<div class="aje-sidebar-inner">
			<a class="aje-brand-title" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php bloginfo( 'name' ); ?></a>
			<nav class="aje-side-nav" aria-label="<?php esc_attr_e( 'Categories', 'ai-jobless-engineer' ); ?>">
				<ul><?php wp_list_categories( array( 'title_li' => '' ) ); ?></ul>
			</nav>
		</div>
	</aside>
	<main class="aje-content aje-section">
		<div class="aje-section-inner">
			<?php if ( have_posts() ) : ?>
				<?php while ( have_posts() ) : the_post(); ?>
					<article <?php post_class( 'aje-fallback-post' ); ?>>
						<?php if ( is_singular() ) : ?>
							<h1><?php the_title(); ?></h1>
							<div class="aje-prose"><?php the_content(); ?></div>
						<?php else : ?>
							<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
							<div class="aje-prose"><?php the_excerpt(); ?></div>
						<?php endif; ?>
						</article>
				<?php endwhile; ?>
				<?php the_posts_pagination(); ?>
			<?php else : ?>
				<h1><?php esc_html_e( 'No posts found', 'ai-jobless-engineer' ); ?></h1>
			<?php endif; ?>
		</div>
	</main>
</div>
<?php wp_footer(); ?>
</body>
</html>

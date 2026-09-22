<?php
/**
 * Template Name: Codex Invite
 * Template Post Type: page
 *
 * Description: Dedicated landing page template for Codex Invite.
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
	<?php
	// Render theme parts if block template parts are available.
	if ( function_exists( 'block_template_part' ) ) {
		block_template_part( 'mobile-header' );
		block_template_part( 'sidebar' );
	} else {
		// Fallback for non-FSE rendering environments.
		get_template_part( 'parts/mobile-header' );
		get_template_part( 'parts/sidebar' );
	}
	?>

	<div class="aje-content">
		<main class="codex-invite-page" id="codex-invite-main">

			<!-- Section 1: Hero -->
			<section class="codex-invite-hero">
				<div class="codex-invite-container">
					<span class="codex-invite-badge">Codex Special Invite</span>
					<h1 class="codex-invite-hero-title">
						AIに聞くだけではなく、<br>
						<span>AIと一緒につくる。</span>
					</h1>
					<p class="codex-invite-hero-lead">
						Codexは、コードを提案するだけではありません。<br><br>
						プロジェクトを読み、実装し、修正し、テストしながら、<br>
						開発そのものを進めてくれるAIエージェントです。
					</p>
					<div class="codex-invite-hero-note">
						<span class="dashicons dashicons-info" aria-hidden="true"></span>
						<p>僕自身、個人開発やこのブログの制作でもCodexを活用しています。</p>
					</div>
					<div class="codex-invite-hero-action">
						<a href="#codex-request-form" class="codex-invite-btn-primary aje-button aje-button-primary">
							Codexの招待をリクエストする ↓
						</a>
					</div>
				</div>
			</section>

			<!-- Section 2: Why Codex (3 Cards) -->
			<section class="codex-invite-features aje-section">
				<div class="codex-invite-container">
					<div class="codex-invite-section-heading aje-section-heading">
						<div>
							<h2>なぜCodexを使うべきなのか</h2>
							<p>AIエージェントと進める次世代の開発体験</p>
						</div>
					</div>
					<div class="codex-invite-grid">
						<!-- Card 1 -->
						<article class="codex-invite-card aje-category-card">
							<div class="codex-invite-card-icon" aria-hidden="true">
								<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>
							</div>
							<div class="codex-invite-card-content">
								<span class="codex-invite-card-step">FEATURE 01</span>
								<h3>圧倒的に速い</h3>
								<p>仕様を伝えるだけで、実装まで一気に進められる。</p>
							</div>
						</article>

						<!-- Card 2 -->
						<article class="codex-invite-card aje-category-card">
							<div class="codex-invite-card-icon" aria-hidden="true">
								<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path><line x1="12" y1="11" x2="12" y2="17"></line><line x1="9" y1="14" x2="15" y2="14"></line></svg>
							</div>
							<div class="codex-invite-card-content">
								<span class="codex-invite-card-step">FEATURE 02</span>
								<h3>プロジェクト全体を理解</h3>
								<p>1ファイルだけではなく、コードベース全体を見ながら作業できる。</p>
							</div>
						</article>

						<!-- Card 3 -->
						<article class="codex-invite-card aje-category-card">
							<div class="codex-invite-card-icon" aria-hidden="true">
								<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"></polyline><polyline points="8 6 2 12 8 18"></polyline></svg>
							</div>
							<div class="codex-invite-card-content">
								<span class="codex-invite-card-step">FEATURE 03</span>
								<h3>実際に手を動かす</h3>
								<p>コードを読む、書く、修正する、テストするところまで進められる。</p>
							</div>
						</article>
					</div>
				</div>
			</section>

			<!-- Section 3 & 4: Remaining Seats & Request Form -->
			<?php
			$codex_quota = function_exists( 'aje_get_codex_invite_quota' ) ? aje_get_codex_invite_quota() : array( 'max' => 10, 'remaining' => 7 );
			$codex_percent = ( $codex_quota['max'] > 0 ) ? round( ( $codex_quota['remaining'] / $codex_quota['max'] ) * 100 ) : 0;
			$is_full = ( $codex_quota['remaining'] <= 0 );
			?>
			<section class="codex-invite-form-section aje-section" id="codex-request-form">
				<div class="codex-invite-container">
					<div class="codex-invite-box">
						
						<!-- Quota Status Card -->
						<div class="codex-invite-quota">
							<div class="codex-invite-quota-header">
								<span class="codex-invite-quota-label">Codex 招待枠</span>
								<div class="codex-invite-quota-count">
									残り <strong class="codex-quota-remaining-display"><?php echo esc_html( $codex_quota['remaining'] ); ?></strong> 枠 
									<span class="codex-invite-quota-total">/ <?php echo esc_html( $codex_quota['max'] ); ?></span>
								</div>
							</div>
							<div class="codex-invite-progress-bar" role="progressbar" aria-valuenow="<?php echo esc_attr( $codex_quota['remaining'] ); ?>" aria-valuemin="0" aria-valuemax="<?php echo esc_attr( $codex_quota['max'] ); ?>" aria-label="残り招待枠">
								<div class="codex-invite-progress-fill" style="width: <?php echo esc_attr( $codex_percent ); ?>%;"></div>
							</div>
							<p class="codex-invite-quota-caption">
								<?php if ( $is_full ) : ?>
									現在、招待枠はすべて埋まっています。
								<?php else : ?>
									枠が埋まり次第、受付を終了いたします。
								<?php endif; ?>
							</p>
						</div>

						<!-- Form -->
						<form class="codex-invite-form" id="codex-invite-form" novalidate>
							<h2 class="codex-invite-form-title">招待リクエストを送る</h2>
							<p class="codex-invite-form-desc">
								以下のメールアドレス宛てにChatGPT公式の招待をお送りします。
							</p>

							<!-- Honeypot for spam bots -->
							<div style="position:absolute;left:-9999px;top:-9999px;" aria-hidden="true">
								<input type="text" name="website_url" tabindex="-1" autocomplete="off">
							</div>

							<!-- Feedback Message Area -->
							<div class="codex-invite-message" id="codex-invite-message" style="display:none;" role="alert"></div>

							<div class="codex-invite-form-group">
								<label for="codex-invite-email" class="codex-invite-label">メールアドレス <span class="codex-invite-required">必須</span></label>
								<input 
									type="email" 
									id="codex-invite-email" 
									name="email" 
									class="codex-invite-input" 
									placeholder="example@gmail.com" 
									required
									<?php echo $is_full ? 'disabled' : ''; ?>
								>
							</div>

							<div class="codex-invite-form-action">
								<button 
									type="submit" 
									id="codex-invite-submit" 
									class="codex-invite-submit-btn aje-button aje-button-primary"
									<?php echo $is_full ? 'disabled' : ''; ?>
								>
									<span class="btn-text"><?php echo $is_full ? '招待受付終了' : '招待をリクエストする'; ?></span>
									<span class="btn-loader" style="display:none;" aria-hidden="true">送信中...</span>
								</button>
							</div>

							<div class="codex-invite-notice">
								<span class="dashicons dashicons-warning" aria-hidden="true"></span>
								<p>リクエスト確認後、ChatGPT公式の招待機能から招待を送信します。（自動即時招待ではありません）</p>
							</div>
						</form>

					</div>
				</div>
			</section>

		</main>

		<?php
		if ( function_exists( 'block_template_part' ) ) {
			block_template_part( 'footer' );
		} else {
			get_template_part( 'parts/footer' );
		}
		?>
	</div>
</div>

<?php wp_footer(); ?>
</body>
</html>

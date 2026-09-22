<?php
/**
 * Codex Invite Page functionality.
 * Handles CPT, Admin UI, Options, Ajax submissions, and Notifications.
 *
 * @package AIJoblessEngineer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register Custom Post Type: codex_invite
 */
function aje_register_codex_invite_cpt() {
	register_post_type(
		'codex_invite',
		array(
			'labels'       => array(
				'name'          => __( 'Codex招待', 'ai-jobless-engineer' ),
				'singular_name' => __( 'Codex招待リクエスト', 'ai-jobless-engineer' ),
				'menu_name'     => __( 'Codex招待', 'ai-jobless-engineer' ),
				'edit_item'     => __( '招待リクエスト詳細', 'ai-jobless-engineer' ),
			),
			'public'       => false,
			'show_ui'      => true,
			'show_in_menu' => true,
			'menu_icon'    => 'dashicons-email-alt2',
			'supports'     => array( 'title' ),
			'capabilities' => array(
				'create_posts' => 'do_not_allow', // Created via form only
			),
			'map_meta_cap' => true,
		)
	);
}
add_action( 'init', 'aje_register_codex_invite_cpt' );

/**
 * Get remaining and max invite counts.
 */
function aje_get_codex_invite_quota() {
	$max = get_option( 'codex_invite_limit', 10 );
	$remaining = get_option( 'codex_invite_remaining', 7 );

	// Ensure non-negative and within limits
	$max = max( 0, (int) $max );
	$remaining = max( 0, (int) $remaining );

	return array(
		'max'       => $max,
		'remaining' => $remaining,
	);
}

/**
 * Enqueue scripts and styles for the Codex Invite page.
 */
function aje_codex_invite_enqueue_assets() {
	$is_codex_page = is_page_template( 'page-codex-invite.php' ) 
		|| is_page_template( 'page-codex-invite' )
		|| is_page( 'codex-invite' )
		|| ( is_singular() && 'page-codex-invite' === get_page_template_slug() )
		|| ( isset( $_GET['preview'] ) && is_page( 'codex-invite' ) );

	if ( ! $is_codex_page ) {
		// Also check if request URI contains codex-invite
		$uri = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
		if ( false !== strpos( $uri, 'codex-invite' ) ) {
			$is_codex_page = true;
		}
	}

	if ( ! $is_codex_page ) {
		return;
	}

	$theme_version = wp_get_theme()->get( 'Version' );
	$quota = aje_get_codex_invite_quota();

	wp_enqueue_style(
		'aje-codex-invite-style',
		get_theme_file_uri( 'assets/css/codex-invite.css' ),
		array( 'aje-style' ),
		$theme_version
	);

	wp_enqueue_script(
		'aje-codex-invite-script',
		get_theme_file_uri( 'assets/js/codex-invite.js' ),
		array( 'aje-theme' ),
		$theme_version,
		true
	);

	// Localize script with Ajax URL, Nonce, and Quota data
	wp_localize_script(
		'aje-codex-invite-script',
		'ajeCodexData',
		array(
			'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
			'nonce'     => wp_create_nonce( 'aje_codex_invite_nonce' ),
			'remaining' => $quota['remaining'],
			'max'       => $quota['max'],
			'isFull'    => ( $quota['remaining'] <= 0 ),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'aje_codex_invite_enqueue_assets', 20 );

/**
 * Handle Codex Invite Ajax Form Submission
 */
function aje_handle_codex_invite_ajax() {
	// 1. Verify Nonce
	check_ajax_referer( 'aje_codex_invite_nonce', 'nonce' );

	// 2. Honeypot check for spam bots
	if ( ! empty( $_POST['website_url'] ) ) {
		wp_send_json_error( array( 'message' => __( 'スパムと判定されました。', 'ai-jobless-engineer' ) ) );
	}

	// 3. Quota check
	$quota = aje_get_codex_invite_quota();
	if ( $quota['remaining'] <= 0 ) {
		wp_send_json_error( array(
			'code'    => 'quota_full',
			'message' => __( '現在、招待枠はすべて埋まっています。', 'ai-jobless-engineer' ),
		) );
	}

	// 4. Sanitize and validate email
	$raw_email = '';
	if ( isset( $_POST['email'] ) ) {
		$raw_email = trim( wp_unslash( $_POST['email'] ) );
	} elseif ( isset( $_REQUEST['email'] ) ) {
		$raw_email = trim( wp_unslash( $_REQUEST['email'] ) );
	} else {
		// Fallback for json body or custom streams
		$input_data = file_get_contents( 'php://input' );
		if ( ! empty( $input_data ) ) {
			parse_str( $input_data, $parsed_params );
			if ( ! empty( $parsed_params['email'] ) ) {
				$raw_email = trim( $parsed_params['email'] );
			}
		}
	}

	$email = sanitize_email( $raw_email );

	if ( empty( $email ) || ! is_email( $email ) ) {
		wp_send_json_error( array(
			'code'    => 'invalid_email',
			'message' => sprintf( __( '正しいメールアドレスを入力してください。（入力値: %s）', 'ai-jobless-engineer' ), esc_html( $raw_email ) ),
		) );
	}

	// 5. Check for duplicate email request
	$existing = get_posts( array(
		'post_type'      => 'codex_invite',
		'post_status'    => 'any',
		'title'          => $email,
		'posts_per_page' => 1,
		'fields'         => 'ids',
	) );

	if ( ! empty( $existing ) ) {
		wp_send_json_error( array(
			'code'    => 'already_requested',
			'message' => __( 'このメールアドレスは既に招待リクエスト済みです。', 'ai-jobless-engineer' ),
		) );
	}

	// 6. Save CPT record
	$post_id = wp_insert_post( array(
		'post_type'   => 'codex_invite',
		'post_title'  => $email,
		'post_status' => 'publish',
	) );

	if ( is_wp_error( $post_id ) ) {
		wp_send_json_error( array(
			'code'    => 'save_failed',
			'message' => __( '保存処理に失敗しました。時間をおいて再試行してください。', 'ai-jobless-engineer' ),
		) );
	}

	// Save custom fields
	$now = current_time( 'mysql' );
	update_post_meta( $post_id, '_codex_email', $email );
	update_post_meta( $post_id, '_codex_status', 'pending' );
	update_post_meta( $post_id, '_codex_requested_at', $now );
	update_post_meta( $post_id, '_codex_invited_at', '' );

	// 7. Send notification email to admin
	$admin_email = get_option( 'admin_email' );
	$subject     = __( '【Codex招待】新しい招待リクエスト', 'ai-jobless-engineer' );
	$message     = sprintf(
		"Codex招待リクエストが届きました。\n\nメールアドレス：\n%s\n\n申請日時：\n%s\n\nWordPress管理画面から確認してください：\n%s\n",
		$email,
		$now,
		admin_url( 'edit.php?post_type=codex_invite' )
	);
	$headers     = array( 'Content-Type: text/plain; charset=UTF-8' );

	wp_mail( $admin_email, $subject, $message, $headers );

	// 8. Send Success Response
	wp_send_json_success( array(
		'message'   => "招待リクエストを受け付けました。\n\n確認後、24時間以内に招待をお送りします。",
		'remaining' => $quota['remaining'],
	) );
}
add_action( 'wp_ajax_aje_codex_invite', 'aje_handle_codex_invite_ajax' );
add_action( 'wp_ajax_nopriv_aje_codex_invite', 'aje_handle_codex_invite_ajax' );


/* ==========================================================================
   Admin Management: Columns, Actions, and Options Page
   ========================================================================== */

/**
 * Custom columns for codex_invite post list.
 */
function aje_codex_invite_columns( $columns ) {
	$new_columns = array(
		'cb'           => $columns['cb'],
		'title'        => __( 'メールアドレス', 'ai-jobless-engineer' ),
		'codex_status' => __( 'ステータス', 'ai-jobless-engineer' ),
		'requested_at' => __( '申請日時', 'ai-jobless-engineer' ),
		'invited_at'   => __( '招待日時', 'ai-jobless-engineer' ),
		'actions'      => __( '操作', 'ai-jobless-engineer' ),
	);
	return $new_columns;
}
add_filter( 'manage_codex_invite_posts_columns', 'aje_codex_invite_columns' );

/**
 * Display content in custom columns.
 */
function aje_codex_invite_custom_column( $column, $post_id ) {
	switch ( $column ) {
		case 'codex_status':
			$status = get_post_meta( $post_id, '_codex_status', true ) ?: 'pending';
			if ( 'invited' === $status ) {
				echo '<span style="display:inline-block;padding:2px 8px;border-radius:4px;background:#dcfce7;color:#166534;font-weight:700;">' . esc_html__( '招待済み', 'ai-jobless-engineer' ) . '</span>';
			} else {
				echo '<span style="display:inline-block;padding:2px 8px;border-radius:4px;background:#fef3c7;color:#92400e;font-weight:700;">' . esc_html__( '未招待 (pending)', 'ai-jobless-engineer' ) . '</span>';
			}
			break;

		case 'requested_at':
			$date = get_post_meta( $post_id, '_codex_requested_at', true );
			echo $date ? esc_html( $date ) : '-';
			break;

		case 'invited_at':
			$date = get_post_meta( $post_id, '_codex_invited_at', true );
			echo $date ? esc_html( $date ) : '-';
			break;

		case 'actions':
			$status = get_post_meta( $post_id, '_codex_status', true ) ?: 'pending';
			if ( 'invited' !== $status ) {
				$nonce = wp_create_nonce( 'aje_mark_invited_' . $post_id );
				$url = admin_url( 'edit.php?post_type=codex_invite&action=mark_invited&post_id=' . $post_id . '&_wpnonce=' . $nonce );
				echo '<a href="' . esc_url( $url ) . '" class="button button-small button-primary">' . esc_html__( '招待済みにする', 'ai-jobless-engineer' ) . '</a>';
			} else {
				echo '<span style="color:#94a3b8;">完了</span>';
			}
			break;
	}
}
add_action( 'manage_codex_invite_posts_custom_column', 'aje_codex_invite_custom_column', 10, 2 );

/**
 * Handle "招待済みにする" Admin Action.
 */
function aje_handle_mark_invited_action() {
	if ( ! isset( $_GET['action'] ) || 'mark_invited' !== $_GET['action'] ) {
		return;
	}

	$post_id = isset( $_GET['post_id'] ) ? (int) $_GET['post_id'] : 0;
	if ( ! $post_id || ! check_admin_referer( 'aje_mark_invited_' . $post_id ) ) {
		wp_die( esc_html__( '不正なアクセスです。', 'ai-jobless-engineer' ) );
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		wp_die( esc_html__( '権限がありません。', 'ai-jobless-engineer' ) );
	}

	$current_status = get_post_meta( $post_id, '_codex_status', true );
	if ( 'invited' !== $current_status ) {
		// Update status and timestamp
		update_post_meta( $post_id, '_codex_status', 'invited' );
		update_post_meta( $post_id, '_codex_invited_at', current_time( 'mysql' ) );

		// Decrement remaining count, keeping >= 0
		$remaining = (int) get_option( 'codex_invite_remaining', 7 );
		$new_remaining = max( 0, $remaining - 1 );
		update_option( 'codex_invite_remaining', $new_remaining );
	}

	wp_safe_redirect( admin_url( 'edit.php?post_type=codex_invite&status_updated=1' ) );
	exit;
}
add_action( 'admin_init', 'aje_handle_mark_invited_action' );

/**
 * Admin notice for status update.
 */
function aje_codex_admin_notices() {
	if ( isset( $_GET['status_updated'] ) && 'codex_invite' === get_post_type() ) {
		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'ステータスを「招待済み」に更新し、残り枠数を1減らしました。', 'ai-jobless-engineer' ) . '</p></div>';
	}
}
add_action( 'admin_notices', 'aje_codex_admin_notices' );

/**
 * Add Settings Submenu under Codex招待.
 */
function aje_register_codex_invite_settings_menu() {
	add_submenu_page(
		'edit.php?post_type=codex_invite',
		__( 'Codex招待設定', 'ai-jobless-engineer' ),
		__( '招待設定', 'ai-jobless-engineer' ),
		'manage_options',
		'codex-invite-settings',
		'aje_render_codex_invite_settings_page'
	);
}
add_action( 'admin_menu', 'aje_register_codex_invite_settings_menu' );

/**
 * Render Settings Page.
 */
function aje_render_codex_invite_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	// Handle saving settings
	if ( isset( $_POST['codex_settings_nonce'] ) && wp_verify_nonce( $_POST['codex_settings_nonce'], 'aje_save_codex_settings' ) ) {
		$limit     = max( 0, (int) $_POST['codex_invite_limit'] );
		$remaining = max( 0, (int) $_POST['codex_invite_remaining'] );

		update_option( 'codex_invite_limit', $limit );
		update_option( 'codex_invite_remaining', $remaining );

		echo '<div class="notice notice-success"><p>' . esc_html__( '設定を保存しました。', 'ai-jobless-engineer' ) . '</p></div>';
	}

	$limit     = get_option( 'codex_invite_limit', 10 );
	$remaining = get_option( 'codex_invite_remaining', 7 );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Codex 招待設定', 'ai-jobless-engineer' ); ?></h1>
		<form method="post" action="">
			<?php wp_nonce_field( 'aje_save_codex_settings', 'codex_settings_nonce' ); ?>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="codex_invite_limit"><?php esc_html_e( '最大招待数', 'ai-jobless-engineer' ); ?></label></th>
					<td>
						<input name="codex_invite_limit" type="number" id="codex_invite_limit" value="<?php echo esc_attr( $limit ); ?>" class="small-text" min="0">
						<p class="description"><?php esc_html_e( '招待枠の母数（例: 10）', 'ai-jobless-engineer' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="codex_invite_remaining"><?php esc_html_e( '現在の残り枠', 'ai-jobless-engineer' ); ?></label></th>
					<td>
						<input name="codex_invite_remaining" type="number" id="codex_invite_remaining" value="<?php echo esc_attr( $remaining ); ?>" class="small-text" min="0">
						<p class="description"><?php esc_html_e( 'ユーザー向けページに表示される残り枠数（管理画面で「招待済みにする」と自動で1減ります）', 'ai-jobless-engineer' ); ?></p>
					</td>
				</tr>
			</table>
			<?php submit_button( __( '設定を保存', 'ai-jobless-engineer' ) ); ?>
		</form>
	</div>
	<?php
}

<?php namespace CheckEmail\Core\UI\Page;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

class Check_Email_Settings_Page extends Check_Email_BasePage {

	const PAGE_SLUG = 'check-email-settings';
	public $page_slug;
	public function load() {
		parent::load();

		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'wp_ajax_oneclick_smtp_install', array( $this, 'install_plugin' ) );
		add_action( 'wp_ajax_oneclick_smtp_activate', array( $this, 'activate_plugin' ) );
	}

	public function register_settings() {
		$sections = $this->get_setting_sections();

		foreach ( $sections as $section ) {
			if( !isset( $section->page_slug ) ) 
			continue;
			$this->page_slug = $section->page_slug;
			register_setting(
				$this->page_slug ,
				$section->option_name,
				array( 'sanitize_callback' => $section->sanitize_callback )
			);

			add_settings_section(
				$section->id,
				$section->title,
				$section->callback,
				$this->page_slug 
			);

			foreach ( $section->fields as $field ) {
				add_settings_field(
					$section->id . '[' . $field->id . ']',
					$field->title,
					$field->callback,
					$this->page_slug,
					$section->id,
					$field->args
				);
			}
		}
	}

	protected function get_setting_sections() {
		return apply_filters( 'check_email_setting_sections', array() );
	}

	public function register_page() {

		$sections = $this->get_setting_sections();
                
		if ( empty( $sections ) ) {
			return;
		}

		$this->page = add_submenu_page(
			Check_Email_Status_Page::PAGE_SLUG,
			esc_html__( 'Settings', 'check-email' ),
			esc_html__( 'Settings', 'check-email' ),
			'manage_options',
			self::PAGE_SLUG,
			array( $this, 'render_page' )
		);

	}
   /**
    * Checks if SMTP plugin is installed and/or active
    * @return string 
    * @since 1.0.5
    */
	public function is_smtp_installed() {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$all_plugins = get_plugins();

		if ( empty( $all_plugins['wp-smtp/wp-smtp.php'] ) ) {

			return 'install';
		}else{
			if( !is_plugin_active( 'wp-smtp/wp-smtp.php' ) ){

				return 'activate';
			}else{
				return 'false';
			}
		}
	}

   /**
    * Renders the plugin settings page HTML
    *
    * @since 1.0.5
    */
	public function render_page() {

			$tab = isset( $_GET['tab']) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'general';
			
		?>
		<div class="wrap">

			<nav class="nav-tab-wrapper">
				<a href="?page=check-email-settings" class="nav-tab <?php if( 'general' == $tab ):?>nav-tab-active<?php endif; ?>"><?php esc_html_e( 'General', 'check-email' ); ?></a>
				<a href="?page=check-email-settings&tab=logging" class="nav-tab <?php if( 'logging' == $tab ):?>nav-tab-active<?php endif; ?>"><?php esc_html_e( 'Logging', 'check-email' ); ?></a>
				<a href="?page=check-email-settings&tab=smtp" class="nav-tab <?php if( 'smtp' == $tab ):?>nav-tab-active<?php endif; ?>"><?php esc_html_e( 'SMTP', 'check-email' ); ?></a>
				<a href="https://docs.google.com/forms/d/e/1FAIpQLSdhHrYons-oMg_9oEDVvx8VTvzdeCQpT4PnG6KLCjYPiyQfXg/viewform" target="_blank" class="nav-tab"><span class="dashicons dashicons-external"></span><?php esc_html_e( 'Suggest a feature', 'check-email' ); ?></a>
			</nav>
			
			<div class="tab-content ce_tab_<?php echo esc_attr( $tab ); ?>">

			<?php if( 'general' == $tab ): ?>
				<h2><?php esc_html_e( 'Core Check Email Log Settings', 'check-email' ); ?></h2>
			<?php elseif( 'logging' == $tab ): ?>
				<h2><?php esc_html_e( 'Logging', 'check-email' ); ?></h2>
			<?php elseif( 'smtp' == $tab ): ?>
				<h2><?php esc_html_e( 'WP SMTP Installer', 'check-email' ); ?></h2>
			<?php endif; ?>

			<?php if( 'smtp' !== $tab ): ?>
				<?php $submit_url = ( '' != $tab ) ? add_query_arg( 'tab', $tab, admin_url( 'options.php' ) ) : 'options.php'; ?>
				<form method="post" action="<?php echo esc_url( $submit_url ); ?>">
					<?php
					settings_errors();
					settings_fields( $this->page_slug  );
					do_settings_sections( $this->page_slug );
					submit_button( esc_html__( 'Save', 'check-email' ) );
					?>
				</form>
			<?php elseif( 'smtp' == $tab ): ?>
				<table class="form-table" role="presentation">
					<tbody>
						<tr>
							<th scope="row"><?php esc_html_e( 'Install WP SMTP', 'check-email' ); ?></th>
							<?php $smtp_status = $this->is_smtp_installed(); ?>
							<?php if( 'false' != $smtp_status ): ?> 
							<?php
								$activate_url = add_query_arg(
									array(
										'action'        => 'activate',
										'plugin'        => rawurlencode( 'wp-smtp/wp-smtp.php' ),
										'plugin_status' => 'all',
										'paged'         => '1',
										'_wpnonce'      => wp_create_nonce( 'activate-plugin_wp-smtp/wp-smtp.php' ),
									),
									admin_url( 'plugins.php' )
								);	
							?>
							<td>
								<div class="install_plugin_wrap">
									<button id="install_wp_smtp" class="button"  data-slug="wp-smtp" data-action="<?php echo ( 'install' == $smtp_status ? 'install' : 'activate' ); ?>" data-activation_url="<?php echo esc_url( $activate_url ); ?>"><?php echo sprintf( esc_html__( '%s SMTP', 'check-email' ),  ( 'install' == $smtp_status ? 'Install' : 'Activate' ) ); ?></button>
									<div id="install_wp_smtp_info"> <p><?php echo sprintf( esc_html__( 'Click to %s WP SMTP', 'check-email' ), ( 'install' == $smtp_status ? 'install' : 'activate' ) ) ; ?> </p></div>
								</div>
								
							</td>
							<?php else: ?>
								<td>
								<div class="install_wp_smtp_wrap"> <?php esc_html_e( 'WP SMTP is allready installed and activated.', 'check-email' ); ?></div>
							</td>
							<?php endif; ?>
						</tr>
					</tbody>
				</table>
			<?php endif; ?>
			</div>
		</div>
		<?php

	}
}

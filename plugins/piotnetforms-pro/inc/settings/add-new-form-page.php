<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function piotnetforms_get_templates() {
	$templates = [];

	$dir   = __DIR__ . '/../../assets/forms/templates/';
	$files = array_diff( scandir( $dir ), [ '.', '..' ] );

	foreach ( $files as $file ) {
		$content     = file_get_contents( $dir . $file );
		$data        = json_decode( $content, true );
		$templates[] = [
			'filename' => $file,
			'title'    => $data['title'],
		];
	}
	return $templates;
}
function pafe_str_contains (string $haystack, string $needle){
    return empty($needle) || strpos($haystack, $needle) !== false;
}
function piotnetforms_import( $file_content ) {
	$data = json_decode( $file_content, true );
	if ( json_last_error() > 0 ) {
		$last_error_msg = json_last_error_msg();
		print_r( "Can't parse file: " . $last_error_msg );
	} elseif ( array_key_exists( 'error', $data ) ) {
		print_r( 'Error: ' . $data['error'] );
	} else {
		$post = [
			'post_title'  => $data['title'],
			'post_status' => 'publish',
			'post_type'   => 'piotnetforms',
		];

		$post_id = wp_insert_post( $post );

		if ( is_wp_error( $post_id ) ) {
			echo "<div class='piotnetforms-dashboard__notice-import'>Can't insert post: " . $post_id->get_error_message() . '</div>';
		} else {
            if(pafe_str_contains($file_content, 'piotnetforms_booking_form_id') && !empty($data['widgets'])){
                foreach($data['widgets'] as $key => $item){
                    if(!empty($item['type']) && $item['type'] === 'booking'){
                        $settings =  !empty($item['settings']) ? $item['settings'] : [];
                        $data['widgets'][$key]['settings']['piotnetforms_booking_form_id'] = $post_id;
                    }
                }
            }
			piotnetforms_do_import( $post_id, $data );

			$post_url = admin_url() . 'admin.php?page=piotnetforms&post=' . $post_id;
			$permalink = get_permalink( $post_id );

			echo '<div class="piotnetforms-dashboard__notice-import">Successfully Imported: <a href="' . $post_url . '" data-post-id="' . $post_id . '" data-permalink="' . $permalink . '">' . $data['title'] . '</a></div>';
		}
	}
}

?>
<div class="piotnetforms-dashboard piotnetforms-dashboard--templates">
	<div class="piotnetforms-dashboard__form-name">
		<div class="piotnetforms-dashboard__form-name-title">Name Your Form</div>
		<div class="piotnetforms-dashboard__form-name-input">
			<input type="text" name="form_name" placeholder="Enter your form name" data-piotnetforms-add-new-form-name>
		</div>
	</div>
	<div class="piotnetforms-dashboard__sidebar">
		<div class="piotnetforms-editor__search active" data-piotnetforms-dashboard-search>
			<input class="piotnetforms-editor__search-input" data-piotnetforms-dashboard-search-input type="text" placeholder="<?php echo __( 'Search Templates', 'piotnetforms' ); ?>">
			<div class="piotnetforms-editor__search-icon">
				<img src="<?php echo plugin_dir_url( __FILE__ ) . '../../assets/icons/e-search.svg'; ?>">
			</div>
		</div>
		<div class="piotnetforms-dashboard__category">
			<?php
				$templates_categories = [
					'all' => __( 'All Templates', 'piotnetforms' ),
					// 'business-operations' => __('Business Operations', 'piotnetforms'),
					// 'customer-service' => __('Customer Service', 'piotnetforms'),
					// 'education' => __('Education', 'piotnetforms'),
					// 'entertainment' => __('Entertainment', 'piotnetforms'),
					// 'event Planning' => __('Event Planning', 'piotnetforms'),
					// 'feedback' => __('Feedback', 'piotnetforms'),
					'import' => __( 'Import', 'piotnetforms' ),
				];

$tab = !empty( $_GET['tab'] ) ? $_GET['tab'] : 'all';

foreach ( $templates_categories as $key => $templates_category ) :
	?>
				<div class="piotnetforms-dashboard__category-item<?php if ( $key == $tab ) {
					echo ' active';
				} ?>" data-piotnetforms-dashboard-category='<?php echo $key;?>'><?php echo $templates_category; ?></div>
			<?php endforeach; ?>
		</div>
	</div>
	<div class="piotnetforms-dashboard__content">
		<div class="piotnetforms-dashboard__title"><?php echo __( 'Select a Template', 'piotnetforms' ); ?></div>
		<div class="piotnetforms-dashboard__templates">
			<?php
					$templates = [
						[
							'title' => __( 'Blank Form', 'piotnetforms' ),
							'category' => 'customer-service',
							'blank' => true,
							'folder' => 'blank',
						],
						[
							'title' => __( 'Simple Contact Form', 'piotnetforms' ),
							'category' => 'simple',
							'folder' => 'simple-contact-form',
						],
						[
							'title' => __( 'Conversational Form', 'piotnetforms' ),
							'category' => 'customer-service',
							'folder' => 'conversational-form',
							'demo' => 'https://piotnetforms.com/piotnetforms/conversational-form/',
						],
						[
							'title' => __( 'Simple Multi Step Form', 'piotnetforms' ),
							'category' => 'customer-service',
							'folder' => 'simple-multi-step-form',
							'demo' => 'https://piotnetforms.com/piotnetforms/simple-multi-step-form/',
						],
						[
							'title' => __( 'Calculation Form', 'piotnetforms' ),
							'category' => 'customer-service',
							'folder' => 'calculation-form',
						],
						[
							'title' => __( 'Loan Calculator Form', 'piotnetforms' ),
							'category' => 'customer-service',
							'folder' => 'loan-calculator-form',
						],
						[
							'title' => __( 'Conditional Logic Form', 'piotnetforms' ),
							'category' => 'customer-service',
							'folder' => 'conditional-logic-form',
							'demo' => 'https://piotnetforms.com/piotnetforms/conditional-logic-form/',
						],
						[
							'title' => __( 'Frontend Post Submissions Form', 'piotnetforms' ),
							'category' => 'customer-service',
							'folder' => 'frontend-post-submissions-form',
						],
						[
							'title' => __( 'Repeater Fields', 'piotnetforms' ),
							'category' => 'customer-service',
							'folder' => 'repeater-fields',
							'demo' => 'https://piotnetforms.com/piotnetforms/repeater-fields/',
						],
						[
							'title' => __( 'Label Animation', 'piotnetforms' ),
							'category' => 'customer-service',
							'folder' => 'label-animation',
							'demo' => 'https://piotnetforms.com/piotnetforms/label-animation-2/',
						],
						[
							'title' => __( 'Inline Fields', 'piotnetforms' ),
							'category' => 'customer-service',
							'folder' => 'inline-fields',
							'demo' => 'https://piotnetforms.com/piotnetforms/inline-fields/',
						],
						[
							'title' => __( 'Stripe Subscriptions', 'piotnetforms' ),
							'category' => 'customer-service',
							'folder' => 'stripe-subscriptions',
							'demo' => 'https://piotnetforms.com/piotnetforms/stripe-subscriptions/',
						],
						[
							'title' => __( 'Stripe Subscriptions with Plan Selection', 'piotnetforms' ),
							'category' => 'customer-service',
							'folder' => 'stripe-subscriptions-with-plan-selection',
							'demo' => 'https://piotnetforms.com/piotnetforms/stripe-subscriptions-with-plan-selection/',
						],
					];

foreach ( $templates as $template ) :
	?>
				<div class="piotnetforms-dashboard__template<?php if ( $tab == 'all' || $tab == $template['category'] ) {
					echo ' active';
				} ?>" data-piotnetforms-dashboard-item-category="<?php echo $template['category']; ?>" data-piotnetforms-dashboard-item-title="<?php echo strtolower( $template['title'] ); ?>">
					<div class="piotnetforms-dashboard__template-image" style="background-image:url('<?php echo plugin_dir_url( __FILE__ ) . '../../assets/forms/templates/' . $template['folder'] . '/image.jpg'; ?>');">
						<div class="piotnetforms-dashboard__template-buttons">
							<button class="piotnetforms-dashboard__template-button" data-piotnetforms-add-new-form="<?php echo $template['folder']; ?>"><?php if ( !empty( $template['blank'] ) ) {
								echo __( 'Create Blank Form', 'piotnetforms' );
							} else {
								echo __( 'Use Template', 'piotnetforms' );
							} ?></button>
							<?php if ( empty( $template['blank'] ) && !empty( $template['demo'] ) ) : ?>
								<a class="piotnetforms-dashboard__template-button piotnetforms-dashboard__template-button--secondary" href="<?php echo $template['demo']; ?>" target="_blank"><?php echo __( 'View Demo', 'piotnetforms' ); ?></a>
							<?php endif; ?>
						</div>
					</div>
					<div class="piotnetforms-dashboard__template-content">
						<div class="piotnetforms-dashboard__template-title"><?php echo $template['title']; ?></div>
					</div>
				</div>
			<?php endforeach; ?>
			<div data-piotnetforms-dashboard-item-category="import"<?php if ( $tab == 'import' ) : ?> class="active"<?php endif; ?>>
				<form method="post" enctype="multipart/form-data" action="">
					<?php
									wp_nonce_field( 'import_action', 'import_nonce' );
?>
					<div class="piotnetforms-dashboard__import-form">
						<input type="file" id="json_file" name="json_file">
						<input type="hidden" name="action" value="import_json_file">
						<?php submit_button( __( 'Import Now', 'piotnetforms' ) ); ?>
					</div>
					<?php
	if ( isset( $_POST['action'] ) ) {
		$import_action = $_POST['action'];
		if ( 'import_json_file' === $import_action && isset( $_POST['import_nonce'] ) && wp_verify_nonce( $_POST['import_nonce'], 'import_action' ) ) {
			$file_content = file_get_contents( $_FILES['json_file']['tmp_name'] );
			piotnetforms_import( $file_content );
		} elseif ( 'select_template' === $import_action && isset( $_POST['select_template_nonce'] ) && wp_verify_nonce( $_POST['select_template_nonce'], 'select_template_action' ) && isset( $_POST['templates'] ) ) {
			$template     = $_POST['templates'];
			$file_content = file_get_contents( __DIR__ . '/../../assets/forms/templates/' . $template );
			piotnetforms_import( $file_content );
		}
	}
?>
				</form>
			</div>
		</div>
	</div>
</div>

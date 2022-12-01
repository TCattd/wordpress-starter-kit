<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// require all widgets
foreach ( glob( __DIR__ . '/../widgets/*.php' ) as $file ) {
	require_once $file;
}

foreach ( glob( __DIR__ . '/../global/*.php' ) as $file ) {
	require_once $file;
}

$editor_class = 'piotnetforms_Editor';

class piotnetforms_Editor {
	private $widgets;

	public function __construct() {
		$this->widgets = [];
	}

	public function editor_panel() {
		$widgets = $this->widgets; ?>
		<div class="piotnetforms-editor__header">
			<div class="piotnetforms-editor__search active" data-piotnetforms-editor-header-search>
				<input class="piotnetforms-editor__search-input" data-piotnetforms-editor-header-search-input type="text" placeholder="Search Widget" />
				<div class="piotnetforms-editor__search-icon">
					<img src="<?php echo plugin_dir_url( __FILE__ ) . '../../assets/icons/e-search.svg'; ?>">
				</div>
			</div>
			<div class="piotnetforms-editor__header-text" data-piotnetforms-editor-header-text>Widgets</div>
		</div>

		<div class="piotnetforms-editor__widgets active" data-piotnetforms-widgets>
		<?php
		$categories = [];
		$categories = apply_filters( 'piotnetforms_widget_categories', $categories );

		foreach ( $categories as $category ) {
			echo '<div class="piotnetforms-editor__widgets-category" data-piotnetforms-widgets-category>';
			echo '<div class="piotnetforms-editor__widgets-category-title" data-piotnetforms-widgets-category-title>';
			echo '<div class="piotnetforms-editor__widgets-category-title-text">' . $category['title'] . '</div>';
			echo '<div class="piotnetforms-editor__widgets-category-title-arrow"><img src="' . plugin_dir_url( __FILE__ ) . '../../assets/icons/e-arrow-right.svg"></div>';
			echo '</div>';
			echo '<div class="piotnetforms-editor__widgets-list" data-piotnetforms-widgets-list>';
			foreach ( $widgets as $widget ) {
				if ( !$widget['is_global'] && in_array( $category['name'], $widget['category'] ) ) {
					echo "<div class='piotnetforms-editor__widgets-item' data-piotnetforms-widget-keywords='" . implode( ',', $widget['keywords'] ) . "'>";
					echo "<div class='piotnetforms-editor__widgets-item-inner' draggable='true' data-piotnetforms-editor-widgets-item-panel data-piotnetforms-editor-widgets-item='" . json_encode( $widget ) . "'>";
					if ( is_array( $widget['icon'] ) ) {
						switch ( $widget['icon']['type'] ) {
							case 'class':
								echo '<i class="' . esc_attr( $widget['icon']['value'] ) . '"></i>';
								break;
							case 'image':
								echo '<div class="piotnetforms-editor__widgets-icon-image"><img src="' . esc_attr( $widget['icon']['value'] ) . '"></div>';
								break;
							default:
								# code...
								break;
						}
					} else {
						echo '<i class="' . esc_attr( $widget['icon'] ) . '"></i>';
					}
					echo '<div class="piotnetforms-editor__widgets-item-name">';
					echo $widget['title'];
					echo '</div>';
					echo '</div>';
					echo '</div>';
				}
			}
			echo '</div>';
			echo '</div>';
		}

		echo '</div>';

		echo '<div class="piotnetforms-editor__widget-settings" data-piotnetforms-editor-widget-settings>'; ?>
		<?php
		echo '</div>';
	}

	public function editor_preview_loop( $loop, $post_id = 0 ) {
		foreach ( $loop as $widget_item ) {
			if ( !empty( $widget_item['class_name'] ) ) {
				$widget            = new $widget_item['class_name']();
				$widget->settings  = $widget_item['settings'];
				$widget_id         = $widget_item['id'];
				$widget->widget_id = $widget_id;
				$widget->post_id   = $post_id;

				if ( ! empty( $widget_item['fonts'] ) ) {
					$fonts = $widget_item['fonts'];
					if ( ! empty( $fonts ) ) {
						echo '<script>jQuery(document).ready(function( $ ) {';
						foreach ( $fonts as $font ) :
							?>
							$('head').append('<link href="<?php echo $font; ?>" rel="stylesheet">');
							<?php
						endforeach;
						echo '})</script>';
					}
				}

				$widget_type = $widget->get_type();
				if ( $widget_type === 'section' || $widget_type === 'column' ) {
					echo @$widget->output_wrapper_start( $widget_id, true );

					if ( isset( $widget_item['elements'] ) ) {
						@$this->editor_preview_loop( $widget_item['elements'], $post_id );
					}
				} else {
					echo @$widget->output( $widget_id, true );
				}

				if ( $widget_type === 'section' || $widget_type === 'column' ) {
					echo @$widget->output_wrapper_end( $widget_id, true );
				}
			}
		}
	}

	public function editor_preview( $widgets_settings, $post_id ) {
		ob_start();
		@$this->editor_preview_loop( $widgets_settings, $post_id );
		return ob_get_clean();
	}

	public function register_widget( $widget_object ) {
		$keywords = [strtolower( $widget_object->get_title() )];
		$keywords = array_merge( $keywords, $widget_object->get_keywords() );

		$this->widgets[] = [
			'type'       => $widget_object->get_type(),
			'class_name' => $widget_object->get_class_name(),
			'title'      => $widget_object->get_title(),
			'icon'       => $widget_object->get_icon(),
			'category'   => $widget_object->get_categories(),
			'is_global'  => isset( $widget_object->is_global ) ? true : false,
			'keywords'   => $keywords,
		];
	}

	public function register_widget_info( $widget_object ) {
		?>
		<script type="text/json" data-piotnetforms-widget-info="<?php echo esc_attr( $widget_object->get_type() ); ?>" id="piotnetforms-<?php echo esc_attr( $widget_object->get_type() ); ?>-widget-info">
			<?php echo json_encode( $widget_object->get_info() ); ?>
		</script>
		<?php
	}

	public function register_script( $widget_object ) {
		?>
		<script type="text/html" data-piotnetforms-template id="piotnetforms-<?php echo esc_attr( $widget_object->get_type() ); ?>-live-preview-template">
			<?php $widget_object->live_preview(); ?>
		</script>
		<?php
	}
}

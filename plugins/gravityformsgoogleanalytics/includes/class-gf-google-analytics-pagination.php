<?php

namespace Gravity_Forms\Gravity_Forms_Google_Analytics;

defined( 'ABSPATH' ) || die();

use GFCommon;
use Gravity_Forms\Gravity_Forms_Google_Analytics\GF_Google_Analytics;

/**
 * Gravity Forms Google Analytics Pagination Settings
 *
 * @since     1.0.0
 * @package   GravityForms
 * @author    Rocketgenius
 * @copyright Copyright (c) 2019, Rocketgenius
 */
class GF_Google_Analytics_Pagination {

	/**
	 * Holds the class instance.
	 *
	 * @since 1.0.0
	 * @var GF_Google_Analytics_Pagination $instance The Instance of pagination tracking.
	 */
	private static $instance = null;

	/**
	 * Instance of the addon class
	 *
	 * @since 1.3
	 * @var GF_Google_Analytics $instance The Instance of GF Google Analytics.
	 */
	private static $addon;

	/**
	 * Retrieve a class instance.
	 *
	 * @since 1.0.0
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$addon    = GF_Google_Analytics::get_instance();
			self::$instance = new self();
		}
		return self::$instance;
	} //end get_instance

	/**
	 * Send pagination events.
	 *
	 * @since 1.0.0
	 *
	 * @param string $ua_code             UA Code to send data to.
	 * @param string $mode                What mode the event is being sent as.
	 * @param array  $form                The form arguments.
	 * @param int    $source_page_number  The original page number.
	 * @param int    $current_page_number The new page number.
	 */
	public function paginate( $ua_code, $mode, $form, $source_page_number, $current_page_number ) {
		if ( ! rgar( $form, 'gravityformsgoogleanalytics' ) && ! rgar( $form, 'gravityformsgoogleanalytics/google_analytics_pagination' ) ) {
			return;
		}

		if ( rgar( $form['gravityformsgoogleanalytics'], 'google_analytics_pagination' ) !== '1' ) {
			return;
		}

		if ( $ua_code === false ) {
			return;
		}
		if ( ! class_exists( 'GF_Google_Analytics_Measurement_Protocol' ) ) {
			include 'class-gf-google-analytics-measurement-protocol.php';
		}
		$event = new GF_Google_Analytics_Measurement_Protocol();
		$event->init();
		$is_ajax_only = isset( $_REQUEST['gform_ajax'] );

		/**
		 * Filter: gform_googleanalytics_pagination_event_category
		 *
		 * Filter the event category dynamically
		 *
		 * @since 1.0.0
		 *
		 * @param string $category              Event Category
		 * @param array  $form                  Gravity Form form array
		 * @param int    $source_page_number    Source page number
		 * @param int    $current_page_number   Current Page Number
		 */
		$event_category = 'form';
		if ( isset( $form['gravityformsgoogleanalytics']['pagination_gaeventcategory'] ) ) {
			$pagination_category = trim( $form['gravityformsgoogleanalytics']['pagination_gaeventcategory'] );
			if ( ! empty( $pagination_category ) ) {
				$event_category = $pagination_category;
			}
		}
		$event_category = apply_filters( 'gform_googleanalytics_pagination_event_category', $event_category, $form, $source_page_number, $current_page_number );

		$event_action = 'pagination';
		if ( isset( $form['gravityformsgoogleanalytics']['pagination_gaeventaction'] ) ) {
			$pagination_action = trim( $form['gravityformsgoogleanalytics']['pagination_gaeventaction'] );
			if ( ! empty( $pagination_action ) ) {
				$event_action = $pagination_action;
			}
		}
		/**
		 * Filter: gform_googleanalytics_pagination_event_action
		 *
		 * Filter the event action dynamically
		 *
		 * @since 1.0.0
		 *
		 * @param string $action                Event Action
		 * @param array  $form                  Gravity Form form array
		 * @param int    $source_page_number    Source page number
		 * @param int    $current_page_number   Current Page Number
		 */
		$event_action = apply_filters( 'gform_googleanalytics_pagination_event_action', $event_action, $form, $source_page_number, $current_page_number );

		$event_label = sprintf(
			'%s::%d::%d',
			esc_html( $form['title'] ),
			absint( $source_page_number ),
			absint( $current_page_number )
		);
		if ( isset( $form['gravityformsgoogleanalytics']['pagination_gaeventlabel'] ) ) {
			$pagination_label = trim( $form['gravityformsgoogleanalytics']['pagination_gaeventlabel'] );
			if ( ! empty( $pagination_label ) ) {
				$pagination_label = str_replace( '{form_title}', esc_html( $form['title'] ), $pagination_label );
				$pagination_label = str_replace( '{source_page_number}', absint( $source_page_number ), $pagination_label );
				$pagination_label = str_replace( '{current_page_number}', absint( $current_page_number ), $pagination_label );
				$event_label      = $pagination_label;
			}
		}
		/**
		 * Filter: gform_googleanalytics_pagination_event_label
		 *
		 * Filter the event label dynamically
		 *
		 * @since 1.0.0
		 *
		 * @param string $label                 Event Label
		 * @param array  $form                  Gravity Form form array
		 * @param int    $source_page_number    Source page number
		 * @param int    $current_page_number   Current Page Number
		 */
		$event_label = apply_filters( 'gform_googleanalytics_pagination_event_label', $event_label, $form, $source_page_number, $current_page_number );

		$event_value = 0;
		if ( isset( $form['gravityformsgoogleanalytics']['pagination_value'] ) ) {
			$pagination_value = trim( $form['gravityformsgoogleanalytics']['pagination_value'] );
			if ( ! empty( $pagination_value ) ) {
				$event_value = $pagination_value;
			}
		}
		// Value is rounded up in Universal Analytics before given an absolute value. GA4 can accept decimal values.
		/**
		 * Filter: gform_googleanalytics_pagination_event_value
		 *
		 * Filter the event value dynamically
		 *
		 * @since 1.0.0
		 *
		 * @param int    $event_value           Event Value
		 * @param array  $form                  Gravity Form form array
		 * @param int    $source_page_number    Source page number
		 * @param int    $current_page_number   Current Page Number
		 */
		$event_value = absint( round( GFCommon::to_number( apply_filters( 'gform_googleanalytics_pagination_event_value', $event_value, $form, $source_page_number, $current_page_number ) ) ) );

		$this->log_debug( __METHOD__ . '(): Attempting to send pagination event with the following attributes: Action: "' . $event_action . '", Category: "' . $event_category . '", Label: "' . $event_label . '", Value: "' . $event_value . '"' );
		?>
		<script>
			if (typeof(Storage) !== "undefined" && false == <?php echo wp_json_encode( $is_ajax_only ); ?> ) {
				// Store Gravity Form Settings to access via JavaScript
				localStorage.setItem("gfpaginatetype", 'pagination');
				localStorage.setItem("gfpaginatecategory", '<?php echo esc_js( $event_category ); ?>');
				localStorage.setItem("gfpaginateaction", '<?php echo esc_js( $event_action ); ?>');
				localStorage.setItem("gfpaginatelabel", '<?php echo esc_js( $event_label ); ?>');
				localStorage.setItem("gfpaginatevalue", '<?php echo esc_js( $event_value ); ?>');
			}
		</script>
		<?php

		// Set environmental variables for the measurement protocol.
		$event->set_event_category( $event_category );
		$event->set_event_action( $event_action );
		$event->set_event_label( $event_label );
		if ( 0 !== $event_value ) {
			$event->set_event_value( $event_value );
		}
		$ip_address = '127.0.0.1';
		if ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$ip_address = sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) );
		} elseif ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
			$ip_address = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
		}
		$event->set_user_ip_address( $ip_address );

		if ( 'gmp' === $mode ) {
			$response = $event->send( $ua_code );
			if ( is_wp_error( $response ) ) {
				$this->log_debug( 'Sending pagination event with GMP failed: ' . $response->get_error_message() );
			}
		} elseif ( 'ga' === $mode && $is_ajax_only ) {
			?>
		<script>
			window.parent.jQuery.gf_send_pagination_to_ga(<?php echo esc_js( $event_value ); ?>,'<?php echo esc_js( $event_category ); ?>','<?php echo esc_js( $event_action ); ?>','<?php echo esc_js( $event_label ); ?>');
		</script>
			<?php
		} elseif ( 'gtm' === $mode && $is_ajax_only ) {
			?>
			<script>
				var utmVariables = localStorage.getItem('googleAnalyticsUTM');
				var utmSource = '',
					utmMedium = '',
					utmCampaign = '',
					utmTerm = '',
					utmContent = '';
				if ( null != utmVariables ) {
					utmVariables = JSON.parse( utmVariables );
					utmSource = utmVariables.source;
					utmMedium = utmVariables.medium;
					utmCampaign = utmVariables.campaign;
					utmTerm = utmVariables.term;
					utmContent = utmVariables.content;
				}
				window.parent.jQuery.gf_send_pagination_to_gtm(
					<?php echo esc_js( $event_value ); ?>,
					'<?php echo esc_js( $event_category ); ?>',
					'<?php echo esc_js( $event_action ); ?>',
					'<?php echo esc_js( $event_label ); ?>',
					utmSource,
					utmMedium,
					utmCampaign,
					utmTerm,
					utmContent
				);
			</script>
			<?php
		}
	}

	/**
	 * Helper method to send pagination events using the add-on's logging method.
	 *
	 * @since 1.3
	 *
	 * @param string $message The message to log.
	 */
	private function log_debug( $message ) {
		self::$addon->log_debug( $message );
	}
}

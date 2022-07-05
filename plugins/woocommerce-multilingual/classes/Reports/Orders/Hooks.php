<?php

namespace WCML\Reports\Orders;

use WCML\Utilities\Resources;
use WCML\Rest\Functions;

class Hooks implements \IWPML_Backend_Action, \IWPML_REST_Action, \IWPML_DIC_Action {

	/** @var \wpdb $wpdb */
	private $wpdb;

	public function __construct( \wpdb $wpdb ) {
		$this->wpdb = $wpdb;
	}

	public function add_hooks() {
		if ( Functions::isAnalyticsPage() ) {
			add_action( 'admin_enqueue_scripts', [ $this, 'enqueueAssets' ] );
		}

		add_filter( "woocommerce_analytics_clauses_join_orders_subquery", [ $this, 'addJoin' ] );
		add_filter( "woocommerce_analytics_clauses_join_orders_stats_total", [ $this, 'addJoin' ] );
		add_filter( "woocommerce_analytics_clauses_join_orders_stats_interval", [ $this, 'addJoin' ] );
		add_filter( "woocommerce_analytics_clauses_where_orders_subquery", [ $this, 'addWhere' ] );
		add_filter( "woocommerce_analytics_clauses_where_orders_stats_total", [ $this, 'addWhere' ] );
		add_filter( "woocommerce_analytics_clauses_where_orders_stats_interval", [ $this, 'addWhere' ] );
		add_filter( "woocommerce_analytics_clauses_select_orders_subquery", [ $this, 'addSelect' ] );
		add_filter( "woocommerce_analytics_clauses_select_orders_stats_total", [ $this, 'addSelect' ] );
		add_filter( "woocommerce_analytics_clauses_select_orders_stats_interval", [ $this, 'addSelect' ] );
	}

	public function enqueueAssets() {
		$enqueue = Resources::enqueueApp( 'reportsOrders' );

		$enqueue(
			[
				'name' => 'wcmlReports',
				'data' => [
					'strings' => [
						'languageLabel' => __( 'Language', 'woocommerce-multilingual' ),
					],
				],
			]
		);
	}

	/**
	 * @param array $clauses
	 *
	 * @return array
	 */
	public function addJoin( $clauses ) {
		$clauses[] = "LEFT JOIN {$this->wpdb->postmeta} wcml_language_postmeta ON wcml_language_postmeta.post_id = IF( {$this->wpdb->prefix}wc_order_stats.parent_id = 0, {$this->wpdb->prefix}wc_order_stats.order_id, {$this->wpdb->prefix}wc_order_stats.parent_id )";

		return $clauses;
	}

	/**
	 * @param array $clauses
	 *
	 * @return array
	 */
	public function addWhere( $clauses ) {
		$clauses[] = "AND wcml_language_postmeta.meta_key = 'wpml_language'";

		return $clauses;
	}

	/**
	 * @param array $clauses
	 *
	 * @return array
	 */
	public function addSelect( $clauses ) {
		$clauses[] = ', wcml_language_postmeta.meta_value AS language';

		return $clauses;
	}

}

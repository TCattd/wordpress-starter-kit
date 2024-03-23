<?php
/**
 * Data structure class
 *
 * @since 1.0
 */
namespace dologin;
defined( 'WPINC' ) || exit;

class Data extends Instance {
	private $_db_updater = array(
		'1.4.1' => array(
			'dologin_update_1_4_1',
		),
	);

	const TB_FAILURE = 'dologin_failure' ;
	const TB_SMS = 'dologin_sms' ;
	const TB_PSWDLESS = 'dologin_pswdless' ;

	/**
	 * Data upgrade
	 *
	 * @since  1.4.1
	 */
	public function conf_upgrade() {
		require_once DOLOGIN_DIR . 'src/data.upgrade.func.php' ;

		foreach ( $this->_db_updater as $k => $v ) {
			if ( version_compare( DOLOGIN_CUR_V, $k, '<' ) ) {
				// run each callback
				foreach ( $v as $v2 ) {
					defined( 'debug' ) && debug( "[Data] Updating [ori_v] " . DOLOGIN_CUR_V . " \t[to] $k \t[func] $v2" ) ;
					call_user_func( $v2 ) ;
				}
			}
		}

		Conf::delete( '_ver' ) ;
		Conf::add( '_ver', Core::VER ) ;

		Util::version_check( 'upgrade' );
	}

	/**
	 * Get the table name
	 *
	 * @since  1.3
	 * @access public
	 */
	public function tb( $tb ) {
		global $wpdb ;

		switch ( $tb ) {
			case 'failure':
				return $wpdb->prefix . self::TB_FAILURE;
				break;

			case 'sms':
				return $wpdb->prefix . self::TB_SMS;
				break;

			case 'pswdless':
				return $wpdb->prefix . self::TB_PSWDLESS;
				break;

			default:
				break;
		}

	}

	/**
	 * Check if one table exists or not
	 *
	 * @since  1.3
	 * @access public
	 */
	public function tb_exist( $tb ) {
		global $wpdb ;
		return $wpdb->get_var( 'SHOW TABLES LIKE "' . $this->tb( $tb ) . '"' ) ;
	}

	/**
	 * Get data structure of one table
	 *
	 * @since  1.3
	 * @access private
	 */
	private function _tb_structure( $tb ) {
		return f::read( DOLOGIN_DIR . 'src/data_structure/' . $tb . '.sql' ) ;
	}

	/**
	 * Create img optm table and sync data from wp_postmeta
	 *
	 * @since  1.3
	 * @access public
	 */
	public function tb_create( $tb ) {
		global $wpdb;

		defined( 'debug' ) && debug2( '[Data] Checking table ' . $tb );

		// Check if table exists first
		if ( $this->tb_exist( $tb ) ) {
			defined( 'debug' ) && debug2( '[Data] Existed' );
			return;
		}

		defined( 'debug' ) && debug( '[Data] Creating ' . $tb );

		$sql = sprintf(
			'CREATE TABLE IF NOT EXISTS `%1$s` (' . $this->_tb_structure( $tb ) . ') %2$s;',
			$this->tb( $tb ),
			$wpdb->get_charset_collate() // 'DEFAULT CHARSET=utf8'
		);

		$res = $wpdb->query( $sql );
		if ( $res !== true ) {
			defined( 'debug' ) && debug( '[Data] Warning! Creating table failed!', $sql );
		}
	}

	/**
	 * Drop table
	 *
	 * @since  1.3
	 * @access public
	 */
	public function tb_del( $tb ) {
		global $wpdb ;

		if ( ! $this->tb_exist( $tb ) ) {
			return ;
		}

		defined( 'debug' ) && debug( '[Data] Deleting table ' . $tb ) ;

		$q = 'DROP TABLE IF EXISTS ' . $this->tb( $tb ) ;
		$wpdb->query( $q ) ;
	}

	/**
	 * Create all tables
	 *
	 * @since  1.3
	 * @access public
	 */
	public function tables_create() {
		global $wpdb ;

		$this->tb_create( 'failure' );
		$this->tb_create( 'sms' );
		$this->tb_create( 'pswdless' );
	}

	/**
	 * Drop generated tables
	 *
	 * @since  1.3
	 * @access public
	 */
	public function tables_del() {
		global $wpdb ;

		$this->tb_del( 'failure' );
		$this->tb_del( 'sms' );
		$this->tb_del( 'pswdless' );
	}


}
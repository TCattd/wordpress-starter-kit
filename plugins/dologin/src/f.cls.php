<?php
/**
* File Operator
*
* @since 1.0
*/
namespace dologin;
defined( 'WPINC' ) || exit;

class f {
	/**
	 *	Delete folder
	 *
	 * @since 1.0
	 */
	public static function rrmdir( $dir ) {
		$files = array_diff( scandir( $dir ), array( '.', '..' ) );

		foreach ( $files as $file ) {
			is_dir( "$dir/$file" ) ? self::rrmdir( "$dir/$file" ) : unlink( "$dir/$file" );
		}

		return rmdir( $dir );
	}

	public static function count_lines($filename) {
		if ( ! file_exists($filename) ) {
			return 0;
		}

		$file = new \SplFileObject($filename);
		$file->seek(PHP_INT_MAX);
		return $file->key() + 1;
	}

	/**
	 * Read data from file
	 *
	 * @since 1.0
	 */
	public static function read( $filename, $start_line = null, $lines = null ) {
		if ( ! file_exists( $filename ) ) {
			return '';
		}

		if ( ! is_readable( $filename ) ) {
			return false;
		}

		if ( $start_line !== null ) {
			$res = array();
			$file = new \SplFileObject( $filename );
			$file->seek( $start_line );

			if ( $lines === null ) {
				while ( ! $file->eof() ) {
					$res[] = rtrim( $file->current(), PHP_EOL );
					$file->next();
				}
			}
			else{
				for ( $i = 0; $i < $lines; $i++ ) {
					if ( $file->eof() ) {
						break;
					}
					$res[] = rtrim( $file->current(), PHP_EOL );
					$file->next();
				}
			}

			unset( $file );
			return $res;
		}

		$content = file_get_contents( $filename );

		$content = self::remove_zero_space( $content );

		return $content;
	}

	/**
	 * Append data to file
	 *
	 * @since 1.0
	 * @access public
	 */
	public static function append( $filename, $data, $mkdir = false, $silence = true ) {
		return self::save( $filename, $data, $mkdir, true, $silence );
	}

	/**
	 * Save data to file
	 *
	 * @since 1.0
	 */
	public static function save( $filename, $data, $mkdir = false, $append = false, $silence = true ) {
		$error = false;
		$folder = dirname( $filename );

		// mkdir if folder does not exist
		if ( ! file_exists( $folder ) ) {
			if ( ! $mkdir ) {
				return $silence ? false : sprintf( __( 'Folder does not exist: %s', 'dologin' ), $folder );
			}

			try {
				mkdir( $folder, 0755, true );
			}
			catch ( \Exception $ex ) {
				return $silence ? false : sprintf( __( 'Can not create folder: %1$s. Error: %2$s', 'dologin' ), $folder, $ex->getMessage() );
			}
		}

		if ( ! file_exists( $filename ) ) {
			if ( ! is_writable( $folder ) ) {
				return $silence ? false : sprintf( __( 'Folder is not writable: %s.', 'dologin' ), $folder );
			}
			try {
				touch( $filename );
			}
			catch ( \Exception $ex ){
				return $silence ? false : sprintf( __( 'File %s is not writable.', 'dologin' ), $filename );
			}
		}
		elseif ( ! is_writeable( $filename ) ) {
			return $silence ? false : sprintf( __( 'File %s is not writable.', 'dologin' ), $filename );
		}

		$data = self::remove_zero_space( $data );

		$ret = file_put_contents( $filename, $data, $append ? FILE_APPEND : LOCK_EX );
		if ( $ret === false ) {
			return $silence ? false : sprintf( __( 'Failed to write to %s.', 'dologin' ), $filename );
		}

		return true;
	}

	/**
	 * Remove Unicode zero-width space <200b><200c>
	 *
	 * @since 1.0
	 */
	public static function remove_zero_space( $content ) {
		if ( is_array( $content ) ) {
			$content = array_map( __CLASS__ . '::remove_zero_space', $content );
			return $content;
		}

		// Remove UTF-8 BOM if present
		if ( substr( $content, 0, 3 ) === "\xEF\xBB\xBF" ) {
			$content = substr( $content, 3 );
		}

		$content = str_replace( "\xe2\x80\x8b", '', $content );
		$content = str_replace( "\xe2\x80\x8c", '', $content );
		$content = str_replace( "\xe2\x80\x8d", '', $content );

		return $content;
	}

}



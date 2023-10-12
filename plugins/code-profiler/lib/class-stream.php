<?php
/**
 +=====================================================================+
 |    ____          _        ____             __ _ _                   |
 |   / ___|___   __| | ___  |  _ \ _ __ ___  / _(_) | ___ _ __         |
 |  | |   / _ \ / _` |/ _ \ | |_) | '__/ _ \| |_| | |/ _ \ '__|        |
 |  | |__| (_) | (_| |  __/ |  __/| | | (_) |  _| | |  __/ |           |
 |   \____\___/ \__,_|\___| |_|   |_|  \___/|_| |_|_|\___|_|           |
 |                                                                     |
 |  (c) Jerome Bruandet ~ https://code-profiler.com/                   |
 +=====================================================================+
*/

if (! defined( 'ABSPATH' ) ) { die( 'Forbidden' ); }

// =====================================================================

class CodeProfiler_Stream {

	static $io_stats = [
		'cast'		=> 0,	'chgrp'		=> 0,
		'chmod'		=> 0,	'chown'		=> 0,
		'close'		=> 0,	'closedir'	=> 0,
		'eof'			=> 0,	'flush'		=> 0,
		'lock'		=> 0,	'mkdir'		=> 0,
		'open'		=> 0,	'opendir'	=> 0,
		'readdir'	=> 0,	'rewinddir'	=> 0,
		'read'		=> 0,	'rename'		=> 0,
		'rmdir'		=> 0,	'seek'		=> 0,
		'set_option'=> 0,	'stat'		=> 0,
		'tell'		=> 0,	'truncate'	=> 0,
		'write'		=> 0,	'touch'		=> 0,
		'unlink'		=> 0,	'url_stat'	=> 0
	];
	static $io_read  = 0;
	static $io_write = 0;
	public $context;
	public $resource;
	protected static $protocol = 'file';
	private $script;


	public static function start() {
		stream_wrapper_unregister( self::$protocol );
		stream_wrapper_register( self::$protocol, CodeProfiler_Stream::class );
	}


	public static function stop() {
		stream_wrapper_restore( self::$protocol );
	}

	/**
	 * Opens file or URL
	 */
	public function stream_open( $path, $mode, $options, &$opened_path ) {
		self::$io_stats['open']++;
		self::stop();
		if ( isset( $this->context) ) {
			$this->resource = fopen( $path, $mode, $options, $this->context );
		} else {
			$this->resource = fopen( $path, $mode, $options );
		}
		self::start();
		if ( in_array( $mode, ['rb', 'rt', 'r']) && "{$path[-4]}{$path[-3]}{$path[-2]}{$path[-1]}" == '.php') {
			$this->script = 1;
		}
		return $this->resource !== false;
	}

	/**
	 * Close a resource
	 */
	public function stream_close() {
		self::$io_stats['close']++;
		return fclose( $this->resource );
	}

	/**
	 * Tests for end-of-file on a file pointer
	 */
	public function stream_eof() {
		self::$io_stats['eof']++;
		return feof( $this->resource );
	}

	/**
	 * Flushes the output
	 */
	public function stream_flush() {
		self::$io_stats['flush']++;
		return fflush( $this->resource );
	}

	/**
	 * Seeks to specific location in a stream
	 */
	public function stream_seek( $offset, $whence = SEEK_SET ) {
		self::$io_stats['seek']++;
		return fseek( $this->resource, $offset, $whence ) === 0;
	}

	/**
	 * Delete a file
	 */
	public function unlink( $path ) {
		self::$io_stats['unlink']++;
		self::stop();
		if ( isset( $this->context ) ) {
			$res = unlink( $path, $this->context );
		} else {
			$res = unlink( $path );
		}
		self::start();
		return $res;
    }

	/**
	 * Renames a file or directory
	 */
	public function rename( $path_from, $path_to ) {
		self::$io_stats['rename']++;
		self::stop();
		if ( isset( $this->context ) ) {
			$res = rename( $path_from, $path_to, $this->context );
		} else {
			$res = rename( $path_from, $path_to );
		}
		self::start();
		return $res;
	}

	/**
	 * Removes a directory
	 */
	public function rmdir( $path, $options ) {
		self::$io_stats['rmdir']++;
		self::stop();
		if ( isset( $this->context ) ) {
			$res = rmdir( $path, $this->context );
		} else {
			$res = rmdir( $path );
		}
		self::start();
		return $res;
    }

	/**
	 * Retrieve the underlaying resource
	 */
	public function stream_cast( $cast_as ) {
		self::$io_stats['cast']++;
		return $this->resource;
	}

	/**
	 * Advisory file locking
	 */
	public function stream_lock( $operation ) {
		self::$io_stats['lock']++;
		if (! $operation ) {
			$operation = LOCK_EX;
		}
		return flock( $this->resource, $operation );
	}

	/**
	 * Truncate stream
	 */
	public function stream_truncate( $new_size ) {
		self::$io_stats['truncate']++;
		return ftruncate( $this->resource, $new_size );
	}


	public function stream_write( $data ) {
		self::$io_stats['write']++;
		$write = fwrite( $this->resource, $data );
		self::$io_write += $write;
		return $write;
	}

	/**
	 * Change stream options
	 */
	public function stream_set_option( $option, $arg1, $arg2 ) {
		self::$io_stats['set_option']++;
		switch ( $option ) {
			case STREAM_OPTION_BLOCKING:
				return stream_set_blocking( $this->resource, $arg1 );
			case STREAM_OPTION_READ_TIMEOUT:
				return stream_set_timeout( $this->resource, $arg1, $arg2 );
			case STREAM_OPTION_WRITE_BUFFER:
				return stream_set_write_buffer( $this->resource, $arg1 );
			case STREAM_OPTION_READ_BUFFER:
				return stream_set_read_buffer( $this->resource, $arg1 );
			default:
				return false;
		}
	}

	/**
	 * Change stream metadata
	 */
	public function stream_metadata( $path, $option, $value ) {
		self::stop();
		$res = false;
		switch ( $option ) {
			case STREAM_META_ACCESS:
				$res = chmod( $path, $value );
				self::$io_stats['chmod']++;
				break;
			case STREAM_META_GROUP:
			case STREAM_META_GROUP_NAME:
				$res = chgrp( $path, $value );
				self::$io_stats['chgrp']++;
				break;
			case STREAM_META_OWNER:
			case STREAM_META_OWNER_NAME:
				$res = chown( $path, $value );
				self::$io_stats['chown']++;
				break;
			case STREAM_META_TOUCH:
				if (! empty( $value ) ) {
					$res = touch( $path, $value[0], $value[1] );
				} else {
					$res = touch( $path );
				}
				self::$io_stats['touch']++;
				break;
		}
		self::start();
		return $res;
	}

	/**
	 * Create a directory
	 */
	public function mkdir( $path, $mode, $options ) {
		self::$io_stats['mkdir']++;
		self::stop();
		if ( isset( $this->context ) ) {
			$res = mkdir( $path, $mode, $options, $this->context );
		} else {
			$res = mkdir( $path, $mode, $options );
		}
      self::start();
		return $res;
	}

	/**
	 * Open directory handle
	 */
	public function dir_opendir( $path, $options ) {
		self::$io_stats['opendir']++;
		self::stop();
		if ( isset( $this->context ) ) {
			$this->resource = opendir( $path, $this->context );
		} else {
			$this->resource = opendir( $path );
		}
		self::start();
		return $this->resource;
	}

	/**
	 * Close directory handle
	 */
	public function dir_closedir() {
		self::$io_stats['closedir']++;
		// closedir returns no value
		return closedir( $this->resource );
	}

	/**
	 * Read entry from directory handle
	 */
	public function dir_readdir() {
		self::$io_stats['readdir']++;
		return readdir( $this->resource );
	}

	/**
	 * Rewind directory handle
	 */
	public function dir_rewinddir() {
		self::$io_stats['rewinddir']++;
		return rewinddir( $this->resource );
	}

	/**
	 * Retrieve information about a file
	 */
	public function url_stat( $path, $flags ) {
		self::$io_stats['url_stat']++;
		self::stop();
		// Catch error and exception
		set_error_handler( function() {} );
		try {
			$res = stat( $path );
		} catch ( \Exception $e ) {
			$res = null;
		}
		restore_error_handler();
		self::start();
		return $res;
	}

	/**
	 * Read from stream & enable ticks
	 */
	public function stream_read( $count ) {
		self::$io_stats['read']++;
		if ( $this->script ) {
			self::stop();
			if ( ftell( $this->resource ) == 0 ) {
				self::start();
				$read = fread( $this->resource, $count - CODE_PROFILER_LENGTH );
				self::$io_read += strlen( $read );
				return preg_replace(
					'/<\?php/i',
					'<?php declare(ticks='. CODE_PROFILER_TICKS .');',
					$read,
					1
				);
			}
			self::start();
		}
		$read = fread( $this->resource, $count );
		self::$io_read += strlen( $read );
		return $read;
	}

	/**
	 * Retrieve information about a file resource
	 */
	public function stream_stat() {
		self::$io_stats['stat']++;
		$res = fstat( $this->resource );
		if ( $this->script ) {
			$res['size']	+= CODE_PROFILER_LENGTH;
			$res[7]			+= CODE_PROFILER_LENGTH;
		}
		return $res;
    }

	/**
	 * Retrieve the current position of a stream
	 */
	function stream_tell() {
		self::$io_stats['tell']++;
		$res = ftell( $this->resource );
		if ( $this->script ) {
			$res += CODE_PROFILER_LENGTH;
		}
		return $res;
	}
}

// =====================================================================
// EOF

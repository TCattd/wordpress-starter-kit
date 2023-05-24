<?php namespace CheckEmail\Util;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly

/**
 * Class Email Header Parser.
 */
class Check_Email_Header_Parser {

	public function join_headers( $data ) {
		$headers = '';

		if ( ! empty( $data['from'] ) ) {
			$headers .= 'From: ' . $data['from'] . "\r\n";
		}

		if ( ! empty( $data['cc'] ) ) {
			$headers .= 'CC: ' . $data['cc'] . "\r\n";
		}

		if ( ! empty( $data['bcc'] ) ) {
			$headers .= 'BCC: ' . $data['bcc'] . "\r\n";
		}

		if ( ! empty( $data['reply_to'] ) ) {
			$headers .= 'Reply-to: ' . $data['reply_to'] . "\r\n";
		}

		if ( ! empty( $data['content_type'] ) ) {
			$headers .= 'Content-type: ' . $data['content_type'] . "\r\n";
		}

		return $headers;
	}

	public function parse_headers( $headers ) {
		return $this->parse( $headers );
	}

	private function parse( $headers ) {
		$data        = array();
		$arr_headers = explode( "\n", $headers );

		foreach ( $arr_headers as $header ) {
			$split_header = explode( ':', $header );
			$value        = $this->parse_header_line( $split_header );

			if ( trim( $value ) != '' ) {
				switch ( strtolower( $split_header[0] ) ) {
					case 'from':
						$data['from'] = $value;
						break;

					case 'cc':
						$data['cc'] = $value;
						break;

					case 'bcc':
						$data['bcc'] = $value;
						break;

					case 'reply-to':
						$data['reply_to'] = $value;
						break;

					case 'content-type':
						$data['content_type'] = $value;
						break;
				}
			}
		}

		return $data;
	}

	private function parse_header_line( $header ) {
		$value = '';
		if ( 2 == count( $header ) ) {
			if ( is_array( $header[1] ) ) {
				$value = trim( implode( ',', array_map( 'trim', $header[1] ) ) );
			} else {
				$value = trim( $header[1] );
			}
		}

		return $value;
	}
}

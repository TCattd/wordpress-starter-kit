<?php

namespace LLAR\Core\Http;

interface HttpTransportInterface {
	public function get( $url, $options = array() );
	public function post( $url, $options = array() );
}
<?php
/**
 * String handler
 *
 * @since  1.3
 */
namespace dologin;
defined( 'WPINC' ) || exit;

class s {
	//xml load sanitize
	public static function sanitizeXml( $content ) {
	  if (!$content) return '';
	  $invalid_characters = '/[^\x9\xa\x20-\xD7FF\xE000-\xFFFD]/';
	  return preg_replace($invalid_characters, '', $content);
	}

	//id2shortURL
	public static function num2code( $id ) {
		global $_charArr, $_len;
		if(!$_charArr) {
			$_charArr = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
			$_len = strlen($_charArr);
		}
		$url = $id >= $_len ? self::num2code(floor($id/$_len)) : '';
		$url .= $_charArr[$id%$_len];
		return $url;
	}

	public static function code2num( $url ) {
		global $_charArr, $_len;
		if(!$_charArr) {
			$_charArr = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
			$_len = strlen($_charArr);
		}
		$id = pow($_len, strlen($url)-1)*strpos($_charArr, substr($url, 0, 1));
		if(strlen($url) > 1) $id += self::code2num(substr($url, 1));
		return $id;
	}

	//parse bbcode
	public static function bbcode( $str ) {
		$search = array (
			"~\[ul\]~",
			"~\[ol\]~",
			"~\n*\[li\]~",
			"~\[/li\]\n*~",
			"~\n*\[/ul\]\s{0,2}~",
			"~\n*\[/ol\]\s{0,2}~",

			"~\[/td\][^\[]*~",
			"~\[/tr\][^\[]*~",
			"~\[table\]~",
			"~\[tr\]~",
			"~\[td\]~",
			"~\[/table\]~",
			'~\[hr\]~',
			'~\[b\]~', '~\[/b\]~',
			'~\[i\]~', '~\[/i\]~',
			'~\[u\]~', '~\[/u\]~',
			'~\[left\]~', "~\[/left\]\s{0,2}~",
			'~\[center\]~', "~\[/center\]\s{0,2}~",
			'~\[right\]~', "~\[/right\]\s{0,2}~",
			'~\[size=(\d+)\]~isU', '~\[/size\]~',
			'~\[color=([^\]]+)\]~isU', '~\[/color\]~',
			'~\[font=([^\]]+)\]~isU', '~\[/font\]~',
			'~\[url=([^\]]+)\]~isU', '~\[/url\]~',
			'~\[youtube\]([^\[]+)\[/youtube\]~',
			'~\[iframe\]([^\[]+)\[/iframe\]~',
			'~\[mp4\]([^\[]+)\[/mp4\]~',
			"~\n~",
		);

		$replace = array (
			'<ul>',
			'<ol>',
			'<li>',
			'</li>',
			'</ul>',
			'</ol>',

			'</td>',
			'</tr>',
			'<table class="table table-bordered table-striped">',
			'<tr>',
			'<td>',
			'</table>',
			'<hr />',
			'<strong>', '</strong>',
			'<em>', '</em>',
			'<u>', '</u>',
			'<p>', '</p>',
			'<p class="text-center">', "</p>",
			'<p class="text-right">', '</p>',
			'<font size="$1">', '</font>',
			'<font color="$1">', '</font>',
			'<font face="$1">', '</font>',
			'<a href="$1" target="_blank">', '</a>',
			'<iframe id="ytplayer" type="text/html" width="640" height="360" src="https://www.youtube.com/embed/$1" frameborder="0" allowfullscreen></iframe>',
			'<iframe width="640" height="360" src="$1" frameborder="0" allowfullscreen></iframe>',
			'<video style="max-width:700px;"><source type="video/mp4" src="$1"></video>',
			'<br />',
		);
		return preg_replace($search, $replace, $str);
	}

	/**
	 *	XML parser
	 *
	 */
	public static function xml2arr($xml) {
		$values = array();
		$index  = array();
		$array  = array();
		$parser = xml_parser_create();
		xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
		xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
		xml_parse_into_struct($parser, $xml, $values, $index);
		xml_parser_free($parser);
		$i = 0;
		$name = $values[$i]['tag'];
		$array[$name] = isset($values[$i]['attributes']) ? $values[$i]['attributes'] : '';
		$array[$name] = self::_xml2arr($values, $i);
		return $array;
	}

	private static function _xml2arr($values, &$i) {
		$child = array();
		if(isset($values[$i]['value'])) array_push($child, $values[$i]['value']);
		while ($i++ < count($values)){
			switch ($values[$i]['type']){
				case 'cdata':
					array_push($child, $values[$i]['value']);
					break;
				case 'complete':
					$name = $values[$i]['tag'];
					if(!empty($name)){
						if(isset($child[$name])){
							if(!isset($child[$name][0])) $child[$name] = array($child[$name]);
							if(isset($values[$i]['attributes'])) $child[$name][] = $values[$i]['attributes'];
						}else{
							$child[$name]= !empty($values[$i]['value']) ? $values[$i]['value'] : '';
							if(isset($values[$i]['attributes'])) $child[$name] = $values[$i]['attributes'];
						}
					}
					break;
				case 'open':
					$name = $values[$i]['tag'];
					$size = isset($child[$name]) ? sizeof($child[$name]) : 0;
					$child[$name][$size] =  self::_xml2arr($values, $i);
					break;
				case 'close':
					return $child;
					break;
			}
		}
		return $child;
	}

	/**
	 *	code convert
	 *
	 */
	public static function conv($text, $from = 'utf-8', $to = 'gbk') {
		if(is_array($text)) foreach($text as $key => $val) $text[$key] = self::conv($val, $from, $to);
		else $text = mb_convert_encoding($text, $to, $from);
		return $text;
	}

	/**
	 *	sub str
	 *
	 */
	public static function rsubstr($string, $len, $add = 0) {
		$str2 = mb_substr($string, 0, $len, 'utf-8');
		if($add != 0) $add ++;
		if(strlen($str2) < strlen($string) && $add != 0) {
			$add ++;
			$leftchars = floor(($len * 2 - self::len($str2)) / 2);
			if($leftchars > 0) $str2 .= "...";
		}
		return $str2;
	}

	/**
	 *	strlen
	 *
	 */
	public static function len($string, $charNum = 0) {
		if(!$charNum) return strlen(self::conv($string));
		return mb_strlen($string, 'utf-8');
	}

	/**
	 *	br
	 *
	 */
	public static function htmlBr( $string ) {
		if(is_array($string)) foreach($string as $k => $v) $string[$k] = self::htmlBr($v);
		else $string = str_replace(array('  ', "\r"."\n", "\n", "\t"), array('&nbsp;&nbsp;', '<br />', '<br />', '&nbsp;&nbsp;&nbsp;&nbsp;'), $string);
		return $string;
	}

	/**
	 *	HTML
	 *
	 */
	public static function html( $string, $showBr = true, $noConv = array() ) {
		if(!$string) return $string;
		if(!is_array($noConv)) $noConv = array($noConv);
		if(is_array($string)){
			foreach($string as $k => $v) {
				if(isInt($k) || !in_array($k, $noConv)) $string[$k] = self::html($v, $showBr, $noConv);
			}
		}else{
			$string = str_replace(array('&', '<', '>', '"', "'", '\\'), array('&amp;', '&lt;', '&gt;', '&quot;', '&#039;', '&#092;'), $string);
		}
		if(!is_array($string) && $showBr) return self::htmlBr($string);
		return $string;
	}

	/**
	 *	convert str to color
	 *
	 */
	public static function color( $str, $returnColor = false ) {
		global $_color;
		if(!$str) return false;
		if(!$_color) $_color = self::colorArr2();//90

		$str2 = hexdec(substr(md5($str), -3)) % count($_color);
		if($returnColor) return $_color[$str2];
		return "<font color='".$_color[$str2]."'>$str</font>";
	}

	/**
	 *	Random
	 */
	public static function rrand( $len, $type = 7 ) {
		mt_srand( ( double ) microtime() * 1000000 );

		switch( $type ) {
			case 0 :
				$charlist = '012';
				break;

			case 1 :
				$charlist = '0123456789';
				break;

			case 2 :
				$charlist = 'abcdefghijklmnopqrstuvwxyz';
				break;

			case 3 :
				$charlist = '0123456789abcdefghijklmnopqrstuvwxyz';
				break;

			case 4 :
				$charlist = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
				break;

			case 5 :
				$charlist = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
				break;

			case 6 :
				$charlist = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
				break;

			case 7 :
				$charlist = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
				break;

		}

		$str = '';

		$max = strlen( $charlist ) - 1;
		for( $i = 0; $i < $len; $i++ ) {
			$str .= $charlist[ mt_rand( 0, $max ) ];
		}

		return $str;
	}

	/**
	 *	encrypt
	 *
	 * NOTE: if use the result in url, need to add urlencode() for the returning data ( no urldecode needed for decrypt )
	 *
	 */
	const METHOD = 'aes-256-cbc';
	public static function encrypt($str) {
		global $__hash;
		$key = substr(hash('sha256', $__hash), 0, 32);
		$ivsize = openssl_cipher_iv_length(self::METHOD);
		$iv = openssl_random_pseudo_bytes($ivsize);
		$str_encrypt = openssl_encrypt(
			$str,
			self::METHOD,
			$key,
			OPENSSL_RAW_DATA,
			$iv
		);
		return base64_encode($iv.$str_encrypt);
	}

	public static function decrypt($str) {
		global $__hash;
		$str = base64_decode($str);
		$key = substr(hash('sha256', $__hash), 0, 32);
		$ivsize = openssl_cipher_iv_length(self::METHOD);
		$iv = substr($str, 0, $ivsize);
		$ciphertext = substr($str, $ivsize);

		try {
			$str_decrypt = openssl_decrypt(
				$ciphertext,
				self::METHOD,
				$key,
				OPENSSL_RAW_DATA,
				$iv
			);
		} catch ( \Exception $e ) {
			$str_decrypt = false;
		}
		return $str_decrypt;
	}

	/**
	 * Convert array to safe url
	 */
	public static function arr2url_encrypt( $arr ) {
		return urlencode( self::encrypt( arr2str( $arr ) ) );
	}

	/**
	 *	seeable color
	 *
	 */
	private static function colorArr2() {
		return array(
			'#000000',
			'#38B0DE',
			'#00FF00',
			'#0000FF',
			'#FF00FF',
			'#00FFFF',
			'#70DB93',
			'#5C3317',
			'#9F5F9F',
			'#B5A642',
			'#D9D919',
			'#A67D3D',
			'#8C7853',
			'#A67D3D',
			'#5F9F9F',
			'#D98719',
			'#B87333',
			'#FF7F00',
			'#42426F',
			'#5C4033',
			'#4A766E',
			'#4F4F2F',
			'#9932CD',
			'#871F78',
			'#6B238E',
			'#2F4F4F',
			'#97694F',
			'#7093DB',
			'#855E42',
			'#545454',
			'#856363',
			'#D19275',
			'#8E2323',
			'#238E23',
			'#CD7F32',
			'#527F76',
			'#93DB70',
			'#215E21',
			'#4E2F2F',
			'#9F9F5F',
			'#32CD32',
			'#E47833',
			'#8E236B',
			'#32CD99',
			'#3232CD',
			'#6B8E23',
			'#9370DB',
			'#426F42',
			'#7F00FF',
			'#7FFF00',
			'#70DBDB',
			'#DB7093',
			'#A68064',
			'#2F2F4F',
			'#23238E',
			'#4D4DFF',
			'#FF6EC7',
			'#00009C',
			'#CFB53B',
			'#FF7F00',
			'#236B8E',
			'#DB70DB',
			'#8FBC8F',
			'#BC8F8F',
			'#EAADEA',
			'#5959AB',
			'#6F4242',
			'#00FF7F',
			'#238E68',
			'#6B4226',
			'#8E6B23',
			'#3299CC',
			'#007FFF',
			'#FF1CAE',
		);
	}

	/**
	 *	seeable color
	 *
	 */
	private static function colorArr() {
		return array(
			array( 'color' => '255 0 0',     'hexcolor' => '#FF0000' ),
			array( 'color' => '0 255 0',     'hexcolor' => '#00FF00' ),
			array( 'color' => '0 0 255',     'hexcolor' => '#0000FF' ),
			array( 'color' => '255 0 255',   'hexcolor' => '#FF00FF' ),
			array( 'color' => '0 255 255',   'hexcolor' => '#00FFFF' ),
			array( 'color' => '255 255 0',   'hexcolor' => '#FFFF00' ),
			array( 'color' => '112 219 147', 'hexcolor' => '#70DB93' ),
			array( 'color' => '255 255 255', 'hexcolor' => '#FFFFFF' ),
			array( 'color' => '0 0 0',       'hexcolor' => '#000000' ),
			array( 'color' => '92 51 23',    'hexcolor' => '#5C3317' ),
			array( 'color' => '159 95 159',  'hexcolor' => '#9F5F9F' ),
			array( 'color' => '181 166 66',  'hexcolor' => '#B5A642' ),
			array( 'color' => '217 217 25',  'hexcolor' => '#D9D919' ),
			array( 'color' => '166 125 61',  'hexcolor' => '#A67D3D' ),
			array( 'color' => '140 120 83',  'hexcolor' => '#8C7853' ),
			array( 'color' => '166 125 61',  'hexcolor' => '#A67D3D' ),
			array( 'color' => '95 159 159',  'hexcolor' => '#5F9F9F' ),
			array( 'color' => '217 135 25',  'hexcolor' => '#D98719' ),
			array( 'color' => '184 115 51',  'hexcolor' => '#B87333' ),
			array( 'color' => '255 127 0',   'hexcolor' => '#FF7F00' ),
			array( 'color' => '66 66 111',   'hexcolor' => '#42426F' ),
			array( 'color' => '92 64 51',    'hexcolor' => '#5C4033' ),
			array( 'color' => '47 79 47',    'hexcolor' => '#2F4F2F' ),
			array( 'color' => '74 118 110',  'hexcolor' => '#4A766E' ),
			array( 'color' => '79 79 47',    'hexcolor' => '#4F4F2F' ),
			array( 'color' => '153 50 205',  'hexcolor' => '#9932CD' ),
			array( 'color' => '135 31 120',  'hexcolor' => '#871F78' ),
			array( 'color' => '107 35 142',  'hexcolor' => '#6B238E' ),
			array( 'color' => '47 79 79',    'hexcolor' => '#2F4F4F' ),
			array( 'color' => '151 105 79',  'hexcolor' => '#97694F' ),
			array( 'color' => '112 147 219', 'hexcolor' => '#7093DB' ),
			array( 'color' => '133 94 66',   'hexcolor' => '#855E42' ),
			array( 'color' => '84 84 84',    'hexcolor' => '#545454' ),
			array( 'color' => '133 99 99',   'hexcolor' => '#856363' ),
			array( 'color' => '209 146 117', 'hexcolor' => '#D19275' ),
			array( 'color' => '142 35 35',   'hexcolor' => '#8E2323' ),
			array( 'color' => '35 142 35',   'hexcolor' => '#238E23' ),
			array( 'color' => '205 127 50',  'hexcolor' => '#CD7F32' ),
			array( 'color' => '219 219 112', 'hexcolor' => '#DBDB70' ),
			array( 'color' => '192 192 192', 'hexcolor' => '#C0C0C0' ),
			array( 'color' => '82 127 118',  'hexcolor' => '#527F76' ),
			array( 'color' => '147 219 112', 'hexcolor' => '#93DB70' ),
			array( 'color' => '33 94 33',    'hexcolor' => '#215E21' ),
			array( 'color' => '78 47 47',    'hexcolor' => '#4E2F2F' ),
			array( 'color' => '159 159 95',  'hexcolor' => '#9F9F5F' ),
			array( 'color' => '192 217 217', 'hexcolor' => '#C0D9D9' ),
			array( 'color' => '168 168 168', 'hexcolor' => '#A8A8A8' ),
			array( 'color' => '143 143 189', 'hexcolor' => '#8F8FBD' ),
			array( 'color' => '233 194 166', 'hexcolor' => '#E9C2A6' ),
			array( 'color' => '50 205 50',   'hexcolor' => '#32CD32' ),
			array( 'color' => '228 120 51',  'hexcolor' => '#E47833' ),
			array( 'color' => '142 35 107',  'hexcolor' => '#8E236B' ),
			array( 'color' => '50 205 153',  'hexcolor' => '#32CD99' ),
			array( 'color' => '50 50 205',   'hexcolor' => '#3232CD' ),
			array( 'color' => '107 142 35',  'hexcolor' => '#6B8E23' ),
			array( 'color' => '234 234 174', 'hexcolor' => '#EAEAAE' ),
			array( 'color' => '147 112 219', 'hexcolor' => '#9370DB' ),
			array( 'color' => '66 111 66',   'hexcolor' => '#426F42' ),
			array( 'color' => '127 0 255',   'hexcolor' => '#7F00FF' ),
			array( 'color' => '127 255 0',   'hexcolor' => '#7FFF00' ),
			array( 'color' => '112 219 219', 'hexcolor' => '#70DBDB' ),
			array( 'color' => '219 112 147', 'hexcolor' => '#DB7093' ),
			array( 'color' => '166 128 100', 'hexcolor' => '#A68064' ),
			array( 'color' => '47 47 79',    'hexcolor' => '#2F2F4F' ),
			array( 'color' => '35 35 142',   'hexcolor' => '#23238E' ),
			array( 'color' => '77 77 255',   'hexcolor' => '#4D4DFF' ),
			array( 'color' => '255 110 199', 'hexcolor' => '#FF6EC7' ),
			array( 'color' => '0 0 156',     'hexcolor' => '#00009C' ),
			array( 'color' => '235 199 158', 'hexcolor' => '#EBC79E' ),
			array( 'color' => '207 181 59',  'hexcolor' => '#CFB53B' ),
			array( 'color' => '255 127 0',   'hexcolor' => '#FF7F00' ),
			array( 'color' => '255 36 0',    'hexcolor' => '#FF2400' ),
			array( 'color' => '219 112 219', 'hexcolor' => '#DB70DB' ),
			array( 'color' => '143 188 143', 'hexcolor' => '#8FBC8F' ),
			array( 'color' => '188 143 143', 'hexcolor' => '#BC8F8F' ),
			array( 'color' => '234 173 234', 'hexcolor' => '#EAADEA' ),
			array( 'color' => '217 217 243', 'hexcolor' => '#D9D9F3' ),
			array( 'color' => '89 89 171',   'hexcolor' => '#5959AB' ),
			array( 'color' => '111 66 66',   'hexcolor' => '#6F4242' ),
			array( 'color' => '188 23 23',   'hexcolor' => '#BC1717' ),
			array( 'color' => '35 142 104',  'hexcolor' => '#238E68' ),
			array( 'color' => '107 66 38',   'hexcolor' => '#6B4226' ),
			array( 'color' => '142 107 35',  'hexcolor' => '#8E6B23' ),
			array( 'color' => '230 232 250', 'hexcolor' => '#E6E8FA' ),
			array( 'color' => '50 153 204',  'hexcolor' => '#3299CC' ),
			array( 'color' => '0 127 255',   'hexcolor' => '#007FFF' ),
			array( 'color' => '255 28 174',  'hexcolor' => '#FF1CAE' ),
			array( 'color' => '0 255 127',   'hexcolor' => '#00FF7F' ),
			array( 'color' => '35 107 142',  'hexcolor' => '#236B8E' ),
			array( 'color' => '56 176 222',  'hexcolor' => '#38B0DE' ),
		);
	}
}

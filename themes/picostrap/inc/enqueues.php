<?php

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

//ADD THE CSS BUNDLE
function picostrap_enqueue_styles() {
    
    ////// IF  RECOMPILED STYLE IS PRESENT, DISABLE THE ORDINARY STYLE AND ENQUEUE THE RECOMPILED ///
    $compiled_style_url=picostrap_get_compiled_css_url();
    //die($compiled_style_url);
    if($compiled_style_url) {
        wp_enqueue_style( 'picostrap-styles',  $compiled_style_url);
    } else {
        wp_enqueue_style( 'picostrap-styles',  "https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css");
    }


}
add_action( 'wp_enqueue_scripts', 'picostrap_enqueue_styles' );

  

//ADD THE CUSTOM HEADER CODE (SET IN CUSTOMIZER)
add_action( 'wp_head', 'picostrap_add_header_code' );
function picostrap_add_header_code() {
      if(! get_theme_mod("picostrap_fonts_header_code_disable")) echo get_theme_mod("picostrap_fonts_header_code")." ";
	  echo get_theme_mod("picostrap_header_code");
}

//ADD THE CUSTOM FOOTER CODE (SET IN CUSTOMIZER)
add_action( 'wp_footer', 'picostrap_add_footer_code' );
function picostrap_add_footer_code() {
	  //if (!current_user_can('administrator'))
      echo get_theme_mod("picostrap_footer_code");
}

//ADD THE CUSTOM CHROME COLOR TAG (SET IN CUSTOMIZER)
add_action( 'wp_head', 'picostrap_add_header_chrome_color' );
function picostrap_add_header_chrome_color() {
	 if (get_theme_mod('picostrap_header_chrome_color')!=""):
        ?><meta name="theme-color" content="<?php echo get_theme_mod('picostrap_header_chrome_color'); ?>" />
	<?php endif;
}

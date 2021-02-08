<?php
/*
SCSS Compiler interface 
*/

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

//CHECK URL PARAMETERS AND REACT ACCORDINGLY
add_action("admin_init", function (){
	
	if (!current_user_can("administrator")) return; //ADMINS ONLY
	
	if (isset($_GET['ps_compile_scss'])) {		picostrap_generate_css();		die();	}
	if (isset($_GET['ps_reset_theme'])) {		remove_theme_mods(); 	wp_die("Theme Options Reset!");	}
	if(isset($_GET['ps_show_mods'])){		print_r(get_theme_mods());		wp_die();	}
});

//SET UPLOAD PATH
if (isset($_GET['ps_compile_scss'])) add_filter( 'pre_option_uploads_use_yearmonth_folders', '__return_true'); //so standard folder structure is used for uploads, important for #fix_paths

// USE LEAFO's SCSSPHP LIBRARY
use ScssPhp\ScssPhp\Compiler; //https://scssphp.github.io/scssphp/docs/

//ALLOW UPLOADING CSS FILES
add_filter('upload_mimes', 'picostrap_enable_extended_upload_css'); function picostrap_enable_extended_upload_css ( $mime_types =array() ) {  $mime_types['css']  = 'text/css'; return $mime_types;}

//SOME UTILITIES
function picostrap_get_upload_dir( $param, $subfolder = '' ) {    $upload_dir = wp_upload_dir();    $url = $upload_dir[ $param ];    if ( $param === 'baseurl' && is_ssl() )  $url = str_replace( 'http://', 'https://', $url );return $url . $subfolder; }
function picostrap_get_active_parent_theme_slug(){ $style_parent_theme = wp_get_theme(get_template()); $theme_name = $style_parent_theme->get('Name'); return sanitize_title($theme_name);}
function picostrap_get_active_theme_slug(){return get_stylesheet();}

function picostrap_get_compiled_css_url() { 
	$css_bundle_relative_upload_path=get_theme_mod('picostrap_css_bundle_wp_relative_upload_path');
	if ($css_bundle_relative_upload_path!='')  return picostrap_get_upload_dir('baseurl') .'/'.$css_bundle_relative_upload_path;	 else return FALSE;
}

/////FUNCTION TO GET ACTIVE SCSS CODE FROM FILE ///////
function picostrap_get_active_scss_code(){
	
	//GRAB SCSS MAIN FILE//////////////////
	$response = wp_remote_get( WP_CONTENT_URL.'/themes/'.picostrap_get_active_theme_slug().'/sass/main.scss' ); //more modern way
 
	if ( is_array( $response ) && ! is_wp_error( $response ) && wp_remote_retrieve_response_code( $response )==200 ) {
		$the_scss_code = $response['body']; // use the content
	} else {
		//fallback alternative 
		echo (" <h4>  Using file_get_contents instead of wp_remote_get</h4>");
		$the_scss_code = file_get_contents('../wp-content/themes/'.picostrap_get_active_theme_slug().'/sass/main.scss');
	}

	//FOR STYLE PACKAGES
	if(function_exists("picostrap_alter_scss")) $the_scss_code = picostrap_alter_scss ($the_scss_code);	 
	
	return $the_scss_code;
}

 
/////FUNCTION TO RECOMPILE THE CSS ///////
function picostrap_generate_css(){
	
	//INITIALIZE COMPILER
	require_once "scssphp/scss.inc.php";
	$scss = new Compiler();
	
	try {
		//SET IMPORT PATH: CURRENTLY ACTIVE THEME's SASS FOLDER
		$scss->setImportPaths(WP_CONTENT_DIR.'/themes/'.picostrap_get_active_theme_slug().'/sass/');

		//IF USING A CHILD THEME, add parent theme sass folder: picostrap
		if (is_child_theme()) $scss->addImportPath(WP_CONTENT_DIR.'/themes/'.picostrap_get_active_parent_theme_slug().'/sass/');
		
		//add extra path for style packages
		if(function_exists("picostrap_add_scss_import_path")) $scss->addImportPath(picostrap_add_scss_import_path());
		
		//SET OUTPUT FORMATTING
		$scss->setFormatter('ScssPhp\ScssPhp\Formatter\Crunched');
		
		// ENABLE SOURCE MAP
		//$scss->setSourceMap(Compiler::SOURCE_MAP_INLINE);
		
		//SET SCSS VARIABLES
		$scss->setVariables(picostrap_get_active_scss_variables_array());
		
		$compiled_css = $scss->compile(picostrap_get_active_scss_code());
		
		//echo "COMPILED:". ($compiled_css);die; //FOR DEBUG
	
	} catch (Exception $e) {
		
		echo  "<div id='compile-error' style='font-size:20px;background:#212337;color:lime;font-family:courier;border:8px solid red;padding:15px;display:block'><h1>SCSS error</h2>".$e->getMessage()."</div>";
		die();
   	}
	
	//CHECK
	if ($compiled_css=="") die("Compiled CSS is empty, aborting.");
	
	//DELETE OLD CSS FILE
	$css_bundle_relative_upload_path=get_theme_mod('picostrap_css_bundle_wp_relative_upload_path');
	if($css_bundle_relative_upload_path!='')  unlink( WP_CONTENT_DIR.'/uploads/'.$css_bundle_relative_upload_path);
	
	
	//SAVE THE COMPILED CSS FILE
	$uploaded= wp_upload_bits( "styles-bundle-".rand(1,1024).".css", FALSE, $compiled_css); //maybe here add a version?
	
	if ($uploaded['error']==FALSE) {
		//UPLOAD WAS SUCCESSFUL
		set_theme_mod('picostrap_css_bundle_wp_relative_upload_path',_wp_relative_upload_path( $uploaded['file'] ));
		set_theme_mod("picostrap_scss_last_filesmod_timestamp",picostrap_scss_last_filesmod_timestamp());

		//GIVE POSITIVE FEEDBACK
		if (isset($_GET['ps_compiler_api'])) {
			echo "New CSS bundle: " . picostrap_get_compiled_css_url();
		}
		else {
			echo "<br><br><b>Generated File:</b><br><a target='new' id='recompiled-successfully' href='".picostrap_get_compiled_css_url()."'>".picostrap_get_compiled_css_url()."</a>";
			echo "<br><br><b>Size: </b><br>".round(mb_strlen($compiled_css, '8bit')/1000)." kB - ".round(mb_strlen(gzcompress($compiled_css), '8bit')/1000)." kB gzipped";
		}
	
	 } else {
		//GIVE NEGATIVE FEEDBACK
		echo  "<br><br ><span id='saving-error'>Error saving CSS file to your uploads directory. ".$uploaded['error']."</span>";
	}
	
	//PRINT A CLOSE BUTTON
	if (!isset($_GET['ps_compiler_api'])) echo  " <button class='cs-close-compiling-window'>Close window</button>";
}



/////FUNCTION TO GET VARIABLES USED IN CUSTOMIZER /////
function picostrap_get_active_scss_variables_array(){
	$output_array=array();
	foreach(get_theme_mods() as $theme_mod_name => $theme_mod_value):
		
		//check we are treating a scss variable, or skip
		if(substr($theme_mod_name,0,8) != "SCSSvar_") continue;
		
		//skip empty values, unless checkboxes that default to true
		if($theme_mod_value=="" && $theme_mod_name!='SCSSvar_enable-rounded') continue;
		
		$variable_name=str_replace("SCSSvar_","$",$theme_mod_name);
		
		//add to output array
		$output_array[$variable_name] = $theme_mod_value;
		
	endforeach;

	return $output_array; 
}


/// TODO /// Upon theme activate, compila da backend

// FORCE CSS REBUILD UPON ENABLING CHILD THEME. deve esse triggertata cambiando anche da pico a un child
add_action( 'after_switch_theme', 'picostrap_force_css_rebuild', 10, 2 ); 
function picostrap_force_css_rebuild() { 
    set_theme_mod("picostrap_css_bundle_wp_relative_upload_path",0);
    set_theme_mod("picostrap_scss_last_filesmod_timestamp",0);
    
}

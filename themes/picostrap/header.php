<?php

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// USEFUL LINKS for hackers
// https://medium.com/wdstack/bootstrap-4-custom-navbar-1f6a2da5ed3c
// https://medium.com/coder-grrl/the-guide-to-customising-the-bootstrap-4-navbar-i-wish-id-had-6-months-ago-7bc6ce0e3c71

?><!doctype html>
<html <?php language_attributes(); ?>>
  <head>
    <!-- Required meta tags -->
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- wp_head begin -->
    <?php wp_head(); ?>
    <!-- wp_head end -->
  </head>
  <body <?php body_class(); ?> >
  <?php wp_body_open(); ?>

  <?php if(function_exists('lc_custom_header')) lc_custom_header(); else {
    //STANDARD NAV
    ?>
  
  <?php if (get_theme_mod("enable_topbar") ) : ?>
	<!-- ******************* The Topbar Area ******************* -->
	<div id="wrapper-topbar" class="py-2 <?php echo get_theme_mod('topbar_bg_color_choice','bg-light') ?> <?php echo get_theme_mod('topbar_text_color_choice','text-dark') ?>">
		<div class="container">
			<div class="row">
				<div id="topbar-content" class="col-md-12 text-left text-center text-md-left small"> <?php echo do_shortcode(get_theme_mod('topbar_content')) ?>	</div>
			</div>
		</div>
	</div>
	<?php endif; ?>
  

  <!-- ******************* The Navbar Area ******************* -->
	<div id="wrapper-navbar" itemscope itemtype="http://schema.org/WebSite">

    <a class="skip-link sr-only sr-only-focusable" href="#content"><?php esc_html_e( 'Skip to content', 'picostrap' ); ?></a>

    <nav class="navbar navbar-expand-lg <?php echo get_theme_mod('picostrap_header_navbar_position')." ". get_theme_mod('picostrap_header_navbar_color_scheme','navbar-dark').' '. get_theme_mod('picostrap_header_navbar_color_choice','bg-dark'); ?>">

     
      <div class="container" >
  
          <div id="logo-tagline-wrap">
            <!-- Your site title as branding in the menu -->
            <?php if ( ! has_custom_logo() ) { ?>

              <?php if ( is_front_page() && is_home() ) : ?>

                <div class="navbar-brand mb-0 h3"><a rel="home" href="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" itemprop="url"><?php bloginfo( 'name' ); ?></a></div>

              <?php else : ?>

                <a class="navbar-brand h3" rel="home" href="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" itemprop="url"><?php bloginfo( 'name' ); ?></a>

              <?php endif; ?>


            <?php } else {
              the_custom_logo();
            } ?><!-- end custom logo -->

          
            <?php if (!get_theme_mod('header_disable_tagline')): ?><small id="top-description" class="text-muted d-none d-md-inline-block"><?php bloginfo("description") ?></small><?php endif ?>
          </div>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="<?php esc_attr_e( 'Toggle navigation', 'picostrap' ); ?>">
          <span class="navbar-toggler-icon"></span>
        </button>

        <!-- The WordPress Menu goes here -->
        <?php  wp_nav_menu( array(
              'theme_location'    => 'primary',
              'depth'             => 2,
              'container'         => 'div',
              'container_class'   => 'collapse navbar-collapse',
              'container_id'      => 'navbarNavDropdown',
              'menu_class'        => 'navbar-nav ml-auto',
              'menu_id'         => 'main-menu',
              'fallback_cb'       => 'WP_Bootstrap_Navwalker::fallback',
              'walker'            => new WP_Bootstrap_Navwalker(),
          ) ); ?>
      
      </div><!-- .container -->
     

    </nav><!-- .site-navigation -->

    </div><!-- #wrapper-navbar end -->

 
<?php } ?>

<main id='theme-main'>
<?php

function add_picostrap_theme_page() {
    add_theme_page( 'Picostrap Theme Options Page', 'Picostrap / SCSS', 'edit_theme_options', 'picostrap-theme-options', 'theme_option_page' );
}
add_action( 'admin_menu', 'add_picostrap_theme_page' );
 
function theme_option_page() {
    ?>
<div class="wrap">
<h1>Picostrap: the power of SASS and Bootstrap at your fingertips</h1>
<p>The main idea behind this theme is to empower you to brew your own Bootstrap CSS easily: using the WordPress Customizer, you can totally control site typography, colors, spacings.
Hit "Publish" and it will recompile the Bootstrap SCSS code "on the fly". </p>
<p>A single, minified CSS file will be saved, containing all the "bundle". While this can reach sizes around 200k, the fact that it will be probably served zipped by your server stack, makes it's size totally negligible.
This is the only CSS your site should need, at least until you don't add bloating plugins.
</p>


<h2>How to add your own CSS / SCSS code and use Livereload to simplify your developer life</h2>
<p>To add your own CSS code, first of all, you need to use one of our child themes (get one from the picostrap site). </p>
<p>You can freely edit the _custom.scss file inside your child theme folder.

HOW DOES IT WORK?

<p>Edit this file, save, view page as admin:<br>

the style will be AUTOMATICALLY recompiled and 
your new CSS bundle will be reloaded via ajax to show 
the new styling edits.
</p>

<h1>Tools and utilities you should NOT need / use typically</h1>

<a target="_blank" href="<?php echo admin_url('?ps_compile_scss') ?>">Rebuild CSS Bundle</a>
    <?php
}
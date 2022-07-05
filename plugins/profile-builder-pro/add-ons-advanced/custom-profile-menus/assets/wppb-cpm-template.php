<?php

// Template used for forms opened in this plugin iFrame

echo '<html>';
	echo '<head>';
		wp_head();

		echo '<style> html { margin-top: 0 !important; } body:before { display: none !important; } </style>';
	echo '</head>';

	echo '<body class="wppb-cpm-template-body"><div class="site" id="page">';
		if( is_user_logged_in() ) {
			echo '<div class="wppb-cpm-logged-in" style="display: none"></div>';
		}

		echo '<div class="wppb-cpm-iframe-content entry-content">';
			if( have_posts() ) {
				while( have_posts() ) {
					the_post();
					the_content();
				}
			}
		echo '<div>';
	echo '</div></body>';

	wp_footer();
echo '<html>';
<?php
/**
 * Make it compatible with the Twenty Twenty-Two theme.
 *
 * Props Google and Steveorevo.
 * https://wordpress.org/support/topic/blank-topic-pages-on-bbpress-while-using-twenty-twenty-two-theme/#post-15612896
 */
add_filter( 'template_include', function( $template ) {
	if ( false !== strpos( $template, 'twentytwentytwo/index.php' ) ) {
		$template = ABSPATH . WPINC . '/template-canvas.php';
	}
	return $template;
} );

<?php
/*
Plugin Name: Comments as bbPress
Description: Replaces the comments section of a regular post with a bbPress subforum
*/

function bbp_subforum_replacer_custom_comments_template() {
	global $post;
	// Check if the current post has an associated bbPress subforum
	$subforum_id = get_post_meta( $post->ID, 'bbp_subforum_id', true );
	if ( empty( $subforum_id ) ) {
		$subforum_id = bbp_insert_forum( array(
			'post_title' => sprintf( '<a href="%s">%s</a>', esc_url( get_permalink( $post->ID ) ), $post->post_title ),
			'post_content' => get_post_embed_html( 600, 400 ),
			'post_status' => 'publish',
		) );
		update_post_meta( $post->ID, 'bbp_subforum_id', $subforum_id );
		update_post_meta( $subforum_id, 'bbp_subforum__post_id', $post->ID );
	}
	add_filter( 'bbp_get_forum_id', function() use ( $subforum_id ) {
		return $subforum_id;
	} );
	$post = get_post( $subforum_id, OBJECT );
	setup_postdata( $post );
	bbp_set_query_name( 'bbp_single_forum' );

	// Register a custom template directory with bbPress only if we are going to render the comments.
	function comments_as_bbpress_template_stack( $stack ) {
		array_unshift( $stack, plugin_dir_path( __FILE__ ) . 'templates/' );
		return $stack;
	}
	add_filter( 'bbp_get_template_stack', 'comments_as_bbpress_template_stack' );

	// Load a custom template for the loop-single-topic template part
	function my_custom_bbpress_template_part( $templates, $slug, $name ) {
		if ( $slug === 'loop' && $name === 'single-topic' ) {
			$templates = array( 'comments-as-bbpress-loop-single-topic.php' );
		}
		return $templates;
	}
	add_filter( 'bbp_get_template_part', 'my_custom_bbpress_template_part', 10, 3 );

	return dirname(__FILE__) . '/templates/custom-comments.php';
}

add_filter( 'comments_template', 'bbp_subforum_replacer_custom_comments_template' );

/**
 * Make it compatible with twentytwentytwo
 * props Google and Steveorevo https://wordpress.org/support/topic/blank-topic-pages-on-bbpress-while-using-twenty-twenty-two-theme/#post-15612896
 */
add_filter( 'template_include', function( $template ) {
	if ( false !== strpos($template, 'twentytwentytwo/index.php') ) {
		$template = ABSPATH . WPINC . '/template-canvas.php';
	}
	return $template;
});

<?php
/**
 * Plugin Name: Comments as bbPress
 * Description: Replaces the comments section of a regular post with a bbPress subforum.
 */

function comments_as_bbpress_replace_comments_template() {
	// Get the current post.
	global $post;

	// Check if the current post has an associated bbPress subforum.
	$subforum_id = get_post_meta( $post->ID, 'bbp_subforum_id', true );
	if ( empty( $subforum_id ) ) {
		// Create a new bbPress subforum.
		$subforum_id = bbp_insert_forum( array(
			'post_title'   => sprintf( '<a href="%s">%s</a>', esc_url( get_permalink( $post->ID ) ), $post->post_title ),
			'post_content' => get_post_embed_html( 600, 400 ),
			'post_status'  => 'publish',
		) );
		// Save the bbPress subforum ID as post meta.
		update_post_meta( $post->ID, 'bbp_subforum_id', $subforum_id );
		update_post_meta( $subforum_id, 'bbp_subforum__post_id', $post->ID );
	}

	// Replace the comments section with the bbPress subforum.
	add_filter( 'bbp_get_forum_id', function () use ( $subforum_id ) {
		return $subforum_id;
	} );
	$post = get_post( $subforum_id, OBJECT );
	setup_postdata( $post );
	bbp_set_query_name( 'bbp_single_forum' );

	// Register a custom template directory with bbPress.
	add_filter( 'bbp_get_template_stack', function ( $stack ) {
		array_unshift( $stack, plugin_dir_path( __FILE__ ) . 'templates/' );

		return $stack;
	} );

	// Load a custom template for the loop-single-topic template part.
	add_filter( 'bbp_get_template_part', function ( $templates, $slug, $name ) {
		if ( $slug === 'loop' && $name === 'single-topic' ) {
			$templates = array( 'comments-as-bbpress-loop-single-topic.php' );
		}

		return $templates;
	}, 10, 3 );

	// Return the path to the custom comment's template.
	return dirname( __FILE__ ) . '/templates/custom-comments.php';
}

add_filter( 'comments_template', 'comments_as_bbpress_replace_comments_template' );

/**
 * Make the plugin compatible with the Twenty Twenty-Two theme.
 */
add_filter( 'template_include', function ( $template ) {
	if ( false !== strpos( $template, 'twentytwentytwo/index.php' ) ) {
		$template = ABSPATH . WPINC . '/template-canvas.php';
	}

	return $template;
} );

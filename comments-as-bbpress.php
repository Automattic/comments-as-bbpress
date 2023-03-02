<?php
/**
 * Plugin Name: Comments as bbPress
 * Description: Replaces the comments section of a regular post with a bbPress forum.
 * Version:     1.0.0
 * Author:      Automattic
 * Text Domain: comments-as-bbpress
 *
 * @package Automattic\CommentsAsbbPress
 */

namespace Automattic\CommentsAsbbPress;

/**
 * Replace the comments section with a forum in the single post view.
 */
function comments_template() {
	global $post;
	// Check if the current post has an associated bbPress forum.
	$forum_id = get_post_meta( $post->ID, 'bbp_forum_id', true );
	if ( empty( $forum_id ) ) {
		$forum_id = bbp_insert_forum( array(
			'post_title'   => sprintf( '<a href="%s">%s</a>', esc_url( get_permalink( $post->ID ) ), $post->post_title ),
			'post_content' => get_post_embed_html( 600, 400 ),
			'post_status'  => 'publish',
		) );
		update_post_meta( $post->ID, 'bbp_forum_id', $forum_id );
		// This isn't used yet but could be useful in the future.
		update_post_meta( $forum_id, 'bbp_forum_post_id', $post->ID );
	}
	add_filter( 'bbp_get_forum_id', function() use ( $forum_id ) {
		return $forum_id;
	} );
	$post = get_post( $forum_id );
	setup_postdata( $post );
	bbp_set_query_name( 'bbp_single_forum' );

	// Register a custom template directory with bbPress.
	function template_stack( $stack ) {
		array_unshift( $stack, plugin_dir_path( __FILE__ ) . 'templates/' );
		return $stack;
	}
	add_filter( 'bbp_get_template_stack', '\Automattic\CommentsAsbbPress\template_stack' );

	// Load a custom template for the loop-single-topic template part.
	function custom_template_part( $templates, $slug, $name ) {
		if ( $slug === 'loop' && $name === 'single-topic' ) {
			$templates = array( 'comments-as-bbpress-loop-single-topic.php' );
		}
		return $templates;
	}
	add_filter( 'bbp_get_template_part', '\Automattic\CommentsAsbbPress\custom_template_part', 10, 3 );

	return dirname( __FILE__ ) . '/templates/custom-comments.php';
}
add_filter( 'comments_template', '\Automattic\CommentsAsbbPress\comments_template' );

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

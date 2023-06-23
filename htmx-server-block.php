<?php
/**
 * Plugin Name:       Htmx Server Block
 * Description:       Example block scaffolded with Create Block tool.
 * Requires at least: 6.1
 * Requires PHP:      7.0
 * Version:           0.1.0
 * Author:            The WordPress Contributors
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       htmx-server-block
 *
 * @package           create-block
 */

/**
 * Registers the block using the metadata loaded from the `block.json` file.
 * Behind the scenes, it registers also all assets so they can be enqueued
 * through the block editor in the corresponding context.
 *
 * @see https://developer.wordpress.org/reference/functions/register_block_type/
 */
function create_block_htmx_server_block_block_init() {
	$block_args = [
		'render_callback' => function () {
			ob_start();
			load_template( __DIR__ . '/templates/random_posts.php' );

			return ob_get_clean();
		},
	];
	register_block_type( __DIR__ . '/build', $block_args );
}

add_action( 'init', 'create_block_htmx_server_block_block_init' );

add_filter( 'htmx.template_paths', static function ( $paths ) {
	$paths[] = __DIR__ . '/templates';

	return $paths;
} );

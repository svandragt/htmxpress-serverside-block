<?php
$post_args = [
	'posts_per_page' => 3,
	'orderby' => 'rand',
	'post_status' => 'publish',
	'post_type' => 'post',
];
$query = new WP_Query( $post_args );

if ( $query->have_posts() ) :
	echo "<div  id='random-posts'>";
	while ( $query->have_posts() ) : $query->the_post();

		echo "<div style='border:2px groove black; margin-bottom:5px;'><h3>";
		the_title();
		echo "</h3>";
		the_excerpt();
		echo "</div>";

	endwhile;

	echo '<button hx-post="/htmx/random_posts" hx-target="#random-posts">
                More
            </button>';
	echo "</div>";

	wp_reset_postdata();
endif;

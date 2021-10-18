<?php
/**
 * @package storefront
 */

get_header();

add_filter( 'body_class', 'custom_class' );
function custom_class( $classes ) {
    if ( is_page_template( 'archive-pokedex.php' ) ) {
        $classes[] = 'archive-template.php';
    }
    return $classes;
}

?>

<main id="main" class="site-main md:th-px-4xl th-px-md th-w-full th-stack--2xl">

	<?php
	if ( have_posts() ) {

		get_template_part( 'template-parts/global/header' );



		while ( have_posts() ) :
			the_post();

			get_template_part( 'template-parts/global/content', 'search' );

		endwhile;



		the_posts_pagination( array( 'mid_size' => 2 ) );

	} else {

		get_template_part( 'template-parts/global/content', 'none' );

	}
	?>

</main><!-- #main -->

<?php
//get_sidebar();
get_footer();

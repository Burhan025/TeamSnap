<?php
/*
YARPP Template: Business Resource Other
Description: This template returns the related posts as thumbnails in an ordered list. Requires a theme which supports post thumbnails.
Author: YARPP Team
*/
?>

<?php
/*
Templating in YARPP enables developers to uber-customize their YARPP display using PHP and template tags.

The tags we use in YARPP templates are the same as the template tags used in any WordPress template. In fact, any WordPress template tag will work in the YARPP Loop. You can use these template tags to display the excerpt, the post date, the comment count, or even some custom metadata. In addition, template tags from other plugins will also work.

If you've ever had to tweak or build a WordPress theme before, you’ll immediately feel at home.

// Special template tags which only work within a YARPP Loop:

1. the_score()      // this will print the YARPP match score of that particular related post
2. get_the_score()      // or return the YARPP match score of that particular related post

Notes:
1. If you would like Pinterest not to save an image, add `data-pin-nopin="true"` to the img tag.

*/
?>

<?php
/* Pick Thumbnail */
global $_wp_additional_image_sizes;
if ( isset( $_wp_additional_image_sizes['yarpp-thumbnail'] ) ) {
	$dimensions['size'] = 'yarpp-thumbnail';
} else {
	$dimensions['size'] = 'medium'; // default
}
?>

<h3>Similar Articles:</h3>
<?php if ( have_posts() ) : ?>
<ul class="masonry-grid masonry-grid__thirds">
	<?php
	while ( have_posts() ) :
		the_post();
		?>
		<li class="masonry-grid--block sal-animate" data-sal="slide-down" data-sal-easing="ease-out-bounce" data-sal-delay="100">
			<a class="masonry-grid--block__link" href="<?php the_permalink(); ?>" rel="bookmark norewrite" title="<?php the_title_attribute(); ?>">
				<div class="masonry--block__content">
					<div class="masonry--block__content-padding masonry--block__less-padding">
						<h5 class="title"><?php the_title(); ?></h5>
						<?php the_excerpt(); ?>
					</div>
					<p class="masonry--block__read-more masonry--block__less-padding text--blue"><span class="text--blue">Read More</span> <svg id="Regular" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="Icon ml5 text--blue"><defs><style>.cls-1,.cls-2{fill:none;stroke:currentColor;stroke-linecap:round;stroke-linejoin:round;stroke-width:1.5px;}.cls-2{fill-rule:evenodd;}</style></defs><title>arrow-circle-right</title><circle class="cls-1" cx="12" cy="12" r="11.25"></circle><path class="cls-2" d="M9,17.25l7.256-4.617a.751.751,0,0,0,0-1.266L9,6.75"></path></svg></p>
				</div>
			</a>
		</li>
	<?php endwhile; ?>
</ul>

<?php else : ?>
<p>No related photos.</p>
<?php endif; ?>
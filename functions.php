<?php

// Defines
define('FS_CHILD_THEME_DIR', get_stylesheet_directory());
define('FS_CHILD_THEME_URL', get_stylesheet_directory_uri());


// Classes
require_once 'classes/class-freshy-bb.php';

// Actions
add_action('wp_enqueue_scripts', 'FreshyBB::enqueue_scripts', 1000);


add_filter('fl_theme_system_fonts', 'FreshyBB::custom_fonts');
add_filter('fl_builder_font_families_system', 'FreshyBB::custom_fonts');

function set_image_alt_tags_and_title($post_ID)
{
    // Check if uploaded file is an image
    $post_type = get_post_type($post_ID);
    if ($post_type != 'attachment') {
        return;
    }

    // Get the uploaded image file name
    $image_path = get_attached_file($post_ID);
    $image_filename = basename($image_path);

    // Remove the extension and replace hyphens with spaces
    $text = pathinfo($image_filename, PATHINFO_FILENAME);
    $text = str_replace('-', ' ', $text);

    // Update the alt text
    update_post_meta($post_ID, '_wp_attachment_image_alt', $text);

    // Update the title
    $post_data = array(
        'ID' => $post_ID,
        'post_title' => $text
    );
    wp_update_post($post_data);
}
add_action('add_attachment', 'set_image_alt_tags_and_title');

function get_news_link_shortcode($atts)
{
    // Use get_field() function from ACF to get the value of 'news_link' field
    $news_link = get_field('news_link');

    // Check if the field is not empty
    if (!empty($news_link)) {
        // Return the URL
        return '<a href="' . esc_url($news_link) . '" title="Read More" target="_blank">Read More</a>';
    }

    // Return nothing if the field is empty
    return '';
}
add_shortcode('news_link', 'get_news_link_shortcode');

function custom_rewrite_rule()
{
    add_rewrite_rule(
        '^skills-drills/(.+)/([^/]+)/?$',
        'index.php?post_type=skills-drills&name=$matches[2]',
        'top'
    );
}
// add_action('init', 'custom_rewrite_rule', 10, 0);

function custom_post_type_link($post_link, $post)
{
    if ($post->post_type == 'coaches-corner') {
        if ($terms = get_the_terms($post->ID, 'resource-type')) {
            // Get an array of all parent/ancestor terms
            $parents = array();
            foreach ($terms as $term) {
                if ($term->parent != 0) {
                    $ancestors = get_ancestors($term->term_id, 'resource-type');
                    $ancestors = array_reverse($ancestors); // Reverse to get the correct order (parent -> child)
                    foreach ($ancestors as $ancestor) {
                        $ancestor_term = get_term($ancestor, 'resource-type');
                        // if (!in_array($ancestor_term->slug, $parents)) 
                        $parents[] = $ancestor_term->slug;
                    }
                }
                //if (!in_array($term->slug, $parents)) 
                $parents[] = $term->slug; // Add the term slug itself
                break; // Assume only one category path per post
            }
            $category_path = implode('/', $parents);
            $post_link = str_replace('%resource-type%', $category_path, $post_link);
        }
    }
    if ($post->post_type == 'brands-blog') {
        if ($terms = get_the_terms($post->ID, 'brand-term')) {
            // Get an array of all parent/ancestor terms
            $parents = array();
            foreach ($terms as $term) {
                if ($term->parent != 0) {
                    $ancestors = get_ancestors($term->term_id, 'brand-term');
                    $ancestors = array_reverse($ancestors); // Reverse to get the correct order (parent -> child)
                    foreach ($ancestors as $ancestor) {
                        $ancestor_term = get_term($ancestor, 'brand-term');
                        // if (!in_array($ancestor_term->slug, $parents)) 
                        $parents[] = $ancestor_term->slug;
                    }
                }
                //if (!in_array($term->slug, $parents)) 
                $parents[] = $term->slug; // Add the term slug itself
                break; // Assume only one category path per post
            }
            $category_path = implode('/', $parents);
            $post_link = str_replace('%brand-term%', $category_path, $post_link);
        }
    }
    return $post_link;
}
add_filter('post_type_link', 'custom_post_type_link', 1, 2);


function display_skills_sports_taxonomy()
{
    global $post;

    // Fetch the terms associated with the custom taxonomy 'skill-sport' for the specified post
    $terms = wp_get_post_terms($post->ID, 'skill-sport', array('parent' => 0)); // Only get parent terms

    // Initialize the output variable
    $output = '';

    // Build the output with links
    foreach ($terms as $term) {
        // Get the term link
        $term_link = get_term_link($term);

        // Check if there was an error getting the term link
        if (is_wp_error($term_link)) {
            continue; // Skip this term if there's an error
        }

        // Append the term link to the output
        $output .= '<p class="skills-category"><a href="' . esc_url($term_link) . '">' . esc_html($term->name) . '</a></p>';
    }

    // Return the list of parent terms with links for display
    return $output;
}
add_shortcode('show_skills_sports', 'display_skills_sports_taxonomy');

function display_skill_sport_subnav()
{
    global $post;

    // Get terms in 'skill-sport' taxonomy for the current post
    $terms = wp_get_post_terms($post->ID, 'skill-sport');
    if (empty($terms) || is_wp_error($terms)) {
        return ''; // Return empty if no terms found or if there's an error
    }

    // Assuming the first term is the desired one (adjust logic as needed)
    $parent_term = $terms[0];

    // If the term has a parent, then it's not the top-level term we're looking for
    while ($parent_term->parent != 0) {
        $parent_term = get_term($parent_term->parent, 'skill-sport');
    }

    // Get child terms of the parent term
    $child_terms = get_terms('skill-sport', array('parent' => $parent_term->term_id, 'hide_empty' => false));

    // Start building the output
    $output = '<nav id="subnav">';
    $output .= '<h3 class="mt0 mb0 mr0 ml0">' . esc_html($parent_term->name) . '</h3><ul class="List--noBullets">';

    foreach ($child_terms as $term) {
        // Here, the count next to each term's name is its post count.
        // Adjust the URL structure and count as needed.
        $term_link = get_term_link($term);
        if (!is_wp_error($term_link)) {
            $output .= sprintf(
                '<li><a href="%s">%s</a><span class="badge pull-right">%d</span></li>',
                esc_url($term_link),
                esc_html($term->name),
                $term->count // Post count for the term. Adjust if using a different count.
            );
        }
    }

    $output .= '</ul></nav>';

    return $output;
}
add_shortcode('skill_sport_subnav', 'display_skill_sport_subnav');

function custom_business_resource_breadcrumb()
{
    global $post;

    // Starting the breadcrumb with the library link
    $breadcrumb = '<h6 class="feature__subnav-heading ms--_1__slab ContentHub--Breadcrumbs">';
    $breadcrumb .= '<a href="/for-business-resource-library/">All</a> / ';

    // Adding the custom taxonomy term link
    $terms = get_the_terms($post->ID, 'resource-type');
    if (!empty($terms) && !is_wp_error($terms)) {
        $term = array_shift($terms); // Get the first term
        $breadcrumb .= '<a href="/for-business-resource-library?_business_resource_type=' . strtolower(str_replace(' ', '-', $term->name)) . '">' . $term->name . '</a> / ';
        // Modified the above line of code to match the new filters
    }

    // Adding the current post link
    $breadcrumb .= '<a class="active" href="' . get_permalink() . '">' . get_the_title() . '</a>';

    // Closing the breadcrumb
    $breadcrumb .= '</h6>';

    return $breadcrumb;
}
add_shortcode('business_resource_breadcrumb', 'custom_business_resource_breadcrumb');

// Shortcode
// Usage: [business_resource_type_filter]
function custom_business_resource_type_filter()
{
    global $post;

    // Starting the business resource filter link
    $typeFilter = '<div class="business-resource-type-filter-active">';

    // Adding the custom taxonomy term link
    $terms = get_the_terms($post->ID, 'resource-type');
    if (!empty($terms) && !is_wp_error($terms)) {
        $term = array_shift($terms); // Get the first term
        $typeFilter .= '<a href="/for-business-resource-library?_business_resource_type=' . strtolower(str_replace(' ', '-', $term->name)) . '">' . $term->name . '</a>';
    }

    // Closing the business resource filter
    $typeFilter .= '</div>';

    return $typeFilter;
}
add_shortcode('business_resource_type_filter', 'custom_business_resource_type_filter');

// Modified shortcode
// Added a category attribute and ensured its script and style are loaded only when the Shortcode is run successfully.
// Usage: [business_resource_slides category="your-category-slug"]
function business_resource_slides_shortcode($atts)
{
    // Extract attributes and set default values
    $atts = shortcode_atts(
        array(
            'category' => 'business-resource', // Default category slug
        ),
        $atts,
        'business_resource_slides'
    );

    // Arguments for querying the slides CPT
    $args = array(
        'post_type' => 'slides',
        'posts_per_page' => -1, // Retrieve all slides
        'tax_query' => array(
            array(
                'taxonomy' => 'slide-category',
                'field'    => 'slug',
                'terms'    => $atts['category'],
            ),
        ),
    );

    // Query the slides
    $slides_query = new WP_Query($args);

    // Check if there are slides
    if ($slides_query->have_posts()) {
        // Enqueue Swiper CSS and JS
        wp_enqueue_style('swiper-css', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css');
        wp_enqueue_script('swiper-js', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js', array(), null, true);
    }

    // Start building the output HTML
    $output = '<div class="container-fluid br-slider CLCampaign--customers CLCampaign--customersNoContent">';
    $output .= '<div class="row">';

    // Continue building the output after slides loop
    $output .= '<div class="col-md-12 custom-slide-wrapper">
        <div class="swiper-container">
           
        <div class="swiper-wrapper">';

    // Initialize the featured image URL variable
    $featured_img_url = '/wp-content/uploads/vimeo-placeholder.png'; // Placeholder, will be replaced for each slide

    // Check if there are slides
    // Reset to loop through slides again for swiper content
    if ($slides_query->have_posts()) {
        while ($slides_query->have_posts()) {
            $slides_query->the_post();

            // Get the featured image URL for the current slide
            $featured_img_url = get_the_post_thumbnail_url(get_the_ID(), 'full');

            // Insert the container for the featured image of the current slide
            $output .= '<div id="customSlideAs" class="swiper-slide"><div data-sal="slide-right" data-sal-easing="ease-in" class="CLCampaign--customersImage" style="background-image: url(' . esc_url($featured_img_url) . ');"></div>';

            // Building the HTML structure with dynamic content for swiper slides
            $output .= '<blockquote class="text--white quote-content">'
                . get_the_content() .
                '<div class="swiper-buttons-wrapper">
                    <div class="swiper-buttons">
                        <div class="swiper-button-prev" tabindex="0" role="button" aria-label="Previous slide"></div>
                        <div class="swiper-button-next" tabindex="0" role="button" aria-label="Next slide"></div>
                    </div>
                 </div></blockquote></div><!-- end swiper-slide -->';
        }
    } else {
        // No slides found message
        $output .= '<p>No slides found in the ' . esc_html($atts['category']) . ' category.</p>';
    }

    // Close swiper-wrapper, swiper-container, and the other divs
    $output .= '<span class="swiper-notification" aria-live="assertive" aria-atomic="true"></span>
    </div></div></div></div></div>';

    // Reset post data
    wp_reset_postdata();

    // Return the final HTML
    return $output;
}
add_shortcode('business_resource_slides', 'business_resource_slides_shortcode');

function display_cat_subnav($atts)
{
    // Extracting shortcode attributes with a default taxonomy
    $attributes = shortcode_atts(array(
        'taxonomy' => 'tm-category', // Default taxonomy
    ), $atts);

    $taxonomy = $attributes['taxonomy'];

    // Get all terms in the specified taxonomy, including empty ones
    $terms = get_terms(array(
        'taxonomy' => $taxonomy,
        'hide_empty' => false, // Include terms with no posts
    ));

    if (empty($terms) || is_wp_error($terms)) {
        return ''; // Return empty if no terms found or if there's an error
    }

    // Start building the output
    $output = '<nav id="subnav"><h3 class="mt0 mb0 mr0 ml0">Categories</h3><ul class="List--noBullets">';

    foreach ($terms as $term) {
        // Here, the count next to each term's name is its post count.
        // Adjust the URL structure and count as needed.
        $term_link = get_term_link($term, $taxonomy); // Ensure the correct taxonomy is used for the link
        if (!is_wp_error($term_link)) {
            $output .= sprintf(
                '<li><a href="%s">%s</a><span class="badge pull-right">%d</span></li>',
                esc_url($term_link),
                esc_html($term->name),
                $term->count // Post count for the term. Adjust if using a different count.
            );
        }
    }

    $output .= '</ul></nav>';

    return $output;
}
add_shortcode('cat_subnav', 'display_cat_subnav');

function display_categories_masonry_shortcode($atts)
{
    // Shortcode attributes with defaults
    $attributes = shortcode_atts(array(
        'taxonomy' => 'tm-category', // Default taxonomy
    ), $atts);

    $taxonomy = $attributes['taxonomy'];

    $categories = get_terms(array(
        'taxonomy' => $taxonomy,
        'hide_empty' => false,
    ));

    if (empty($categories) || is_wp_error($categories)) {
        return 'No categories found.';
    }

    $output = '<ul class="masonry-grid masonry-grid__thirds">';
    $delay = 100; // Initial animation delay

    foreach ($categories as $category) {
        $featured_image = get_field('featured_image', $taxonomy . '_' . $category->term_id); // Adjust if necessary
        $category_link = get_term_link($category->term_id, $taxonomy);
        $category_name = $category->name;
        $article_count = $category->count;

        $output .= sprintf(
            '<li class="masonry-grid--block sal-animate" data-sal="slide-down" data-sal-easing="ease-out-bounce" data-sal-delay="%d">
                <a class="masonry-grid--block__link" href="%s">
                    <div class="masonry--block__content">
                        <div class="Article--imageWrapper">
                            <img loading="lazy" src="%s" alt="A preview image for the category: %s">
                        </div>
                        <div class="masonry--block__content-padding masonry--block__less-padding">
                            <h5 class="rank">%s</h5>
                            <h6 class="title smaller masonry-grid--block__article-title">Articles: <span class="badge masonry-grid--block__article-badge">%d</span></h6>
                        </div>
                        <p class="masonry--block__read-more masonry--block__less-padding">
                            <span class="text--blue">Read More</span>
                            <svg id="Regular" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="Icon ml5 text--blue"><defs><style>.cls-1,.cls-2{fill:none;stroke:currentColor;stroke-linecap:round;stroke-linejoin:round;stroke-width:1.5px;}.cls-2{fill-rule:evenodd;}</style></defs><title>arrow-circle-right</title><circle class="cls-1" cx="12" cy="12" r="11.25"></circle><path class="cls-2" d="M9,17.25l7.256-4.617a.751.751,0,0,0,0-1.266L9,6.75"></path></svg>
                        </p>
                    </div>
                </a>
            </li>',
            $delay,
            esc_url($category_link),
            esc_url($featured_image),
            esc_attr($category_name),
            esc_html($category_name),
            $article_count
        );

        $delay += 100; // Increment delay for each block
    }

    $output .= '</ul>';

    return $output;
}
add_shortcode('categories_masonry', 'display_categories_masonry_shortcode');

function display_cpt_posts_for_category_from_context()
{
    // Attempt to get the current queried object
    $queried_object = get_queried_object();

    // Ensure we have a queried object and it includes necessary properties
    if (!is_a($queried_object, 'WP_Term')) {
        return 'This shortcode works on taxonomy term archives only.';
    }

    // Extract the taxonomy and term slug from the queried object
    $taxonomy = $queried_object->taxonomy;
    $term_slug = $queried_object->slug;

    // Determine the post type(s) associated with the taxonomy
    $taxonomy_object = get_taxonomy($taxonomy);
    $post_types = $taxonomy_object->object_type;
    $post_type = !empty($post_types) ? $post_types[0] : 'post'; // Default to 'post' if uncertain

    // Query posts from the determined CPT and taxonomy term
    $args = array(
        'post_type' => $post_type,
        'posts_per_page' => -1,
        'tax_query' => array(
            array(
                'taxonomy' => $taxonomy,
                'field'    => 'slug',
                'terms'    => $term_slug,
            ),
        ),
    );
    $query = new WP_Query($args);

    if (!$query->have_posts()) {
        return 'No posts found.';
    }

    // Initialize output and delay
    $output = '<ul class="masonry-grid masonry-grid__thirds">';
    $delay = 100;

    while ($query->have_posts()) {
        $query->the_post();
        $image_url = get_the_post_thumbnail_url(get_the_ID(), 'full'); // Adjust image size as needed
        $excerpt_length = 12; // Adjust excerpt length as desired

        $output .= sprintf(
            '<li class="masonry-grid--block sal-animate" data-sal="slide-down" data-sal-easing="ease-out-bounce" data-sal-delay="%d">
				<a class="masonry-grid--block__link" href="%s">
					<div class="masonry--block__content">
						<div class="Article--imageWrapper">
							<img loading="lazy" src="%s" alt="%s">
						</div>
						<div class="masonry--block__content-padding masonry--block__less-padding">
							<h5 class="title">%s</h5>
							<p>%s</p>
						</div>
						<p class="masonry--block__read-more masonry--block__less-padding text--blue">
							<span class="text--blue">Read More</span>
							<svg id="Regular" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="Icon ml5 text--blue"><defs><style>.cls-1,.cls-2{fill:none;stroke:currentColor;stroke-linecap:round;stroke-linejoin:round;stroke-width:1.5px;}.cls-2{fill-rule:evenodd;}</style></defs><title>arrow-circle-right</title><circle class="cls-1" cx="12" cy="12" r="11.25"></circle><path class="cls-2" d="M9,17.25l7.256-4.617a.751.751,0,0,0,0-1.266L9,6.75"></path></svg>
						</p>
					</div>
				</a>
			</li>',
            $delay,
            esc_url(get_permalink()),
            esc_url($image_url),
            esc_attr(get_the_title()),
            esc_html(get_the_title()),
            wp_trim_words(get_the_excerpt(), $excerpt_length, '...')
        );

        $delay += 100; // Increment delay for each post
    }

    wp_reset_postdata();

    $output .= '</ul>';

    return $output;
}
add_shortcode('cpt_category_posts', 'display_cpt_posts_for_category_from_context');

function show_resource_source_shortcode($atts)
{
    // Extract shortcode attributes (if any)
    $attributes = shortcode_atts(array(
        'post_id' => get_the_ID(), // Default to current post ID
    ), $atts);

    // Get the ACF field value
    $resource_source = get_field('resource_source', $attributes['post_id']);

    // Check if the resource source is not empty
    if (!empty($resource_source)) {
        // Return the formatted HTML structure
        return '<div class="CoachesCorner--author">' . esc_html($resource_source) . '</div>';
    }

    // Return empty if the field is not found or is empty
    return '';
}
add_shortcode('show_resource_source', 'show_resource_source_shortcode');

function show_resource_type_shortcode()
{
    // Get the current post ID
    $post_id = get_the_ID();

    // Get terms of the 'resource-type' taxonomy related to the post
    $resource_types = wp_get_post_terms($post_id, 'resource-type', array("fields" => "all"));

    // Initialize an output string
    $output = '';

    // Check if there are any terms returned and no error
    if (!empty($resource_types) && !is_wp_error($resource_types)) {
        // Loop through each term and format it with the specified HTML structure
        foreach ($resource_types as $term) {
            $output .= '<h6 class="mt0 CoachesCorner--category">' . esc_html($term->name) . '</h6>';
        }
    }

    // Return the formatted output or an empty string if no terms are found
    return $output;
}
add_shortcode('show_resource_type', 'show_resource_type_shortcode');

function recipe_categories_loop_shortcode()
{
    ob_start(); // Start output buffering.

    // Fetch all terms in the 'recipe-category' taxonomy.
    $terms = get_terms([
        'taxonomy' => 'recipe-category',
        'hide_empty' => false, // Set to true if you want to hide empty categories.
        'orderby' => 'id', // Change this to 'name', 'id', 'slug', 'count', 'term_group' etc. according to your needs.
        'order' => 'ASC', // Change this to 'DESC' for descending order.
    ]);

    if (!empty($terms) && !is_wp_error($terms)) {
        echo '<div class="recipe-category-listing">'; // Container for the categories.
        foreach ($terms as $term) {
            $term_id = $term->term_id;
            // Get the 'featured_image' field for the term. Note that the field name and taxonomy must match your setup.
            $image_id = get_field('featured_image', 'recipe-category_' . $term_id);
            $image_url = $image_id ? wp_get_attachment_image_url($image_id, 'full') : '';

            if ($image_url) {
                echo '<div class="recipe-category-item"><a href="#' . esc_attr($term->slug) . '">';
                echo '<img src="' . esc_url($image_url) . '" alt="' . esc_attr($term->name) . '">';
                echo '<h6>' . esc_html($term->name) . '</h6>';
                echo '</a></div>';
            } else {
                // Optional: Display something if there's no image.
                echo '<div class="recipe-category-item no-image">';
                echo '<h6>' . esc_html($term->name) . '</h6>';
                echo '</div>';
            }
        }
        echo '</div>';
    } else {
        echo '<p>No recipe categories found.</p>'; // In case there are no terms or an error occurred.
    }

    return ob_get_clean(); // Return the buffered output.
}
add_shortcode('recipe_categories_loop', 'recipe_categories_loop_shortcode');

function my_permalink_shortcode()
{
    // Get the current post's permalink
    $permalink = get_permalink();

    // Return the permalink wrapped in an HTML element for styling if needed
    // For example, here it is wrapped in a <p> tag
    return $permalink;
}
add_shortcode('permalink', 'my_permalink_shortcode');

function display_skill_sport_subcat()
{
    global $post;

    // Get terms in 'skill-sport' taxonomy for the current post
    $terms = wp_get_post_terms($post->ID, 'skill-sport');
    if (empty($terms) || is_wp_error($terms)) {
        return ''; // Return empty if no terms found or if there's an error
    }

    // Assuming the first term is the desired one (adjust logic as needed)
    $parent_term = $terms[0];

    // If the term has a parent, then it's not the top-level term we're looking for
    while ($parent_term->parent != 0) {
        $parent_term = get_term($parent_term->parent, 'skill-sport');
    }

    // Get child terms of the parent term
    $child_terms = get_terms('skill-sport', array('parent' => $parent_term->term_id, 'hide_empty' => false));

    // Start building the output
    $output = '<ul class="masonry-grid masonry-grid__thirds">';

    foreach ($child_terms as $term) {
        $term_link = get_term_link($term);
        $featured_image = get_field('featured_image', $term); // Assuming 'featured_image' is the ACF field name.

        if (!is_wp_error($term_link) && !empty($featured_image)) {
            $output .= sprintf(
                '<li class="masonry-grid--block sal-animate" data-sal="slide-down" data-sal-easing="ease-out-bounce" data-sal-delay="100"><a class="masonry-grid--block__link" href="%s"><div class="masonry--block__content"><div class="Article--imageWrapper"><img src="%s" alt=""></div><div class="masonry--block__content-padding masonry--block__less-padding"><h5 class="rank">%s</h5><h6 class="title thin smaller masonry-grid--block__article-title">Articles: <span class="badge masonry-grid--block__article-badge">%d</span></h6></div><p class="masonry--block__read-more masonry--block__less-padding"><span class="text--blue">Read More</span> <svg id="Regular" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="Icon ml5 text--blue"><defs><style>.cls-1,.cls-2{fill:none;stroke:currentColor;stroke-linecap:round;stroke-linejoin:round;stroke-width:1.5px;}.cls-2{fill-rule:evenodd;}</style></defs><title>arrow-circle-right</title><circle class="cls-1" cx="12" cy="12" r="11.25"></circle><path class="cls-2" d="M9,17.25l7.256-4.617a.751.751,0,0,0,0-1.266L9,6.75"></path></svg></p></div></a></li>',
                esc_url($term_link),
                esc_url($featured_image),
                esc_html($term->name),
                $term->count
            );
        }
    }

    $output .= '</ul>';

    return $output;
}
add_shortcode('skill_sport_subcat', 'display_skill_sport_subcat');

function get_unique_post_count_including_children($term_id, $taxonomy)
{
    $posts = get_posts(array(
        'post_type' => 'any',
        'numberposts' => -1, // Retrieve all posts
        'fields' => 'ids', // Only need the IDs for counting
        'tax_query' => array(
            array(
                'taxonomy' => $taxonomy,
                'field' => 'term_id',
                'terms' => $term_id,
                'include_children' => true, // This automatically includes posts from child terms
            ),
        ),
    ));

    // The 'fields' => 'ids' and 'include_children' => true ensure we only get unique post IDs across all child terms
    return count(array_unique($posts)); // Ensure uniqueness, though 'fields' => 'ids' should already do this.
}

function display_skill_sport_nav()
{
    // No need for the global post variable since we're fetching top-level terms, not related to a specific post.

    // Fetch top-level terms in 'skill-sport' taxonomy
    $top_level_terms = get_terms('skill-sport', array('parent' => 0, 'hide_empty' => false));

    // Check if terms were found or if there's an error
    if (empty($top_level_terms) || is_wp_error($top_level_terms)) {
        return ''; // Return empty if no terms found or if there's an error
    }

    // Start building the output
    $output = '<nav id="subnav">';
    $output .= '<h3 class="mt0 mb0 mr0 ml0">Categories</h3><ul class="List--noBullets">';

    foreach ($top_level_terms as $term) {
        // Generate the term link
        $term_link = get_term_link($term);
        if (!is_wp_error($term_link)) {
            // Call the custom function to get the unique post count for the term and its children
            $unique_post_count = get_unique_post_count_including_children($term->term_id, 'skill-sport');

            // Build the output string with the unique post count
            $output .= sprintf(
                '<li><a href="%s">%s</a><span class="badge pull-right">%d</span></li>',
                esc_url($term_link),
                esc_html($term->name),
                $unique_post_count // Use the unique post count instead of $term->count
            );
        }
    }

    $output .= '</ul></nav>';

    return $output;
}
add_shortcode('skill_sport_nav', 'display_skill_sport_nav');

function display_skill_sport_categories()
{
    // Fetch all top-level terms in 'skill-sport' taxonomy
    $parent_terms = get_terms('skill-sport', array('parent' => 0, 'hide_empty' => false));
    if (empty($parent_terms) || is_wp_error($parent_terms)) {
        return ''; // Return empty if no terms found or if there's an error
    }

    // Start building the output
    $output = '<ul class="masonry-grid masonry-grid__thirds">';

    foreach ($parent_terms as $term) {
        $term_link = get_term_link($term);
        $featured_image = get_field('featured_image', 'skill-sport_' . $term->term_id); // Adjusted to fetch the featured image for a term.

        if (!is_wp_error($term_link) && !empty($featured_image)) {
            // Call the custom function to get the unique post count for the term and its children
            $unique_post_count = get_unique_post_count_including_children($term->term_id, 'skill-sport');

            $output .= sprintf(
                '<li class="masonry-grid--block sal-animate" data-sal="slide-down" data-sal-easing="ease-out-bounce" data-sal-delay="100"><a class="masonry-grid--block__link" href="%s"><div class="masonry--block__content"><div class="Article--imageWrapper"><img src="%s" alt=""></div><div class="masonry--block__content-padding masonry--block__less-padding"><h5 class="rank">%s</h5><h6 class="title thin smaller masonry-grid--block__article-title">Articles: <span class="badge masonry-grid--block__article-badge">%d</span></h6></div><p class="masonry--block__read-more masonry--block__less-padding"><span class="text--blue">Read More</span> <svg id="Regular" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="Icon ml5 text--blue"><defs><style>.cls-1,.cls-2{fill:none;stroke:currentColor;stroke-linecap:round;stroke-linejoin:round;stroke-width:1.5px;}.cls-2{fill-rule:evenodd;}</style></defs><title>arrow-circle-right</title><circle class="cls-1" cx="12" cy="12" r="11.25"></circle><path class="cls-2" d="M9,17.25l7.256-4.617a.751.751,0,0,0,0-1.266L9,6.75"></path></svg></p></div></a></li>',
                esc_url($term_link),
                esc_url($featured_image),
                esc_html($term->name),
                $unique_post_count // Use the unique post count instead of $term->count
            );
        }
    }

    $output .= '</ul>';

    return $output;
}
add_shortcode('skill_sport_categories', 'display_skill_sport_categories');

function custom_tech_blog_breadcrumbs()
{
    global $post;

    // Initialize breadcrumbs array
    $breadcrumbs = array();

    // Blog link
    $breadcrumbs[] = '<span><a href="' . get_home_url() . '/documentation/blog">Blog</a></span>';

    // Tech-term link
    $terms = get_the_terms($post->ID, 'tech-term');
    if ($terms && !is_wp_error($terms)) {
        // Get the first term
        $term = array_shift($terms);
        $term_link = get_term_link($term);
        $term_name = $term->name;
        $breadcrumbs[] = '<span>/ <a href="' . esc_url($term_link) . '">' . esc_html($term_name) . '</a></span>';
    }

    // Current post link
    $breadcrumbs[] = '<span> / <a class="active" href="' . get_permalink() . '">' . get_the_title() . '</a></span>';

    // Combine all parts of the breadcrumbs
    $breadcrumbs_html = '<h6 class="feature__subnav-heading ms--_1__slab ContentHub--Breadcrumbs">' . implode('', $breadcrumbs) . '</h6>';

    return $breadcrumbs_html;
}
add_shortcode('tech_blog_breadcrumbs', 'custom_tech_blog_breadcrumbs');

function display_author_and_terms_shortcode()
{
    global $post;

    // Fetch author name and image URL from ACF fields.
    $author_name = get_field('author_name', $post->ID);
    $author_image_url = get_field('author_image', $post->ID); // Assumes return format is URL.

    // Initialize the HTML structure with author details.
    $html = '<div class="bg--light-grey Docs--BlogBar corner--lg"><span class="blog-author"><img src="' . esc_url($author_image_url) . '" alt="Image of engineering blog author: ' . esc_attr($author_name) . '">' . esc_html($author_name) . '</span>';

    // Get terms from 'tech-term' taxonomy associated with the post.
    $terms = get_the_terms($post->ID, 'tech-term');
    if (!is_wp_error($terms) && !empty($terms)) {
        $html .= '<ul class="List--noBullets blog-tags">';
        foreach ($terms as $term) {
            $term_url = get_term_link($term);
            $html .= '<li><a href="' . esc_url($term_url) . '">' . esc_html($term->name) . '</a></li>';
        }
        $html .= '</ul>';
    }

    // Close the main div.
    $html .= '</div>';

    return $html;
}

// Register the shortcode with WordPress.
add_shortcode('author_and_terms', 'display_author_and_terms_shortcode');

function tech_blog_terms_list_shortcode()
{
    // Get the current URL
    $current_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

    // Fetch all posts count within 'tech-blog' CPT
    $all_posts_count = wp_count_posts('tech-blog')->publish;

    // Start the list-group container
    $output = '<ul class="list-group small nav-link">';

    // Determine if the 'All Posts' item should be active
    $all_posts_active_class = ($current_url == site_url('/documentation/blog/')) ? 'active' : '';

    // Add the 'All Posts' item with count and potential active class
    $output .= '<li class="list-group-item ' . $all_posts_active_class . '"><a href="/documentation/blog">All Posts<span class="badge pull-right">' . $all_posts_count . '</span></a></li>';

    // Get terms in the 'tech-term' taxonomy
    $terms = get_terms(array(
        'taxonomy' => 'tech-term',
        'hide_empty' => true,
    ));

    // Check if there are any terms found
    if (!empty($terms) && !is_wp_error($terms)) {
        // Loop through each term to create list items
        foreach ($terms as $term) {
            // Get term link
            $term_link = get_term_link($term);

            // Determine if the current item should have the active class
            $active_class = (trailingslashit($current_url) == trailingslashit($term_link)) ? 'active' : '';

            // Add term item to the list with potential active class
            $output .= '<li class="list-group-item ' . $active_class . '"><a href="' . esc_url($term_link) . '">' . esc_html($term->name) . ' <span class="badge pull-right">' . $term->count . '</span></a></li>';
        }
    }

    // Close the list-group container
    $output .= '</ul>';

    return $output;
}

// Register the shortcode with WordPress
add_shortcode('tech_blog_terms_list', 'tech_blog_terms_list_shortcode');

function testimonial_slider_shortcode($atts)
{
    // Shortcode attributes with default value
    $attributes = shortcode_atts(array(
        'category' => 'brands' // Default category if none provided
    ), $atts);

    // Arguments for querying the slides CPT
    $args = array(
        'post_type' => 'testimonial',
        'posts_per_page' => -1, // Retrieve all slides
        'tax_query' => array(
            array(
                'taxonomy' => 'testimonial-category',
                'field'    => 'slug',
                'terms'    => $attributes['category'],
            ),
        ),
    );

    // WP_Query to fetch testimonials
    $testimonial_query = new WP_Query($args);
    $output = '<div class="Brands--overviewSliderWrapper Brands--quoteSliderWrapper">';
    $output .= '<div class="swiper-container"><div class="swiper-wrapper">';
    if ($testimonial_query->have_posts()) {
        while ($testimonial_query->have_posts()) {
            $testimonial_query->the_post();
            $featured_image = get_the_post_thumbnail_url(get_the_ID(), 'full'); // Retrieve featured image URL
            $testimonial_title = get_the_title(); // Get the title of the testimonial
            $testimonial_content = get_the_content(); // Get the content of the testimonial

            // Individual slide HTML structure
            $output .= '<div class="swiper-slide" role="group" aria-label="' . $testimonial_title . '">';
            $output .= '<div class="text-center">';
            $output .= '<img src="' . esc_url($featured_image) . '" alt="' . esc_attr($testimonial_title) . '" style="max-width:250px;">';
            $output .= '<blockquote class="Brands--blockquote">' . wp_kses_post($testimonial_content) . '<cite>— ' . esc_html($testimonial_title) . '</cite></blockquote>';
            $output .= '</div>';
            $output .= '</div>';
        }
    } else {
        $output .= '<p>No testimonials found.</p>';
    }
    $output .= '';
    $output .= '</div><div class="swiper-nav"><div class="swiper-button-prev"></div><div class="swiper-button-next"></div></div></div></div>'; // Close swiper-wrapper div
    wp_reset_postdata();

    return $output;
}
add_shortcode('testimonial_slider', 'testimonial_slider_shortcode');


add_action('wp_enqueue_scripts', 'my_child_theme_enqueue_styles', 100);
//Enqueueing CSS files

// Inject ACF Backend Styles
function my_acf_admin_head()
{
?>
    <style type="text/css">
        /*** Blog ACF ***/
        #blog_sponsor_img {
            border-right: 1px solid #eeeeee;
        }

        #blog_sponsor_url {
            border-left-color: transparent;
        }
    </style>
<?php
}
add_action('acf/input/admin_head', 'my_acf_admin_head');





/**
 * Function to restrict the query to specific custom post types based on taxonomy.
 * 
 * Added by Adnan 06/07/2024
 * 
 * This function modifies the main query to restrict posts to the 'business-resources' 
 * or 'coaches-corner' custom post types based on the 'resource-type' taxonomy terms.
 * It ensures that only the relevant posts are shown in the archive pages of these post types.
 * 
 * Issue: Previously, WordPress was displaying posts from both post types instead of showing 
 * posts from the specific post type corresponding to the current taxonomy term. 
 *  
 * @param WP_Query $query The current query object.
 */
function restrict_to_specific_cpt($query)
{
    // Check if we're in the admin area or if it's not the main query
    if (is_admin() || !$query->is_main_query()) {
        return;
    }

    // Check if the query is for any term in the 'resource-type' taxonomy
    if ($query->is_tax('resource-type')) {


        // Get the queried object to determine the term
        $queried_object = get_queried_object();

        // Check the current post type in the query
        $post_type = $query->get('post_type');

        // If no specific post type is set, default to the relevant custom post type
        if (!$post_type) {
            if ($queried_object && $queried_object->taxonomy == 'resource-type') {
                // Set post type based on the taxonomy term context
                $query->set('post_type', ['business-resources', 'coaches-corner']);
            }
        }
    }
}
add_action('pre_get_posts', 'restrict_to_specific_cpt');


//Registering thumbnail sizes begins
function custom_thumbnail_sizes()
{
    // Register a new image size
    add_image_size('thumbnail360x200', 360, 200, true); // 360x200 pixels, hard crop mode
	add_image_size('thumbnail900x300', 900, 300, true); // 900x300 pixels, hard crop mode
	add_image_size('thumbnail400x250', 400, 250, true); // 400x250 pixels, hard crop mode
}
add_action('after_setup_theme', 'custom_thumbnail_sizes');
//Registering thumbnail sizes ends

// Added ACF Admin Styles :: 6/13/2024
function enqueue_admin_css_based_on_acf_groups()
{
    // Define an array of ACF group keys to check
    $group_keys = array(
        'group_66632bad6fd5f',
        'group_66671004cf2c7',
        'group_6662efd119199',
        'group_66623ce074e6d'
        // Add more keys as needed
    );

    // Retrieve all ACF field groups
    $groups = acf_get_field_groups();

    // Check if any of the specified ACF groups exist
    foreach ($groups as $group) {
        if (in_array($group['key'], $group_keys)) {
            // Enqueue your admin CSS file
            wp_enqueue_style('a-acf-style', get_stylesheet_directory_uri() . '/assets/css/a-acf.css');
            return; // Stop checking once we find one of the specified ACF groups
        }
    }
}
add_action('admin_enqueue_scripts', 'enqueue_admin_css_based_on_acf_groups');

/**
 * Set of functions to disable default gutenberg panel of custom taxonomy resource-type
 * on busines-resource post type and create a custom meta box so the user/admin can 
 * selet only one resource type.
 * 
 * Added by Adnan 06/17/2024
 * 
 * START
 */
// Function to add the custom meta box
function add_single_term_meta_box() {
    add_meta_box('single-term-meta-box', 'Types', 'single_term_meta_box', 'business-resource', 'side', 'default');
}
add_action('add_meta_boxes', 'add_single_term_meta_box');

// Function to display the custom meta box
function single_term_meta_box($post) {
    // Get all terms for this taxonomy
    $terms = get_terms([
        'taxonomy' => 'resource-type',
        'hide_empty' => false,
    ]);

    // Get the current term
    $current_terms = wp_get_post_terms($post->ID, 'resource-type', ['fields' => 'ids']);
    $current_term_id = !empty($current_terms) ? $current_terms[0] : '';

    echo '<div id="taxonomy-resource-type" class="categorydiv">';
    echo '<ul id="resource-type-tabs" class="category-tabs"><li class="tabs"><a href="#resource-type-all" tabindex="3">All Types</a></li></ul>';
    echo '<div id="resource-type-all" class="tabs-panel">';
    echo '<ul id="resource-typechecklist" class="list:resource-type categorychecklist form-no-clear">';
    
    foreach ($terms as $term) {
        $checked = $term->term_id == $current_term_id ? ' checked="checked"' : '';
        echo '<li id="resource-type-' . $term->term_id . '">';
        echo '<label class="selectit">';
        echo '<input type="radio" name="resource_type" value="' . $term->term_id . '"' . $checked . '> ' . $term->name;
        echo '</label>';
        echo '</li>';
    }

    echo '</ul>';
    echo '</div>';
    echo '</div>';
}

// Function to save the selected term
function save_single_term($post_id) {
    // Verify post type
    if (isset($_POST['post_type']) && 'business-resource' == $_POST['post_type']) {
        // Check if the custom taxonomy term is set
        if (isset($_POST['resource_type'])) {
            // Get the selected term
            $term = intval($_POST['resource_type']);

            // Set the term
            wp_set_object_terms($post_id, $term, 'resource-type');
        } else {
            // If no term is selected, set an empty term
            wp_set_object_terms($post_id, [], 'resource-type');
        }
    }
}
add_action('save_post', 'save_single_term');

// Enqueue the script to hide the default taxonomy panel
/* function custom_taxonomy_script() {
    ?>
    <script type="text/javascript">
        (function($) {
            const unregisterDefaultPanel = () => {
                const postType = wp.data.select('core/editor').getCurrentPostType();
                if (postType === 'business-resource') {
                    // Hide the default taxonomy panel for 'resource-type'
                    const panelTitles = document.querySelectorAll('.components-panel__body-title button');
                    panelTitles.forEach(button => {
                        if (button.textContent.trim() === 'Types') {
                            const panel = button.closest('.components-panel__body');
                            if (panel) {
                                panel.style.display = 'none';
                            }
                        }
                    });
                }
            };

            wp.data.subscribe(() => {
                unregisterDefaultPanel();
            });

            $(document).ready(function() {
                // Initial call to hide the default taxonomy panel
                unregisterDefaultPanel();
            });
        })(jQuery);
    </script>
    <?php
}
add_action('admin_footer', 'custom_taxonomy_script'); **/
/*** END ***/

function enqueue_marketo_form_JS() {

    // Enqueue JavaScript file
    wp_enqueue_script('enqueue-marketo-JS', '//huddle.teamsnap.com/js/forms2/js/forms2.min.js', array(), null, true);
}
// add_action('wp_enqueue_scripts', 'enqueue_marketo_form_JS');

function custom_dynamic_rewrite_rules_for_cpt() {
    $post_type = 'coaches-corner';
    $taxonomies = ['sport', 'resource-type'];

    foreach ($taxonomies as $taxonomy) {
        $terms = get_terms([
            'taxonomy' => $taxonomy,
            'hide_empty' => false,
        ]);

        foreach ($terms as $term) {
            $term_hierarchy = get_term_hierarchy($term, $taxonomy);
            $term_path = implode('/', $term_hierarchy);

            add_rewrite_rule(
                '^resources/coaches/' . $term_path . '/?$',
                'index.php?' . $taxonomy . '=' . $term->slug . '&post_type=' . $post_type,
                'top'
            );
        }
    }
}
add_action('init', 'custom_dynamic_rewrite_rules_for_cpt');

function custom_dynamic_term_link_for_cpt($url, $term, $taxonomy) {
    if (in_array($taxonomy, ['sport', 'resource-type'])) {
        // Check if the request is for the 'coaches-corner' CPT
        global $post;
        $is_edit_screen = is_admin() && isset($_GET['post_type']) && $_GET['post_type'] === 'coaches-corner';

        if ((isset($post) && $post->post_type === 'coaches-corner') || $is_edit_screen) {
            $term_hierarchy = get_term_hierarchy($term, $taxonomy);
            $term_path = implode('/', $term_hierarchy);

            $url = home_url('/resources/coaches/' . $term_path . '/');
        }
    }

    return $url;
}
add_filter('term_link', 'custom_dynamic_term_link_for_cpt', 10, 3);

function get_term_hierarchy($term, $taxonomy) {
    $ancestors = get_ancestors($term->term_id, $taxonomy, 'taxonomy');
    $ancestors = array_reverse($ancestors);
    $term_hierarchy = [];

    foreach ($ancestors as $ancestor_id) {
        $ancestor = get_term($ancestor_id, $taxonomy);
        $term_hierarchy[] = $ancestor->slug;
    }

    $term_hierarchy[] = $term->slug;
    return $term_hierarchy;
}








function add_to_calendar_buttons($atts) {
    // Extract shortcode attributes
    $atts = shortcode_atts(
        array(
            'post_id' => get_the_ID(), // Default to current post ID
            'title' => 'Schedule', // Default title
        ),
        $atts,
        'add_to_calendar_buttons'
    );

    // Get the post ID and title from shortcode attributes
    $post_id = $atts['post_id'];
    $title = esc_html($atts['title']);

    // Get the post title and permalink
    $post_title = get_the_title($post_id);
    $post_permalink = get_permalink($post_id);

    // Create the event title
    $event_title = urlencode("Family Movie Night with HBO Max: $post_title");

    // Get the movie link from ACF field
    $movie_link = get_field('movie_link', $post_id);

    // Create the event description
    $event_description = urlencode("Family Movie night brought to you by HBO Max! Enjoy and don’t forget the snacks. Watch here: $movie_link");

    // Define the start and end times
    $start_time = date('Ymd\THis\Z', strtotime('now'));
    $end_time = date('Ymd\THis\Z', strtotime('+1 hour'));

    // Google Calendar URL
    $google_calendar_url = "https://www.google.com/calendar/render?action=TEMPLATE&text=$event_title&details=$event_description&dates=$start_time/$end_time";

    // iCal URL
    $ical_url = "data:text/calendar;charset=utf8," . urlencode(
        "BEGIN:VCALENDAR\n" .
        "VERSION:2.0\n" .
        "BEGIN:VEVENT\n" .
        "SUMMARY:$event_title\n" .
        "DESCRIPTION:$event_description\n" .
        "DTSTART:$start_time\n" .
        "DTEND:$end_time\n" .
        "END:VEVENT\n" .
        "END:VCALENDAR"
    );

    // Outlook URL
    $outlook_url = "https://outlook.live.com/owa/?path=/calendar/action/compose&rru=addevent" .
        "&subject=$event_title" .
        "&body=$event_description" .
        "&startdt=$start_time" .
        "&enddt=$end_time";

    // Yahoo URL
    $yahoo_start_time = date('Ymd\THis\Z', strtotime('now'));
    $yahoo_end_time = date('Ymd\THis\Z', strtotime('+1 hour'));
    $yahoo_calendar_url = "https://calendar.yahoo.com/?v=60&view=d&type=20" .
        "&title=$event_title" .
        "&st=$yahoo_start_time" .
        "&et=$yahoo_end_time" .
        "&desc=$event_description";

    // Return the HTML for the calendar links with the specified title
    return '
        <div class="chooseCalFromTheList">
            <ul class="chooseCalFromTheListParentList">
                <li>' . $title . '
                    <ul class="chooseCalFromTheListChildList">
                        <li><a href="' . $google_calendar_url . '" target="_blank">Google</a></li>
                        <li><a href="' . $ical_url . '" target="_blank" download="event.ics">iCal</a></li>
                        <li><a href="' . $outlook_url . '" target="_blank">Outlook</a></li>
                        <li><a href="' . $yahoo_calendar_url . '" target="_blank">Yahoo</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    ';
}
add_shortcode('add_to_calendar_buttons', 'add_to_calendar_buttons');













// Function to display star ratings based on numeric rating
function display_star_rating_ss($rating) {
    // Round to the nearest half star
    $rounded_rating = round($rating * 2) / 2;

    // Calculate full stars, half stars, and empty stars
    $full_stars = floor($rounded_rating);
    $half_star = ($rounded_rating - $full_stars) >= 0.5 ? 1 : 0;
    $empty_stars = 5 - $full_stars - $half_star;

    // Output stars HTML
    $output = '';

    // Full stars
    for ($i = 0; $i < $full_stars; $i++) {
        $output .= '<span class="full_ss">&#9733;</span>';
    }

    // Half star
    if ($half_star) {
        $output .= '<span class="half_ss">&#9733;</span>';
    }

    // Empty stars
    for ($i = 0; $i < $empty_stars; $i++) {
        $output .= '<span class="empty_ss">&#9734;</span>';
    }

    return $output;
}

// Shortcode for displaying star ratings
function star_rating_shortcode_ss($atts) {
    $atts = shortcode_atts(array(
        'post_id' => get_the_ID(), // Default to current post ID
    ), $atts, 'star_rating_ss');

    // Get rating from ACF field or any other source
    $rating = get_field('rating', $atts['post_id']);

    // Return star rating HTML
    if ($rating !== false && $rating !== '') {
        // Call display_star_rating function to generate star HTML
        $output = '<div class="star_rating_ss">';
        $output .= display_star_rating_ss($rating);
        $output .= '</div>';
        
        return $output;
    }
    return ''; // Return empty string if no valid rating found
}
add_shortcode('star_rating_ss', 'star_rating_shortcode_ss');


function custom_skills_drills_post_link($post_link, $post) {
    if ($post->post_type == 'skills-drills') {
        // Get the terms of the custom taxonomy 'skill-sport'
        $terms = get_the_terms($post->ID, 'skill-sport');
        if ($terms && !is_wp_error($terms)) {
            // Initialize the taxonomy path
            $taxonomy_path = array();

            // Identify child and parent terms
            foreach ($terms as $term) {
                if ($term->parent == 0) {
                    // It's a parent term, add it to the taxonomy path
                    $taxonomy_path[] = $term->slug;
                } else {
                    // It's a child term, add it to the taxonomy path
                    $taxonomy_path[] = $term->slug;

                    break; // Stop after finding child and its parents
                }
            }

            // Build the new URL
            $taxonomy_path = implode('/', $taxonomy_path);
            $post_link = home_url('skills-drills/' . $taxonomy_path . '/' . $post->post_name . '/');
        }
    }
    return $post_link;
}
// add_filter('post_type_link', 'custom_skills_drills_post_link', 10, 2);



function custom_skills_drills_rewrite_rules() {

    add_rewrite_rule(
        '^skills-drills/([^/]+)/([^/]+)/([^/]+)/?$',
        'index.php?post_type=skills-drills&name=$matches[3]&skill-sport=$matches[1]',
        'top'
    );

    add_rewrite_rule(
        '^skills-drills/([^/]+)/([^/]+)/?$',
        'index.php?post_type=skills-drills&skill-sport=$matches[1]',
        'top'
    );
}
// add_action('init', 'custom_skills_drills_rewrite_rules');

// Also flush rewrite rules manually when necessary 
if (isset($_GET['flush_rewrite_rules']) && current_user_can('manage_options')) { 
    custom_skills_drills_rewrite_rules();
    flush_rewrite_rules(); 
    echo 'Rewrite rules flushed.'; 
}

// Allow PDF Uploads Begins
function allow_pdf_uploads($mime_types) {
    $mime_types['pdf'] = 'application/pdf';
    return $mime_types;
}
add_filter('upload_mimes', 'allow_pdf_uploads');
// Allow PDF Uploads Ends


// Adding GTM in the Body Tag Begins
function insert_gtm_code() {
    ?>
    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src=""
    height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->
    <?php
}
add_action('wp_body_open', 'insert_gtm_code');
// Adding GTM in the Body Tag Ends


//Display Sports Categories on /community/sports-photography/ Begins [selected_categories_masonry category_ids="1,3,5"]
function display_sports_photography_selected_categories_masonry_shortcode($atts)
{
    // Shortcode attributes with defaults
    $attributes = shortcode_atts(array(
        'taxonomy' => 'sp-category', // Custom taxonomy 'sp-category'
        'category_ids' => '', // Comma-separated list of category IDs
    ), $atts);

    $taxonomy = $attributes['taxonomy'];
    $category_ids = explode(',', $attributes['category_ids']); // Convert the string of IDs into an array

    // Get only the specified categories by their IDs
    $categories = get_terms(array(
        'taxonomy' => $taxonomy,
        'hide_empty' => false,
        'include' => $category_ids, // Only include specified category IDs
    ));

    if (empty($categories) || is_wp_error($categories)) {
        return 'No categories found.';
    }

    $output = '<ul class="masonry-grid masonry-grid__thirds">';
    $delay = 100; // Initial animation delay

    foreach ($categories as $category) {
        $featured_image = get_field('featured_image', $taxonomy . '_' . $category->term_id); // Adjust if necessary
        $category_link = get_term_link($category->term_id, $taxonomy);
        $category_name = $category->name;
        $article_count = $category->count;

        $output .= sprintf(
            '<li class="masonry-grid--block sal-animate" data-sal="slide-down" data-sal-easing="ease-out-bounce" data-sal-delay="%d">
                <a class="masonry-grid--block__link" href="%s">
                    <div class="masonry--block__content">
                        <div class="Article--imageWrapper">
                            <img loading="lazy" src="%s" alt="A preview image for the category: %s">
                        </div>
                        <div class="masonry--block__content-padding masonry--block__less-padding">
                            <h5 class="rank">%s</h5>
                            <h6 class="title smaller masonry-grid--block__article-title">Articles: <span class="badge masonry-grid--block__article-badge">%d</span></h6>
                        </div>
                        <p class="masonry--block__read-more masonry--block__less-padding">
                            <span class="text--blue">Read More</span>
                            <svg id="Regular" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="Icon ml5 text--blue"><defs><style>.cls-1,.cls-2{fill:none;stroke:currentColor;stroke-linecap:round;stroke-linejoin:round;stroke-width:1.5px;}.cls-2{fill-rule:evenodd;}</style></defs><title>arrow-circle-right</title><circle class="cls-1" cx="12" cy="12" r="11.25"></circle><path class="cls-2" d="M9,17.25l7.256-4.617a.751.751,0,0,0,0-1.266L9,6.75"></path></svg>
                        </p>
                    </div>
                </a>
            </li>',
            $delay,
            esc_url($category_link),
            esc_url($featured_image),
            esc_attr($category_name),
            esc_html($category_name),
            $article_count
        );

        $delay += 100; // Increment delay for each block
    }

    $output .= '</ul>';

    return $output;
}
add_shortcode('display_sports_photography_selected_categories', 'display_sports_photography_selected_categories_masonry_shortcode');
//Display Sports Categories on /community/sports-photography/ Begins - Ends [selected_categories_masonry category_ids="1,3,5"]



//Display Skill Drills Categories on /community/skills-drills/category/baseball/ Begins [display_skills_drills_selected_categories category_ids="1,3,5"]
function display_skill_sport_selected_categories_masonry_shortcode($atts)
{
    // Shortcode attributes with defaults
    $attributes = shortcode_atts(array(
        'taxonomy' => 'skill-sport', // Custom taxonomy 'sp-category'
        'category_ids' => '', // Comma-separated list of category IDs
    ), $atts);

    $taxonomy = $attributes['taxonomy'];
    $category_ids = explode(',', $attributes['category_ids']); // Convert the string of IDs into an array

    // Get only the specified categories by their IDs
    $categories = get_terms(array(
        'taxonomy' => $taxonomy,
        'hide_empty' => false,
        'include' => $category_ids, // Only include specified category IDs
    ));

    if (empty($categories) || is_wp_error($categories)) {
        return 'No categories found.';
    }

    $output = '<ul class="masonry-grid masonry-grid__thirds">';
    $delay = 100; // Initial animation delay

    foreach ($categories as $category) {
        $featured_image = get_field('featured_image', $taxonomy . '_' . $category->term_id); // Adjust if necessary
        $category_link = get_term_link($category->term_id, $taxonomy);
        $category_name = $category->name;
        $article_count = $category->count;

        $output .= sprintf(
            '<li class="masonry-grid--block sal-animate" data-sal="slide-down" data-sal-easing="ease-out-bounce" data-sal-delay="%d">
                <a class="masonry-grid--block__link" href="%s">
                    <div class="masonry--block__content">
                        <div class="Article--imageWrapper">
                            <img loading="lazy" src="%s" alt="A preview image for the category: %s">
                        </div>
                        <div class="masonry--block__content-padding masonry--block__less-padding">
                            <h5 class="rank">%s</h5>
                            <h6 class="title smaller masonry-grid--block__article-title">Articles: <span class="badge masonry-grid--block__article-badge">%d</span></h6>
                        </div>
                        <p class="masonry--block__read-more masonry--block__less-padding">
                            <span class="text--blue">Read More</span>
                            <svg id="Regular" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="Icon ml5 text--blue"><defs><style>.cls-1,.cls-2{fill:none;stroke:currentColor;stroke-linecap:round;stroke-linejoin:round;stroke-width:1.5px;}.cls-2{fill-rule:evenodd;}</style></defs><title>arrow-circle-right</title><circle class="cls-1" cx="12" cy="12" r="11.25"></circle><path class="cls-2" d="M9,17.25l7.256-4.617a.751.751,0,0,0,0-1.266L9,6.75"></path></svg>
                        </p>
                    </div>
                </a>
            </li>',
            $delay,
            esc_url($category_link),
            esc_url($featured_image),
            esc_attr($category_name),
            esc_html($category_name),
            $article_count
        );

        $delay += 100; // Increment delay for each block
    }

    $output .= '</ul>';

    return $output;
}
add_shortcode('display_skills_drills_selected_categories', 'display_skill_sport_selected_categories_masonry_shortcode');
//Display Skills Drills Categories on /community/skills-drills/category/baseball/ Begins - Ends [display_skills_drills_selected_categories category_ids="1,3,5"]



//taxonomy=skill-sport and post_type=skills-drills child tax only - Begins
function display_cskills_drills_child_sports() {
    if (is_tax('skill-sport')) { // Ensure we are on a taxonomy archive page
        // Get the current term for the taxonomy archive
        $current_term = get_queried_object();

        // If the current term has a parent, we know it's a child category
        if ($current_term && $current_term->parent != 0) {
            // Query posts that are in the current child category
            $args = array(
                'post_type'      => 'skills-drills', // Custom post type
                'posts_per_page' => -1, // Show all posts without pagination
                'tax_query'      => array(
                    array(
                        'taxonomy' => 'skill-sport',
                        'field'    => 'term_id',
                        'terms'    => $current_term->term_id, // Only query posts for the current child term
                        'operator' => 'IN',
                    ),
                ),
            );

            $query = new WP_Query($args);

            // Start building the output
            $output = '<ul class="post-list_skills_drills_child_sports_ss">';
            if ($query->have_posts()) {
                while ($query->have_posts()) {
                    $query->the_post();

                    // Get post details (title, link, etc.)
                    $post_link = get_permalink();
                    $post_title = get_the_title();
                    $post_excerpt = wp_trim_words(get_the_excerpt(), 5); // Get a 5-word excerpt

                    // Output each post with basic formatting
                    $output .= sprintf(
                        '<li><a href="%s"><h5>%s</h5><p>%s...</p><span class="read-more_ss">Read More <svg id="Regular" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="Icon ml5 text--blue"><defs><style>.cls-1,.cls-2{fill:none;stroke:currentColor;stroke-linecap:round;stroke-linejoin:round;stroke-width:1.5px;}.cls-2{fill-rule:evenodd;}</style></defs><title>arrow-circle-right</title><circle class="cls-1" cx="12" cy="12" r="11.25"></circle><path class="cls-2" d="M9,17.25l7.256-4.617a.751.751,0,0,0,0-1.266L9,6.75"></path></svg></span></a></li>',
                        esc_url($post_link),
                        esc_html($post_title),
                        esc_html($post_excerpt),
                        esc_url($post_link) // Read more link directs to the same post
                    );
                }
                wp_reset_postdata(); // Reset post data after the query
            } else {
                $output .= '<p>No posts found in this child category.</p>';
            }
            $output .= '</ul>';

            return $output;
        } else {
            return '<p>This is not a child category, or there are no child categories available.</p>';
        }
    } else {
        return '<p>This shortcode should only be used on the skill-sport taxonomy archive pages.</p>';
    }
}
add_shortcode('skills_drills_child_sports', 'display_cskills_drills_child_sports');

//taxonomy=skill-sport and post_type=skills-drills child tax only - Begins

//Back to top button on /type/ pages Begins
function custom_back_to_top_button() {
    // Check if '/type/' exists in any part of the URL
    if ( strpos($_SERVER['REQUEST_URI'], '/topics/') !== false ) {
        echo '
        <div class="backToTopTopics_container_SS">
        <a href="#" id="backToTopTopics_SS" style="display:none;"><svg width="50" height="50" viewBox="0 0 50 50" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="25" cy="25" r="25" fill="#FF9100"></circle><path d="M25 36.5V15M18 21L25 14L32 21" stroke="white" stroke-width="2" stroke-linecap="round"></path></svg></a>
        </div>
        <script>
            // Show/hide button on scroll
            window.onscroll = function() {
                var button = document.getElementById("backToTopTopics_SS");
                if (document.body.scrollTop > 200 || document.documentElement.scrollTop > 200) {
                    button.style.display = "block";
                } else {
                    button.style.display = "none";
                }
            };
            
            // Smooth scroll to top when button is clicked
            document.getElementById("backToTopTopics_SS").onclick = function(e) {
                e.preventDefault();
                window.scrollTo({top: 0, behavior: "smooth"});
            };
        </script>';
    }
}
add_action('wp_footer', 'custom_back_to_top_button');

// Function to add post ID in admin bar
function show_post_id_in_admin_bar($wp_admin_bar) {
    if (is_admin_bar_showing() && is_singular()) {
        $post_id = get_the_ID();
        $post_type = get_post_type($post_id);

        $wp_admin_bar->add_node(array(
            'id'    => 'post_id_display',
            'title' => $post_id,
            'meta'  => array(
                'class' => 'post-id-display'
            )
        ));
    }
}
add_action('admin_bar_menu', 'show_post_id_in_admin_bar', 100);
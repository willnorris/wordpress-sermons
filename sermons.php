<?php
/*
 Plugin Name: Sermons
 Plugin URI: http://github.com/willnorris/wordpress-sermons
 Description: Sermons
 Author: Will Norris
 Author URI: http://willnorris.com/
 Version: 1.0-trunk
 License: Apache 2 (http://www.apache.org/licenses/LICENSE-2.0.html)
 Text Domain: sermons
*/

require_once dirname(__FILE__) . '/admin.php';

/**
 * Register the 'sermon' post type as well as the supporting taxonomies 'Sermon Series' and 'Sermon Services'.
 */
function sermons_register_post_type() {

  // make sure and register the taxonomies BEFORE the post type because the rewrite slugs overlap

  $series_args = array(
    'hierarchical' => true,
    'labels' => array(
      'name' => __('Sermon Series', 'sermons'),
      'singular_name' => __('Sermon Series', 'sermons'),
      'add_new_item' => __('Add New Sermon Series', 'sermons'),
      'edit_item' => __('Edit Sermon Series', 'sermons'),
      'new_item' => __('New Sermon Series', 'sermons'),
      'view_item' => __('View Sermon Series', 'sermons'),
      'search_items' => __('Search Sermon Series', 'sermons'),
      'not_found' => __('No sermon series found', 'sermons'),
      'not_found_in_trash' => __('No sermon series found in Trash', 'sermons'),
    ),
    'rewrite' => array(
      'slug' => get_sermon_permalink_base() . '/series'
    ),
  );
  register_taxonomy( 'sermon_series', '', $series_args );

  $service_args = array(
    'hierarchical' => true,
    'labels' => array(
      'name' => __('Sermon Services', 'sermons'),
      'singular_name' => __('Sermon Service', 'sermons'),
      'add_new_item' => __('Add New Service', 'sermons'),
      'edit_item' => __('Edit Service', 'sermons'),
      'new_item' => __('New Service', 'sermons'),
      'view_item' => __('View Service', 'sermons'),
      'search_items' => __('Search Services', 'sermons'),
      'not_found' => __('No services found', 'sermons'),
      'not_found_in_trash' => __('No services found in Trash', 'sermons'),
    ),
    'rewrite' => array(
      'slug' => get_sermon_permalink_base() . '/service'
    ),
  );
  register_taxonomy( 'sermon_service', '', $service_args );

  // setup custom post type
  $post_type_args = array(
    'labels' => array(
      'name' => __('Sermons', 'sermons'),
      'singular_name' => __('Sermon', 'sermons'),
      'add_new_item' => __('Add New Sermon', 'sermons'),
      'edit_item' => __('Edit Sermon', 'sermons'),
      'new_item' => __('New Sermon', 'sermons'),
      'view_item' => __('View Sermon', 'sermons'),
      'search_items' => __('Search Sermons', 'sermons'),
      'not_found' => __('No sermons found', 'sermons'),
      'not_found_in_trash' => __('No sermons found in Trash', 'sermons'),
    ),
    'public' => true,
    'show_ui' => true,
    'capability_type' => 'post',
    'hierarchical' => false,
    'rewrite' => array(
      'slug' => get_sermon_permalink_base(),
      'with_front' => true
    ),
    'has_archive' => true,
    'supports' => array('title', 'editor', 'author', 'thumbnail', 'excerpt', 'custom-fields'),
    'taxonomies' => array('sermon_series', 'sermon_service'),
  );
  register_post_type('sermon', $post_type_args);


  // allow sermons to have pings and enclosures
  add_action('publish_sermon', '_publish_post_hook', 5, 1);
}
add_action('init', 'sermons_register_post_type');


/**
 * Perform any post-activation tasks for the plugin such as flushing rewrite
 * rules so that permalinks will work.
 */
function sermons_activation_hook() {
  sermons_register_post_type();
  flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'sermons_activation_hook');


/**
 * Get the URL base for sermon permalinks.
 */
function get_sermon_permalink_base() {
  $base = get_option('sermon_base');
  if ( empty($base) ) {
    $base = 'sermons';
  }
  return $base;
}


/**
 * Is the query for a specific sermon?
 *
 * If the $sermon parameter is specified, this function will additionally
 * check if the query is for one of the Sermons specified.
 *
 * @param mixed $sermon Sermon ID, title, slug, or array of Sermon IDs, titles, and slugs.
 * @return bool
 */
function is_sermon( $sermon = '' ) {
  return is_singular( 'sermon' ) && is_single( $sermon );
}


/**
 * Is the query for a specific sermon series?
 *
 * If the $series parameter is specified, this function will additionally
 * check if the query is for one of the series specified.
 *
 * @param mixed $series Series ID, title, slug, or array of Series IDs, titles, and slugs.
 * @return bool
 */
function is_sermon_series( $series = '' ) {
  return is_tax( 'sermon_series', $series );
}


/**
 * Is the query for a specific sermon service?
 *
 * If the $service parameter is specified, this function will additionally
 * check if the query is for one of the service specified.
 *
 * @param mixed $service Service ID, title, slug, or array of Service IDs, titles, and slugs.
 * @return bool
 */
function is_sermon_service( $service = '' ) {
  return is_tax( 'sermon_service', $service );
}


/**
 * Get the default Bible translation to use for Biblical passages.  Defaults to 'nkjv'.
 *
 * @uses apply_filters() Calls 'sermons_default_bible_translation' on translation name
 */
function sermons_default_bible_translation() {
  return apply_filters('sermons_default_bible_translation', 'nkjv');
}


/**
 * Get the URL for the specified Biblical passage and translation.  By default, this function uses
 * Bible Gateway (biblegateway.com).
 *
 * @uses apply_filters() Calls 'sermons_passage_url' on passage URL
 *
 * @param string $passage Biblical passage to get URL for
 * @param string $translation Bible translation to create link for.  Defaults to sermons_default_bible_translation()
 * @return string URL for the passage
 */
function sermons_passage_url( $passage, $translation = null ) {
  if ( !$translation ) $translation = sermons_default_bible_translation();
  $url = 'http://www.biblegateway.com/passage/?version=' . urlencode($translation) . '&search=' . urlencode($passage);
  return apply_filters('sermons_passage_url', $url, $passage, $translation);
}


/**
 * Get the HTML link for the specified Biblical passage and translation.
 *
 * @uses sermons_passage_url()
 * @uses apply_filters() Calls 'sermons_passage_link' on passage link
 *
 * @param string $passage Biblical passage to get URL for
 * @param string $translation Bible translation to create link for.
 * @return HTML link for the passage
 */
function sermons_passage_link( $passage, $translation = null ) {
  $url = sermons_passage_url($passage, $translation);
  $link = '<a href="' . esc_url($url) . '" class="bible-link">' . esc_html($passage) . '</a>';
  return apply_filters('sermons_passage_link', $link, $passage, $translation);
}


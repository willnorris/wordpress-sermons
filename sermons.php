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
      'all_items' => __('All Sermon Series', 'sermons'),
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
      'all_items' => __('All Sermon Services', 'sermons'),
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
      'all_items' => __('All Sermons', 'sermons'),
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


/**
 * Get the URL for the audio file of the specified sermon.
 *
 * @param int|object $post Sermon ID or object.
 * @return string audio URL for the sermon, if one exists
 */
function get_sermon_audio_url( $sermon = '' ) {
  $post = get_post($sermon);
  if ( $post ) {
    return get_post_meta($post->ID, '_sermon_audio', true);
  }
}


/**
 * Get a YouTube URL for a sermon.
 *
 * @param int|object $post Sermon ID or object.
 * @param string $type YouTube URL type to get.  Supported values are: url, iframe, embed, short.
 * @return string YouTube URL for the sermon, if one exists
 */
function get_sermon_youtube_url( $sermon = '', $type = 'url' ) {
  $post = get_post($sermon);
  if ( $post ) {
    $id = get_post_meta($post->ID, '_sermon_youtube_id', true);
    if ( $id ) {
      switch ( $type ) {
        case 'iframe':
          return 'https://www.youtube.com/embed/' . $id;
        case 'embed':
          return 'https://www.youtube.com/v/' . $id;
        case 'short':
          return 'https://youtu.be/' . $id;
        case 'url':
        default:
          return 'https://www.youtube.com/watch?v=' . $id;
      }
    }
  }
}


/**
 * Get a list of sermon series, sorted in reverse chronological order by the 
 * most recent sermon in each series.
 *
 * @return array sermon series
 */
function get_active_sermon_series() {
  $active_series = get_transient('active_sermon_series');

  if ( !$active_series ) {
    $active_series = array();

    // IDs of all sermon series that have at least one sermon
    $series_ids = array_flip(get_terms('sermon_series', 'fields=ids'));

    $sermon_ids = get_posts('post_type=sermon&fields=ids&numberposts=-1');
    foreach ($sermon_ids as $sermon_id) {
      $sermon_series = get_the_terms($sermon_id, 'sermon_series');
      if ( $sermon_series) {
        foreach ($sermon_series as $series) {
          if (array_key_exists($series->term_id, $series_ids)) {
            $active_series[] = $series;
            unset($series_ids[$series->term_id]);
            if (empty($series_ids)) {
              break 2;
            }
          }
        }
      }
    }

    if ( $active_series ) {
      set_transient('active_sermon_series', $active_series, 60 * 60);
    }
  }

  return $active_series;
}


/**
 * Invalidate cached 'active_sermon_series' when a sermon is modified.
 */
function post_sermon_modified($id) {
  if ( get_post_type($id) == 'sermon' ) {
    delete_transient('active_sermon_series');
  }
}
add_action('wp_insert_post', 'post_sermon_modified');
add_action('after_delete_post', 'post_sermon_modified');


/**
 * Invalidate cached 'active_sermon_series' when a sermon taxonomy is modified.
 */
function post_sermon_taxonomy_modified($term_id, $tt_id, $taxonomy) {
  if ( $taxonomy == 'sermon_series' ) {
    delete_transient('active_sermon_series');
  }
}
add_action('created_term', 'post_sermon_taxonomy_modified', 10, 3);
add_action('edited_term', 'post_sermon_taxonomy_modified', 10, 3);
add_action('delete_term', 'post_sermon_taxonomy_modified', 10, 3);


/**
 * Get the IDs of the sermons in the specified series.
 */
function get_sermon_ids_in_series( $series_id ) {
  return get_objects_in_term($series_id, 'sermon_series');
}


function get_primary_sermon_series( $sermon_id = '' ) {
  if ( !$sermon_id && is_sermon() ) {
    $sermon_id = get_queried_object_id();
  }
  if ( $sermon_id ) {
    $sermon_series = get_the_terms($sermon_id, 'sermon_series');
    if ( $sermon_series ) {
      return array_shift($sermon_series);
    }
  }
}


/**
 * Get the ID of the thumbnail image for the specified sermon series.  The series thumbnail is 
 * identified as a media attachment that has the name "sermon-series-{slug}" 
 * which matches the slug of the sermon series.
 */
function get_sermon_series_thumbnail_id( $series_id = null ) {
  $thumbnail_id = null;

  if (!$series_id && is_sermon_series()) {
    $series_id = get_queried_object_id();
  }
  $series = get_term($series_id, 'sermon_series');

  if ($series) {
    $attachments = get_posts('post_type=attachment&name=sermon-series-' . $series->slug);
    if ($attachments) {
      $thumbnail_id = $attachments[0]->ID;
    }
  }

  return apply_filters('sermon_series_thumbnail_id', $thumbnail_id, $series_id);
}


function the_sermon_series_thumbnail( $size = 'post-thumbnail', $attr = '') {
  echo get_the_sermon_series_thumbnail(null, $size, $attr);
}


function get_the_sermon_series_thumbnail( $series_id = null, $size = 'post-thumbnail', $attr = '' ) {
  $thumbnail_id = get_sermon_series_thumbnail_id($series_id);
  $size = apply_filters( 'sermon_series_thumbnail_size', $size );
  if ( $thumbnail_id ) {
    $html = wp_get_attachment_image( $thumbnail_id, $size, false, $attr );
  } else {
    $html = '';
  }
  return apply_filters( 'sermon_series_thumbnail_html', $html, $series_id, $thumbnail_id, $size, $attr );
}


/**
 * Add appropriate metadata if the opengraph plugin is installed.
 *
 * @see http://wordpress.org/extend/plugins/opengraph/
 */
function sermon_opengraph_metadata( $metadata ) {
  if ( is_sermon() ) {
    $metadata['og:type'] = 'article';

    $audio_url = get_sermon_audio_url();
    if ( $audio_url ) {
      $metadata['og:audio'] = $audio_url;
    }

    $youtube_url = get_sermon_youtube_url('', 'embed');
    if ( $youtube_url ) {
      $metadata['og:video'] = $youtube_url;
      $metadata['og:video:type'] = 'application/x-shockwave-flash';
    }
  }

  return $metadata;
}
add_filter('opengraph_metadata', 'sermon_opengraph_metadata');


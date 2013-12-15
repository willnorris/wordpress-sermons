<?php

/**
 * Initialize admin pages.
 */
function sermons_admin_init() {
  register_setting('permalink', 'sermon_base');
  add_settings_field('sermon_base', __('Sermon base', 'sermons'), 'sermons_permalink_form', 'permalink', 'optional');
  add_settings_field('sermon_series_base', __('Sermon Series base', 'sermons'), 'sermons_series_permalink_form', 'permalink', 'optional');
  add_settings_field('sermon_speaker_base', __('Sermon Speaker base', 'sermons'), 'sermons_speaker_permalink_form', 'permalink', 'optional');
}
add_action('admin_init', 'sermons_admin_init');


/**
 * Add sermon permastruct base to the Permalinks admin page.
 */
function sermons_permalink_form() {
  global $blog_prefix;

  if ( isset($_POST['sermon_base']) ) {
    check_admin_referer('update-permalink');
    update_option('sermon_base', trim($_POST['sermon_base']));
    flush_rewrite_rules();
  }

  $base = get_option('sermon_base');

  echo $blog_prefix
    . '<input id="sermon_base" class="regular-text code" type="text" value="' . esc_attr($base) . '" name="sermon_base" />'
    . '<p class="description">Default: <code>sermons</code></p>';
}

function sermons_series_permalink_form() {
  global $blog_prefix;

  if ( isset($_POST['sermon_series_base']) ) {
    check_admin_referer('update-permalink');
    update_option('sermon_series_base', trim($_POST['sermon_series_base']));
    flush_rewrite_rules();
  }

  $base = get_option('sermon_series_base');

  echo $blog_prefix
    . '<input id="sermon_series_base" class="regular-text code" type="text" value="' . esc_attr($base) . '" name="sermon_series_base" />'
  . '<p class="description">Default: <code>sermons/series</code></p>';
}

function sermons_speaker_permalink_form() {
  global $blog_prefix;

  if ( isset($_POST['sermon_speaker_base']) ) {
    check_admin_referer('update-permalink');
    update_option('sermon_speaker_base', trim($_POST['sermon_speaker_base']));
    flush_rewrite_rules();
  }

  $base = get_option('sermon_speaker_base');

  echo $blog_prefix
    . '<input id="sermon_speaker_base" class="regular-text code" type="text" value="' . esc_attr($base) . '" name="sermon_speaker_base" />'
  . '<p class="description">Default: <code>sermons/speakers</code></p>';
}


/**
 * Register meta boxes for the 'sermons' post type.
 */
function sermons_add_meta_boxes() {
  add_meta_box('sermon-data', __('Sermon Data', 'sermons'), 'sermons_data_meta_box', 'sermon');
}
add_action('add_meta_boxes', 'sermons_add_meta_boxes');


/**
 * Content of the 'sermon data' meta box.
 */
function sermons_data_meta_box( $post ) {
  $passage = get_post_meta( $post->ID, '_sermon_passage', true );
  echo '<p>' . __('Enter the primary Bible passage(s) for this sermon.', 'sermons') . '</p>
  <input style="width:99%" type="text" name="sermon_passage" value="' . esc_attr( $passage ) . '" />';

  $audio = get_post_meta( $post->ID, '_sermon_audio', true );
  echo '<p>' . __('Enter the URL of the audio file for this sermon.', 'sermons') . '</p>
  <input style="width:99%" type="text" name="sermon_audio" value="' . esc_attr( $audio ) . '" />';

  $video = get_post_meta( $post->ID, '_sermon_youtube_id', true );
  echo '<p>' . __('Enter the YouTube video ID for this sermon. For example, <code>U6RfzbCxQqg</code>', 'sermons') . '</p>
  <input style="width:99%" type="text" name="sermon_youtube_id" value="' . esc_attr( $video ) . '" />';
}


/**
 * Save custom data.
 */
function sermons_save_post( $post_id, $post ) {
  if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
    return;
  }

  $sermon_meta_keys = array('sermon_passage', 'sermon_audio', 'sermon_youtube_id');
  wp_reset_vars( $sermon_meta_keys );

  foreach ($sermon_meta_keys as $key) {
    global $$key;
    if ( empty($$key) ) {
      delete_post_meta( $post_id, '_' . $key );
    } else {
      update_post_meta( $post_id, '_' . $key, $$key );
    }
  }

}
add_action('save_post', 'sermons_save_post', 10, 2);


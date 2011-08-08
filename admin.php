<?php

/**
 * Initialize admin pages.
 */
function sermons_admin_init() {
	register_setting('permalink', 'sermon_base');
	add_settings_field('sermon_base', __('Sermon base', 'sermons'), 'sermons_permalink_form', 'permalink', 'optional');
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

	$sermon_base = get_option('sermon_base');

	echo $blog_prefix
	  . '<input id="sermon_base" class="regular-text code" type="text" value="' . esc_attr($sermon_base) . '" name="sermon_base" />';
}

/**
 * Register meta boxes for the 'sermons' post type.
 */
function sermons_add_meta_boxes() {
	add_meta_box('sermon-audio', __('Sermon Audio', 'sermons'), 'sermons_audio_meta_box', 'sermon');
	add_meta_box('sermon-passage', __('Sermon Passage', 'sermons'), 'sermons_passage_meta_box', 'sermon');
}
//add_action('add_meta_boxes', 'sermons_add_meta_boxes');


/**
 * Content of the 'sermon audio' meta box.
 */
function sermons_audio_meta_box( $post ) {
  $audio = get_post_meta( $post->ID, '_sermon_audio', true );
  echo '<p>' . __('Enter the URL of the audio file for this sermon.', 'sermons') . '</p>
	<input style="width:99%" type="text" name="sermon_audio" value="' . esc_attr( $audio ) . '" />';
}


/**
 * Content of the 'sermon passage' meta box.
 */
function sermons_passage_meta_box( $post ) {
  $passage = get_post_meta( $post->ID, '_sermon_passage', true );
  echo '<p>' . __('Enter the primary Bible passage(s) for this sermon.', 'sermons') . '</p>
	<input style="width:99%" type="text" name="sermon_passage" value="' . esc_attr( $passage ) . '" />';
}


/**
 * Save custom data.
 */
function sermons_save_post( $post_id, $post ) {
  if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
    return;
  }

  wp_reset_vars( array('sermon_audio', 'sermon_passage') );
  global $sermon_audio, $sermon_passage;

  if ( empty($sermon_audio) ) {
    delete_post_meta( $post_id, '_sermon_audio' );
  } else {
    update_post_meta( $post_id, '_sermon_audio', $sermon_audio );
  }

  if ( empty($sermon_passage) ) {
    delete_post_meta( $post_id, '_sermon_passage' );
  } else {
    update_post_meta( $post_id, '_sermon_passage', $sermon_passage );
  }
}
add_action('save_post', 'sermons_save_post', 10, 2);

 
?>

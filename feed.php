<?php

function sermons_audio_enclosure($enclosures, $post_id) {
  error_log( 'enclosures(1) = ' . print_r($enclosures, true) );
  $post = get_post($post_id);
  if ( $post && $post->post_type == 'sermon' ) {
    if ( $audio = get_post_meta($post->ID, 'sermon-audio', true) ) {
      $enclosures[] = $audio;
    }
  }
  error_log( 'enclosures(2) = ' . print_r($enclosures, true) );
  return $enclosures;
}
//add_filter('get_enclosed', 'sermons_audio_enclosure', 10, 2);

function sermons_save_audio_enclosure() {
}


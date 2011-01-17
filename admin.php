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

?>

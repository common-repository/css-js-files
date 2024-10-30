<?php
/*
Plugin Name: CSS JS Files
Plugin URI: https://wordpress.org/plugins/css-js-files/
Description: Add CSS files and/or CSS custom rules to any single page or post or globally
Version: 1.5.2
Author: James Low
Author URI: http://jameslow.com
License: MIT License
*/

namespace CSS_JS_Files;

class CSS_JS_Files {
	public static function add_hooks() {
		/* Define the custom box */
		add_action('add_meta_boxes', array('\CSS_JS_Files\CSS_JS_Files', 'add_custom_box'));
		/* backwards compatible (before WP 3.0) */
		add_action('admin_init', array('\CSS_JS_Files\CSS_JS_Files', 'add_custom_box'), 1);
		/* Save the selected css files and the custom css rules */
		add_action('save_post', array('\CSS_JS_Files\CSS_JS_Files', 'save_post'));
		/* Enqueue styles ans function in editor page/post */
		add_action('admin_enqueue_scripts', array('\CSS_JS_Files\CSS_JS_Files', 'admin_enqueue_scripts'));
		/* Put the css files selected */
		add_action('wp_enqueue_scripts', array('\CSS_JS_Files\CSS_JS_Files', 'wp_enqueue_scripts'));
		/* Add the custom css rules */
		add_action('wp_head', array('\CSS_JS_Files\CSS_JS_Files', 'wp_head'));
		add_action('wp_body_open', array('\CSS_JS_Files\CSS_JS_Files', 'wp_body'));
		add_action('wp_footer', array('\CSS_JS_Files\CSS_JS_Files', 'wp_footer'));
		/* Delete options when post is deleted */
		//add_action('delete_post', array('\CSS_JS_Files\CSS_JS_Files', 'delete_post'));
		/* Delete all options when the plugin is uninstalling */
		//register_uninstall_hook(plugin_dir_path( __FILE__ ).'uninstall.php', 'uninstall');
		add_action('admin_head', array('\CSS_JS_Files\CSS_JS_Files', 'admin_head'));
		
		add_action('admin_menu', array('\CSS_JS_Files\CSS_JS_Files', 'admin_menu'));
		add_option('css_js_files_css_links', '', false, true);
		add_option('css_js_files_css_files', true, false, true);
		add_option('css_js_files_css_rules', '', false, true);
		add_option('css_js_files_css_admin', '', false, true);
		add_option('css_js_files_js_links', '', false, true);
		add_option('css_js_files_js_files', true, false, true);
		add_option('css_js_files_js_rules', '', false, true);
		add_option('css_js_files_js_admin', '', false, true);
		add_option('css_js_files_head_rules', '', false, true);
		add_option('css_js_files_body_rules', '', false, true);
		add_option('css_js_files_footer_rules', '', false, true);
		add_option('css_js_files_path', substr(get_template_directory(), strlen(WP_CONTENT_DIR)+1), false, true);
	}
	public static function admin_menu() {
		add_menu_page('CSS JS Files', 'CSS/JS Files', 'manage_options', 'css-js-files', array('\CSS_JS_Files\CSS_JS_Files', 'menu_page'));
		add_submenu_page('css-js-files', 'CSS JS Files Editor', 'Editor', 'manage_options', 'css-js-files-editor', array('\CSS_JS_Files\CSS_JS_Files', 'editor_page'));
	}
	public static function menu_page() {
		echo '<div class="wrap">';
		echo '<h2>CSS/JS Files</h2>';
		
		if (!current_user_can( 'manage_options' ))  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		if (self::has_nounce()) {
			if (!self::verify_nounce()) {
				echo 'Could not save, please login and try again.';
			} else {
				$csslinks = self::post_field('css_js_files_css_links');
				$cssfiles = implode(',', rest_sanitize_array($_POST['css_js_files_css_files']));
				$cssrules = self::post_field('css_js_files_css_rules');
				$cssadmin = self::post_field('css_js_files_css_admin');
				$jslinks = self::post_field('css_js_files_js_links');
				$jsfiles = implode(',', rest_sanitize_array($_POST['css_js_files_js_files']));
				$jsrules = self::post_field('css_js_files_js_rules');
				$jsadmin = self::post_field('css_js_files_js_admin');
				$headrules = wp_unslash($_POST['css_js_files_head_rules']);
				$bodyrules = wp_unslash($_POST['css_js_files_body_rules']);
				$footerrules = wp_unslash($_POST['css_js_files_footer_rules']);
				$path = self::post_field('css_js_files_path');
				
				update_option('css_js_files_css_links', $csslinks, true);
				update_option('css_js_files_css_files', $cssfiles, true);
				update_option('css_js_files_css_rules', $cssrules, true);
				update_option('css_js_files_css_admin', $cssadmin, true);
				update_option('css_js_files_js_links', $jslinks, true);
				update_option('css_js_files_js_files', $jsfiles, true);
				update_option('css_js_files_js_rules', $jsrules, true);
				update_option('css_js_files_js_admin', $jsadmin, true);
				update_option('css_js_files_head_rules', $headrules, true);
				update_option('css_js_files_body_rules', $bodyrules, true);
				update_option('css_js_files_footer_rules', $footerrules, true);
				if (strpos($path, '../') === false) {
					update_option('css_js_files_path', $path, true);
				}
			}
		}
		
		echo '<form method="post" action="">';
		echo '<div align="right"><button type="submit" class="button button-primary button-large">Update</button></div>';
		echo '<br /><h3>CSS/JS Location: wp-content/<input name="css_js_files_path" value="'.self::get_path_option().'" /></h3><br />';
		self::inner_custom_box();
		echo '<h2>Head/Body/Footer</h2>';
		echo '<div>These hooks, particularly the wp_body_open hook, may not be supported by all themes</div>';
		self::box('head');
		self::box('body');
		self::box('footer');
		echo '</form>';
		echo '</div>';
	}
	public static function box($type) {
		$rules = get_option('css_js_files_'.$type.'_rules', true);
		echo '<div class="row"><label for="css-js-files-'.$type.'-rules">Enter '.$type.' HTML here:</label><br /><textarea class="css-js-files-text" id="css-js-files-'.$type.'-rules" name="css_js_files_'.$type.'_rules">'.htmlentities($rules).'</textarea></div>';
	}
	public static function verify_nounce() {
		return wp_verify_nonce(sanitize_key(wp_unslash($_POST['css_js_files_chrgiga'])), plugin_basename( __FILE__ ));
	}
	public static function nounce_field() {
		wp_nonce_field(plugin_basename( __FILE__ ), 'css_js_files_chrgiga');
	}
	public static function has_nounce() {
		return isset($_POST['css_js_files_chrgiga']);
	}
	public static function post_field($key) {
		return sanitize_textarea_field(wp_unslash($_POST[$key]));
	}
	public static function editor_page() {
		$file = isset($_GET['file']) ? sanitize_text_field($_GET['file']) : '';
		$lower = strtolower($file);
		$hasfile = $file != '' && str_starts_with($file, get_option('css_js_files_path')) && strpos($file, '../') === false
			&& (strpos($lower, '.css') !== false || strpos($lower, '.js') !== false);
		$path = $hasfile ? WP_CONTENT_DIR.'/'.$file : '';
		if (!current_user_can( 'manage_options' ))  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		echo '<div class="wrap">';
		echo '<h2>CSS/JS Editor</h2>';

		//Save File
		if (self::has_nounce()) {
			if (!self::verify_nounce()) {
				echo 'Could not save, please login and try again.';
			} else {
				self::write_file($path, self::post_field('css_js_files_content'));
			}
		}
		
		if ($file != '' && !$hasfile || !file_exists($path)) {
			echo '<div class="notice notice-error">
				<p>Invalid file path.</p>
			</div>';
			$content = '';
		} else {
			$content = $hasfile ? self::read_file($path) : '';
		}

		//Form
		echo '<form action="" method="post">';
		self::nounce_field();
		echo '<div align="right"><button type="submit" class="button button-primary button-large'.($hasfile?'':' button-disabled').'"'.($hasfile?'':' disabled').'>Save</button></div>';
		echo self::get_file(array($file), 'all');
		echo '<br /><textarea class="css-js-files-text css-js-files-text-full" id="css-js-files-content" name="css_js_files_content">'.htmlentities($content).'</textarea>';
		echo '</form>';
		
		echo '</div>';
	}
	public static function read_file($name) {
		$file = fopen($name, 'r');
		if (!$file) {
			return null;
		}
		$data = fread($file,filesize($name));
		fclose($file);
		return $data;
	}
	public static function write_file($name, $data) {
		$file = fopen($name, 'w');
		fwrite($file, $data);
		fclose($file);
	}
	public static function admin_enqueue_scripts() {
		//For post and options page only
		$screen = get_current_screen();
		if (strpos($_SERVER['REQUEST_URI'],'post.php') !== false || strpos($screen->base, 'css-js-files')  !== false || strpos($screen->base, 'css-js-files-editor') !== false) {
			wp_enqueue_style('css-js-files.css', plugins_url('css/css-js-files.css', __FILE__));
			wp_enqueue_script('css-js-files.js', plugins_url('js/css-js-files.js', __FILE__), array(), '1.0.0', true);
		}
	}
	public static function get_file($selectfiles, $type) {
		$upper = $type == 'all' ? 'No' : strtoupper($type);
		// Recursive function for read directories and subdirectories
		$path_template = self::get_dir();
		$files = array();
		self::read_dirs($path_template, $files, $type);
		
		$select = '';
		$first = true;
		foreach($selectfiles as $file) {
			if ($first || $file != '') {
				$option = '';
				$option_group = '';
				$select .= '<select name="css_js_files_'.$type.'_files[]">';
				foreach ($files as $dir => $list) {
					$name_dir = str_replace($path_template, '', $dir);
					$name_dir = $name_dir == '' ? '/' : $name_dir;
					if ($name_dir != '' && $option_group != $name_dir) {
						$option .= '<optgroup label="'.$name_dir.'">';
						$option_group = $name_dir;
					} else {
						$option .= '</optgroup>';
					}
					asort($list);
					foreach ($list as $row) {
						$relative = substr($row, strlen(WP_CONTENT_DIR)+1);
						$selected = $relative == $file ? 'selected="selected"' : '';
						$option .= '<option value="'.$relative.'" '.$selected.'>'.basename($row).'</option>';
					}
				}
				$option = $option == '' ? '<option value="">'.$upper.' files not found</option>' : ($type == 'all' ? '<option value="">Select File</option>' : '<option value="">Without '.$upper.' file</option>').$option.'</optgroup>';
				$select .= $option.'</select>';
			}
			$first = false;
		}
		return $select;
	}
	public static function read_dirs($directory, &$files, $type) {
		if (is_dir($directory)) {
			if ($open_dir = opendir($directory)) {
				while (($file = readdir($open_dir)) !== false) {
					if ($file != '.' AND $file != '..') {
						// Verify if is directory or file
						if (is_dir( $directory.'/'.$file)) {
							self::read_dirs($directory.'/'.$file , $files, $type);
						} else {
							// Ready File
							$explodefile = explode('.', $file);
							if (is_file($directory.'/'.$file) && end($explodefile) == $type || ($type == 'all' && (end($explodefile) == 'css' || end($explodefile) == 'js'))) {
								$files[dirname($directory.'/'.$file)][] = $directory.'/'.$file;
							}
						}
					}
				}
				closedir($open_dir);
			}
		}
	}
	public static function add_custom_box() {
		$screens = array( 'post', 'page' );
		foreach ($screens as $screen) {
			add_meta_box(
				'css-js-files',
				__('Select CSS/JS files and/or write your custom CSS/JS', 'cssjsfles'),
				array('\CSS_JS_Files\CSS_JS_Files', 'inner_custom_box'),
				$screen
			);
		}
	}
	public static function inner_custom_box($post = null) {
		// Use nonce for verification
		self::nounce_field();
		self::generic_box($post, 'css');
		self::generic_box($post, 'js');
	}
	public static function generic_box($post, $type) {
		// The actual fields for data entry
		// Use get_post_meta to retrieve an existing value from the database and use the value for the form
		if ($post != null) {
			$links = get_post_meta($post->ID, 'css_js_files_'.$type.'_links', true);
			$files = get_post_meta($post->ID, 'css_js_files_'.$type.'_files', true);
			$rules = get_post_meta($post->ID, 'css_js_files_'.$type.'_rules', true);
			$admin = null;
		} else {
			$links = get_option('css_js_files_'.$type.'_links', true);
			$files = get_option('css_js_files_'.$type.'_files', true);
			$rules = get_option('css_js_files_'.$type.'_rules', true);
			$admin = get_option('css_js_files_'.$type.'_admin', true);
		}
		$upper = strtoupper($type);
		echo '<h2>'.strtoupper($type).'</h2>';
		echo '<div class="row"><label>Select '.$upper.' files</label><br />'.self::get_file(explode(',', $files), $type).' <button type="button" id="css-js-files-'.$type.'-button" class="css-js-files-button button button-primary button-large">Add other file</button><hr /></div>';
		echo '<div class="row"><label for="css-js-files-'.$type.'-rules">Write your custom '.$upper.'</label><br /><textarea class="css-js-files-text" id="css-js-files-'.$type.'-rules" name="css_js_files_'.$type.'_rules">'.htmlentities($rules).'</textarea></div>';
		echo '<div class="row"><label for="css-js-files-'.$type.'-rules">Enter '.$upper.' links here</label><br /><textarea class="css-js-files-text" id="css-js-files-'.$type.'-links" name="css_js_files_'.$type.'_links">'.htmlentities($links).'</textarea></div>';
		if ($post == null) {
			echo '<div class="row"><label for="css-js-files-'.$type.'-admin">Enter admin '.$upper.' here</label><br /><textarea class="css-js-files-text" id="css-js-files-'.$type.'-admin" name="css_js_files_'.$type.'_admin">'.htmlentities($admin).'</textarea></div>';
		}
	}
	public static function save_post($post_id) {
		// First we need to check if the current user is authorised to do this action.
		if (isset($_POST['post_type']) && 'page' == sanitize_text_field($_POST['post_type'])) {
			if (!current_user_can( 'edit_page', $post_id)) {
				return;
			}
		} else {
			if (!current_user_can('edit_post', $post_id)) {
				return;
			}
		}
		
		// Secondly we need to check if the user intended to change this value.
		if (!self::has_nounce() || !self::verify_nounce()) {
			return;
		}
		
		// Thirdly we can save the value to the database
		$post_ID = sanitize_textarea_field($_POST['post_ID']);
		$csslinks = self::post_field('css_js_files_css_links');
		$cssfiles = implode(',', rest_sanitize_array($_POST['css_js_files_css_files']));
		$cssrules = self::post_field('css_js_files_css_rules');
		$jslinks = self::post_field('css_js_files_js_links');
		$jsfiles = implode(',', rest_sanitize_array($_POST['css_js_files_js_files']));
		$jsrules = self::post_field('css_js_files_js_rules');
		
		add_post_meta($post_ID, 'css_js_files_css_links', $csslinks, true) or update_post_meta($post_ID, 'css_js_files_css_links', $csslinks);
		add_post_meta($post_ID, 'css_js_files_css_files', $cssfiles, true) or update_post_meta($post_ID, 'css_js_files_css_files', $cssfiles);
		add_post_meta($post_ID, 'css_js_files_css_rules', $cssrules, true) or update_post_meta($post_ID, 'css_js_files_css_rules', $cssrules);
		add_post_meta($post_ID, 'css_js_files_js_links', $jslinks, true) or update_post_meta($post_ID, 'css_js_files_js_links', $jslinks);
		add_post_meta($post_ID, 'css_js_files_js_files', $jsfiles, true) or update_post_meta($post_ID, 'css_js_files_js_files', $jsfiles);
		add_post_meta($post_ID, 'css_js_files_js_rules', $jsrules, true) or update_post_meta($post_ID, 'css_js_files_js_rules', $jsrules);
	}
	public static function delete_post() {
		global $post;
		if ('trash' == get_post_status($post)) {
			delete_post_meta($post->ID, 'css_js_files_css_links');
			delete_post_meta($post->ID, 'css_js_files_css_files');
			delete_post_meta($post->ID, 'css_js_files_css_rules');
			delete_post_meta($post->ID, 'css_js_files_js_links');
			delete_post_meta($post->ID, 'css_js_files_js_files');
			delete_post_meta($post->ID, 'css_js_files_js_rules');
		}
	}
	public static function is_post() {
		return is_single() || is_page() || ((is_front_page() || is_home()) && get_option('show_on_front') == 'page');
	}
	public static function wp_head() {
		self::insert_css_links('css_js_files_css_links');
		self::insert_js_links('css_js_files_js_links');
		self::insert_rules('css_js_files_css_rules', '<style type="text/css">','</style>');
		self::insert_rules('css_js_files_js_rules', '<script type="text/javascript">','</script>');
		echo get_option('css_js_files_head_rules');
	}
	public static function admin_head() {
		self::insert_rules('css_js_files_css_admin', '<style type="text/css">','</style>');
		self::insert_rules('css_js_files_js_admin', '<script type="text/javascript">','</script>');
	}
	public static function wp_body() {
		echo get_option('css_js_files_body_rules');
	}
	public static function wp_footer() {
		echo get_option('css_js_files_footer_rules');
	}
	public static function get_rules($rules) {
		if (!is_array($rules) && $rules != '' && $rules != null) {
			return $rules;
		} elseif (is_array($rules) && count($rules) && $rules[0] != '') {
			return $rules[0];
		}
	}
	public static function echo_rules($rules) {
		echo self::get_rules($rules);
	}
	public static function insert_rules($key, $before, $after) {
		echo '<!-- CSS JS Files (custom rules) -->';
		echo "\n".$before."\n";
		$rules = get_option($key);
		self::echo_rules($rules);
		if (self::is_post()) {
			global $post;
			self::echo_rules(get_post_meta($post->ID, $key));
		}
		echo "\n".$after."\n";
	}
	public static function insert_js_links($key) {
		self::echo_js_links(get_option($key));
		if (self::is_post()) {
			global $post;
			self::echo_js_links(get_post_meta($post->ID, $key));
		}
	}
	public static function insert_css_links($key) {
		self::echo_css_links(get_option($key));
		if (self::is_post()) {
			global $post;
			self::echo_css_links(get_post_meta($post->ID, $key));
		}
	}
	public static function echo_js_links($rules) {
		$links = explode("\n", self::get_rules($rules));
		foreach ($links as $link) {
			if ($link != '') {
				echo '<script src="'.esc_attr($link).'" type="text/javascript"></script>'."\n";
			}
		}
	}
	public static function echo_css_links($rules) {
		$links = explode("\n", self::get_rules($rules));
		foreach ($links as $link) {
			if ($link != '') {
				echo '<link href="'.esc_attr($link).'" rel="stylesheet" type="text/css"></link>'."\n";
			}
		}
	}
	public static function wp_enqueue_scripts() {
		self::insert_css();
		self::insert_js();
	}
	public static function trailing_slash($path) {
		return rtrim($path, '/');
	}
	public static function get_path_option() {
		return self::trailing_slash(get_option('css_js_files_path'));
	}
	public static function get_dir() {
		return WP_CONTENT_DIR.'/'.self::get_path_option();
	}
	public static function get_url() {
		return content_url().'/'.self::get_path_option();
	}
	public static function insert_css() {
		self::insert_css_files(array(get_option('css_js_files_css_files')));
		if (self::is_post()) {
			global $post;
			self::insert_css_files(get_post_meta($post->ID, 'css_js_files_css_files'));
		}
	}
	public static function insert_css_files($files) {
		if (is_array($files) && count($files) && $files[0] != '') {
			foreach (explode(',', $files[0]) as $file) {
				if ($file != '') {
					$uri = content_url().'/'.$file;
					wp_enqueue_style('css-js-files-'.str_replace('.min', '', basename($file, '.css')), $uri);
				}
			}
		}
	}
	public static function insert_js() {
		self::insert_js_files(array(get_option('css_js_files_js_files')));
		if (self::is_post()) {
			global $post;
			self::insert_js_files(get_post_meta($post->ID, 'css_js_files_js_files'));
		}
	}
	public static function insert_js_files($files) {
		if (is_array($files) && count($files) && $files[0] != '') {
			foreach (explode(',', $files[0]) as $file) {
				if ($file != '') {
					$uri = content_url().'/'.$file;
					wp_enqueue_script('css-js-files-'.str_replace('.min', '', basename($file, '.js')), $uri);
				}
			}
		}
	}
}

CSS_JS_Files::add_hooks();
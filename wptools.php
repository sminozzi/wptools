<?php
/*
Plugin Name: wptools
Plugin URI:  https://BillMinozzi.com
Description: PHP error log reader tool
Version:     1.0
Author:      Bill Minozzi
Plugin URI:  https://BillMinozzi.com
Domain Path: /language
License:     GPL3
License URI: https://www.gnu.org/licenses/gpl-3.0.html
*/
/*
// is_multisite()
10/11/2014 - A 'must-use plugin' (also known as 'MU-Plugins') is a WordPress plugin that resides in /wp-content/mu-plugins and will always be activated by ...
*/
add_action('admin_menu', 'wptools_menu');
function wptools_menu()
{
	add_menu_page("wptools", "wptools", "manage_options", "wptools display", 'wotools_options');
}
function wotools_options()
{
	$wotools_count = 0;
	define('WPTOOLSPLUGINPATH', plugin_dir_path(__file__));
	$wotools_themePath = get_theme_root();
	$wotools_folders = array(
		ABSPATH . "error_log",
		ABSPATH . "php_errorlog",
		WPTOOLSPLUGINPATH . "/error_log",
		WPTOOLSPLUGINPATH . "/php_errorlog",
		$wotools_themePath . "/error_log",
		$wotools_themePath . "/php_errorlog"
	);
	$wotools_admin_path = str_replace(get_bloginfo('url') . '/', ABSPATH, get_admin_url());
	array_push($wotools_folders, $wotools_admin_path . "/error_log");
	array_push($wotools_folders, $wotools_admin_path . "/php_errorlog");
	$wotools_plugins = array_slice(scandir(WPTOOLSPLUGINPATH), 2);
	foreach ($wotools_plugins as $wotools_plugin) {
		if (is_dir(WPTOOLSPLUGINPATH . "/" . $wotools_plugin)) {
			array_push($wotools_folders, WPTOOLSPLUGINPATH . "/" . $wotools_plugin . "/error_log");
			array_push($wotools_folders, WPTOOLSPLUGINPATH . "/" . $wotools_plugin . "/php_errorlog");
		}
	}
	$wotools_themes = array_slice(scandir($wotools_themePath), 2);
	foreach ($wotools_themes as $wotools_theme) {
		if (is_dir($wotools_themePath . "/" . $wotools_theme)) {
			array_push($wotools_folders, $wotools_themePath . "/" . $wotools_theme . "/error_log");
			array_push($wotools_folders, $wotools_themePath . "/" . $wotools_theme . "/php_errorlog");
		}
	}
	echo '<h2>';
	echo __("We show the lasts 200 lines of the log files.", "wptools");
	echo '</h2>';
	echo '<h4>';
	echo __("For bigger files, download and open them in your local computer.", "wptools");
	echo '</h4>';
	foreach ($wotools_folders as $wotools_folder) {
		foreach (glob($wotools_folder) as $wotools_filename) {
			if (strpos($wotools_filename, 'backup') != true) {
				echo '<hr>';
				echo '<strong>';
				echo wotools_sizeFilter(filesize($wotools_filename));
				echo ' - ';
				echo $wotools_filename;
				echo '</strong>';
				$wotools_count++;
				$marray = wptools_read_file($wotools_filename, 200);
				if (count($marray) > 0) {
					echo '<textarea style="width:100%" id="anti_hacker" rows="6">';
					for ($i = 0; $i < count($marray); $i++) {
						echo $marray[$i];
						echo PHP_EOL;
					}
					echo '</textarea>';
				}
				echo '<br />';
			}
		}
	}
	echo "<p>".__("Log Files found", "wptools").": " . $wotools_count . "</p>";
}
function wptools_read_file($file, $lines)
{
	$handle = fopen($file, "r");
	$linecounter = $lines;
	$pos = -2;
	$beginning = false;
	$text = array();
	while ($linecounter > 0) {
		$t = " ";
		while ($t != "\n") {
			if (fseek($handle, $pos, SEEK_END) == -1) {
				$beginning = true;
				break;
			}
			$t = fgetc($handle);
			$pos--;
		}
		$linecounter--;
		if ($beginning)
			rewind($handle);
		$text[$lines - $linecounter - 1] = fgets($handle);
		if ($beginning)
			break;
	}
	fclose($handle);
	// return array_reverse($text); // array_reverse is optional: you can also just return the $text array which consists of the file's lines.
    return ($text);
}
function wotools_sizeFilter($bytes)
{
	$label = array('Bytes', 'KB', 'MB', 'GB', 'TB', 'PB');
	for ($i = 0; $bytes >= 1024 && $i < (count($label) - 1); $bytes /= 1024, $i++);
	return (round($bytes, 2) . " " . $label[$i]);
}
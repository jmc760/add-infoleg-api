<?php

/**
 * Plugin Name:       Add Infoleg Api
 * Description:       Provee bloques para mostrar datos de la API de InfoLeg.
 * Version:           1.0.0
 * Requires at least: 6.7
 * Requires PHP:      7.4
 * Author:            Juan Manuel Cerdeira
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       add-infoleg-api
 *
 * @package AddInfolegApi
 */

if (! defined('ABSPATH')) {
	exit;
}

// Include the main class file.
require_once plugin_dir_path(__FILE__) . 'includes/add-infoleg-api-class.php';

function run_add_infoleg_api_plugin()
{
	// We pass __FILE__ (the path to this main file) to the class constructor.
	// This is crucial for properly registering activation hooks.
	new Add_Infoleg_API_Plugin(__FILE__);
}

run_add_infoleg_api_plugin();

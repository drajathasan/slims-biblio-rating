<?php
/**
 * Plugin Name: Biblio Rating
 * Plugin URI: https://github.com/drajathasan/slims-biblio-rating
 * Description: Give some rating at detail page
 * Version: 1.0.0
 * Author: Drajat Hasan
 * Author URI: https://github.com/drajathasan/
 */

// get plugin instance
$plugin = \SLiMS\Plugins::getInstance();

// registering menus or hook
$plugin->registerMenu("opac", "show_detail", __DIR__ . "/index.php");
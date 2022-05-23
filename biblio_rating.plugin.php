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
$plugin->registerMenu("opac", "show_detail", __DIR__ . "/pages/show_detail.inc.php");
$plugin->registerMenu("reporting", "Rating Chart", __DIR__ . "/pages/chart.php");
$plugin->registerMenu("reporting", "Rating Report", __DIR__ . "/pages/report.php");
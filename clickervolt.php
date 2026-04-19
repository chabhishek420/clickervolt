<?php
/**
 * Plugin Name: ClickerVolt
 * Plugin URI:  https://clickervolt.com/
 * Description: Advanced click tracking, link cloaking and affiliate campaigns management made easy.
 * Version:     1.146
 * Author:      ClickerVolt.com
 * License:     GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Requires at least: 6.5
 * Requires PHP: 8.2
 * Tested up to: 6.7
 */

if (!defined('ABSPATH')) {
    exit;
}

define('CLICKERVOLT_VERSION', '1.146');
define('CLICKERVOLT_PLUGIN_FILE', __FILE__);
define('CLICKERVOLT_PLUGIN_DIR', __DIR__);
define('CLICKERVOLT_MIN_PHP_VERSION', '8.2');
define('CLICKERVOLT_MIN_WP_VERSION', '6.5');

$autoloadPath = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
}

require_once __DIR__ . '/src/Core/Plugin.php';

\ClickerVolt\Core\Plugin::bootstrap();

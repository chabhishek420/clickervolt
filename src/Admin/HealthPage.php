<?php

namespace ClickerVolt\Admin;

class HealthPage
{
    public static function render()
    {
        global $wp_version;

        $redisExtension = extension_loaded('redis') ? 'Available' : 'Not available';
        $redisObjectCache = function_exists('wp_cache_get') && defined('WP_CACHE') && WP_CACHE ? 'Enabled' : 'Disabled';

        echo '<div class="wrap">';
        echo '<h1>ClickerVolt Health Check</h1>';
        echo '<table class="widefat striped" style="max-width: 920px;">';
        echo '<tbody>';

        self::row('Plugin version', defined('CLICKERVOLT_VERSION') ? CLICKERVOLT_VERSION : 'unknown');
        self::row('WordPress version', (string) $wp_version);
        self::row('PHP version', PHP_VERSION);
        self::row('Redis extension', $redisExtension);
        self::row('Object cache', $redisObjectCache);
        self::row('Memory limit', (string) ini_get('memory_limit'));
        self::row('Max execution time', (string) ini_get('max_execution_time'));

        echo '</tbody>';
        echo '</table>';
        echo '</div>';
    }

    private static function row($label, $value)
    {
        echo '<tr><th scope="row">' . esc_html((string) $label) . '</th><td>' . esc_html((string) $value) . '</td></tr>';
    }
}

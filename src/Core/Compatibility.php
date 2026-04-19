<?php

namespace ClickerVolt\Core;

class Compatibility
{
    public static function isPhpCompatible()
    {
        return version_compare(PHP_VERSION, CLICKERVOLT_MIN_PHP_VERSION, '>=');
    }

    public static function isWpCompatible()
    {
        global $wp_version;

        if (!isset($wp_version)) {
            return true;
        }

        return version_compare($wp_version, CLICKERVOLT_MIN_WP_VERSION, '>=');
    }

    public static function isCompatible()
    {
        return self::isPhpCompatible() && self::isWpCompatible();
    }

    public static function getCompatibilityErrors()
    {
        $errors = [];

        if (!self::isPhpCompatible()) {
            $errors[] = sprintf('PHP %s+ is required. Current: %s.', CLICKERVOLT_MIN_PHP_VERSION, PHP_VERSION);
        }

        global $wp_version;
        if (!self::isWpCompatible()) {
            $errors[] = sprintf('WordPress %s+ is required. Current: %s.', CLICKERVOLT_MIN_WP_VERSION, (string) $wp_version);
        }

        return $errors;
    }

    public static function maybeDeactivateAndShowNotice()
    {
        if (self::isCompatible()) {
            return;
        }

        if (function_exists('deactivate_plugins')) {
            deactivate_plugins(plugin_basename(CLICKERVOLT_PLUGIN_FILE));
        }

        add_action('admin_notices', [self::class, 'renderAdminNotice']);
    }

    public static function renderAdminNotice()
    {
        $errors = self::getCompatibilityErrors();
        if (empty($errors)) {
            return;
        }

        echo '<div class="notice notice-error"><p><strong>ClickerVolt was deactivated:</strong> ' . esc_html(implode(' ', $errors)) . '</p></div>';
    }
}

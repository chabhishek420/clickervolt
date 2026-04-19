<?php

namespace ClickerVolt\Core;

class Plugin
{
    public static function bootstrap()
    {
        register_activation_hook(CLICKERVOLT_PLUGIN_FILE, ['ClickerVolt\\Setup', 'onActivate']);
        register_deactivation_hook(CLICKERVOLT_PLUGIN_FILE, ['ClickerVolt\\Setup', 'onDeactivate']);
        register_uninstall_hook(CLICKERVOLT_PLUGIN_FILE, ['ClickerVolt\\Setup', 'onDelete']);

        Compatibility::maybeDeactivateAndShowNotice();
        if (!Compatibility::isCompatible()) {
            return;
        }

        if (function_exists('\\ClickerVolt\\cli_fs')) {
            \ClickerVolt\cli_fs()->set_basename(true, CLICKERVOLT_PLUGIN_FILE);
            return;
        }

        self::requireLegacyFiles();
        self::registerHooks();
    }

    private static function requireLegacyFiles()
    {
        $root = CLICKERVOLT_PLUGIN_DIR;

        require_once $root . '/freemiusSetup.php';
        require_once $root . '/admin/setup.php';
        require_once $root . '/admin/cron.php';
        require_once $root . '/admin/ajax/ajaxLinks.php';
        require_once $root . '/admin/ajax/ajaxSources.php';
        require_once $root . '/admin/ajax/ajaxStats.php';
        require_once $root . '/admin/ajax/ajaxSearches.php';
        require_once $root . '/admin/ajax/ajaxFeed.php';
        require_once $root . '/admin/ajax/ajaxCVSettings.php';
        require_once $root . '/admin/reporting/segment.php';
        require_once $root . '/admin/reporting/handlers/handlerWholePath.php';
        require_once $root . '/admin/api/api.php';
        require_once $root . '/redirect/dynamicTokens.php';
        require_once $root . '/pixel/pixelInfo.php';
        require_once $root . '/redirect/router.php';
        require_once $root . '/redirect/jsTracking/jsTracking.php';
        require_once $root . '/redirect/rules/rules.php';
        require_once $root . '/db/tableSourceTemplates.php';
        require_once $root . '/utils/dataProxy.php';
    }

    private static function registerHooks()
    {
        add_action('plugins_loaded', ['ClickerVolt\\Setup', 'onLoaded']);
        add_action('rest_api_init', ['ClickerVolt\\Api', 'registerRoutes']);

        add_action('rest_api_init', static function () {
            // Register early to intercept post inserts/updates.
            add_filter('wp_unique_post_slug', static function ($slug, $post_ID, $post_status, $post_type, $post_parent, $original_slug) {
                $link = \ClickerVolt\DataProxy::getLink($slug);
                if ($link !== null) {
                    if ($post_status !== \ClickerVolt\AjaxLinks::STATUS_SLUG_CHECKING || $link->getId() !== $post_ID) {
                        $slug .= '-' . time();
                    }
                }

                return $slug;
            }, 10, 6);
        });

        $isDoingAjax = function_exists('wp_doing_ajax') ? wp_doing_ajax() : (defined('DOING_AJAX') && DOING_AJAX);
        $isDoingCron = function_exists('wp_doing_cron') ? wp_doing_cron() : (defined('DOING_CRON') && DOING_CRON);

        if (!is_admin() && !$isDoingAjax && !$isDoingCron) {
            return;
        }

        add_action('admin_enqueue_scripts', ['ClickerVolt\\Setup', 'enqueueScripts']);
        add_action('admin_menu', ['ClickerVolt\\Setup', 'addMainMenu']);
        add_action('in_admin_footer', ['ClickerVolt\\Setup', 'addFooterElements']);

        add_action('wp_ajax_clickervolt_save_link', ['ClickerVolt\\AjaxLinks', 'saveLinkAjax']);
        add_action('wp_ajax_clickervolt_get_link', ['ClickerVolt\\AjaxLinks', 'getLinkAjax']);
        add_action('wp_ajax_clickervolt_get_link_by_slug', ['ClickerVolt\\AjaxLinks', 'getLinkBySlugAjax']);
        add_action('wp_ajax_clickervolt_delete_link_by_slug', ['ClickerVolt\\AjaxLinks', 'deleteLinkBySlugAjax']);
        add_action('wp_ajax_clickervolt_get_all_slugs', ['ClickerVolt\\AjaxLinks', 'getAllSlugsAjax']);
        add_action('wp_ajax_clickervolt_get_aida_script_template', ['ClickerVolt\\AjaxLinks', 'getAIDAScriptTemplateAjax']);
        add_action('wp_ajax_clickervolt_save_source_template', ['ClickerVolt\\AjaxSources', 'saveSourceAjax']);
        add_action('wp_ajax_clickervolt_get_sources', ['ClickerVolt\\AjaxSources', 'getAllSourcesAjax']);
        add_action('wp_ajax_clickervolt_delete_source_template', ['ClickerVolt\\AjaxSources', 'deleteSourceAjax']);
        add_action('wp_ajax_clickervolt_process_clicks_queue', ['ClickerVolt\\AjaxStats', 'processClicksQueueAjax']);
        add_action('wp_ajax_clickervolt_get_stats', ['ClickerVolt\\AjaxStats', 'getStatsAjax']);
        add_action('wp_ajax_clickervolt_get_clicklog', ['ClickerVolt\\AjaxStats', 'getClickLogAjax']);
        add_action('wp_ajax_clickervolt_search_isps', ['ClickerVolt\\AjaxSearches', 'searchISPsAjax']);
        add_action('wp_ajax_clickervolt_search_regions', ['ClickerVolt\\AjaxSearches', 'searchRegionsAjax']);
        add_action('wp_ajax_clickervolt_search_cities', ['ClickerVolt\\AjaxSearches', 'searchCitiesAjax']);
        add_action('wp_ajax_clickervolt_search_device_names', ['ClickerVolt\\AjaxSearches', 'searchDeviceNamesAjax']);
        add_action('wp_ajax_clickervolt_save_settings', ['ClickerVolt\\AjaxCVSettings', 'saveAjax']);
        add_action('wp_ajax_clickervolt_load_rss', ['ClickerVolt\\AjaxFeed', 'loadRSSAjax']);

        add_filter('cron_schedules', static function ($schedules) {
            $schedules['clickervolt_one_minute'] = [
                'interval' => 60,
                'display' => esc_html__('Each Minute', 'clickervolt'),
            ];

            $schedules['clickervolt_once_per_week'] = [
                'interval' => 60 * 60 * 24 * 7,
                'display' => esc_html__('Once per Week', 'clickervolt'),
            ];

            return $schedules;
        });

        add_action('clickervolt_cron_clicks_queue', ['ClickerVolt\\Cron', 'processClicksQueue']);
        if (!wp_next_scheduled('clickervolt_cron_clicks_queue')) {
            wp_schedule_event(time(), 'clickervolt_one_minute', 'clickervolt_cron_clicks_queue');
        }

        add_action('clickervolt_cron_maxmind_update_dbs', ['ClickerVolt\\Cron', 'maxmindUpdate']);
        if (!wp_next_scheduled('clickervolt_cron_maxmind_update_dbs')) {
            wp_schedule_event(time(), 'clickervolt_once_per_week', 'clickervolt_cron_maxmind_update_dbs');
        }
    }
}

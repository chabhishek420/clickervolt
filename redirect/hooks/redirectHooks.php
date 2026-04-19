<?php

namespace ClickerVolt;

require_once __DIR__ . '/../../db/tableLinks.php';

class RedirectHooks
{
    const UNSAFE_PHP_HOOKS_FLAG = 'CLICKERVOLT_ENABLE_UNSAFE_PHP_HOOKS';

    /**
     * 
     */
    static function executeHTML($html, $duration, $nextURL = '')
    {

        $script = <<<SCRIPT
{$html}
<script>
        if( '{$nextURL}' ) {
            setTimeout( function() {
                location.href = '{$nextURL}';
            }, {$duration} );
        }
</script>
SCRIPT;

        echo $script;
    }

    /**
     * 
     */
    static function executePHP($code)
    {
        // Legacy behavior used eval(); permanently disabled for security hardening.
        return;
    }
}

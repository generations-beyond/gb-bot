<?php

/**
 * GB•BOT - shortcodes.php
 *
 * Global custom shortcodes go here.
 *
 */

function gb_year_function()
{
    return date('Y');
}
add_shortcode('gb_year', 'gb_year_function');
if (!shortcode_exists('oceanwp_date')) {
    add_shortcode('oceanwp_date', 'gb_year_function');
}

// Add following shortcodes only if GBTC is inactive
if (!$GBTC_ACTIVE) {
    // "Designed by GB" link
    function gblink_function()
    {
        return '<span class="gb-link"><a href="https://generationsbeyond.com/" rel="nofollow" target="_blank">Responsive Web Design</a> by <span>Generations Beyond</span></span>';
    }
    add_shortcode('gblink', 'gblink_function');
    add_shortcode('gb_link', 'gblink_function');
}

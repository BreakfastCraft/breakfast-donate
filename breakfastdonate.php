<?php
namespace BreakfastCraft;

class BreakfastDonate
{
    public function __construct()
    {
        load_plugin_textdomain('breakfast-donate', false, dirname(plugin_basename(__FILE__)) . '/lang');

        add_action('wp_enqueue_scripts', array($this, 'register_plugin_styles'));
        add_action('wp_enqueue_scripts', array($this, 'register_plugin_styles'));

        

    }
}

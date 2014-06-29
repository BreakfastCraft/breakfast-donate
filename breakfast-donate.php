<?php
/**
 * Plugin Name: Breakfast Donate
 * Plugin URI:  https://github.com/BreakfastCraft/breakfast-donate
 * Description: Donation Tracking widget
 * Author: Bret Belgarde
 * Version 0.1.0
 * Author URI: http://bretbelgarde.com/
 **/

class BreakfastDonate extends WP_Widget
{
    public function __construct()
    {
        parent::WP_Widget(
            'breakfast_donate_widget',
            __('Breakfast Donate Widget', 'text_domain'),
            array('description' => __('A donation tracking widget', 'text_domain'))
        );

        add_action('widget_init', array($this, 'regBreakfastDonateWidget'));
        add_action('wp_enqueue_scripts', array($this, 'breakfastDonateScripts'));
        add_action('wp_enqueue_scripts', array($this, 'breakfastDonateStyles'));
    }

    public function form($instance)
    {

    }

    public function update($new_instance, $old_instance)
    {

    }

    public function widget($args, $instance)
    {

    }

    public function regBreakfastDonateWidget()
    {
        register_widget('BreakfastDonate');
    }

    public function breakfastDonateStyles()
    {
        wp_register_style('breakfast-donate', plugins_url('breakfast-donate/css/plugin.css'));
        wp_enqueue_style('breakfast-donate');
    }

    public function breakfastDonateScripts()
    {
        wp_register_script('breakfast-donate', plugins_url('breakfast-donate/js/display.js'));
        wp_enqueue_script('breakfast-donate');
    }
}

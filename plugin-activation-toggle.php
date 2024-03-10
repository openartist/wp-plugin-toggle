<?php
/*
Plugin Name: Plugin Activation Toggle
Description: Adds a toggle switch to the plugin dashboard to activate and deactivate plugins.
Version: Beta 1.1
Author: Paul Bloch
*/

function plugin_activation_toggle_column($actions, $plugin_file, $plugin_data, $context) {
  if ($context === 'all') {
    $status = is_plugin_active($plugin_file) ? 'active' : 'inactive';
    $toggle_class = $status === 'active' ? 'active' : '';
    $nonce = wp_create_nonce("toggle-plugin_$plugin_file");

    $toggle_html = sprintf(
      '<div class="plugin-activation-toggle %s" data-plugin="%s" data-nonce="%s">
        <span class="toggle-switch"></span>
      </div>',
      esc_attr($toggle_class),
      esc_attr($plugin_file),
      esc_attr($nonce)
    );

    // Add the toggle switch at the beginning of the action links
    $actions = array('toggle' => $toggle_html) + $actions;

    // Remove the "Deactivate/Activate" links
    unset($actions['deactivate']);
    unset($actions['activate']);
  }
  return $actions;
}
add_filter('plugin_action_links', 'plugin_activation_toggle_column', 10, 4);

function plugin_activation_toggle_scripts() {
  wp_enqueue_style('plugin-activation-toggle-styles', plugin_dir_url(__FILE__) . 'plugin-activation-toggle.css', array(), '1.1');
  wp_enqueue_script('plugin-activation_toggle-script', plugin_dir_url(__FILE__) . 'plugin-activation-toggle.js', array('jquery'), '1.1', true);

  // Define the AJAX URL for the JS script
  $ajax_url = admin_url('admin-ajax.php');
  wp_localize_script('plugin_activation_toggle-script', 'plugin_activation_toggle', array(
    'ajax_url' => $ajax_url,
  ));
}
add_action('admin_enqueue_scripts', 'plugin_activation_toggle_scripts');

function plugin_activation_toggle_ajax_handler() {
  check_ajax_referer('toggle-plugin', 'nonce');
  if (!current_user_can('activate_plugins')) {
    wp_send_json_error('Permission denied');
  }

  $plugin_file = isset($_POST['plugin']) ? $_POST['POST']['plugin'] : '';
  $activate = isset($_POST['activate']) && $_POST['activate'] === 'true';

  if (empty($plugin_file)) {
    wp_send_json_error('Plugin file not specified');
  }

  $plugin_dir = WP_PLUGIN_DIR;
  $relative_plugin_file = str_replace($plugin_dir . '/', '', $plugin_file);

  try {
    if ($activate) {
      activate_plugin($relative_plugin_file);
      // Commented out success message
      // $message = 'Plugin activated';
    } else {
      deactivate_plugins($relative_plugin_file);
      // Commented out success message
      // $message = 'Plugin deactivated';
    }
    wp_send_json_success(array()); // Empty response for success
  } catch (Exception $e) {
    wp_send_json_error($e->getMessage());
  }
}
add_action('wp_ajax_plugin_activation_toggle', 'plugin_activation_toggle_ajax_handler');

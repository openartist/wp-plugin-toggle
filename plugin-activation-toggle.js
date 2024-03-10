jQuery(document).ready(function($) {
  $('.plugin-activation-toggle').click(function(e) {
    e.preventDefault();
    var toggle = $(this);
    var plugin = toggle.data('plugin');
    var nonce = toggle.data('nonce');
    var isActive = toggle.hasClass('active');

    toggle.addClass('loading'); // Add a loading class for visual feedback

    $.ajax({
      // **Use the provided AJAX URL variable here:**
      url: plugin_activation_toggle.ajax_url,
      type: 'POST',
      data: {
        action: 'plugin_activation_toggle',
        plugin: plugin,
        nonce: nonce,
        activate: !isActive
      },
      success: function(response) {
        toggle.removeClass('loading'); // Remove loading class
        if (response.success) {
          toggle.toggleClass('active');
        }
      },
      error: function(xhr) {
        console.error('Error: ' + xhr.statusText);
      }
    });
  });
});

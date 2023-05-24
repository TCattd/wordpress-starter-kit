/**
 * Show/Hide individual add-on license key input.
 */
(function ($) {
  $(document).ready(function () {
    $(".checkemail-hide").hide();
    var widget = $("#check-email-enable-widget").parent().parent();
    var dbNotifications = $("#check-email-enable-db-notifications")
      .parent()
      .parent();
    if (!$("#check-email-enable-logs").is(":checked")) {
      widget.hide();
      dbNotifications.hide();
    }

    $("#checkemail_autoheaders,#checkemail_customheaders").on(
      "change",
      function () {
        if ($("#checkemail_autoheaders").is(":checked")) {
          $("#customheaders").hide();
          $("#autoheaders").show();
        }
        if ($("#checkemail_customheaders").is(":checked")) {
          $("#autoheaders").hide();
          $("#customheaders").show();
        }
      }
    );
    $("#check-email-enable-logs").on("click", function () {
      if ($(this).is(":checked")) {
        widget.show();
        dbNotifications.show();
      } else {
        widget.hide();
        dbNotifications.hide();
      }
    });

    var from_name_setting = $("#check-email-from_name").parent().parent();
    var from_email_setting = $("#check-email-from_email").parent().parent();
    if (!$("#check-email-overdide-from").is(":checked")) {
      from_name_setting.hide();
      from_email_setting.hide();
    }

    $("#check-email-overdide-from").on("click", function () {
      if ($(this).is(":checked")) {
        from_name_setting.show();
        from_email_setting.show();
      } else {
        from_name_setting.hide();
        from_email_setting.hide();
      }
    });

    function activatePlugin(url) {
      $.ajax({
        async: true,
        type: "GET",
        dataType: "html",
        url: url,
        success: function () {
          location.reload();
        },
      });
    }

    // Install plugins actions
    $("#install_wp_smtp").on("click", (event) => {
      event.preventDefault();
      const current = $(event.currentTarget);
      const plugin_slug = current.data("slug");
      const plugin_action = current.data("action");
      const activate_url = current.data("activation_url");

      // Now let's disable the button and show the action text
      current.attr("disabled", true);

      if ("install" === plugin_action) {
        current.addClass("updating-message");

        const args = {
          slug: plugin_slug,
          success: (response) => {
            current.html("Activating plugin");

            activatePlugin(response.activateUrl);
          },
          error: (response) => {
            current.removeClass("updating-message");
            jQuery("#install_wp_smtp_info p").html(response.errorMessage);
            jQuery("#install_wp_smtp_info").addClass("notice-error notice");
          },
        };

        wp.updates.installPlugin(args);
      } else if ("activate" === plugin_action) {
        activatePlugin(activate_url);
      }
    });

  });
})(jQuery);

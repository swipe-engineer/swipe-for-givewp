jQuery(function ($) {

  init_swipego_meta();

  $(".swipego_customize_swipego_donations_field input:radio").on("change", function () {
    init_swipego_meta();
  });

  $('#swipego_business_id option[value="0"]').prop('disabled', true)

  $('#swipego_webhook_button').on('click', function (e) {

    e.preventDefault();

    let business_id = $('#swipego_business_id').val();

    var button_set_webhook = $(this);
    let field_webhook_url = $('#swipego_webhook_url');
    let enviroment = $('input[type=radio][name="swipego_environment"]:checked').val();

    $.ajax({
      url: swipego_gwp_set_webhook.ajax_url,
      type: 'POST',
      dataType: 'json',
      data: {
        action: 'swipego_gwp_set_webhook',
        nonce: swipego_gwp_set_webhook.nonce,
        business_id: business_id,
        environment: enviroment,
      },
      beforeSend: function () {
        button_set_webhook.prop('disabled', true);
        button_set_webhook.css('cursor', 'wait');
        button_set_webhook.html('Loading...');
        field_webhook_url.css('background', '#ddd');
      },
      success: function (response) {
        button_set_webhook.prop('disabled', true);
        button_set_webhook.css('cursor', 'pointer');
        button_set_webhook.html('Saved');
        field_webhook_url.css('background', '#f0f0f1');

        if (response.data?.data?.url) {
          $('#swipego_webhook_url').val(response.data.data.url);
        }

        Swal.fire({
          icon: 'success',
          title: 'Webhook Set!',
          text: 'Your GiveWP webhook URL have been successfully saved in Swipe.',
          timer: 3000,
        });
      },
      error: function (error) {
        button_set_webhook.prop('disabled', false);
        button_set_webhook.css('cursor', 'pointer');
        button_set_webhook.html('Retry');
        field_webhook_url.css('background', '#f0f0f1');

        console.log(error);

        try {
          var error = JSON.parse(error.responseText);
          if (error && error.data && error.data.message) {
            var message = '<span class="font-medium">Error!</span> ' + error.data.message + '.';
          } else {
            var message = 'An error occured! Please try again.';
          }
        } catch (e) {
          var message = error.responseText
        }

        Swal.fire({
          icon: 'error',
          title: 'Oops...',
          html: message,
          timer: 3000,
        });
      }
    });
  });

  if ($('#swipego_business_id').val() == 0) {
    $('#swipego_webhook_button').hide();
    $('.give-save-button').prop('disabled', true);
    $('input[type=radio][name="swipego_environment"]').prop('disabled', true);
    $('#swipego_webhook_url').prop('placeholder', '');
  }

  $('#swipego_business_id').on('change', function (e) {

    init_swipego_meta();

    var selected_li = $(this)
    var selected_business_id = selected_li.val();

    if (!selected_business_id) {
      return false;
    }

    let field_api_key = $('#swipego_api_key');
    let field_signature_key = $('#swipego_signature_key');
    let field_webhook_url = $('#swipego_webhook_url');
    let button_save = $('.give-save-button');
    var button_set_webhook = $('#swipego_webhook_button');
    let select_environment = $('input[type=radio][name="swipego_environment"]');

    $.ajax({
      url: swipego_gwp_update_settings.ajax_url,
      type: 'POST',
      dataType: 'json',
      data: {
        action: 'swipego_gwp_update_settings',
        nonce: swipego_gwp_update_settings.nonce,
        business_id: selected_business_id,
      },
      beforeSend: function () {
        button_set_webhook.prop('disabled', true);
        button_set_webhook.html('Set Webhook');
        button_save.prop('disabled', true);
        button_save.css('cursor', 'wait');
        button_save.val('Loading...');

        field_api_key.css('background', '#ddd');
        field_signature_key.css('background', '#ddd');
        field_webhook_url.css('background', '#ddd');

        select_environment.prop('disabled', true);
      },
      success: function (response) {
        button_set_webhook.prop('disabled', false);
        button_save.prop('disabled', false);
        button_save.css('cursor', 'pointer');
        button_save.val('Save changes');

        field_api_key.css('background', '#f0f0f1');
        field_signature_key.css('background', '#f0f0f1');
        field_webhook_url.css('background', '#f0f0f1');

        select_environment.prop('disabled', false);

        if (response.success !== undefined && response.success == true) {
          $("#swipego_api_key").val(response.data.business?.api_key);
          $("#swipego_signature_key").val(response.data.business?.signature_key);
          $("#swipego_webhook_url").val(response.data.webhook?.url);
        }
      },
      error: function (error) {
        button_set_webhook.prop('disabled', false);
        button_save.prop('disabled', false);
        button_save.css('cursor', 'pointer');
        button_save.val('Save changes');
        select_environment.prop('disabled', false);

        field_api_key.css('background', '#f0f0f1');
        field_signature_key.css('background', '#f0f0f1');
        field_webhook_url.css('background', '#f0f0f1');
      }
    });

    e.preventDefault();
  });

  $('input[type=radio][name="swipego_environment"]').change(function (e) {

    var selected_business_id = $('#swipego_business_id').val();

    if (!selected_business_id) {
      return false;
    }

    let field_api_key = $('#swipego_api_key');
    let field_signature_key = $('#swipego_signature_key');
    let field_webhook_url = $('#swipego_webhook_url');
    let button_save = $('.give-save-button');
    var button_set_webhook = $('#swipego_webhook_button');

    $.ajax({
      url: swipego_gwp_update_settings.ajax_url,
      type: 'POST',
      dataType: 'json',
      data: {
        action: 'swipego_gwp_update_settings',
        nonce: swipego_gwp_update_settings.nonce,
        business_id: selected_business_id,
        environment: $(this).val(),
      },
      beforeSend: function () {
        button_set_webhook.prop('disabled', true);
        button_set_webhook.html('Set Webhook');
        button_save.prop('disabled', true);
        button_save.css('cursor', 'wait');
        button_save.val('Loading...');

        field_api_key.css('background', '#ddd');
        field_signature_key.css('background', '#ddd');
        field_webhook_url.css('background', '#ddd');
      },
      success: function (response) {
        button_set_webhook.prop('disabled', false);
        button_save.prop('disabled', false);
        button_save.css('cursor', 'pointer');
        button_save.val('Save changes');

        field_api_key.css('background', '#f0f0f1');
        field_signature_key.css('background', '#f0f0f1');
        field_webhook_url.css('background', '#f0f0f1');

        if (response.success !== undefined && response.success == true) {
          $("#swipego_api_key").val(response.data.business?.api_key);
          $("#swipego_signature_key").val(response.data.business?.signature_key);
          $("#swipego_webhook_url").val(response.data.webhook?.url);
        }
      },
      error: function (error) {
        button_set_webhook.prop('disabled', false);
        button_save.prop('disabled', false);
        button_save.css('cursor', 'pointer');
        button_save.val('Save changes');

        field_api_key.css('background', '#f0f0f1');
        field_signature_key.css('background', '#f0f0f1');
        field_webhook_url.css('background', '#f0f0f1');
      }
    });

    e.preventDefault();
  });

  function init_swipego_meta() {

    if ("enabled" === $(".swipego_customize_swipego_donations_field input:radio:checked").val()) {

      let business_id = $('#swipego_business_id').val();

      $(".swipego_business_id_field").show();

      if (business_id != 0) {
        $(".swipego_api_key_field").show();
        $(".swipego_signature_key_field").show();
        $(".swipego_environment_field").show();
      }

      if (business_id == 0) {
        $(".swipego_api_key_field").hide();
        $(".swipego_signature_key_field").hide();
        $(".swipego_environment_field").hide();
      }

    } else {

      $(".swipego_business_id_field").hide();
      $(".swipego_api_key_field").hide();
      $(".swipego_signature_key_field").hide();
      $(".swipego_environment_field").hide();

    }
  }
});


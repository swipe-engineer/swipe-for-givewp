jQuery(document).ready(function ($) {
    // Toggle password visibility
    $('body').on('click', '.toggle-password', function () {
        var input = $(this).prev('input');

        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            $(this).find('.eye-open').hide();
            $(this).find('.eye-close').show();
        } else {
            input.attr('type', 'password');
            $(this).find('.eye-open').show();
            $(this).find('.eye-close').hide();
        }
    });

    // Handle login
    $('#swipego-login').validate({
        rules: {
            email: {
                required: true,
                email: true,
            },
            password: {
                required: true,
            },
        },
        errorElement: 'span',
        errorPlacement: function (error, element) {
            error.addClass('text-xs text-error pt-2');
            element.closest('.form-group').append(error);
        },
        highlight: function (element, errorClass, validClass) {
            $(element).addClass('ring-error focus:ring-error');
        },
        unhighlight: function (element, errorClass, validClass) {
            $(element).removeClass('ring-error focus:ring-error');
        },
        submitHandler: function (form) {
            var form = $(form),
                submit_btn = form.find('button[type=submit]'),
                submit_text_span = form.find('#submit-text'),
                submit_text = submit_text_span.text(),
                email = form.find('#email').val(),
                password = form.find('#password').val(),
                remember = form.find('#remember').prop('checked'),
                errors = form.find('#errors');

            $.ajax({
                url: swipego_login.ajax_url,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'swipego_login',
                    nonce: swipego_login.nonce,
                    email: email,
                    password: password,
                    remember: remember,
                },
                beforeSend: function () {
                    submit_btn.prop('disabled', true);
                    submit_btn.css('cursor', 'wait');
                    submit_text_span.html('<svg width="73" height="72" viewBox="0 0 73 72" fill="none" xmlns="http://www.w3.org/2000/svg" class="animate-spin text-white h-5 w-5 mr-2 text-white"><path opacity="0.5" d="M36.5 6C30.5666 6 24.7664 7.75947 19.8329 11.0559C14.8994 14.3524 11.0543 19.0377 8.78363 24.5195C6.513 30.0013 5.9189 36.0333 7.07646 41.8527C8.23401 47.6721 11.0912 53.0176 15.2868 57.2132C19.4824 61.4088 24.8279 64.266 30.6473 65.4236C36.4667 66.5811 42.4987 65.987 47.9805 63.7164C53.4623 61.4458 58.1476 57.6006 61.4441 52.6671C64.7405 47.7336 66.5 41.9334 66.5 36C66.5 32.0603 65.724 28.1593 64.2164 24.5195C62.7088 20.8797 60.499 17.5726 57.7132 14.7868C54.9274 12.001 51.6203 9.79126 47.9805 8.28361C44.3407 6.77597 40.4397 6 36.5 6ZM36.5 60C31.7533 60 27.1131 58.5924 23.1663 55.9553C19.2195 53.3181 16.1434 49.5698 14.3269 45.1844C12.5104 40.799 12.0351 35.9734 12.9612 31.3178C13.8872 26.6623 16.173 22.3859 19.5294 19.0294C22.8859 15.673 27.1623 13.3872 31.8178 12.4612C36.4734 11.5351 41.299 12.0104 45.6844 13.8269C50.0698 15.6434 53.8181 18.7195 56.4553 22.6663C59.0924 26.6131 60.5 31.2532 60.5 36C60.5 42.3652 57.9714 48.4697 53.4706 52.9706C48.9697 57.4714 42.8652 60 36.5 60Z" fill="currentColor"></path><path d="M60.5 36H66.5C66.5 32.0603 65.724 28.1593 64.2164 24.5195C62.7087 20.8797 60.499 17.5726 57.7132 14.7868C54.9274 12.001 51.6203 9.79126 47.9805 8.28361C44.3407 6.77597 40.4397 6 36.5 6V12C42.8652 12 48.9697 14.5286 53.4706 19.0294C57.9714 23.5303 60.5 29.6348 60.5 36Z" fill="currentColor"></path></svg>');
                    errors.html('');
                },
                success: function (response) {
                    submit_btn.prop('disabled', false);
                    submit_btn.css('cursor', 'pointer');
                    submit_text_span.html(submit_text);

                    Swal.fire({
                        icon: 'success',
                        title: 'Login Success!',
                        text: 'You will be redirected to the dashboard.',
                        showConfirmButton: false,
                        timer: 1500,
                    }).then((result) => {
                        location.reload();
                    });
                },
                error: function (xhr) {
                    submit_btn.prop('disabled', false);
                    submit_btn.css('cursor', 'pointer');
                    submit_text_span.html(submit_text);

                    try {
                        var error = JSON.parse(xhr.responseText);
                        var message = 'An error occured! Please try again';
                        if (error && error.data && error.data.message) {
                            var message = error.data.message;
                        }

                    } catch (e) {
                        var message = xhr.responseText;
                    }

                    errors.html(
                        '<div class="flex font-medium items-center justify-start text-sm text-error">' +
                        '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" class="h-4 w-4 text-error mr-2"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>' +
                        '<span>' + message + '.</span>' +
                        '</div>'
                    );
                }
            });
        }
    });

    // Handle logout
    $('#swipego-logout').on('click', function () {
        var btn = $(this),
            btn_text = btn.text();

        $.ajax({
            url: swipego_logout.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'swipego_logout',
                nonce: swipego_logout.nonce,
            },
            beforeSend: function () {
                btn.prop('disabled', true);
                btn.css('cursor', 'wait');
                btn.prepend('<svg width="73" height="72" viewBox="0 0 73 72" fill="none" xmlns="http://www.w3.org/2000/svg" class="animate-spin text-white h-5 w-5 mr-2 text-white"><path opacity="0.5" d="M36.5 6C30.5666 6 24.7664 7.75947 19.8329 11.0559C14.8994 14.3524 11.0543 19.0377 8.78363 24.5195C6.513 30.0013 5.9189 36.0333 7.07646 41.8527C8.23401 47.6721 11.0912 53.0176 15.2868 57.2132C19.4824 61.4088 24.8279 64.266 30.6473 65.4236C36.4667 66.5811 42.4987 65.987 47.9805 63.7164C53.4623 61.4458 58.1476 57.6006 61.4441 52.6671C64.7405 47.7336 66.5 41.9334 66.5 36C66.5 32.0603 65.724 28.1593 64.2164 24.5195C62.7088 20.8797 60.499 17.5726 57.7132 14.7868C54.9274 12.001 51.6203 9.79126 47.9805 8.28361C44.3407 6.77597 40.4397 6 36.5 6ZM36.5 60C31.7533 60 27.1131 58.5924 23.1663 55.9553C19.2195 53.3181 16.1434 49.5698 14.3269 45.1844C12.5104 40.799 12.0351 35.9734 12.9612 31.3178C13.8872 26.6623 16.173 22.3859 19.5294 19.0294C22.8859 15.673 27.1623 13.3872 31.8178 12.4612C36.4734 11.5351 41.299 12.0104 45.6844 13.8269C50.0698 15.6434 53.8181 18.7195 56.4553 22.6663C59.0924 26.6131 60.5 31.2532 60.5 36C60.5 42.3652 57.9714 48.4697 53.4706 52.9706C48.9697 57.4714 42.8652 60 36.5 60Z" fill="currentColor"></path><path d="M60.5 36H66.5C66.5 32.0603 65.724 28.1593 64.2164 24.5195C62.7087 20.8797 60.499 17.5726 57.7132 14.7868C54.9274 12.001 51.6203 9.79126 47.9805 8.28361C44.3407 6.77597 40.4397 6 36.5 6V12C42.8652 12 48.9697 14.5286 53.4706 19.0294C57.9714 23.5303 60.5 29.6348 60.5 36Z" fill="currentColor"></path></svg>');
            },
            success: function (response) {
                btn.prop('disabled', false);
                btn.css('cursor', 'pointer');
                btn.html(btn_text);

                Swal.fire({
                    icon: 'success',
                    title: 'Logout Success!',
                    text: 'You will be redirected to the login page.',
                    showConfirmButton: false,
                    timer: 1500,
                }).then((result) => {
                    location.reload();
                });
            },
            error: function (xhr) {
                btn.prop('disabled', false);
                btn.css('cursor', 'pointer');
                btn.html(btn_text);

                var error = JSON.parse(xhr.responseText);

                if (error && error.data && error.data.message) {
                    var message = '<span class="font-medium">Error!</span> ' + error.data.message + '.';
                } else {
                    var message = 'An error occured! Please try again.';
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
});

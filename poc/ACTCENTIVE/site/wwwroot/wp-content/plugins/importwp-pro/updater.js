(function ($, iwp) {

    $(document).on('keyup keypress', '.iwp-license-input', function (e) {
        var keyCode = e.keyCode || e.which;
        if (keyCode === 13) {
            e.preventDefault();
            $('.iwp-updater-button--activate').trigger('click');
            return false;
        }
    });

    var original_license_message = '';

    $(document).on('click', '.iwp-updater-button--activate', function () {

        // TODO: check to see if license is valid
        var data = {
            // Billing
            license: $('.iwp-license-input').val(),
            action: 'iwp_validate_license',
            nonce: iwp.nonce
        };

        var $row = $('.iwp-updater-license-row');

        $row.find('.spinner').addClass('is-active');

        if (original_license_message.length === 0) {
            original_license_message = $row.find('.iwp-updater-msg').html();
        }

        $.ajax({
            url: iwp.ajax_url,
            method: 'POST',
            dataType: 'json',
            beforeSend: function (xhr) {
                xhr.setRequestHeader('X-WP-Nonce', iwp.nonce);
            },
            data: data,
            complete: function (data) {
                var result = data.responseJSON;
                $row.find('.spinner').removeClass('is-active');
                var success = false;

                if (result.hasOwnProperty('status')) {
                    if (result.status == 'S') {
                        $row.find('.iwp-updater-msg').text(result.data.msg);
                        if (result.data.license === 'valid') {
                            $row.addClass('iwp-valid');
                            $row.removeClass('iwp-invalid');
                            success = true;
                            location.reload();

                        } else {
                            $row.addClass('iwp-invalid');
                            $row.removeClass('iwp-valid');
                            $row.find('.iwp-updater-msg').text(result.data.msg);
                        }

                    } else {
                        $row.find('.iwp-updater-msg').text('An error has occured: ' + result.data.msg);
                    }
                } else {
                    $row.find('.iwp-updater-msg').text('An error has occured, please refresh and try again.');
                }

                if (!success) {
                    setTimeout(function () {
                        $row.find('.iwp-updater-msg').html(original_license_message);
                    }, 2000);
                }
            }
        });

    });

    $(document).on('click', '.iwp-updater-button--deactivate', function () {

        // TODO: check to see if license is valid
        var data = {
            // Billing
            license: '',
            action: 'iwp_validate_license',
            nonce: iwp.nonce
        };

        var $row = $('.iwp-updater-license-row');

        $row.find('.spinner').addClass('is-active');

        $.ajax({
            url: iwp.ajax_url,
            method: 'POST',
            dataType: 'json',
            beforeSend: function (xhr) {
                xhr.setRequestHeader('X-WP-Nonce', iwp.nonce);
            },
            data: data,
            complete: function (data) {
                var result = data.responseJSON;
                $row.find('.spinner').removeClass('is-active');

                if (result.hasOwnProperty('status')) {
                    if (result.status == 'S') {
                        $row.removeClass('iwp-valid');
                        $row.addClass('iwp-invalid');
                        $row.find('.iwp-updater-msg').text('License has been deactivated');
                        location.reload();
                    } else {
                        $row.find('.iwp-updater-msg').text('An error has occured: ' + result.data.msg);
                    }
                } else {
                    $row.find('.iwp-updater-msg').text('An error has occured, please refresh and try again.');
                }


            }
        });

    });

})(jQuery, window.iwp);
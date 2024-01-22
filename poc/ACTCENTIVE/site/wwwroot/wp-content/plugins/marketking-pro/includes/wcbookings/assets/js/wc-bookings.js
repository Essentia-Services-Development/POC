// let marketkingPluginModule;
(function () {

    let marketkingPluginModule = (function ($) {

        $.fn.hasAttr = function (name) {
            return (typeof this.attr(name) !== 'undefined' && this.attr(name) !== false);
        };

        let security_settings = marketking_wc_bookings_display_settings.security;
        let ajaxurl_settings = marketking_wc_bookings_display_settings.ajaxurl;

        let booking_product_name_must = marketking_wc_bookings_display_settings.booking_product_must_name;
        let booking_product_link_settings = marketking_wc_bookings_display_settings.booking_product_edit_link;
        let booking_product_delete_message = marketking_wc_bookings_display_settings.sure_delete_booking_product;
        let booking_products_dashboard_page = marketking_wc_bookings_display_settings.booking_products_dashboard_page;

        let resource_name_must = marketking_wc_bookings_display_settings.resource_must_name;
        let resource_link_settings = marketking_wc_bookings_display_settings.resource_edit_link;
        let resource_delete_message = marketking_wc_bookings_display_settings.sure_delete_resource;
        let resource_dashboard_page = marketking_wc_bookings_display_settings.resources_dashboard_page;


        let booking_order_edit_link_settings = marketking_wc_bookings_display_settings.booking_order_edit_link;
        return {

            //BOOKING PRODUCT
            updateBookingProductButton: function () {

                // Save / Update product
                $('#marketking_save_booking_product_button').on('click', function () {


                    if ($('#marketking_booking_product_title').val() !== '') {


                        $(this).addClass('loading');
                        $('#marketking_save_booking_product_button .btn-primary').addClass('disabled');


                        $(this).off('click');


                        // switch descriptions to html before saving, helps pass the data correctly
                        $('.switch-html').click();

                        let title = $('#marketking_booking_product_title').val();
                        let actionedit = $('#marketking_edit_booking_product_action_edit').val();

                        let datavar = $('#marketking_save_booking_product_form').serialize() + '&' + $.param({
                            action: 'marketkingsavebookingproduct',
                            security: security_settings,
                            id: $('#marketking_save_booking_product_button_id').val(),
                            actionedit: actionedit,
                            title: title
                        });
                        // console.log(datavar);

                        $.post(ajaxurl_settings, datavar, function (response) {
                            // console.log(response);

                            if (actionedit === 'edit') {
                                window.location = booking_product_link_settings + response + '?update=success';
                            } else if (actionedit === 'add') {
                                // go to newly created product
                                window.location = booking_product_link_settings + response + '?add=success';
                            }

                            $(this).removeClass('loading');
                        });
                    } else {
                        alert(booking_product_name_must);
                        $(this).removeClass('loading');
                    }
                });


            },

            deleteBookingProduct: function () {
                // Delete product
                $('body').on('click', '.marketking_delete_button_booking_product', function () {
                    if (confirm(booking_product_delete_message)) {
                        // delete product
                        let datavar = {
                            action: 'marketkingdeletebookableresource',
                            security: security_settings,
                            id: $(this).attr('value'),
                        };
                        $.post(ajaxurl_settings, datavar, function (response) {
                            // redirect to products page
                            window.location = booking_products_dashboard_page;
                        });


                    }
                });
            },


            //BOOKING RESOURCES PRODUCT

            addBookableResourceProduct: function () {

                // Add a resource
                $('#bookings_resources').on('click', 'button.marketking_add_resource', function () {

                    let loop = $('.woocommerce_booking_resource').length;
                    let add_resource_id = $('select.add_resource_id').val();
                    let add_resource_name = '';

                    if (!add_resource_id) {
                        add_resource_name = prompt(wc_bookings_admin_js_params.i18n_new_resource_name);

                        if (!add_resource_name) {
                            return false;
                        }
                    }

                    $('.woocommerce_bookable_resources').block({message: null});


                    let data = {
                        action: 'marketking_add_bookable_resource',
                        post_id: $('#marketking_save_product_button_id').val(),

                        loop: loop,
                        add_resource_id: add_resource_id,
                        add_resource_name: add_resource_name,
                        security: wc_bookings_admin_js_params.nonce_add_resource
                    };

                    $.post(wc_bookings_admin_js_params.ajax_url, data, function (response) {
                        // console.log(data);
                        if (response.error) {
                            alert(response.error);
                            $('.woocommerce_bookable_resources').unblock();
                        } else {
                            $('.woocommerce_bookable_resources').append(response.html).unblock();
                            $('.woocommerce_bookable_resources').sortable(resources_sortable_options);
                            if (add_resource_id) {
                                $('.add_resource_id').find('option[value=' + add_resource_id + ']').remove();
                            }
                        }
                    });

                    return false;
                });

                let resources_sortable_options = {
                    items: ".woocommerce_booking_resource",
                    cursor: "move",
                    axis: "y",
                    handle: "h3",
                    scrollSensitivity: 40,
                    forcePlaceholderSize: !0,
                    helper: "clone",
                    opacity: .65,
                    placeholder: "wc-metabox-sortable-placeholder",
                    start: function (event, ui) {
                        ui.item.css("background-color", "#f6f6f6")
                    },

                    stop: function (event, ui) {
                        ui.item.removeAttr("style");
                        $(".woocommerce_bookable_resources" +
                            " .woocommerce_booking_resource").each((function (event, ui) {
                            $(".resource_menu_order", ui).val((0, o.default)($(ui).index(".woocommerce_bookable_resources .woocommerce_booking_resource"), 10));
                        }));
                    }
                };
                $(".woocommerce_bookable_resources").sortable(resources_sortable_options);

            },
            deleteBookableResourceProduct: function () {
                // Remove a resource
                $('body').on('click', 'button.marketking_remove_booking_resource', function (e) {

                    e.preventDefault();

                    let answer = confirm(wc_bookings_admin_js_params.i18n_remove_resource);

                    if (answer) {

                        let parent = $(this).parent().parent();
                        let resource = $(this).attr('rel');

                        $(parent).block({
                                message: null,
                                overlayCSS: {
                                    background: "#fff url(" + wc_bookings_admin_js_params.plugin_url + "/assets/images/ajax-loader.gif) no-repeat center",
                                    opacity: .6
                                }
                            }
                        );

                        let data = {
                            action: 'marketking_remove_bookable_resource',
                            post_id: $('#marketking_save_product_button_id').val(),
                            resource_id: resource,
                            security: wc_bookings_admin_js_params.nonce_delete_resource
                        };

                        $.post(wc_bookings_admin_js_params.ajax_url, data, function (response) {
                            $(parent).fadeOut('300', function () {
                                parent.remove();
                                let resource_id = parent.find("input[name*=resource_id]").val(),
                                    resource_title = parent.find("input[name*=resource_title]").val();
                                $("select[name=add_resource_id]").append($("<option>", {
                                    value: resource_id,
                                    text: resource_title
                                }))
                            });
                        });
                    }
                    return false;
                });
            },

            //BOOKING RESOURCES
            updateresourceButton: function () {


                $('#marketking_save_resource_button').one('click', function ($event) {

                    if ($('#marketking_resource_title').val() !== '') {

                        $(this).addClass('loading');

                        $('#marketking_save_resource_button .btn-primary').addClass('disabled');

                        $(this).off('click');


                        let actionedit = $('#marketking_edit_resource_action_edit').val();

                        let datavar = $('#marketking_save_bookable_resource_form').serialize() + '&' + $.param({
                            title: $('#marketking_resource_title').val(),
                            actionedit: $('#marketking_edit_resource_action_edit').val(),
                            action: 'marketkingsavebookableresource',
                            security: security_settings,
                            id: $('#marketking_save_resource_button_id').val(),

                        });


                        $.post(ajaxurl_settings, datavar, function (response) {


                            if (actionedit === 'edit') {
                                // console.log('edit');

                                window.location = resource_link_settings + response + '/?update=success';

                            } else if (actionedit === 'add') {

                                // console.log('add');
                                // go to newly created product
                                window.location = resource_link_settings + response + '/?add=success';

                            }

                            $(this).removeClass('loading');

                        });

                    } else {
                        alert(resource_name_must);
                    }
                });
            }
            ,

            deleteResource: function () {
                // Delete product
                $('body').on('click', '.marketking_delete_button_resource', function () {
                    if (confirm(resource_delete_message)) {
                        // delete product
                        let datavar = {
                            action: 'marketkingdeletebookableresource',
                            security: security_settings,
                            id: $(this).attr('value'),
                        };


                        $.post(ajaxurl_settings, datavar, function (response) {
                            // redirect to products page
                            window.location = resource_dashboard_page;
                        });


                    }
                });
            }
            ,

            //BOOKING ORDERS
            updateBookingOrderEditButton: function () {


                $('#marketking_save_booking_order_button').one('click', function ($event) {


                    $(this).addClass('loading');

                    $('#marketking_save_booking_order_button .btn-primary').addClass('disabled');

                    $(this).off('click');


                    let datavar = $('#marketking_save_booking_order_form').serialize() + '&' + $.param({

                        action: 'marketkingsavebookingorderedit',
                        security: security_settings,
                        id: $('#marketking_save_booking_order_button_id').val(),

                    });


                    $.post(ajaxurl_settings, datavar, function (response) {


                        window.location = booking_order_edit_link_settings + response + '/?update=success';


                        $(this).removeClass('loading');

                    });

                });
            }
            ,

            init: function () {


                //booking products
                marketkingPluginModule.updateBookingProductButton();
                marketkingPluginModule.deleteBookingProduct();


                //bookable resources product add/remove
                marketkingPluginModule.addBookableResourceProduct();
                marketkingPluginModule.deleteBookableResourceProduct();

                //booking resources
                marketkingPluginModule.updateresourceButton();
                marketkingPluginModule.deleteResource();
                //booking orders
                marketkingPluginModule.updateBookingOrderEditButton();

            }

        }
    })
    (jQuery);


    $(document).ready(function () {
        marketkingPluginModule.init();
    });
})
();
(function ($) {

    /**
     * Sparkle Modal
     * */
    $('.sparkle-theme-modal-button').on('click', function (e) {
        e.preventDefault();
        $('body').addClass('sparkle-theme-modal-opened');
        var modal = $(this).attr('href');
        $(modal).fadeIn();

        $("html, body").animate({scrollTop: 0}, "slow");
    });

    /** modal cancel action */
    $('.sparkle-theme-modal-back, .sparkle-theme-modal-cancel').on('click', function (e) {
        $('body').removeClass('sparkle-theme-modal-opened');
        $('.sparkle-theme-modal').hide();
        $("html, body").animate({scrollTop: 0}, "slow");
    });

    /**
     * Import demo action with ajax
     * */

    $('body').on('click', '.sparkle-theme-import-demo', function () {
        var $el = $(this);
        var demo = $(this).attr('data-demo-slug');
        var reset = $('#checkbox-reset-' + demo).is(':checked');
        var reset_message = '';
        
        if (reset) {
            reset_message = sparkle_ajax_data.reset_database;
            var confirm_message = 'Are you sure to proceed? Resetting the database will delete all your contents.';
        }else{
            var confirm_message = 'Are you sure to proceed?';
        }
        
        $import_true = confirm(confirm_message);
        if ($import_true == false)
            return;
        
        $("html, body").animate({scrollTop: 0}, "slow");

        $('#sparkle-theme-modal-' + demo).hide();
        $('#sparkle-theme-import-progress').show();

        $('#sparkle-theme-import-progress .sparkle-theme-import-progress-message').html(sparkle_ajax_data.prepare_importing).fadeIn();

        var info = {
            demo: demo,
            reset: reset,
            next_step: 'sparkle_demo_import_install_demo',
            next_step_message: reset_message
        };

        setTimeout(function () {
            run_ajax(info);
        }, 2000);
    });

    localStorage.setItem('demo_import_erro_count', 0);
    /** ajax run recurrsive function */
    function run_ajax(info) {
        if (info.next_step) {
            var data = {
                action: info.next_step,
                demo: info.demo,
                reset: info.reset,
                security: sparkle_ajax_data.nonce
            };

            jQuery.ajax({
                url: ajaxurl,
                type: 'post',
                data: data,
                statusCode: {
                    500: function() {
                        var count = parseInt(localStorage.getItem('demo_import_erro_count'));
                        if( count < 5){
                            console.log("Script exhausted", info);
                            localStorage.setItem('demo_import_erro_count', count + 1);
                            do_ajax(info);
                        }
                    }
                },
                beforeSend: function () {
                    if (info.next_step_message) {
                        $('#sparkle-theme-import-progress .sparkle-theme-import-progress-message').hide().html('').fadeIn().html(info.next_step_message);
                    }
                },
                success: function (response) {
                    var info = JSON.parse(response);

                    if (!info.error) {
                        if (info.complete_message) {
                            $('#sparkle-theme-import-progress .sparkle-theme-import-progress-message').hide().html('').fadeIn().html(info.complete_message);
                        }
                        setTimeout(function () {
                            run_ajax(info);
                        }, 5000);
                    } else {
                        $('#sparkle-theme-import-progress .sparkle-theme-import-progress-message').html(sparkle_ajax_data.import_error);
                        
                    }
                },
                error: function (xhr, status, error) {
                    var errorMessage = xhr.status + ': ' + xhr.statusText
                    $('#sparkle-theme-import-progress .sparkle-theme-import-progress-message').html(sparkle_ajax_data.import_error);
                    $('#sparkle-theme-import-progress').addClass('import-error');
                }
            });
        } else {
            $('#sparkle-theme-import-progress .sparkle-theme-import-progress-message').html(sparkle_ajax_data.import_success);
            $('#sparkle-theme-import-progress').addClass('import-success');
        }
    }



    /** filter tab with isotop library **/
    if ($('.sparkle-theme-tab-filter').length > 0) {
        $('.sparkle-theme-tab-group').each(function () {
            $(this).find('.sparkle-theme-tab:first').addClass('sparkle-theme-active');
        });

        // init Isotope
        var $grid = $('.sparkle-theme-demo-box-wrap').imagesLoaded(function () {
            $grid.isotope({
                itemSelector: '.sparkle-theme-demo-box',
                layoutMode: 'fitRows'
            });
        });

        // store filter for each group
        var filters = {};

        $('.sparkle-theme-tab-group').on('click', '.sparkle-theme-tab', function (event) {
            var $button = $(event.currentTarget);
            // get group key
            var $buttonGroup = $button.parents('.sparkle-theme-tab-group');
            var filterGroup = $buttonGroup.attr('data-filter-group');
            // set filter for group
            filters[ filterGroup ] = $button.attr('data-filter');
            // combine filters
            var filterValue = concatValues(filters);
            // set filter for Isotope
            $grid.isotope({filter: filterValue});
        });

        // change is-checked class on buttons
        $('.sparkle-theme-tab-group').each(function (i, buttonGroup) {
            var $buttonGroup = $(buttonGroup);
            $buttonGroup.on('click', '.sparkle-theme-tab', function (event) {
                $buttonGroup.find('.sparkle-theme-active').removeClass('sparkle-theme-active');
                var $button = $(event.currentTarget);
                $button.addClass('sparkle-theme-active');
            });
        });

        // flatten object by concatting values
        function concatValues(obj) {
            var value = '';
            for (var prop in obj) {
                value += obj[ prop ];
            }
            return value;
        }
    }
})(jQuery);

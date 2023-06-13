jQuery(document).ready(function($) {
    // Color picker
    $('.color-picker').wpColorPicker();

    // Media library uploader
    function dbc_station_media_uploader(button_id, preview_id, input_id) {
            var custom_uploader;

            $(button_id).click(function(e) {
                e.preventDefault();

                // If the uploader object has already been created, reopen the dialog
                if (custom_uploader) {
                    custom_uploader.open();
                    return;
                }

                // Create the media frame
                custom_uploader = wp.media.frames.file_frame = wp.media({
                    title: 'Choose Image',
                    button: {
                        text: 'Choose Image'
                    },
                    multiple: false
                });

                // When an image is selected, run a callback
                custom_uploader.on('select', function() {
                    var attachment = custom_uploader.state().get('selection').first().toJSON();
                    $(input_id).val(attachment.url);
                    $(preview_id).attr('src', attachment.url);
                });

                // Open the uploader dialog
                custom_uploader.open();
            });
        }

        dbc_station_media_uploader('#site-logo-button', '#site-logo-preview', '#site-logo');
        dbc_station_media_uploader('#site-favicon-button', '#site-favicon-preview', '#site-favicon');
    });
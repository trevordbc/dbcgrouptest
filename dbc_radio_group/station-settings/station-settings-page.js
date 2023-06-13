jQuery(document).ready(function($) {
    var mediaUploader;
    $('#upload-logo').on('click', function(e) {
        e.preventDefault();
        if (mediaUploader) {
            mediaUploader.open();
            return;
        }
        mediaUploader = wp.media.frames.file_frame = wp.media({
            title: 'Choose Logo',
            button: {
                text: 'Choose Logo'
            },
            multiple: false
        });
        mediaUploader.on('select', function() {
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            $('#logo-preview').attr('src', attachment.url);
            $('#logo-url').val(attachment.url);
            $('#logo-id').val(attachment.id);
        });
        mediaUploader.open();
    });

    $('#upload-favicon').on('click', function(e) {
        e.preventDefault();
        if (mediaUploader) {
            mediaUploader.open();
            return;
        }
        mediaUploader = wp.media.frames.file_frame = wp.media({
            title: 'Choose Favicon',
            button: {
                text: 'Choose Favicon'
            },
            multiple: false
        });
        mediaUploader.on('select', function() {
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            $('#favicon-preview').attr('src', attachment.url);
            $('#favicon-url').val(attachment.url);
            $('#favicon-id').val(attachment.id);
        });
        mediaUploader.open();
    });

    $('#upload-favicon').click(function() {
			$('#favicon-upload').click();
		});

		$('#favicon-upload').change(function() {
			var input = $(this)[0];
			if (input.files && input.files[0]) {
				var reader = new FileReader();

				reader.onload = function(e) {
					var img = new Image();
					img.src = e.target.result;

					img.onload = function() {
						var canvas = document.createElement("canvas");
						canvas.width = this.width;
						canvas.height = this.height;

						var ctx = canvas.getContext("2d");
						ctx.drawImage(this, 0, 0);

						var dataURL = canvas.toDataURL("image/png");
						$('#favicon-icon img').attr('src', dataURL);
						$('input[name="dbc_station_website_favicon"]').val(dataURL);
					};
				};

				reader.readAsDataURL(input.files[0]);
			}
	});
});
jQuery(document).ready(function($) {
    $('#the-list').sortable({
        axis: 'y',
        handle: '.custom-order-icon',
        start: function(event, ui) {
            ui.item.start_pos = ui.item.index();
        },
        update: function(event, ui) {
            var order = [];
            var first_pos = ui.item.start_pos;
            $('#the-list').find('tr').each(function() {
                var current_pos = $(this).index();
                var original_pos = current_pos - first_pos;
                var post_id = $(this).attr('id').replace('post-', '');
                post_id = parseInt(post_id) + original_pos;
                order.push(post_id);
                $(this).attr('data-post-id', post_id); // update the data-post-id attribute
				$(this).attr('id', 'post-' + post_id);
            });
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
					action: 'custom_save_post_order',
					'custom-order': order.join(','),
					security: $('#custom_save_post_order_nonce').val() // Add the nonce field value
				},
                success: function(response) {
				  console.log('Order saved');
				  console.log(response);
				},
                error: function(xhr, status, error) {
					console.log('Status: ' + status + ', Error: ' + error);
				}
            });
        }
    });
});
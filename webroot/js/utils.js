/**
 *
 *  JS functions to work with roles and capabilities
 *
 */
(function ($) {
    // Find all checkboxes in the specific group and check/uncheck them
    $('.select_all').click(function () {
        var checkboxes = $(this).closest('.tab-pane').find(':checkbox');
        if ($(this).is(':checked')) {
            checkboxes.prop('checked', true);
        } else {
            checkboxes.prop('checked', false);
        }
    });

    // Find all collapsable boxes and collapse/expand them
    $('#collapse_all').click(function () {
        $('.permission-box').each(function () {
            $(this).find('[data-widget="collapse"]').trigger('click');
        });
    });

    // json encode selected capabilities to avoid hitting PHP's max_input_vars limit (task #4031)
    $('#capabilities-submit').click(function () {
        var capabilities = [];
        $('.checkbox-capability:checked').each(function () {
            if (!$(this).is(':disabled')) {
                var capString = $(this).attr('name');

                var parts = capString.split('@');

                var cap = {
                    resource: parts[0].replace('_', '.'),
                    operation: parts[1],
                    association: parts[2]
                };

                capabilities.push(cap);
            }
        });

        $('#capabilities-input').val(JSON.stringify(capabilities));

        $('#capabilities-form').submit();
    });

})(jQuery);

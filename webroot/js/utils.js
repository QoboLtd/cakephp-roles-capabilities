/**
 *
 *  JS functions to work with roles and capabilities
 *
 */
(function ($) {
    // Find all checkboxes in the specific group and check/uncheck them
    $('.select_all').click(function () {
        var checkboxes = $(this).closest('.permission-box').find(':checkbox');
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
        var checkboxes = {};
        $('.checkbox-capability:checked').each(function () {
            checkboxes[$(this).attr('name')] = true;
        });

        $('#capabilities-input').val(JSON.stringify(checkboxes));

        $('#capabilities-form').submit();
    });

})(jQuery);

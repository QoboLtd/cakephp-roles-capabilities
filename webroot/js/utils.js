/**
 *
 *  JS functions to work with roles and capabilities
 *
 */
(function ($) {
    $('#select_all').click(function () {
        var checkboxes = $(this).closest('form').find(':checkbox[name^=capabilities]');
        if ($(this).is(':checked')) {
            checkboxes.prop('checked', true);
        } else {
            checkboxes.prop('checked', false);
        }
    });
})(jQuery);

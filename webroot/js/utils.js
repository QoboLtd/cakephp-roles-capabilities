/**
 *
 *  JS functions to work with roles and capabilities
 *
 */
(function ($) {
    // Find all checkboxes in the specific group and check/uncheck them
    $('.select_all').click(function () {
        console.log('Checking ... ' + this.name);
        var checkboxes = $(this).closest("form").find(":checkbox[name*=" + this.name + "]");
        if ($(this).is(':checked')) {
            checkboxes.prop('checked', true);
        } else {
            checkboxes.prop('checked', false);
        }
    });

    // Find all collapsable boxes and collapse/expand them
    $('#collapse_all').click(function () {
        console.log("Expand/collapse all ... ");
        var expand = $(this).is(":checked") ? true : false;
        $('.permission-box').each(function () {
            if (expand) {
                $(this).removeClass("collapsed-box");
            } else {
                $(this).addClass("collapsed-box");
            }
        });
    });
})(jQuery);

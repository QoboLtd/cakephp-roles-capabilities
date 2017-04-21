(function ($) {
    $('#permission-type').on('select2:select', function (event) {
        $('#type-inner-container>#permission-group').detach().prependTo('#type-outer-container');
        $('#type-inner-container>#permission-user').detach().prependTo('#type-outer-container');
        $('#type-outer-container>#permission-' + this.value).detach().prependTo('#type-inner-container');
    });

    $('#permission-type').on('select2:unselect', function (event) {
        $('#type-inner-container>#permission-group').detach().prependTo('#type-outer-container');
        $('#type-inner-container>#permission-user').detach().prependTo('#type-outer-container');
    });
})(jQuery);
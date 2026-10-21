jQuery( function( $ ) {
    //Dynamically adding of rows
    $( 'form' ).on( 'click', '.soft79_bulk_rules .soft79_wcpr_add_rule a', function( event ) {
        event.preventDefault();
        var newRow = $(this).closest('.soft79_bulk_rules').find('.soft79_wcpr_new_row').html();
        var rowElements = $.parseHTML(newRow);
        $(this).closest('.soft79_wcpr_add_rule').before(rowElements);
        $(this).closest('.soft79_bulk_rules').find('input').first().trigger('change');
    });

    //Dynamically deletion of rows
    $( 'form' ).on( 'click', '.soft79_bulk_rules .soft79_wcpr_delete_row', function( event ) {
            event.preventDefault();
            var $rules = $(this).closest('.soft79_bulk_rules');
            $(this).closest(".soft79_bulk_row").remove();
            $rules.find('input').first().trigger('change');
    } );

});

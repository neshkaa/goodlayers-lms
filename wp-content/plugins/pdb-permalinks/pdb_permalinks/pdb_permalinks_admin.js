jQuery(document).on( 'click', '.pdb-permalinks-process .notice-dismiss', function() {

    jQuery.ajax({
        url: ajaxurl,
        data: {
            action: 'pdb-permalinks-process_notice_dismiss'
        }
    })

})

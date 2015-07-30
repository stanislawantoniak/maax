jQuery(document).ready(function () {
    // create jqxTree
    jQuery('#jqxTree').jqxTree({
        height: '300px',
        //hasThreeStates: true,
        checkboxes: true,
        width: '90%',
        theme: "arctic"
    });

    jQuery('#jqxTree').jqxTree('hitTest', 10, 20);
    jQuery('#jqxTree').css('visibility', 'visible');

    jQuery("#browseCategory").on("show.bs.modal", function () {
        jQuery('#jqxTree').jqxTree('uncheckAll');
        jQuery('#jqxTree').jqxTree('collapseAll');

    });



    jQuery("#browseCategory button[type=submit]").click(function(){
        var items = jQuery('#jqxTree').jqxTree('getCheckedItems');

        var categories = [];
        jQuery(items).each(function (i, item) {
            categories.push(jQuery(item.element).data("category"));
        })

        jQuery("input[name=<?php echo $this->getFieldName(); ?>]").val(categories.join(","));
        jQuery("#browseCategory").modal("hide");

        jQuery('#jqxTree').jqxTree('uncheckAll');
        jQuery('#jqxTree').jqxTree('collapseAll');
    });
});
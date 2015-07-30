jQuery(document).ready(function () {
    jQuery('#jstree').jstree({
        "core": {
            "multiple": false,
            "animation": 0,
            'themes': {
                'name': 'proton',
                'responsive': true
            }
        },
        "checkbox": {
            "keep_selected_style": false,
            three_state: false
        },
        "plugins": ["checkbox"]

    });

    jQuery("#browseCategory").on("show.bs.modal", function () {
        jQuery('#jstree').jstree("deselect_all");
        jQuery('#jstree').jstree("close_all");

    });

    jQuery("#browseCategory button[type=submit]").click(function () {
        var items = jQuery('#jstree').jstree("get_checked", null, true);
        var checked_ids = [];
        jQuery(items).each(function (i, item) {
            checked_ids.push(item);
        });
        jQuery("input[name=landing_page_category]").val(checked_ids.join(","));
        jQuery("#browseCategory").modal("hide");

    });

});
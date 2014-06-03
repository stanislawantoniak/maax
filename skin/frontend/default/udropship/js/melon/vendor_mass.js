"use strict";

jQuery(document).ready(function ($) {
    $('#hide_columns_form a.check').click(function (e) {
        e.preventDefault();

        var a = $('#hide_columns_form #checked_group input[type=checkbox]'); //childElements();

        a.each(function () {
            $(this).prop('checked', true);
        })
    })
    $('#hide_columns_form a[name=uncheck]').click(function (e) {
        e.preventDefault();
        var a = $('#hide_columns_form #checked_group').find('input[type=checkbox]'); //childElements();
        a.each(function () {
            $(this).prop('checked', false);
        })
    })

    $('#saveColumnsModal button[name=saveColumns]').click(function (e) {
        e.preventDefault();
        $('#hide_columns_form').submit();
    })
});
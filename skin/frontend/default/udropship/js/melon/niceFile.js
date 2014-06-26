"use strict";

(function ($) {
    $.fn.niceFileField = function () {
        this.each(function (index, file_field) {
            file_field = $(file_field);
            var label = file_field.attr("data-label") || "Choose File";

            file_field.css({"display": "none"});
            var wrapperExist = file_field.next('.nice_file_field').length;
            if (wrapperExist < 1) {
                file_field.after("<div class=\"nice_file_field input-append\"><input class=\"input form-control\" type=\"text\"><a class=\"btn\">" + label + "</a></div>");

                var nice_file_field = file_field.next(".nice_file_field");
                nice_file_field.find("a").click(function () {
                    file_field.click()
                });
                file_field.change(function () {
                    nice_file_field.find("input").val(file_field.val());
                });
            }

        });
    };
})(jQuery);
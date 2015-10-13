jQuery(document).ready(function () {
    jQuery("[name=send_confirmation_email_button]").click(function () {
        alert("send_confirmation_email");
    });


    jQuery("[name=send_regulation_accept_button]").click(function () {
        alert("send_regulation_accept");
    });

    //wyślij prośbę o akceptację regulaminu aktywny - gdy regulamin niezaakceptowany
    jQuery("[name=regulation_accepted]").change(function () {
        var vendorRegulationAccepted = jQuery(this).val();
        if (vendorRegulationAccepted == 0) {
            jQuery("[name=send_regulation_accept_button]").prop("disabled", false).removeClass("disabled");
            //NIE pozwalamy na przełączenie statusu na aktywny - gdy regulamin zaakceptowany
            jQuery("[name=status1] option[value='A']").prop("disabled", true);
        } else {
            jQuery("[name=send_regulation_accept_button]").prop("disabled", true).addClass("disabled");
            //pozwalamy na przełączenie statusu na aktywny - gdy regulamin zaakceptowany
            jQuery("[name=status1] option[value='A']").prop("disabled", false);
        }
    }).change();

    //zresetuj hasło  aktywny  - gdy vendor aktywny
    jQuery("[name=status1]").change(function () {
        var vendorStatus = jQuery(this).val();
        if (vendorStatus == "A") {
            jQuery("[name=send_confirmation_email_button]").prop("disabled", false).removeClass("disabled");

        } else {
            jQuery("[name=send_confirmation_email_button]").prop("disabled", true).addClass("disabled");

        }
    }).change();
});

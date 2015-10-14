jQuery(document).ready(function () {


    //wyślij prośbę o akceptację regulaminu aktywny - gdy regulamin niezaakceptowany
    var vendorRegulationAccepted = jQuery("[name=regulation_accepted]").val();
    if (vendorRegulationAccepted == 0) {
        jQuery("[name=send_regulation_accept_button]")
            .prop("disabled", false)
            .removeClass("disabled")
            .attr("title", "");
        //NIE pozwalamy na przełączenie statusu na aktywny - gdy regulamin zaakceptowany
        jQuery("[name=status1] option[value='A']").prop("disabled", true);
    } else {
        jQuery("[name=send_regulation_accept_button]")
            .prop("disabled", true)
            .addClass("disabled")
            .attr("title", "przycisk wyślij prośbę o akceptację regulaminu aktywny gdy regulamin niezaakceptowany");
        //pozwalamy na przełączenie statusu na aktywny - gdy regulamin zaakceptowany
        jQuery("[name=status1] option[value='A']").prop("disabled", false);
    }

    //zresetuj hasło  aktywny  - gdy vendor aktywny
    var vendorStatus = jQuery("[name=status1]").val();
    if (vendorStatus == "A") {
        jQuery("[name=send_confirmation_email_button]")
            .prop("disabled", false)
            .removeClass("disabled")
            .attr("title", "");

    } else {
        jQuery("[name=send_confirmation_email_button]")
            .prop("disabled", true)
            .addClass("disabled")
            .attr("title", "Przycisk zresetuj hasło  aktywny gdy vendor aktywny");

    }

});

jQuery(function ($) {
    Mall.rma.edit = jQuery.extend({}, Mall.rma.new, {

        init: function () {
            "use strict";
            this._init();

            // Fix footer
            jQuery(window).resize();

            this.addUsefulFunctions();

        },
        // Internal init
        _init: function () {
            this.newRma = $("#edit-rma");
            this.steps = [this.step2];
            this._initStep2();
        },
        // Step 2 init
        _initStep2: function () {
            var s = this.step2,
                self = this,
                next = s.find("button.next"),
                back = s.find(".back"),
                zip = '',
                rmaId = jQuery("input[name=rma_id]").val();

            back.click(function () {
                window.location = "/sales/rma/view/id/" + rmaId;
                return false;
            });
            // Handle next click
            next.click(function () {
                var valid = true;

                //validate if user has chosen pickup date
                if (!s.find('input[name="rma[carrier_date]"]:checked').length) {
                    valid = false;
                }

                //--validation
                if (valid) {
                    self._submitForm();
                }
                return false;
            });

            //PICKUP DATE AND HOURS START
            self.addUsefulFunctions();
            self.initDateList(dateList);//INIT DATE LIST
            self.initDefaultSlider(dateList);//INIT SLIDER DEFAULT VALUES AND PARAMS
            self.attachSlideOnSlider();//CHANGE DESCRIPTIONS ON SLIDER SLIDE
            self.attachClickOnDate();//SET SLIDER, SAVE PICKUP TIME, WRITE MESSAGES
            self.initDateListValues(dateList);//INIT VALUES FOR DATE LIST
            jQuery('#pickup-date-form-panel input').first().click();//default set the first day

            //PICKUP DATE AND HOURS START END

            //##############################


            this.addressbook.init();


            zip = jQuery('.selected-postcode').filter(function( index ) {
                return jQuery(this).text().indexOf("{{") === -1;
            }).first().text();

            self.getDateList(zip);

            jQuery(this.addressbook.content).on("selectedAddressChange", function (e, address) {
                var zip = address.getData().postcode;
                self.getDateList(zip);
            });
        }
    });
});
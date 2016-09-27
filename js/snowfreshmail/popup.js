document.observe("dom:loaded", function() {

    var backdrop             = $('freshmail_backdrop'),
        formcontainer        = $('freshmail_formcontainer'),
        freshmailMainWrapper = $('freshmail-main-wrapper'),
        message              = $('freshmail_message'),
        form                 = $('freshmail_form'),
        input                = $$('.freshmail-container__field'),
        counterName          = formcontainer.readAttribute('data-counter'),
        timestampName        = formcontainer.readAttribute('data-timestamp'),
        successName          = formcontainer.readAttribute('data-success'),
        timeout              = formcontainer.readAttribute('data-timeout'),
        scrollPosition       = formcontainer.readAttribute('data-scroll-position'),
        successMsg           = formcontainer.readAttribute('data-success-msg'),
        redirect             = formcontainer.readAttribute('data-redirect'),
        errorMsg             = formcontainer.readAttribute('data-error-msg'),
        popupAppear          = formcontainer.readAttribute('data-popup-appear'),
        freshmailButton      = document.getElementById('freshmail-button');

    function checkPopupAllowed() {
        var maxDisplays = formcontainer.readAttribute('data-max-displays'),
            interval    = formcontainer.readAttribute('data-interval'),
            now         = Math.floor(+new Date() / 1000),
            counter     = Mage.Cookies.get(counterName),
            timestamp   = Mage.Cookies.get(timestampName),
            success     = Mage.Cookies.get(successName);

        if (success) {
            return false;
        }
        if (maxDisplays && parseInt(counter, 10) >= parseInt(maxDisplays,10)) {
            return false;
        } else {
            if (interval && (now - parseInt(timestamp,10)) < parseInt(interval,10)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Sending the form via AJAX
     */
    form.observe('submit', function(event) {
        event.preventDefault();
        var result = new Validation('freshmail_form');

        if (!result.validate()) {
            Event.stop(event);
            return false;
        }

        form.request({
            onCreate: function() {
                freshmailButton.classList.add('freshmail-container__button--disabled');
                freshmailButton.disabled = true;
                input.each(function (element) {
                    element.setAttribute('disabled', 'disabled');
                });
            },
            onComplete: function(data) {
                var response = data.responseText.evalJSON();

                if (data.status == '200' && response.status == 'success') {
                    Mage.Cookies.set(successName, true);

                    if (redirect) {
                        window.location.assign(redirect);
                        return false;
                    }

                    form.addClassName('freshmail-container__form--hide');
                    message.classList.remove('freshmail-container__message--error');
                    freshmailMessage(successMsg, 'freshmail-container__message--success');
                }
                else if (data.status == '200' && data.responseText != 'success') {
                    freshmailMessage(response.message, 'freshmail-container__message--error');
                }
                else {
                    freshmailMessage(errorMsg, 'freshmail-container__message--error');
                }

                freshmailButton.classList.remove('freshmail-container__button--disabled');
                freshmailButton.disabled = false;
                input.each(function(element) {
                    element.removeAttribute('disabled');
                });
            }
        });
        Event.stop(event);
    });

    /**
     * Display message
     */
    function freshmailMessage(msg, type) {
        message.addClassName(type);
        message.update(msg);
    }

    /**
     * Popup form on scroll
     */
    function freshmailPopupOnScroll() {
        if (!checkPopupAllowed()) {
            return;
        }
        Mage.Cookies.set(timestampName, Math.floor(+new Date() / 1000));

        var freshmailScroll     = document.viewport.getScrollOffsets(),
            freshmailBodyHeight = $$('body')[0].getHeight();

        if ((freshmailScroll.top * 100) / (freshmailBodyHeight - window.innerHeight - 50) > parseInt(scrollPosition, 10)) {
            var counter = Mage.Cookies.get(counterName);
            Mage.Cookies.set(counterName, +counter + 1);

            backdrop.appear({duration: 0.5, from: 0, to: 0.7});
            freshmailMainWrapper.appear({duration: 0.5, from: 0, to: 1});
            Event.stopObserving(window, 'scroll', freshmailPopupOnScroll);
        }
    }

    /**
     * Popup form on load
     */
    function freshmailPopup() {
        if (!checkPopupAllowed()) {
            return;
        }
        var counter = Mage.Cookies.get(counterName);
        Mage.Cookies.set(counterName, +counter + 1);
        Mage.Cookies.set(timestampName, Math.floor(+new Date() / 1000));
        setTimeout(function() {
            backdrop.appear({duration: 0.5, from: 0, to: 0.7});
            freshmailMainWrapper.appear({duration: 0.5, from: 0, to: 1});
        }, parseInt(timeout, 10));
    }

    if (popupAppear == 'after_scroll') {
        Event.observe(window, 'scroll', freshmailPopupOnScroll);
    } else {
        Event.observe(window, 'load', freshmailPopup);
    }

    function closestByClass(el, inClass) {
        while (el.className != inClass) {
            el = el.parentNode;
            if (!el) {
                return null;
            }
        }
        return el;
    }

    /**
     * Close popup on demand
     */

    window.addEventListener('click', function(e) {
        if (e.target.classList.contains('freshmail-close')) {
            backdrop.fade({duration: 1});
            freshmailMainWrapper.fade({duration: 1});
        }
        if (!closestByClass(e.target, 'freshmail-container')) {
            backdrop.fade({duration: 1});
            freshmailMainWrapper.fade({duration: 1});
        }
    });
});

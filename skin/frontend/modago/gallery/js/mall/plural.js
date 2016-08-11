/**
 * Created by Paweł Chyl <pawel.chyl@gmail.com> on 28.08.2014.
 */

/**
 *
 * Pluralization object for polish language.
 *
 */
var Plural;

Plural = {
    lang: "pl",

    /**
     * Returns index based on number.
     *
     * @param num {number}
     * @returns {number}
     * @private
     */
    _calculate: function (num) {
        "use strict";

        var result = 0;
        if (num === 1) {
            return 0;
        }

        if (num % 10 >= 2 && num % 10 <= 4 && (num % 100 < 10 || num % 100 >= 20)) {
            result = 1;
        } else {
            result = 2;
        }

        return result;
    },

    /**
     * Returns form based on given number.
     *
     * @param num {number}
     * @param forms {array}
     * @returns {*}
     */
    get: function (num, forms) {
        "use strict";

        return forms[this._calculate(num)];
    }
};
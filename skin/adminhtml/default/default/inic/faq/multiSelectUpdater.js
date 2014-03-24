/**
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magesupport.com/license/
 * 
 * @package    Inic_Faq
 * @copyright  Copyright (c) 2013 Inic
 * @license    http://www.magesupport.com/license/
 */
SelectAutoUpdater = Class.create();
SelectAutoUpdater.prototype = {
    initialize: function (firstSelect, secondSelect, selectFirstMessage, noValuesMessage, values, selected)
    {
        this.first = $(firstSelect);
        this.second = $(secondSelect);
        this.message = selectFirstMessage;
        this.values = values;
        this.noMessage = noValuesMessage;
        this.selected = selected;

        this.update();

        Event.observe(this.first, 'change', this.update.bind(this));
    },

    update: function()
    {
        this.second.length = 0;
        this.second.value = '';

        if (this.first.value && this.values[this.first.value]) {
            for (optionValue in this.values[this.first.value]) {
                optionTitle = this.values[this.first.value][optionValue];
                
                this.addOption(this.second, optionValue, optionTitle);
            }
            this.second.disabled = false;
        } else if (this.first.value && !this.values[this.first.value]) {
            this.addOption(this.second, '', this.noMessage);
        } else {
            this.addOption(this.second, '', this.message);
            this.second.disabled = true;
        }
    },

    addOption: function(select, value, text)
    {
        option = document.createElement('OPTION');
        option.value = value;
        option.text = text;
        
        //if (this.selected && option.value == this.selected) {
        if (this.selected && (this.selected).include(optionValue)) {
            option.selected = true;
            //this.selected = false;
        }

        if (select.options.add) {
            select.options.add(option);
        } else {
            select.appendChild(option);
        }
    }
}

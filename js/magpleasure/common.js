/**
 * MagPleasure Ltd.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magpleasure.com/LICENSE.txt
 *
 * @category   Magpleasure
 * @package    Magpleasure_Common
 * @copyright  Copyright (c) 2012-2015 MagPleasure Ltd. (http://www.magpleasure.com)
 * @license    http://www.magpleasure.com/LICENSE.txt
 */

/**
 * Display message on frontend
 *
 * @param string  message
 * @param string|undefined  id
 */
var sendMessage = function(message, id){

    if (typeof(id) == 'undefined'){
        id = 'message';
    }

    if (!$(id)){
        $$('.col-main').each(function(el){
            var div = document.createElement('div');
            $(div).addClassName(id);
            $(div).id = id;
            Element.insert(el, {'top': div });
        });
    }
    if ($(id)){
        $(id).innerHTML = message;
    }
};

var showAdminLoading = function (show){
    changeZIndex('loading-mask', 1500);
    if ($('loading-mask')){
        $('loading-mask').style.display = show ? 'block' : 'none';
    }
};

var changeZIndex = function(id, z){
    if ($(id)){
        $(id).style.zIndex = z;
    }
};

var addHiddenFiledToForm = function(formId, fieldName, value){
    if ($(formId)){
        var input = document.createElement('input');
        input.setAttribute('type', 'hidden');
        input.setAttribute('name', fieldName);
        input.setAttribute('value', value);
        $(formId).appendChild(input);
    }
};

var disabledEventPropagation = function(event){
    if (event.stopPropagation){
        event.stopPropagation();
    } else if(window.event){
        window.event.cancelBubble=true;
    }
};
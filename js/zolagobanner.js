"use strict";

jQuery(document).ready(function($){



});
function addPictureForm(imagesCount, row, target){
    jQuery.template("imagesFormRow", '\
        <div>\
        <div class="banner-config-row">\
        <div class="banner-config-item">\
        <label>Field Label: </label>\
        <input type="text" style="width:250px;"  name="groups[config][fields][zolagobannertypes][value][${row}][picture][${n}][picture_label]" class="input-text">\
        </div>\
        <div class="banner-config-item">\
        <td>Width: <input type="text" style="width:50px;"  name="groups[config][fields][zolagobannertypes][value][${row}][picture][${n}][pictures_w]" class="input-text"> px </td>\
        </div>\
        <div class="banner-config-item">\
        <td>Height: <input type="text" style="width:50px;"  name="groups[config][fields][zolagobannertypes][value][${row}][picture][${n}][pictures_h]" class="input-text"> px </td>\
        </div>\
        </div>\
        </div>');


    var count = parseInt(imagesCount);
    //construct rows
    var rowHTML ='';
    if(count > 0){
        for(var i=1; i <=count; i++){
            rowHTML += jQuery.tmpl('imagesFormRow', {
                row: row,
                n:i
            }).html();
        }
    }
    //put rows
    target.html(rowHTML);
}

function addCaptionForm(imagesCount, row, target){
    jQuery.template("captionFormRow", '\
        <div>\
        <div class="caption-config-row">\
        <div class="caption-config-item">\
        <label>Field Label: </label>\
        <input type="text" style="width:250px;"  name="groups[config][fields][zolagobannertypes][value][${row}][caption][${n}][caption_label]" class="input-text">\
        </div>\
        </div>');


    var count = parseInt(imagesCount);
    //construct rows
    var rowHTML ='';
    if(count > 0){
        for(var i=1; i <=count; i++){
            rowHTML += jQuery.tmpl('captionFormRow', {
                row: row,
                n:i
            }).html();
        }
    }
    //put rows
    target.html(rowHTML);
}


/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


if ( (typeof jQuery === 'undefined') && !window.jQuery ) {
        document.write(unescape("%3Cscript type='text/javascript' src='//ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js'%3E%3C/script%3E"));
    } else {
        if((typeof jQuery === 'undefined') && window.jQuery) {
            jQuery = window.jQuery;
        } else if((typeof jQuery !== 'undefined') && !window.jQuery) {
            window.jQuery = jQuery;
        }
    }
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
function setAllQuesTr(){
	var parent_detail = $$(".faq-grid-list");
	for(var x=0;x<parent_detail.length;x++)
	{
		var prd_detail = parent_detail[x].getElementsByClassName("faq-question");
		var height=0;
		for(var i=0;i<prd_detail.length;i++){
			if(parseInt(height)<parseInt(prd_detail[i].clientHeight)) {
				height = prd_detail[i].clientHeight;
			}
		}
		for(i=0;i<prd_detail.length;i++){
			if(height!=0){
				prd_detail[i].style.height=height+"px";
			}
		}
	}
}
function addLoadEvent(func) {
	var oldonload = window.onload;
	if (typeof window.onload != 'function') {
		window.onload = func;
	} else {
		window.onload = function() {
			if (oldonload) {
				oldonload();
			}
			func();
		}
	}
}

function expand(linkele){
	
	if(linkele.innerHTML=="Expand All"){
		linkele.innerHTML="Collaps All";
		//If any one element is already expanded then collaps it and then again expand
		$$('.faq-question').each(function(element) {
			if(element.next().clientHeight){
				element.next().toggle();
			}
		})
		$('expand-collaps').addClassName("collaps");
		$('expand-collaps').removeClassName("expand");
	}else{
		linkele.innerHTML="Expand All";
		//If any one element is already collapsed then expand it and then again collaps
		$$('.faq-question').each(function(element) {
			if(element.next().clientHeight==0){
				element.next().toggle();
			}
		})
		$('expand-collaps').addClassName("expand");
		$('expand-collaps').removeClassName("collaps");
	}
	
	$$('.faq-question').each(function(element) {
		element.next().toggle();
	})
}
addLoadEvent(setAllQuesTr);
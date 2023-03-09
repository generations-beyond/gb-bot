/**
 * GB•BOT - frontend.js
 *
 * This file is for global frontend javascript.
 * 
 */

// Close all popups when a scrollable anchor is clicked e.g. slideout navigation on a one-page website.
jQuery(document).ready(function($) {
	$(document).on('click', '.elementor-widget-nav-menu a[href*="#"]:not([href="#"])', function(event) {
		elementorProFrontend.modules.popup.closePopup({}, event);
	});
});

// Force any images to load for print styles
jQuery(document).ready(function($) {
	$('.visible-on-print img').each(function(){
		var img = document.createElement('img');
		img.setAttribute('src', $(this).attr('src'));
		img.setAttribute('style','display: none;');
		document.body.appendChild(img);
	});
});
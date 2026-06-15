var thwmscf_admin = (function($){
	$(function(){
		$( ".thpladmin-colorpick" ).each(function() {     	
			var value = $(this).val();
			$( this ).parent().find( '.thpladmin-colorpickpreview' ).css({ backgroundColor: value });
		});

	    $('.thpladmin-colorpick').iris({
			change: function( event, ui ) {
				$( this ).parent().find( '.thpladmin-colorpickpreview' ).css({ backgroundColor: ui.color.toString() });
			},
			hide: true,
			border: true
		}).click( function() {
			$('.iris-picker').hide();
			$(this ).closest('td').find('.iris-picker').show(); 
		});

		$('body').click( function() {
			$('.iris-picker').hide();
		});

		// $('.thpladmin-colorpick').click( function( event ) {
		$('.thpladmin-colorpick, .thwmscf-colorpreview').click( function( event ) { 
			event.stopPropagation();
		});

		var span_element = $('.thwmscf-colorpreview');
		span_element.on('click', function(){
			$(this).siblings('input').trigger('click');
		});
	});

	$(document).ready(function(e){
		var feature_popup = $(".thwmscf-pro-discount-popup");
	    var feature_popup_wrapper = $(".thwmscf-pro-discount-popup-wrapper");

	    if (feature_popup.length > 0) {
	    	$('body').css('overflow','hidden');
	        feature_popup[0].style.display = "flex";
	    }
	});

	function back_to_cart(elm){
		var cart_text_settings = $('.back-to-cart-show');		
		if(elm.checked){
			cart_text_settings.show();;
		}else{
			cart_text_settings.hide();
		}
	}

	function display_login(elm){
		var host_class = $('[name="i_title_login"]').closest('td');
		if(elm.checked){
			remove_class_wmsc_blur(host_class);
		}else{
			add_class_wmsc_blur(host_class);
		}
	}

	function shippingTitle(elm) {
		var host_class = $('[name="i_title_shipping"]').closest('td');
		hide_field_using_wmsc_blur($(elm), host_class);
	}

	function orderreviewtitle(elm) {
		var host_class = $('[name="i_title_confirm_order"]').closest('td');
		if(elm.checked){
			remove_class_wmsc_blur(host_class);
		}else{
			add_class_wmsc_blur(host_class);
		}
	}

	function order_review_right(elm){
		var form = elm.closest('form');
		var host_class = $(form).find('#th-show-review-right');

		hide_field_using_wmsc_blur($(elm), host_class);
	}

	function hide_field_using_wmsc_blur(elm, host_class){
		if(elm.prop('checked') != true){
			remove_class_wmsc_blur(host_class)
		}else{
			add_class_wmsc_blur(host_class);
		}
	}

	function remove_class_wmsc_blur(host_class){
		host_class.removeClass('wmsc-blur');
	}

	function add_class_wmsc_blur(host_class){
		host_class.addClass('wmsc-blur');
	}

	function layout_change(elm){
		var layout = elm.value;
		var tab_position = $('.display-tab-position');
		if(layout == 'thwmscf_time_line_step'){   	
			var color = '#050505';
			var text_color_active = $('input[name="i_step_text_color_active"]');
			if(text_color_active.val() == '#ffffff'){
				text_color_active.parent().find( '.thpladmin-colorpickpreview' ).css({ backgroundColor: color });
				text_color_active.val(color);
			}
			tab_position.hide();
		}else if(layout == 'thwmscf_accordion_step'){
			render_default_text_color();
			tab_position.hide();
		}else{
			render_default_text_color();
			tab_position.show();
		}
	}

	function render_default_text_color() {
		var color = '#ffffff';
		var text_color_active = $('input[name="i_step_text_color_active"]');
		if(text_color_active.val() != '#ffffff'){
			text_color_active.parent().find( '.thpladmin-colorpickpreview' ).css({ backgroundColor: color });
			text_color_active.val(color);
		}
	}

	var slideIndex = 1;
	var count = 0;
	var myTimer;
	var contentTimer;
	var slideshowContainer;

	window.addEventListener("load",function() {
		showSlides(slideIndex);
	    myTimer = setInterval(function(){plusSlides(1)}, 3000);
	    slideshowContainer = document.getElementsByClassName('th-user-review-section')[0];
	    if(slideshowContainer){
	    	slideshowContainer.addEventListener('mouseenter', pause)
		    slideshowContainer.addEventListener('mouseleave', resume)
			slideContent(count);
			contentTimer = setInterval(function(){ contentchange(1)},3000);
	    }
	})

	function pause() {
	  	clearInterval(myTimer);
	  	clearInterval(contentTimer)
	};

	function resume(){
		clearInterval(myTimer);
	  	clearInterval(contentTimer)
	  	myTimer = setInterval(function(){plusSlides(slideIndex)}, 3000);
	  	contentTimer = setInterval(function(){ contentchange(count)},3000);
	};

	function showSlides(n){
		var i;		  
	  	var dots = document.getElementsByClassName("th-review-nav-btn");
	  	
	  	if(dots.length>0){
	  		if (n > dots.length) {
	  			slideIndex = 1
	  		}
			for (i = 0; i < dots.length; i++) {
				dots[i].className = dots[i].className.replace(" active", "");
			}
	  		dots[slideIndex-1].className += " active";	
	  	}
	}

	function plusSlides(n){
		clearInterval(myTimer);
		if (n < 0){
			showSlides(slideIndex -= 1);
		} else {
			showSlides(slideIndex += 1); 
		}
		if (n === -1){
			myTimer = setInterval(function(){plusSlides(n + 2)}, 3000);
		} else {
			myTimer = setInterval(function(){plusSlides(n + 1)}, 3000);
		}
	}

	function accordionexpand(elm){
		var curr_panel = elm.getElementsByClassName("panel")[0];
		var accordion_qstn = elm.getElementsByClassName("accordion-qstn")[0];
		var accordion_qstn_img = elm.getElementsByClassName("accordion-img")[0];
		var accordion_qstn_img_opn = elm.getElementsByClassName("accordion-img-opn")[0];
		var accordion_qstn_para = accordion_qstn.querySelector('p');
		var panel = document.getElementsByClassName("panel");
		var i;
		for(i = 0; i < panel.length; i++){
			if (curr_panel != panel[i]) {
				if(panel[i].style.display === "block"){
					var parentaccordion = panel[i].parentNode;
					var parent_accordion_qstn = parentaccordion.getElementsByClassName("accordion-qstn")[0];
					var parent_accordion_img = parentaccordion.getElementsByClassName("accordion-img")[0];
					var parent_accordion_img_opn = parentaccordion.getElementsByClassName("accordion-img-opn")[0];
					var parent_accordion_qstn_p = parent_accordion_qstn.querySelector('p');
					panel[i].style.display = "none";
					parent_accordion_qstn_p.style.color = "#121933";
					parentaccordion.style.zIndex = "unset";
					parentaccordion.style.borderColor = "#dfdfdf";
					parent_accordion_qstn.style.marginTop = "0px";
					parent_accordion_img.style.display = "block";
					parent_accordion_img_opn.style.display = "none";
				}
			}
		}
		if (curr_panel.style.display === "block") {
			curr_panel.style.display = "none";
			accordion_qstn_para.style.color = "#121933";
			elm.style.zIndex = "unset";
			accordion_qstn.style.marginTop = "0";
			elm.style.borderColor = "#dfdfdf";
			accordion_qstn_img.style.display = "block";
			accordion_qstn_img_opn.style.display = "none";
		} else {
			curr_panel.style.display = "block";
			accordion_qstn_para.style.color = "#6E55FF";
			elm.style.zIndex = "1";
			elm.style.borderColor = "#6E55FF";
			accordion_qstn.style.marginTop = "1.53rem";
			accordion_qstn_img.style.display = "none";
			accordion_qstn_img_opn.style.display = "block";
		}
	}

	function slideContent(n){
		var review_heading = ['Great support','Great Plugin Great Support','Great Plugin and Professional Assistance', 'Excellent plugin highly customizable', 'Fantastic Support – and a really valuable plugin'];
		var headingContainer = document.getElementsByClassName('th-review-heading');
		var review_content = ['I used the pro version of the plugin in a project where I needed to add several customizations.The support was really nice and answered every one of my questions with a clear neat response and some snippets that resolve my issue every time I contact them.',
			'I bought the paid version to unlock all the features and get support, since my website is in RTL it usually has some styling issues with most plugins and I’m glad to pay some money for good products and help.This time, I couldn’t solve the styling issues myself – nonetheless, the support team was incredibly helpful and professional.Solved all of my problems in a matter of days.Besides, this plugin is a must for every shop that respects itself for obvious reasons. Thank you Themehigh for your help!!',
			'Very well done plugin, clean code and 100% working. We needed assistance and the team was very helpful and professional to accommodate our requests. We are evaluating, based on customer needs, to install it on all e-commerce and, when necessary, switch to the pro version. 5 stars you deserve!',
			'We purchased the premium version of this plugin and are very happy with it. We have been able to customize a new Woocommerce site checkout into 3 separate steps which makes the user flow much better. The main reason we chose this plugin was the ability to add a custom step to the checkout process (step 2 in our case) which we used to asked the custom important non-billing related details about their order.We also were able to add our own custom checkout form validation thanks in part to their helpful customer support team.I recommend this plugin to both non-techie site owners looking to make their checkout pages better as well as developers looking to create a more expansive user experience.',
			'I’ve needed a few customizations and their team was AMAZING to deal with. The plugin is great but their communication and support were OFF THE CHARTS FANTASTIC. Thank you thank you thank you!!!! I wish all plugins were as well supported as this one. Would highly recommend.',
		];
		var contentContainer = document.getElementsByClassName('th-review-content');
		var review_author = ['Dexter0015','coldpie','developress','Vegas Website Designs','sock2me'];
		var authorContainer = document.getElementsByClassName('th-review-user-name');
		if(n > review_heading.length - 1){
			count = 0;
		}
		headingContainer[0].innerHTML =  review_heading[count];
		contentContainer[0].innerHTML = review_content[count];
		authorContainer[0].innerHTML = review_author[count];
	}

	function contentchange(n){
		clearInterval(contentTimer);
	  	if(n<0){
	  		slideContent(count -= 1);
	  	}else{
	  		slideContent(count += 1);
	  	}
	  	if (n === -1){
		    contentTimer = setInterval(function(){ contentchange(1)},3000);
		} else {
		    contentTimer = setInterval(function(){ contentchange(1)},3000);
		}
	}

	function plusSlides(n){
		clearInterval(myTimer);
		if (n < 0){
			showSlides(slideIndex -= 1);
		} else {
			showSlides(slideIndex += 1); 
		}
		if (n === -1){
			myTimer = setInterval(function(){plusSlides(n + 2)}, 3000);
		} else {
			myTimer = setInterval(function(){plusSlides(n + 1)}, 3000);
		}
	}

	function currentSlide(n){
		clearInterval(myTimer);
		myTimer = setInterval(function(){plusSlides(n + 1)}, 3000);
		showSlides(slideIndex = n);
		clearInterval(contentTimer);
		contentTimer = setInterval(function(){ contentchange(n+1)},3000);
		slideContent(count = n);
	}

	function widgetPopUp() {
		var x = document.getElementById("myDIV");
    	var y = document.getElementById("myWidget");
    	var th_animation=document.getElementById("th_quick_border_animation")
    	var th_arrow = document.getElementById("th_arrow_head");

    	if (x.style.display === "none" || !x.style.display) {
        	x.style.display = "block";
        	th_arrow.style="transform:rotate(-12.5deg);";
        	th_animation.style="box-shadow: 0 0 0 0 rgba(0, 0, 0, 0);";
        	th_animation.style.animation='none';
    	} else {
        	x.style.display = "none";
        	th_arrow.style="transform:rotate(45deg);"
        	th_animation.style.animation='pulse 1.5s infinite';
    	}
	}
	function widgetClose() {
    	var z = document.getElementById("myDIV");
	    var za = document.getElementById("myWidget");
		var th_animation=document.getElementById("th_quick_border_animation")
	    var th_arrow = document.getElementById("th_arrow_head");
	    z.style.display = "none";
		th_arrow.style="transform:rotate(45deg);"
	    th_animation.style.animation='pulse 1.5s infinite';
	}

	function popUpClose(elm){
		var addressValue = elm.getAttribute("href");
		window.open(addressValue);
		var link = document.getElementById("thwmscf-discount-close-btn");
		link.click();
	}

	return {
		back_to_cart : back_to_cart,
		display_login : display_login,
		shippingTitle : shippingTitle,
		layout_change : layout_change,
		render_default_text_color : render_default_text_color,
		orderreviewtitle : orderreviewtitle,
		order_review_right : order_review_right,
		thwmscAccordionexpand : accordionexpand,
		currentSlide : currentSlide,
		thwmscfwidgetPopUp : widgetPopUp,
		thwmscfwidgetClose : widgetClose,
		thwmscfPopUpClose  : popUpClose,
	}
}(window.jQuery, window, document))

function thwmscfBackToCart(elm){
	thwmscf_admin.back_to_cart(elm);
}
function thwmscfDisplayLogin(elm){
	thwmscf_admin.display_login(elm);
}
function thwmscfShippingTitle(elm){
	thwmscf_admin.shippingTitle(elm);
}
function thwmscfOrderReview(elm){
	thwmscf_admin.orderreviewtitle(elm);
	thwmscf_admin.order_review_right(elm);
}
function thwmscLayoutChange(elm){
	thwmscf_admin.layout_change(elm);
}
function thwmscAccordionexpand(elm){
	thwmscf_admin.thwmscAccordionexpand(elm);
}
function currentSlide(elm) {
	thwmscf_admin.currentSlide(elm);
}
function thwmscfwidgetPopUp() {
	thwmscf_admin.thwmscfwidgetPopUp();
}
function thwmscfwidgetClose() {
	thwmscf_admin.thwmscfwidgetClose();
}
function thwmscfPopUpClose(elm) {
	thwmscf_admin.thwmscfPopUpClose(elm);
}
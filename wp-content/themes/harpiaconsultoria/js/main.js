$(document).ready(function($){
    $(this).scrollTop(0);
    
    setTimeout(function() { 
        $('body').addClass('d-block');
    }, 500);

    $(".mCustomScrollbar.text-inner").mCustomScrollbar({
        axis:"y"
    });    

    $(".mCustomScrollbar.diagnostics-list").mCustomScrollbar({
        axis:"x"
    });        

    let tax_link = $('.header .menu-item-type-taxonomy > a, .header .menu-item-has-children > a');

    if(tax_link) {
        for (let index = 0; index < tax_link.length; index++) {
            const element = tax_link[index];
            if(window.innerWidth <= 992) {
                $(element).attr('href', 'javascript:void(0)');
            } else {
                if($(element).attr('href') == '/servicos' || $(element).hasClass('menu-item-type-taxonomy')) {
                    $(element).attr('href', 'javascript:void(0)');
                }
            }
        }
    }

	$( "body" ).on( "click", ".hamburger", function(e) {
		$('.hamburger, .navigation.mobile').toggleClass('is-active');
        $('.sidebar.mobile').removeClass('is-active')
	});	

    let default_url = '';

	$( "body" ).on( "mouseover", ".submenu-inner .submenu *", function(e) {
        default_url = $(this).closest('[data-depth="0"]').find('.thumbnail').attr('src');

        if($(this).hasClass('menu-item-has-children')) {
            let img = $(this).attr('data-img');
            $(this).closest('[data-depth="0"]').find('.thumbnail').attr('src', img);
        } else if($(this).hasClass('menu-item-type-taxonomy')) {
            let img = $(this).parent().attr('data-img');
            $(this).closest('[data-depth="0"]').find('.thumbnail').attr('src', img);
        } else {
            if($(this).closest('[data-depth=1]') && $(this).closest('[data-depth=1]').parent()) {
                let el = $(this).closest('[data-depth=1]').parent();
                if(el[0]) {
                    let img = el[0].dataset.img;
                    $(this).closest('[data-depth="0"]').find('.thumbnail').attr('src', img);
                }
            }
        }
	});

	$( "body" ).on( "click", "#menu-main > li", function(e) {
        $(this).toggleClass('is-active');
	})    

    $("header").before($("header").clone().addClass("sticky"));

    $(".header .topbar").before($(".blog .sidebar").clone().addClass("d-none mobile"));

	$( "body" ).on( "click", ".sidebar input[name='s'][readonly], .sidebar button, .sidebar .searchbar", function(e) {
        if(window.innerWidth <= 992) {
            $('.sidebar.mobile').addClass('is-active');
            console.log(true)
        }
	});	  
    
	$( "body" ).on( "click", ".close-button", function(e) {
        $('.is-active').removeClass('is-active');
	});

    // $(".diagnostics h2.title").before($(".diagnostics h2.title").clone().addClass("fixed col-lg-4"));

    // if($('.diagnostics-list').length) {
    //     window.addEventListener('scroll', function() {
    //         var element = document.querySelector('.diagnostics-list');
    //         var position = element.getBoundingClientRect();
    
    //         // if(position.top >= 0 && position.bottom <= window.innerHeight) {
    //         // 	console.log('Element is fully visible in screen');
    //         // }
    
    //         if (position.top + 500 < window.innerHeight && position.bottom >= 0) {
    //             $(".diagnostics").addClass("scroll")
    //         } else {
    //             $(".diagnostics").removeClass("scroll")
    //         }
    //     });  
    // }

    $(window).on("scroll", function() {
        $(".sticky").toggleClass("stuck", ($(window).scrollTop() > 49));
        $(".is-active").removeClass('is-active')
    });

	$( "body" ).on( "click", ".navigation.mobile .menu-item-has-children > a", function(e) {
        $(this).parent().toggleClass('is-active');
	});	   

	$( "body" ).on( "click", ".navigation.mobile .menu-item > a.menu-item-has-children", function(e) {
        if(window.innerWidth <= 992) {
            e.preventDefault();
        }
	});	       

    $('.yu2fvl').yu2fvl();

    if($("body").hasClass("home")) {
        window.addEventListener('scroll', function() {
            var element = document.querySelector('.quem-somos');
            var position = element.getBoundingClientRect();
    
            if (position.top > window.innerHeight && position.bottom >= 0) {
                $('body').removeClass('scrolled');
            } else {
                $('body').addClass('scrolled');
            }
        });  
    }
    
	$( "body" ).on( "click", ".accordion-list .title", function(e) {
        $(this).next().toggleClass("d-none d-block");
        $(this).find('[class*="fa"]').toggleClass("fa-plus fa-minus");
	});	  

    if($("body").hasClass("archive")) {
        const queryString = window.location.search;
        const urlParams = new URLSearchParams(queryString);
        if(urlParams.get('id')) {
            $(`.taxonomies-filter-item[data-slug="${urlParams.get('id')}"]`).parent().find('.active').removeClass('active'),
            $(`.taxonomies-filter-item[data-slug="${urlParams.get('id')}"]`).addClass('active'),
            $(`.taxonomies-terms-item:not([data-slug="${urlParams.get('id')}"])`).addClass('d-none').removeClass('d-block'),
            $(`.taxonomies-terms-item[data-slug="${urlParams.get('id')}"]`).addClass('d-block').removeClass('d-none');

            setTimeout(function() { 
                document.querySelector('.taxonomies-filter').scrollIntoView({block: "start"});
            }, 100);                 
        }
    }
    
	$( ".archive" ).on( "click", ".taxonomies-filter-item", function(e) {
        e.preventDefault();

        setTimeout(function() { 
            document.querySelector('.taxonomies-filter').scrollIntoView({block: "start"});
        }, 100);        

        $(this).closest('ul').find('li').not($(this)).removeClass('active'),
        $(this).closest('li').addClass('active');
        
        let cat = e.target.dataset.cat;
        $(`.taxonomies-terms-item:not([data-cat="${cat}"])`).addClass('d-none').removeClass('d-block'),
        $(`.taxonomies-terms-item[data-cat="${cat}"]`).addClass('d-block').removeClass('d-none');
	});	  
    
	$( "body" ).on( "mouseover", ".banner .btn", function(e) {
        $(this).css({
            backgroundColor: $(this).next()[0] ? $(this).next()[0].dataset.background : $(this).prev()[0].dataset.background,
        });
	}).on( "mouseleave", ".banner .btn", function(e) {
        $(this).css({
            backgroundColor: e.target.dataset.background,
        });
	});		    

    let events = ['scroll', 'resize'];

    events.forEach(event => {
        $(window).on(event, function () {
            $(".is-active").removeClass('is-active')
        });	  
    });
    
	$( "body" ).on( "change", "[name='switcher']", function(e) {
        console.log(e.target.value)
        let fid = e.target.value;
        let el = $("[data-form]");

        if(el.attr('data-form') === fid) {
            el.toggleClass('d-block d-none')
        } else {
            el.toggleClass('d-none d-block')
        }
	})    

    // 

	setTimeout(function () {
        if (sessionStorage.getItem('name') !== "whatsappIconMessage") {
            $('.whatsapp-icon-message').addClass('active');
        }
    }, 12000);

    $('.whatsapp-icon-message-close').click(function () {
        sessionStorage.setItem('name', 'whatsappIconMessage');
        $('.whatsapp-icon-message').removeClass('active');
    });

    setTimeout(function() {
        $('#module-whatsapp').css('visibility', 'visible');
    }, 2000);

    $('.whatsapp-btn, [href*="https://api.whatsapp.com"]').click(function(e) {
        e.preventDefault();

        if ($('.whatsapp-btn').hasClass('active')) {
            $('.whatsapp-btn').addClass('not-active');
            $('.whatsapp-btn').removeClass('active');
            $('#module-whatsapp-container').removeClass('active');
            setTimeout(function() {
                if (sessionStorage.getItem('name') !== "whatsappIconMessage") {
                    $('.whatsapp-icon-message').addClass('active');
                }
            }, 2000);
        } else {
            $('.whatsapp-btn').addClass('active');
            $('.whatsapp-btn').removeClass('not-active');
            $('#module-whatsapp-container').addClass('active');
            $('.whatsapp-icon-message').removeClass('active');
        }
    });

    setTimeout(function() {
        $('#module-whatsapp').css('visibility', 'visible');
    }, 2000);

    var disableSubmit = false;
    
    jQuery('button.module-whatsapp-content-form-button').click(function() {
        jQuery('button.module-whatsapp-content-form-button').addClass("disabled");
        jQuery('button.module-whatsapp-content-form-button').text('INICIANDO...');
        if (disableSubmit == true) {
            return false;
        }
        disableSubmit = true;
        return true;
    })
    
    document.addEventListener('wpcf7submit', function(event) {
        jQuery('#' + event.detail.unitTag + ' button.module-whatsapp-content-form-button').removeClass("disabled");
        jQuery('#' + event.detail.unitTag + ' button.module-whatsapp-content-form-button').text('INICIAR CONVERSA');
        disableSubmit = false;
    }, false);    
});
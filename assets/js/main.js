

var stickyOffset = $('nav').height();
var topBtnOffset = 150;
function onScroll(){
    //Sticky Header
    var sticky = $('nav'),
        scroll = $(window).scrollTop();
    if (scroll >= stickyOffset)
        sticky.addClass('sticky');
    else
        sticky.removeClass('sticky');
    //Return to Top
    if ($(this).scrollTop() >= topBtnOffset) {
        $('#return-to-top').fadeIn(200);
    } else {
        $('#return-to-top').fadeOut(200);
    }
}
function onResize(){
    var w = $(window).width();
    if(w<768){
        if(!$('#roadmap-content .row').hasClass('slick-slider')){
            $('#roadmap-content .row').slick({
                slidesToShow: 1.5,
                infinite: false,
                slidesToScroll: 1,
                centerMode: true
            });
        }
    }
    else{
        if($('#roadmap-content .row').hasClass('slick-slider')){
            $('#roadmap-content .row').slick('destroy');
            $('#roadmap-content .row').removeAttr('tabindex');
        }
    }
}

$(document).ready(function(){
    //API Call
    $.getJSON("libraries/api.php", function (data) {
        var oPrice = data.price,
            pMarket = Math.round(parseInt(oPrice.marketcap_btc.replace(' BTC', ''))),
            pPrice = oPrice.price_btc.replace(' BTC', ''),
            pVolume = oPrice.volume_btc.replace(' BTC', '');
        
        $('.dyn-cap').html(pMarket + ' <i class="fab fa-btc"></i>');
        $('.dyn-pri').html(pPrice + ' <i class="fab fa-btc"></i>');
        $('.dyn-vol').html(pVolume + ' <i class="fab fa-btc"></i>');
    });
    //onResize
    $(window).resize(onResize);
    onResize();
    //onScroll
    $(window).scroll(onScroll);
    onScroll();
    //
//    $('#hero-bar a').mouseover(function(){
//        $(this).find('img').attr('src',$(this).find('img').data('hover'));
//    });
//    $('#hero-bar a').mouseout(function(){
//        $(this).find('img').attr('src',$(this).find('img').data('original'));
//    });
    
    //
    $('#home_features').DrSlider({
        'transition': 'door'
    });
    
    //Features Slider
    $('#features .items').slick({
        slidesToShow: 1,
        infinite: true,
        dots: true,
        arrows: false,
        autoplay: true,
        customPaging: function (slider, i) {
             return '<a></a>';
        }
//        autoplay: true,
//        autoplaySpeed: 3000,
    });
    /* Sidr Menu */
    $('.navbar-toggler').sidr({
        name: 'sidr-main',
        source: '#main-menu',
        side: 'left'
    });
    //Smooth Scroll
    var isScrollingAnim = false;
    $('nav a[href*="#"]').not('[href="#"]').not('[href="#0"]').click(function (event) {
        if (location.pathname.replace(/^\//, '') == this.pathname.replace(/^\//, '') && location.hostname == this.hostname) {
            $('.page-nav a').removeClass('active');
            $(this).addClass('active');
            var target = $(this.hash);
            target = target.length ? target : $('[name=' + this.hash.slice(1) + ']');
            if (target.length) {
                event.preventDefault();
                isScrollingAnim = true;
                var mov = 0;
                if ($(window).width() < 768) {
                    mov = target.offset().top - $('nav').outerHeight();
                } else {
                    mov = target.offset().top - $('nav').outerHeight();
                }
                $('html, body').stop();
                $('html, body').animate({
                    scrollTop: mov
                }, 1000, function () {
                    isScrollingAnim = false;
                });
            }
        }
    });
    $('#return-to-top').click(function(e) {
        e.preventDefault();
        $('body,html').animate({scrollTop : 0}, 500);
    });
    
    AOS.init();
});
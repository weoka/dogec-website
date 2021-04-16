

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

//SHOW PROPER WALLET VERSION
var OS;
var bit = '64';

function getOS() {
    let userAgent = window.navigator.userAgent,
        platform = window.navigator.platform,
        macosPlatforms = ['Macintosh', 'MacIntel', 'MacPPC', 'Mac68K'],
        windowsPlatforms = ['Win32', 'Win64', 'Windows', 'WinCE'],
        iosPlatforms = ['iPhone', 'iPad', 'iPod'],
        os = null;

    if (macosPlatforms.indexOf(platform) !== -1) {
        os = 'Mac OS';
    } else if (iosPlatforms.indexOf(platform) !== -1) {
        os = 'iOS';
    } else if (windowsPlatforms.indexOf(platform) !== -1) {
        os = 'Windows';
    } else if (/Android/.test(userAgent)) {
        os = 'Android';
    } else if (!os && /Linux/.test(platform)) {
        os = 'Linux';
    }

    return os;
}

async function showVersion(){
    const request = await fetch('https://api.github.com/repos/dogecash/dogecash/releases/latest');
    const content = await request.json();
    const lastVersion = (content['tag_name']).replace('v','');
    const OS = getOS();

    if(OS =='Windows'){
        var Download = document.getElementById('download');
        $("#download").fadeIn();
        Download.setAttribute('href', `https://github.com/dogecash/dogecash/releases/download/v${lastVersion}/DogeCash-${lastVersion}-win64-setup-unsigned.exe`);
        Download.innerHTML = OS + ' ' + ' Wallet Download';
    }else if(OS =='Linux'){
        var Download = document.getElementById('download');
        Download.setAttribute('href', `https://github.com/dogecash/dogecash/releases/download/v${lastVersion}/DogeCash-${lastVersion}-x86_64-linux-gnu.tar.gz`);
        Download.innerHTML = OS + ' ' + ' Wallet Download';
        $("#download").fadeIn();
    }else if(OS =='Mac OS'){
        var Download = document.getElementById('download');
        Download.setAttribute('href', `https://github.com/dogecash/dogecash/releases/download/v${lastVersion}/DogeCash-${lastVersion}-osx-unsigned.dmg`);
        Download.innerHTML = OS + ' ' + ' Wallet Download';
        $("#download").fadeIn();
    }
}

showVersion();
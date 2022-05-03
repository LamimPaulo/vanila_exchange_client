
jQuery(document).ready(function($) {
    var c = localStorage.getItem("ColorMode")
    $(".se-pre-con").fadeOut("slow");
    c == 'light' ? $('#cpswitch').attr('href', 'resources/templates/Cointrade/css/skins/light.css') : $('#cpswitch').attr('href', 'resources/templates/Cointrade/css/skins/dark.css');
    c == 'light' ? $('#hrefColor').attr('class', 'hrefColordark') : $('#hrefColor').attr('class', 'hrefColorlight');
});

var settings = $.extend({
            styleSheet: '#cpswitch'
            , colors: {
                'default': 'skins/default.css'
            , }
            , linkClass: 'linka'
        });

    $("#hrefColor").click(function(){
        var c = localStorage.getItem("ColorMode")
        if (c == 'light'){
            localStorage.setItem("ColorMode", "dark");
            $(settings.styleSheet).attr('href', 'resources/templates/Cointrade/css/skins/dark.css');
            $('#hrefColor').attr('class', 'hrefColorlight');
        }else{
            localStorage.setItem("ColorMode", "light");
            $(settings.styleSheet).attr('href', 'resources/templates/Cointrade/css/skins/light.css');
            $('#hrefColor').attr('class', 'hrefColordark');
        }
    });


$(document).ready(function() {

    //get all the disguised links
    var disguisedLinks = $('.email-disguised');

    //add click listener
    disguisedLinks.click(function(event) {

        //fetch initial vars
        var href = $(this).attr('href');

        //reverse address
        var chars = [];
        for (var i=href.length - 1; i>=7; i--) {
            chars.push(href.substr(i, 1));
        }
        var address = href.substr(0, 7) + chars.join('').replace('[at]', '@');

        //mark event as handled and open mailto-link
        event.preventDefault();
        window.location.href = address;

    });

});

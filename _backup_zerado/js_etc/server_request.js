var serverRequest = (data, url, animate = true) => {
    if(animate) {
        $('body').append('<div id="for_loading_div"><i id="loading" class="fa fa-spinner" aria-hidden="true"></i></div>');
        rotateLoad();
    }

    const sendRequest = new Promise((resolve, reject) => {
        $.ajax({
            url: url,
            data: data,
            type: 'POST',
            contentType: false, // NEEDED, DON'T OMIT THIS (requires jQuery 1.6+)
            processData: false, // NEEDED, DON'T OMIT THIS
            timeout: 60000,
            error: function() {   // A function to be called if request fails
                clearTimeout(t);
                $('#for_loading_div').remove();
                reject('Server request error.');
            },
            success: function(reply) {  // A function to be called if request succeeds
                clearTimeout(t);
                $('#for_loading_div').remove();
                resolve(reply);	
            }
        });
    });

    return sendRequest;
}

//animate load
var degree = 0;
var t;
function rotateLoad() {
    if(degree >= 360) {
        degree = 0;
    }
    $('#loading').css('-webkit-transform', 'rotate(' + degree + 'deg)',
                    '-moz-transform', 'rotate(' + degree + 'deg)',
                    '-ms-transform', 'rotate(' + degree + 'deg)',
                    '-o-transform', 'rotate(' + degree + 'deg)',
                    'transform', 'rotate(' + degree + 'deg)');
    degree += 3;
    
    t = setTimeout(rotateLoad, 15);
};
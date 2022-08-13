jQuery(function($){

    $('body').on( 'click', '.img-wrapper', function(e){

        e.preventDefault();

        var imgContainer = $(this),
            customUploader = wp.media({
                title: 'Insert image',
                library : {
                    // uploadedTo : wp.media.view.settings.post.id,
                    type : 'image'
                },
                button: {
                    text: 'Use this image'
                },
                multiple: false
            }).on('select', function() {
                var attachment = customUploader.state().get('selection').first().toJSON();
                imgContainer.html('<img src="' + attachment.url + '">').next().show();
                $("#_image_field").val(attachment.id)
            }).open();

    });

    $('body').on('click', '.remove-custom-image', function(e){

        e.preventDefault();

        var button = $(this);
        $("#_image_field").val(''); // emptying the hidden field
        button.hide().prev().html('');
    });

    $("#publish").replaceWith($("<input>", {id: "publish", type:"submit", name:"save", "class": "button button-primary button-large", "value": "Replaced update"}))

    $('#general_product_data').append($("<button>", {id:"reset-custom-fields", class:"button button-primary button-large", text:"Reset custom fields"}))

    $('body').on( 'click', '#reset-custom-fields', () => {
        $("#_select, #_image_field, #_date_field").val('')
        $(".img-wrapper").html('')
        alert("Don't forget update product to changes have effect!")
        return false;
    })
});

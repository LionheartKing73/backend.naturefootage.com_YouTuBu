$(function() {
    $('.scrollPane').jScrollPane({
        verticalDragMinHeight: 20,
        verticalDragMaxHeight: 20
    });
})

$(document).ready(function () {

    // Add to cart and bin events
    $(".toBin").click(function(){

        addToBin($(this), $(this).attr("href"));

        return false;

    });

    $(".toCart").click(function(){

        //addToCart($(this), $(this).attr("href"));
        addToCart($(this));

        return false;

    });

});



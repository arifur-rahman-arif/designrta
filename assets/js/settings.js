;(function($) {
  "use strict";

 // Coupon code js
    $('input#gform_submit_button_5').attr('disabled', 'disabled');

    $('input#input_5_1').on('input',function(e){
        var changeval = $(this).val();
        var allcouponcode = '<?php echo json_encode($couponcode); ?>';
        var input_value = allcouponcode.indexOf(changeval);
        console.log(input_value);
        if (input_value === -1) {
            $('input#gform_submit_button_5').attr('disabled', 'disabled');
        }
        else {
            $('input#gform_submit_button_5').removeAttr('disabled');
        }
    });

    //

    // $("#upstream_new_project").on('click', function(){
    //     $("#modal-project").addClass('show');
    //     $(".modal-backdrop").addClass('show');
    // });

    // $(".o-comment-control").on('click', function(){
    //     $("#modal-reply_comment").addClass('show');
    //     $(".modal-backdrop").addClass('show');
    // });

}(jQuery));
(function ($) {
    "use strict";

    $(".approve_design_btn").on("click", function (e) {
        e.preventDefault();
        var post_id = $(this).attr("id");
        $.ajax({
            type: "POST",
            url: upstream.ajaxurl,
            data: {
                action: "custom_update_post",
                post_id: post_id,
            },
        });
        setInterval("location.reload()", 3000);
    });

    $(".download_all_files").on("click", function (e) {
        e.preventDefault();

        var post_id = $(this).attr("id");

        $.ajax({
            type: "POST",
            url: upstream.ajaxurl,
            data: {
                action: "create_zip_file_for_download",
                post_id: post_id,
            },

            success: function (response) {
                if ("NO FILES" === response) {
                    alert(response);
                } else {
                    window.location = response;
                }
            },

            error: function (error) {
                console.log(error);
            },
        });
    });

    $(".request_for_approve_design_btn").on("click", function (e) {
        e.preventDefault();
        var post_id = $(this).attr("id");
        $.ajax({
            type: "POST",
            url: upstream.ajaxurl,
            data: {
                action: "after_request_ready_for_approve",
                post_id: post_id,
            },
        });
        setInterval("location.reload()", 3000);
    });

    $("#add_comment_btn button").on("click", function () {
        console.log("submited");
        var post_id = $(this).attr("data-id");
        $.ajax({
            type: "POST",
            url: upstream.ajaxurl,
            data: {
                action: "after_add_comment_change_status",
                post_id: post_id,
            },
            success: function (response) {
                setInterval("location.reload()", 2000);
            },
        });
    });

    $("#file_submit_form button").on("click", function () {
        console.log("submited");
        var post_id = $(this).attr("data-id");
        $.ajax({
            type: "POST",
            url: upstream.ajaxurl,
            data: {
                action: "after_file_submit_form_change_status",
                post_id: post_id,
            },
            success: function (resp) {
                setInterval("location.reload()", 2000);
            },
        });
    });

    $("#add_reply_btn").on("click", function () {
        var post_id = $(this).attr("data-id");
        $.ajax({
            type: "POST",
            url: upstream.ajaxurl,
            data: {
                action: "after_add_comment_change_status",
                post_id: post_id,
            },
        });
        setInterval("location.reload()", 2000);
    });

    // coupon code button
    $("#coupon_code_btn").on("click", function (e) {
        e.preventDefault();
        var coupon_code = $(".input_coupon").val();
        $.ajax({
            type: "POST",
            url: upstream.ajaxurl,
            data: {
                action: "designrta_coupon_code_add",
                coupon_code: coupon_code,
            },
            beforeSend: function () {
                $(".coupon_message").append("<p>Please Wait</p>");
            },
            success: function (data) {
                $(".coupon_message p").css("display", "none");
                $(".success_coupon_message").css("display", "block");
            },
        });
        setInterval("location.reload()", 3000);
    });

    jQuery(document).on("gform_confirmation_loaded", function () {
        setTimeout(function () {
            $.ajax({
                type: "POST",
                url: upstream.ajaxurl,
                data: {
                    action: "after_create_post_change_status",
                },
            });
        }, 3000);
        setInterval("location.reload()", 4000);
    });
    $("button.button.insert-media.add_media").css("display", "none");
})(jQuery);

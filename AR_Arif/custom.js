jQuery(document).ready(function () {
    // load all the variables here
    const popupDismissBtn = $(".dismiss_btn");
    const approvalBtn = $(".approval_projects .approve-design");

    // Close the approve project propup for 2days
    function closeApprovePopup(e) {
        let target = $(e.currentTarget);
        let poupContainer = target.parents("#project_notification_modal");

        $.ajax({
            type: "POST",
            url: designRTALocal.ajaxUrl,
            data: {
                action: "hide_approve_project_propup",
            },
            success: function (res) {
                console.log(res);
                if (!res) return;

                let response = JSON.parse(res);

                if (response.status === "success") {
                    poupContainer.fadeOut();
                } else {
                    console.error(res);
                }
            },
        });
    }

    // JS initializer and main event initializer
    const events = () => {
        popupDismissBtn.click(closeApprovePopup);
    };

    events();
});

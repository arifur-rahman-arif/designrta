# designrta

code changed at single-project.php file starting from

<div id="discussion" class="row"> 
</div>
area

upstream plugin bug fixed at class-up-comments.php at line 392 - 395 comment this code
if (!upstream_can_access_field('publish_project_discussion', $commentTargetItemType, $item_id, UPSTREAM_ITEM_TYPE_PROJECT, $project_id, 'comments', UPSTREAM_PERMISSIONS_ACTION_EDIT, true)) {
throw new \Exception(\_\_("You're not allowed to do this.", 'upstream'));
}

upstream plugin bug fixed at class-up-comments.php at line 360 comment this code
|| !isset($\_POST['nonce'])
and this code at line 367
|| !check_ajax_referer('upstream:project.add_comment_reply:' . $\_POST['parent_id'], 'nonce', false)

upstream plugin bug fixed at class-up-comments.php at line 266 - 268 comment this code
if (!check_ajax_referer($nonceIdentifier, 'nonce', false)) {
throw new \Exception(\_\_("Invalid nonce.", 'upstream'));
}

upstream plugin bug fixed at class-up-comments.php at line 238 comment this code
|| !isset($\_POST['nonce'])

Inside upstream plugin I have added a new line of code at class-up-comments.php at line 315 $response['comment_id'] = $comment->id;

<!--  -->
<!--  -->
<!--  -->
<!--  -->
<!--  -->

Date: 27-May-2021

I have changed the upstream-frontend-edit plugin to added project re-uploading feature

I have added this code at wp-content/plugins/upstream-frontend-edit/includes/class-up-frontend-output.php file

// =========================================
// Custom code written by Arifur Rahman Arif
// =========================================
public static function insertCommentControls($comment){
  if (empty($comment) || ! isset($comment->currentUserCap)) {
return;
}

        $canReply    = isset($comment->currentUserCap->can_reply) ? $comment->currentUserCap->can_reply : false;
        $canModerate = isset($comment->currentUserCap->can_moderate) ? $comment->currentUserCap->can_moderate : false;
        $canDelete   = isset($comment->currentUserCap->can_delete) ? $comment->currentUserCap->can_delete || $canModerate : false;

        $comment->state = (int)$comment->state;

        $controls = [];
        if ($canModerate) {
            if ($comment->state === 1) {
                $controls[0] = [
                    'action' => 'unapprove',
                    'nonce'  => "unapprove_comment",
                    'icon'   => "eye-slash",
                    'label'  => __('Unapprove'),
                ];
            } else {
                $controls[2] = [
                    'action' => 'approve',
                    'nonce'  => "approve_comment",
                    'icon'   => "eye",
                    'label'  => __('Approve'),
                ];
            }
        }

        if ($canReply) {
            $controls[1] = [
                'action' => 'reply',
                'nonce'  => "add_comment_reply",
                'icon'   => "reply",
                'label'  => __('Reply'),
            ];
        }

        if ($canDelete) {
            $controls[] = [
                'action' => 'trash',
                'nonce'  => "trash_comment",
                'icon'   => "trash",
                'label'  => __('Delete'),
            ];
        }

        $controls = apply_filters('control_comment_options', $controls);

        if (count($controls) > 0) {
            foreach ($controls as $control) {
                printf(
                    '<a href="#" class="o-comment-control %s" data-action="comment.%s" data-nonce="%s" data-comment_id="'.$comment->id.'" data-post_id="'.get_the_ID().'" >
                      <i class="fa fa-%s"></i>&nbsp;
                      %s
                      %s
                    </a>',
                    $control['action'] == 'show_project_files' ? "uploaded_project_files" : null,
                    $control['action'],
                    wp_create_nonce('upstream:project.' . $control['nonce'] . ':' . $comment->id),
                    $control['icon'],
                    $control['label'],
                    $control['action'] == 'show_project_files' ? "(<b class='project_count'>". self::getProjectCount(get_the_ID(), $comment->id) ."</b>)" : null
                );
            }
        }
    }

     /**
     * @param  $post_id
     * @param  $comment_id
     * @return mixed
     */
    public static function getProjectCount(
        $post_id,
        $comment_id
    ) {
        $projectCount = 0;

        $get_meta = get_post_meta(intval($post_id), 'main_project_file');

        if ($get_meta) {
            foreach ($get_meta as $key => $meta) {
                if ($meta['comment_id'] == $comment_id) {
                    $projectCount += 1;
                }
            }
            return $projectCount;
        } else {
            return $projectCount;
        }
    }

// ================================================
// End of custom code written by Arifur Rahman Arif
// ================================================

<!--  -->
<!--  -->
<!--  -->
<!--  -->
<!--  -->

Date: 27-May-2021

I have added & as well as modified this code at wp-content/plugins/upstream-frontend-edit/assets/js/project.js file

    $(".main_container").on("click", '[data-action="comments.add_comment"]', function (e) {
        e.preventDefault();
        e.stopPropagation();

        var self = $(this);
        var commentContent = "";

        var editor_id = self.data("editor_id");
        var commentContent = getEditorContent(editor_id, false);
        if (isEditorEmpty(editor_id)) {
            setFocus(editor_id);
            return;
        } else {
            commentContent = getEditorContent(editor_id, true);
        }

        var item_id, commentsWrapper;
        var wrapper = $(self.parents(".modal-body")[0]);

        if ($(".c-comments", wrapper).length > 0) {
            item_id = $('[name="editing"]', wrapper).val();
            commentsWrapper = $(".c-comments", wrapper);
        } else {
            commentsWrapper = $(".row .x_content > .c-comments");
        }

        var item_type = self.data("item_type");
        var item_title = self.data("item_title");

        $.ajax({
            type: "POST",
            url: upstream.ajaxurl,
            data: {
                action: "upstream:project.add_comment",
                nonce: self.data("nonce"),
                project_id: $("#post_id").val(),
                content: commentContent,
                item_type: item_type,
                item_id: item_id || null,
                item_title: item_title || null,
                teeny: 1,
            },
            beforeSend: function () {
                self.addClass("disabled").text(l.LB_ADDING);
            },
            success: function (response) {
                self.removeClass("disabled").text(l.LB_ADD_COMMENT);

                if (response.success) {
                    resetCommentEditorContent(editor_id);

                    if (item_type === "project") {
                        var modalWrapper = self.parents(".modal-content");

                        $('button.close[data-dismiss="modal"]', modalWrapper).trigger("click");
                    }
                    // ==========================
                    // Custom Coding from AR Arif
                    // ==========================

                    let attachment_data = $(".upload-project-file").attr("data-uploading_info");

                    attachment_data = attachment_data ? JSON.parse(attachment_data) : false;
                    if (attachment_data != false) {
                        attachment_data.comment_id = response.comment_id;
                        attachment_data.post_id = $("#post_id").val();

                        attachProjectFileToComment(attachment_data);
                    }
                    // =====================
                    // End of Custom Coding
                    // =====================

                    appendCommentHtmlToDiscussion(response.comment_html, commentsWrapper);
                } else {
                    console.error(response.error);

                    $(".modal-body", wrapper).prepend(
                        $(
                            "" +
                                '<div class="alert alert-danger alert-dismissible" role="alert">' +
                                '<button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span></button>' +
                                response.error +
                                "</div>"
                        )
                    );
                }
            },
            error: function (request, textStatus, errorThrown) {
                console.error(errorThrown);

                self.removeClass("disabled").text(l.LB_ADD_COMMENT);
            },
        });
    });
    // ==========================================
    // Custom function made by Arifur Rahman Arif
    // ==========================================
    function attachProjectFileToComment(attachment_obj) {
        $.ajax({
            type: "post",
            url: upstream.ajaxurl,
            data: {
                action: "reupload_project_file",
                attachment_obj: attachment_obj,
            },
            success: function (response) {
                let res = JSON.parse(response);
                console.log(res);

                if (res.response == "invalid_action") {
                    makeAlert("Action is invalid");
                }
                if (res.response == "missing_parameter") {
                    makeAlert("One or more parameter is missing");
                }
                if (res.response == "file_exists") {
                    makeAlert("This File already uploaded for this comment");
                }
                if (res.response == "failed") {
                    makeAlert("Failed to upload file. There is something error");
                }
                if (res.response == "success") {
                    if (res.msg == "mail_sent") {
                        makeAlert("File Uploaded & Mail Sent Successfully");
                    }
                }
            },
            error: () => {
                alert("Something went wrong");
            },
        });
    }

    function makeAlert(text) {
        $("#alert_box")
            .html(
                `
                <div class="alert alert-success text-capitalize" role="alert">
                    ${text}
                </div>
            `
            )
            .hide()
            .slideDown();
    }

    // =================================================
    // End of Custom function made by Arifur Rahman Arif
    // =================================================

<!--  -->
<!--  -->
<!--  -->
<!--  -->
<!--  -->

Date: 26-Aug-2021

added post update code at wp-content\plugins\upstream\includes\class-up-comments.php line 315-320

// Update the post to restart the countdown timer
updatePostTimeStamp($project_id);

added post update code at wp-content\plugins\upstream\includes\class-up-comments.php line 429-434

// Update the post to restart the coutdown
updatePostTimeStamp($project_id);

public static function updatePostTimeStamp($postID) {
global $wpdb;
    $table = $wpdb->prefix . 'posts';
    $respond = $wpdb->update(
        $table,
        [
            'post_modified'     => date('Y-m-d H:i:s', current_time('timestamp', 0)),
            'post_modified_gmt' => date('Y-m-d H:i:s', current_time('timestamp', 0))
        ],
        [
            'id' => intval(sanitize_text_field($postID))
],
[
'%s',
'%s'
],
[
'%d'
]
);
}

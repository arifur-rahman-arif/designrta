<?php

// ==============================================-
// Codes written by Arifur Rahman Arif from WPPOOL
// ==============================================-

add_action('wp_enqueue_scripts', 'loadCustomScripts');

function loadCustomScripts() {
    wp_enqueue_script('designRtaCustom', get_stylesheet_directory_uri() . '/AR_Arif/custom.js', ['jquery'], time(), true);
    wp_localize_script('designRtaCustom', 'designRTALocal', [
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'siteUrl' => site_url('/')
    ]);
}

/**
 * @return array
 */
function custom_meta_values() {
    $meta = array(
        array(
            'key'  => 'gpaz9',
            'name' => 'Design In Progress'
        ),
        array(
            'key'  => '6ea5e',
            'name' => 'Ready For Approval'
        ),
        array(
            'key'  => 'azp39',
            'name' => 'Completed'
        ),
        array(
            'key'  => 'wfyaa',
            'name' => 'New Comment'
        ),
        array(
            'key'  => 'cerwk',
            'name' => 'Revision Requested'
        )
    );
    return $meta;
}

/**
 * @return array
 */
function filter_datewise_projects() {
    $filtered_project = array();

    $meta_values = custom_meta_values();

    $current_user = wp_get_current_user();

    foreach ($meta_values as $value) {

        $arg = [
            'post_type'      => 'project',
            'posts_per_page' => -1,
            'meta_query'     => array(
                array(
                    'key'     => '_upstream_project_status',
                    'value'   => $value['key'],
                    'compare' => 'LIKE'
                )
            )
        ];

        if (in_array('upstream_client_user', $current_user->roles)) {
            $arr_with_client_user_meta = [
                array(
                    'key'     => '_upstream_project_status',
                    'value'   => $value['key'],
                    'compare' => 'LIKE'
                ),
                array(
                    'key'     => '_upstream_project_client',
                    'value'   => get_client_id($current_user->data->display_name),
                    'compare' => 'LIKE'
                )
            ];
            $arg['meta_query'] = $arr_with_client_user_meta;
        }

        $projectPost = get_posts($arg);
        $filtered_project[] = array(
            'project_status' => $value['name'],
            'project_count'  => count($projectPost)
        );
    }

    return $filtered_project;
}

/**
 * @param $parent_comment_id
 * @param $file_value
 */
function get_reply_comments(
    $parent_comment_id,
    $file_value
) {
    $child_comments = get_comments(
        array(
            'parent' => $parent_comment_id
        )
    );
    ?>
<?php if ($child_comments) {
        foreach ($child_comments as $comment) {
            ?>
<div class="o-comment s-status-approved" id="comment-<?php echo $comment->comment_ID ?>"
    data-id="<?php echo $comment->comment_ID ?>">
    <div class="o-comment__body">
        <div class="o-comment__body__left">
            <img class="o-comment__user_photo"
                src="http://2.gravatar.com/avatar/29618afe0a56b675c5d1de92d8783806?s=96&amp;d=mm&amp;r=g" width="30">
        </div>
        <div class="o-comment__body__right">
            <div class="o-comment__body__head">
                <div class="o-comment__user_name" style="display: flex;
                                                        align-items: center;
                                                        justify-content: space-between;">
                    <?php echo $comment->comment_author ?>
                    <div>
                        <span style="font-weight: bolder; margin-left: 10px;">
                            <i style="color: #886f6f;font-size: 0.8em;" class="fas fa-paperclip"></i>
                            <span style="color: #68acff; font-size: 0.9em;"
                                class="total_project_count"><?php echo get_revison_count(get_the_ID(), $comment->comment_ID) ?></span>
                        </span>
                    </div>
                </div>
                <div class="o-comment__reply_info"></div>
                <div class="o-comment__date" data-toggle="tooltip" title=""
                    data-original-title="<?php echo date_format(date_create($comment->comment_date), "F d, Y h:i a") ?>">
                    <?php echo human_time_diff(strtotime($comment->comment_date), current_time('timestamp')) ?> ago
                </div>
            </div>
            <div class="o-comment__content">
                <p><?php echo str_replace('&nbsp;', '', preg_replace('/(.zip|.jpg|.png|.jpeg|.csv)/', '', $comment->comment_content)) ?>
                </p>
            </div>
            <div class="o-comment__body__footer">
                <a data-item-id="<?php echo $file_value['id'] ?>" data-project-id="<?php echo get_the_ID() ?>"
                    data-toggle="modal" data-target="#modal-reply_comment" href="#"
                    class="o-comment-control project_file_comment_reply" data-action="comment.reply"
                    data-nonce="ea14e753ee">
                    <i class="fa fa-reply"></i>&nbsp;
                    Reply
                </a>

                <!-- Upload Button -->
                <a data-item-id="<?php echo $file_value['id'] ?>" data-file_id="<?php echo $file_value['file_id'] ?>"
                    data-project-id="<?php echo get_the_ID() ?>" data-comment_id="<?php echo $comment->comment_ID ?>"
                    href="#" class="comment_revision_file_upload">
                    <i class="fas fa-upload"></i>&nbsp;
                    Upload
                </a>
                <!-- End of Upload Button -->

                <!-- View revison files button -->
                <a data-item-id="<?php echo $file_value['id'] ?>" data-project-id="<?php echo get_the_ID() ?>"
                    data-comment_id="<?php echo $comment->comment_ID ?>" data-toggle="modal"
                    data-target="#modal-revision_store" href="#" class="revision_files">
                    <i class="fas fa-file-alt"></i>&nbsp;
                    Revisions
                    (<b
                        class="revision_file_count"><?php echo get_revison_count(get_the_ID(), $comment->comment_ID) ?></b>)
                </a>
                <!-- End View revison files button  -->
            </div>
        </div>
    </div>

    <div class="o-comment-replies">
        <?php get_reply_comments($comment->comment_ID, $file_value)?>
    </div>
</div>
<?php }?>

<?php }?>
<?php
}

add_action('wp_ajax_create_zip_file_for_selected', 'create_zip_file_for_selected');
add_action('wp_ajax_nopriv_create_zip_file_for_selected', 'create_zip_file_for_selected');

function create_zip_file_for_selected() {

    $file_name = time() . '_all-files-downloads.zip';

    # define file array

    $seleted_files = sanitize_url($_POST['files']);

    if (empty($seleted_files)) {
        echo 'NO FILES';
        die();
    }

    # create new zip object
    $zip = new ZipArchive();

    # create a temp file & open it

    $zip->open($file_name, ZipArchive::CREATE);

    # loop through each file
    foreach ($seleted_files as $file) {
        # download file
        $download_file = file_get_contents($file);

        #add it to the zip
        $zip->addFromString(basename($file), $download_file);
    }

    # close zip
    $zip->close();

    update_option('designrta_last_download_file', $file_name);

    echo admin_url($file_name);
    die();
}

/* Function for saving discussion revisions along with uploaded file data */
add_action('wp_ajax_save_discussion_revison', 'save_discussion_revison');
add_action('wp_ajax_nopriv_save_discussion_revison', 'save_discussion_revison');

function save_discussion_revison() {

    if (sanitize_text_field($_POST['action']) != 'save_discussion_revison') {
        echo json_encode([
            'response' => 'invalid_action'
        ]);
        wp_die();
    }

    $sanitizedData = sanitizeData($_POST['attachment_obj']);

    extract($sanitizedData);

    if (!isset($attachment_id) or !isset($post_id) or !isset($comment_id)) {
        echo json_encode([
            'response' => 'missing_parameter'
        ]);
        wp_die();
    }

    $post_id = intval($post_id);

    $get_meta = get_post_meta(intval($post_id), '_revision_file');

    if ($get_meta) {
        foreach ($get_meta as $key => $meta) {
            if ($meta['attachment_id'] == $attachment_id && $comment_id == $meta['comment_id']) {
                echo json_encode([
                    'response' => 'file_exists'
                ]);
                wp_die();
            }
        }
    }

    $meta_value = [
        'attachment_id'       => $attachment_id,
        'upload_date'         => $upload_date,
        'uploader_name'       => $uploader_name,
        'attachment_filename' => $attachment_filename,
        'attachment_url'      => $attachment_url,
        'post_id'             => $post_id,
        'comment_id'          => $comment_id,
        'file_id'             => $file_id,
        'revision_type'       => $revision_type
    ];

    $add_meta = add_post_meta($post_id, '_revision_file', $meta_value);

    if ($add_meta) {

        // update the post to restart the project countdown timer
        updatePostTimeStamp($post_id);

        $current_user = wp_get_current_user();

        if (in_array('upstream_client_user', $current_user->roles)) {
            /* cerwk = Revision Requested */
            update_post_meta($post_id, '_upstream_project_status', 'cerwk');

            $in_revision_timestamp = time();
            if (get_post_meta($post_id, '_in_revision_timestamp')) {
                update_post_meta($post_id, '_in_revision_timestamp', $in_revision_timestamp);
            } else {
                add_post_meta($post_id, '_in_revision_timestamp', $in_revision_timestamp);
            }

        }

        if (in_array('upstream_manager', $current_user->roles)) {
            // 6ea5e = Ready For Approval
            update_post_meta($post_id, '_upstream_project_status', '6ea5e');
        }

        $subject = '';
        $body = '';

        if (in_array('upstream_client_user', $current_user->roles)) {
            $subject = '' . get_the_title($post_id) . ' project got a revision file uploaded by client';
            $body = '' . get_the_title($post_id) . ' project got a revision file uploaded by client. <br><a href="https://www.designrta.com/login">LOGIN</a> to view.';

        } else if (in_array('upstream_manager', $current_user->roles)) {
            $subject = '' . get_the_title($post_id) . ' project got a revision file uploaded by designer';
            $body = '' . get_the_title($post_id) . ' project got a revision file uploaded by a designer. <br><a href="https://www.designrta.com/login">LOGIN</a> to view.';
        } else {
            $subject = '' . get_the_title($post_id) . ' project got a revision file uploaded';
            $body = '' . get_the_title($post_id) . ' project got a revision file uploaded. <br><a href="https://www.designrta.com/login">LOGIN</a> to view.';
        }

        if (in_array('upstream_client_user', $current_user->roles)) {
            // Decrease the revision limit by 1
            $updatedRevisionLimit = intval($revision_limit) - 1;
            update_post_meta($post_id, '_client_revision', $updatedRevisionLimit);
        }

        if (send_cross_client_and_designer_email($post_id, $$subject, $body)) {
            echo json_encode([
                'response' => 'success',
                'msg'      => 'mail_sent'
            ]);
            wp_die();
        } else {
            echo json_encode([
                'response' => 'success',
                'msg'      => 'mail_sending_failed'
            ]);
            wp_die();
        }

    } else {
        echo json_encode([
            'response' => 'success',
            'msg'      => 'mail_sending_failed'
        ]);
        wp_die();
    }

    wp_die();
}

/**
 * @param $post_id
 */
function send_cross_client_and_designer_email(
    $post_id,
    $subject,
    $body
) {
    if (in_array('upstream_client_user', wp_get_current_user()->roles)) {
        $designer_emails = [];
        $designer_array = get_users([
            'role' => 'upstream_manager'
        ]);
        if ($designer_array) {
            foreach ($designer_array as $key => $designer) {
                $designer_emails[] = $designer->data->user_email;
            }
            $subject = '' . get_the_title($post_id) . ' project got a revision file uploaded by client';

            $body = '' . get_the_title($post_id) . ' project got a revision file uploaded by client. <br><a href="https://www.designrta.com/login">LOGIN</a> to view.';
            $headers[] = 'Content-Type: text/html; charset=UTF-8';
            $headers[] = 'From: DesignRTA <requests@designrta.com>';
            if (wp_mail(
                $designer_emails,
                $subject,
                $body,
                $headers
            )) {
                return true;
            } else {
                return false;
            }
        }
    }
    if (in_array('upstream_manager', wp_get_current_user()->roles)) {
        $client_email = get_userdata(get_post_meta($post_id, '_upstream_project_client_users', true)[0])->data->user_email;

        if ($client_email) {

            $headers[] = 'Content-Type: text/html; charset=UTF-8';
            $headers[] = 'From: DesignRTA <requests@designrta.com>';
            if (wp_mail(
                $client_email,
                $subject,
                $body,
                $headers
            )) {
                return true;
            } else {
                return false;
            }
        }
    }
}

/**
 * @param  $post_id
 * @param  $comment_id
 * @return mixed
 */
function get_revison_count(
    $post_id,
    $comment_id
) {
    $revison_count = 0;

    $get_meta = get_post_meta(intval($post_id), '_revision_file');

    if ($get_meta) {
        foreach ($get_meta as $key => $meta) {
            if ($meta['comment_id'] == $comment_id) {
                $revison_count += 1;
            }
        }
        return $revison_count;
    } else {
        return $revison_count;
    }
}

add_action('wp_ajax_get_discussion_revison', 'get_revison_by_commnent');
add_action('wp_ajax_nopriv_get_discussion_revison', 'get_revison_by_commnent');

function get_revison_by_commnent() {
    $revisions = [];
    if ($_POST['action'] != 'get_discussion_revison') {
        wp_die('invalid_action');
    }

    extract($_POST['data']);

    $get_meta = get_post_meta(intval($post_id), '_revision_file');

    if (isset($_POST['project_revison']) && $_POST['project_revison']) {
        if ($get_meta) {
            foreach ($get_meta as $key => $meta) {
                if ($meta['file_id'] == $file_id) {
                    $revisions[] = $meta;
                }
            }
        }
        if ($revisions) {
            $output = [
                'response' => 'success',
                'revision' => $revisions
            ];
            echo json_encode($output);
        } else {
            $output = [
                'response' => 'empty',
                'revision' => $revisions
            ];
            echo json_encode($output);
        }
    } else {

        if ($get_meta) {
            foreach ($get_meta as $key => $meta) {
                if ($meta['comment_id'] == $comment_id) {
                    $revisions[] = $meta;
                }
            }
        }
        if ($revisions) {
            $output = [
                'response' => 'success',
                'revision' => $revisions
            ];
            echo json_encode($output);
        } else {
            $output = [
                'response' => 'empty',
                'revision' => $revisions
            ];
            echo json_encode($output);
        }
    }

    wp_die();
}

/**
 * @param $post_id
 * @param $file_id
 */
function view_revision_button(
    $post_id,
    $file_id
) {
    $revision_count = 0;

    $get_meta = get_post_meta(intval($post_id), '_revision_file');

    if ($get_meta) {
        foreach ($get_meta as $key => $meta) {
            if ($meta['file_id'] == $file_id) {
                $revision_count += 1;
            }
        }
    }

    if ($revision_count > 0) {
        return ' <button data-project-id="' . $post_id . '" data-file_id="' . $file_id . '" data-toggle="modal" data-target="#modal-view_revisions" href="#" class="view_revisions btn btn-outline-primary">
                        View Revisions <b class="revision_file_count">' . $revision_count . '</b>
                    </button>';
    } else {
        return '<div class="alert alert-dark" role="alert">
                            <b>No revisions</b>
                    </div>';
    }
}

add_action('wp_insert_post', 'update_designrta_post_meta', 99);

/**
 * @param $post_id
 */
function update_designrta_post_meta($post_id) {
    if (get_post_type($post_id) == 'project') {
        $current_user = wp_get_current_user();
        if (in_array('upstream_client_user', $current_user->roles)) {
            update_post_meta($post_id, '_upstream_project_client_users', [get_current_user_id()]);
            update_post_meta($post_id, '_upstream_project_client', get_client_id($current_user->data->display_name));
            update_post_meta($post_id, '_upstream_project_status', 'gpaz9');
            update_post_meta($post_id, '_upstream_project_start', time());
            update_post_meta($post_id, '_upstream_project_end', (time() + (86400 * 2)));

            $designer_emails = [];
            $designer_array = get_users([
                'role' => 'upstream_manager'
            ]);
            if ($designer_array) {
                foreach ($designer_array as $key => $designer) {
                    $designer_emails[] = $designer->data->user_email;
                }
                $subject = '' . get_the_title($post_id) . ' project added to the website';
                $body = '' . get_the_title($post_id) . ' project added to the website. <br><a href="https://www.designrta.com/login">LOGIN</a> to view.';
                $headers[] = 'Content-Type: text/html; charset=UTF-8';
                $headers[] = 'From: DesignRTA <requests@designrta.com>';
                wp_mail(
                    $designer_emails,
                    $subject,
                    $body,
                    $headers
                );
            }
            addRevisonRestriction($post_id);
        }
        delete_post_on_no_credits([
            'post_id' => $post_id,
            'user_id' => $current_user->ID
        ]);
    }
}

/**
 * add restricion of 3 revision of newly created project on user meta
 * @param int|string $postID
 */
function addRevisonRestriction($postID) {
    /* the meta key. */
    $meta_key = '_client_revision';
    $revisonLimitMeta = get_post_meta($postID, $meta_key, true);
    // IF post had already created meta then update the meta or else add a new meta of revision limit
    if ($revisonLimitMeta) {
        update_post_meta($postID, $meta_key, 3);
    } else {
        add_post_meta($postID, $meta_key, 3);
    }
}

/**
 * @param  $name
 * @return mixed
 */
function get_client_id($name) {
    $client_id = null;
    $client_objects = get_posts([
        'post_type' => 'client',
        's'         => $name
    ]);
    if ($client_objects) {
        $client_id = $client_objects[0]->ID;
    }
    return $client_id;
}

add_action('wp_insert_comment', 'update_post_meta_on_revison');

/**
 * @param  $comment_id
 * @return null
 */
function update_post_meta_on_revison($comment_id) {
    $comment = get_comment($comment_id);
    $post_id = $comment->comment_post_ID;
    if (get_post_type($post_id) != 'project') {
        return;
    }

    $current_user = wp_get_current_user();
    if (in_array('upstream_client_user', $current_user->roles)) {
        if ($comment->comment_parent) {
            return;
        }

        update_post_meta(intval($post_id), '_upstream_project_status', 'cerwk');
    }
}

/**
 * @param $status_name
 */
function project_status_based_on_user($status_name) {
    if ($status_name == 'Revision Requested') {
        $current_user = wp_get_current_user();
        if (in_array('upstream_client_user', $current_user->roles)) {
            return 'In Revision';
        } else {
            return esc_html($status_name);
        }
    } else {
        return esc_html($status_name);
    }
}

add_action('wp_ajax_add_project_file_title', 'add_project_file_title');
add_action('wp_ajax_nopriv_add_project_file_title', 'add_project_file_title');

function add_project_file_title() {

    if ($_POST['action'] != 'add_project_file_title') {
        echo json_encode([
            'response' => 'invalid_action'
        ]);
        wp_die();
    }

    if (!isset($_POST['file_id']) || $_POST['file_id'] == '' || $_POST['title'] == '' || $_POST['post_id'] == '') {
        echo json_encode([
            'response' => 'missing_parameter'
        ]);
        wp_die();
    }

    $post_id = $_POST['post_id'];

    $file_title_meta = [
        'title'   => sanitize_text_field($_POST['title']),
        'file_id' => $_POST['file_id']
    ];

    $get_meta_value = get_post_meta($post_id, '_project_file_title', true);

    if ($get_meta_value) {
        $get_meta_value[] = $file_title_meta;
        update_post_meta($post_id, '_project_file_title', $get_meta_value);
    } else {
        add_post_meta($post_id, '_project_file_title', [$file_title_meta]);
    }

    updatePostTimeStamp($post_id);

    $client_email = get_userdata(get_post_meta($post_id, '_upstream_project_client_users', true)[0])->data->user_email;

    if ($client_email) {

        $subject = '' . get_the_title($post_id) . ' project got a file uploaded by designer';
        $body = '' . get_the_title($post_id) . ' project got a new file uploaded by a designer. <br><a href="https://www.designrta.com/login">LOGIN</a> to view.';
        $headers[] = 'Content-Type: text/html; charset=UTF-8';
        $headers[] = 'From: DesignRTA <requests@designrta.com>';
        if (wp_mail(
            $client_email,
            $subject,
            $body,
            $headers
        )) {
            echo json_encode([
                'response' => 'success',
                'msg'      => 'mail_sent'
            ]);
        } else {
            echo json_encode([
                'response' => 'success',
                'msg'      => 'mail_failed'
            ]);
        }

    }

    wp_die();
}

/**
 * @param  $file_id
 * @param  $i
 * @return mixed
 */
function get_revision_title(
    $file_id,
    $i
) {
    $return_value = '<div class="alert alert-dark text-center" role="alert">
                                    <b>Empty</b>
                                </div>';
    $uploaded_revsion = get_post_meta(get_the_ID(), '_project_file_title', true);
    if ($uploaded_revsion) {
        $each_revision = $uploaded_revsion[$i];
        if (!$each_revision) {
            return $return_value;
        }

        if ($each_revision['file_id'] == $file_id) {
            return '<p>
                            ' . esc_html($each_revision['title']) . '
                        </p>';
        }
    }
    return $return_value;
}

/**
 * @param $arg
 */
function delete_post_on_no_credits($arg) {
    extract($arg);
    if (intval(get_user_meta($user_id, '_upstream_client_credits', true)) < 1) {
        wp_delete_post($post_id, true);
        return true;
    }
    return false;
}

/**
 * @return mixed
 */
function get_user_credits() {
    $current_user = wp_get_current_user();
    $credits = intval(get_user_meta($current_user->ID, '_upstream_client_credits', true));
    if ($credits < 1) {
        return 0;
    } else {
        return $credits;
    }
}

add_action('init', 'project_reminder_mail');

/**
 * @return null
 */
function project_reminder_mail() {
    $projects = get_posts([
        'post_type'      => 'project',
        'posts_per_page' => -1,
        'meta_value'     => 'cerwk'
    ]);

    if (!$projects) {
        return;
    }

    $designer_emails = [];
    $designer_array = get_users([
        'role' => 'upstream_manager'
    ]);

    if (!$designer_array) {
        return false;
    }

    foreach ($designer_array as $key => $designer) {
        $designer_emails[] = $designer->data->user_email;
    }

    $headers[] = 'Content-Type: text/html; charset=UTF-8';
    $headers[] = 'From: DesignRTA <requests@designrta.com>';

    foreach ($projects as $key => $project) {
        $post_id = $project->ID;
        $project_status = get_post_meta($post_id, '_upstream_project_status', true);
        if ($project_status == 'cerwk') {

            $in_revision_timestamp = intval(get_post_meta($post_id, '_in_revision_timestamp', true));

            if ($in_revision_timestamp) {

                $timestamp_diff = time() - $in_revision_timestamp;

                if ($timestamp_diff > (60 * 60 * 24 * 2)) {

                    $subject = '' . get_the_title($post_id) . ' project needs a revision';
                    $body = '' . get_the_title($post_id) . ' project needs a revision to be made. <br><a href="https://www.designrta.com/login">LOGIN</a> to view.';
                    $is_mail_sent = wp_mail(
                        $designer_emails,
                        $subject,
                        $body,
                        $headers
                    );
                    if ($is_mail_sent) {
                        delete_post_meta($post_id, '_in_revision_timestamp');
                    }
                }
            }
        }
    }
}

/**
 * @return mixed
 */
function notification_data() {
    $notification_data = [
        'notification_count'   => 0,
        'notification_details' => []
    ];
    $current_user = wp_get_current_user();
    if (in_array('upstream_client_user', $current_user->roles)) {
        $projects = get_posts([
            'post_type'      => 'project',
            'posts_per_page' => -1,
            'meta_query'     => [
                [
                    'key'   => '_upstream_project_status',
                    'value' => '6ea5e', /* Ready for approval status */
                    'compare' => 'LIKE'
                ],
                [
                    'key'     => '_upstream_project_client_users',
                    'value'   => $current_user->ID,
                    'compare' => 'LIKE'
                ]
            ]
        ]);
        if (!$projects) {
            return $notification_data;
        }

        $notification_data['notification_count'] += count($projects);

        foreach ($projects as $key => $project) {

            $notification_data['notification_details'][] = [
                'ID'                => $project->ID,
                'post_title'        => $project->post_title,
                'notification_text' => 'Needs approval if everthing is alright'
            ];
        }
    } elseif (
        in_array('upstream_manager', $current_user->roles) ||
        in_array('administrator', $current_user->roles)
    ) {

        $projects = get_posts([
            'post_type'      => 'project',
            'posts_per_page' => -1,
            'meta_query'     => [
                [
                    'key'   => '_upstream_project_status',
                    'value' => 'cerwk', /* Project is In revision status */
                    'compare' => 'LIKE'
                ]
            ]
        ]);
        if (!$projects) {
            return $notification_data;
        }

        $notification_data['notification_count'] += count($projects);

        foreach ($projects as $key => $project) {
            $notification_data['notification_details'][] = [
                'ID'                => $project->ID,
                'post_title'        => $project->post_title,
                'notification_text' => in_array('upstream_manager', $current_user->roles) ? 'Needs a revision for client to approve this project' : 'Needs a revision by a designer for client to approve this project'
            ];
        }
    }
    return $notification_data;
}

/* Request for re-uploading project file and attach it  with commnet */
add_action('wp_ajax_reupload_project_file', 'reUploadProject');
add_action('wp_ajax_nopriv_reupload_project_file', 'reUploadProject');

function reUploadProject() {

    if ($_POST['action'] != 'reupload_project_file') {
        echo json_encode([
            'response' => 'invalid_action'
        ]);
        wp_die();
    }

    extract($_POST['attachment_obj']);

    if (!isset($attachment_id) || !isset($post_id) || !$post_id || !isset($comment_id)) {
        echo json_encode([
            'response' => 'missing_parameter'
        ]);
        wp_die();
    }

    $post_id = intval($post_id);

    $get_meta = get_post_meta(intval($post_id), 'main_project_file');

    if ($get_meta) {
        foreach ($get_meta as $key => $meta) {
            if ($meta['attachment_id'] == $attachment_id && $comment_id == $meta['comment_id']) {
                echo json_encode([
                    'response' => 'file_exists'
                ]);
                wp_die();
            }
        }
    }

    $meta_value = [
        'attachment_id'       => $attachment_id,
        'upload_date'         => $upload_date,
        'uploader_name'       => $uploader_name,
        'attachment_filename' => $attachment_filename,
        'attachment_url'      => $attachment_url,
        'post_id'             => $post_id,
        'comment_id'          => $comment_id
    ];

    $add_meta = add_post_meta($post_id, 'main_project_file', $meta_value);

    if ($add_meta) {

        $subject = '';
        $body = '';

        if ($attachment_id) {
            if (in_array('upstream_client_user', $current_user->roles)) {
                $subject = '' . get_the_title($post_id) . ' project had a file re-uploaded';
                $body = '' . get_the_title($post_id) . ' project had a file re-uploaded by client. <br><a href="https://www.designrta.com/login">LOGIN</a> to view.';

            } else if (in_array('upstream_manager', $current_user->roles)) {
                $subject = '' . get_the_title($post_id) . ' project had a file re-uploaded';
                $body = '' . get_the_title($post_id) . ' project had a file re-uploaded by a designer. <br><a href="https://www.designrta.com/login">LOGIN</a> to view.';
            } else {
                $subject = '' . get_the_title($post_id) . ' project had a file re-uploaded';
                $body = '' . get_the_title($post_id) . ' project had a file re-uploaded. <br><a href="https://www.designrta.com/login">LOGIN</a> to view.';
            }

            if (send_cross_client_and_designer_email($post_id, $subject, $body)) {
                echo json_encode([
                    'response' => 'success',
                    'msg'      => 'mail_sent'
                ]);
                wp_die();
            } else {
                echo json_encode([
                    'response' => 'success',
                    'msg'      => 'mail_sending_failed'
                ]);
                wp_die();
            }
        } else {
            echo json_encode([
                'response' => 'failed'
            ]);
            wp_die();
        }
    } else {
        echo json_encode([
            'response' => 'failed'
        ]);
        wp_die();
    }

    wp_die();
}

add_filter('control_comment_options', 'addExtraOptions');

/**
 * @param  array   $controls
 * @return array
 */
function addExtraOptions(array $controls) {

    $controls[] = [
        'action' => 'upload_project_files',
        'nonce'  => "upload_project_file",
        'icon'   => "upload",
        'label'  => 'Upload Files'
    ];
    $controls[] = [
        'action' => 'show_project_files',
        'nonce'  => "project_files",
        'icon'   => "file-alt",
        'label'  => 'Project Files'
    ];

    return $controls;
}

// Get the re uploaded project files
add_action('wp_ajax_get_re_uploaded_project', 'getReUploadedProjects');
add_action('wp_ajax_nopriv_get_re_uploaded_project', 'getReUploadedProjects');

function getReUploadedProjects() {
    $revisions = [];
    if ($_POST['action'] != 'get_re_uploaded_project') {
        wp_die('invalid_action');
    }

    extract($_POST['data']);

    $get_meta = get_post_meta(intval($post_id), 'main_project_file');

    if ($get_meta) {
        foreach ($get_meta as $key => $meta) {
            if ($meta['comment_id'] == $comment_id) {
                $revisions[] = $meta;
            }
        }
    }
    if ($revisions) {
        $output = [
            'response'     => 'success',
            'projectFiles' => $revisions
        ];
        echo json_encode($output);
    } else {
        $output = [
            'response'     => 'empty',
            'projectFiles' => $revisions
        ];
        echo json_encode($output);
    }

    wp_die();
}

/**
 * @param  int   $postID
 * @return int
 */
function allRevisionCount(int $postID) {
    $filesCount = 0;

    $allFiles = get_post_meta(intval($postID), '_revision_file');
    if (!$allFiles) {
        return $filesCount;
    }

    $filesCount += count($allFiles);

    return $filesCount;
}

/**
 * get the project revision type
 * @param  int   $postID
 * @return int
 */
function getProjectRevsionType(int $postID, $commentID) {
    $revisionType = 'none';

    $metaValue = get_post_meta(intval($postID), '_revision_file');

    if (!$metaValue) {
        return $revisionType;
    }

    foreach ($metaValue as $key => $metaArray) {
        if ($metaArray['comment_id'] == $commentID) {
            return $metaArray['revision_type'];
            break;
        }
    }

    return $revisionType;
}

/**
 * @param  int   $postID
 * @return int
 */
function totalReuploadedFiles(int $postID) {
    $filesCount = 0;
    $allFiles = get_post_meta(intval($postID), 'main_project_file');
    if (!$allFiles) {
        return $filesCount;
    }

    $filesCount += count($allFiles);

    return $filesCount;
}

// Get the re uploaded project files
add_action('wp_ajax_delete_re_uploaded_project', 'deleteReUploadedFile');
add_action('wp_ajax_nopriv_delete_re_uploaded_project', 'deleteReUploadedFile');

function deleteReUploadedFile() {

    if ($_POST['action'] != 'delete_re_uploaded_project') {
        wp_die('invalid_action');
    }

    extract($_POST['data']);

    if (!isset($post_id) || !$post_id || !isset($comment_id)) {
        echo json_encode([
            'response' => 'missing_parameter'
        ]);
        wp_die();
    }

    global $wpdb;

    $table = $wpdb->prefix . 'postmeta';

    $uploadedProjectFiles = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM " . $table . " WHERE post_id=%d AND meta_key=%s",
        $post_id,
        'main_project_file'
    ));

    $filteredFileID = null;

    if (!$uploadedProjectFiles) {
        return false;
    }

    foreach ($uploadedProjectFiles as $file) {
        $unserializedArray = unserialize($file->meta_value);

        if ($unserializedArray['comment_id'] == $comment_id) {
            $filteredFileID = $file->meta_id;
        }
    }

    if (!$filteredFileID) {
        wp_die('file_not_found');
    }

    $deletedResponse = $wpdb->delete(
        $table,
        [
            'meta_id' => $filteredFileID,
            'post_id' => $post_id
        ],
        [
            '%d',
            '%d'
        ]
    );

    if ($deletedResponse) {
        echo json_encode([
            'response' => 'success'
        ]);
        wp_die();
    } else {
        echo json_encode([
            'response' => 'failed'
        ]);
        wp_die();
    }

}

/**
 * Show a popup into archive page if there is any unapproved projects remained by client
 * @param  $projects
 * @return null
 */
function showUnApprovedProjects($projects) {

    $current_user = wp_get_current_user();
    // if current user in to a upstream client user then don't run below of this code
    if (!in_array('upstream_client_user', $current_user->roles)) {
        return;
    }

    if (!$projects) {
        return;
    }

    $popupRestrictionMetaKey = 'poup_restriction';

    $popupRestrictionMeta = get_user_meta(get_current_user_id(), $popupRestrictionMetaKey, true);

    // If popup restriction time is more than 2 days then show the popup
    if ($popupRestrictionMeta) {
        $restrictionTimeDiff = (time() - intval($popupRestrictionMeta));

        if ($restrictionTimeDiff < (60 * 60 * 24 * 1)) {
            return;
        }
    }

    $hasUnApprovedProjects = false;

    // Check by loop to see there is any unapproved projects left by upstream user
    foreach ($projects as $key => $project) {
        if (get_post_meta($project->ID, '_upstream_project_status', true) === '6ea5e') {
            $hasUnApprovedProjects = true;
            break;
        }
    }

    // if client has no ready for approval projects than return
    if (!$hasUnApprovedProjects) {
        return;
    }

    $unApprovedPopupHtmlWrapper = '
    <div id="project_notification_modal" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="exampleModalLabel">Approve Projects</h4>
                    <div class="btn-group">
                    <button type="button" class="btn btn-secondary dismiss_btn">Dismiss</button>
                    </div>
                </div>
                <div class="modal-body">
                    <h5>Please apporve your all completed project to dismiss this popup.</h5>
                    ' . getAllUnApprovedProjects($projects) . '
                </div>
            </div>
        </div>
    </div>';

    return $unApprovedPopupHtmlWrapper;

}

/**
 * Get all unapproved project list that need approval
 * @param  $projects
 * @return null
 */
function getAllUnApprovedProjects($projects) {
    if (!$projects) {
        return;
    }

    $unApprovedProjectListHTML = '';

    foreach ($projects as $key => $project) {
        if (get_post_meta($project->ID, '_upstream_project_status', true) === '6ea5e') {
            $unApprovedProjectListHTML .= '
                <div class="approval_projects">
                    <strong>' . esc_html(get_the_title($project->ID)) . '</strong>
                    <div class="btn-group mb-2 mr-2">
                        <li id="approve-design" style="background-color:#5cd165; margin-left: 10px; list-style:none">
                            <a href="#" id="' . $project->ID . '" class="approve_design_btn btn btn-outline-success has-ripple"
                                style="color: #fff">
                                Approve Project
                                <span class="ripple ripple-animate"
                                    style="height: 144.297px; width: 144.297px; animation-duration: 0.7s; animation-timing-function: linear; background: rgb(255, 255, 255); opacity: 0.4; top: -57.1485px; left: -5.49225px;"></span></a>

                        </li>
                    </div>
                </div>
            ';
        }
    }

    return $unApprovedProjectListHTML;
}

/* remove product from user cart page */
add_action('wp_ajax_hide_approve_project_propup', 'hideProjectsPopup');
add_action('wp_ajax_nopriv_hide_approve_project_propup', 'hideProjectsPopup');

function hideProjectsPopup() {
    $output = [];

    if (sanitize_text_field($_POST['action']) !== 'hide_approve_project_propup') {
        $output['status'] = 'invalid';
        $output['message'] = 'Action is not valid';
        echo json_encode($output);
        wp_die();
    }

    $current_user = wp_get_current_user();
    // if current user in to a upstream client user then don't run below of this code
    if (!in_array('upstream_client_user', $current_user->roles)) {
        $output['status'] = 'invalid';
        $output['message'] = 'User is not valid';
        echo json_encode($output);
    }

    $popupRestrictionMetaKey = 'poup_restriction';

    $popupRestrictionMeta = get_user_meta(get_current_user_id(), $popupRestrictionMetaKey, true);

    if ($popupRestrictionMeta) {
        if (update_user_meta(get_current_user_id(), $popupRestrictionMetaKey, time())) {
            $output['status'] = 'success';
            $output['message'] = 'User popup restriction meta updated';
            echo json_encode($output);
            wp_die();
        }
    } else {
        if (add_user_meta(get_current_user_id(), $popupRestrictionMetaKey, time())) {
            $output['status'] = 'success';
            $output['message'] = 'User popup restriction meta added';
            echo json_encode($output);
            wp_die();
        }
    }

    echo json_encode($output);
    wp_die();

}

// Add a meta box to project post type for managing client revision limit
add_action('add_meta_boxes_project', 'registerMetaBox');
// Save the post meta on saving the project post type
add_action('save_post_project', 'saveMetaValue', 10, 2);

function registerMetaBox() {
    add_meta_box(
        'client_revision',
        'Client Revision Limit',
        'metaBoxHTML',
        ['project', 'job_listing'],
        'side',
        'core'
    );
}

// Meta box html layout to generate in meta box
/**
 * @param $post
 */
function metaBoxHTML($post) {
    wp_nonce_field('client_revision_limit_action', 'client_revision_limit_nonce');

    $postID = $post->ID;

    $metaKey = '_client_revision';
    $metaValue = get_post_meta($post->ID, $metaKey, true);

    $metaValue = $metaValue ? $metaValue : 3;

    $clientID = get_post_meta($postID, '_upstream_project_client_users', true)[0];

    $userData = get_userdata($clientID);

    echo '
            <div>
                <strong>
                    <label for="client_revision">Revision Limit for <i>' . $userData->display_name . '</i> client</label>
                    <br/>
                </strong>
                <br />
                <input type="number" name="' . $metaKey . '" id="client_revision" value="' . $metaValue . '"/>
            </div>
       ';
}

/**
 * @param  int     $postID
 * @param  object  $postObject
 * @return mixed
 */
function saveMetaValue(int $postID, object $postObject) {

    if (!isset($_POST['client_revision_limit_nonce']) || !wp_verify_nonce($_POST['client_revision_limit_nonce'], 'client_revision_limit_action')) {
        return $postID;
    }

    /* Does current user have capabitlity to edit post */
    $postType = get_post_type_object($postObject->post_type);

    if (!current_user_can($postType->cap->edit_post, $postID)) {
        return $postID;
    }

    /* the meta key. */
    $meta_key = '_client_revision';

    /* Get the posted data and check it for uses. */
    $new_meta_value = (isset($_POST[$meta_key]) ? sanitize_text_field($_POST[$meta_key]) : "");

    /* Get the meta value of the custom field key. */
    $meta_value = get_post_meta($postID, $meta_key, true);

    if ($new_meta_value && "" == $meta_value) {
        /* If a new meta value was added and there was no previous value, add it. */
        add_post_meta($postID, $meta_key, $new_meta_value);
    } elseif ($new_meta_value && $new_meta_value != $meta_value) {
        /* If the new meta value does not match the old value, update it. */
        update_post_meta($postID, $meta_key, $new_meta_value);
    } elseif ("" == $new_meta_value && $meta_value) {
        /* If there is no new meta value but an old value exists, delete it. */
        delete_post_meta($postID, $meta_key, $meta_value);
    }

}

/**
 * Show revision left for client
 * @return null
 */
function showRevisionLeftHTML() {
    $current_user = wp_get_current_user();
    // if current user is not a upstream client user then don't run below of this code
    if (!in_array('upstream_client_user', $current_user->roles)) {
        return;
    }

    return '
    <span class="badge badge-pill badge-primary" style="font-size: 17px;">
        Revisions left: &nbsp;<span
            class="badge badge-light designrta_revision_limit">' . esc_html(get_post_meta(get_the_ID(), '_client_revision', true)) . '</span>
    </span>
    ';
}

/**
 * Sanitize data for safe use
 * @param  array   $unsanitzedData
 * @return mixed
 */
function sanitizeData(array $unsanitzedData) {
    $sanitizedData = null;

    $sanitizedData = array_map(function ($data) {
        if (gettype($data) == 'array') {
            return sanitizeData($data);
        } else {
            return sanitize_text_field($data);
        }
    }, $unsanitzedData);

    return $sanitizedData;
}

/**
 * @param $postID
 */
function updatePostTimeStamp($postID) {
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
<?php
    // Prevent direct access.
    if (!defined('ABSPATH')) {
        exit;
    }

    $post_id = upstream_post_id();
    $itemTypeSingular = 'discussion';
    $itemTypePlural = 'discussions';
    $fieldPrefix = '_upstream_project_'.$itemTypeSingular.'_';
?>

<div id="modal-<?php echo $itemTypeSingular; ?>" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true"
    data-type="<?php echo $itemTypeSingular; ?>">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    <i class="fa fa-comments"></i> <span><?php _e('New Message', 'upstream');?></span>
                </h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="tab-content">
                    <div role="tabpanel" class="tab-pane fade in active"
                        id="modal-<?php echo $itemTypeSingular; ?>-data-wrapper">
                        <form name="the_<?php echo $itemTypeSingular; ?>" method="POST"
                            data-type="<?php echo $itemTypeSingular; ?>" class="form-horizontal o-modal-form">
                            <div class="row">
                                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                                    <?php
                                        wp_editor('', $fieldPrefix.'comment', [
                                            'media_buttons' => true,
                                            'textarea_rows' => 1,
                                            'textarea_name' => 'comment'
                                        ]);
                                    ?>
                                </div>
                                <div class="hidden-xs hidden-sm col-md-1 col-lg-1"></div>
                            </div>

                            <input type="hidden" id="type" name="type" value="<?php echo $itemTypeSingular; ?>" />
                            <?php wp_nonce_field('upstream:project.add_comment', 'discussion.csrf');?>
                        </form>
                    </div>
                    <br>

                    <button type="button" class="btn btn-primary has-ripple upload-project-file">
                        <?php _e('Upload Project', 'upstream-frontend-edit');?>
                        &nbsp;
                        <i class="fa fa-upload"></i>
                    </button>
                    <br>

                    <div class="project-file">

                    </div>

                </div>
            </div>
            <div class="modal-footer">
                <div class="row">
                    <div class="col-md-12 col-sm-12 col-xs-12 text-right" id="add_comment_btn">
                        <button type="button" class="btn btn-danger"
                            data-dismiss="modal"><?php _e('Cancel',
                                                                                              'upstream-frontend-edit');?></button>
                        <button data-id="<?php echo get_the_ID(); ?>" type="submit" class="btn btn-success"
                            data-action="comments.add_comment" data-editor_id="<?php echo $fieldPrefix.'comment'; ?>"
                            data-nonce="<?php echo wp_create_nonce('upstream:project.add_comment'); ?>"
                            data-item_type="project">
                            <i class="fa fa-plus"></i>
                            <?php _e('Add Comment', 'upstream');?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'comment_reply.php';?>

<script>
jQuery(document).ready(function($) {

    // Re upload project file for main project
    $('.upload-project-file').click((e) => {
        e.preventDefault();
        open_media(e)
    })

    // Open media uploader
    function open_media(e, fromWhere = false) {
        let file_frame = wp.media.frames.file_frame = wp.media({
            multiple: false,
            title: 'Upload File',
        });
        file_frame.open();
        file_frame.on('select', () => {
            add_meta_data_to_button(file_frame, e, fromWhere)
        })
    }

    // Add meta data to Re uploader button
    function add_meta_data_to_button(file_frame, e, fromWhere) {
        let attachment = file_frame.state().get('selection').first();

        let attachment_obj = {

            attachment_id: attachment.id,
            upload_date: attachment.attributes.dateFormatted,
            uploader_name: attachment.attributes.authorName,
            attachment_filename: attachment.attributes.filename,
            attachment_url: attachment.attributes.url,
        }

        if (fromWhere == 'from_comment') {
            attachment_obj.comment_id = $(e.currentTarget).attr('data-comment_id');
            attachment_obj.post_id = $(e.currentTarget).attr('data-post_id')
            saveProjectFiles(attachment_obj, e)
        } else {
            $(e.currentTarget).attr('data-uploading_info', JSON.stringify(attachment_obj))
            $('.project-file').html(`
                    <div class="alert alert-success" role="alert">
                        <strong>File Name:  ${attachment_obj.attachment_filename}</strong>
                    </div>
            `)
        }

    }

    // upload project file in comment section
    $(document).on('click', '.o-comment-control.upload_project_files', e => {
        e.preventDefault();
        open_media(e, 'from_comment');
    })


    function saveProjectFiles(attachment_obj, e) {

        let projectCount = $(e.currentTarget).parent().find('.project_count').text()

        $.ajax({
            type: "post",
            url: "<?php echo admin_url('admin-ajax.php') ?>",
            data: {
                action: 'reupload_project_file',
                attachment_obj: attachment_obj
            },
            success: function(response) {
                console.log(response)
                if (!response) return;
                let res = JSON.parse(response)
                if (res.response == 'invalid_action') {
                    make_alert('Action is invalid')
                }
                if (res.response == 'missing_parameter') {
                    make_alert('One or more parameter is missing')
                }
                if (res.response == 'file_exists') {
                    make_alert('This File already uploaded for this comment')
                }
                if (res.response == 'failed') {
                    make_alert('Failed to upload file. There is something error')
                }
                if (res.response == 'success') {
                    if (res.msg == 'mail_sent') {
                        make_alert('File Uploaded & Mail Sent Successfully')
                    } else {
                        make_alert('File Uploaded Successfully But Mail Could Not Be Sent')
                    }
                    let count = parseInt(projectCount);
                    $(e.currentTarget).parent().find('.project_count').html(count += 1)
                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                }
            },
            error: () => {
                alert('Something went wrong')
            }
        });
    }

    // make a alert at the top of the page
    function make_alert(text) {

        $('#alert_box').html(
            `
                <div class="alert alert-success text-capitalize" role="alert">
                    ${text}
                </div>
            `).hide().slideDown()
    }

});
</script>
<?php
// Prevent direct access.
if (!defined('ABSPATH')) {
    exit;
}

$post_id = upstream_post_id();
$itemTypeSingular = 'file';
$itemTypePlural = 'files';
$fieldPrefix = '_upstream_project_' . $itemTypeSingular . '_';
$meta = (array) get_post_meta($post_id, '_upstream_project_files', true);
$formActionURL = (isset($_SERVER['QUERY_STRING']) && !empty($_SERVER['QUERY_STRING'])
    ? $_SERVER['QUERY_STRING'] . '&'
    : "")
    . 'action=add_' . $itemTypeSingular;

$allowComments = upstreamAreCommentsEnabledOnFiles();
$members = (array) upstream_project_users_dropdown();
$statuses = (array) upstream_get_all_project_statuses();

$user = wp_get_current_user();
?>

<div id="modal-<?php echo $itemTypeSingular; ?>" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">

                <h4 class="modal-title">
                    <i class="fa fa-file"></i> <span><?php _e('Add File', 'upstream');?></span>
                </h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">

                <div class="tab-content">
                    <div role="tabpanel" class="tab-pane fade in active"
                        id="modal-<?php echo $itemTypeSingular; ?>-data-wrapper">
                        <form id="the_<?php echo $itemTypeSingular; ?>" method="POST"
                            data-type="<?php echo $itemTypeSingular; ?>" class="o-modal-form"
                            enctype="multipart/form-data">
                            <style>
                            #add_file_title:focus {
                                background-image: none;
                            }
                            </style>
                            <div class="input-group mb-3 w-75">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">Add Title</span>
                                </div>
                                <input required type="text" id="add_file_title" class="form-control"
                                    aria-label="Default" aria-describedby="inputGroup-sizing-default">
                            </div>


                            <?php if (in_array('upstream_client_user', $user->roles)) {?>
                            <div class="row upstream-file-title form_row_title">
                                <div class="col-xs-12 col-sm-3 col-md-2 col-lg-2">
                                    <label for="<?php echo $fieldPrefix . 'title'; ?>"><?php esc_html_e(
    'Title',
    'upstream'
);?></label>
                                </div>
                                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                                    <input type="text" id="<?php echo $fieldPrefix . 'title'; ?>" name="data[title]"
                                        class="form-control" required>
                                </div>
                                <div class="hidden-xs hidden-sm col-md-1 col-lg-1"></div>
                            </div>

                            <div class="row upstream-file-description form_row_description">
                                <div class="hidden-xs hidden-sm col-md-1 col-lg-1"></div>
                                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                                    <label for=""><?php esc_html_e('Description', 'upstream');?></label>
                                </div>
                                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                                    <?php
wp_editor('', $fieldPrefix . 'description', [
    'media_buttons' => true,
    'textarea_rows' => 5,
    'textarea_name' => 'data[description]'
]);
    ?>
                                </div>
                                <div class="hidden-xs hidden-sm col-md-1 col-lg-1"></div>
                            </div>

                            <?php }?>


                            <div class="row upstream-file-file form_row_file pt-3">
                                <div class="hidden-xs hidden-sm col-md-1 col-lg-1"></div>
                                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 ">
                                    <label for="<?php echo $fieldPrefix . 'file'; ?>"><?php esc_html_e(
    'File',
    'upstream'
);?></label>
                                </div>
                                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                                    <button type="button" class="btn btn-default btn-xs o-media-library-btn"
                                        data-title="<?php esc_attr_e('File', 'upstream');?>" data-name="file">
                                        <i class="fa fa-upload"></i>
                                        <?php esc_html_e('Add or Upload File', 'cmb2');?>
                                    </button>
                                    <div class="file-preview">
                                        <div></div>
                                    </div>
                                </div>
                                <div class="hidden-xs hidden-sm col-md-1 col-lg-1"></div>
                            </div>

                            <?php do_action('upstream.frontend-edit:renderAdditionalFields', 'file');?>

                            <input type="hidden" id="post_id" name="post_id" value="<?php echo $post_id; ?>" />
                            <input type="hidden" id="type" name="type" value="<?php echo $itemTypePlural; ?>" />
                            <?php wp_nonce_field('upstream_security', 'upstream-nonce');?>
                            <?php wp_nonce_field('upload-file', 'upstream-' . $itemTypePlural . '-nonce');?>
                        </form>
                    </div>

                </div>
            </div>
            <div class="modal-footer">
                <div class="row" data-visible-when="edit">
                    <div class="col-md-12 col-sm-12 col-xs-12 text-right" id="add_comment_btn">
                        <button type="button" class="btn btn-danger" data-dismiss="modal"><?php _e(
    'Cancel',
    'upstream-frontend-edit'
);?></button>
                        <button disabled id="file_upload_sumbit_btn" data-post_id="<?php echo get_the_ID() ?>"
                            type="submit" class="btn btn-success" form="the_<?php echo $itemTypeSingular; ?>">
                            <i class="fa fa-save"></i>
                            <?php esc_html_e('Save', 'upstream-frontend-edit');?>
                        </button>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>



<div id="modal-revision" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">

                <h4 class="modal-title">
                    <i class="fa fa-file"></i> <span><?php _e('Add Revision', 'upstream');?></span>
                </h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">

                <div class="tab-content">
                    <div role="tabpanel" class="tab-pane fade in active"
                        id="modal-<?php echo $itemTypeSingular; ?>-data-wrapper">
                        <form id="the_<?php echo $itemTypeSingular; ?>" method="POST"
                            data-type="<?php echo $itemTypeSingular; ?>" class="o-modal-form"
                            enctype="multipart/form-data">



                            <div class="row upstream-file-description form_row_description">

                                <div class="hidden-xs hidden-sm col-md-1 col-lg-1"></div>
                                <div class="revision-value col-xs-12 col-sm-12 col-md-12 col-lg-12"></div>
                                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                                    <label for=""><?php esc_html_e('Add Revision', 'upstream');?></label>
                                </div>
                                <div class="col-12 m" id="alertbox_inside_comment">
                                </div>
                                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">

                                    <?php if ($allowComments): ?>
                                    <div role="tabpanel" class="tab-pane fade"
                                        id="modal-<?php echo $itemTypeSingular; ?>-comments-wrapper"
                                        data-wrapper-type="comments">
                                        <?php if (upstream_permissions('publish_project_discussion')):
    $editor_id = 'upstream_' . $itemTypePlural . '_comment_editor';
    ?>

                                        <textarea name="comment_field" id="designrta_revision_comment" cols="100"
                                            style="width: 100%; " rows="5"></textarea>
                                        <br>
                                        <br>

                                        <select class="form-select revsion_type" name="revision_type"
                                            aria-label="Default select example">
                                            <option selected>Revsion Type</option>
                                            <option value="Color change">Color change</option>
                                            <option value="Dimension Change">Dimension Change</option>
                                            <option value="Manufacture Change">Manufacture Change</option>
                                        </select>

                                        <br>
                                        <br>
                                        <button data-uploading_info="" type="button" class="btn btn-primary"
                                            id="upload_revision_in_reply">Upload Revison &nbsp;<i
                                                class="fas fa-upload"></i></button>
                                        <br>

                                        <div id="uploading_file_name">
                                        </div>
                                        <div class="comments-controls-btns">
                                            <button type="button" id="project_file_comment" class="btn btn-success"
                                                data-action="comments.add_comment"
                                                data-editor_id="<?php echo $editor_id; ?>"
                                                data-nonce="<?php echo wp_create_nonce('upstream:project.' . $itemTypePlural . '.add_comment'); ?>"
                                                data-item_type="<?php echo $itemTypeSingular; ?>"
                                                data-item_title=""><?php esc_html_e('Add Comment', 'upstream');?></button>
                                        </div>
                                        <?php endif;?>
                                        <div class="c-comments" data-type="<?php echo $itemTypeSingular; ?>"
                                            data-nonce="<?php echo wp_create_nonce('upstream:project.' . $itemTypePlural . '.fetch_comments'); ?>">
                                        </div>
                                    </div>
                                    <?php endif;?>
                                </div>
                                <div class="hidden-xs hidden-sm col-md-1 col-lg-1"></div>
                            </div>


                            <?php do_action('upstream.frontend-edit:renderAdditionalFields', 'file');?>

                            <input type="hidden" id="post_id" name="post_id" value="<?php echo $post_id; ?>" />
                            <input type="hidden" id="type" name="type" value="<?php echo $itemTypePlural; ?>" />
                            <?php wp_nonce_field('upstream_security', 'upstream-nonce');?>
                            <?php wp_nonce_field('upload-file', 'upstream-' . $itemTypePlural . '-nonce');?>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>


<!-- No revision limit popup -->
<div class="modal fade" id="revision_limit_popup" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel"><?php _e('Out of revision limit', 'upstream')?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Please contact the adminitstrator to increase your revision limit
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <!-- <button type="button" class="btn btn-primary">Save changes</button> -->
            </div>
        </div>
    </div>
</div>

</div>


<script>
jQuery(document).ready(function($) {
    /* disable the submit button if input value is empty */
    $('#add_file_title').on('input', (e) => {
        let target = $(e.currentTarget);
        let input_value = target.val();
        if (input_value != '') {
            $('#file_upload_sumbit_btn').attr('disabled', false)
        } else {
            $('#file_upload_sumbit_btn').attr('disabled', true)
        }
    })

    /* on sumbit of file upload send ajax req */
    $('#file_upload_sumbit_btn').on('click', (e) => {
        let target = $(e.currentTarget);
        let file_id = $("input[name='data[file_id]']").val()
        $.ajax({
            type: "post",
            url: "<?php echo admin_url('admin-ajax.php') ?>",
            data: {
                action: 'add_project_file_title',
                title: $('#add_file_title').val(),
                file_id: file_id,
                post_id: target.attr('data-post_id')
            },
            success: function(response) {
                if (!response) return;

                let res = JSON.parse(response)

                if (res.response == 'missing_parameter') {
                    make_alert('One or more parameter is missing');
                }
                if (res.response == 'invalid_action') {
                    make_alert('Action is invalid');
                }
                if (res.response == 'success') {
                    if (res.msg == 'mail_sent') {
                        make_alert('Project File Added & Mail Sent Successfully');
                    } else {
                        make_alert(
                            'Project File Added Successfully But Mail Could Not Be Sent'
                        );
                    }
                }
            }
        });
    })

    function make_alert(text) {
        $('#alert_box').html(`
                    <div class="alert alert-success text-capitalize" role="alert">
                        ${text}
                    </div>
                `).hide().slideDown()
    }
})
</script>
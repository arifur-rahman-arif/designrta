<?php
// Prevent direct access.
if (!defined('ABSPATH')) {
    exit;
}

if (
    !upstream_are_files_disabled()
    && !upstream_disable_files()
):

    $collapseBox = isset($pluginOptions['collapse_project_files'])
    && (bool) $pluginOptions['collapse_project_files'] === true;

    $collapseBoxState = \UpStream\Frontend\getSectionCollapseState('files');

    if (!is_null($collapseBoxState)) {
        $collapseBox = $collapseBoxState === 'closed';
    }

    $itemType = 'file';
    $currentUserId = get_current_user_id();
    $users = upstream_admin_get_all_project_users();

    $rowset = [];
    $projectId = upstream_post_id();

    $meta = (array) get_post_meta($projectId, '_upstream_project_files', true);

    foreach ($meta as $data) {
        if (
        !isset($data['id'])
        || !isset($data['created_by'])
        || !upstream_override_access_object(true, UPSTREAM_ITEM_TYPE_FILE, $data['id'], UPSTREAM_ITEM_TYPE_PROJECT, $projectId, UPSTREAM_PERMISSIONS_ACTION_VIEW)
    ) {
            continue;
        }

        $data['created_by'] = (int) $data['created_by'];
        $data['created_time'] = isset($data['created_time']) ? (int) $data['created_time'] : 0;
        $data['title'] = isset($data['title']) ? (string) $data['title'] : '';
        $data['file_id'] = isset($data['file_id']) ? (int) $data['file_id'] : 0;
        $data['description'] = isset($data['description']) ? (string) $data['description'] : '';

        $rowset[$data['id']] = $data;
    }

    $l = [
        'LB_TITLE'       => __('Title', 'upstream'),
        'LB_NONE'        => __('none', 'upstream'),
        'LB_DESCRIPTION' => __('Description', 'upstream'),
        'LB_COMMENTS'    => __('Comments', 'upstream'),
        'LB_FILE'        => __('File', 'upstream'),
        'LB_UPLOADED_AT' => __('Upload Date', 'upstream')
    ];

    $areCommentsEnabled = upstreamAreCommentsEnabledOnFiles();

    $tableSettings = [
        'id'              => 'files',
        'type'            => 'file',
        'data-ordered-by' => 'created_at',
        'data-order-dir'  => 'DESC'
    ];

    $columnsSchema = \UpStream\Frontend\getFilesFields($areCommentsEnabled);

    ?>

<div class="row">
    <!-- [ static-layout ] start -->
    <div class="col-sm-12">
        <div class="card">
            <!-- prject ,team member start -->
            <div class="card table-card">
                <div class="card-header">
                    <h5>Project: "<?php echo esc_html(upstream_file_label_plural()); ?>"</h5>

                    <?php if (!in_array('upstream_manager', wp_get_current_user()->roles)) {?>
                    <div class="btn-group mb-2 mr-2">
                        <button type="button" class="btn  btn-outline-primary">Download Options</button>
                        <button type="button" class="btn  btn-outline-primary dropdown-toggle dropdown-toggle-split"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span
                                class="sr-only">Toggle
                                Dropdown</span></button>
                        <div class="dropdown-menu">
                            <form class="form-inline c-data-table__filters" data-target="#files">
                                <a class="dropdown-item download_selected_files" href="#!">Download Selected</a>
                                <a class="dropdown-item  download_all_files" id="<?php echo get_the_ID() ?>"
                                    href="#!">Download All</a>

                            </form>
                        </div>

                    </div>
                    <?php }?>

                    <div class="btn-group mb-2 mr-2">
                        <?php $user = wp_get_current_user();
    if (in_array('upstream_client_user', $user->roles)) {
        ?>
                        <li id="approve-design" style="background-color:#5cd165; margin-left: 10px; list-style:none">
                            <a href="#" id="<?php echo get_the_ID(); ?>"
                                class="approve_design_btn btn btn-outline-success" style="color: #fff">
                                <?php
    $status = upstream_project_status_color($id);
        if ($status['status'] == 'Completed') {?>
                                <i class="fa fa-check" style="color:#fff" ;=""></i>
                                Approved
                                <?php } else {?>
                                Approve Project
                                <?php }?>
                            </a>

                        </li>
                        <?php }
    if (in_array('upstream_manager', $user->roles)) {
        ?>

                        <li id="request_for_approve_design_btn"
                            style="background-color:#5cd165; margin-left: 10px; list-style:none">
                            <a href="#" id="<?php echo get_the_ID(); ?>"
                                class="request_for_approve_design_btn  btn btn-outline-success" style="color: #fff">
                                <i class="fa fa-check" style="color:#fff" ;=""></i>
                                Request Approval
                            </a>

                        </li>
                        <?php
    }
    ?>

                    </div>
                    <div class="btn-group mb-2 mr-2">
                        <?php
    if (in_array('upstream_client_user', $user->roles)) {?>
                        <a style="display:none" class="btn btn-outline-primary" data-toggle="up-modal"
                            data-target="#modal-file" data-form-type="add" data-modal-title="New: Design File">Add
                            Revision</a>
                        <?php } else {
        ?>
                        <a class="btn btn-outline-primary" data-toggle="modal" data-target="#modal-file"
                            data-form-type="add" data-modal-title="New: Project Files">Add Project Files </a>
                        <?php }?>


                    </div>

                    <?php echo showRevisionLeftHTML() ?>

                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>
                                        <div class="chk-option main_checkbox">
                                            <label
                                                class="check-task custom-control custom-checkbox d-flex justify-content-center done-task">
                                                <input type="checkbox" class="custom-control-input">
                                                <span class="custom-control-label"></span>
                                            </label>
                                        </div>
                                        File Type
                                    </th>
                                    <th>File Title</th>
                                    <th>Uploaded By</th>
                                    <th>Upload Date</th>
                                    <?php
    if (in_array('upstream_client_user', $user->roles)) {
        ?>
                                    <th>Request Revision</th>
                                    <?php }
    ?>
                                    <th>Download File</th>
                                    <th>Revisions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
    if ($meta) {

        foreach ($meta as $index => $pdata):
            if ($pdata) {
                $user_id = $pdata['created_by'] ? $pdata['created_by'] : null;
                $userName = get_userdata($user_id)->display_name;
                $file_path = pathinfo($pdata['file'], PATHINFO_EXTENSION);
                $ptime = date('M d Y ', $pdata['created_time']);
                $item_id = $pdata['id'];
                $title = $pdata['title'];
                $itemTypeSingular = 'file';
                $project_type = 'file';

                ?>


                                <tr>
                                    <td>
                                        <div class="chk-option">
                                            <label
                                                class="check-task custom-control custom-checkbox d-flex justify-content-center done-task">
                                                <input
                                                    data-file="<?php echo $pdata['file'] ? $pdata['file'] : 'no_file'; ?>"
                                                    type="checkbox" class="custom-control-input"
                                                    data-item-file="<?php echo $file_path ? $file_path : 'no_file' ?>">
                                                <span class="custom-control-label"></span>
                                            </label>
                                        </div>
                                        <div class="d-inline-block align-middle">
                                            <div class="d-inline-block">
                                                <h6 class="revision-file-name">.<?php printf('%s', $file_path);?> File
                                                </h6>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <?php echo get_revision_title($pdata['file_id'], $index) ?>
                                    </td>
                                    <td><?php printf('%s', $userName);?></td>
                                    <td><?php printf('%s', $ptime);?></td>
                                    <?php
        if (in_array('upstream_client_user', $user->roles)) {
                    ?>
                                    <td data-file_id="<?php echo $pdata['file_id'] ?>"
                                        data-filename="<?php echo $file_path ?>" data-item-title="<?php echo $title ?>"
                                        data-item-id="<?php echo $item_id ?>"
                                        data-project-type="<?php echo $project_type ?>"
                                        class="<?php echo intval(get_post_meta(get_the_ID(), '_client_revision', true)) < 1 ? 'revision_limit_popup' : 'open-revision-popup' ?>"
                                        id="<?php echo $pdata['id']; ?>" data-toggle="modal"
                                        data-target="<?php echo intval(get_post_meta(get_the_ID(), '_client_revision', true)) < 1 ? '#revision_limit_popup' : '#modal-revision' ?>">
                                        Add Revision Comment</a>
                                    </td>
                                    <?php }
                ?>

                                    <td data-column="file" data-value="<?php printf('%s', $pdata['file']);?>"
                                        data-type="file" data-order="<?php printf('%s', $pdata['file']);?>">
                                        <a href="<?php printf('%s', $pdata['file']);?>" target="_blank">Download
                                            .<?php printf('%s', $file_path);?> file</a>
                                    </td>

                                    <td style="text-align: center;">
                                        <?php echo view_revision_button(get_the_ID(), $pdata['file_id']) ?>
                                    </td>
                                </tr>
                                <?php }?>

                                <?php endforeach;?>

                                <?php }?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- [ static-layout ] end -->
</div>
<?php endif;?>

<script>
jQuery(document).ready(function($) {

    let data = {};
    $(".open-revision-popup").on("click", function(e) {
        $(".revision-value").html($(e.currentTarget).parent().find(".revision-file-name").text())
        data.project_id = <?php echo get_the_ID(); ?>;
        data.item_type = $(e.currentTarget).attr('data-project-type');
        data.item_id = $(e.currentTarget).attr('data-item-id');
        data.item_title = $(e.currentTarget).attr('data-item-title');
        data.file_name = '.' + $(e.currentTarget).attr('data-filename') + '&nbsp;';
        data.file_id = $(e.currentTarget).attr('data-file_id');
    });

    $("#project_file_comment").on("click", function(e) {

        data.content = data.file_name + $('#designrta_revision_comment').val();
        data.nonce = $('#upstream-nonce').val();

        $.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {
                action: 'upstream:project.add_comment',
                nonce: data.nonce,
                project_id: data.project_id,
                item_type: data.item_type,
                item_id: data.item_id,
                item_title: data.item_title,
                content: data.content,
            },
            beforeSend: function() {
                $('#designrta_revision_comment').attr('disabled', 'disabled');
                $(e.currentTarget).attr('disabled', 'disabled')
            },
            success: function(response) {
                if (response.error) {
                    console.error(response.error);
                    make_alert(response.error, true)
                } else {
                    if (!response.success) {
                        console.error('Something went wrong.');
                        make_alert('Something went wrong.', true)
                    } else {

                        let attachment_data = $('#upload_revision_in_reply').attr(
                            'data-uploading_info')
                        attachment_data = attachment_data ? JSON.parse(attachment_data) :
                            false;
                        if (attachment_data != false) {
                            attachment_data.comment_id = response.comment_id;
                            attachment_data.post_id = data.project_id;
                            attachment_data.file_id = data.file_id;
                            attachment_data.revision_limit = $('.designrta_revision_limit')
                                .text();

                            attachment_data.revision_type = $('.revsion_type').val();

                            add_revison_file_in_reply(attachment_data)
                        } else {
                            make_alert('Comment Added Successfully.', true)
                            location.reload()
                        }
                    }
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {

                var response = {
                    text_status: textStatus,
                    errorThrown: errorThrown
                };

                console.error(response);
            },
            complete: function() {
                // enableCommentArea(editor_id);
                $('#designrta_revision_comment').attr('disabled', false);
            }
        });
    });


    function add_revison_file_in_reply(attachment_obj) {
        $.ajax({
            type: "post",
            url: "<?php echo admin_url('admin-ajax.php') ?>",
            data: {
                action: 'save_discussion_revison',
                attachment_obj: attachment_obj
            },
            success: function(response) {
                let res = JSON.parse(response)
                console.log(res)
                if (res.response == 'invalid_action') {
                    make_alert('Action is invalid', true)
                }
                if (res.response == 'missing_parameter') {
                    make_alert('One or more parameter is missing', true)
                }
                if (res.response == 'file_exists') {
                    make_alert('This File already uploaded for this revision comment', true)
                }
                if (res.response == 'failed') {
                    make_alert('Failed to upload file. There is something error', true)
                }
                if (res.response == 'success') {
                    if (res.msg == 'mail_sent') {
                        make_alert('File Uploaded & Mail Sent Successfully', true)
                    } else {
                        make_alert('File Uploaded Successfully But Mail Could Not Be Sent', true)
                    }
                    location.reload();
                }
            },
            error: () => {
                alert('Something went wrong')
            }
        });
    }

    $('.main_checkbox .custom-control-input').on('change', function(e) {
        let tr_checkbox = $('tr > td > .chk-option .custom-control-input');
        if ($(e.currentTarget).prop('checked')) {

            $.each(tr_checkbox, function(index, elem) {
                if ($(elem).attr('data-item-file') !== 'no_file') {
                    $(elem).prop('checked', true)
                }
            });

        } else {
            $.each(tr_checkbox, function(index, elem) {
                $(elem).prop('checked', false)
            });
        }
    })

    $('.download_selected_files').click(function(e) {
        e.preventDefault();
        let files = [];
        $.each($('tr > td > .chk-option .custom-control-input'), function(index, value) {
            if ($(value).prop('checked')) {
                if ($(value).attr('data-file') !== 'no_file') {
                    files.push($(value).attr('data-file'));
                }
            }
        });

        $.ajax({
            type: 'POST',
            url: upstream.ajaxurl,
            data: {
                action: 'create_zip_file_for_selected',
                files: files,
            },

            success: function(response) {
                if ('NO FILES' === response) {
                    make_alert(response)
                } else {
                    window.location = response;
                }
            },

            error: function(error) {
                console.log(error);
            }

        });
    })


    /* View all revsions files */

    $('.view_revisions').on('click', (e) => {
        e.preventDefault();
        let data = {
            post_id: $(e.currentTarget).attr('data-project-id'),
            file_id: $(e.currentTarget).attr('data-file_id')
        }

        $.ajax({
            type: "post",
            url: "<?php echo admin_url('admin-ajax.php') ?>",
            data: {
                action: 'get_discussion_revison',
                data: data,
                project_revison: true
            },

            beforeSend: () => {
                let rows = `<tr>
                                                    <td scope="row" colspan="5" style="text-align: center;"><strong>Loading...</strong></td>
                                            </tr>`;
                $('#project_revisions').html(rows)
            },
            success: function(response) {

                let output = JSON.parse(response)

                console.log(output)
                if (output.response == 'success') {

                    let rows = ``;
                    let revisions = output.revision;

                    revisions.forEach((revision, i) => {
                        rows += `<tr>
                                                        <th scope="row">${i +=1 }</th>
                                                        <td>${revision.attachment_filename}</td>
                                                        <td>${revision.uploader_name}</td>
                                                        <td>${revision.upload_date}</td>
                                                        <td style="text-align: center;"><a href="${revision.attachment_url}" download type="button" class="btn btn-primary">Download File</a></td>
                                                </tr>`;
                    });

                    $('#project_revisions').html(rows)
                }
                if (output.response == 'empty') {
                    let rows = `<tr>
                                                    <td scope="row" colspan="5" style="text-align: center;"><strong>No File Found</strong></td>
                                            </tr>`;
                    $('#project_revisions').html(rows)
                }
            },
            error: () => {
                alert('Something went wrong')
            }
        });
    })



    $('#upload_revision_in_reply').on('click', (e) => {
        e.preventDefault();
        open_media(e)
    })

    function open_media(e) {
        let file_frame = wp.media.frames.file_frame = wp.media({
            multiple: false,
            title: 'Upload File',
        });
        file_frame.open();
        file_frame.on('select', () => {
            add_meta_data_to_button(file_frame, e)
        })
    }

    function add_meta_data_to_button(file_frame, e) {
        let attachment = file_frame.state().get('selection').first();

        let attachment_obj = {

            attachment_id: attachment.id,
            upload_date: attachment.attributes.dateFormatted,
            uploader_name: attachment.attributes.authorName,
            attachment_filename: attachment.attributes.filename,
            attachment_url: attachment.attributes.url,

        }
        $(e.currentTarget).attr('data-uploading_info', JSON.stringify(attachment_obj))
        $('#uploading_file_name').html(`
                 <div class="alert alert-success" role="alert">
                    <strong>File Name:  ${attachment_obj.attachment_filename}</strong>
                </div>
            `)
    }


    function make_alert(text, selector = false) {
        if (selector) {
            $('#alertbox_inside_comment').html(`
                    <div class="alert alert-success text-capitalize" role="alert">
                        ${text}
                    </div>
                `).hide().slideDown()
        } else {

            $('#alert_box').html(`
                    <div class="alert alert-success text-capitalize" role="alert">
                        ${text}
                    </div>
                `).hide().slideDown()
        }
    }


});
</script>
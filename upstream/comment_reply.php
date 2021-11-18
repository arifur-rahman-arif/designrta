<?php
    // Prevent direct access.
    if (!defined('ABSPATH')) {
        exit;
    }

if (upstream_permissions('publish_project_discussion')): ?>
<!-- Reply comment modal -->
<div id="modal-reply_comment" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title"><?php _e('Replying Comment', 'upstream');?></h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="panel panel-default o-comment-reply">
                        <div class="panel-body">
                            <div class="o-comment">
                                <div class="o-comment__body">
                                    <div class="o-comment__body__left">
                                        <img class="o-comment__user_photo" src="">
                                    </div>
                                    <div class="o-comment__body__right">
                                        <div class="o-comment__body__head">
                                            <div class="o-comment__user_name"></div>
                                            <div class="o-comment__reply_info"></div>
                                            <div class="o-comment__date"></div>
                                        </div>
                                        <div class="o-comment__content"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <textarea name="reply_comment" id="designrta_reply_comment" cols="100" rows="5"
                            style="width: 100%; display:none;"></textarea>
                    </div>
                    <div id="naormal-project-discussion" style="display:none;">
                        <?php
                            wp_editor("", '_upstream_project_comment_reply', [
                                'media_buttons' => true,
                                'textarea_rows' => 5,
                                'textarea_name' => 'comment_reply'
                            ]);
                        ?>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button style="display:none;" type="button" id="colose_comment_modal" class="btn btn-danger"
                    data-dismiss="modal"><?php _e('Cancel');?></button>
                <button style="display:none;" id="add_reply_btn_designrta" type="button" class="btn btn-success"
                    data-id="<?php echo get_the_ID(); ?>">
                    <i class="fa fa-reply"></i> <?php _e('Reply');?>
                </button>
                <button style="display:none;" id="add_reply_btn" type="submit" class="btn btn-success"
                    data-id="<?php echo get_the_ID(); ?>">
                    <i class="fa fa-reply"></i> <?php _e('Reply');?>
                </button>
            </div>
        </div>
    </div>
</div>
<!-- End of  reply comment modal -->


<!-- Comment revisions modal -->

<div id="modal-revision_store" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title"><?php _e('Revisions', 'upstream');?></h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">

                <div class="table-responsive">
                    <table class="table table-striped" style="width: 100%;">
                        <thead>
                            <tr>
                                <th scope="col" width="10%">#</th>
                                <th scope="col" width="18%">Revision File</th>
                                <th scope="col" width="18%">Uploaded By</th>
                                <th scope="col" width="18%">Upload Date</th>
                                <th scope="col" width="18%">Dowload Revision</th>
                            </tr>
                        </thead>
                        <tbody id="revision_table">

                        </tbody>
                    </table>
                </div>

            </div>
            <div class="modal-footer">
            </div>
        </div>
    </div>
</div>

<!-- End of revisons modal -->


<!-- Start of View Revision popup -->

<div id="modal-view_revisions" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title"><?php _e('Project Revisions', 'upstream');?></h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">

                <div class="table-responsive">
                    <table class="table table-striped" style="width: 100%;">
                        <thead>
                            <tr>
                                <th scope="col" width="10%">#</th>
                                <th scope="col" width="18%">Revision File</th>
                                <th scope="col" width="18%">Uploaded By</th>
                                <th scope="col" width="18%">Upload Date</th>
                                <th scope="col" width="18%">Dowload Revision</th>
                            </tr>
                        </thead>
                        <tbody id="project_revisions">

                        </tbody>
                    </table>
                </div>

            </div>
            <div class="modal-footer">
            </div>
        </div>
    </div>
</div>


<div id="modal-project-file" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title"><?php _e('Re Uploaded Project Files', 'upstream');?></h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">

                <div class="table-responsive">
                    <table class="table table-striped" style="width: 100%;">
                        <thead>
                            <tr>
                                <th scope="col" width="10%">#</th>
                                <th scope="col" width="18%">Project File</th>
                                <th scope="col" width="18%">Uploaded By</th>
                                <th scope="col" width="18%">Upload Date</th>
                                <th scope="col" width="18%">Dowload Revision</th>
                            </tr>
                        </thead>
                        <tbody id="reUploadedFiles">

                        </tbody>
                    </table>
                </div>

            </div>
            <div class="modal-footer">
            </div>
        </div>
    </div>
</div>
<!-- End of View Revision popup -->

<?php endif;?>
<?php
// Prevent direct access.
if (!defined('ABSPATH')) {
    exit;
}
?>

<?php if (upstreamAreProjectCommentsEnabled() && !upstream_are_comments_disabled()) :
    $pluginOptions = get_option('upstream_general');
    $collapseBox = isset($pluginOptions['collapse_project_discussion']) && (bool)$pluginOptions['collapse_project_discussion'] === true;

    $collapseBoxState = \UpStream\Frontend\getSectionCollapseState('discussion');

    $projectId = upstream_post_id();

    if (!is_null($collapseBoxState)) {
        $collapseBox = $collapseBoxState === 'closed';
    }
?>

    <div class="x_content" style="display: <?php echo $collapseBox ? 'none' : 'block'; ?>;">
        <?php upstreamRenderCommentsBox(); ?>
    </div>
    
        <div class="form-group" style="margin-top:100px;">
   
            <button type="button" class="btn btn-success float-right" data-toggle="modal" data-target="#modal-discussion" data-form-type="add" data-modal-title="Add Comment" style="float:right; margin-top: 20px;">
                            <?php _e('Add Comment', 'upstream'); ?>
                        </button>

                        
        </div>

<?php endif; ?>
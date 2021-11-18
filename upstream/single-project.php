<?php

/**
 * The Template for displaying a single project
 *
 * This template can be overridden by copying it to yourtheme/upstream/single-project.php.
 *
 *
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// redirect to projects if no permissions for this project
if (!upstream_user_can_access_project(get_current_user_id(), upstream_post_id())) {
    wp_redirect(get_post_type_archive_link('project'));
    exit;
}

// Some hosts disable this function, so let's make sure it is enabled before call it.
if (function_exists('set_time_limit')) {
    set_time_limit(120);
}

try {
    if (!session_id()) {
        session_start();
    }
} catch (\Exception $e) {
}

add_action('init', function () {
    try {
        if (!session_id()) {
            session_start();
        }
    } catch (\Exception $e) {
    }
}, 9);

$currentUser = (object) upstream_user_data();

$projectsList = [];
if (isset($currentUser->projects)) {
    if (is_array($currentUser->projects) && count($currentUser->projects) > 0) {
        foreach ($currentUser->projects as $project_id => $project) {
            $data = (object) [
                'id'          => $project_id,
                'author'      => (int) $project->post_author,
                'created_at'  => (string) $project->post_date_gmt,
                'modified_at' => (string) $project->post_modified_gmt,
                'title'       => $project->post_title,
                'slug'        => $project->post_name,
                'status'      => $project->post_status,
                'permalink'   => get_permalink($project_id)
            ];

            $projectsList[$project_id] = $data;
        }

        unset($project, $project_id);
    }

    unset($currentUser->projects);
}

$projectsListCount = count($projectsList);

upstream_get_template_part('global/header.php');
upstream_get_template_part('global/sidebar.php');
upstream_get_template_part('global/top-nav.php');

/*
 * upstream_single_project_before hook.
 */
do_action('upstream_single_project_before');

$user = upstream_user_data();

$options = (array) get_option('upstream_general');
$displayOverviewSection = !isset($options['disable_project_overview']) || (bool) $options['disable_project_overview'] === false;
$displayDetailsSection = !isset($options['disable_project_details']) || (bool) $options['disable_project_details'] === false;

/**
 * @param  bool   $displayOverviewSection
 * @return bool
 */
$displayOverviewSection = apply_filters('upstream_display_overview_section', $displayOverviewSection);

/**
 * @param  bool   $displayDetailsSection
 * @return bool
 */
$displayDetailsSection = apply_filters('upstream_display_details_section', $displayDetailsSection);

unset($options);

/*
 * Sections
 */
$sections = [
    'details',
    'milestones',
    'tasks',
    'bugs',
    'files',
    'discussion'
];
$sections = apply_filters('upstream_panel_sections', $sections);

// Apply the order to the panels.
$sectionsOrder = (array) \UpStream\Frontend\getPanelOrder();
$sections = array_merge($sectionsOrder, $sections);
// Remove duplicates.
$sections = array_unique($sections);

while (have_posts()): the_post();?>

<!-- [ Main Content ] start -->
<div class="pcoded-main-container">
    <div class="pcoded-wrapper">
        <div class="pcoded-content">
            <div class="pcoded-inner-content">
                <div class="main-body">
                    <div class="page-wrapper">
                        <!-- [ breadcrumb ] start -->
                        <div class="page-header">
                            <div class="page-block">
                                <div class="row align-items-center">
                                    <div class="col-md-12">
                                        <div class="page-header-title">
                                            <h5 class="m-b-10" style="display: inline-block;">
                                                <?php echo get_the_title(get_the_ID()); ?>

                                            </h5>
                                            <?php $status = upstream_project_status_color($id);?>
                                            <?php if (!empty($status['status'])): ?>
                                            <span class="label up-o-s-label"
                                                style="background-color:	                                                                        	                                                                        	                                                                        	                                                                        	                                                                        	                                                                        	                                                                        	                                                                        	                                                                        	                                                                        	                                                                        	                                                                        	                                                                        	                                                                        	                                                                        	                                                                        	                                                                        	                                                                        	                                                                        	                                                                        	                                                                        	                                                                        	                                                                        	                                                                        	                                                                        	                                                                        	                                                                        	                                                                        	                                                                        	                                                                        	                                                                        	                                                                        	                                                                        	                                                                        	                                                                        	                                                                        	                                                                        		                                                                        	                                                                        	                                                                        	                                                                        		                                                                        	                                                                        	                                                                        	                                                                        	                                                                        	                                                                        	                                                                        	                                                                        	                                                                        	                                                                        	                                                                        	                                                                        	                                                                        	                                                                        	                                                                        	                                                                        	                                                                         <?php echo esc_attr($status['color']); ?>"><?php echo project_status_based_on_user($status['status']); ?></span>
                                            <?php endif;?>
                                        </div>
                                        <ul class="breadcrumb">
                                            <li class="breadcrumb-item"><a href="<?php bloginfo('url');?>"><i
                                                        class="feather icon-home"></i></a></li>
                                            <li class="breadcrumb-item"><a href="<?php bloginfo('url');
echo "/projects";?>">projects</a></li>
                                            <li class="breadcrumb-item"><a href="#"><?php the_title();?></a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- [ breadcrumb ] end -->




                        <div class="alerts" id="alert_box">
                            <?php do_action('upstream_frontend_projects_messages');?>
                            <?php do_action('upstream_single_project_before_overview');?>
                        </div>

                        <div id="project-dashboard" class="sortable">
                            <?php foreach ($sections as $section): ?>
                            <?php switch ($section):
case 'details':
    ?>

                            <?php
break;

case 'milestones':

    break;

case 'tasks':
    if (!upstream_are_tasks_disabled() && !upstream_disable_tasks()): ?>
                            <div class="row" id="project-section-tasks">
                                <?php do_action('upstream_single_project_before_tasks');?>

                                <?php upstream_get_template_part('single-project/tasks.php');?>

                                <?php do_action('upstream_single_project_after_tasks');?>
                            </div>
                            <?php endif;
    break;

case 'bugs':
    if (!upstream_disable_bugs() && !upstream_are_bugs_disabled()): ?>
                            <div class="row" id="project-section-bugs">
                                <?php do_action('upstream_single_project_before_bugs');?>

                                <?php upstream_get_template_part('single-project/bugs.php');?>

                                <?php do_action('upstream_single_project_after_bugs');?>
                            </div>
                            <?php endif;
    break;

case 'files':
    if (!upstream_are_files_disabled() && !upstream_disable_files()): ?>
                            <div id="project-section-files">
                                <?php do_action('upstream_single_project_before_files');?>

                                <?php upstream_get_template_part('single-project/files.php');?>

                                <?php do_action('upstream_single_project_after_files');?>
                            </div>
                            <?php endif;
    break;

default:
    do_action('upstream_single_project_section_' . $section, upstream_post_id());

    break;

    endswitch;?>
                            <?php endforeach;?>
                            <!-- [ Main Content ] end -->
                            <!-- [ Main Content ] start -->
                            <div id="discussion" class="row">
                                <div class="col-md-6">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="card">
                                                <div class="card-header">
                                                    <h5>Project Overview: (<?php echo get_the_title(get_the_ID()) ?>)
                                                    </h5>
                                                </div>
                                                <div class="card-body">

                                                    <div class="discussion-up">
                                                        <div class="x_content" style="display: block;">
                                                            <ol>
                                                                <?php
$outside_casing = get_field('outside_casing_to_casing_dimensions_for_windows_and_doors');
    if (!empty($outside_casing)) {
        printf('<li><label>Outside Casing To Casing Dimensions (For Windows And Doors)</label><p>%s</p></li>', $outside_casing);
    }

    $rooms_properly = get_field('are_the_rooms_properly_labeled');
    if (!empty($rooms_properly)):
    ?>
                                                                <li>
                                                                    <label>Are The Rooms Properly Labeled?</label>
                                                                    <p><?php if ($rooms_properly == "im_not_sure") {
        echo "I'm not sure";
    } elseif ($rooms_properly == "yes") {
        echo "Yes";
    } else {
        echo "No";
    }?></p>
                                                                </li>
                                                                <?php
endif;
    $ceiling_height = get_field('ceiling_height_if_applicable');
    if (!empty($ceiling_height)) {
        printf('<li><label>Ceiling Height (If Applicable)</label><p>%s</p></li>', $ceiling_height);
    }

    $appliance_model = get_field('appliance_model_numbers');
    if (!empty($appliance_model)) {
        printf('<li><label>Appliance Model Numbers</label><p>%s</p></li>', $appliance_model);
    }
    ?>
                                                                <li>
                                                                    <label>Sink Type</label>
                                                                    <?php
$sink_type = get_field('sink_type')['0'];
    ?>
                                                                    <p><?php if ($sink_type == "farm_house") {
        echo "Farm House";
    } else {
        echo "Regular";
    }?></p>
                                                                </li>
                                                                <li>
                                                                    <label>Molding Type</label>
                                                                    <?php
$molding_type = get_field('molding_type')['0'];
    ?>
                                                                    <p><?php if ($molding_type == "Crown_Molding") {
        echo "Crown Molding";
    } elseif ($molding_type == "Toe_Kick") {
        echo "Toe Kick";
    } else {
        echo "Other";
    }?></p>
                                                                </li>
                                                                <?php
$uscd_multiplier = get_field('uscd_multiplier');
    if (!empty($uscd_multiplier)) {
        printf('<li><label>USCD Multiplier</label><p>%s</p></li>', $uscd_multiplier);
    }
    ?>
                                                                <?php
$other_molding = get_field('other_molding_style');
    if (isset($other_molding)): ?>
                                                                <li>
                                                                    <label>Other Molding Style</label>
                                                                    <p><?php echo $other_molding; ?></p>
                                                                </li>
                                                                <?php endif;?>
                                                                <li>
                                                                    <label>Cabinet Brand</label>
                                                                    <?php
$cabinet_brand = get_field('cabinet_brand');
    ?>
                                                                    <p><?php if ($cabinet_brand == "Luxor") {
        echo "Luxor";
    } else {
        echo "USCD";
    }

    ?></p>
                                                                </li>
                                                                <?php
if ($cabinet_brand == "USCD"):
        $cabinet_style = get_field('cabinet_style');
        if (!empty($cabinet_style)):
        ?>
                                                                <li>
                                                                    <label>Cabinet Style</label>
                                                                    <p>
                                                                        <?php
    switch ($cabinet_style) {
        case "Cabinet_Style":
            echo "Cabinet Style";
            break;
        case "shaker":
            echo "Shaker";
            break;
        case "casselberry":
            echo "Casselberry";
            break;
        case "york":
            echo "York";
            break;
        case "torrance":
            echo "Torrance";
            break;
        case "torino":
            echo "Torino";
            break;
        case "riviera":
            echo "Riviera";
            break;
        case "palermo":
            echo "Palermo";
            break;
        }
        ?>
                                                                    </p>
                                                                </li>
                                                                <?php
endif;
    $cabinet_color = get_field('cabinet_color');
    if (!empty($cabinet_color)):
    ?>
                                                                <li>
                                                                    <label>Cabinet Color</label>

                                                                    <p>
                                                                        <?php
switch ($cabinet_color) {
    case "Casselberry_Saddle":
        echo "Casselberry Saddle";
        break;
    case "Casselberry_Antique_White":
        echo "Casselberry Antique White";
        break;
    case "York_Chocolate":
        echo "York Chocolate";
        break;
    case "York_Antique_White":
        echo "York Antique White";
        break;
    case "Shaker_Cinder":
        echo "Shaker Cinder";
        break;
    case "Shaker_Espresso":
        echo "Shaker Espresso";
        break;
    case "Shaker_Grey":
        echo "Shaker Grey";
        break;
    case "Shaker_Dove":
        echo "Shaker Dove";
        break;
    case "Shaker_Antique_White":
        echo "Shaker Antique White";
        break;
    case "Shaker_White":
        echo "Shaker White";
        break;
    case "Torrance_Dove":
        echo "Torrance Dove";
        break;
    case "Torrance_White":
        echo "Torrance White";
        break;
    case "Torino_Grey_Wood":
        echo "Torino Grey Wood";
        break;
    case "Torino_Dark_Wood":
        echo "Torino Dark Wood";
        break;
    case "Torino_White_Pine":
        echo "Torino_White_Pine";
        break;
    case "Riviera_Conch_Shell":
        echo "Riviera Conch Shell";
        break;
    case "Palermo_Gloss_White":
        echo "Palermo Gloss White";
        break;
    default:
        echo "Cabinet Color";
    }
    ?>
                                                                    </p>
                                                                </li>
                                                                <?php endif;
    endif;?>
                                                                <?php if ($cabinet_brand == "Luxor"): ?>
                                                                <li>
                                                                    <label>Luxor Type</label>
                                                                    <?php
$luxor = get_field('luxor');
    ?>
                                                                    <p><?php if ($luxor == "Farm_House") {
        echo "Farm House";
    } else {
        echo "Luxor";
    }?></p>
                                                                </li>
                                                                <?php endif;?>
                                                                <?php
$order_notes = get_field('order_notes');
    if (!empty($order_notes)) {
        printf('<li><label>Order Notes</label><p>%s</p></li>', $order_notes);
    }
    $upload_file_url = get_field('upload_file_url');
    if (!empty($upload_file_url)) {
        printf('<li><label>Upload Files</label><br><a href="%s" download target="_blank"><button type="button" class="btn btn-success"><i class="feather mr-2 icon-download"></i>Download</button></a></li>', $upload_file_url);
    }
    ?>
                                                            </ol>

                                                        </div>
                                                    </div>


                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="row">
                                        <div class="col-md-12">

                                            <?php
$meta = (array) get_post_meta(get_the_ID(), '_upstream_project_files', true);
    ?>

                                            <div class="card">
                                                <div class="card-header" style="display: flex;
                                                                                justify-content: space-between;
                                                                                align-items: center;
                                                                                ">
                                                    <h5>Project File Discussion</h5>
                                                    <div>
                                                        <strong>Total Files:&nbsp;
                                                        </strong>
                                                        <span style="font-weight: bolder;">
                                                            <i style="color: #886f6f;font-size: 0.9em;"
                                                                class="fas fa-paperclip"></i>&nbsp;
                                                            <span class="total_project_count" style="color: #68acff;">
                                                                <?php echo allRevisionCount(get_the_ID()) ?>
                                                            </span>
                                                        </span>
                                                    </div>
                                                </div>

                                                <div class="card-body">

                                                    <div class="x_content" style="display: block;">
                                                        <?php foreach ($meta as $file_value) {

        if ($file_value) {?>
                                                        <h5>
                                                            File Name :
                                                            <strong>
                                                                <?php echo $file_value['file'] ? pathinfo($file_value['file'], PATHINFO_BASENAME) : 'No file uploaded'; ?>
                                                            </strong>
                                                        </h5>

                                                        <?php
$comments = get_comments(array(
            'post_id'    => get_the_ID(),
            'meta_key'   => 'id',
            'meta_value' => $file_value['id']
        ));

            if ($comments) {

                foreach ($comments as $comment) {
                    ?>



                                                        <div class="o-comment s-status-approved"
                                                            id="comment-<?php echo $comment->comment_ID ?>"
                                                            data-id="<?php echo $comment->comment_ID ?>">
                                                            <?php if ($comment->comment_parent == 0) {?>
                                                            <div class="o-comment__body">
                                                                <div class="o-comment__body__left">
                                                                    <img class="o-comment__user_photo"
                                                                        src="http://2.gravatar.com/avatar/29618afe0a56b675c5d1de92d8783806?s=96&amp;d=mm&amp;r=g"
                                                                        width="30">
                                                                </div>
                                                                <div class="o-comment__body__right">
                                                                    <div class="o-comment__body__head">
                                                                        <div class="o-comment__user_name"
                                                                            style="display: flex;
                                                                                                                    align-items: center;
                                                                                                                    justify-content: space-between;">
                                                                            <?php echo $comment->comment_author ?>
                                                                            <div>
                                                                                <span
                                                                                    style="font-weight: bolder; margin-left: 10px;">
                                                                                    <i style="color: #886f6f;font-size: 0.8em;"
                                                                                        class="fas fa-paperclip"></i>
                                                                                    <span
                                                                                        style="color: #68acff; font-size: 0.9em;"
                                                                                        class="total_project_count"><?php echo get_revison_count(get_the_ID(), $comment->comment_ID) ?></span>
                                                                                </span>
                                                                            </div>



                                                                        </div>

                                                                        <div class="o-comment__reply_info"></div>
                                                                        <div class="o-comment__date"
                                                                            data-toggle="tooltip" title=""
                                                                            data-original-title="<?php echo date_format(date_create($comment->comment_date), "F d, Y h:i a") ?>">
                                                                            <?php echo human_time_diff(strtotime($comment->comment_date), current_time('timestamp')) ?>
                                                                            ago
                                                                        </div>
                                                                    </div>
                                                                    <div class="o-comment__content">
                                                                        <p><?php echo str_replace('&nbsp;', '', preg_replace('/(.zip|.jpg|.png|.jpeg|.csv|.txt|.docx|.pdf)/', '', $comment->comment_content)) ?>
                                                                        </p>
                                                                    </div>

                                                                    <h6>
                                                                        Revision Type:
                                                                        <strong>
                                                                            <i><?php echo getProjectRevsionType(get_the_ID(), $comment->comment_ID); ?></i>
                                                                        </strong>
                                                                    </h6>
                                                                    <div class="o-comment__body__footer">
                                                                        <a data-item-id="<?php echo $file_value['id'] ?>"
                                                                            data-project-id="<?php echo get_the_ID() ?>"
                                                                            data-toggle="modal"
                                                                            data-target="#modal-reply_comment" href="#"
                                                                            class="project_file_comment_reply"
                                                                            data-action="comment.reply"
                                                                            data-nonce="ea14e753ee">
                                                                            <i class="fa fa-reply"></i>&nbsp;
                                                                            Reply
                                                                        </a>

                                                                        <!-- Upload Button -->
                                                                        <a data-item-id="<?php echo $file_value['id'] ?>"
                                                                            data-file_id="<?php echo $file_value['file_id'] ?>"
                                                                            data-project-id="<?php echo get_the_ID() ?>"
                                                                            data-comment_id="<?php echo $comment->comment_ID ?>"
                                                                            data-toggle="modal" href="#"
                                                                            class="comment_revision_file_upload">
                                                                            <i class="fas fa-upload"></i>&nbsp;
                                                                            Upload
                                                                        </a>
                                                                        <!-- End of Upload Button -->


                                                                        <!-- View revison files button -->
                                                                        <a data-item-id="<?php echo $file_value['id'] ?>"
                                                                            data-project-id="<?php echo get_the_ID() ?>"
                                                                            data-comment_id="<?php echo $comment->comment_ID ?>"
                                                                            data-toggle="modal"
                                                                            data-target="#modal-revision_store" href="#"
                                                                            class="revision_files">
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
                                                            <?php }?>
                                                        </div>


                                                        <?php }?>

                                                        <?php } else {
                ?>
                                                        <h4>No comments found for this file</h4>
                                                        <br>
                                                        <br>
                                                        <?php }?>

                                                        <?php }?>
                                                        <?php }?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- <?php if (upstreamAreProjectCommentsEnabled()): ?>
                                        <div class="col-md-12">
                                            <div class="card">
                                                <div class="card-header" style="display: flex;
                                                                                justify-content: space-between;
                                                                                align-items: center;
                                                                                ">
                                                    <h5>Project Discussion</h5>
                                                    <div>
                                                        <strong>Total Files:&nbsp;
                                                        </strong>
                                                        <span style="font-weight: bolder;">
                                                            <i style="color: #886f6f;font-size: 0.9em;"
                                                                class="fas fa-paperclip"></i>&nbsp;
                                                            <span class="total_project_count" style="color: #68acff;">
                                                                <?php echo totalReuploadedFiles(get_the_ID()) ?>
                                                            </span>
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="card-body">
                                                    <div class="x_content" style="display: block;">
                                                        <div class="row" id="project-section-discussion ">
                                                            <?php
do_action('upstream_single_project_before_discussion');
    upstream_get_template_part('single-project/discussion.php');
    do_action('upstream_single_project_after_discussion');
    ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endif;?> -->
                                    </div>
                                </div>
                            </div>
                            <!-- [ Main Content ] end -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- [ Main Content ] end -->

    <input type="hidden" id="project_id" value="<?php echo upstream_post_id(); ?>">




    <?php endwhile;
    /*
     * upstream_after_project_content hook.
     *
     */

    do_action('upstream_after_project_content');

    include_once 'global/footer.php';
    ?>


    <script>
    jQuery(document).ready(function($) {

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

        let data = {};

        $('.project_file_comment_reply').on('click', function(e) {
            data.project_id = parseInt($(e.currentTarget).attr('data-project-id'));
            data.parent_id = parseInt($(e.currentTarget).parents('.o-comment').attr('data-id'));
            data.item_type = 'file';
            data.item_id = $(e.currentTarget).attr('data-item-id');
            $("#naormal-project-discussion").hide();
            $("#add_reply_btn").hide();
            $("#designrta_reply_comment").show();
            $("#add_reply_btn_designrta").show();
        })

        $('.o-comment-control').on('click', function() {
            $("#designrta_reply_comment").hide();
            $("#add_reply_btn_designrta").hide();
            $("#naormal-project-discussion").show();
            $("#add_reply_btn").show();
        })

        $('#add_reply_btn_designrta').on('click', function(e) {
            e.preventDefault();
            data.content = $('#designrta_reply_comment').val()
            $.ajax({
                type: 'POST',
                url: ajaxurl,
                data: {
                    action: 'upstream:project.add_comment_reply',
                    // nonce: self.data('nonce'),
                    project_id: data.project_id,
                    parent_id: data.parent_id,
                    content: data.content,
                    item_type: data.item_type || null,
                    item_id: data.item_id || null
                },
                beforeSend: function() {
                    $('#designrta_reply_comment').attr('disabled', 'disabled');
                    $(e.currentTarget).attr('disabled', 'disabled')
                },
                success: function(response) {
                    console.log(response);
                    if (response.error) {
                        console.error(response.error);
                        make_alert(response.error, true)
                    } else {
                        if (!response.success) {
                            console.error('Something went wrong.');
                            make_alert('Something went wrong.')
                        } else {
                            make_alert('success')
                            location.reload();
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

            });
        })


        /* Media uploader */

        $('.comment_revision_file_upload').on('click', (e) => {
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
                get_file_value(file_frame, e)
            })
        }

        function get_file_value(file_frame, e) {

            let attachment = file_frame.state().get('selection').first();

            let attachment_obj = {

                attachment_id: attachment.id,
                upload_date: attachment.attributes.dateFormatted,
                uploader_name: attachment.attributes.authorName,
                attachment_filename: attachment.attributes.filename,
                attachment_url: attachment.attributes.url,
                post_id: $(e.currentTarget).attr('data-project-id'),
                comment_id: $(e.currentTarget).attr('data-comment_id'),
                file_id: $(e.currentTarget).attr('data-file_id'),

            }

            save_discussion_revison_file(attachment_obj, e)

        }

        function save_discussion_revison_file(attachment_obj, e) {

            let revision_count = $(e.currentTarget).parent().find('.revision_file_count').text()

            $.ajax({
                type: "post",
                url: "<?php echo admin_url('admin-ajax.php') ?>",
                data: {
                    action: 'save_discussion_revison',
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
                        make_alert('This File already uploaded for this revision comment')
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
                        let count = parseInt(revision_count);
                        $(e.currentTarget).parent().find('.revision_file_count').html(count += 1)
                    }
                },
                error: () => {
                    alert('Something went wrong')
                }
            });
        }



        /* get revison files */

        $('.revision_files').on('click', (e) => {
            e.preventDefault();
            let data = {
                post_id: $(e.currentTarget).attr('data-project-id'),
                comment_id: $(e.currentTarget).attr('data-comment_id')
            }

            $.ajax({
                type: "post",
                url: "<?php echo admin_url('admin-ajax.php') ?>",
                data: {
                    action: 'get_discussion_revison',
                    data: data
                },

                beforeSend: () => {
                    let rows = `<tr>
                                    <td scope="row" colspan="5" style="text-align: center;"><strong>Loading...</strong></td>
                                </tr>`;
                    $('#revision_table').html(rows)
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

                        $('#revision_table').html(rows)
                    }
                    if (output.response == 'empty') {
                        let rows = `<tr>
                                        <td scope="row" colspan="5" style="text-align: center;"><strong>No File Found</strong></td>
                                    </tr>`;
                        $('#revision_table').html(rows)
                    }
                }
            });
        })

    });

    $('.uploaded_project_files').click(e => {
        $("#modal-project-file").modal();
        e.preventDefault();

        let data = {
            post_id: $(e.currentTarget).attr('data-post_id'),
            comment_id: $(e.currentTarget).attr('data-comment_id')
        }

        $.ajax({
            type: "post",
            url: "<?php echo admin_url('admin-ajax.php') ?>",
            data: {
                action: 'get_re_uploaded_project',
                data: data
            },

            beforeSend: () => {
                let rows = `<tr>
                              <td scope="row" colspan="5" style="text-align: center;"><strong>Loading...</strong></td>
                            </tr>`;
                $('#reUploadedFiles').html(rows)
            },
            success: function(response) {
                let output = JSON.parse(response)

                console.log(output)
                if (output.response == 'success') {

                    let rows = ``;
                    let projectFiles = output.projectFiles;

                    projectFiles.forEach((project, i) => {
                        rows += `<tr>
                                    <th scope="row">${i+=1}</th>
                                    <td>${project.attachment_filename}</td>
                                    <td>${project.uploader_name}</td>
                                    <td>${project.upload_date}</td>
                                    <td style="text-align: center;"><a href="${project.attachment_url}" download type="button" class="btn btn-primary">Download File</a></td>
                                </tr>`;
                    });

                    $('#reUploadedFiles').html(rows)
                }
                if (output.response == 'empty') {
                    let rows = `<tr>
                                    <td scope="row" colspan="5" style="text-align: center;"><strong>No File Found</strong></td>
                                </tr>`;
                    $('#reUploadedFiles').html(rows)
                }
            }
        });
    })

    $('.delete_project_file').click(e => {
        e.preventDefault();

        let projectCount = $('.total_project_count').text();

        let data = {
            post_id: $(e.currentTarget).attr('data-post_id'),
            comment_id: $(e.currentTarget).attr('data-comment_id')
        }

        $.ajax({
            type: "post",
            url: "<?php echo admin_url('admin-ajax.php') ?>",
            data: {
                action: 'delete_re_uploaded_project',
                data: data
            },

            success: function(response) {

                console.log(response);

                let res = JSON.parse(response)

                if (res.response == 'invalid_action') {
                    make_alert('Action is invalid')
                }
                if (res.response == 'missing_parameter') {
                    make_alert('One or more parameter is missing')
                }

                if (res.response == 'failed') {
                    make_alert('Failed to delete project file attachment')
                }
                if (res.response == 'success') {

                    make_alert('Project files deleted with comment.')

                    let count = parseInt(projectCount);
                    $('.total_project_count').html(count -= 1)
                }

            },

            error: (err) => {
                alert('Something went wrong. Contact the developer');
                console.err(err);
            }
        });



    })
    </script>
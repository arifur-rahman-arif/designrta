<?php

/**
 *
 * Template Name: Dashboard page
 * The Template for displaying a single project
 *
 * This template can be overridden by copying it to yourtheme/upstream/single-project.php.
 *
 *
 */

?>
<?php
// Prevent direct access.
if (!defined('ABSPATH')) {
    exit;
}

/*
 * The Template for displaying all projects
 *
 * This template can be overridden by copying it to wp-content/themes/yourtheme/upstream/archive-project.php.
 */

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

$pluginOptions = get_option('upstream_general');
$areClientsEnabled = !is_clients_disabled();

$archiveClosedItems = upstream_archive_closed_items();

$i18n = [
    'LB_PROJECT'        => upstream_project_label(),
    'LB_PROJECTS'       => upstream_project_label_plural(),
    'LB_TASKS'          => upstream_task_label_plural(),
    'LB_BUGS'           => upstream_bug_label_plural(),
    'LB_LOGOUT'         => __('Log Out', 'upstream'),
    'LB_ENDS_AT'        => __('Ends at', 'upstream'),
    'MSG_SUPPORT'       => upstream_admin_support_label($pluginOptions),
    'LB_TITLE'          => __('Title', 'upstream'),
    'LB_TOGGLE_FILTERS' => __('Toggle Filters', 'upstream'),
    'LB_EXPORT'         => __('Export', 'upstream'),
    'LB_PLAIN_TEXT'     => __('Plain Text', 'upstream'),
    'LB_CSV'            => __('CSV', 'upstream'),
    'LB_CLIENT'         => upstream_client_label(),
    'LB_CLIENTS'        => upstream_client_label_plural(),
    'LB_STATUS'         => __('Status', 'upstream'),
    'LB_STATUSES'       => __('Statuses', 'upstream'),
    'LB_CATEGORIES'     => __('Last Status Update'),
    'LB_PROGRESS'       => __('Progress', 'upstream'),
    'LB_NONE_UCF'       => __('None', 'upstream'),
    'LB_NONE'           => __('none', 'upstream'),
    'LB_COMPLETE'       => __('%s Complete', 'upstream')
];

$currentUser = (object) upstream_user_data(@$_SESSION['upstream']['user_id']);

$projectStatuses = upstream_get_all_project_statuses();
$projectOrder = [];

$statuses = [];
// We start from 1 instead of 0 because the 0 position is used for "__none__".
$statusIndex = 1;
foreach ($projectStatuses as $statusId => $status) {
    $projectOrder[$statusIndex++] = $statusId;

    // If closed items will be archived, we do not need to display closed statuses.
    if ($archiveClosedItems && 'open' !== $status['type']) {
        continue;
    }

    $statuses[$status['id']] = $status;

    if ('open' === $status['type']) {
        $openStatuses[] = $status['id'];
    }
}

$projectsList = [];

$projectsStatus = [];

$current_projects = $currentUser->projects;

echo showUnApprovedProjects($current_projects);

if (isset($current_projects)) {
    if (is_array($current_projects) && count($current_projects) > 0) {
        foreach ($current_projects as $project_id => $project) {
            $data = (object) [
                'id'                 => $project_id,
                'author'             => (int) $project->post_author,
                'created_at'         => (string) $project->post_date_gmt,
                'modified_at'        => (string) $project->post_modified_gmt,
                'title'              => $project->post_title,
                'slug'               => $project->post_name,
                'status'             => $project->post_status,
                'permalink'          => get_permalink($project_id),
                'startDateTimestamp' => (int) upstream_project_start_date($project_id),
                'endDateTimestamp'   => (int) upstream_project_end_date($project_id),
                'progress'           => (float) upstream_project_progress($project_id),
                'status'             => (string) upstream_project_status($project_id),
                'clientName'         => null,
                'categories'         => [],
                'features'           => [
                    ''
                ]
            ];

            // If should archive closed items, we filter the rowset.
            if ($archiveClosedItems) {
                if (!empty($data->status) && !in_array($data->status, $openStatuses)) {
                    continue;
                }
            }

            $data->startDate = (string) upstream_format_date($data->startDateTimestamp);
            $data->endDate = (string) upstream_format_date($data->endDateTimestamp);

            if ($areClientsEnabled) {
                $data->clientName = trim((string) upstream_project_client_name($project_id));
            }

            if (isset($statuses[$data->status])) {
                $data->status = $statuses[$data->status];
            }

            $data->timeframe = $data->startDate;
            if (!empty($data->endDate)) {
                if (!empty($data->timeframe)) {
                    $data->timeframe .= ' - ';
                } else {
                    $data->timeframe = '<i>' . $i18n['LB_ENDS_AT'] . '</i>';
                }

                $data->timeframe .= $data->endDate;
            }

            $categories = (array) wp_get_object_terms($data->id, 'project_category');
            if (count($categories) > 0) {
                foreach ($categories as $category) {
                    $data->categories[$category->term_id] = $category->name;
                }
            }

            $projectsList[$project_id] = $data;
        }

        unset($project, $project_id);
    }

    unset($currentUser->projects);
}

$projectStatus = [];

foreach ($projectsList as $project) {

    if ($project->status) {
        $projectStatus[] = $project->status['name'];
    }
}
// wp_update_post([
//     'ID'                => 2999,
//     'post_modified'     => date('Y-m-d H:i:s', current_time('timestamp', 0)),
//     'post_modified_gmt' => date('Y-m-d H:i:s', current_time('timestamp', 0))
// ]);
$readyforApproval = [];
$Completed = [];
$designInProgress = [];
$inRevition = [];
$newComment = [];

foreach ($projectStatus as $project) {

    if ($project == 'Ready For Approval') {
        $readyforApproval[] = $project;
    }

    if ($project == 'Completed') {
        $Completed[] = $project;
    }

    if ($project == 'Design In Progress') {
        $designInProgress[] = $project;
    }

    if ($project == 'Revision Requested') {
        $inRevition[] = $project;
    }

    if ($project == 'New Comment') {
        $newComment[] = $project;
    }
}

$projectsListCount = count($projectsList);
$approvalCount = count($readyforApproval);
$completedCount = count($Completed);
$inProgressCount = count($designInProgress);
$inRevitionCount = count($inRevition);
$newCommentCount = count($newComment);

upstream_get_template_part('global/header.php');
upstream_get_template_part('global/sidebar.php');
upstream_get_template_part('global/top-nav.php');

$categories = (array) get_terms([
    'taxonomy'   => 'project_category',
    'hide_empty' => false
]);

$projectsView = !isset($_GET['view']);

// Filters
$tableSettings = [
    'id'              => 'projects',
    'type'            => 'project',
    'data-ordered-by' => 'start_date',
    'data-order-dir'  => 'DESC'
];
$columnsSchema = \UpStream\Frontend\getProjectFields();

$hiddenColumnsSchema = [];

foreach ($columnsSchema as $columnName => $columnArgs) {
    if (isset($columnArgs['isHidden']) && (bool) $columnArgs['isHidden'] === true) {
        $hiddenColumnsSchema[$columnName] = $columnArgs;
    }
}

$filter_closed_items = upstream_filter_closed_items();

$ordering = \UpStream\Frontend\getTableOrder('projects');
$orderBy = '';
$orderDir = '';
if (!empty($ordering)) {
    $orderBy = $ordering['column'];
    $orderDir = $ordering['orderDir'];
}
?>

<!-- [ Main Content ] start -->

<?php if ($projectsView): ?>
<div class="pcoded-main-container">
    <div class="pcoded-content">
        <!-- [ breadcrumb ] start -->
        <div class="page-header">
            <div class="page-block">
                <div class="row align-items-center">
                    <div class="col-md-12">
                        <div class="page-header-title">
                            <h5 class="m-b-10">Dashboard</h5>

                        </div>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?php bloginfo('url');?>"><i
                                        class="feather icon-home"></i></a></li>
                            <li class="breadcrumb-item"><a href="<?php bloginfo('url');
echo "/projects";?>">Dashboard</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <!-- [ breadcrumb ] end -->
        <!-- [ Main Content ] start -->
        <div class="row">
            <div class="col-lg-7 col-md-12">
                <!-- support-section start -->
                <div class="row">
                    <div class="col-sm-6">
                        <div class="card support-bar overflow-hidden">
                            <div class="card-body pb-0">
                                <h2 class="m-0">Add A New Project</h2>
                                <span class="text-c-green">Submit A New Design Request</span><br>
                                <div class="addprojecticon">

                                    <a href="<?php echo site_url('/add-project') ?>">
                                        <i id="upstream_new_project_icon" class="fas fa-plus-circle fa-7x "></i>
                                    </a>

                                </div>
                            </div>
                            <div class="card-footer bg-success text-white">
                                <div class="row text-center">
                                    <div class="col">
                                        <h4 class="m-0 text-white">
                                            <?php echo get_user_credits() ?>
                                        </h4>
                                        <span>Available Credits</span>
                                    </div>
                                    <div class="col"
                                        style="border-left-color: #fff; border-left-style: solid; border-left-width: 2px;">
                                        <h4 class="m-0 text-white">Use Credits</h4>
                                        <span>Use Coupon</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="card support-bar overflow-hidden">
                            <div class="card-body pb-0">
                                <h2 class="m-0">
                                    <?php
if ($projectsListCount > 0) {
    echo $projectsListCount;
} else {
    echo '0';
}
?>
                                </h2>
                                <span class="text-c-blue">Project Overview</span>
                                <p class="mb-3 mt-3">All of your project details</p>
                            </div>
                            <div id="support-chart"></div>
                            <div class="card-footer bg-primary text-white">
                                <div class="row text-center">
                                    <div class="col">
                                        <h4 class="m-0 text-white">
                                            <!--                                            10-->
                                            <?php
if ($inProgressCount > 0) {
    echo $inProgressCount;
} else {
    echo "0";
}
?>
                                        </h4>
                                        <span>Design In Progress</span>
                                    </div>
                                    <div class="col">
                                        <h4 class="m-0 text-white">
                                            <!--                                            5-->
                                            <?php
if ($approvalCount > 0) {
    echo $approvalCount;
} else {
    echo "0";
}
?>
                                        </h4>
                                        <span>Need Approval</span>
                                    </div>
                                    <div class="col">
                                        <h4 class="m-0 text-white">
                                            <!--                                            3-->
                                            <?php
if ($completedCount > 0) {
    echo $completedCount;
} else {
    echo "0";
}
?>
                                        </h4>
                                        <span>Completed</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- support-section end -->
            </div>
            <div class="col-lg-5 col-md-12">
                <!-- page statustic card start -->
                <div class="row">

                    <div class="col-sm-6 col-6">
                        <div class="card">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-8">
                                        <h4 class="text-c-yellow">
                                            <!--                                            4-->
                                            <?php
if ($inProgressCount > 0) {
    echo $inProgressCount;
} else {
    echo "0";
}
?>

                                        </h4>
                                        <h6 class="text-muted m-b-0">In Progress</h6>
                                    </div>
                                    <div class="col-4 text-right">
                                        <!-- <i class="feather icon-bar-chart-2 f-28"></i> -->
                                        <i class="fas fa-drafting-compass f-28"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer bg-c-yellow in-progress-c">
                                <div class="row align-items-center">
                                    <div class="col-9">
                                        <p class="text-white m-b-0">View Projects</p>
                                    </div>
                                    <div class="col-3 text-right">
                                        <i class="feather icon-arrow-right text-white f-16"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-6 col-6">
                        <div class="card">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-8">
                                        <h4 class="text-c-green">
                                            <!--                                            33-->
                                            <?php
if ($completedCount > 0) {
    echo $completedCount;
} else {
    echo "0";
}
?>

                                        </h4>
                                        <h6 class="text-muted m-b-0">Completed</h6>
                                    </div>
                                    <div class="col-4 text-right">
                                        <!-- <i class="feather icon-file-text f-28"></i> -->
                                        <i class="fas fa-check f-28"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer bg-c-green completed-c">
                                <div class="row align-items-center">
                                    <div class="col-9">
                                        <p class="text-white m-b-0">View Projects</p>
                                    </div>
                                    <div class="col-3 text-right">
                                        <i class="feather icon-arrow-right text-white f-16"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-6 col-6">
                        <div class="card">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-8">
                                        <h4 class="text-c-red">
                                            <!--                                            5-->
                                            <?php
if ($inRevitionCount > 0) {
    echo $inRevitionCount;
} else {
    echo "0";
}
?>

                                        </h4>
                                        <h6 class="text-muted m-b-0">In Revision</h6>
                                    </div>
                                    <div class="col-4 text-right">
                                        <!-- <i class="feather icon-calendar f-28"></i> -->
                                        <i class="fas fa-history f-28"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer bg-c-red in-revision-c">
                                <div class="row align-items-center">
                                    <div class="col-9">
                                        <p class="text-white m-b-0">View Projects</p>
                                    </div>
                                    <div class="col-3 text-right">
                                        <i class="feather icon-arrow-right text-white f-16"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-6 col-6">
                        <div class="card">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-8">
                                        <h4 class="text-c-blue">
                                            <!--                                            30-->
                                            <?php
if ($approvalCount > 0) {
    echo $approvalCount;
} else {
    echo "0";
}
?>
                                        </h4>
                                        <h6 class="text-muted m-b-0">Need Approval</h6>
                                    </div>
                                    <div class="col-4 text-right">
                                        <!-- <i class="feather icon-thumbs-down f-28"></i> -->
                                        <i class="fas fa-exclamation f-28"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer bg-c-blue need-approval-c">
                                <div class="row align-items-center">
                                    <div class="col-9">
                                        <p class="text-white m-b-0">View Projects</p>
                                    </div>
                                    <div class="col-3 text-right">
                                        <i class="feather icon-arrow-right text-white f-16"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <!-- page statustic card end -->
            </div>
            <!-- prject ,team member start ---------------------------------------------------------------------->
            <div id="project-id" class="col-xl-12 col-md-12">
                <div class="card table-card">
                    <div class="card-header">
                        <h5>Projects</h5>

                    </div>

                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>
                                            Project Name
                                        </th>
                                        <th>Client Name</th>
                                        <th>Start Date</th>
                                        <th>Delivery ETA</th>
                                        <th>Last Status Update</th>
                                        <th class="text-center">Status</th>

                                    </tr>
                                </thead>

                                <tbody>
                                    <?php

$isProjectIndexOdd = true;

foreach ($projectsList as $projectIndex => $project): ?>
                                    <?php
$project = apply_filters(
    'upstream_frontend_project_data',
    $project,
    $project->id
);

if ($project->status) {

    $project_status_id = $project->status['id'];
}

if ($project_status_id == 'gpaz9' && '' != $project->status) {

    $project_class = 'design-in-progress';
} elseif ($project_status_id == '6ea5e' && '' != $project->status) {

    $project_class = 'ready-for-approval';
} elseif ($project_status_id == 'azp39' && '' != $project->status) {

    $project_class = 'completed';
} elseif ($project_status_id == 'cerwk' && '' != $project->status) {

    $project_class = 'in-revision';
} else {

    $project_class = '';
}

?>
                                    <tr class="t-row-<?php echo $isProjectIndexOdd ? 'odd' : 'even'; ?> <?php echo $project_class; ?>"
                                        data-id="<?php echo $project->id; ?>">

                                        <?php if (upstream_override_access_field(true, UPSTREAM_ITEM_TYPE_PROJECT, $project->id, null, 0, 'title', UPSTREAM_PERMISSIONS_ACTION_VIEW)): ?>
                                        <td data-column="title" data-value="<?php echo esc_attr($project->title); ?>">
                                            <div class="d-inline-block align-middle">
                                                <span
                                                    class="img-radius wid-40 align-top m-r-15"><?php echo get_avatar(get_the_author_meta("id")); ?></span>
                                                <div class="d-inline-block">
                                                    <h6><?php do_action('upstream:frontend.project.details.before_title', $project);?>
                                                        <?php echo esc_html($project->title); ?>
                                                    </h6>
                                                    <p class="text-muted m-b-0">
                                                        <a href="<?php echo esc_url($project->permalink); ?>"
                                                            style="color: #053E89;">
                                                            View Project
                                                        </a>
                                                    </p>
                                                </div>
                                            </div>
                                        </td>

                                        <?php else: ?>
                                        <td data-column="title" data-value="">
                                            <span class="label up-o-label"
                                                style="background-color:#666;color:#fff">(hidden)</span>
                                        </td>
                                        <?php endif;?>


                                        <?php if ($areClientsEnabled): ?>

                                        <?php if (upstream_override_access_field(true, UPSTREAM_ITEM_TYPE_PROJECT, $project->id, null, 0, 'client', UPSTREAM_PERMISSIONS_ACTION_VIEW)): ?>
                                        <td data-column="client"
                                            data-value="<?php echo $project->clientName !== null ? esc_attr($project->clientName) : '__none__'; ?>">
                                            <?php if ($project->clientName !== null): ?>
                                            <?php echo esc_html($project->clientName); ?>
                                            <?php else: ?>
                                            <i class="s-text-color-gray"><?php echo esc_html($i18n['LB_NONE']); ?></i>
                                            <?php endif;?>
                                        </td>
                                        <?php else: ?>
                                        <td data-column="client" data-value="">
                                            <span class="label up-o-label"
                                                style="background-color:#666;color:#fff">(hidden)</span>
                                        </td>
                                        <?php endif;?>

                                        <?php endif;?>

                                        <?php if (upstream_override_access_field(true, UPSTREAM_ITEM_TYPE_PROJECT, $project->id, null, 0, 'start', UPSTREAM_PERMISSIONS_ACTION_VIEW)): ?>
                                        <td data-column="startDate"
                                            data-value="<?php echo esc_attr($project->startDateTimestamp); ?>">
                                            <?php echo esc_html($project->startDate); ?>
                                        </td>
                                        <?php else: ?>
                                        <td data-column="startDate" data-value="">
                                            <span class="label up-o-label"
                                                style="background-color:#666;color:#fff">(hidden)</span>
                                        </td>
                                        <?php endif;?>

                                        <?php if (upstream_override_access_field(true, UPSTREAM_ITEM_TYPE_PROJECT, $project->id, null, 0, 'end', UPSTREAM_PERMISSIONS_ACTION_VIEW)): ?>
                                        <td data-column="endDate"
                                            data-value="<?php echo esc_attr($project->endDateTimestamp); ?>">
                                            <?php echo esc_html($project->endDate); ?>
                                        </td>
                                        <?php else: ?>
                                        <td data-column="endDate" data-value="">
                                            <span class="label up-o-label"
                                                style="background-color:#666;color:#fff">(hidden)</span>
                                        </td>
                                        <?php endif;?>

                                        <?php if (upstream_override_access_field(true, UPSTREAM_ITEM_TYPE_PROJECT, $project->id, null, 0, 'categories', UPSTREAM_PERMISSIONS_ACTION_VIEW)): ?>
                                        <!--last update-->
                                        <td data-column="categories" data-value="<?php echo count($project->categories) ? esc_attr(implode(
    ',',
    array_keys((array) $project->categories)
)) : '__none__'; ?>">
                                            <?php
if (!empty($project->status)) {

    if ($project->status['name'] == 'Completed') {?>
                                            <div id="counter_<?php echo $project->id; ?>" style="display: none"></div>
                                            <?php } else {?>
                                            <div id="counter_<?php echo $project->id; ?>"
                                                style="background-color:#f5f5f5; color: rgb(0, 0, 0);padding: 5px;">
                                                <?php echo $project->modified_at ?></div>
                                            <?php }
}?>

                                        </td>
                                        <script>
                                        document.getElementById("counter_<?php echo $project->id; ?>").innerHTML = "";
                                        let countDownDate_<?php echo $project->id; ?> = new Date(
                                            "<?php echo $project->modified_at; ?>").getTime();

                                        var x = setInterval(function() {
                                            var now = new Date();
                                            var nowUtc = new Date(now.getTime() + (now.getTimezoneOffset() *
                                                60000)).getTime();

                                            var distance = nowUtc - countDownDate_<?php echo $project->id; ?>;
                                            var days = Math.floor(distance / (1000 * 60 * 60 * 24));
                                            var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 *
                                                60 * 60));
                                            var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 *
                                                60));
                                            var seconds = Math.floor((distance % (1000 * 60)) / 1000);

                                            document.getElementById("counter_<?php echo $project->id; ?>")
                                                .innerHTML = days + "d " + hours + "h " + minutes + "m " +
                                                seconds + "s ";

                                            // If the count down is over, write some text
                                            if (distance < 0) {
                                                clearInterval(x);
                                                document.getElementById("counter_<?php echo $project->id; ?>")
                                                    .innerHTML = "EXPIRED";
                                            }
                                        }, 1000);
                                        </script>
                                        <?php else: ?>
                                        <td data-column="categories" data-value="">
                                            <span class="label up-o-label"
                                                style="background-color:#666;color:#fff">(hidden)</span>
                                        </td>
                                        <?php endif;?>


                                        <?php
if ($project->status !== null && is_array($project->status)) {
    $status = $project->status;
} else {
    $status = [
        'id'    => '',
        'name'  => '',
        'color' => '#aaa',
        'order' => '0'
    ];
}
//Last status update
$statusOrder = array_search($status['id'], $projectOrder);
?>
                                        <!--- --------------last status update-->
                                        <?php if (upstream_override_access_field(true, UPSTREAM_ITEM_TYPE_PROJECT, $project->id, null, 0, 'status', UPSTREAM_PERMISSIONS_ACTION_VIEW)): ?>

                                        <td data-column="status"
                                            data-value="<?php echo !empty($status['id']) ? esc_attr($status['id']) : '__none__'; ?>"
                                            data-order="<?php echo $statusOrder > 0 ? $statusOrder : '0'; ?>">
                                            <?php if ($project->status !== null || empty($status['id']) || empty($status['name'])): ?>
                                            <span class="label up-o-label"
                                                style="background-color: <?php echo esc_attr($status['color']); ?>;"><?php echo !empty($status['name']) ? project_status_based_on_user($status['name']) : esc_html($i18n['LB_NONE']); ?></span>
                                            <?php else: ?>
                                            <i class="s-text-color-gray"><?php echo esc_html($i18n['LB_NONE']); ?></i>
                                            <?php endif;?>
                                        </td>
                                        <?php else: ?>
                                        <td data-column="status" data-value="">
                                            <span class="label up-o-label"
                                                style="background-color:#666;color:#fff">(hidden)</span>
                                        </td>
                                        <?php endif;?>

                                        <?php do_action(
    'upstream:project.columns.data',
    $tableSettings,
    $columnsSchema,
    $project->id,
    $project
);?>
                                    </tr>

                                    <?php if (!empty($hiddenColumnsSchema)): ?>
                                    <tr data-parent="<?php echo $project->id; ?>" aria-expanded="false"
                                        style="display: none;">
                                        <td>
                                            <div>
                                                <?php foreach ($hiddenColumnsSchema as $columnName => $column):
    $columnValue = isset($project->{$columnName}) ? $project->{$columnName} : null;
    if (is_null($columnValue)) {
        continue;
    }

    if (is_array($columnValue) && isset($columnValue['value'])) {
        $columnValue = $columnValue['value'];
    }
    ?>
                                                <div class="form-group"
                                                    data-column="<?php echo esc_attr($columnName); ?>">
                                                    <label><?php echo isset($column['label']) ? esc_html($column['label']) : ''; ?></label>
                                                    <?php UpStream\Frontend\renderTableColumnValue(
        $columnName,
        $columnValue,
        $column,
        (array) $project,
        'project',
        $project->id
    );?>
                                                </div>
                                                <?php endforeach;?>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endif;

$isProjectIndexOdd = !$isProjectIndexOdd;
endforeach;?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>


            </div>

            <!-- prject ,team member start -->




            <!-- Latest Customers end -->
        </div>
        <!-- [ Main Content ] end -->
    </div>


    <?php endif;?>

    <?php do_action('upstream:frontend.renderAfterProjectsList');?>

    <input type="hidden" id="project_id" value="<?php echo upstream_post_id(); ?>">

    <?php

do_action('upstream_after_project_list_content');

upstream_get_template_part('global/footer.php');
?>


    <script>
    jQuery(document).ready(() => {
        $('#upstream_new_project_icon').on('click', (e) => {
            let target = $(e.currentTarget);
            if (target.parent().attr('href') == '') {
                e.preventDefault();
            }
        })
    })
    </script>
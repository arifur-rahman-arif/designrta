<?php
if (!defined('ABSPATH')) {
    exit;
}

$pluginOptions     = get_option('upstream_general');
$siteUrl           = get_bloginfo('url');
$pageTitle         = get_bloginfo('name');
$currentUser       = (object)upstream_user_data();
$projectsListUrl   = get_post_type_archive_link('project');
$isSingle          = is_single();
$supportUrl        = upstream_admin_support($pluginOptions);
$logOutUrl         = upstream_logout_url();
$areClientsEnabled = !is_clients_disabled();

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
    'LB_CATEGORIES'     => __('Categories'),
    'LB_PROGRESS'       => __('Progress', 'upstream'),
    'LB_NONE_UCF'       => __('None', 'upstream'),
    'LB_NONE'           => __('none', 'upstream'),
    'LB_COMPLETE'       => __('%s Complete', 'upstream'),
];

if ($isSingle) {
    $areMilestonesDisabledAtAll          = upstream_disable_milestones();
    $areMilestonesDisabledForThisProject = upstream_are_milestones_disabled();
    $areTasksDisabledAtAll               = upstream_disable_tasks();
    $areTasksDisabledForThisProject      = upstream_are_tasks_disabled();
    $areBugsDisabledAtAll                = upstream_disable_bugs();
    $areBugsDisabledForThisProject       = upstream_are_bugs_disabled();
    $areFilesDisabledForThisProject      = upstream_are_files_disabled();
    $areCommentsDisabled                 = upstream_are_comments_disabled();
}

$projects = upstream_user_projects();
?>

<?php do_action('upstream_before_sidebar'); ?>

<nav class="pcoded-navbar menu-light ">
		<div class="navbar-wrapper  ">
			<div class="navbar-content scroll-div " >
				
				<div class="">
					<div class="main-menu-header">
						<div class="img-radius">
							<?php echo get_avatar(get_current_user_id(), 300, null, null, [
								'class' => 'img-radius'
							]); ?>
						</div>
						<div class="user-details">
							<div id="more-details"><?php echo wp_get_current_user()->display_name; ?><i class="fa fa-caret-down"></i></div>
						</div>
					</div>
					<div class="collapse" id="nav-user-link">
						<ul class="list-unstyled">
							<li class="list-group-item">
								<a href="<?php echo esc_url(get_post_type_archive_link('project')); ?>">
                                <i class="feather icon-home"></i><?php echo esc_html(sprintf(
                                    __(' My %s', 'upstream'),
                                    upstream_project_label_plural()
                                )); ?>
								</a>
								<?php echo apply_filters('upstream_additional_nav_content', null); ?>
							</li>

							<li class="list-group-item">
								<a href="<?php echo esc_url(upstream_admin_support($pluginOptions)); ?>">
								<i class="feather icon-settings m-r-5"></i><?php echo esc_html(upstream_admin_support_label($pluginOptions)); ?>
								</a>
							</li>

							<li class="list-group-item"><a href="<?php echo esc_url($logOutUrl); ?>"><i class="feather icon-log-out m-r-5"></i><?php echo esc_attr($i18n['LB_LOGOUT']); ?></a></li>
						</ul>
					</div>
				</div>
				
				<ul class="nav pcoded-inner-navbar ">
					<li class="nav-item pcoded-menu-caption">
					    <label>Navigation</label>
					</li>

					<li class="nav-item">
                        <a href="<?php echo esc_attr($projectsListUrl); ?>" class="nav-link "><span class="pcoded-micon"><i
                                    class="feather icon-home"></i></span><span class="pcoded-mtext">Dashboard</span></a>
                    </li>
                    <?php if( ! $isSingle ):?>
					<li class="nav-item pcoded-hasmenu">
                        <a href="javascript:void(0);" class="nav-link "><span class="pcoded-micon"><i
                                    class="feather icon-layout"></i></span><span
                                class="pcoded-mtext">Projects</span></a>
                        <ul class="pcoded-submenu">
                            <li class="in-progress-c"><a href="javascript:void(0);" >In Production</a></li>
                            <li class="need-approval-c"><a href="javascript:void(0);" >Needs Approval</a></li>
                            <li class="in-revision-c"><a href="javascript:void(0);" >In Revision</a></li>
                            <li class="completed-c"><a href="javascript:void(0);" >Completed</a></li>

                        </ul>
                    </li>
                    <?php endif; ?>
					<?php if ($isSingle && get_post_type() === 'project'): ?>
				<?php $project_id = get_the_ID(); ?>
				
					<li class="nav-item pcoded-hasmenu">
					    <a href="#!" class="nav-link "><span class="pcoded-micon"><i class="feather icon-layout"></i></span><span class="pcoded-mtext"> <?php echo esc_html(get_the_title($project_id)); ?></span></a>
					    <ul class="pcoded-submenu">
							<?php do_action('upstream_sidebar_before_single_menu'); ?>
							<?php if ( ! $areMilestonesDisabledForThisProject && ! $areMilestonesDisabledAtAll): ?>
					        	<li>
									<a href="#milestones"> <?php echo esc_html(upstream_milestone_label_plural()); ?>
                                            <?php
                                            if (function_exists('countItemsForUserOnProject')) {
                                                $itemsCount = countItemsForUserOnProject(
                                                    'milestones',
                                                    get_current_user_id(),
                                                    upstream_post_id()
                                                );
                                            } else {
                                                $itemsCount = (int)upstream_count_assigned_to('milestones');
                                            }

                                            if ($itemsCount > 0): ?>
                                                <span class="label label-info pull-right" data-toggle="tooltip"
                                                      title="<?php esc_html_e('Assigned to me', 'upstream'); ?>"
                                                      style="margin-top: 3px;"><?php echo esc_html($itemsCount); ?></span>
                                            <?php endif; ?></a></li>
							
							<?php endif; ?>

							<?php if ( ! $areTasksDisabledForThisProject && ! $areTasksDisabledAtAll): ?>
					        <li><a href="#tasks"><?php echo esc_html($i18n['LB_TASKS']); ?>
                                            <?php
                                            if (function_exists('countItemsForUserOnProject')) {
                                                $itemsCount = countItemsForUserOnProject(
                                                    'tasks',
                                                    get_current_user_id(),
                                                    upstream_post_id()
                                                );
                                            } else {
                                                $itemsCount = (int)upstream_count_assigned_to('tasks');
                                            }

                                            if ($itemsCount > 0): ?>
                                                <span class="label label-info pull-right" data-toggle="tooltip"
                                                      title="<?php esc_html_e('Assigned to me', 'upstream'); ?>"
                                                      style="margin-top: 3px;"><?php echo esc_html($itemsCount); ?></span>
                                            <?php endif; ?>
                                            <?php do_action('upstream_sidebar_after_tasks_menu'); ?> </a></li>
							
							<?php endif; ?>

							<?php if ( ! $areBugsDisabledAtAll && ! $areBugsDisabledForThisProject): ?>
					        <li><a href="#bugs"><?php echo esc_html($i18n['LB_BUGS']); ?>
                                            <?php
                                            if (function_exists('countItemsForUserOnProject')) {
                                                $itemsCount = countItemsForUserOnProject(
                                                    'bugs',
                                                    get_current_user_id(),
                                                    upstream_post_id()
                                                );
                                            } else {
                                                $itemsCount = (int)upstream_count_assigned_to('bugs');
                                            }

                                            if ($itemsCount > 0): ?>
                                                <span class="label label-info pull-right" data-toggle="tooltip"
                                                      title="<?php esc_html_e('Assigned to me', 'upstream'); ?>"
                                                      style="margin-top: 3px;"><?php echo esc_html($itemsCount); ?></span>
                                            <?php endif; ?>
											<?php do_action('upstream_sidebar_after_bugs_menu'); ?>
										</a>
									</li>
									<?php endif; ?>

									<?php if ( ! $areFilesDisabledForThisProject && ! upstream_disable_files()): ?>
											<li>
												<a href="#files" >
														<?php echo esc_html(upstream_file_label_plural()); ?>
												</a>
											</li>
							
									<?php endif; ?>
									<?php if ( ! $areCommentsDisabled): ?>
											<li>
												<a href="#discussion">
													<?php echo esc_html(upstream_discussion_label()); ?>
												</a>
											</li>
							
									<?php endif; ?>
					    </ul>
					</li>
				
				<?php endif; ?>
				</ul>

                <?php 
            $user = wp_get_current_user();
            if ( in_array( 'upstream_client_user', $user->roles ) ){
        ?>  
            <style>
                form.coupon_code_form {
                    padding: 6px;
                    color: #fff;
                    text-align: center;
                }
                input.input_coupon {
                    padding: 5px 7px;
                    border: 0;
                    margin-top: 5px;
                    margin-bottom: 5px;
                    color: #000;
                }
                input#coupon_code_btn {
                    background: #429edb;
                    border: 0;
                    padding: 5px 30px;
                    margin-top: 6px;
                    font-size: 12px;
                    color: #fff;
                }
                input[type=text]{
                    background: #e2e0e0;
                }
                input[type=text]:focus{
                    background: #e2e0e0;
                }
                .success_coupon_message{
                    display: none; 
                    color: green; 
                    padding-left: 5px; 
                    font-size: 12px;
                }
            </style>
            <!--<form method="post" class="coupon_code_form">-->
            <!--    Coupon Code:<br>-->
            <!--    <input class="input_coupon" type="text" name="coupon_code" placeholder="Enter Coupon Code">-->
            <!--    <input id="coupon_code_btn" data-id="" type="submit" value="Submit">-->
            <!--</form>-->
            <!--<div class="coupon_message" style="color: #fff; padding-left: 5px"></div>-->
            <!--<div class="success_coupon_message">Your Coupon Successfully Added.</div>-->
        <?php } ?>
				
			</div>
		</div>
	</nav>

<?php do_action('upstream_after_sidebar'); ?>
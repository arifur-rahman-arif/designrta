<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!is_archive()) {
    $clientId   = (int)upstream_project_client_id();
    $clientLogo = upstream_client_logo($clientId);
} else {
    $clientLogo = null;
}

$pluginOptions = get_option('upstream_general');

$currentUser = (object)upstream_user_data();
?>
<?php do_action('upstream_before_top_nav'); ?>

<!-- [ Header ] start -->
<header class="navbar pcoded-header navbar-expand-lg navbar-light header-blue">
    <div class="m-header">
        <a class="mobile-menu" id="mobile-collapse" href="#!"><span></span></a>
        <a href="#!" class="b-brand">
            <!-- ========   change your logo hear   ============ -->
            <img src="assets/images/logo.png" alt="" class="logo">
            <img src="assets/images/logo-icon.png" alt="" class="logo-thumb">
        </a>
        <a href="#!" class="mob-toggler">
            <i class="feather icon-more-vertical"></i>
        </a>
    </div>
    <div class="collapse navbar-collapse">
        <ul class="navbar-nav mr-auto">
            <li class="nav-item">
                <a href="#!" class="pop-search"><i class="feather icon-search"></i></a>
                <div class="search-bar">
                    <input type="text" class="form-control border-0 shadow-none" placeholder="Search hear">
                    <button type="button" class="close" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            </li>
        </ul>
        <ul class="navbar-nav ml-auto">

            <!-- Notification layout -->
            <?php $notification_data = notification_data(); ?>

            <li>
                <div class="dropdown">
                    <a class="dropdown-toggle" href="#" data-toggle="dropdown">
                        <i class="icon feather icon-bell"></i>
                        <span class="notification_count" style="font-size: 16px; color: #62ff00; font-weight: bold;"><?php echo $notification_data['notification_count'] ?></span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right notification" style="min-width: 350px;">

                        <div class="noti-head">
                            <h6 class="d-inline-block m-b-0">Notifications</h6>
                            <!-- <div class="float-right">
                                <a href="" id="notification_clear_btn">clear all</a>
                            </div> -->
                        </div>

                        <ul class="noti-body">

                            <?php
                            if ($notification_data['notification_details']) {
                                foreach ($notification_data['notification_details'] as $key => $data) {
                            ?>
                                    <li class="notification" id="<?php echo $data['ID'] ?>">
                                        <div class="media">
                                            <div class="media-body">
                                                <p>
                                                    <strong>Project: <i><?php echo $data['post_title'] ?></i></strong>
                                                    <br>
                                                    <?php echo $data['notification_text'] ?>
                                                </p>
                                            </div>
                                        </div>
                                    </li>
                                <?php
                                }
                            } else {
                                ?>
                                <li class="notification">
                                    <div class="media">
                                        <div class="media-body">
                                            <p>
                                                <strong>No notification found for you.</strong>
                                            </p>
                                        </div>
                                    </div>
                                </li>
                            <?php
                            }
                            ?>

                        </ul>

                    </div>
                </div>
            </li>

            <!-- End of notification -->


            <li>
                <div class="dropdown drp-user">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <i class="feather icon-user"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right profile-notification">
                        <div class="pro-head">
                            <?php echo get_avatar(get_current_user_id(), 300, null, null, [
                                'class' => 'img-radius'
                            ]); ?>
                            <span><?php echo wp_get_current_user()->display_name; ?></span>
                            <a href="<?php echo esc_url($logOutUrl); ?>" class="dud-logout" title="Logout">
                                <i class="feather icon-log-out"></i>
                            </a>
                        </div>
                        <ul class="pro-body">

                            <li><a href="<?php echo esc_url(get_post_type_archive_link('project')); ?>" class="dropdown-item"><i class="feather icon-user"></i><?php echo esc_html(sprintf(
                                                                                                                                                                    __(' My %s', 'upstream'),
                                                                                                                                                                    upstream_project_label_plural()
                                                                                                                                                                )); ?></a></li>

                            <li><a href="<?php echo esc_url(upstream_admin_support($pluginOptions)); ?>" class="dropdown-item"><i class="feather icon-mail"></i> <?php echo esc_html(upstream_admin_support_label($pluginOptions)); ?></a></li>

                            <li><a href="<?php echo esc_url(wp_logout_url()); ?>" class="dropdown-item"><i class="feather icon-log-out"></i> <?php echo  __('Log Out', 'upstream'); ?></a></a></li>

                        </ul>
                    </div>
                </div>
            </li>
        </ul>
    </div>
</header>
<!-- [ Header ] end -->

<?php do_action('upstream_after_top_nav'); ?>


<!-- <script>
    jQuery(document).ready(() => {
        $('#notification_clear_btn').click((e) => {
            e.preventDefault();
        })
    })
</script> -->
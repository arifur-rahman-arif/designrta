<?php

upstream_get_template_part('global/header.php');
upstream_get_template_part('global/sidebar.php');
upstream_get_template_part('global/top-nav.php');
?>

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
                            <li class="breadcrumb-item"><a href="<?php bloginfo('url'); ?>"><i class="feather icon-home"></i></a></li>
                            <li class="breadcrumb-item"><a href="<?php bloginfo('url');
                                                                    echo "/projects"; ?>">Dashboard</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <!-- [ breadcrumb ] end -->
        <!-- [ Main Content ] start -->
        <div class="row">

            <div class="col-sm-12">

                <div class="alerts" id="alert_box">

                </div>

                <div class="card">
                    <div class="card-body">
                        <?php
                        echo do_shortcode('[gravityform id="1" title="false" description="false" ajax="true"]');
                        ?>
                    </div>
                </div>
            </div>
            <!-- Latest Customers end -->
        </div>
        <!-- [ Main Content ] end -->
    </div>

    <?php do_action('upstream:frontend.renderAfterProjectsList'); ?>

    <input type="hidden" id="project_id" value="<?php echo upstream_post_id(); ?>">
    <?php upstream_get_template_part('global/footer.php'); ?>

    <script>
        jQuery(document).ready(function($) {

            $(document).on('click', '#gform_submit_button_1', (function(e) {
                let target = $(e.currentTarget);
                let credit = parseInt($("#field_1_31 > div").text());
                if (credit < 1) {
                    e.preventDefault();
                    target.attr('disabled', true);
                    $('#alert_box').html(`
                        <div class="alert alert-danger" role="alert">
                            You are out of credits. Please use coupon or buy credits.
                        </div>
                    `).hide().slideDown();
                } else {
                    target.attr('disabled', false);
                }
            }));
        });
    </script>
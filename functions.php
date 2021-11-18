<?php

// add js css
add_action('wp_enqueue_scripts', 'astra_enqueue_styles', 99);
function astra_enqueue_styles() {
    // var_dump(get_stylesheet_directory_uri() . '/style.css');
    $parent_style = 'parent-style';
    // Style css
    wp_enqueue_style($parent_style, get_template_directory_uri().'/style.css');
    wp_enqueue_style('child-style', get_stylesheet_directory_uri().'/style.css', array(), time());
    wp_enqueue_style('prism-coy', get_stylesheet_directory_uri().'/assets/css/plugins/prism-coy.css', array(), time());
    wp_enqueue_style('style-css', get_stylesheet_directory_uri().'/assets/css/style.css', array(), time());

    //     // enqueue  Js
    wp_enqueue_script('setting-js', get_stylesheet_directory_uri().'/assets/js/settings.js', ['jquery'], time(), true);
    if (!is_page('add-project')) {
        wp_enqueue_script('vendor-all-upv', get_stylesheet_directory_uri().'/assets/js/vendor-all.min.js', ['jquery'], time(), true);
    }
    wp_enqueue_script('bootstrap.min', get_stylesheet_directory_uri().'/assets/js/plugins/bootstrap.min.js', ['jquery'], time(), true);
    wp_enqueue_script('ripple', get_stylesheet_directory_uri().'/assets/js/ripple.js', ['jquery'], time(), true);
    wp_enqueue_script('pcoded', get_stylesheet_directory_uri().'/assets/js/pcoded.min.js', ['jquery'], time(), true);
    wp_enqueue_script('prism', get_stylesheet_directory_uri().'/assets/js/plugins/prism.js', ['jquery'], time(), true);
    wp_enqueue_script('apexcharts', get_stylesheet_directory_uri().'/assets/js/plugins/apexcharts.min.js', ['jquery'], time(), true);
    wp_enqueue_script('dashboard-main', get_stylesheet_directory_uri().'/assets/js/pages/dashboard-main.js', ['jquery'], time(), true);
    wp_localize_script('dashboard-main', 'filtered_data', array(
        'projects' => filter_datewise_projects()
    ));
}

wp_enqueue_script('vendor-all-uv');

add_filter('upstream_project_post_type_args', 'allow_project_for_gravity_form');

/**
 * @param  $project_args
 * @return mixed
 */
function allow_project_for_gravity_form($project_args) {
    $project_args['public'] = true;

    return $project_args;
}

//add js for upstream frontend
function add_ajax_file_for_upstream_frontend() {
    wp_enqueue_script(
        'ajax-calls',
        get_stylesheet_directory_uri().'/assets/js/ajax-calls.js',
        ['jquery', 'jquery-ui-sortable', 'up-modal', 'admin-bar'],
        time(),
        true
    );
}

add_action('upstream_frontend_enqueue_scripts', 'add_ajax_file_for_upstream_frontend');
// change project status waiting to complete
// change project status waiting to complete
function custom_update_post() {

    $post_id = $_POST['post_id'];
    update_post_meta($post_id, '_upstream_project_status', 'azp39');
    // $post = array(
    //     'post_modified'  => date(),
    //     'post_modified_gmt'   => date(),
    //     'ID'          => $post_id, // $post->ID;
    // );
    // // update post
    // wp_update_post($post);

    $project_title = get_the_title($post_id);
    $current_user_id = get_current_user_id();
    $user_meta = get_userdata($current_user_id);
    $project_client_data = get_user_by('id', $current_user_id);

    // send an email for client
    $client_id = get_post_meta($post_id, '_upstream_project_manager', true);
    $manager_data = get_users(['role__in' => ['upstream_manager']]);
    $manager_email[] = '';
    $manager_number[] = '0000000000';
    foreach ($manager_data as $manager) {
        $managerId = $manager->ID;
        $manager_data = get_user_by('id', $managerId);
        $manager_email[] = $manager_data->user_email;
        $manager_number[] = $manager_data->phone_number;
    }
    $magnager_emails = implode(", ", $manager_email);
    $manager_numbers = implode(", ", $manager_number);

    $user_data = get_user_by('id', $client_id[0]);

    if (function_exists('twl_send_sms')) {
        $args = array(
            'number_to' => $manager_numbers,
            'message'   => $project_title." is approved"
        );
        twl_send_sms($args);
    }

    $to = $magnager_emails;
    $subject = $project_title." is approved";
    $body = 'Your Project is approved by client Email='.$project_client_data->user_email;
    //$headers = array('Content-Type: text/html; charset=UTF-8', 'From: DesignRTA &lt;requests@designrta.com');
    $headers[] = 'Content-Type: text/html; charset=UTF-8';
    $headers = "From: DesignRTA <$user_data->user_email>"."\r\n";

    wp_mail($to, $subject, $body, $headers);

    wp_die();
}

add_action('wp_ajax_custom_update_post', 'custom_update_post');
add_action('wp_ajax_nopriv_custom_update_post', 'custom_update_post');

// wp_mail_from

/**
 * @param $content_type
 */
function design_wp_mail_from($content_type) {
    return 'requests@designrta.com';
}

add_filter('wp_mail_from', 'design_wp_mail_from');

// change status in-progress to ready for approve and send mail for client

// send an email for client

function after_request_ready_for_approve() {
    $post_id = $_POST['post_id'];
    update_post_meta($post_id, '_upstream_project_status', '6ea5e');
    // $post = array(
    //     'post_modified'  => date(),
    //     'post_modified_gmt'   => date(),
    //     'ID'          => $post_id, // $post->ID;
    // );
    // // update post
    // // wp_update_post($post);

    // send an email for client
    $client_id = get_post_meta($post_id, '_upstream_project_client_users', true);

    $user_data = get_user_by('id', $client_id[0]);

    if (function_exists('twl_send_sms')) {
        $args = array(
            'number_to' => $user_data->phone_number,
            'message'   => 'Our designer has requested an approval for your open project. Please approve or request a revision.'
        );
        twl_send_sms($args);
    }

    $to = $user_data->user_email;
    //$to = "richardsetu1@gmail.com, richardsetu@gmail.com";
    $subject = 'Your Project Is Ready For Approval';
    $body = 'Our designer has requested an approval for your open project. Please approve or request a revision.<br><a href="https://www.designrta.com/login">LOGIN</a> to view.';
    //$headers = array('Content-Type: text/html; charset=UTF-8', 'From: DesignRTA &lt;requests@designrta.com');
    $headers[] = 'Content-Type: text/html; charset=UTF-8';
    $headers[] = 'From: DesignRTA <requests@designrta.com>';
    $headers[] = 'Cc: design@kitchen365.com';
    $headers[] = 'Cc: nabil@kitchen365.com';
    $headers[] = 'Cc: robin@hypemill.com';
    $headers[] = 'Cc: designrtawebmaster@gmail.com';

    wp_mail($to, $subject, $body, $headers);
    wp_die();
}

add_action('wp_ajax_after_request_ready_for_approve', 'after_request_ready_for_approve');
add_action('wp_ajax_nopriv_after_request_ready_for_approve', 'after_request_ready_for_approve');

// after add comment change status and send mail to designer and client
function after_add_comment_change_status() {
    $post_id = $_POST['post_id'];
    update_post_meta($post_id, '_upstream_project_status', 'wfyaa');

    // $post = array(
    //     'post_modified'  => date(),
    //     'post_modified_gmt'   => date(),
    //     'ID'          => $post_id, // $post->ID;
    // );
    // // update post
    // wp_update_post($post);

    $project_title = get_the_title($post_id);
    $current_user_id = get_current_user_id();
    $user_meta = get_userdata($current_user_id);
    $user_roles = $user_meta->roles[0];

    // client_id
    $project_client_id = get_post_meta($post_id, '_upstream_project_client_users', true);
    $project_client_data = get_user_by('id', $project_client_id);

    if ($user_roles == 'upstream_client_user') {
        $to = "nabil@kitchen365.com";
        $body = 'A client has added a revision to a project. Please <a href="https://www.designrta.com/login">Login</a> to view and edit.';
    } else {
        $to = "$project_client_data->user_email";
        $body = 'A Designer has added a comment to a project. Please <a href="https://www.designrta.com/login">Login</a> to view and edit.';
    }

    $subject = 'New Comment Added on "'.$project_title.'"';
    $headers[] = 'Content-Type: text/html; charset=UTF-8';
    $headers[] = 'From: DesignRTA <requests@designrta.com>';
    $headers[] = 'Cc: design@kitchen365.com';
    $headers[] = 'Cc: nabil@kitchen365.com';
    $headers[] = 'Cc: robin@hypemill.com';
    $headers[] = 'Cc: designrtawebmaster@gmail.com';

    wp_mail($to, $subject, $body, $headers);
    wp_die();
}

add_action('wp_ajax_after_add_comment_change_status', 'after_add_comment_change_status');
add_action('wp_ajax_nopriv_after_add_comment_change_status', 'after_add_comment_change_status');

// after add file change status and send mail to designer and client
function after_file_submit_form_change_status() {
    $post_id = $_POST['post_id'];
    update_post_meta($post_id, '_upstream_project_status', 'cerwk');

    // $post = array(
    //     'post_modified'  => date(),
    //     'post_modified_gmt'   => date(),
    //     'ID'          => $post_id, // $post->ID;
    // );
    // // update post
    // wp_update_post($post);

    $project_title = get_the_title($post_id);
    $current_user_id = get_current_user_id();
    $user_meta = get_userdata($current_user_id);
    $user_roles = $user_meta->roles[0];

    // client_id
    $project_client_id = get_post_meta($post_id, '_upstream_project_client_users', true);
    $project_client_data = get_user_by('id', $project_client_id);

    if ($user_roles == 'upstream_client_user') {
        $to = "nabil@kitchen365.com";
        $body = 'A client has added a revision to a project. Please <a href="https://www.designrta.com/login">Login</a> to view and edit.';
    } else {
        $to = $project_client_data->user_email;
        $body = 'A Designer has added a file to a project. Please <a href="https://www.designrta.com/login">Login</a> to view and edit.';
    }

    $subject = 'New File Added on "'.$project_title.'"';
    $headers[] = 'Content-Type: text/html; charset=UTF-8';
    $headers[] = 'From: DesignRTA <requests@designrta.com>';
    $headers[] = 'Cc: design@kitchen365.com';
    $headers[] = 'Cc: nabil@kitchen365.com';
    $headers[] = 'Cc: robin@hypemill.com';
    $headers[] = 'Cc: designrtawebmaster@gmail.com';

    wp_mail($to, $subject, $body, $headers);
    wp_die();
}

add_action('wp_ajax_after_file_submit_form_change_status', 'after_file_submit_form_change_status');
add_action('wp_ajax_nopriv_after_file_submit_form_change_status', 'after_file_submit_form_change_status');

// create a zip file for download button

function create_zip_file_for_download() {
    $last_file = get_option('designrta_last_download_file');
    if (file_exists($last_file)) {
        unlink($last_file);
    }

    $file_name = time().'_all-files-downloads.zip';

    $post_id = $_POST['post_id'];
    # define file array
    $all_files = get_post_meta($post_id, '_upstream_project_files', true);

    $files = wp_list_pluck($all_files, 'file');

    if (empty($files)) {
        echo 'NO FILES';
        die();
    }

    # create new zip object
    $zip = new ZipArchive();

    # create a temp file & open it

    $zip->open($file_name, ZipArchive::CREATE);

    # loop through each file
    foreach ($files as $file) {
        # download file
        $download_file = file_get_contents($file);

        #add it to the zip
        $zip->addFromString(basename($file), $download_file);
    }

    # close zip
    $zip->close();

    update_option('designrta_last_download_file', $file_name);

    echo admin_url($file_name);
    die();
}

add_action('wp_ajax_create_zip_file_for_download', 'create_zip_file_for_download');
add_action('wp_ajax_nopriv_create_zip_file_for_download', 'create_zip_file_for_download');

// after create a new post change post status
function after_create_post_change_status() {
    global $wpdb;
    $lastrowId = $wpdb->get_col("SELECT ID FROM wp_posts where post_type='project' ORDER BY post_date DESC ");

    $lastPropertyId = $lastrowId[0];
    update_post_meta($lastPropertyId, '_upstream_project_status', 'gpaz9');

    $posttime = get_the_time('Y-m-d H:i:s', $lastPropertyId);
    $project_start = strtotime($posttime);
    update_post_meta($lastPropertyId, '_upstream_project_start', $project_start);

    $project_end = strtotime($posttime.' +2 day');
    update_post_meta($lastPropertyId, '_upstream_project_end', $project_end);

    update_post_meta($lastPropertyId, '_upstream_project_client_users', get_current_user_id());

    // auto select user
    $args = array(
        'post_type'   => array('client'),
        'post_status' => array('publish')
    );
    // The Query
    $query = new WP_Query($args);
    // The Loop
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();

            $client_user = get_post_meta(get_the_ID(), '_upstream_new_client_users', true);
            $client_user_id = $client_user['0']["user_id"];
            if ($client_user_id == get_current_user_id()) {
                update_post_meta($lastPropertyId, '_upstream_project_client', get_the_ID());
            }
        }
    }
    // Restore original Post Data
    wp_reset_postdata();

    wp_die();
}

add_action('wp_ajax_after_create_post_change_status', 'after_create_post_change_status');
add_action('wp_ajax_nopriv_after_create_post_change_status', 'after_create_post_change_status');

/*
 * Create new custom post type post on new user registration
 */
add_action('user_register', 'create_new_post_after_register_new_user', 10, 1);
/**
 * @param $user_id
 */
function create_new_post_after_register_new_user($user_id) {
    // Get user info
    $user_info = get_userdata($user_id);

    // Create a new post
    $user_post = array(
        'post_title'   => $user_info->display_name,
        'post_content' => $user_info->description,
        'post_type'    => 'client',
        'post_status'  => 'publish'
    );

    // Insert the post into the database
    $post_id = wp_insert_post($user_post);

    $client_users = [];

    $client_users[] = [
        'user_id'     => $user_id,
        'assigned_by' => $user_id,
        'assigned_at' => date('Y-m-d')
    ];

    update_post_meta($post_id, '_upstream_new_client_users', $client_users);
    update_user_meta($user_id, '_upstream_client_credits', 2);
}

// adding credits for client
/**
 * @param $entry
 * @param $form
 */
function after_submission(
    $entry,
    $form
) {
    $credits = intval(get_user_meta(get_current_user_id(), '_upstream_client_credits', true));
    $add_credit = rgar($entry, '29');
    if (isset($add_credit)) {
        $credits += intval($add_credit);
    }
    $credits -= 1;
    update_user_meta(get_current_user_id(), '_upstream_client_credits', $credits);

    // multi files create a zip file for download

    $multi_files = array();
    $multi_files = $entry["34"]; //The field ID of the multi upload
    $multi_files = stripslashes($multi_files);
    $post_id = $entry['post_id'];

    $file_name = $post_id.'_all-design-files.zip';

    $files = json_decode($multi_files, true);

    if (empty($files)) {
        echo 'NO FILES';
        die();
    }

    # create new zip object
    $zip = new ZipArchive();

    # create a temp file & open it

    $zip->open($file_name, ZipArchive::CREATE);

    # loop through each file
    foreach ($files as $file) {
        # download file
        $download_file = file_get_contents($file);

        #add it to the zip
        $zip->addFromString(basename($file), $download_file);
    }

    # close zip
    $zip->close();

    update_post_meta($post_id, 'upload_file_url', site_url($file_name));
}

add_action('gform_after_submission', 'after_submission', 10, 2);

add_action("wp_print_footer_scripts", function () {

    ?>
<script>
(function($) {

    $(document).ready(function() {

        $(document).on('click', 'label#label_1_30_1', function() {
            $("input#input_1_31").replaceWith(`<?php
$avaible_credits = 0;
    $avaible_credits += intval(get_user_meta(get_current_user_id(), '_upstream_client_credits', true));
    if ($avaible_credits >= 0) {
        echo $avaible_credits;
    } else {
        echo "0";
    }
    ?>`)
        });

    });

})(jQuery);
</script>
<?php
});

// add user meta for user credits

/**
 * @param $user
 */
function drta_usermeta_form_field__upstream_client_credits($user) {
    ?>
<h3>User Credits</h3>
<table class="form-table">
    <tr>
        <th>
            <label for="_upstream_client_credits">User add or remove credits</label>
        </th>
        <td>
            <input type="number" class="regular-text" id="_upstream_client_credits" name="_upstream_client_credits"
                value="<?php echo esc_attr(get_user_meta($user->ID, '_upstream_client_credits', true)); ?>" required>
            <p class="description">User Credits</p>
        </td>
    </tr>
</table>
<h3>User Phone Number</h3>
<table class="form-table">
    <tr>
        <th>
            <label for="phone_number">Add or change user phone number</label>
        </th>
        <td>
            <input type="text" class="regular-text" id="phone_number" name="phone_number"
                value="<?php echo esc_attr(get_user_meta($user->ID, 'phone_number', true)); ?>" required>
            <p class="description">Country code + 10-digit phone number (i.e. +16175551212)</p>
        </td>
    </tr>
</table>
<?php
}

// add the field to user's own profile editing screen
add_action('edit_user_profile', 'drta_usermeta_form_field__upstream_client_credits');

// add the field to user profile editing screen
add_action('show_user_profile', 'drta_usermeta_form_field__upstream_client_credits');
/**
 * The save action.
 */
function drta_usermeta_form_field__upstream_client_credits_update($user_id) {
    // check that the current user have the capability to edit the $user_id
    if (!current_user_can('edit_user', $user_id)) {
        return false;
    }

    // create/update user meta for the $user_id
    return update_user_meta(
        $user_id,
        '_upstream_client_credits',
        $_POST['_upstream_client_credits']
    );
}

// add the save action to user's own profile editing screen update
add_action('personal_options_update', 'drta_usermeta_form_field__upstream_client_credits_update');

// add the save action to user profile editing screen update
add_action('edit_user_profile_update', 'drta_usermeta_form_field__upstream_client_credits_update');

/**
 * The save action.
 */
function user_phone_number_update($user_id) {
    // check that the current user have the capability to edit the $user_id
    if (!current_user_can('edit_user', $user_id)) {
        return false;
    }

    // create/update user meta for the $user_id
    return update_user_meta(
        $user_id,
        'phone_number',
        $_POST['phone_number']
    );
}

// add the save action to user's own profile editing screen update
add_action('personal_options_update', 'user_phone_number_update');

// add the save action to user profile editing screen update
add_action('edit_user_profile_update', 'user_phone_number_update');

// Register Custom Post Type Coupon
function create_coupon_cpt() {

    $labels = array(
        'name'                  => _x('Coupons', 'Post Type General Name', 'designrta'),
        'singular_name'         => _x('Coupon', 'Post Type Singular Name', 'designrta'),
        'menu_name'             => _x('DesignRTA Coupons', 'Admin Menu text', 'designrta'),
        'name_admin_bar'        => _x('Coupon', 'Add New on Toolbar', 'designrta'),
        'archives'              => __('Coupon Archives', 'designrta'),
        'attributes'            => __('Coupon Attributes', 'designrta'),
        'parent_item_colon'     => __('Parent Coupon:', 'designrta'),
        'all_items'             => __('All Coupons', 'designrta'),
        'add_new_item'          => __('Add New Coupon', 'designrta'),
        'add_new'               => __('Add New', 'designrta'),
        'new_item'              => __('New Coupon', 'designrta'),
        'edit_item'             => __('Edit Coupon', 'designrta'),
        'update_item'           => __('Update Coupon', 'designrta'),
        'view_item'             => __('View Coupon', 'designrta'),
        'view_items'            => __('View Coupons', 'designrta'),
        'search_items'          => __('Search Coupon', 'designrta'),
        'not_found'             => __('Not found', 'designrta'),
        'not_found_in_trash'    => __('Not found in Trash', 'designrta'),
        'featured_image'        => __('Featured Image', 'designrta'),
        'set_featured_image'    => __('Set featured image', 'designrta'),
        'remove_featured_image' => __('Remove featured image', 'designrta'),
        'use_featured_image'    => __('Use as featured image', 'designrta'),
        'insert_into_item'      => __('Insert into Coupon', 'designrta'),
        'uploaded_to_this_item' => __('Uploaded to this Coupon', 'designrta'),
        'items_list'            => __('Coupons list', 'designrta'),
        'items_list_navigation' => __('Coupons list navigation', 'designrta'),
        'filter_items_list'     => __('Filter Coupons list', 'designrta')
    );
    $args = array(
        'label'               => __('Coupon', 'designrta'),
        'description'         => __('', 'designrta'),
        'labels'              => $labels,
        'menu_icon'           => 'dashicons-admin-network',
        'supports'            => array('title'),
        'taxonomies'          => array(),
        'public'              => true,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'menu_position'       => 5,
        'show_in_admin_bar'   => true,
        'show_in_nav_menus'   => true,
        'can_export'          => true,
        'has_archive'         => true,
        'hierarchical'        => false,
        'exclude_from_search' => false,
        'show_in_rest'        => true,
        'publicly_queryable'  => true,
        'capability_type'     => 'post'
    );
    register_post_type('coupon', $args);
    update_post_meta('1591', '_upstream_project_client_users', '65');
}

add_action('init', 'create_coupon_cpt', 0);

function designrta_coupon_code_add() {
    $coupon_code = $_POST["coupon_code"];
    // coupon code
    global $wpdb;
    $sql = "SELECT DISTINCT meta_value FROM {$wpdb->postmeta} WHERE `meta_key` = 'coupon_code' limit 0, 9999999999999";
    $allcouponcode = array_filter($wpdb->get_col($sql));

    if (in_array($coupon_code, $allcouponcode)) {
        $args = array(
            'post_type'      => 'coupon',
            'meta_key'       => 'coupon_code',
            'meta_query'     => array(
                array(
                    'key'   => 'coupon_code',
                    'value' => $coupon_code
                )
            ),
            'fields'         => 'ids',
            'posts_per_page' => 1
        );

        $lists = get_posts($args);
        $post_id = $lists[0];
        $coupon_credits = get_post_meta($post_id, 'coupon_credits', true);

        $allcredits = intval(get_user_meta(get_current_user_id(), '_upstream_client_credits', true));
        if (isset($coupon_credits)) {
            $allcredits += intval($coupon_credits);
        }
        update_user_meta(get_current_user_id(), '_upstream_client_credits', $allcredits);
    }
}

add_action('wp_ajax_designrta_coupon_code_add', 'designrta_coupon_code_add');

/**
 * @param  $mimes
 * @return mixed
 */
function my_custom_mime_types($mimes) {

    $mimes['kit'] = 'application/octet-stream';
    return $mimes;
}

add_filter('upload_mimes', 'my_custom_mime_types');

require_once get_theme_file_path().'/AR_Arif/functions.php';
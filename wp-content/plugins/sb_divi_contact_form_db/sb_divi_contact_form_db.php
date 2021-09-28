<?php

/*
 * Plugin Name: Divi Contact Form DB
 * Plugin URI:  https://webacetechs.in/
 * Description: A simple plugin to save contact form submissions in the database
 * Author:      Web-Ace Tech Services
 * Version:     1.9.1
 * Author URI:  https://webacetechs.in/
 */

//constants
define('SB_DIVI_DB_VERSION', '1.9.1');
define('SB_DIVI_DB_STORE_URL', 'https://elegantmarketplace.com');
define('SB_DIVI_DB_ITEM_NAME', 'Divi Form DB');
define('SB_DIVI_DB_AUTHOR_NAME', 'Web-Ace');
define('SB_DIVI_DB_ITEM_ID', 172927);
define('SB_DIVI_DB_FILE', __FILE__);

require_once('includes/emp-licensing.php');

add_action('plugins_loaded', 'sb_divi_cfd_init');

function sb_divi_cfd_init() {
    load_plugin_textdomain('divi-db', false, dirname(plugin_basename(__FILE__)) . '/lang/');

    add_action('admin_enqueue_scripts', 'sb_divi_cfd_css_enqueue', 9999);

    add_filter('et_contact_page_headers', 'sb_divi_cfd_et_contact_page_headers', 10, 10);
    add_action('add_meta_boxes', 'sb_divi_cfd_register_meta_box');
    add_action('init', 'sb_divi_cfd_pt_init');
    add_action('admin_notices', 'sb_divi_cfd_admin_notice');
    add_action('admin_head', 'sb_divi_cfd_admin_head');
    add_action('admin_init', 'sb_divi_cfd_download_csv', 1, 1);
    add_action('admin_menu', 'sb_divi_cfd_submenu');

    add_filter('manage_divi_cf_db_posts_columns', 'sb_divi_cfd_columns_head', 100);
    add_action('manage_divi_cf_db_posts_custom_column', 'sb_divi_cfd_columns_content', 100, 2);

}

function sb_divi_cfd_submenu() {
    add_submenu_page(
        'edit.php?post_type=divi_cf_db',
        __('Export', 'divi-db'),
        __('Export', 'divi-db'),
        'manage_options',
        'sb_divi_cfd',
        'sb_divi_cfd_submenu_cb'
    );

    add_submenu_page(
        'edit.php?post_type=divi_cf_db',
        __('Settings', 'divi-db'),
        __('Settings', 'divi-db'),
        'manage_options',
        'sb_divi_cfd_settings',
        'sb_divi_cfd_settings_submenu_cb'
    );

    add_submenu_page(
        'edit.php?post_type=divi_cf_db',
        __('Licensing', 'divi-db'),
        __('Licensing', 'divi-db'),
        'manage_options',
        'sb_divi_cfd_license',
        'sb_divi_cfd_license_submenu_cb'
    );
}

function sb_divi_cfd_license_submenu_cb() {

    echo '<div class="wrap"><div id="icon-tools" class="icon32"></div>';
    echo '<h2>' . SB_DIVI_DB_ITEM_NAME . ' - ' . __('Version', 'divi-db') . ' ' . SB_DIVI_DB_VERSION . '</h2>';

    echo '<div id="poststuff">';

    echo '<div id="post-body" class="metabox-holder columns-2">';

    echo '<form method="POST">';

    sb_divi_db_license_page();

    echo '</form>';

    echo '</div>';
    echo '</div>';

    echo '</div>';
}

function sb_divi_cfd_box_start($title) {
    return '<div class="postbox">
                    <h2 class="hndle">' . $title . '</h2>
                    <div class="inside">';
}

function sb_divi_cfd_download_csv() {

    if (isset($_REQUEST['download_csv'])) {
        if (isset($_REQUEST['form_name'])) {
            if ($rows = sb_divi_cfd_get_export_rows($_REQUEST['form_name'])) {

                header('Content-Type: application/csv');
                header('Content-Disposition: attachment; filename=' . sanitize_title($_REQUEST['form_name']) . '.csv');
                header('Pragma: no-cache');
                echo implode("\n", $rows);
                die;
            }
        }
    }
}

function sb_divi_cfd_box_end() {
    return '    </div>
                </div>';
}

function sb_divi_cfd_submenu_cb() {

    echo '<div class="wrap"><div id="icon-tools" class="icon32"></div>';
    echo '<h2>' . SB_DIVI_DB_ITEM_NAME . ' - ' . __('Version', 'divi-db') . ' ' . SB_DIVI_DB_VERSION . '</h2>';

    echo '<div id="poststuff">';

    echo '<div id="post-body" class="metabox-holder columns-2">';

    echo sb_divi_cfd_box_start(__('Export Results', 'divi-db'));

    echo '<p>' . __('Use this simple form to export your contact data to CSV file. This is fairly crude but we don\'t have names for forms but we do have the page it was submitted from.. often the page the form is on. The plugin only supports the first form on each page anyway so you vcan generally assume that, for example, the form called "Contact" is your contact form and the one called "Work with me" would be your enquiry form.', 'divi-db') . '</p>';

    if ($posts = get_posts('post_type=divi_cf_db&posts_per_page=-1')) {
        $forms = array();
        foreach ($posts as $post) {
            if ($data = get_post_meta($post->ID, 'sb_divi_cfd', true)) {
                $forms[$data['extra']['submitted_on']] = $data['extra']['submitted_on'];
            }
        }

        echo '<form method="POST">';
        echo '<h3>' . __('Select a form to export', 'divi-db') . ':</h3>';
        echo '<select  style="margin-right: 10px; width: 200px;" name="form_name">';

		    $alpha_forms = array();
		    foreach ($forms as $form) {
			    $alpha_forms[sanitize_title($form)] = $form;
		    }

		    ksort($alpha_forms);
		    foreach ($alpha_forms as $form=>$form_label) {
			    echo '<option ' . (isset($_REQUEST['form_name']) && $_REQUEST['form_name'] == $form ? 'selected="selected"' : '') . ' value="' . $form . '">' . $form_label . '</option>';
		    }

        echo '</select>';
        echo '<input type="submit" name="" class="button-primary" value="' . __('Export Form', 'divi-db') . '" />';
        echo '</form>';

        if (isset($_REQUEST['form_name'])) {

            $rows = sb_divi_cfd_get_export_rows($_REQUEST['form_name']);

            echo '<h3>' . __('CSV Content', 'divi-db') . '</h3>';
            echo '<div style="margin-top: 20px; min-height: 150px; max-height: 350px; overflow: scroll; margin-bottom: 10px; border: 1px solid #EEE; padding: 20px;">' . implode('<br />', $rows) . '</div>';

            echo '<form method="POST">';
            echo '<input type="hidden" name="form_name" value="' . $_REQUEST['form_name'] . '" />';
            echo '<input type="submit" name="download_csv" class="button-primary" value="' . __('Download CSV File', 'divi-db') . '" />';
            echo '</form>';
        }
    } else {
        echo '<p>' . __('This page will show a form when you have at least one submission. Until then, enjoy this picture of a cat!', 'divi-db') . '</p>';
        echo '<img src="http://placekitten.com/g/500/500" />';
    }

    echo sb_divi_cfd_box_end();

    echo '</div>';

    echo '</div>';
    echo '</div>';
}

function sb_divi_cfd_settings_submenu_cb() {

    echo '<div class="wrap"><div id="icon-tools" class="icon32"></div>';
    echo '<h2>' . SB_DIVI_DB_ITEM_NAME . ' - ' . __('Version', 'divi-db') . ' ' . SB_DIVI_DB_VERSION . '</h2>';

    echo '<div id="poststuff">';

    echo '<div id="post-body" class="metabox-holder columns-2">';

    if (isset($_POST['sb_divi_cfd_save'])) {
        //echo 'updating';
        update_option('sb_divi_cfd', @$_POST['sb_divi_cfd']);

        echo '<div id="message" class="updated fade"><p>' . __('Settings saved successfully', 'divi-db') . '</p></div>';
    }

    $sb_divi_cfd = get_option('sb_divi_cfd');

    echo sb_divi_cfd_box_start(__('Settings', 'divi-db'));

    echo '<p>' . __('This simple form will provide some handy switches and settings for the plugin.', 'divi-db') . '</p>';

    echo '<form method="POST">';

    echo '<p>
                <label><input type="checkbox" name="sb_divi_cfd[disable_admin_nag]" ' . checked(1, (isset($sb_divi_cfd['disable_admin_nag']) ? 1 : 0), false) . ' value="1" /> ' . __('Disable Admin Nag?', 'divi-db') . '</label>
                <br /><small>' . __('The admin nag is the red box that shows at the top of your admin pages when there is a contact submission to review. If you would prefer to use the plugin as a backup only then just check this box to turn the nag off.', 'divi-db') . '</small>
            </p>';

    echo '<input type="submit" name="sb_divi_cfd_save" class="button-primary" value="' . __('Save Settings', 'divi-db') . '" />';
    echo '</form>';

    echo sb_divi_cfd_box_end();

    echo '</div>';

    echo '</div>';
    echo '</div>';
}

function sb_divi_cfd_get_export_rows($form_name) {
		$form_name = sanitize_title($form_name);
    $rows = array();

    if ($posts = get_posts('post_type=divi_cf_db&posts_per_page=-1')) {
        $row = '';
        $row .= '"' . __('Date', 'divi-db') . '","' . __('Submitted On', 'divi-db') . '","' . __('Submitted By', 'divi-db') . '",';

        foreach ($posts as $post) {
            if ($data = get_post_meta($post->ID, 'sb_divi_cfd', true)) {
                if (sanitize_title($data['extra']['submitted_on']) == $form_name) {
                    foreach ($data['data'] as $field) {
                        $row .= '"' . $field['label'] . '",';
                    }
                    break; //looking for the first instance of this form.
                }
            }
        }

        $rows[] = rtrim($row, ',');

        foreach ($posts as $post) {
            if ($data = get_post_meta($post->ID, 'sb_divi_cfd', true)) {
                if (sanitize_title($data['extra']['submitted_on']) == $form_name) {
                    $row = '';
                    $row .= '"' . $post->post_date . '","' . $data['extra']['submitted_on'] . '","' . $data['extra']['submitted_by'] . '",';

                    foreach ($data['data'] as $field) {
                        $row .= '"' . addslashes($field['value']) . '",';
                    }

                    $rows[] = rtrim($row, ',');
                }
            }
        }
    }

    return $rows;
}

function sb_divi_cfd_css_enqueue() {
    global $current_screen;

    if ($current_screen->id == 'divi_cf_db') {
        wp_enqueue_script('sb_divi_cfd_js', plugins_url('/script.js', __FILE__));
    }
}

function sb_divi_cfd_columns_head($defaults) {
    unset($defaults['date']);
    //unset( $defaults['cb'] );
    unset($defaults['title']);

    $defaults['cf_divi_title'] = __('View', 'divi-db');
    $defaults['email'] = __('Email', 'divi-db');
    $defaults['read'] = __('Read/Unread', 'divi-db');
    $defaults['cloned'] = __('Cloned', 'divi-db');
    $defaults['sub_on'] = __('Submitted On', 'divi-db');
    $defaults['sub_date'] = __('Submission Date', 'divi-db');

    return $defaults;
}

// SHOW THE FEATURED IMAGE
function sb_divi_cfd_columns_content($column_name, $post_id) {
    $contact = get_post($post_id);
    $data = get_post_meta($post_id, 'sb_divi_cfd', true);

    if ($column_name == 'cf_divi_title') {
        echo '<a href="' . admin_url('post.php?action=edit&post=' . $post_id) . '">' . __('View Submission', 'divi-db') . '</a>';
    } else if ($column_name == 'read') {
        if ($read = get_post_meta($post_id, 'sb_divi_cfd_read', true)) {
            echo '<span style="color: green;">' . $read['by_name'] . '<br />' . date('Y-m-d H:i', $read['on']) . '</span>';
        } else {
            echo '<span class="dashicons dashicons-email-alt"></span>';
        }
    } else if ($column_name == 'sub_on') {
        if ($data['extra']['submitted_on']) {
            echo '<a href="' . get_permalink($data['extra']['submitted_on_id']) . '">' . $data['extra']['submitted_on'] . '</a>';
        }
    } else if ($column_name == 'sub_date') {
        echo $contact->post_date;
    } else if ($column_name == 'cloned') {
        if ($cloned = get_post_meta($post_id, 'sb_divi_cfd_cloned', true)) {
            $cloned_count = count($cloned);

            echo '<span class="dashicons dashicons-yes"></span> (' . $cloned_count . ')';
        } else {
            echo '<span class="dashicons dashicons-no-alt"></span>';
        }
    } else if ($column_name == 'email') {
        if ($email = get_post_meta($post_id, 'sb_divi_cfd_email', true)) {
            $email = '<a href="mailto:' . $email . '" target="_blank">' . $email . '</a>';
        } else {
            $email = '-';
        }
        echo $email;
    }
}

function sb_divi_cfd_admin_head() {
    global $current_user;

    if (isset($_GET['sb-action'])) {
        $action = $_GET['sb-action'];

        if ($action == 'mark-all-read') {
            $args = array(
                'posts_per_page' => -1,
                'meta_key'       => 'sb_divi_cfd_read',
                'meta_value'     => 0,
                'post_type'      => 'divi_cf_db',
                'post_status'    => 'publish',
            );

            if ($other_contacts = get_posts($args)) {
                foreach ($other_contacts as $other_contact) {
                    $read = array(
                        'by_name' => $current_user->display_name,
                        'by'      => $current_user->ID,
                        'on'      => time()
                    );
                    update_post_meta($other_contact->ID, 'sb_divi_cfd_read', $read);
                }
            }
        }
    }

    // Hide link on listing page
    if ((isset($_GET['post_type']) && $_GET['post_type'] == 'divi_cf_db') || (isset($_GET['post']) && get_post_type($_GET['post']) == 'divi_cf_db')) {
        echo '<style type="text/css">
	    .page-title-action, #favorite-actions, .add-new-h2 { display:none; }
	    </style>';
    }

    echo '<style>#menu-posts-divi_cf_db .wp-submenu li:nth-child(3) { display:none; }</style>';

}

function sb_divi_cfd_admin_notice() {
    if (!current_user_can('administrator')) {
        return;
    }

    if ($sb_divi_cfd = get_option('sb_divi_cfd')) {
        if ($sb_divi_cfd['disable_admin_nag']) {
            return;
        }
    }

    $args = array(
        'posts_per_page' => -1,
        'meta_key'       => 'sb_divi_cfd_read',
        'meta_value'     => 0,
        'post_type'      => 'divi_cf_db',
        'post_status'    => 'publish',
    );

    if ($other_contacts = get_posts($args)) {
        //Use notice-warning for a yellow/orange, and notice-info for a blue left border.
        $class = 'notice notice-error is-dismissible';
        $message = __('You have', 'divi-db') . ' ' . count($other_contacts) . ' ' . __('unread contact form submissions.', 'divi-db') . ' ' . __('Click', 'divi-db') . ' <a href="' . admin_url('edit.php?post_type=divi_cf_db') . '">' . __('here', 'divi-db') . '</a> ' . __('to visit them or click', 'divi-db') . ' <a href="' . admin_url('edit.php?post_type=divi_cf_db&sb-action=mark-all-read') . '">' . __('here', 'divi-db') . '</a> ' . __('to mark all as read', 'divi-db');

        printf('<div class="%1$s"><p>%2$s</p></div>', $class, $message);
    }
}

function sb_divi_cfd_register_meta_box() {
    add_meta_box('sb_divi_cfd', __('Form Submission', 'divi-db'), 'sb_divi_cfd_meta_box_callback', 'divi_cf_db', 'normal', 'high');
    add_meta_box('sb_divi_cfd_extra', __('Extra Information', 'divi-db'), 'sb_divi_cfd_meta_box_callback_extra', 'divi_cf_db', 'normal', 'high');
    add_meta_box('sb_divi_cfd_actions', __('Actions', 'divi-db'), 'sb_divi_cfd_meta_box_callback_actions', 'divi_cf_db', 'normal', 'high');
    //add_meta_box( 'sb_divi_cfd_debug', __( 'Debug/Server Info', 'divi-db' ), 'sb_divi_cfd_meta_box_callback_debug', 'divi_cf_db', 'normal', 'high' );
}

function sb_divi_cfd_meta_box_callback() {
    global $current_user;

    $submission = get_post(get_the_ID());

    if (!$read = get_post_meta(get_the_ID(), 'sb_divi_cfd_read', true)) {
        $read = array('by_name' => $current_user->display_name, 'by' => $current_user->ID, 'on' => time());
        update_post_meta(get_the_ID(), 'sb_divi_cfd_read', $read);
    }

    $class = 'notice notice-info';
    $message = __('First read by', 'divi-db') . ' ' . $read['by_name'] . ' ' . __('at', 'divi-db') . ' ' . date('Y-m-d H:i', $read['on']);
    printf('<div class="%1$s"><p>%2$s</p></div>', $class, $message);

    if ($data = get_post_meta(get_the_ID(), 'sb_divi_cfd', true)) {

        if ($fields = $data['data']) {
            echo '<table class="widefat">
                        <thead>
                        <tr>
                            <th>' . __('Label', 'divi-db') . '</th>
                            <th>' . __('Value', 'divi-db') . '</th>
                        </tr>
                        </thead>
                        <tbody>';

            foreach ($fields as $field) {
                $value = $field['value'];

                if (is_email($value)) {
                    $value = '<a href="mailto:' . $value . '" target="_blank">' . $value . '</a>';
                }

                echo '<tr>
                            <td><strong>' . $field['label'] . '</strong></td>
                            <td>' . wpautop($value) . '</td>
                        </tr>';
            }

            echo '<tr>
                            <td><strong>' . __('Date of Submission', 'divi-db') . '</strong></td>
                            <td>' . $submission->post_date . '</td>
                        </tr>';

            echo '</tbody>
                </table>';
        }
    }

}

function sb_divi_cfd_meta_box_callback_extra() {
    $other_submissions = '';

    if ($data = get_post_meta(get_the_ID(), 'sb_divi_cfd', true)) {
        if ($extra = $data['extra']) {
            echo '<table class="widefat">
                        <thead>
                        <tr>
                            <th>Label</th>
                            <th>Value</th>
                        </tr>
                        </thead>
                        <tbody>';

            foreach ($extra as $key => $value) {

                switch ($key) {
                    case 'submitted_on_id':
                    case 'submitted_by_id':
                        continue(2); //we don't really care about these ones
                        break;
                    case 'submitted_on':
                        if ($extra['submitted_on_id']) {
                            $value = $value . ' (<a href="' . get_permalink($extra['submitted_on_id']) . '" target="_blank">' . __('View Page', 'divi-db') . '</a> | <a href="' . admin_url('post.php?action=edit&post=' . $extra['submitted_on_id']) . '" target="_blank">' . __('Edit Page', 'divi-db') . '</a>)';
                        } else {
                            $value = '<em>' . __('Unknown', 'divi-db') . '</em>';
                        }
                        break;
                    case 'submitted_by':
                        if ($extra['submitted_by_id']) {
                            $value = $value . ' (<a href="' . admin_url('user-edit.php?user_id=' . $extra['submitted_by_id']) . '" target="_blank">' . __('View User Profiile', 'divi-db') . '</a>';

                            $args = array(
                                'posts_per_page' => -1,
                                'meta_key'       => 'sb_divi_cfd_submitted_by',
                                'meta_value'     => $extra['submitted_by_id'],
                                'post_type'      => 'divi_cf_db',
                                'post_status'    => 'publish',
                            );

                            if ($other_contacts = get_posts($args)) {
                                $value .= ' | <a style="cursor: pointer;" onclick="jQuery(\'.other_submissions\').slideToggle();">' . __('View', 'divi-db') . ' ' . count($other_contacts) . ' ' . __('more submissions by this user', 'divi-db') . '</a>';
                                $other_submissions .= '<div style="display: none;" class="other_submissions">
                                                            <h3>' . __('Other submissions made by the same person', 'divi-db') . '</h3>';
                                $other_submissions .= '<table class="widefat">';

                                foreach ($other_contacts as $other_contact) {
                                    $other_submissions .= '<tr><td><a href="' . admin_url('post.php?action=edit&post=' . $other_contact->ID) . '">' . $other_contact->post_title . '</a></td></tr>';
                                }

                                $other_submissions .= '</table></div>';
                            }

                            $value .= ')';
                        } else {
                            $value = '<em>' . __('Not a registered user', 'divi-db') . '</em>';
                        }

                        break;
                }

                $key_label = ucwords(str_replace('_', ' ', $key));

                echo '<tr>
                            <td><strong>' . $key_label . '</strong></td>
                            <td>' . $value . '</td>
                        </tr>';
            }

            echo '</tbody>
                </table>';

            echo $other_submissions;
        }

    }

}

function sb_divi_cfd_meta_box_callback_actions() {
    $submission = get_post(get_the_ID());
    $data = get_post_meta(get_the_ID(), 'sb_divi_cfd', true);

    if (isset($_POST['sb_divi_cfd_map_to'])) {
        $map_to = $_POST['sb_divi_cfd_map_to'];
        $map_to_other = $_POST['sb_divi_cfd_map_to_other'];

        if ($fields = $data['data']) {
            $mapped_fields = array();
            $custom_fields = array();

            foreach ($fields as $field) {
                $mapped_fields[$field['label']] = $field['value'];
            }

            $db_ins = array(
                'post_title'   => __('Cloned from contact form', 'divi-db'),
                'post_content' => __('Cloned from contact form', 'divi-db'),
                'post_status'  => 'publish',
                'post_type'    => $_POST['sb_divi_cfd_pt'],
            );

            if (isset($_POST['sb_divi_cfd_date'])) {
                $db_ins['post_date'] = $_POST['sb_divi_cfd_date'];
            }

            $found = 0;

            foreach ($map_to as $key => $field) {
                if ($field) {
                    $found++;

                    if ($field == 'custom_field') {
                        if ($map_to_other[$key]) {
                            $custom_fields[$map_to_other[$key]] = $mapped_fields[$key];
                        }
                    } else {
                        $db_ins[$field] = $mapped_fields[$key];
                    }
                }
            }

            if ($found) {
                // Insert the post into the database
                if ($post_id = wp_insert_post($db_ins)) {
                    if (!is_wp_error($post_id)) {
                        foreach ($custom_fields as $key => $value) {
                            update_post_meta($post_id, $key, $value);
                        }

                        echo '<div id="message" class="updated fade">
                                    <p>' . __('Successfully copied the content of this contact form submission to another post type.', 'divi-db') . ' ' . __('Click here to', 'divi-db') . ' <a href="' . get_permalink($post_id) . '">' . __('View', 'divi-db') . '</a> ' . __('or', 'divi-db') . ' <a href="' . admin_url('post.php?action=edit&post=' . $post_id) . '">' . __('Edit', 'divi-db') . '</a></p>
                                </div>';

                        if (!$cloned = get_post_meta($_GET['post'], 'sb_divi_cfd_cloned', true)) {
                            $cloned = array();
                        }

                        $cloned[$post_id] = time();

                        update_post_meta($_GET['post'], 'sb_divi_cfd_cloned', $cloned);

                    } else {
                        echo '<div id="message" class="error fade">
                                    <p>' . __('Oops something went wrong. This error message may be helpful', 'divi-db') . ': ' . print_r($post_id, true) . '</p>
                                </div>';
                    }
                }
            } else {
                echo '<div id="message" class="error fade">
                            <p>' . __('You need to choose at least one field to map against for the clone to work.', 'divi-db') . '</p>
                        </div>';
            }

            //echo '<pre>';
            //print_r($db_ins);
            //print_r($custom_fields);
            //print_r($data['data']);
            //print_r($_POST);
            //echo '</pre>';
        }
    }

    $map_to_options = array();
    $maps = array(
        'post_title'   => __('Title', 'divi-db'),
        'post_content' => __('Content', 'divi-db'),
        'custom_field' => __('Custom Field', 'divi-db')
    );

    foreach ($maps as $key => $value) {
        $map_to_options[] = '<option value="' . $key . '">' . $value . '</option>';
    }

    $types = get_post_types();
    $type_options = array();

    foreach ($types as $type2) {
        $type_obj2 = get_post_type_object($type2);

        if (!$type_obj2->public) {
            continue;
        }

        $type_options[] = '<option value="' . $type2 . '">' . $type_obj2->labels->name . '</option>';
    }

    echo '<p>';

    if ($email = get_post_meta(get_the_ID(), 'sb_divi_cfd_email', true)) {
        echo '<a style="margin-right: 10px;" class="button-primary" target="_blank" href="mailto:' . $email . '">' . __('Reply via Email', 'divi-db') . '</a>';
    }

    echo '<a onclick="jQuery(\'.sb_divi_cfd_convert\').slideToggle();" class="button-secondary">' . __('Copy to another Post Type', 'divi-db') . '</a>';

    echo '</p>';

    ///////////////////////////////////

    echo '<div style="display: none; overflow: scroll;" class="sb_divi_cfd_convert">';

    echo '<h3>' . __('Copy to another post type', 'divi-db') . '</h3>';

    echo '<p><label>' . __('Select Post Type', 'divi-db') . ': <select name="sb_divi_cfd_pt">' . implode('', $type_options) . '</select></label></p>';
    echo '<p>' . __('Select Field Mappings', 'divi-db') . ':</p>';

    echo '<table class="widefat">';

    foreach ($data['fields_original'] as $field) {
        echo '<tr>
                    <td>' . $field['field_label'] . '</td>
                    <td>
                        <select name="sb_divi_cfd_map_to[' . $field['field_label'] . ']"><option value="">-- ' . __('Unused', 'divi-db') . ' --</option>' . implode('', $map_to_options) . '</select>
                        <span style="margin-left: 20px; display: inline-block;">(' . __('If "Custom Field" selected, enter field name', 'divi-db') . ': <input type="text" name="sb_divi_cfd_map_to_other[' . $field['field_label'] . ']" />)</span>
                    </td>
                </tr>';
    }

    echo '</table>';

    echo '<p><label><input type="checkbox" name="sb_divi_cfd_date" value="' . $submission->post_date . '" />&nbsp;' . __('Keep date of original submission?', 'divi-db') . ' (' . $submission->post_date . ')</label></p>';
    echo '<p><input type="submit" class="button-primary sb_divi_cfd_copy" value="' . __('Copy', 'divi-db') . '" /></p>';

    //echo '<pre>';
    //print_r($data['fields_original']);
    //echo '</pre>';

    echo '</div>';

    if ($cloned = get_post_meta($_GET['post'], 'sb_divi_cfd_cloned', true)) {
        echo '<h3>' . __('Clone History', 'divi-db') . '</h3>';

        echo '<table class="widefat">
                    <thead>
                        <tr>
                            <th>' . __('New Post Title', 'divi-db') . '</th>
                            <th>' . __('Post Type', 'divi-db') . '</th>
                            <th>' . __('Date Cloned', 'divi-db') . '</th>
                            <th>' . __('Actions', 'divi-db') . '</th>
                        </tr>
                    </thead>';

        foreach ($cloned as $cloned_id => $date) {
            if ($cloned_post = get_post($cloned_id)) {
                $type_obj = get_post_type_object($cloned_post->post_type);
                $type_name = $type_obj->labels->name;

                echo '<tr>
                            <td>' . $cloned_post->post_title . '</td>
                            <td>' . $type_name . '</td>
                            <td>' . date('Y-m-d H:i', $date) . '</td>
                            <td><a href="' . get_permalink($cloned_id) . '">' . __('View', 'divi-db') . '</a> | <a href="' . admin_url('post.php?action=edit&post=' . $post_id) . '">' . __('Edit', 'divi-db') . '</a></td>
                        </tr>';
            }
        }

        echo '</table>';

    }
}

/*function sb_divi_cfd_meta_box_callback_debug() {

	if ( $data = get_post_meta( get_the_ID(), 'sb_divi_cfd', true ) ) {
		echo '<div style="display: none; overflow: scroll;" class="sb_divi_cfd_debug">';

		echo '<pre>';
		print_r( $data );
		echo '</pre>';

		echo '</div>';

		echo '<p><a onclick="jQuery(\'.sb_divi_cfd_debug\').slideToggle();" class="button-secondary">' . __( 'Reveal Debug/Server Information', 'divi-db' ) . '</a></p>';
	}

}*/

function sb_divi_cfd_pt_init() {
    $labels = array(
        'name'               => _x('Divi DB - Contact form submissions', 'post type general name', 'divi-db'),
        'singular_name'      => _x('Divi DB', 'post type singular name', 'divi-db'),
        'menu_name'          => _x('Divi DB', 'admin menu', 'divi-db'),
        'name_admin_bar'     => _x('Divi DB', 'add new on admin bar', 'divi-db'),
        'add_new'            => _x('Add New', 'Divi DB', 'divi-db'),
        'add_new_item'       => __('Add New Divi DB', 'divi-db'),
        'new_item'           => __('New Divi DB', 'divi-db'),
        'edit_item'          => __('Edit Divi DB', 'divi-db'),
        'view_item'          => __('View Divi DB', 'divi-db'),
        'all_items'          => __('All Divi DB', 'divi-db'),
        'search_items'       => __('Search Divi DB', 'divi-db'),
        'parent_item_colon'  => __('Parent Divi DB:', 'divi-db'),
        'not_found'          => __('No contact form submissions found.', 'divi-db'),
        'not_found_in_trash' => __('No contact form submissions found in Trash.', 'divi-db')
    );

    $args = array(
        'labels'             => $labels,
        'description'        => __('For storing Divi contact form submissions.', 'divi-db'),
        'public'             => false,
        'publicly_queryable' => false,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => false,
        'capability_type'    => 'post',
        'has_archive'        => false,
        'hierarchical'       => false,
        'menu_position'      => null,
        'menu_icon'          => 'dashicons-admin-comments',
        'supports'           => array('title')
    );

    register_post_type('divi_cf_db', $args);
}

function sb_divi_cfd_et_contact_page_headers($headers, $contact_name, $contact_email) {
    global $current_user;

    for ($et_pb_contact_form_num = 0; $et_pb_contact_form_num <= apply_filters('divi_db_max_forms', 25); $et_pb_contact_form_num++) {
        $current_form_fields = isset($_POST['et_pb_contact_email_fields_' . $et_pb_contact_form_num]) ? $_POST['et_pb_contact_email_fields_' . $et_pb_contact_form_num] : '';

        if ($current_form_fields) {
            $data = array();
            $fields_data_json = str_replace('\\', '', $current_form_fields);
            $fields_data_array = json_decode($fields_data_json, true);
            $email = false;

            foreach ($fields_data_array as $index => $value) {
                $value2 = isset($_POST[$value['field_id']]) ? $_POST[$value['field_id']] : '-';
                $label = $value['field_label'];

                if ($value['field_type'] == 'email') {
                    $email = $value2;
                }

                $data[] = array('label' => $label, 'value' => $value2);
            }

            $this_page = get_post(get_the_ID());
            $this_user = false;

            if ($this_user_id = (isset($current_user->ID) ? $current_user->ID : 0)) {
                if ($this_user = get_userdata($this_user_id)) {
                    $this_user = $this_user->display_name;
                }
            }

            $extra = array(
                'submitted_on'    => $this_page->post_title,
                'submitted_on_id' => $this_page->ID,
                'submitted_by'    => $this_user,
                'submitted_by_id' => $this_user_id
            );

            $db_ins = array(
                'post_title'  => date('Y-m-d H:i:s'),
                'post_status' => 'publish',
                'post_type'   => 'divi_cf_db',
            );

            // Insert the post into the database
            if ($post_id = wp_insert_post($db_ins)) {
                update_post_meta(
                    $post_id, 'sb_divi_cfd', array(
                                'data'            => $data,
                                'extra'           => $extra,
                                'fields_original' => $fields_data_array,
                                'post'            => $_POST,
                                //'server'          => $_SERVER
                            )
                );

                if ($this_user_id) {
                    update_post_meta($post_id, 'sb_divi_cfd_submitted_by', $this_user_id);
                }

                update_post_meta($post_id, 'sb_divi_cfd_read', 0);
                update_post_meta($post_id, 'sb_divi_cfd_email', $email);
            }

        }
    }

    return $headers;
}

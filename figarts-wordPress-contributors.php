<?php

/**
 * Plugin Name:       Figarts WordPress Contributors Plugin
 * Plugin URI:        https://figarts.co
 * Description:       This plugin display more than one author-name on a post.
 * Version:           1.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Emmanuel Ochubili (Figarts.co)
 * Author URI:        https://figarts.co
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI:        https://figarts.co
 * Text Domain:       my-basics-plugin
 * Domain Path:       /languages
 */

/*
function figarts_contributor_metabox()
{

    //register custom meta box
    add_meta_box("figarts_author_id", "Contributors", "figarts_author_author_callback_function", "post", "side", "high");
}

add_action("add_meta_boxes", "figarts_contributor_metabox");

function figarts_author_author_callback_function($post)
{

    wp_nonce_field(basename(__FILE__), "figarts_author_nonce");
    ?>
    <div>
        <label for="author">
            <?php
            $post_id = $post->ID;

            $author_id = get_post_meta($post_id, "figarts_author_name", true);

            $all_authors = get_users(array("role" => "author"));
            // print_r($all_authors);
            foreach ($all_authors as $index => $author) {

                $checked = "";
                if ($author_id == $author->data->ID) {
                    $checked = 'checked="checked"';
                }
            ?>
                <input type="checkbox" name="author" value="<?php echo $author->data->ID; ?>" <?php echo $checked; ?>> <?php echo $author->data->display_name ?>
            <?php
            }
            ?>
            
        </label>
    </div>
<?php
}

add_action("save_post", "figarts_save_author", 10, 2);

function figarts_save_author($post_id, $post)
{

    //nonce value first step verification
    if (!isset($_POST['figarts_author_nonce']) || !wp_verify_nonce($_POST['figarts_author_nonce'], basename(__FILE__))) {
        return $post_id;
    }

    //verifying post slug
    $post_slug = "post";
    if ($post_slug != $post->post_type) {
        return $post_id;
    }

    $author_name = "";

    if (isset($_POST['author'])) {
        $author_name = sanitize_text_field($_POST['author']);
    } else {
        $author_name = '';
    }

    update_post_meta($post_id, "figarts_author_name", $author_name);
}


function figarts_display_author_in_template( $content ) {

    global $post;

    // retrieve the global notice for the current post

    $display = esc_attr( get_post_meta( $post->ID, 'figarts_author_name', true ) );
    
    $notice = "<div class='sp_display'>$display</div>";

    return $notice . $content;

}

add_filter( 'the_content', 'figarts_display_author_in_template' );

*/


add_action('admin_menu', 'add_metabox');

function add_metabox()
{

    add_meta_box(
        'figarts_author_id', // metabox ID
        ' Contributors', // title
        'figarts_author_author_callback_function', // callback function
        'post', // post type or post types in array
        'side', // position (normal, side, advanced)
        'high' // priority (default, low, high, core)
    );
}

function figarts_author_author_callback_function($post)
{
    wp_nonce_field(basename(__FILE__), "figarts_author_nonce");

    $contributor_meta = get_post_meta($post->ID, '_contributor_meta', true);
    $contributor_meta = $contributor_meta ? $contributor_meta : [];

    $args = array(
        'orderby' => 'user_nicename',
        'order'   => 'ASC',
    );
    $users = get_users(array('role' => 'author'), $args);

    foreach ($users as $key => $user) { ?>
        <div style="display:inline-block;">
            <input type="checkbox" name="contributor_meta[]" value="<?php echo $user->ID ?>" <?php echo (in_array($user->ID, $contributor_meta)) ? 'checked="checked"' : ''; ?> />
            <span> <?php echo $user->user_nicename ?> </span>
        </div>
<?php  }
}


add_action('save_post', 'figarts_save_author');

function figarts_save_author($post_id)
{
    //nonce value first step verification
    if (!isset($_POST['figarts_author_nonce']) || !wp_verify_nonce($_POST['figarts_author_nonce'], basename(__FILE__))) {
        return $post_id;
    }

    global $post;
    // Get our form field
    if (isset($_POST['contributor_meta'])) {
        $custom = $_POST['contributor_meta'];
        $old_meta = get_post_meta($post->ID, '_contributor_meta', true);
        // Update post meta
        if (!empty($old_meta)) {
            update_post_meta($post->ID, '_contributor_meta', $custom);
        } else {
            add_post_meta($post->ID, '_contributor_meta', $custom, true);
        }
    }
}

//add contributors to the end of post
add_filter('the_content', 'figarts_display_author_in_template');

function figarts_display_author_in_template($content)
{
    global $post;

    

    $contributor_meta = get_post_meta($post->ID, '_contributor_meta', true);
    $contributor_meta = $contributor_meta ? $contributor_meta : [];
    if (is_single() && is_main_query()) {
        $r =
            '<div style="margin-left:25px">
            <h5 style="color:red"> Contributors</h5>';

        foreach ($contributor_meta as $key => $value) {
            $user = get_userdata($value);
            $r .=  '<p class="text-success ml-4 ms-3">' .
                '<span style="margin-right:8px">' . get_avatar($value, $size = '15') . '</span>' .
                '<a style="text-decoration:none" href="'. home_url() .'/author/' . $user->user_nicename . '">' . $user->user_nicename . '</a>' .
                '</p>';
        }
    }
    return $r . $content . '</div>';

}

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
    $book_slug = "post";
    if ($book_slug != $post->post_type) {
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

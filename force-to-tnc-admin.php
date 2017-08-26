<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>
<div class="wrap">
  <h1>Force To Terms and Conditions</h1>
  <?php
  if ( ! isset( $_POST['name_fttnc_settings_nonce'] ) || ! wp_verify_nonce( $_POST['name_fttnc_settings_nonce'], 'fttnc_settings_action' )) {
    //Verifiy not match..
  } else {
      if(isset($_POST['fttnc_settings_submit'])){
        update_option('fttnc_page_id', intval($_POST['termnc_page_id']));
        update_option('termnc_display_type', sanitize_text_field($_POST['termnc_display_type']));
        update_option('termnc_notice_info', sanitize_textarea_field($_POST['termnc_notice_info']));
        update_option('termnc_custom_css', sanitize_textarea_field($_POST['termnc_custom_css']));
        echo '<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible">
                <p><strong>Settings saved.</strong></p>
                <button type="button" class="notice-dismiss">
                  <span class="screen-reader-text">Dismiss this notice.</span>
                </button>
              </div>';
      }
  }
  ?>
  <form method="post" action="options-general.php?page=fttnc-settings" novalidate="novalidate">
    <input type="hidden" name="fttnc_settings_page" value="fttnc">
    <?php wp_nonce_field( 'fttnc_settings_action', 'name_fttnc_settings_nonce' ); ?>
    <?php wp_referer_field(); ?>
    <table class="form-table">
      <tr>
        <th scope="row"><label for="last_update_time">Last Updated</label></th>
        <td>
          <?php echo get_option('fttnc_last_update', true); ?>
          <p class="description" id="last_update_time-description">Terms and conditions page last update time</p>
        </td>
      </tr>
      <tr>
        <th scope="row"><label for="termnc_page_id">Terms and Conditions Page</label></th>
        <td>
          <?php
            if(get_option('fttnc_page_id', true) > 1){
              $selected_id = get_option('fttnc_page_id', true);
            }else {
              $selected_id = 0;
            }
            $page_arg = array(
              'name' => 'termnc_page_id',
              'selected' => $selected_id,
            );
            wp_dropdown_pages($page_arg);
          ?>
          <?php if($selected_id > 1){ ?>
            <a href="<?php echo get_edit_post_link($selected_id); ?>" title="Edit <?php echo get_the_title($selected_id); ?>">Edit</a>
          <?php } ?>
        </td>
      </tr>
      <tr>
        <th scope="row"><label for="termnc_display_type">Force or dispaly mode</label></th>
        <td>
          <?php
            if(get_option('termnc_display_type', true) != ''){
              $display_type = esc_html(get_option('termnc_display_type', true));
            }else {
              $display_type = 'redirect';
            }
          ?>
          <select name="termnc_display_type" id="termnc_display_type">
          	<option value="redirect" <?php if($display_type == 'redirect'){ ?>selected="selected"<?php } ?>>Redirect to Terms and Conditions Page</option>
          	<option value="display_top" <?php if($display_type == 'display_top'){ ?>selected="selected"<?php } ?>>Notice at top</option>
          	<option value="display_bottom" <?php if($display_type == 'display_bottom'){ ?>selected="selected"<?php } ?>>Notice at bottom</option>
          </select>
        </td>
      </tr>
      <tr>
        <th scope="row"><label for="termnc_notice_info">Notice Info</label></th>
        <td>
          <textarea name="termnc_notice_info" id="termnc_notice_info" rows="5" cols="100"><?php echo esc_textarea(get_option('termnc_notice_info')); ?></textarea>
          <p class="description"><code>Recently we updated our terms and conditions. Please check it &lt;a href="/terms/"&gt;here&lt;/a&gt; or  click [fttnc-agree-link] to access our updated terms and conditions.</code></p>
        </td>
      </tr>
      <tr>
        <th scope="row"><label for="termnc_shortcodes">Shortcodes</label></th>
        <td>
          <p class="description"><code>[fttnc-agree checkbox_label="" button_label="" agree_redirect="" agreed_ok_message="" non_loged_message="" agreed_error_message=""]</code> Generate form to agree terms and conditions.</p>
          <p class="description"><code>[fttnc-agree-link link_label="Yes, I agree"]</code> Generate link to agree terms and conditions.</p>
        </td>
      </tr>
      <tr>
        <th scope="row"><label for="termnc_custom_css">Custom CSS</label></th>
        <td>
          <textarea name="termnc_custom_css" id="termnc_custom_css" rows="5" cols="100"><?php echo esc_textarea(get_option('termnc_custom_css')); ?></textarea>
        </td>
      </tr>

    </table>
    <p class="submit"><input type="submit" name="fttnc_settings_submit" id="fttnc_settings_submit" class="button button-primary" value="Save Changes"></p>
  </form>
</div>

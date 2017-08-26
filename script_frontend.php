<!---Start Redirect Terms-->
<script type="text/javascript">
  jQuery(document).ready(function($) {
      <?php
      $display_type = get_option('termnc_display_type');
      if(($display_type == 'display_top')){
      ?>
      var fttnc_notice_height = jQuery('#force_to_term_notice_box').height();
      jQuery("body").css("padding-top", fttnc_notice_height+'px');
      <?php }if(($display_type == 'display_bottom')){?>
      var fttnc_notice_height = jQuery('#force_to_term_notice_box').height();
      jQuery("body").css("padding-bottom", fttnc_notice_height+'px');
      <?php } ?>
  });
</script>
<style type="text/css">
  .force_totnc_notice {
    box-sizing: border-box;
    z-index: 99999;
    overflow: hidden;
    color: #636363;
    position: fixed;
    left: 0;
    width: 100%;
    background-color: #d6d6d6;
  }
  .force_totnc_notice.display_top{ top: 0; }
  .force_totnc_notice.display_bottom{ bottom: 0; }
  .force_totnc_notice_inner{ padding: 10px 20px; }
  .agree_check_wrap, .rttnc_submit{ display: inline; }
  .rttnc_submit{ margin-left: 10px; }
  .rttnc_submit .button{padding: 5px 15px; font-weight: normal;}
  <?php
  echo esc_textarea(get_option('termnc_custom_css'));
  ?>
</style>
<!---End Redirect Terms-->';

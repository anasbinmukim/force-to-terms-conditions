<?php
/*
Add checkbox to user edit page to exclude individual user from terms and conditions redirect or notice.
*/
add_action( 'show_user_profile', 'fttnc_show_extra_profile_fields' );
add_action( 'edit_user_profile', 'fttnc_show_extra_profile_fields' );

function fttnc_show_extra_profile_fields( $user ) { ?>

	<h3>Force To Terms and Conditions</h3>

	<table class="form-table">
		<tr class="fttnc-redirect-exclude user-fttnc-terms-wrap">
		<th scope="row">Exclude</th>
		<td>
			<fieldset>
				<label for="fttnc_exclude_user_flag">
				<?php
					$fttnc_exclude_user = get_the_author_meta( 'fttnc_exclude_user', $user->ID );
				?>
				<input name="fttnc_exclude_user_flag" type="checkbox" id="fttnc_exclude_user_flag" value="1" <?php if($fttnc_exclude_user == 1){ ?> checked="checked" <?php } ?>>
				Excluded from receiving the redirect message like always agreed.</label><br>
			</fieldset>
		</td>
		</tr>

	</table>
<?php }

add_action( 'personal_options_update', 'fttnc_save_extra_profile_fields' );
add_action( 'edit_user_profile_update', 'fttnc_save_extra_profile_fields' );

function fttnc_save_extra_profile_fields( $user_id ) {

	if ( !current_user_can( 'edit_user', $user_id ) )
		return false;

	update_user_meta( $user_id, 'fttnc_exclude_user', sanitize_text_field($_POST['fttnc_exclude_user_flag']) );
}

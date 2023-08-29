<?php



?>
<div class="tc-front-container">
        <div class="tc-front-area">
            <h1><?php _e('Round Table Token Generator', 'TC');?></h1>
			<div id="tc_order_attendee_message"></div>
			<form id="tc_email_for_token_form">
				<table id="tc-attendee-listing">
				  <tr>
					<th><?php _e('Email', 'TC');?></th>
					<td><input type="email" required id="tc_email_for_token" name="tc_email_for_token" /></td>
				  </tr>
				  <tr>
					<th></th>
					<td><input type="submit" class="btn btn-primary" value="<?php _e('Generate Token', 'TC');?>" id="tc_email_submit" /></td>
				  </tr>
				</table>
			</form>
        </div>
    </div>

<div style="float:right;width:200px;">
<table cellpadding="0" class="widefat donation" style="margin-bottom:10px; border:solid 2px #0074a2;">
	<thead>
		<th style="text-align:center;color:#0074a2;"><strong><?php _e('Am&eacute;liorer ce plugin', 'rencontre') ?></strong></th>
	</thead>
	<tbody>
		<tr>
			<td><?php _e('Vous appr&eacute;ciez ce plugin gratuit ? Un don nous aidera &agrave; d&eacute;gager du temps pour le faire &eacute;voluer. Merci pour votre participation.','rencontre') ?></td>
		</tr>
		<tr>
			<td style="text-align:center;">
				<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_blank">
					<input type="hidden" name="cmd" value="_donations" />
					<input type="hidden" name="business" value="J5R558LZ7FQ7L" />
					<input type="hidden" name="notify_url" value="http://www.boiteasite.fr/fiches/uno/plugins/paypal/ipn.php" />
					<input type="hidden" name="lc" value="<?php _e('FR', 'rencontre') ?>" />
					<input type="hidden" name="item_name" value="<?php _e('Don plugin Rencontre', 'rencontre') ?>" />
					<input type="hidden" name="no_note" value="1" />
					<input type="hidden" name="src" value="0" />
					<input type="hidden" name="currency_code" value="EUR" />
					<input type="hidden" name="bn" value="PP-DonationsBF:btn_donateCC_LG.gif:NonHostedGuest" />
					<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="<?php _e('PayPal - la solution de paiement en ligne la plus simple et la plus s&eacute;curis&eacute;e !','rencontre') ?>" />
				</form>
			</td>
		</tr>
		<tr>
			<td><?php _e('Vous pouvez aussi nous aider en', 'rencontre') ?>&nbsp;<a href="https://wordpress.org/support/view/plugin-reviews/rencontre" target="_blank"><?php _e('notant ce plugin','rencontre') ?></a>.</td>
		</tr>
	</tbody>
</table>
</div>
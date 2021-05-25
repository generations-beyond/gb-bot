<form action="admin.php?page=<?php echo $this->plugin->name; ?>" method="POST">
	<?php wp_nonce_field( $this->plugin->name, $this->plugin->name . '_nonce' ); ?>

	<?php 
		$gbbot_team_cpt_enable = gbbot_cpt_settings('enabled');
		$gbbot_team_post_label = gbbot_cpt_settings('label');
		$gbbot_team_post_type = gbbot_cpt_settings('type');
	?>

	<div id="post-body-content">
		<div class="postbox">
			<h3 class="hndle">GB Bot CPT</h3>
			<div class="inside">
				<p>
					Custom Post Types: 
				</p>
				<p>
					<input name="gbbot_team_cpt_enable" type="checkbox" id="gbbot_team_cpt_enable" value='1' <?=$gbbot_team_cpt_enable?'checked':''?> > Enable 
				</p>
				<p>
					Post type label: <input name="gbbot_team_post_label" type="text" id="gbbot_team_post_label" value="<?=$gbbot_team_post_label?>" placeholder="Ex: Team">
				</p>
				<p>
					Post type slug: <input name="gbbot_team_post_type" type="text" id="gbbot_team_post_type" value="<?=$gbbot_team_post_type?>" placeholder="Ex: team_member">
				</p>
			</div>
		</div>

		

		<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes"></p>
	</div>
</form>
<script>
		document.getElementById('gbbot_team_post_type').addEventListener('keyup',function(e) {
			this.value = this.value.replace(/[ ]/,'_');
			this.value = this.value.toLowerCase();
		});
</script>
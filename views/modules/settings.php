<form action="admin.php?page=<?php echo $this->plugin->name; ?>" method="POST">
	<?php wp_nonce_field( $this->plugin->name, $this->plugin->name . '_nonce' ); ?>

	<?php 
		$gbbot_team_cpt_enable = gbbot_cpt_settings('enabled');
		$gbbot_team_post_label = gbbot_cpt_settings('label');
		$gbbot_team_post_type = gbbot_cpt_settings('type');
	?>

	<div id="post-body-content">
		<div class="postbox">
			<h3 class="hndle">"Team Member" Custom Post Type</h3>
			<div class="inside">
				<p>
					This is a custom post type that includes a collection of fields that are useful for any website needing an "About Us" section that features members of their organization or team.
				</p>
				<table class="form-table">
					<tbody>
						<tr>
							<th scope="row">Enable</th>
							<td><input name="gbbot_team_cpt_enable" type="checkbox" id="gbbot_team_cpt_enable" value='1' <?=$gbbot_team_cpt_enable?'checked':''?> ></td>
						</tr>
						<tr>
							<th scope="row">Post Type Label</th>
							<td><input name="gbbot_team_post_label" type="text" id="gbbot_team_post_label" value="<?=$gbbot_team_post_label?>" placeholder="Ex: Team"></td>
						</tr>
						<tr>
							<th scope="row">Post type slug</th>
							<td>
								<input name="gbbot_team_post_type" type="text" id="gbbot_team_post_type" value="<?=$gbbot_team_post_type?>" placeholder="Ex: team_member">
								<p class="description">
									<span style="color: red;">Warning: Changing the post type slug after creating Team Members can cause existing Team Members to disappear.</span>
								</p>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
			<h3 class="hndle">Featured Image Admin Thumbnail</h3>
			<div class="inside">
				<p>
					Adds inline image thumbnails to admin columns for the selected post types.
				</p>
				<table class="form-table">
					<tbody>
						<tr>
							<th scope="row">Post Types:</th>
							<td>
								<?php 
									$post_types = get_post_types( [], 'objects');
									$enabled_post_types = get_option('gbbot_featured_image_post_types', []);
									foreach ($post_types as $post_type) :
										if($post_type->public && !in_array( $post_type->name, ['attachment','elementor_library'] )) :
											?>
												<label>
													<input name="gbbot_featured_image_post_types[]" type="checkbox" value='<?=$post_type->name?>' <?= in_array($post_type->name, ($enabled_post_types ?: [])) ? 'checked' : '' ?>>
													<?=$post_type->label?>
												</label><br>
											<?php
										endif;
									endforeach;
								?>
							</td>
						</tr>
						
					</tbody>
				</table>
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
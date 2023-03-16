<form class="gbbot-settings" action="admin.php?page=<?php echo $this->plugin->name; ?>" method="POST">
	<?php wp_nonce_field( $this->plugin->name, $this->plugin->name . '_nonce' ); ?>

	<div id="post-body-content">
		<div class="settings-form-options" id="general-options">
			<div class="postbox">
				<h3>"Team Member" Custom Post Type</h3>
				<div class="inside">
					<p>
						This is a custom post type that includes a collection of fields that are useful for any website needing an "About Us" section that features members of their organization or team.
					</p>
					<table class="form-table">
						<tbody>
							<tr>
								<th scope="row">Enable</th>
								<td><input name="gbbot_team_cpt_enable" type="checkbox" id="gbbot_team_cpt_enable" value='1' <?= $this->settings['gbbot_team_cpt_enable'] ? 'checked' : '' ?> ></td>
							</tr>
							<tr>
								<th scope="row">Post Type Label</th>
								<td><input name="gbbot_team_post_label" type="text" id="gbbot_team_post_label" value="<?= $this->settings['gbbot_team_post_label'] ?>" placeholder="Team"></td>
							</tr>
							<tr>
								<th scope="row">Post type slug</th>
								<td>
									<input name="gbbot_team_post_type" type="text" id="gbbot_team_post_type" value="<?= $this->settings['gbbot_team_post_type'] ?>" placeholder="team_member">
									<p class="description">
										<span style="color: red;">Warning: Changing the post type slug after creating Team Members can cause existing Team Members to disappear.</span>
									</p>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
			<?php if($GBTC_ACTIVE) : ?>
				<div class="postbox notice-warning notice-alt">
					<h3>GB Theme Core Detected</h3>
					<div class="inside">
						<p>
							<strong>Notice</strong>: GB Theme Core is currently activated. To get the most features out of <?= $this->plugin->displayName ?>, install and enable the <strong>Proactive by GB</strong> theme.
						</p>
					</div>
				</div>
			<?php else : ?>
				<div class="postbox">
					<h3>Featured Image Admin Thumbnail</h3>
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
				<div class="postbox">
					<h3>Enable Back to Top arrow</h3>
					<div class="inside">
						<p>
							Adds an icon on the bottom of the site that returns to the top
						</p>
						<table class="form-table">
							<tbody>
								<tr>
									<th scope="row">Location:</th>
									<td>
										<label>
											<input name="gbbot_enable_return_to_top" type="radio" value='bottom_right' <?= $this->settings['gbbot_enable_return_to_top'] == 'bottom_right' ? 'checked' : '' ?> > 
											Bottom Right
										</label><br>
										<label>
											<input name="gbbot_enable_return_to_top" type="radio" value='bottom_left' <?= $this->settings['gbbot_enable_return_to_top'] == 'bottom_left' ? 'checked' : '' ?> > 
											Bottom Left
										</label><br>
										<label>
											<input name="gbbot_enable_return_to_top" type="radio" value='none' <?= $this->settings['gbbot_enable_return_to_top'] == 'none' ? 'checked' : '' ?> > 
											None (disabled)
										</label>
									</td>
								</tr>
								
							</tbody>
						</table>
					</div>
				</div>
			<?php endif; ?>
		</div>

		<div class="settings-form-options" id="integrations-options">
			<div class="postbox">
				<h3>Live Analytics</h3>
				<div class="inside">
					<p>
						Chances are you have analytics already being collected on your website, but how often do you check them? And when you do check them, how easily are you able to interpret your stats? GB•BOT allows us to create a live data feed to a monitor in your office so you can keep an eye on your stats with an easy to understand interface.
					</p>
					<p>
						<em>To begin viewing your live analytics data, contact <a href="https://generationsbeyond.com/#contact" target="_blank" rel="nofollow">Generations Beyond</a>.</em>
					</p>
				</div>
			</div>

			<div class="postbox">
				<h3>ReBound Tracking</h3>
				<div class="inside">
					<p>
						ReBound allows you to directly market to what we call "hand raisers"&ndash;potential customers who have browsed a specific product or service on your website, but left without leaving their contact information. We now have the technology to reach them anyway! Enter your ReBound Tracking ID in the box below to enable ReBound tracking.
					</p>
					<table class="form-table" role="presentation">
						<tbody>
							<tr>
								<th scope="row"><label for="gbbot_rebound_id">Tracking ID</label></th>
								<td>
									<input name="gbbot_rebound_id" type="text" id="gbbot_rebound_id" value="<?= $this->settings['gbbot_rebound_id'] ?>">
									<p>
										<em>To obtain your ReBound Tracking ID, contact <a href="https://generationsbeyond.com/rebound-marketing/" target="_blank" rel="nofollow">Generations Beyond</a>.</em>
									</p>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>

			<div class="postbox">
				<h3>IoT Notifications</h3>
				<div class="inside">
					<p>
						The Internet of Things (IoT) is growing every day. Wouldn't it make sense that your website become a part of it? With GB•BOT installed, we have the ability to trigger real objects in your office, such as an internet-enabled light bulb to react whenever you get a new website lead.
					</p>
					<p>
						<em>To inquire about IoT Notifications, contact <a href="https://generationsbeyond.com/#contact" target="_blank" rel="nofollow">Generations Beyond</a>.</em>
					</p>
				</div>
			</div>
		</div>

		<div class="settings-form-options" id="advanced-options">
			<div class="postbox">
				<h3>Version Control</h3>
				<div class="inside">
					<p>
						Enter the name of the git branch that you want to track updates from.
					</p>
					<table class="form-table">
						<tbody>
							<tr>
								<th scope="row">Branch:</th>
								<td>
									<input name="gbbot_active_branch" type="text" id="gbbot_active_branch" value="<?= $this->settings['gbbot_active_branch'] ?>" placeholder="master">
									<p class="description">
										<span style="color: red;">Warning: Using a branch other than 'master' may result in website instability. Do not modify this unless you know what you're doing.</span>
									</p>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
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
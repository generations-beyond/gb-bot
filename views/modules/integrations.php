<form action="admin.php?page=<?php echo $this->plugin->name; ?>" method="post">
	<?php wp_nonce_field( $this->plugin->name, $this->plugin->name . '_nonce' ); ?>

	<div id="post-body-content">
		<div class="postbox">
			<h3 class="hndle">Live Analytics</h3>
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
			<h3 class="hndle">ReBound Tracking</h3>
			<div class="inside">
				<p>
					ReBound allows you to directly market to what we call "hand raisers"&ndash;potential customers who have browsed a specific product or service on your website, but left without leaving their contact information. We now have the technology to reach them anyway! Enter your ReBound Tracking ID in the box below to enable ReBound tracking.
				</p>
				<table class="form-table" role="presentation">
					<tbody>
						<tr>
							<th scope="row"><label for="gbbot_rebound_id">Tracking ID</label></th>
							<td>
								<input name="gbbot_rebound_id" type="text" id="gbbot_rebound_id" value="<?php echo $this->settings['gbbot_rebound_id']; ?>" class="regular-text ltr">
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
			<h3 class="hndle">IoT Notifications</h3>
			<div class="inside">
				<p>
					The Internet of Things (IoT) is growing every day. Wouldn't it make sense that your website become a part of it? With GB•BOT installed, we have the ability to trigger real objects in your office, such as an internet-enabled light bulb to react whenever you get a new website lead.
				</p>
				<p>
					<em>To inquire about IoT Notifications, contact <a href="https://generationsbeyond.com/#contact" target="_blank" rel="nofollow">Generations Beyond</a>.</em>
				</p>
			</div>
		</div>

		<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes"></p>
	</div>
</form>
<img style="width: 200px; max-width: 100%; height: auto; display: block; margin: 0 auto;" src="<?= $this->plugin->url; ?>img/gb-bot.png" alt="GB&bull;BOT"/>

<div style="margin-top: 10px; text-align: center;">
    <?php if (current_user_can('level_10')) : ?>
        <a href="admin.php?page=gb-bot" class="button button-primary">
            <span style="vertical-align: middle; font-size: 17px;" aria-hidden="true" class="dashicons dashicons-edit"></span> Edit Settings
        </a>
    <?php endif; ?>
</div>
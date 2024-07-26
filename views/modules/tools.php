<div id="post-body-content">
    <div class="postbox">
        <h3 class="hndle">Elements Usage Calculator for Elementor</h3>
        <div class="inside">
            <p>
                Did you know that excessive use of feature-enhancing Elementor addons can cause your website to <em>slow down?</em>
            </p>
            <p>
                There are many addon plugins available for Elementor which add new elements and features. We recommend looking at the settings for each of these addon plugins and disabling any elements that are not being used on any pages/posts as this can help improve page speed.
                Not sure which elements to disable? Fear not! GB&bull;BOT can help!
            </p>
            <p>
                Below you will find a list of all of your <code>posts</code>, organized by <code>post_type</code>. Each post's box contains a list of every element used in that post along with a count of how many times it was used. These elements usually have unique identifiers that can be used to determine which addon they are from. For example, "Essential Addons" elements typically begin with <code>eael-</code>.
            </p>
            <p>
                If you need any assistance using this tool, feel free to contact <a href="https://generationsbeyond.com/#contact" target="_blank" rel="nofollow">Generations Beyond</a>!
            </p>
            <p>
                <em>
                    Note:
                    <br/>To ensure a correct element count, make sure "Usage Data Sharing" is enabled from the <code>Elementor &rightarrow; Settings</code> menu.
                    <br/>You may also want to visit <code>Elementor &rightarrow; System Info</code> and press the "Recalculate" button in the Elements Usage section.
                </em>
            </p>
        </div>
    </div>

    <style type="text/css">
        .gb-euc-wrap pre {
            max-width: 100%;
            overflow: scroll;
        }

        .gb-euc-post_type-title {
            margin: 15px 0 7px;
        }
        .gb-euc-posts {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            justify-content: flex-start;
        }
        .gb-euc-posts > .postbox {
            margin-bottom: 0;
            flex: 0 1 calc(25% - 10px);
        }
        .gb-euc-posts > .postbox a .hndle {
            cursor: pointer;
        }
        .gb-euc-element {
            display: flex;
            gap: 5px;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        @media (max-width: 1576px) {
            .gb-euc-wrap > .postbox {
                flex: 0 1 calc(33.33% - 10px);
            }
        }
    </style>

    <?php
        $euc_data = gb_calculate_elements_usage();
    ?>
    <div class="gb-euc-wrap">
        <?php foreach ($euc_data as $euc_post_type => $euc_posts) : ?>
            <div class="gb-euc-post_type">
                <div class="gb-euc-post_type-title">
                    <code><strong>post_type</strong>: <?= $euc_post_type ?></code>
                </div>
                <div class="gb-euc-posts">
                    <?php foreach ($euc_posts as $euc_post) : ?>
                        <div class="postbox">
                            <a href="<?= $euc_post['permalink'] ?>" target="_blank" rel="noopener">
                                <h3 class="hndle">
                                    <?= $euc_post['post_title'] ?>
                                </h3>
                            </a>
                            <div class="inside">
                                <?php if ($euc_post['_elementor_controls_usage'] !== false) : ?>
                                    <?php foreach (unserialize($euc_post['_elementor_controls_usage'][0]) as $name => $element) : ?>
                                        <div class="gb-euc-element">
                                            <code><?= $name ?></code>
                                            <code><?= $element['count'] ?></code>
                                        </div>
                                    <?php endforeach; ?>
                                <?php elseif ($euc_post['post_status'] == 'draft') : ?>
                                    <p>
                                        <em>Post set to draft, unable to determine elements usage.</em>
                                    </p>
                                <?php else : ?>
                                    <p>
                                        No elements found.
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

</div>

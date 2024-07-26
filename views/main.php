<div class="wrap">
    <h1><?php echo $this->plugin->displayName; ?> Settings</h1>

    <?php
    if (isset($this->message)) {
        ?>
        <div class="updated fade"><p><?php echo $this->message; ?></p></div>
        <?php
    }
    if (isset($this->errorMessage)) {
        ?>
        <div class="error fade"><p><?php echo $this->errorMessage; ?></p></div>
        <?php
    }
    ?>

    <div id="poststuff">
        <div id="post-body" class="metabox-holder columns-2">
            <!-- Content -->
            <?php
            if (isset($_GET["page"])) {
                if ($_GET["page"] == $this->plugin->name) {
                    require_once($this->plugin->folder . 'views/modules/nav.php');
                    $cur_page = 'settings.php';
                } else {
                    $cur_page = $_GET["page"];
                    $cur_page = str_replace($this->plugin->name . '-', '', $cur_page);
                    $cur_page .= '.php';
                }
                include($this->plugin->folder . 'views/modules/' . $cur_page);
            }
            ?>
            
            <!-- Sidebar -->
            <div id="postbox-container-1" class="postbox-container">
                <?php require_once($this->plugin->folder . 'views/sidebar.php'); ?>
            </div>
            <!-- /postbox-container -->
        </div>
    </div>
</div>
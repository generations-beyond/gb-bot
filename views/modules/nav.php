<h2 style="margin-bottom:15px;padding-bottom:0;" class="nav-tab-wrapper">
	<a href="admin.php?page=<?=$this->plugin->name?>" class="nav-tab <?=isset($_GET["page"])&&$_GET["page"]==$this->plugin->name?'nav-tab-active':''?>">Settings</a>
	<a href="admin.php?page=<?=$this->plugin->name?>-integrations" class="nav-tab <?=isset($_GET["page"])&&$_GET["page"]==$this->plugin->name."-integrations"?'nav-tab-active':''?>">Integrations</a>
	<a href="admin.php?page=<?=$this->plugin->name?>-tools" class="nav-tab <?=isset($_GET["page"])&&$_GET["page"]==$this->plugin->name."-tools"?'nav-tab-active':''?>">Tools</a>
</h2>
<?php

class AddOnAdmin extends ModelAdmin {
	static $menu_title = "Extensions";
	static $url_segment = "addons";

	static $managed_models = array(
		"Member",
		"ModulePage",
		"WidgetPage",
		"ThemePage",
		"AddonRelease",
	);
	
	static $page_length = 20;
}

<?php
class AddonMemberDecorator extends DataObjectDecorator {
	
	function extraStatics() {
		return array(
			'belongs_many_many' => array(
				'AddOns' => 'AddOnPage',
			)
		);
	}

	function HasContributions() {
		return (bool)DB::query("SELECT ID FROM AddOnPage_Maintainers WHERE MemberID = " . (int)$this->owner->ID)->value();
	}
	
	function Widgets() {
		return $this->owner->AddOns("ClassName = 'WidgetPage'");
	}
	
	function Themes() {
		return $this->owner->AddOns("ClassName = 'ThemePage'");
	}
	
	function Modules() {
		if($member = Member::CurrentUser()){
			if($member->exists() && $member->ID == $this->owner->ID){
				$stage = Versioned::current_stage();
				Versioned::reading_stage('Stage');
				$modules = $this->owner->AddOns("ClassName = 'ModulePage'");
				Versioned::reading_stage($stage);
			}
		}
		
		if(!isset($modules)) {
			$modules = $this->owner->AddOns("ClassName = 'ModulePage'");
		}
		return $modules;
	}
	
	function WidgetPageHolder() {
		return DataObject::get_one('WidgetPageHolder');
	}
	
	function ThemeHolder() {
		return DataObject::get_one('ThemeHolder');
	}
	
	function ModuleHolder() {
		return DataObject::get_one('ModuleHolder');
	}

}
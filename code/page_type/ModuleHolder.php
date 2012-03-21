<?php

class ModuleHolder extends Page {
	
	static $default_child = 'ModulePage';
	
	static $db = array(
		"RightContent" => "HTMLText",
		"SupportedByExplanation" => "HTMLText",
		"AddContent" => 'HTMLText'
	);
	
	static $many_many = array(
		'Reviewers' => 'Member'
	);

	static $default_records = array(
		array('Title' => "Modules")
	);
	
	static $defaults = array(
		'AddContent' => "
		<h3>How do I submit a module to the SilverStripe directory?</h3>

		<p>Complete and submit the form below. While you are working on it, you can save it as draft and return to it later via the <a href=\"ForumMemberProfile/edit\">link on your profile page</a>.</p>

		<h3>What happens after I submit my module?</h3>	

		<p>Our module reviewers at SilverStripe are notified, and they will review your submission and contact you if they have questions. Please note that we are verifying that your module will install, but we won't do a full code review.</p> 
		<p>You'll be notified when your module has been listed on the site. We try and approve new submissions quickly but please know that it typically takes at least 4 weeks for your module to appear on the SilverStripe website. If you have questions, please contact <a href=\"mailto:modules@silverstripe.org\">modules@silverstripe.org</a>.</p>

		<h3>What if I need to make changes to my module?</h3>	

		<p>Once your module is listed on the SilverStripe website, you can edit it via the <a href=\"ForumMemberProfile/edit\">link on your profile page</a>. Changes to previously approved modules  will go into effect directly, without the need for further approval from SilverStripe.</p>"
	);
	
	function getCMSFields() {
		$fields = parent::getCMSFields();
		$fields->addFieldToTab("Root.Content.Right", new HTMLEditorField("SupportedByExplanation", "'Supported By' Explanatory Text"));
		$fields->addFieldToTab("Root.Content.Right", new HTMLEditorField("RightContent", "Right content"));
		$fields->addFieldToTab('Root.Content.Main', new TagField('Reviewers', 'Reviewers', '', 'ModuleHolder', 'Nickname'), 'Content');
		$fields->addFieldToTab("Root.Content.Main", new HTMLEditorField("AddContent", "Content for 'add' page"));
		
		return $fields;
	}
	
	/**
	 * Returns what background colour is needed to be applied to the template and menu2
	 * @return String
	 */
	function BgColor() {
		return 'black';
	}	
}

class ModuleHolder_Controller extends Page_Controller {
	protected $sorts = array(
		'name' => 'Title',
		'added' => 'Created DESC',
		'creator' => 'MIN(`Member`.Nickname), `Member`.Nickname ASC',
		'supportlevel' => 'Maintainer'
	);
	
	
	function Modules() {
		//sort
		$start = isset($_GET['start']) ? (int) $_GET['start'] : 0;
		if(isset($_GET['sort']) && isset($this->sorts[$_GET['sort']])) {
			$sort = $this->sorts[$_GET['sort']];
		}else{
			$sort = 'Title';	
		}

		//filter
		$baseClass = Versioned::current_stage()=='Live'?"SiteTree_Live":"SiteTree";
		$SQL_filter = $baseClass.".ParentID = '".$this->ID."'";
		if(isset($_GET['KeyWordSearch']) && $_GET['KeyWordSearch']){
			$SQL_keywordSearch = Convert::raw2sql(trim($_GET['KeyWordSearch']));
			$SQL_keyword_filter = array();
			foreach(AddOnPage::$key_word_searchable as $f){
				$SQL_keyword_filter[]= $f." LIKE '%{$SQL_keywordSearch}%'";
			}
			$SQL_filter .= " AND (".implode(" OR ", $SQL_keyword_filter).")";
		}
		if(isset($_GET['SupportLevelField'])){
			$SQL_supportLevels = implode("','", Convert::raw2sql($_GET['SupportLevelField']));
			$SQL_filter .= " AND Maintainer IN ('$SQL_supportLevels')";
		}else{
			if(empty($_GET['sort'])){ //first load
				//filter on supported by SilverStripe and Community only
				$SQL_filter .= " AND Maintainer IN ('".implode("','", array("SilverStripe","Community"))."')";
			}else{
				return new ArrayData(
					array(
						'ModulePages' => null,
						'Submitted' => true,
					)
				);
			}
		}
		if(isset($_GET['SSversionsField']) && $_GET['SSversionsField']){
			$SQL_versions = Convert::raw2sql($_GET['SSversionsField']);
			$SQL_ssVersion_filter = array();
			$SQL_ssVersion_filter[]= "AddonRelease.CompatibleSilverStripeVersions = '$SQL_versions'";
			$SQL_ssVersion_filter[]= "AddonRelease.CompatibleSilverStripeVersions LIKE '%,$SQL_versions,%'";
			$SQL_ssVersion_filter[]= "AddonRelease.CompatibleSilverStripeVersions LIKE '%,$SQL_versions'";
			$SQL_ssVersion_filter[]= "AddonRelease.CompatibleSilverStripeVersions LIKE '$SQL_versions,%'";
			if(preg_match('/^([0-9]+[\.][0-9]+).*$/', $_GET['SSversionsField'], $matches)){
				if($matches[1]){
					$SQL_ssVersion_filter[]= "AddonRelease.CompatibleSilverStripeVersions = '".$matches[1]."'";
					$SQL_ssVersion_filter[]= "AddonRelease.CompatibleSilverStripeVersions LIKE '%,".$matches[1].",%'";
					$SQL_ssVersion_filter[]= "AddonRelease.CompatibleSilverStripeVersions LIKE '%,".$matches[1]."'";
					$SQL_ssVersion_filter[]= "AddonRelease.CompatibleSilverStripeVersions LIKE '".$matches[1].",%'";
				}
			}

			$SQL_filter .= " AND (".implode(" OR ", $SQL_ssVersion_filter).")"; // treat is as "OR"
		}
		//join
		$join = "";
		if(isset($_GET['sort'])){
			if($_GET['sort'] == 'creator'){
				$join = "LEFT JOIN AddOnPage_Maintainers ON AddOnPage_Maintainers.AddOnPageID = `ModulePage`.ID	LEFT JOIN `Member` ON AddOnPage_Maintainers.MemberID = `Member`.ID";
			}
		}
		if(isset($_GET['SSversionsField']) && $_GET['SSversionsField']){
			$join .= " LEFT JOIN AddonRelease ON AddonRelease.ParentID = ".$baseClass.".ID";
		}
		
		$modules = DataObject::get("ModulePage", $SQL_filter, $sort, $join, "10 OFFSET $start");

		//special sort minSSversion
		if(isset($_GET['sort']) && $_GET['sort'] == 'minSSversion'){
			if($modules && $modules->count()) {

				foreach($modules as $module){
					$releases = $module->AddonReleases();
					if($releases && $releases->count()){
						$newReleases = array();
						foreach($releases as $release){
							$versions = explode(',', $release->CompatibleSilverStripeVersions);
							if(!empty($versions)){
								foreach($versions as $version){
									if(preg_match('/^([0-9\.?]+)[\-]?([^\d]*)([0-9]*)$/', $version, $matches)){
										$parts = array_pad(explode(".", $matches[1]), 3, 0);
										$parts[3] = $matches[2]?self::$pre_release_categories[$matches[2]]:0;
										$parts[4] = $matches[3]?$matches[3]:0;
										$versionIdx = $parts[0]*100000000 + $parts[1]*1000000 + $parts[2]*10000 + $parts[3]*100 + (int)$parts[4];
										$newReleases[$versionIdx] = $versionIdx;
									}else{
										$newReleases[999999999] = 999999999;
									}
								}
							}else{
								$newReleases[999999999] = 999999999;
							}
						}
						ksort($newReleases);
						$module->Earliest = reset($newReleases);
					}else{
						$module->Earliest = '999999999';
					}
				}

				$modules->sort('Earliest');
			}
		}

		//special sort supportlevel
		if(isset($_GET['sort']) && $_GET['sort'] == 'supportlevel'){
			if($modules && $modules->count()){
				foreach($modules as $module){
					$module->SupportLevel = AddOnPage::$support_level_map[$module->Maintainer];
				}
				$modules->sort('SupportLevel');
			}
		}
		
		if(!$modules || !$modules->count()){
			$modules = null;
		}
		if(isset($_GET['sort'])){
			$submitted = true;
		}else{
			$submitted = false;
		}
		return new ArrayData(
			array(
				'ModulePages' => $modules,
				'Submitted' => $submitted,
			)
		);
	}
	
	
	static $pre_release_categories = array(
		'alpha' => 0,
		'beta' => 1,
		'rc' => 2,
	);

/*	function ThirdMenu() {
		return new DataObjectSet(array(
			new ArrayData(array(
				'Title' => 'Supported Modules',
				'Link' => $this->Link(),
				'LinkOrCurrent' => ($this->action != 'prerelease') ? 'current' : 'link',
				'IsCurrent' => ($this->action != 'prerelease'),
			)),
			new ArrayData(array(
				'Title' => 'Unsupported Modules',
				'Link' => $this->Link() . 'prerelease',
				'LinkOrCurrent' => ($this->action == 'prerelease') ? 'current' : 'link',
				'IsCurrent' => ($this->action == 'prerelease'),
			)),
		));
	}*/
	
	// The template has deleted use this function.
	/*function ModuleInfoContent() {
		return 'Our developers here at SilverStripe have reviewed and tested these modules.';
	}*/
	
	/*function prerelease() {
		return array(
			'ModuleInfoContent' => 'These modules have been submitted by the community and may or may not be maintained. Their stability may vary.'
		);
	}*/
	
	function SortOption() {
		if (isset($_GET['sort'])) {
			return $_GET['sort'];
		}
		return false;
	}
	
	function KeyWordSearchField(){
		if(isset($_GET['KeyWordSearch'])){
			$value = $_GET['KeyWordSearch'];
		}else{
			$value = "";
		}
		$field = new TextField("KeyWordSearch", " Keyword Search: ", $value);
		return "<label class=\"keywordlabel\" for=\"KeyWordSearch\">".$field->Title()."</label>".$field->Field();
	}
	
	function SupportLevelField(){
		if(isset($_GET['SupportLevelField'])){
			$values = $_GET['SupportLevelField'];
		}else{
			if(isset($_GET['sort'])){
				$values = null;
			}else{//first load
				$values = array('SilverStripe','Community');
			}
		}

		$field = new CheckboxSetField("SupportLevelField", "Supported By:", array(
			"SilverStripe"=>"SilverStripe", "Community"=>"Community", "None"=>"Not supported"), $values);
		Requirements::javascript(THEMES_DIR . '/silverstripe/javascript/HoverPopup.js');
		Requirements::css('themes/silverstripe/css/CustomisedCheckboxSetField.css');
		return "<label>".$field->Title()."</label>".$field->Field();
	}
	
	function SSVersionsField() {
		$source = AddonRelease::all_stables_plus_one_pre_if_newer_groupby_releaselines();
		$source[0] = 'any';
		ksort($source);
		$values = isset($_GET['SSversionsField']) && $_GET['SSversionsField']?$_GET['SSversionsField']:"";
		
		Requirements::javascript(THIRDPARTY_DIR .'/jquery/jquery.js');
		Requirements::javascript('sapphire/javascript/jquery_improvements.js');
		Requirements::javascript('addons/javascript/sort_options_form.js');
		Requirements::css('addons/css/sort_options_form.css');
		$field = new GroupedDropdownField('SSversionsField', "Compatible SilverStripe Versions:", $source, $values);
		
		return "<label>".$field->Title()."</label>".$field->Field();
	}

	function manage() {
		$to = 'modules@silverstripe.org';
		
		return new AddOnCRUD($this->data(), "ModulePage", $to,
			// Add form content
			array(
				'Title' => 'Submit a module',
				'Content' => $this->dataRecord->AddContent
			), 
			// Edit form content
			array(
				'Title' => 'Edit a module',
				'Content' => '<p>Update the details of your module in the form below.</p>',
			),
			// After add/edit content
			array(
				'Title' => 'Thanks for your submission',
				'Content' => "<p>You'll be notified when your module has been listed on the site. We try and approve new submissions quickly but please know that it typically takes at least 4 weeks for your module to appear on the SilverStripe website. If you have questions, please contact <a href=\"mailto:modules@silverstripe.org\">modules@silverstripe.org</a></p>"
			)
		);
	}	
}
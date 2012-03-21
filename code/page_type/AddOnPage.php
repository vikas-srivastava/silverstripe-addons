<?php

/**
 * Base class for all add-ons: ModulePage, WidgetPage, and ThemePage
 */
class AddOnPage extends Page {
	
	static $db = array(
		'Abstract' => 'HTMLText',
		'Maintainer' => "Enum('None,SilverStripe,Community', 'Community')",
	/** 
	 * We will leave these for compatabilty with WidgetPage and ThemePage in short term, but 
	 * for a long term, all these should be removed and will be defined in {@link: AddonReleaase} level.
	 * These three fields have been transfered into {@link: AddonRelease} level, the usage should be like
	 * $module->CurrentRelease()->ReleaseVersion in php code and CurrentRelease.ReleaseVersion in ss template
	 */
		'CurrentVersion' => 'Varchar(20)',
		'ReleaseDate' => 'Date',
		'CompatibleSilverStripeVersions' => 'Varchar(255)', 
		
	// newly added to distinguish if a module has been submitted.
		'Submitted' => "Boolean",
	);
	
	static $has_one = array(
		/** 
		 * We will leave these for temperarily compatabilty with WidgetPage and ThemePage, but 
		 * for a long term, all these should be removed and will be defined in {@link: AddonReleaase} level.
		 */
		'DownloadFile' => 'File',
	);
	
	static $has_many = array(
		'AddonReleases' => 'AddonRelease'
	);
	
	static $many_many = array(
		'Maintainers' => 'Member',
	);

	/**
	 * @var string $url_suffix 
	 */
	protected $urlSuffix = '-addonpage';
	
	static $support_level_map = array(
		'SilverStripe' => 1,
		'Community' => 2,
		'None' => 3,
	);
	
	static $key_word_searchable = array(
		"Title","MenuTitle","Content","MetaTitle","MetaDescription","MetaKeywords","Abstract"
	);
	
	
	static $sphinx = array(
		"search_fields" => array("Title", "Abstract")
	);

	function getCMSFields() {
		Requirements::javascript(THEMES_DIR . '/silverstripe/javascript/VersionControlChoice.js');

		$fields = parent::getCMSFields();

		$fields->removeFieldFromTab("Root.Content", "Right");
		$fields->removeFieldFromTab("Root.Content.Main", "TitleImage");
		$fields->removeFieldFromTab("Root.Content.Main", "TitleImageAlt");
		$fields->removeFieldFromTab("Root.Content", "Metadata");
		$fields->removeByName("Videos");
		$fields->removeFieldFromTab("Root.Content.Main", "MenuTitle");

		// Copied from SiteTree.php
		$urlField = new FieldGroup(_t('SiteTree.URL', "URL"),
			new LabelField('BaseUrlLabel',Director::absoluteBaseURL()),
			new UniqueRestrictedTextField("URLSegment","URLSegment","SiteTree",_t('SiteTree.VALIDATIONURLSEGMENT1', "Another page is using that URL. URL must be unique for each page"),"[^A-Za-z0-9-]+","-",_t('SiteTree.VALIDATIONURLSEGMENT2', "URLs can only be made up of letters, digits and hyphens."),"","","",50),
			new LabelField('TrailingSlashLabel',"/")
		);
		$fields->addFieldToTab("Root.Content.Main", $urlField, "Content");

		$maintainersField = new TagField('Maintainers','Maintainers (separated by commas)','','ModulePage','Nickname'); 
		$maintainersField->setSeparator(',');
		$fields->addFieldsToTab('Root.Content.Source', array(
			new HeaderField("SourceHeader", "Information about the module's codebase"),
			$maintainersField,
			new TextField('CurrentVersion', 'Current version number e.g. 2.2.2'),
			new CalendarDateField('ReleaseDate', 'Release date'),
			new FileIFrameField('DownloadFile', 'File download'),
		));
		
		return $fields;
	}
	
	protected $holderPageTypes = array(
		'WidgetPage' => 'WidgetPageHolder',
		'ModulePage' => 'ModuleHolder',
		'ThemePage' => 'ThemeHolder',
	);


	function CanEdit($member = null) {
		if(!$member) $member = Member::currentUserID();
		$memberID = (int)(is_object($member) ? $member->ID : $member);
		if($member) {
			if(Permission::checkMember($memberID, "ADMIN")) return true;
			return (bool)DB::query("SELECT ID FROM AddOnPage_Maintainers 
				WHERE AddOnPageID = " . (int)$this->ID . " AND MemberID = " . $memberID)->value();
		}
	}
	
	function EditLink() {
		$parent = DataObject::get_one($this->holderPageTypes[$this->class]);
		
		if(Versioned::get_one_by_stage($this->ClassName, "Live", "SiteTree_Live.ID = '".$this->ID."'", false) ){
			$action = "edit";
		}else{
			$action = "draft";
		}
		return Controller::join_links($parent->Link(), 'manage/'.$action.'/'.$this->ID);
	}
	
	/**
	 * return the link with <a> tag to edit this module, the link should be different according to if the 
	 * module has been approved
	 */
	function EditLinkWithATag(){
		$parent = DataObject::get_one($this->holderPageTypes[$this->class]);
		
		if(Versioned::get_one_by_stage($this->ClassName, "Live", "SiteTree_Live.ID = '".$this->ID."'", false) ){
			$action = "edit";
			$actionText = "edit";
		}else{
			$action = "draft";
			$actionText = "edit";
		}
		$href = Controller::join_links($parent->Link(), 'manage/'.$action.'/'.$this->ID);
		return "<a href=\"$href\">$actionText</a>";
	}
	
	function IsApproved(){
		return Versioned::get_one_by_stage($this->ClassName, "Live", "SiteTree_Live.ID = '".$this->ID."'", false);
	}
	
	function onBeforeWrite() {
		if(!$this->Maintainer) $this->Maintainer = "Community";
		
		if(isset($this->holderPageTypes[$this->class])) {
			$holderClass = $this->holderPageTypes[$this->class];
			if(!$this->ParentID && ($parent = DataObject::get_one($holderClass))) {
				$this->ParentID = $parent->ID;
			}
			// make sure the URL segment doesnt clutter our namespace
			if((!$this->URLSegment || $this->URLSegment == 'new-page') && $this->Title) {
				$this->URLSegment = $this->generateURLSegment($this->Title) . $this->urlSuffix;
			}
		}
				
		parent::onBeforeWrite();
	}
	
	/**
	 * Before running doPublish, we need to check if this page has
	 * been published before, if it hasn't, we send an email notification
	 * to the maintainer so they know it's been approved.
	 */
	function doPublish() {
		// Let's check if there's an ID for this page in the SiteTree_Live table
		$id = DB::query("SELECT ID FROM SiteTree_Live WHERE ID = {$this->ID}")->value();
		
		// mark this as submitted as it is published
		$this->Submitted = true;
		$this->write();
		
		if(!$id) {
			$maintainers = $this->getManyManyComponents('Maintainers');
			$reviewers = $this->Parent()->getManyManyComponents('Reviewers');
			$members = new DataObjectSet();
			if($maintainers) $members->merge($maintainers);
			if($reviewers) $members->merge($reviewers);
			$members->removeDuplicates();
			
			foreach($members as $member) {
				// Important: Never send emails to any "real" addresses if not in live mode
				$email = $member->Email;
				if(!Director::isLive()) {
					$email = 'sean@silverstripe.com,normann@silverstripe.com';
				}
				
				$message = "Hi {$member->FirstName},\n\n" .
					"Thank you for submitting your extension. We have published \"{$this->Title}\".\n\n" . 
					"You can view it here: " . Director::absoluteURL($this->Link()) . "\n\n" .
					"Thanks,\n" .
					"the SilverStripe Team";
				
				mail(
					$email,
					"\"{$this->Title}\" has been approved on silverstripe.org",
					$message,
					"From: noreply@silverstripe.com"
				);
			}
		}
		
		parent::doPublish();
	}
	
	/**
	 * @return AddonRelease
	 */
	function CurrentRelease(){
		return DataObject::get_one("AddonRelease", "Status = 'Current' AND ParentID = '".$this->ID."'");
	}
	
	/**
	 * @return AddonRelease
	 */
	function OlderReleases(){
		return DataObject::get("AddonRelease", "Status = 'Older' AND ParentID = '".$this->ID."'");
	}
	
	function Download() {
		if($latest = $this->LatestReleaseDownload()) return $latest;
		else if($git = $this->MasterDownload()) return $git;
		else return $this->TrunkDownload();
	}
	
	function LatestReleaseDownload(){
		$currentRelease = $this->CurrentRelease();
		if($currentRelease){
			return $currentRelease->Download();
		}
	}
	
	function OlderReleaseDownloads(){
		$downloads = new DataObjectSet();
		if($downloads && $downloads->count()) foreach($this->OlderReleases() as $r){
			$downloads->push($r->Download());
		}
		return $downloads;
	}

	/** 
	 * Download for generating a new zip out of the trunk of a svn repo 
	 * or git repo.
	 */
	function TrunkDownload() {
		if (isset($_REQUEST['u'])) {
			$u = Convert::raw2sql($_REQUEST['u']);
			if ($u && strlen($u) > 0) {
				//SECURITY: check against AddOnReleases class' svn url and make sure
				$release1 = DataObject::get_one("AddOnRelease","SubversionURL = '$u'");
				$release2 = DataObject::get_one("ModulePage","SubversionTrunk = '$u'");
				if (!$release1 && !$release2) user_error("Invalid URL, the following URL is not associated with any ModulePage or AddOnRelease: $u", E_USER_ERROR);
			}
		}

		return new CachedSvnArchiver($this, 'TrunkDownload', $this->SubversionTrunk, 'assets/modules/trunk');
	}

	/** 
	 * Download for generating a new zip out of a git repo 
	 */
	function MasterDownload() {
		if($this->GitRepo) {
			return new CachedGitArchiver($this, 'master', 'HEAD', $this->GitRepo, 'assets/modules/master');
		}
	}
}
<?php

class ModulePage extends AddOnPage {
	
	static $db = array(
		'URLToDemo' => 'URL',
		'SubversionTrunk' => 'URL',
		'GitRepo' => 'URL',
		'DevelopmentStatus' => "Enum('Alpha,Beta,Production')",
		'InstallationLink' => 'URL',
		'FAQLink' => 'URL',
		'GettingStartedLink' => 'URL',
		'FileTicketLink' => 'URL',
		'ExistingTicketsLink' => 'URL',
		'BugTrackerLink' => 'URL',
	);
	
	static $has_one = array(
		'ScreenshotImage' => 'Image',
		'SupportForum' => 'Forum',
	);

	static $searchable_fields = array(
		"Title",
		"DevelopmentStatus",
		"Maintainer",
	);
	
	static $sphinx = array(
		"search_fields" => array("Title", "Abstract")
	);

	static $summary_fields = array(
		"Title" => "Module name",
		"DevelopmentStatus" => "Development status",
		"Maintainer" => "Maintainer",
	);
	
	static $field_labels = array(
		"Title" => "Module name",
		"Content" => "Detailed description",
		"ScreenshotImage" => "Screenshot image",
		"InstallationLink" => "Link to installation docs",
	);
	
	static $singular_name = "Module";
	
	protected $urlSuffix = '-module';

	static function modules_checkboxset_field($fieldName) {
		$allModules = DataObject::get('ModulePage');
		$sourceMap = $allModules ? $allModules->map('ID', 'Title') : array();
		
		$field = new CheckboxSetField(
			$fieldName,
			'Modules',
			$sourceMap
		);
		
		return $field;
	}
	
	function LinkExist($link){
		$theLink = trim($this->$link);
		return $theLink != 'http://' && !empty($this->$link) && $this->$link;
	}
	
	function AlternativeSupportForum(){
		$alternative = DataObject::get_one("Forum", "Title = 'All other Modules'");
		if(!$alternative) $alternative = DataObject::get_one("Forum", "Title = 'General Questions'");
		return $alternative;
	}
	
	function searchableFields() {
		$fields = parent::searchableFields();
		$fields['DevelopmentStatus']['field'] = new CheckboxSetField("DevelopmentStatus", 
			"Development Status", $this->obj('DevelopmentStatus')->enumValues());
		$fields['DevelopmentStatus']['filter'] = 'ExactMatchMultiFilter';
		$fields['Maintainer']['field'] = new CheckboxSetField("Maintainer", 
			"Maintainer", $this->obj('Maintainer')->enumValues());
		$fields['Maintainer']['filter'] = 'ExactMatchMultiFilter';
		return $fields;
	}
	
	
	function getCMSFields() {
		Requirements::javascript(THIRDPARTY_DIR . '/jquery/jquery.js');
		Requirements::javascript(THIRDPARTY_DIR . '/jquery-livequery/jquery.livequery.js');
		Requirements::javascript(THEMES_DIR . '/silverstripe/javascript/VersionControlChoice.js');

		$fields = parent::getCMSFields();
		$maintainerMap = $this->obj('Maintainer')->enumValues();

		$forumDOS = DataObject::get('Forum');
		$forumList = $forumDOS ? $forumDOS->toDropdownMap("ID", "Title") : array("" => "(None available)");
		$forumDefault = DataObject::get_one("Forum", "Title = 'All other Modules'");
		if(!$forumDefault) $forumDefault = DataObject::get_one("Forum", "Title = 'General Questions'");
		if($forumDefault) $forumDefaultID = $forumDefault->ID;
		else $forumDefaultID = null;
		$fields->dataFieldByName("Title")->Title = "Module name";
		$fields->addFieldToTab("Root.Content.Main", new HTMLEditorField('Abstract','Abstract',5), 'Content');
		$fields->addFieldToTab("Root.Content.Main", new ImageField('ScreenshotImage', 'Screenshot of module', null, null, null, 'ModuleImages'), 'Content');

		$fields->removeFieldsFromTab('Root.Content.Source', array('DownloadFile','CurrentVersion','ReleaseDate'));
		
		$fields->addFieldsToTab('Root.Content.Source', array(
			new DropdownField('Maintainer', 'Maintenance Status', $maintainerMap),
			new DropdownField("DevelopmentStatus", "Development status of your module", $this->obj('DevelopmentStatus')->enumValues()),
			new HeaderField("CodebaseSVNaccess", "Codebase Git or Subversion access", 3),
			new LiteralField("SvnExplanaton", "<p>If you use version control, we recommend that you provide Git or Subversion details for accessing your module codebase.
				Our module manager will make daily master/trunk builds automatically. The first build will be available a few minutes after you save this page. If it does become
				available please check that the URL you provided is valid and then try saving this page again. </p><p>Please enter either a Git URL or a Subversion URL below:</p>"),
			new URLField("GitRepo", "Git repository URL (read-only)"),
			new LiteralField("GitExplanation", "(If you are a GitHub users, please enter a GitHub read-only URL in the field above. See <a href='http://help.github.com/remotes/#fetching'>this guide</a> for an explanation of the different kinds of URLs GitHub provides.)"),
			new URLField("SubversionTrunk", "Subversion trunk URL"),
		));
		
		if($this->ID){
			$fields->addFieldsToTab('Root.Content.Source', array(
				new LiteralField("clearfix", "<div class=\"clearfix\"></div>"),
				new HeaderField("ReleasesHeader", "Releases"),
				new LiteralField("ReleasesTableHeading", "<p>Use the following table to manage/add/delete your releases</p>"),
				new ComplexTableField(Controller::curr(), "Releases", "AddonRelease",
					array('ReleaseVersion'=>"Version Number", 'ReleaseDate'=>"Release Date", 'CompatibleSilverStripeVersions' => "SilverStripe version tested/required", 'Status' => "Current / Older"),
					null,
					"ParentID = '".$this->ID."'"				
				)
			));
		}

		$fields->addFieldsToTab('Root.Content.Links', array(
			new HeaderField("SourceHeader", "Other resources for your module"),
			new LiteralField("SourceExpn", "<p>If you have other useful resources for your module, such as a demo or documentation, please add the links below.</p>"),
			new URLField('URLToDemo', 'Link to demo site'),
			new URLField('InstallationLink', 'Link to installation docs'),
			new URLField('FAQLink', 'Link to FAQs'),
			new URLField('GettingStartedLink', 'Link to "Getting started" docs'),
			new URLField('FileTicketLink', 'Link to file a new bug ticket'),
			new URLField('ExistingTicketsLink', 'Link to view existing bug tickets'),
			new URLField('BugTrackerLink', 'Link to a non-SilverStripe bug tracker for your module'),
			new DropdownField('SupportForumID', "Support forum", $forumList, $forumDefaultID),
		));
		
		return $fields;
	}
	
	/**
	 * Get fields for front-end forms in the module management section
	 */
	function getFrontEndFields() {
		Requirements::block(CMS_DIR . "/javascript/SitetreeAccess.js");
		Requirements::block(SAPPHIRE_DIR . '/javascript/UpdateURL.js');
		
		// Ignore all the CMSey fields
		$fields = new FieldSet(
			$this->getCMSFields()->fieldByName('Root')->fieldByName('Content')
		);
		$fields->fieldByName('Content')->fieldByName('Main')->removeByname('URL');

		// Put front-end ready image fields in
		$fields->removeFieldFromTab("Content.Main", "ScreenshotImage");
		$fields->addFieldToTab("Content.Main", new SimpleImageField("ScreenshotImage", "Screenshot image"), "Content");
		$fields->removeFieldFromTab("Content.Source", "DownloadFile");

		$fields->removeFieldFromTab("Content.Source", "Maintainers");
		$fields->removeFieldFromTab("Content.Source", "Maintainer");
		$fields->removeByName("GoogleSitemap");

		if($this->ID){
			//We have to add a hidden ID for ComplexTableField "Releases" to work;
			$fields->push(new HiddenField('ID', 'ID', $this->ID));	
		}else{
			$fields->addFieldToTab("Content.Source", new TextField("Release[ReleaseVersion]", 'Release version', 'Unreleased'));
			$fields->addFieldToTab("Content.Source", new DateField("Release[ReleaseDate]", 'Release date', date('DD/MM/YYYY')));
			$fields->addFieldToTab("Content.Source", new HeaderField("CompatibleSilverStripeHeader", "Compatible SilverStripe versions", 3));
			$source = AddonRelease::all_stables_plus_one_pre_if_newer_groupby_releaselines();
			$fields->addFieldToTab("Content.Source", new TreeCheckboxSetField("Release[CompatibleSilverStripeVersions]", "", $source));
			$fields->addFieldToTab("Content.Source", new URLField("Release[SubversionURL]", "Stable Release svn URL", ""));
			$fields->addFieldToTab("Content.Source", new HeaderField("ReleaseSvnHeader2", "If your module isn't in subversion", 3));
			$fields->addFieldToTab("Content.Source", new LiteralField("ReleaseSvnExplanaton2", "<p>If your module isn't in subversion you can upload a .tar.gz file.</p>"));
			$fields->addFieldToTab("Content.Source", new FileIFrameField('Release[DownloadFile]', 'Upload file'));
		}

		return $fields;
	}
	
	function getFrontEndFieldsForAddForm(){
		$fields = $this->getFrontEndFields();
		$main = $fields->fieldByName("Content.Main");
		$source = $fields->fieldByName("Content.Source");
		$links = $fields->fieldByName("Content.Links");
		
		//move field for "Installation docs" up into the first section
		$installationlink = $links->fieldByName("InstallationLink");
		$links->removeByName("InstallationLink");
		$main->push($installationlink);
		
		$formFields = new FieldSet(
			new HeaderField("MainContent", "Key module information"),
			new LiteralField("MainContentExplanatory", "<p>All fields in this section are required.</p>", true),
			new CompositeField($main->Fields()),
			$save1 = new InlineFormAction('save1', 'Save'),
			new CompositeField($source->Fields()),
			$save3 = new InlineFormAction('save3', 'Save'),
			new CompositeField($links->Fields())
		);
		
		$save1->includeDefaultJS = false;
		$save3->includeDefaultJS = false;
		return $formFields;
	}
	
	function getValidator() {
		$required = array(
			'Title',
			'Abstract',
			'Content',
			'InstallationLink'
		);
				
		if(!$this->ID) {
			$required = array_merge($required, array('Release[ReleaseDate]', 'Release[ReleaseVersion]'));
		}
		
		$validator = new RequiredFields(
			$required
		);
		
		return $validator;
	}
	
	
	function ScreenshotImageWidth(){
		if($this->ScreenshotImage() && $this->ScreenshotImage()->getWidth() > 520) return true;
		return false;
	}


	////////////////////////////////////////////////////////////////////////////////////////////////////////
	
	/**
	 * Returns what background colour is needed to be
	 * applied to the template and menu2
	 * @return string
	 */
	function BgColor() {
		return 'black';
	}

	function onBeforeWrite() {
		if ($this->GitRepo) {
			$this->GitRepo = CachedGitArchiver::filterGitURL($this->GitRepo);
		}

		parent::onBeforeWrite();
	}

	/** Builds an archive of the current git release */
	function onAfterWrite() {
		parent::onAfterWrite();

		//generate the release zip file using a message queue
		if ($this->GitRepo && strlen($this->GitRepo) > 1) {
			$invo = new MethodInvocationMessage("CachedGitArchiver", "generateGitArchive", $this->GitRepo);  //build master HEAD
			MessageQueue::send("generateReleaseQueue", $invo);
		}
	}
	
	/**
	 * Updates any url fields which only contain http:// to be null instead so that
	 * editing a page doesn't throw validation errors now that we use URLField
	 */
	function requireDefaultRecords() {
		parent::requireDefaultRecords();
		
		$fields = array(
			'ExistingTicketsLink', 'URLToDemo', 'InstallationLink', 
			'FAQLink', 'GettingStartedLink', 'FileTicketLink',
			'BugTrackerLink'
		);
	
		foreach($fields as $field) {
			DB::query(sprintf('
				UPDATE ModulePage SET %1$s = NULL WHERE %1$s = \'http://\'', 
				$field
			));
			
			DB::query(sprintf('
				UPDATE ModulePage_Live SET %1$s = NULL WHERE %1$s = \'http://\';', 
				$field
			));
		}
		
		DB::alteration_message('Updated Module Links', 'created');
	}
		
}

class ModulePage_Controller extends Page_Controller {

	static $allowed_actions = array(
		'TrunkDownload',
		'MasterDownload',
		'Download',
		'FooterNewsletterSubscribeForm'
	);
}
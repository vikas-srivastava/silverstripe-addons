<?php

/**
 * Due to a module having multiple releases and each release might required different Silverstripe core version,
 * the module release class will be introduced, but to make more generic and compatable with {@link WidgetPage} and 
 * {@link @ThemePage} in a long run, we here name it as AddonRelease, we could have ModuleRelease, WidgetRelease and
 * ThemeRelease extending from AddonRelease if necessary, ie, this is a basical class for Release object.
 */

class AddonRelease extends DataObject {
	//basical db field
	static $db = array(
		/**
		 * The following three fields migrated from {@linkAddOnPage} level
		 * In long term run, those fields in {@link AddOnPage} level should be removed and out of usage.
		 * The time being, {@link ThemePage} / {@link WedgetPage} are still using these field in {@link AddOnPage} level.
		 */
		'ReleaseVersion' => 'Varchar(20)',
		'ReleaseDate' => 'Date',
		'CompatibleSilverStripeVersions' => 'Varchar(255)',
		
		'Status' => "Enum('Current,Older')",

		"GitURL" => 'URL',
		"GitBranch" => 'Varchar(255)',
		"GitTag" => 'Varchar(255)',
		"SubversionURL" => 'URL',
		"DownloadLinkURL" => 'URL',
		"VersionControlChoice" => 'Enum("Git,Subversion,Link,FileUpload","Git")',
		
		//for data migration purpose only, will be delete after data migration finished.
		'TempCompatibleVersionsLegacy' => 'Varchar(255)',
	);
	
	//basical has_one relatioin
	static $has_one = array(
		'DownloadFile' => 'File',
		'Parent' => "AddOnPage",
	);

	static $field_labels = array(
		"GitURL" => "Git Repository URL",
		"GitBranch" => "Git Branch Name",
		"GitTag" => "Git Tag Name",
	);

	static $summary_fields = array(
		'Parent.Title',
		'ReleaseVersion',
		'ReleaseDate',
		'CompatibleSilverStripeVersions',
		'Status',
		'SubversionURL',
		'TempCompatibleVersionsLegacy',
	);

	static $defaults = array(
		"GitBranch" => 'master',
		"GitTag" => 'HEAD',
	);
	
	static $default_sort = "Created DESC";
	
	function getCMSFields(){
		$fields = parent::getCMSFields();

		$fields->removefieldsfromTab("Root.Main", array('ParentID','DownloadFile', 'CompatibleSilverStripeVersions', 'ReleaseDate', 'TempCompatibleVersionsLegacy', 'VersionControlChoice'));
		
		$fields->insertBefore(new CalendarDateField('ReleaseDate', 'Release date'), 'Status');

		if($this->TempCompatibleVersionsLegacy){
			$fields->insertBefore(new TextField('TempCompatibleVersionsLegacy', "please set the 'Compatible SilverStripe Versions' according to this legacy data"), 'GitURL');
		}
		
		$source = self::all_stables_plus_one_pre_if_newer_groupby_releaselines();
		$fields->insertBefore(new TreeCheckboxSetField('CompatibleSilverStripeVersions', "Compatible SilverStripe Versions", $source), 'GitURL');
		
		$fields->insertBefore(new HeaderField("SvnHeader", "Accessing the source code"), 'GitURL');
		$fields->insertBefore(new LiteralField("SvnExplanaton", "<p>We recommend that you provide Git or Subversion details for accessing your module.
		- Our module manager will build the release automatically. However, if your module isn't in a version control system you can upload or link to a .tar.gz file.</p>"),'GitURL');

		$fields->insertBefore(new OptionsetField('VersionControlChoice', "Choose a version control system or upload source",array(
			'Git'=>'Git',
			'Subversion'=>'Subversion',
			'Link'=>'Direct link to archive',
			'FileUpload'=>'Upload a file',
		)), 'GitURL');

		$fields->insertAfter(new LiteralField("GitExplanaton", "<p id='GitExplanaton'>The download should generate in less than a minute. If after few minutes the link on the module page is still invalid, then please check you entered the correct URL, branch and tag values and re-save the release.</p>"
		),'GitTag');


		$downloadFile = new FileIFrameField('DownloadFile', 'File download');
		if(!is_a(Controller::curr(),"LeftAndMain")) {//not used in back-end cms
			//as we know the module add/edit form will call this getCMSFields() func for its front-end form as well;
			//$downloadFile->setServerFileBrowsing(false);
		}
		$fields->addFieldToTab("Root.Main", $downloadFile);

		return $fields;
	}
	
	function getCMSValidator(){
		return $this->getValidator();
	}
	
	function getValidator() {
		return $validator = new RequiredFields(
			'ReleaseVersion',
			'ReleaseDate',
			'CompatibleSilverStripeVersions',
			'Status'
		);
	}
	
	function getHasSubversionUrl() {
		$url = $this->SubversionURL;
		
		$url = str_replace(array('http', ':', '/', 'svn'), '', $url);

		return (strlen($url) > 0);
	}

	function getHasGitUrl() {
		$url = $this->GitURL;

		$url = str_replace(array('http', ':', '/', 'git'), '', $url);
		
		return (strlen($url) > 0);
	}
	
	static function all_stables_plus_one_pre_if_newer_groupby_releaselines(){
		$ret = array();
		$page = new PreReleasesPage(array('SubversionBaseURL' => "http://svn.silverstripe.com/open/phpinstaller/tags/"));
		$childDirs = SvnInfoCache::for_url("http://svn.silverstripe.com/open/phpinstaller/tags/")->childDirs();
		$rs = DataObject::get("MajorRelease");
		if($rs) foreach($rs as $r){

			$rule = '/^'.str_replace(".", "\.", $r->ReleaseIdentifier).'[.\0-9]*$/';
			foreach($childDirs as $name => $info) {
				if(preg_match($rule, $name)) {
					$ret[$r->ReleaseIdentifier][$name] = $name;
				}
			}
			$s = $page->latestStable($r->ReleaseIdentifier);
			$p = $page->latestPrerelease($r->ReleaseIdentifier);
			
			if($s) {
				if($p){
					$latest = $page->compareToGetLatest($s, $p);
				}
			}else{
				if($p){
					$latest = $p;
				}
			}
			
			if(isset($latest)){
				$ret[$r->ReleaseIdentifier][basename($latest['url'])]=basename($latest['url']);
			}
		}
		
		if(!empty($ret)){
			// add special case 2.2.3 which is not in tags/ as stable, but being in tags/rc
			$ret['2.2']['2.2.3'] = '2.2.3';
		}
		ksort($ret);
		foreach($ret as $releaseline => $stables){
			ksort($ret[$releaseline]);
		}
		return $ret;
	}
	
	function Download(){
		if($this->getHasGitUrl()) {
			$escaped_branch = Convert::raw2sql($this->GitBranch);
			$escaped_tag = Convert::raw2sql($this->GitTag);
			$cachedGit = new CachedGitArchiver($this->Parent(), $escaped_branch, $escaped_tag, $this->GitURL, 'assets/modules/stable');

			if ($this->VersionControlChoice == "Git") return $cachedGit;
		}
		if($this->getHasSubversionUrl()) {
			$cachedSvn = new CachedSvnArchiver($this->Parent(), 'Download', $this->SubversionURL, 'assets/modules/stable');
			
			if ($this->VersionControlChoice == "Subversion") return $cachedSvn;
		}
		if ($this->DownloadLinkURL) {
			$cachedDownloadLink = new CachedDownloadLink($this->Parent(), Convert::raw2xml($this->DownloadLinkURL));

			if ($this->VersionControlChoice == "Link") return $cachedDownloadLink;
		}
		if($this->DownloadFile()){
			$file = $this->DownloadFile();
			if ($this->VersionControlChoice == "FileUpload") return $file;
		}

		//fallback downloads
		if (isset($cachedGit) && $cachedGit) return $cachedGit;
		if (isset($cachedSvn) && $cachedSvn) return $cachedSvn;
		if (isset($cachedDownloadLink) && $cachedDownloadLink) return $cachedDownloadLink;
		if (isset($file) && $file) return $file;
	}
	
	function getLiteralCompatibleSilverStripeVersions(){
		$source = self::all_stables_plus_one_pre_if_newer_groupby_releaselines();
		$mapping = array();
		if(!empty($source)) {
			foreach($source as $releaseline => $versions){
				if(!empty($versions)) {
					$dummy = $versions;
					foreach($versions as $version){
						if(count($dummy) === count($versions)){
							$mapping[$releaseline] = implode(",",$dummy);
						}elseif(count($dummy) > 1){
							$mapping[$version."+"] = implode(",",$dummy);
						}
						unset($dummy[$version]);
					}
				}
			}	
		}
		
		return str_replace(",", ", ", str_replace(array_values($mapping), array_keys($mapping), $this->CompatibleSilverStripeVersions));
	}
	
	function onBeforeWrite(){
		if ($this->GitURL) {
			$this->GitURL = CachedGitArchiver::filterGitURL($this->GitURL);
		}

		parent::onBeforeWrite();
		
		if($this->CompatibleSilverStripeVersions && $this->TempCompatibleVersionsLegacy){
			unset($this->TempCompatibleVersionsLegacy);
			$this->TempCompatibleVersionsLegacy = null;
		}
	}
	
	function onAfterWrite(){
		if($this->Status == 'Current'){
			foreach($this->Parent()->AddonReleases() as $release){
				if($release->ID != $this->ID && $release->Status == 'Current'){
					$release->Status = 'Older';
					$release->write();
				}
			}
		}
		parent::onAfterWrite();

		//generate the release zip file using a message queue
		if ($this->VersionControlChoice == "Git") {
			$invo = new MethodInvocationMessage("CachedGitArchiver", "generateGitArchive", $this->GitURL, $this->GitBranch, $this->GitTag, "assets/modules/stable");
			MessageQueue::send("generateReleaseQueue", $invo);
		}
	}
	
	function CanEdit($member = null){
		$addon = $this->Parent();
		if($addon){
			return $addon->CanEdit($member);
		}
	}
	
	function CanDelete($member = null){
		return $this->canEdit($member);
	}
}
?>
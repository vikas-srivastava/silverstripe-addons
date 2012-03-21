<?php

class PreReleasesPage extends DownloadPage {
	static $db = array(
		'SubversionBaseURL' => 'Text',
	);
	
	// Don't change the value of each release categories. They are used to compute the latest 
	// pre-realse version, given a major release.
	
	static $pre_release_categories = array(
		'alpha' => 0,
		'beta' => 1,
		'rc' => 2,
	);
	
	function getCMSFields(){
		$fields = parent::getCMSFields();
		
		$releasesTable = new TableField("SilverStripeCoreMajorReleases",
			"MajorRelease",
			array("ReleaseIdentifier" => "Identifier"),
			array("ReleaseIdentifier" => 'TextField')
		);

		$fields->addFieldToTab("Root.Content.Main", new HeaderField("MajorReleaseTable", "Use this table to add or delete a SS core release line"), 'Content');
		$fields->addFieldToTab("Root.Content.Main", $releasesTable, 'Content');
		return $fields;
	}
	
	function Downloads(){
		$releaseLines = MajorRelease::AllMajorReleases();
		if($releaseLines && $releaseLines->count()){
			$downloads = new DataObjectSet();
			foreach($releaseLines as $releaseLine){
				$s = $this->latestStable($releaseLine->ReleaseIdentifier);
				$p = $this->latestPrerelease($releaseLine->ReleaseIdentifier);
				if($s) {
					if($p){
						$latest = $this->compareToGetLatest($s, $p);
					}else{
						// there is the latest stable release whitout latest pre-release,
						// we do nothing though it is very rare and almost never happened.
					}
				}else{	//there is no latest stable release yet
					if($p){
						$latest = $p;
					}else{
						// no latest stable and not latest prerelease
						// we do nothing though it is very rare and almost never happened.
					}
				}

				if(isset($latest)){
					$url = $latest['url'];
					$archiver = new CachedSvnArchiver($this, "Download", $url, "assets/downloads");
					$archiver->setBaseFilename("SilverStripe");
					$dt = new SS_Datetime('ReleaseDate');
					$dt->setValue($latest['date']);

					preg_match('/^([0-9\.?]+)-[\w]+[\d]+$/', basename($latest['url']), $matches);
					$changeLogSegment = (isset($matches[1])&&$matches[1])?$matches[1]:basename($latest['url']);

					$downloads->push(new DataObject(array(
						'Download' => $archiver,
						'DownloadVersion' => basename($latest['url']),
						'ReleaseDate' => $dt,
						'ReleaseType' => 'pre-release',
						'ChangeLogSegment' => $changeLogSegment,
						'ReleaseLine' => "Latest ".$releaseLine->ReleaseIdentifier." pre-release",
						'MajorRelease' => $releaseLine->ReleaseIdentifier,
					)));
					$latest = null; // reset to null, so not to messed up with next loop
				}else{
					// the latest stable release is more recent than the lastest pre-release
					// doing nothing.
				}
			}
			return $downloads;
		}
	}
	
	function compareToGetLatest($s, $p){
		if($p['crossIdx'] > $s['crossIdx']) return $p;
	}
	
	function latestStable($releaseLine){
		$cache = SvnInfoCache::for_url($this->SubversionBaseURL);
		if(!$cache) return false;
		
		$childDirs = $cache->childDirs();
		$rule = '/^'.str_replace(".", "\.", $releaseLine).'[.\0-9]*$/';
		foreach($childDirs as $name => $info) {
			if(preg_match($rule, $name)) {
				// Reformat the version number into an int that can be sorted on
				$parts = array_pad(explode(".", $name), 3,0);
				$versionIdx = $parts[0]*10000 + $parts[1]*100 + $parts[2];
				$versions[$versionIdx] = array("url" => "$this->SubversionBaseURL/$name", "date" => $info['date'], "crossIdx" => $versionIdx );
			}
		}

		if(isset($versions)) {
			krsort($versions);
			$latest = reset($versions);
			return $latest;
		}
	}
	
	function latestPrerelease($releaseLine){
		foreach(self::$pre_release_categories as $cat => $val) {
			$cache = SvnInfoCache::for_url($this->SubversionBaseURL."/".$cat);
			if(!$cache) continue;

			$childDirs = $cache->childDirs();
			$rule = '/^('.str_replace(".", "\.", $releaseLine).'[\.0-9]*)-('.$cat.')([0-9]*)/';
			foreach($childDirs as $name => $info) {
				if(preg_match($rule, $name, $matches)) {
					$parts = array_pad(explode(".", $matches[1]), 3, 0);
					$parts[3] = self::$pre_release_categories[$matches[2]];
					$parts[4] = $matches[3];
					$versionIdx = $parts[0]*100000000 + $parts[1]*1000000 + $parts[2]*10000 + $parts[3]*100 + (int)$parts[4];
					$crossIdx = $parts[0]*10000 + $parts[1]*100 + $parts[2];
					$versions[$versionIdx] = array("url" => "$this->SubversionBaseURL/$cat/$name", "date" => $info['date'], "crossIdx" => $crossIdx);
				}
			}	
		}
		
		if(isset($versions)) {
			krsort($versions);
			$latest = reset($versions);
			return $latest;
		}
	}
}

class PreReleasesPage_Controller extends DownloadPage_Controller {

}

?>
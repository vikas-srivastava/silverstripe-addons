<?php

class AddonPageDataTransmation extends Controller{
	/**
	 * this is only supposed to run once when first installed with the flag Submitted
	 */
/*	function initailiseSubmitted(){
		$addons = DataObject::get("AddOnPage");
		$countSubmitted = 0;
		$countUnsubmitted = 0;
		foreach($addons as $addon){
			if(Versioned::get_one_by_stage("AddOnPage", "Live", "SiteTree_Live.ID = '".$addon->ID."'", false) ){
				$countSubmitted ++;
				$addon->Submitted = true;
				$addon->writeToStage("Stage");
				$addon->writeToStage("Live");
			}else{
				Debug::show($addon->Title . " ". $addon->Status);
				$countUnsubmitted ++;
			}
		}
		
		echo $countUnsubmitted."<br />";
		echo $countSubmitted."<br />";
	}*/
}

?>
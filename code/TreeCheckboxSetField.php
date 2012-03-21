<?php

class TreeCheckboxSetField extends CheckboxSetField {
	
	function Field() {
		Requirements::css(SAPPHIRE_DIR . '/css/CheckboxSetField.css');
		Requirements::css('addons/css/TreeCheckboxSetField.css');
		Requirements::javascript(THIRDPARTY_DIR .'/jquery/jquery.js');
		Requirements::javascript('sapphire/javascript/jquery_improvements.js');
		Requirements::javascript('addons/javascript/TreeCheckboxSetField.js');

		$source = $this->source;
		$values = $this->value;

		// Get values from the join, if available
		if(is_object($this->form)) {
			$record = $this->form->getRecord();
			if(!$values && $record && $record->hasMethod($this->name)) {
				$funcName = $this->name;
				$join = $record->$funcName();
				if($join) {
					foreach($join as $joinItem) {
						$values[] = $joinItem->ID;
					}
				}
			}
		}
		if($values){
			if(is_string($values)){
				$items = explode(',', $values);	
			}elseif(is_array($values)){
				$items = $values;
			}
			$items = str_replace('{comma}', ',', $items);
		}else{
			$items = array();
		}
		if(is_array($source)) {
			unset($source['']);
		}
		$odd = 0;
		$options = '';
		if ($source == null) {
			$source = array();
			$options = "<li>No options available</li>";
		}

		$options = $this->build_options_by_source($source, $items);
		$ret = "<ul id=\"{$this->id()}\" class=\"optionset checkboxsetfield treecheckboxsetfield{$this->extraClass()}\">\n$options</ul>\n";
		return $ret;
	}
	
	function build_options_by_source($source, $items, $childrendisabled=false){
		$html = '';
		foreach($source as $index => $item) {
			//Debug::show($index." => ".$item);
			if(is_array($item)){
				$itemID = $this->id() . '_' . ereg_replace('[^a-zA-Z0-9]+', '', $index);
				
				if(isset($items)) {
					if(in_array($index, $items)) {
						$checked = ' checked="checked"';
						$childrenDisabled = true;
					}else{
						$checked = '';
						$childrenDisabled = false;
					}
				}

				$disabled = ($this->disabled) ? $disabled = ' disabled="disabled"' : '';
				$options = $this->build_options_by_source($item, $items, $childrenDisabled);
				
				$html .= "
				<li class=\"root\">
					<input class=\"root\" id=\"$itemID\" name=\"$this->name[$index]\" type=\"checkbox\" value=\"$index\"$checked $disabled class=\"checkbox\" />
					<label for=\"$itemID\">All ".$index."</label>
					<ul id=\"{$this->id()}\" class=\"subUL optionset checkboxsetfield treecheckboxsetfield{$this->extraClass()}\">\n$options</ul>\n
				</li>\n";
			}else{
				$html .= $this->build_one_option($index, $item, $items, $childrendisabled);
			}
		}
		return $html;
	}
	
	function build_one_option($index,$item, $items, $childrendisabled){
		$itemID = $this->id() . '_' . ereg_replace('[^a-zA-Z0-9]+', '', $index);
		$checked = '';
	
		if(isset($items)) {
			$checked = (in_array($index, $items)) ? ' checked="checked"' : '';
		}
	
		$disabled = ($this->disabled || $childrendisabled) ? $disabled = ' disabled="disabled"' : '';
		$option = "<li class=\"leaf\"><input id=\"$itemID\" name=\"$this->name[$index]\" type=\"checkbox\" value=\"$index\"$checked $disabled class=\"checkbox\" /> <label for=\"$itemID\">$item</label></li>\n";
		return $option;
	}
	
	/**
	 * Transforms the source data for this CheckboxSetField
	 * into a comma separated list of values.
	 * 
	 * @return ReadonlyField
	 */
	function performReadonlyTransformation() {
		$source = AddonRelease::all_stables_plus_one_pre_if_newer_groupby_releaselines();
		$mapping = array();
		if(!empty($source)) {
			foreach($source as $releaseline => $versions){
				if(!empty($versions)) {
					$dummy = $versions;
					foreach($versions as $version){
						if(count($dummy) === count($versions)){
							$mapping[$releaseline] = implode(",",$dummy);
						}elseif(count($dummy) > 1){
							$mapping[$version." above"] = implode(",",$dummy);
						}
						unset($dummy[$version]);
					}
				}
			}	
		}
		
		$title = ($this->title) ? $this->title : '';
		$values = str_replace(",", ", ", str_replace(array_values($mapping), array_keys($mapping), $this->value));

		$field = new ReadonlyField($this->name, $title, $values);
		$field->setForm($this->form);
		
		return $field;
	}
}

?>
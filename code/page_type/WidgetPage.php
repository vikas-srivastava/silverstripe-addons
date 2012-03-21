<?php

class WidgetPage extends AddOnPage {
	static $has_one = array(
		'CMSImage' => 'Image',
		'ScreenshotImage' => 'Image',
	);
	
	static $singular_name = "Widget";	
	
	protected $urlSuffix = '-widget';
	
	function getCMSFields() {
		$fields = parent::getCMSFields();

		$fields->dataFieldByName("Title")->Title = "Widget name";
		$fields->addFieldToTab("Root.Content.Main", new ImageField('CMSImage', 'CMS screenshot', null, null, null, 'WidgetCMSImages'));
		$fields->addFieldToTab("Root.Content.Main", new ImageField('ScreenshotImage', 'Front-end screenshot', null, null, null, 'WidgetScreenshotImages'));

		return $fields;
	}

	function BgColor() {
		return 'black';
	}	
	
	/**
	 * Get fields for front-end forms in the module management section
	 */
	function getFrontEndFields() {
		Requirements::block(CMS_DIR . "/javascript/SitetreeAccess.js");
		Requirements::block(SAPPHIRE_DIR . '/javascript/UpdateURL.js');

		$fields = new FieldSet();
		$sourceFields = $this->getCMSFields();
		foreach($sourceFields->fieldByName('Root')->fieldByName('Content')->fieldByName('Main')->Children as $field) {
			if($field->Name() != 'URL') $fields->push($field);
		}
		foreach($sourceFields->fieldByName('Root')->fieldByName('Content')->fieldByName('Source')->Children as $field) {
			if($field->Name() != 'Maintainers') $fields->push($field);
		}

		// Put front-end ready image fields in
		$fields->removeByName("ScreenshotImage");
		$fields->removeByName("CMSImage");
		$fields->removeByName("DownloadFile");
		$fields->push(new SimpleImageField("ScreenshotImage", "Front-end screenshot"));
		$fields->push(new SimpleImageField("CMSImage", "CMS screenshot"));
		$fields->push(new FileField('DownloadFile', 'Upload a tar.gz file containing your widget'));
		
		$fields->replaceField('ReleaseDate', $dateField = new DateField('ReleaseDate', 'Release date'));
		$dateField->setRightTitle(' (in DD/MM/YYYY)');
		
		$fields->removeByName('SourceHeader');
		$fields->insertBefore(new HeaderField('SourceHeader', 'Information about the widget\'s codebase'), 'CurrentVersion');
		
		return $fields;
	}
	
	function getValidator() {
		$fields = new RequiredFields(
			'Title',
			'Content',
			'CurrentVersion',
			'ReleaseDate',
			'DownloadFile'
		);
		
		return $fields;
	}
	
	static function widgets_checkboxset_field($fieldName) {
		$allWidgets = DataObject::get('WidgetPage');
		$sourceMap = $allWidgets ? $allWidgets->map('ID', 'Title') : array();
		
		$field = new CheckboxSetField(
			$fieldName,
			'Widgets',
			$sourceMap
		);
		
		return $field;
	}
	
}

class WidgetPage_Controller extends Page_Controller {
	
}

?>
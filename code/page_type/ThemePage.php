<?php

class ThemePage extends AddOnPage {
	
	static $db = array(
		'ShortName' => 'Varchar',
	);
	
	static $has_one = array(
		'ScreenshotImage' => 'Image'
	);

	static $singular_name = "Theme";
	
	protected $urlSuffix = '-theme';
	
	static $sphinx = array(
		'search_fields' => array('ShortName')
	);
	
	function getCMSFields() {
		$fields = parent::getCMSFields();
		
		$fields->dataFieldByName("Title")->Title = "Theme name";
		$fields->addFieldToTab('Root.Content.Main', new ImageField('ScreenshotImage', 'Screenshot of theme', null, null, null, 'ThemeImages'));
		$fields->addFieldToTab('Root.Content.Source', new TextField('ShortName', 'Name of the theme folder'), 'Maintainers');
		
		return $fields;
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
		$fields->insertBefore(new SimpleImageField("ScreenshotImage", "Screenshot image"), "Content");
		$fields->removeByName("DownloadFile");
		$fields->push(new FileField('DownloadFile', 'Upload a tar.gz file containing your theme'));

		$fields->replaceField('ReleaseDate', $dateField = new DateField('ReleaseDate', 'Release date'));
		$dateField->setRightTitle(' (in DD/MM/YYYY)');
		
		$fields->removeByName('SourceHeader');
		$fields->insertBefore(new HeaderField('SourceHeader', 'Information about the theme\'s codebase'), 'ShortName');
		
		return $fields;
	}
		
	function getValidator() {
		$validator = new RequiredFields(
			'Title',
			'ShortName',
			'Content',
			'CurrentVersion',
			'DownloadFile'
		);
		
		return $validator;
	}

	////////////////////////////////////////////////////////////////////////////////////////////////////////
	
	/**
	 * Returns what background colour is needed to be applied to the template and menu2
	 * @return String
	 */
	function BgColor() {
		return 'black';
	}
	
	static function themes_checkboxset_field($fieldName) {
		$allThemes = DataObject::get('ThemePage');
		$sourceMap = $allThemes ? $allThemes->map('ID', 'Title') : array();
		
		$field = new CheckboxSetField(
			$fieldName,
			'Themes',
			$sourceMap
		);
		
		return $field;
	}
	
	/**
	 * Returns the next instance of Theme that
	 * is a sibling of this ThemePage instance in terms
	 * of it's location in the CMS site tree.
	 * 
	 * @return ThemePage
	 */
	function NextPage() {
		$holder = $this->Parent();
		$themes = $holder ? $holder->Themes() : null;
		$currentSort = $this->Sort;
		$nextThemes = new DataObjectSet();
		
		if($themes) foreach($themes as $theme) {
			if($theme->Sort > $currentSort) {
				$nextThemes->push($theme);
			}
		}
		
		return $nextThemes ? $nextThemes->First() : null;
	}

	
}
class ThemePage_Controller extends Page_Controller {
	
}
?>

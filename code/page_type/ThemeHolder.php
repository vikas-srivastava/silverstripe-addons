<?php

class ThemeHolder extends Page {
	
	static $default_child = 'ThemePage';
	
	static $has_many = array(
		'Themes' => 'ThemePage'
	);
	
	static $many_many = array(
		'Reviewers' => 'Member'
	);

	static $default_records = array(
		array('Title' => "Themes")
	);
	
	function getCMSFields() {
		$fields = parent::getCMSFields();
		$fields->addFieldToTab('Root.Content.Main', new TagField('Reviewers', 'Reviewers', '', 'ThemeHolder', 'Nickname'), 'Content');
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

class ThemeHolder_Controller extends Page_Controller {

	protected $sorts = array(
		'name' => 'Title',
		'creator' => '`Member`.Nickname',
		'added' => 'Created DESC'
	);
	
	function Themes() {
		if(isset($_GET['sort']) && isset($this->sorts[$_GET['sort']])) $sort = $this->sorts[$_GET['sort']];
		else $sort = 'Created DESC';
		
		$start = isset($_GET['start']) ? (int)$_GET['start'] : 0;
		$themes = DataObject::get("ThemePage", "ParentID = " . (int)$this->ID, $sort, "
			LEFT JOIN AddOnPage_Maintainers ON AddOnPage_Maintainers.AddOnPageID = `ThemePage`.ID
			LEFT JOIN `Member` ON AddOnPage_Maintainers.MemberID = `Member`.ID
		", "9 OFFSET $start");
		
		if(!$themes) return false;
		
		// Give each record a column position
		$numberOfCols = 3;
		$colPos = 0;
		foreach ($themes as $theme) {
			$colPos++;
			if($colPos > 3) $colPos = 1;
			$theme->Column = $colPos;
		}
		
		return $themes;
	}
	
	function SortOption() {
		if (isset($_GET['sort'])) {
			return $_GET['sort'];
		}
		return false;
	}

	function manage() {
		$reviewers = $this->getManyManyComponents('Reviewers');
		$to = '';
		if($reviewers) {
			$to = implode(',', $reviewers->column('Email'));
		}
		
		return new AddOnCRUD($this->data(), "ThemePage", $to,
			// Add form content
			array(
				'Title' => 'Submit a theme',
				'Content' => '<p>Fill out the form below to list your theme in our directory.</p>',
			), 
			// Edit form content
			array(
				'Title' => 'Edit a module',
				'Content' => '<p>Update the details of your theme in the form below.</p>',
			),
			// After add/edit content
			array(
				'Title' => 'Thanks for your submission',
				'Content' => '<p>Thanks! Your submission will be listed on the site after we have reviewed it.</p>'
			)
		);
	}
	
	function clear() {
		if(Permission::check("ADMIN")) {
			$themes = DataObject::get("ThemePage");
			foreach($themes as $theme) {
				$theme->doUnpublish();
				$theme->delete();
			}
		}
	}

}

?>
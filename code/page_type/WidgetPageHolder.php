<?php

class WidgetPageHolder extends Page {
	
	static $default_child = 'WidgetPage';
	
	function BgColor() {
		return 'black';
	}
	
	static $many_many = array(
		'Reviewers' => 'Member'
	);

	static $default_records = array(
		array('Title' => "Widgets")
	);
	
	function getCMSFields() {
		$fields = parent::getCMSFields();
		$fields->addFieldToTab('Root.Content.Main', new TagField('Reviewers', 'Reviewers', '', 'ModuleHolder', 'Nickname'), 'Content');
		return $fields;
	}
	
}

class WidgetPageHolder_Controller extends Page_Controller {
	
	function init() {
		parent::init();
		
		Requirements::customScript("
			(function($) {
				$(document).ready(function() {
					var popupElements = $('a.fancy');
					if($.browser.msie == true) return false;
					if(typeof(popupElements) != 'undefined' && popupElements.length > 0) popupElements.fancybox({{$this->fancyBoxSettings()}}); 
				});
			})(jQuery);
		", 'fancyBoxCustomScript');
	}
	
	protected $sorts = array(
		'name' => 'Title',
		'creator' => '`Member`.Nickname',
		'added' => 'ReleaseDate DESC'
	);
	
	function fancyBoxSettings() {
		return 'frameWidth: 830, frameHeight: 600';
	}
	
	function Widgets() {
		
		if(isset($_GET['sort']) && isset($this->sorts[$_GET['sort']])) $sort = $this->sorts[$_GET['sort']];
		else $sort = 'ReleaseDate DESC';
		
		$start = isset($_GET['start']) ? (int)$_GET['start'] : 0;
		
		//return DataObject::get("WidgetPage", "ParentID = " . (int)$this->ID, "Title", '', "6 OFFSET $start");
		
		$widgets = DataObject::get("WidgetPage", "ParentID = " . (int)$this->ID, $sort, "
			LEFT JOIN AddOnPage_Maintainers ON AddOnPage_Maintainers.AddOnPageID = `WidgetPage`.ID
			LEFT JOIN `Member` ON AddOnPage_Maintainers.MemberID = `Member`.ID
		", "6 OFFSET $start");
		
		if(!$widgets) return false;
		
		return $widgets;
	}

	function manage() {
		$reviewers = $this->getManyManyComponents('Reviewers');
		$to = '';
		if($reviewers) {
			$to = implode(',', $reviewers->column('Email'));
		}
		
		return new AddOnCRUD($this->data(), "WidgetPage", $to,
			// Add form content
			array(
				'Title' => 'Submit a widget',
				'Content' => '<p>Fill out the form below to list your widget in our directory.</p>',
			), 
			// Edit form content
			array(
				'Title' => 'Edit a module',
				'Content' => '<p>Update the details of your widget in the form below.</p>',
			),
			// After add/edit content
			array(
				'Title' => 'Thanks for your submission',
				'Content' => '<p>Thanks! Your submission will be listed on the site after we have reviewed it.</p>'
			)
		);
	}
	
	function SortOption() {
		if (isset($_GET['sort'])) {
			return $_GET['sort'];
		}
		return false;
	}
	
}

?>

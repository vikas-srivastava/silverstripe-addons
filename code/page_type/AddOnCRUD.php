<?php

/**
 * Provides a common CRUD code base for all the add-ons on the site: Showcase, Widget, Module, Theme
 */
class AddOnCRUD extends Page_Controller {
	static $url_handlers = array(
		'add' => 'add',
		'AddForm' => 'AddForm',
		'draft/$ID' => 'draft',
		'edit/$ID' => 'edit',
		'changesmade' => 'changesmade',
	);
	
	protected $className, $addContent, $editContent, $afterEditContent;
	protected $basePage, $baseLink;
	protected $issubmit = true;
	public $afterEditContentModerationNeed;
	
	/**
	 * Email address that will get send items for review.
	 * Only Versioned objects are sent for review.
	 */
	protected $reviewerEmail;
	
	function __construct($basePage, $className, $reviewerEmail, $addContent, $editContent, $afterEditContent = array()) {
		$this->basePage = $basePage;
		$this->baseLink = Controller::join_links($basePage->Link(), 'manage');
		$this->className = $className;
		$this->addContent = $addContent;
		$this->editContent = $editContent;
		$this->reviewerEmail = $reviewerEmail;
		
		if($afterEditContent) $this->afterEditContent = $afterEditContent;
		parent::__construct($basePage);
	}

	function handleRequest($request) {
		return RequestHandler::handleRequest($request);
	}
	
	function Link() {
		return $this->baseLink;
	}
	
	//////////////////////////////////////////////////////////////////////////////////////
	// ADD
	
	function add() {
		if(!Member::currentUser()) return Security::permissionFailure();

		$content = $this->addContent;
		$content['Form'] = $this->AddForm();
		$content['BgColor'] = 'white';
		$content['BasePageLinK'] = $this->basePage->Link();
		
		return $this->customise($content)->renderWith(array('AddOnCRUDPage', 'Page'));
	}
	
	function AddForm() {
		Requirements::css('addons/css/AddForm.css');
		if(!Member::currentUser()) return Security::permissionFailure();

		$SNG_class = singleton($this->className);
		
		if(method_exists($SNG_class, 'getFrontEndFieldsForAddForm')) {
			$fields = $SNG_class->getFrontEndFieldsForAddForm();
		}else{
			$fields = $SNG_class->getFrontEndFields();
		}

		$fields->removeByName('MemberID');

		$validator = null;

		if(method_exists($SNG_class, 'getValidator')) {
			$validator = $SNG_class->getValidator();
			$validator -> setJavascriptValidationHandler('prototype');
			$required = $validator->getRequired();
			if($required && !empty($required)){
				foreach($required as $requiredName){
					$requiredField = $fields->dataFieldByName($requiredName);
					if($requiredField) $requiredField->setTitle($requiredField->Title()."*");
				}
			}
		}
		
		// For ModulePage only
		$abstract = $fields->dataFieldByName('Abstract');
		if($abstract && $this->className == 'ModulePage') $abstract->setTitle($abstract->Title() . " (this will be displayed on the overview page of all modules)");
		$content = $fields->dataFieldByName('Content');
		if($content && $this->className == 'ModulePage') $content->setTitle("Detailed description*" . " (this will be displayed on the detail page for your module)");
		
		// For SilverStripe Derectory only
		$contact = $fields->dataFieldByName('Contact');
		if($contact && $this->className == 'Listing')	{
			$contact->setTitle($contact->Title() . "</label><label class=\"secondleft\"><span class=\"helptext\">Please include country code, area code and the phone number, eg. +64 (0)4 9787330</span>");
			$contact->setCustomValidationMessage("Please fill out \"Contact information*\", it is required.");
		}		
		if($abstract && $this->className == 'Listing') {
			$abstract->setTitle($abstract->Title() . "</label><label class=\"secondleft\"><span class=\"helptext\">Maximal 400 characters allowed.<br />Please provide a brief summary of your company. The abstract will be displayed on the list page of the SilverStripe Developer Network.</span>");
			$abstract->setCustomValidationMessage("Please fill out \"Abstract*\", it is required.");
		}
		
		$description = $fields->dataFieldByName('Description');
		if($description && $this->className == 'Listing') {
			$description->setTitle($description->Title() . "</label><label class=\"secondleft\"><span class=\"helptext\">Please describe your company and the services you offer. You may also include links to your portfolio. The detailed description will be displayed on the detail page for your listing.</span>");
			$description->setCustomValidationMessage("Please fill out \"Detailed description*\", it is required.");
		}
		
		$actions = new FieldSet(
			new FormAction('doAdd', 'Submit')
		);
		
		
		$form = new Form($this, 'AddForm', $fields, $actions, $validator);
		$form->setRedirectToFormOnValidationError(true);
		return $form;
	}
	
	function save1($data, $form){
		$this->issubmit = false;
		$this->doAdd($data, $form);
	}
	
	function save3($data, $form){
		$this->save1($data, $form);
	}

	function doAdd($data, $form) {
		if(Member::currentUser()) {
			$origStage = Versioned::current_stage();
			Versioned::reading_stage('Stage');
			
			$class = $this->className;
			
			// HACK! (To make sure screenshots get uploaded in SSProject)
			if($class == 'SSProject') {
				if(empty($data['Screenshot']['tmp_name'])) {
					$form->sessionMessage('Please upload a screenshot of your website.', 'required');
					Director::redirectBack();
					return false;
				}
			}
			
			// Do initial save
			if(isset($data['ID']) && $data['ID']){
				$project = DataObject::get_by_id($this->className, $data['ID']);
			}else{
				$project = new $class();
			}
			$form->saveInto($project);
			
			if(!$project->Submitted && $this->issubmit){
				$project->Submitted = true;
			}
			$project->write();
			
			// Set maintainers / owners
			if($project->has_one('Member')) {
				$project->MemberID = Member::currentuserID();
				
			} else if($project->many_many('Maintainers')) {
				$project->Maintainers()->add(Member::currentuserID());
			} else if($project->many_many('Members')) {
				$project->Members()->add(Member::currentUserID());
			}
		
			$project->write();
			
			if(is_a($project, 'AddOnPage') && !empty($data['Release'])){
				$release = new AddonRelease();
				$release->Status = 'Current';
				$release->ParentID = $project->ID;
				foreach($data['Release'] as $f=>$v){
					$form->Fields()->dataFieldByName("Release[".$f."]")->setName($f);
				}
				
				$form->saveInto($release);
				
				// explictly set the compatible versions since save into doesn't want to play ball
				$release->CompatibleSilverStripeVersions = isset($data['Release']) ? implode(',',array_values($data['Release']['CompatibleSilverStripeVersions'])) : false;
				$release->write();
			}

			$email = new Email();
			$email->setTo($this->reviewerEmail);
			$email->setFrom("noreply@silverstripe.com");
			
			if($this->issubmit && $project->hasExtension('Versioned')) { // only the submit button is clicked, the Reviewers need to notify.
				if($this->afterEditContent && is_a($project, 'Listing')){ // indicating this is a newly submitted Listing
					$email->setSubject("New submission for SilverStripe Developer Network: $project->OrganisationName");
					
					// should be in a template
					$email->setBody( 
						"Hi, \n\nWe have received a new submission for the SilverStripe Developer Network.\n\n $project->OrganisationName\n $project->City\n $project->Country\n $project->URL\n\n" . 
						"Please moderate this submission:\n" . Director::absoluteURL($project->ModerationLink())."\n\n");
					$email->setFrom("noreply@silverstripe.com");
				}else{
					$email->setSubject("A new $project->class has been added to the site: $project->Title"); 
					$email->setBody("There is a new $project->class on the site: '$project->Title'.\n\n" . 
						"View it here:\n" . Director::absoluteURL($project->Link()) . "?stage=Stage\n\n" .
						"It will need to be published.  Open the CMS here:\n" . 
						Director::absoluteURL("admin/show/" . $project->ID));
				}
				
				$email->sendPlain();
			}
			
			if($this->issubmit && $this->afterEditContent && is_a($project, 'SSProject')){ // indicating this is a newly submitted Showcase
				$email->setSubject("A new community showcase has been submitted to the site: $project->SiteName"); 
				$email->setBody("There is a new community showcase submitted to the site: '$project->SiteName'.\n\n" . 
					"To moderate it in front-end here:\n" . Director::absoluteURL($project->Link())."\n\n");
				$email->sendPlain();
			}
			
			Versioned::reading_stage($origStage);
			if($this->issubmit){ // if the submit button is clicked, do it as normal, this is only for the case that $project is_a ModulePage
				if($this->afterEditContent) Director::redirect($this->Link() . '/changesmade');
				else Director::redirect($project->Link());
			}else{
				Director::redirect($this->Link() . '/draft/'.$project->ID);
			}
		} else {
			return Security::permissionFailure();
		}
	}

	
	/**
	 * Handles requests for community-showcase/manage/item/36
	 */
	function edit($request) {
		Requirements::css('addons/css/AddForm.css');
		
		//Requirements::block(THIRDPARTY_DIR . '/jquery/jquery.js');
		Requirements::block('mysite/javascript/misc.js');
		Requirements::block("addons/thirdparty/jquery.fancybox-1.0.0.js");
		Requirements::block('fancyBoxCustomScript');
		Requirements::css('addons/thirdparty/fancybox/jquery.fancybox-1.3.4.css');
		
		// Return the sub-controller
		$id = $request->param('ID');
		return new AddOnCRUD_ItemController($this, Controller::join_links($this->Link(), "edit/$id"), 
			$this->className, $id, $this->editContent);
	}
	
	/**
	 * Handles requests for drafting a addon, ie a module has been saved and not submitted yet, e.g. modules/manage/draft/889
	 */
	function draft($request) {
		$id = $request->param('ID');
		return new AddOnCRUD_DraftItemController($this, Controller::join_links($this->Link(), "draft/$id"), 
			$this->className, $id, $this->addContent);
	}

	function afterEditContent() {
		return $this->afterEditContent;
	}
	
	function reviewerEmail() {
		return $this->reviewerEmail;
	}
	
	function changesmade() {
		$content = $this->afterEditContent;
		$content['BgColor'] = 'white';
		
		return $this->customise($content)->renderWith(array('AddOnCRUDPage', 'Page'));
	}
	
	function changemademoderation() {
			$content = array(
				'Title' => "",
				'Content' => "<h3>Thank you for updating your listing on the SilverStripe Developer Network.</h3><br /><p>We will review your updates within a few business days (New Zealand time.)</p>",
			);
			$content['BgColor'] = 'white';

			return $this->customise($content)->renderWith(array('AddOnCRUDPage', 'Page'));
	}
}

class AddOnCRUD_ItemController extends Page_Controller {
	static $url_handlers = array(
		'' => 'index',
		'EditForm' => 'EditForm',
	);
	
	protected $parent, $baseLink, $project;
	protected $editContent;
	
	function __construct($parent, $baseLink, $className, $itemID, $editContent) {
		$this->parent = $parent;
		$this->baseLink = $baseLink;
		$this->project = DataObject::get_by_id($className, $itemID);
		if(!$this->project){
			$origStage = Versioned::current_stage();
			Versioned::reading_stage('Stage');
			$this->project = Versioned::get_one_by_stage($className, "Stage", $className.".ID = '".$itemID."'");
			Versioned::reading_stage($origStage);
		}
		$this->editContent = $editContent;
		parent::__construct();
	}
	
	function handleRequest($request) {
		return RequestHandler::handleRequest($request);
	}
	
	function Link() {
		return $this->baseLink;
	}
	
	//////////////////////////////////////////////////////////////////////////////////////
	// EDIT
	
	function index() {
		$content = $this->editContent;
		$content['Form'] = $this->EditForm();
		$content['BgColor'] = 'white';

		return $this->customise($content)->renderWith(array('AddOnCRUDPage', 'Page'));
	}
	
	
	function EditForm() {
		if($this->project && $this->project->CanEdit()) {
			$fields = $this->project->getFrontendFields();
			$fields->removeByName('MemberID');

			// For SilverStripe Derectory only
			$contact = $fields->dataFieldByName('Contact');
			if($contact && $this->project->ClassName == 'Listing')	{
				$contact->setTitle($contact->Title() . "</label><label class=\"secondleft\"><span class=\"helptext\">Please include country code, area code and the phone number, eg. +64 (0)4 9787330</span>");
				$contact->setCustomValidationMessage("Please fill out \"Contact information*\", it is required.");
			}
			
			$abstract = $fields->dataFieldByName('Abstract');		
			if($abstract && $this->project->ClassName == 'Listing') {
				$abstract->setTitle($abstract->Title() . "</label><label class=\"secondleft\"><span class=\"helptext\">Maximal 400 characters allowed.<br />Please provide a brief summary of your company. The abstract will be displayed on the list page of the SilverStripe Developer Network.</span>");
				$abstract->setCustomValidationMessage("Please fill out \"Abstract*\", it is required.");
			}

			$description = $fields->dataFieldByName('Description');
			if($description && $this->project->ClassName == 'Listing') {
				$description->setTitle($description->Title() . "</label><label class=\"secondleft\"><span class=\"helptext\">Please describe your company and the services you offer. You may also include links to your portfolio. The detailed description will be displayed on the detail page for your listing.</span>");
				$description->setCustomValidationMessage("Please fill out \"Detailed description*\", it is required.");
			}
			
			$validator = null;
			$SNG_class=get_class($this->project);
			if(method_exists($this->project, 'getValidator')) {
				$validator = $this->project->getValidator();
				if(get_class($this->project) == 'SSProject'){
					$validator->removeRequiredField('Screenshot');
				}
				$validator -> setJavascriptValidationHandler('prototype');
			}
			
			$form = new Form($this, 'EditForm', $fields, new FieldSet(new FormAction('doUpdate', 'Save')), $validator);
			$form->loadDataFrom($this->project);
			$form->setRedirectToFormOnValidationError(true);
			return $form;
		} else {
			return Security::permissionFailure();
		}
	}
	
	function doUpdate($data, $form) {
		if($this->project && $this->project->CanEdit()) {
			$origStage = Versioned::current_stage();
			Versioned::reading_stage('Stage');
			
			$form->saveInto($this->project); 
			$this->project->write();
			
			if(method_exists($this->project, 'doPublish')) {
				
				/**
				 * We temporarily bypass the whole permission checking process, due to the cmsworkfollow module alway return false when checking the permission and that result in the whole add-on object can be published from front end
				 * TODO: need to switch back and fix the bug in SiteTree::batch_permission_check();
				 */
				//$this->project->doPublish();
				$this->project->publish("Stage", "Live");
				$project = $this->project;

				mail($this->parent->reviewerEmail(), "A $project->class has been updated: $project->Title", 
					"A $project->class page has been updated on the site: '$project->Title'.\n\n" . 
					"View it here:\n" . Director::absoluteURL($project->Link()) . "\n\n" .
					"This page has been automatically published since it was just an update.", "From: noreply@silverstripe.com");
			}
			
			if($this->project->ClassName == 'Listing'){
				$project = $this->project;
				$project->Status = "Update";
				$project-> write();
				 // indicating this is a newly submitted Listing
				mail("SilverStripe Developer Network <".SS_DEV_NETWORK_ACCOUNT.">", "Updated listing for SilverStripe Developer Network: $project->OrganisationName", 
					"Hi, \n\nA listing for the SilverStripe Developer Network has been updated.\n\n $project->OrganisationName\n $project->City\n $project->Country\n $project->URL\n\n" . 
					"Please moderate this update:\n" . Director::absoluteURL($project->ModerationLink())."\n\n", "From: noreply@silverstripe.com\r\n");
				Versioned::reading_stage($origStage);		
				Director::redirect($this->parent->Link() . '/changemademoderation');
				return;
			}
			Versioned::reading_stage($origStage);
			Director::redirect($this->project->Link());
		} else {
			return Security::permissionFailure();
		}
	}
}


class AddOnCRUD_DraftItemController extends AddOnCRUD_ItemController {
	static $url_handlers = array(
		'' => 'index',
		'DraftForm' => 'DraftForm',
	);
	
	function index(){
		$content = $this->editContent;
		$content['Form'] = $this->DraftForm();
		$content['BgColor'] = 'white';

		return $this->customise($content)->renderWith(array('AddOnCRUDPage', 'Page'));
	}
	function DraftForm(){
		if($this->project && $this->project->CanEdit()) {
			Requirements::css('addons/css/AddForm.css');
			if(!Member::currentUser()) return Security::permissionFailure();
		
			if(method_exists($this->project, 'getFrontEndFieldsForAddForm')) {
				$fields = $this->project->getFrontEndFieldsForAddForm();
			}else{
				$fields = $this->project->getFrontEndFields();
			}

			$validator = null;
			if(method_exists($this->project, 'getValidator')) {
				$validator = $this->project->getValidator();
				$validator -> setJavascriptValidationHandler('prototype');
				$required = $validator->getRequired();
				if($required && !empty($required)){
					foreach($required as $requiredName){
						$requiredField = $fields->dataFieldByName($requiredName);
						if($requiredField) $requiredField->setTitle($requiredField->Title()."*");
					}
				}
			}
			
			$abstract = $fields->dataFieldByName('Abstract');
			$abstract->setTitle($abstract->Title() . " (this will be displayed on the overview page of all modules)");
			$content = $fields->dataFieldByName('Content');
			$content->setTitle("Detailed description*" . " (this will be displayed on the detail page for your module)");
			
			$actions = new FieldSet(
				new FormAction('doAdd', 'Submit')
			);
			
			$fields->removeByName('MemberID');
			$fields->push(new HiddenField("ID", "ID"));
			$form = new Form($this, 'DraftForm', $fields, $actions, $validator);
			$form->loadDataFrom($this->project);
			$form->setRedirectToFormOnValidationError(true);
			return $form;
		}else{
			return Security::permissionFailure();
		}
	}
	
	function save1($data, $form){
		$this->parent->issubmit = false;
		$this->parent->doAdd($data, $form);
	}
	
	function save3($data, $form){
		$this->save1($data, $form);
	}

	function doAdd($data, $form) {
		$this->parent->doAdd($data, $form);
	}
	
}

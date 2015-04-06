<?php
/**
 * Created by Nivanka Fonseka (nivanka@silverstripers.com).
 * User: nivankafonseka
 * Date: 4/6/15
 * Time: 2:00 PM
 * To change this template use File | Settings | File Templates.
 */

class NewsIndex extends Page {

	private static $db = array(
		'ItemsPerPage' 	=> 'Int',
	);


	function getCMSFields(){
		$fields = parent::getCMSFields();

		$fields->removeByName('Content');

		$fields->addFieldToTab('Root.Main',
			TextField::create('ItemsPerPage')
				->setTitle('Number of items per page'),
			'Metadata'
		);

		return $fields;
	}

}

class NewsIndex_Controller extends Page_Controller {


	private static $allowed_actions = array(
		'tag',
		'archive'
	);

	public function init(){
		parent::init();
	}

	public function tag(){
		return $this;
	}

	public function archive(){
		return $this;
	}

	/**
	 * @return bool
	 */
	public function IsTag(){
		return $this->request->param('Action') == 'tag';
	}

	/**
	 * @return bool
	 */
	public function IsArchive(){
		return $this->request->param('Action') == 'archive';
	}


	/**
	 * @param int $iOffset
	 * @return PaginatedList
	 */
	public function Items($iOffset = 0){
		$request = $this->GetRequestForItems($iOffset);

		$items = NewsPost::get()->filter('ParentID', $this->ID);

		if($this->IsTag()){
			$items = $items->filter('Tag:PartialMatch', $this->request->param('ID'));
		}

		if($this->IsArchive()){
			$strPattern = SiteConfig::current_site_config()->ArchivePattern ? : '%Y, %M';
			$items = $items->where('DATE_FORMAT(\'' . $strPattern .  '\') = \'' . Convert::raw2sql($this->request->param('ID')) . '\'');
		}


		$paginatedList = new PaginatedList($items, $request);
		$paginatedList->setPageLength($this->ItemsPerPage ? : SiteConfig::current_site_config()->ItemsPerPage ? : 10 );
		return $paginatedList;
	}


	/**
	 * @param int $iOffset
	 * @return NullHTTPRequest|SS_HTTPRequest
	 */
	function GetRequestForItems($iOffset = 0){
		if($iOffset == 0)
			return $this->request;

		$iStart = 0;
		$request = Controller::curr()->request;
		if($request->getVar('start')){
			$iStart = $request->getVar('start');
		}
		$iStart += $iOffset;

		return new SS_HTTPRequest("get", "/", array(
			"start"		=> $iStart,
		));
	}

}
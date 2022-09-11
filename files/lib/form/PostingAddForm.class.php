<?php
namespace cash\form;
use cash\data\cash\posting\CashPostingAction;
use wcf\data\category\Category;
use wcf\data\category\CategoryNodeTree;
use wcf\form\MessageForm;
use wcf\system\exception\UserInputException;
use wcf\system\html\input\HtmlInputProcessor;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\DateUtil;
use wcf\util\HeaderUtil;
use wcf\util\StringUtil;

/**
 * Shows the posting add form.
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.cash
 */
class PostingAddForm extends MessageForm {
	/**
	 * @inheritDoc
	 */
	public $attachmentObjectType = 'com.uz.cash.posting';
	/**
	 * @inheritDoc
	 */
	public $messageObjectType = 'com.uz.cash.posting';
	
	/**
	 * @inheritDoc
	 */
	public $loginRequired = true;
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['user.cash.canManage'];
	
	/**
	 * category related
	 */
	public $categoryID = 0;
	public $categoryList;
	public $categoryWarning = 0;
	
	/**
	 * basic data
	 */
	public $amount = 0.0;
	public $availableCurrencies = [];
	public $currency = '';
//	public $subject = '';
	public $type = 'expense';
	
	/**
	 * time
	 */
	public $time = '';
	public $timeObj;
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		// get available availableCurrencies
		$this->availableCurrencies = explode("\n", StringUtil::unifyNewlines(StringUtil::trim(CASH_CURRENCIES)));
	}
	
	/**
	 * @inheritDoc
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['categoryID'])) $this->categoryID = intval($_POST['categoryID']);
	//	if (isset($_POST['subject'])) $this->subject = StringUtil::trim($_POST['subject']);
		if (isset($_POST['type'])) $this->type = $_POST['type'];
		
		if (isset($_POST['amount'])) {
			$this->amount = StringUtil::trim($_POST['amount']);
			$this->amount = str_replace(WCF::getLanguage()->get('wcf.global.thousandsSeparator'), '', $this->amount);
			if (WCF::getLanguage()->get('wcf.global.decimalPoint') != '.') $this->amount = str_replace(WCF::getLanguage()->get('wcf.global.decimalPoint'), '.', $this->amount);
			$this->amount = floatval($this->amount);
		}
		if (isset($_POST['currency'])) $this->currency = $_POST['currency'];
		
		if (isset($_POST['time'])) {
			$this->time = $_POST['time'];
			$this->timeObj = \DateTime::createFromFormat('Y-m-d\TH:i:sP', $this->time);
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		// categories
		$this->categoryNodeTree = new CategoryNodeTree('com.uz.cash.category', 0, false);
		foreach ($this->categoryNodeTree->getIterator() as $category) {
			if (!$category->isDisabled) {
				$this->categoryWarning = 1;
				break;
			}
		}
		
		parent::readData();
		
		if (empty($_POST)) {
			$dateTime = DateUtil::getDateTimeByTimestamp(TIME_NOW);
			$dateTime->setTimezone(WCF::getUser()->getTimeZone());
			$this->time = $dateTime->format('c');
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
				'action' => 'add',
				'categoryNodeList' => $this->categoryNodeTree->getIterator(),
				'categoryID' => $this->categoryID,
				'categoryWarning' => $this->categoryWarning,
				
				'time' => $this->time,
				'amount' => $this->amount,
				'availableCurrencies' => $this->availableCurrencies,
				'currency' => $this->currency,
			//	'subject' => $this->subject,
				'type' => $this->type
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function validate() {
		parent::validate();
		
		// category
		if (empty($this->categoryID)) {
			throw new UserInputException('categoryID');
		}
		$category = new Category($this->categoryID);
		if (!$category->categoryID) {
			throw new UserInputException('categoryID', 'invalid');
		}
		
		// time
		if (empty($this->time)) {
			throw new UserInputException('time');
		}
		if (!$this->timeObj) {
			throw new UserInputException('time', 'invalid');
		}
		
		// amount and currency
		if (!$this->amount) {
			throw new UserInputException('amount');
		}
		if ($this->amount < 0.01) {
			throw new UserInputException('amount', 'invalid');
		}
		if (empty($this->currency)) {
			throw new UserInputException('currency');
		}
		if (!in_array($this->currency, $this->availableCurrencies)) {
			throw new UserInputException('currency', 'invalid');
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function save() {
		parent::save();
		
		// save posting
		$data = array_merge($this->additionalFields, [
				'categoryID' => $this->categoryID,
				'time' => $this->timeObj->getTimestamp(),
				'userID' => WCF::getUser()->userID,
				'username' => WCF::getUser()->username,
				'amount' => $this->amount,
				'currency' => $this->currency,
				'subject' => $this->subject,
				'type' => $this->type
		]);
		
		$postingData = [
				'data' => $data,
				'attachmentHandler' => $this->attachmentHandler,
				'htmlInputProcessor' => $this->htmlInputProcessor
		];
		
		$this->objectAction = new CashPostingAction([], 'create', $postingData);
		$this->objectAction->executeAction();
		
		// call saved event
		$this->saved();
		
		HeaderUtil::redirect(LinkHandler::getInstance()->getLink('PostingList', ['application' => 'cash']));
		exit;
	}
}

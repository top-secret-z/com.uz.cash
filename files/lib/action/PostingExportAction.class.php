<?php
namespace cash\action;
use cash\data\cash\posting\CashPostingList;
use wcf\action\AbstractAction;
use wcf\system\WCF;
use wcf\util\DateUtil;

/**
 * Exports postings
 * 
 * @author		2018-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.cash
 */
class PostingExportAction extends AbstractAction {
	/**
	 * @inheritDoc
	 */
	public $loginRequired = true;
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['user.cash.canManage'];
	
	/**
	 * separator for the exported data and enclosure
	 */
	public $separator = ',';
	public $textSeparator = '"';
	
	/**
	 * @inheritDoc
	 */
	public function execute() {
		parent::execute();
		
		$postingList = new CashPostingList();
		if ($_GET['categoryID']) $postingList->getConditionBuilder()->add('categoryID = ?', [$_GET['categoryID']]);
		if ($_GET['currency']) $postingList->getConditionBuilder()->add('currency LIKE ?', [$_GET['currency']]);
		if ($_GET['subject']) $postingList->getConditionBuilder()->add('subject LIKE ?', ['%' . $_GET['subject'] . '%']);
		if ($_GET['type']) $postingList->getConditionBuilder()->add('type LIKE ?', [$_GET['type']]);
		if ($_GET['startDate']) {
			$timestamp = strtotime($_GET['startDate']) - 1;
			$postingList->getConditionBuilder()->add('time > ?', [$timestamp]);
		}
		if ($_GET['endDate']) {
			$timestamp = strtotime($_GET['endDate']) + 86399;
			$postingList->getConditionBuilder()->add('time < ?', [$timestamp]);
		}
		
		$postingList->readObjects();
		
		$language = WCF::getLanguage();
		
		// send content type
		header('Content-Type: text/csv; charset=UTF-8');
		header('Content-Disposition: attachment; filename=postings.csv');
		echo $this->textSeparator.$language->get('wcf.global.objectID').$this->textSeparator.$this->separator;
		echo $this->textSeparator.$language->get('cash.posting.add.date').$this->textSeparator.$this->separator;
		echo $this->textSeparator.$language->get('cash.posting.add.time').$this->textSeparator.$this->separator;
		echo $this->textSeparator.$language->get('cash.posting.add.type').$this->textSeparator.$this->separator;
		echo $this->textSeparator.$language->get('cash.posting.add.categoryID').$this->textSeparator.$this->separator;
		echo $this->textSeparator.$language->get('cash.posting.add.amount').$this->textSeparator.$this->separator;
		echo $this->textSeparator.$language->get('cash.posting.add.currency').$this->textSeparator.$this->separator;
		echo $this->textSeparator.$language->get('cash.posting.add.subject').$this->textSeparator.$this->separator;
		echo "\r\n";
		
		foreach ($postingList->getObjects() as $posting) {
			echo $this->textSeparator.$posting->postingID.$this->textSeparator.$this->separator;
			echo $this->textSeparator.DateUtil::format(DateUtil::getDateTimeByTimestamp($posting->time), DateUtil::DATE_FORMAT).$this->textSeparator.$this->separator;
			echo $this->textSeparator.DateUtil::format(DateUtil::getDateTimeByTimestamp($posting->time), DateUtil::TIME_FORMAT).$this->textSeparator.$this->separator;
			echo $this->textSeparator.$language->get('cash.posting.add.type.' . $posting->type).$this->textSeparator.$this->separator;
			echo $this->textSeparator.$posting->categoryID.$this->textSeparator.$this->separator;
			if ($posting->type == 'expense') $posting->amount *= -1;
			$amount = number_format(round($posting->amount, 2), 2, WCF::getLanguage()->get('wcf.global.decimalPoint'), WCF::getLanguage()->get('wcf.global.thousandsSeparator'));
			echo $this->textSeparator.$amount.$this->textSeparator.$this->separator;
			echo $this->textSeparator.$posting->currency.$this->textSeparator.$this->separator;
			echo $this->textSeparator.$posting->subject.$this->textSeparator.$this->separator;
			echo "\r\n";
		}
		
		$this->executed();
		
		exit;
	}
}

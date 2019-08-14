<?php /** 
* Moogento
* 
* SOFTWARE LICENSE
* 
* This source file is covered by the Moogento End User License Agreement
* that is bundled with this extension in the file License.html
* It is also available online here:
* https://moogento.com/License.html
* 
* NOTICE
* 
* If you customize this file please remember that it will be overwrtitten
* with any future upgrade installs. 
* If you'd like to add a feature which is not in this software, get in touch
* at www.moogento.com for a quote.
* 
* ID          pe+sMEDTrtCzNq3pehW9DJ0lnYtgqva4i4Z=
* File        NoMoreSpam.php
* @category   Moogento
* @package    noMoreSpam
* @copyright  Copyright (c) 2016 Moogento <info@moogento.com> / All rights reserved.
* @license    https://moogento.com/License.html
*/ ?>
<?php
 
class Moogento_NoMoreSpam_Model_NoMoreSpam extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('nomorespam/nomorespam');
    }
	public function getReviewsToday(){
		$today = date("Y-m-d");
		$review_collection     = Mage::getModel('review/review')->getCollection();
		$review_collection = $review_collection->addFieldToFilter('created_at', 
		array(
		   'from' => $today,
		));
		return $review_collection;
	}
	
	public function checkCronTime()
    {
    	$mage_time = Mage::getModel('core/date')->timestamp(time());  
    	
		$current_time =  date('H:i:s', $mage_time);	
    	$cron_config_time = Mage::getStoreConfig('no_more_spam/no_spam/specific_time');
    	$cron_period = 5;
    	if(is_numeric($cron_period))
    		$cron_period = 60*$cron_period;
    	else
    		$cron_period = 300;
    	$cron_config_time = str_replace(',',':',$cron_config_time);
    	$time_cron =  strtotime($current_time);
		$time_config = strtotime($cron_config_time);	
		if((($time_cron -30) <= $time_config) && ($time_config <= ($time_cron + $cron_period -30)))
    	{
			return true;
    	}
    	return false;
    }
    
	public function sendEmailReview(){
		$new_no_spam_review_yn = mage::getStoreConfig("no_more_spam/no_spam/new_no_spam_review");
		$enabled_review_yn = mage::getStoreConfig("no_more_spam/no_spam/enabled_review");
		if($enabled_review_yn == 0)
		return;
		
		if($new_no_spam_review_yn==0)
		return;
		
		if($this->checkCronTime() == false)
			return;
		$review_collection = $this->getReviewsToday();
		$ids = array();
		$reviews_collection = array();
		foreach($review_collection as $review){
			
			$review_id = $review->getData('review_id');
			$ids[] =$review_id; 
			$reviews_collection[] = $review;
		}
		Mage::helper("nomorespam")->sendEmailNotify($ids,$reviews_collection);
	}
}

<?php /** 
* Moogento
* 
* SOFTWARE LICENSE
* 
* This source file is covered by the Moogento End User License Agreement
* that is bundled with this extension in the file License.html
* It is also available online here:
* http://www.moogento.com/License.html
* 
* NOTICE
* 
* If you customize this file please remember that it will be overwrtitten
* with any future upgrade installs. 
* If you'd like to add a feature which is not in this software, get in touch
* at www.moogento.com for a quote.
* 
* ID          pe+sMEDTrtCzNq3pehW9DJ0lnYtgqva4i4Z=
* File        Status.php
* @category   Moogento
* @package    noMoreSpam
* @copyright  Copyright (c) 2014 Moogento <info@moogento.com> / All rights reserved.
* @license    http://www.moogento.com/License.html
*/ ?>
<?php

class Moogento_NoMoreSpam_Model_Email extends Varien_Object
{
    public function _sendAnEmail(){
		$storeId = Mage::app()->getStore()->getId();
		//$templateId = 'catalog_giftrequest_email_template';//here you can use template id defined in XML or you can use template ID in database (would be 1,2,3,4 .....etc)
		$mailSubject = Mage::getStoreConfig('no_more_spam/no_spam/email_subject');
		$mailBody = Mage::getStoreConfig('no_more_spam/no_spam/email_body');
		$sender = array('name' => $from,
		'email' => $from);
		$email = $to;

		$name = '';

		$mailTemplate = Mage::getModel('core/email_template');

		$mailTemplate->setTemplateSubject($mailSubject)
		->sendTransactional($templateId, $sender, $email, $name, $vars, $storeId);
	}
}

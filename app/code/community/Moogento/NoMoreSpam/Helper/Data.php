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
* File        Data.php
* @category   Moogento
* @package    noMoreSpam
* @copyright  Copyright (c) 2014 Moogento <info@moogento.com> / All rights reserved.
* @license    http://www.moogento.com/License.html
*/ ?>
<?php

class Moogento_NoMoreSpam_Helper_Data extends Mage_Core_Helper_Abstract
{
	const XML_PATH_EMAIL_NO_MORE_SPAM = 'no_more_spam/no_spam/email_template';
	protected $reviews_collection = array();
    private function _crypto_rand_secure($min, $max) {
        $range = $max - $min;
        if ($range < 0) return $min; // not so random...
        $log = log($range, 2);
        $bytes = (int) ($log / 8) + 1; // length in bytes
        $bits = (int) $log + 1; // length in bits
        $filter = (int) (1 << $bits) - 1; // set all lower bits to 1
        do {
            if(function_exists("openssl_random_pseudo_bytes"))
            	$rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
            else
            	$rnd = hexdec(bin2hex("01010011"));
            $rnd = $rnd & $filter; // discard irrelevant bits
        } while ($rnd >= $range);
        return $min + $rnd;
    }

    private function _getToken($length=32){
        $token = "";
        $codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $codeAlphabet.= "abcdefghijklmnopqrstuvwxyz";
        $codeAlphabet.= "0123456789";
        for($i=0;$i<$length;$i++){
            $token .= $codeAlphabet[$this->_crypto_rand_secure(0,strlen($codeAlphabet))];
        }
        return $token;
    }
    private function _getNmsId(){  //nms_nms_id
        $nms_id = Mage::getStoreConfig("no_more_spam/no_spam/nms_id");
        if($nms_id==''){
            $nms_id = $this->_getToken(8);
            Mage::getModel('core/config')->saveConfig("no_more_spam/no_spam/nms_id", $nms_id );
        }
        return $nms_id;
    }
    private function _getNmsField1(){  //nms_field_1
        $field_1 = Mage::getStoreConfig("no_more_spam/no_spam/field_1");
        if($field_1==''){
            $field_1 = $this->_getToken(6);
            Mage::getModel('core/config')->saveConfig("no_more_spam/no_spam/field_1", $field_1 );
        }
        return $field_1;
    }
    private function _getNmsField2(){ //nms_field_2
        $field_2 = Mage::getStoreConfig("no_more_spam/no_spam/field_2");
        if($field_2==''){
            $field_2 = $this->_getToken(6);
            Mage::getModel('core/config')->saveConfig("no_more_spam/no_spam/field_2", $field_2 );
        }
        return $field_2;
    }
	private function _getNmsTooFast(){
		$field = Mage::getStoreConfig("no_more_spam/no_spam/field_toofast");
        if($field==''){
            $field = $this->_getToken(6);
            Mage::getModel('core/config')->saveConfig("no_more_spam/no_spam/field_toofast", $field );
        }
        return $field;
	}
	public function IsToofast(){
		return Mage::getStoreConfig('no_more_spam/no_spam/too_fast_form');
	}
    public function getNmsId(){
        return $this->_getNmsId();
    }
	public function getNmsField1(){
        return $this->_getNmsField1();
	}
	public function getNmsField2(){
        return $this->_getNmsField2();
	}
	public function getNmsTooFast(){
		return $this->_getNmsTooFast();
	}
    public function createvalue(){
        $nms_field_1 = $this->_getNmsField1();
        $nms_field_2 = $this->_getNmsField2();
        $hash_result = hash("sha256", $nms_field_2 . $nms_field_1);
        return $hash_result;
    }
    public function getReviewsCollection()
    {
    	return $this->reviews_collection;
    }
	public function sendEmailNoMore(){
		//$translate = Mage::getSingleton('core/translate');
        /* @var $translate Mage_Core_Model_Translate */
        //$translate->setTranslateInline(false);
		//$sender_email = Mage::getStoreConfig("no_more_spam/no_spam/new_no_spam_email_sender");
		//$sender_name = Mage::getStoreConfig("no_more_spam/no_spam/new_no_spam_name_sender");
		$sender_name = Mage::getStoreConfig("no_more_spam/no_spam/sender_email_identity");
		$sender_email = Mage::getStoreConfig("no_more_spam/no_spam/ident_" . $sender_name . "_email");
		$sender[] = array(
			'email' => $sender_email,
			'name' => $sender_name
		);
		$recipient_email = Mage::getStoreConfig("no_more_spam/no_spam/new_no_spam_review_notify");
		$recipient_name = Mage::getStoreConfig("no_more_spam/no_spam/new_no_spam_review_notify_name");
		$email_subject = Mage::getStoreConfig("no_more_spam/no_spam/email_subject");
		$email_body = Mage::getStoreConfig("no_more_spam/no_spam/email_body");
        $mailTemplate = Mage::getModel('core/email_template');
        /* @var $mailTemplate Mage_Core_Model_Email_Template */
		
        $template = self::XML_PATH_EMAIL_NO_MORE_SPAM;
        //create the object  
		$mail = Mage::getModel('core/email');  
		//set the reciever name  
		$mail->setToName($recipient_name);  
		//set the reciever mail  
		$mail->setToEmail($recipient_email);  
		//mail body  
		$mail->setBody($email_body);  
		//set subject  
		$mail->setSubject($email_subject);  
		//set sender mail id  
		$mail->setFromEmail($sender_email);  
		//message in subject  
		$mail->setFromName($sender_name);  
		// YOu can use Html or text as Mail format  
		$mail->setType('html');   
		  
		try {  
			//send mail  
			$mail->send();  
			//show success msg  
			Mage::getSingleton('core/session')->addSuccess('Your request has been sent');  
			// redirect to url  
			//$this->_redirect('');  
		}  
		catch (Exception $e) {  
			//if not success then show error msg  
			Mage::getSingleton('core/session')->addError('Unable to send.');  
			//$this->_redirect('');  
		}  
	}
	public function _getUrlActive($id){
		//$status = Mage_Review_Model_Review::STATUS_APPROVED;
		//$reviews = array();
		//$reviews[1] = $id;
		//return Mage::helper("adminhtml")->getUrl('adminhtml/catalog_product_review/massUpdateStatus', array('reviews' => $reviews, 'status' => $status));
		return Mage::getUrl('nomorespam/active/active', array('id' => $id));
	}
	public function _getUrlDelete($id){
		return Mage::helper("adminhtml")->getUrl('adminhtml/catalog_product_review/delete', array('id' => $id));
		//return Mage::getUrl('nomorespam/active/delete', array('id' => $id)); 
	}
	public function sendEmailNotify($ids,$reviews_collection = array())
	{
		$this->reviews_collection = $reviews_collection;
		$templateId = Mage::getStoreConfig(self::XML_PATH_EMAIL_NO_MORE_SPAM);
		if(strlen(trim($templateId)) == 0)
			$templateId = 'moogento_noremorespam_notify_new_review_email_template';
			
		$sender_email_identity = Mage::getStoreConfig("no_more_spam/no_spam/sender_email_identity");
		switch($sender_email_identity){
			case 'general':
				$sender_email = Mage::getStoreConfig('trans_email/ident_general/email');
				$sender_name  = Mage::getStoreConfig('trans_email/ident_general/name'); 
			break;
			case 'support':
				$sender_email = Mage::getStoreConfig('trans_email/ident_sales/email');
				$sender_name  = Mage::getStoreConfig('trans_email/ident_sales/name'); 
			break;
			case 'support':
				$sender_email = Mage::getStoreConfig('trans_email/ident_support/email');
				$sender_name  = Mage::getStoreConfig('trans_email/ident_support/name'); 
			break;
			case 'custom1':
				$sender_email = Mage::getStoreConfig('trans_email/ident_custom1/email');
				$sender_name  = Mage::getStoreConfig('trans_email/ident_custom1/name'); 
			break;
			case 'custom2':
				$sender_email = Mage::getStoreConfig('trans_email/ident_custom2/email');
				$sender_name  = Mage::getStoreConfig('trans_email/ident_custom2/name'); 
			break;
			default:
				$sender_email = Mage::getStoreConfig('trans_email/ident_general/email');
				$sender_name  = Mage::getStoreConfig('trans_email/ident_general/name'); 
			break;
		}
		$sender = array(
			'email' => $sender_email,
			'name' => $sender_name
		);
		$recipient_email = Mage::getStoreConfig("no_more_spam/no_spam/new_no_spam_review_notify");
		$recipient_name = Mage::getStoreConfig("no_more_spam/no_spam/new_no_spam_review_notify_name");
		$vars = Array();
		$storeId = Mage::app()->getStore()->getId();
		$translate = Mage::getSingleton('core/translate');						    
		try {
			Mage::getModel('core/email_template')
			->sendTransactional($templateId, $sender, $recipient_email, $recipient_name, $vars, $storeId);
			Mage::getSingleton('core/session')->addSuccess('Email has been sent');  
		}
		catch(Exception $e){
			Mage::getSingleton('core/session')->addError('Unable to send email.');  
		}
		$translate->setTranslateInline(true);
	}
	private $_spam_link_words = array('a href', '[url', 'http', '://', '[link', 'www.');
	
    public function getHashKey(){
        $nms_field_1 = mage::getStoreConfig("no_more_spam/no_spam/field_1");
        $nms_field_2 = mage::getStoreConfig("no_more_spam/no_spam/field_2");
        $hash_result = hash("sha256", $nms_field_2 . $nms_field_1);
        return $hash_result;
    }
	
    public function checkHashKey($key_form, $empty_key){
    	
        $hash_key = $this->getHashKey();
        $isKey = false;
        if(($key_form==$hash_key) && $empty_key==''){
            $isKey = true;
        }
        return $isKey;
    }
	
    private function _checkForLink($text){	
		foreach($this->_spam_link_words as $a) {
			if (stripos($text,$a) !== false) return true;
		}
		return false;
    }
	
    public function _checkManualSpam($post){
		$has_spam = FALSE;
		$time_end = microtime(true);
		try {
			$check_name_yn = 1;
			$check_message_yn = 0;
			$field_toofast = Mage::helper("nomorespam")->getNmsTooFast();
	        //$post = $this->getRequest()->getPost();
			$check_review_yn = mage::getStoreConfig('no_more_spam/no_spam/enabled_review_review_link');
			$check_title_yn = mage::getStoreConfig('no_more_spam/no_spam/enabled_review_title_link');
			$check_faster_yn = mage::getStoreConfig('no_more_spam/no_spam/too_fast_form');
			
			if(($check_review_yn == 1) && (isset($post['detail'])) && (trim($post['detail'])!='') ) {
				if($this->_checkForLink($post['detail']) === TRUE) {$has_spam = TRUE;}
			}
			
			if(($check_title_yn == 1) && (isset($post['title'])) && (trim($post['title'])!='') ) {
				if($this->_checkForLink($post['title']) === TRUE) {$has_spam = TRUE;}
			}
			
			if(($check_faster_yn == 1) && (isset($post[$field_toofast])) && (trim($post[$field_toofast])!='') ) {
				$time_start = $post[$field_toofast];
				if($time_end - $time_start <=3 ) {$has_spam = TRUE;}
			}
		} catch (Exception $e) {
            Mage::getSingleton('customer/session')->addError(Mage::helper('contacts')->__('Error: Unable to send this message. Please, try again later').' '.$e);
            $this->_redirect('*/*/');
		}	
        return $has_spam;
    }
    public function checkHashKey_f($data,$rating){
    	$isKeyHash = false;
    	$session    = Mage::getSingleton('core/session');
        $field_1 = $this->getNmsField1();
        $empty_text = $this->getNmsField2();
        if(isset($data[$field_1]) && isset($data[$empty_text])){
            $isKeyHash = $this->checkHashKey($data[$field_1], $data[$empty_text]);
        }
        else 
        {
            if(!(isset($data[$field_1])) && (!(isset($data[$empty_text]))))
            {
                $isKeyHash = true; 
                $moo_message = 'Your review form is not protected as you have a custom-coded form. Please add the code described *<a href="http://www.moogento.com/guides/noMoreSpam!_Quickstart">here</a>* to enable NoMoreSpam! for your product reviews.';
                Mage::getSingleton('adminhtml/session')->addWarning($moo_message);
                Mage::log($moo_message, null, 'moogento_nomorespam.log');
            }
        }
        $enable_review = mage::getStoreConfig("no_more_spam/no_spam/enabled_review");
        $enable_review_rating = mage::getStoreConfig("no_more_spam/no_spam/enabled_review_rating");
        if(!$enable_review) $isKeyHash = true; // if this section is off in nomorespam config then don't check our fields
        $rating_collection = mage::getModel("rating/rating")->getCollection();
        if($this->_checkManualSpam($data) || (count($rating) < count($rating_collection) && $enable_review_rating == 1)) {
        	$isKeyHash = false;
            if($this->_checkManualSpam($data))
                $session->addError($this->__('Unable to add this review at the moment.'));
            else
                $session->addError($this->__('Unable to add this review at the moment. Please choise ratings'));
        }
        return $isKeyHash;
    }
}

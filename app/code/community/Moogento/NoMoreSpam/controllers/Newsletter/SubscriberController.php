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
* at moogento.com for a quote.
* 
* ID          pe+sMEDTrtCzNq3pehW9DJ0lnYtgqva4i4Z=
* File        ProductController.php
* @category   Moogento
* @package    noMoreSpam
* @copyright  Copyright (c) 2016 Moogento <info@moogento.com> / All rights reserved.
* @license    https://moogento.com/License.html
*/ ?>
<?php
require_once 'Mage/Newsletter/controllers/SubscriberController.php';
class Moogento_NoMoreSpam_Newsletter_SubscriberController extends Mage_Newsletter_SubscriberController
{
	private $_spam_link_words = array('a href', '[url', 'http', '://', '[link', 'www.');
	
    private function getHashKey(){
        $nms_field_1 = mage::getStoreConfig("no_more_spam/no_spam/field_1");
        $nms_field_2 = mage::getStoreConfig("no_more_spam/no_spam/field_2");
        $hash_result = hash("sha256", $nms_field_2 . $nms_field_1);
        return $hash_result;
    }
	
    private function checkHashKey($key_form, $empty_key){
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
	
    private function _checkManualSpam(){
		$has_spam = FALSE;
		$time_end = microtime(true);
		try {
			$check_name_yn = 1;
			$check_message_yn = 0;
			$field_toofast = Mage::helper("nomorespam")->getNmsTooFast();
	        $post = $this->getRequest()->getPost();
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
	/**
      * New subscription action
      */
    public function newAction()
    {
		$data = $this->getRequest()->getPost();
		$isKeyHash = false;
		$field_1 = Mage::helper("nomorespam")->getNmsField1();
		$empty_text = Mage::helper("nomorespam")->getNmsField2();

        if(isset($data[$field_1]) && isset($data[$empty_text])){
            $isKeyHash = $this->checkHashKey($data[$field_1], $data[$empty_text]);
        }
		//else $isKeyHash = true; //
        $enabled_newsletter = mage::getStoreConfig("no_more_spam/no_spam/enabled_newsletter");
		$session            = Mage::getSingleton('core/session');
		if(!$enabled_newsletter) $isKeyHash = true; // if this section is off in nomorespam config then don't check our fields
        if ($this->getRequest()->isPost() && $this->getRequest()->getPost('email') && $isKeyHash) {
            $customerSession    = Mage::getSingleton('customer/session');
            $email              = (string) $this->getRequest()->getPost('email');

            try {
                if (!Zend_Validate::is($email, 'EmailAddress')) {
                    Mage::throwException($this->__('Please enter a valid email address.'));
                }

                if (Mage::getStoreConfig(Mage_Newsletter_Model_Subscriber::XML_PATH_ALLOW_GUEST_SUBSCRIBE_FLAG) != 1 && 
                    !$customerSession->isLoggedIn()) {
                    Mage::throwException($this->__('Sorry, but administrator denied subscription for guests. Please <a href="%s">register</a>.', Mage::helper('customer')->getRegisterUrl()));
                }

                $ownerId = Mage::getModel('customer/customer')
                        ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
                        ->loadByEmail($email)
                        ->getId();
                if ($ownerId !== null && $ownerId != $customerSession->getId()) {
                    Mage::throwException($this->__('This email address is already assigned to another user.'));
                }

                $status = Mage::getModel('newsletter/subscriber')->subscribe($email);
                if ($status == Mage_Newsletter_Model_Subscriber::STATUS_NOT_ACTIVE) {
                    $session->addSuccess($this->__('Confirmation request has been sent.'));
                }
                else {
                    $session->addSuccess($this->__('Thank you for your subscription.'));
                }
            }
            catch (Mage_Core_Exception $e) {
                $session->addException($e, $this->__('There was a problem with the subscription: %s', $e->getMessage()));
            }
            catch (Exception $e) {
                $session->addException($e, $this->__('There was a problem with the subscription.'));
            }
			$this->_redirectReferer();
        }
		else{
			$session->addError($this->__('Unable subscription. Try again later.'));
			$this->_redirectReferer();
		}
    }
	// /**
     // * Create customer account action
     // */
    // public function createPostAction()
    // {
		// $data = $this->getRequest()->getPost();
		// $isKeyHash = false;
		// $field_1 = Mage::helper("nomorespam")->getNmsField1();
		// $empty_text = Mage::helper("nomorespam")->getNmsField2();

        // if(isset($data[$field_1]) && isset($data[$empty_text])){
            // $isKeyHash = $this->checkHashKey($data[$field_1], $data[$empty_text]);
        // }
		// //else $isKeyHash = true; //
        // $enable_create_account = mage::getStoreConfig("no_more_spam/no_spam/create_account");
		// if(!$enable_create_account) $isKeyHash = true; // if this section is off in nomorespam config then don't check our fields

        // /** @var $session Mage_Customer_Model_Session */
        // $session = $this->_getSession();
        // if ($session->isLoggedIn()) {
            // $this->_redirect('*/*/');
            // return;
        // }
        // $session->setEscapeMessages(true); // prevent XSS injection in user input
        // if (!$this->getRequest()->isPost()) {
            // $errUrl = $this->_getUrl('*/*/create', array('_secure' => true));
            // $this->_redirectError($errUrl);
            // return;
        // }
		// if($isKeyHash){
			// $customer = $this->_getCustomer();
			
			// try {
				// $errors = $this->_getCustomerErrors($customer);

				// if (empty($errors)) {
					// $customer->save();
					// $this->_dispatchRegisterSuccess($customer);
					// $this->_successProcessRegistration($customer);
					// return;
				// } else {
					// $this->_addSessionError($errors);
				// }
			// } catch (Mage_Core_Exception $e) {
				// $session->setCustomerFormData($this->getRequest()->getPost());
				// if ($e->getCode() === Mage_Customer_Model_Customer::EXCEPTION_EMAIL_EXISTS) {
					// $url = $this->_getUrl('customer/account/forgotpassword');
					// $message = $this->__('There is already an account with this email address. If you are sure that it is your email address, <a href="%s">click here</a> to get your password and access your account.', $url);
					// $session->setEscapeMessages(false);
				// } else {
					// $message = $e->getMessage();
				// }
				// $session->addError($message);
			// } catch (Exception $e) {
				// $session->setCustomerFormData($this->getRequest()->getPost())
					// ->addException($e, $this->__('Cannot save the customer.'));
			// }
			// $errUrl = $this->_getUrl('*/*/create', array('_secure' => true));
			// $this->_redirectError($errUrl);
		// }
		// $session->addError($this->__('Unable create account.'));
        // $errUrl = $this->_getUrl('*/*/create', array('_secure' => true));
        // $this->_redirectError($errUrl);
    // }
}

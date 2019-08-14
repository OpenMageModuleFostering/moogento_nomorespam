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
* File        ProductController.php
* @category   Moogento
* @package    noMoreSpam
* @copyright  Copyright (c) 2016 Moogento <info@moogento.com> / All rights reserved.
* @license    https://moogento.com/License.html
*/ ?>
<?php
require_once 'Mage/Customer/controllers/AccountController.php';
class Moogento_NoMoreSpam_Customer_AccountController extends Mage_Customer_AccountController
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
     * Create customer account action
     */
    public function createPostAction()
    {
		$data = $this->getRequest()->getPost();
		$isKeyHash = false;
		$field_1 = Mage::helper("nomorespam")->getNmsField1();
		$empty_text = Mage::helper("nomorespam")->getNmsField2();

        if(isset($data[$field_1]) && isset($data[$empty_text])){
            $isKeyHash = $this->checkHashKey($data[$field_1], $data[$empty_text]);
        }
		//else $isKeyHash = true; //
        $enable_create_account = mage::getStoreConfig("no_more_spam/no_spam/create_account");
		if(!$enable_create_account) $isKeyHash = true; // if this section is off in nomorespam config then don't check our fields
		$version_magento = substr(mage::getVersion(),0,3);
		$version_magento_full = mage::getVersion();
		$version_magento = substr($version_magento_full,0,3);
		$version_magento_sub = substr($version_magento_full,3,1);
		
        /** @var $session Mage_Customer_Model_Session */
        $session = $this->_getSession();
        if ($session->isLoggedIn()) {
            $this->_redirect('*/*/');
            return;
        }
        $session->setEscapeMessages(true); // prevent XSS injection in user input
        if($version_magento == '1.9')
	        if (!$this->getRequest()->isPost()) {
	            $errUrl = $this->_getUrl('*/*/create', array('_secure' => true));
	            $this->_redirectError($errUrl);
	            return;
	        }
	    if($version_magento == '1.9'){
	        if($isKeyHash){
	        	$customer = $this->_getCustomer();
		        try {
		            $errors = $this->_getCustomerErrors($customer);

		            if (empty($errors)) {
						// cleanPaswordsValidationData doesn't exist until Magento 1.9.1 +
						// Thanks to Mark Hewgill for this fix :)
                		if(isset($version_magento_sub) && ($version_magento_sub > 0)) {
		                $customer->cleanPasswordsValidationData();
						}
		                $customer->save();
		                $this->_dispatchRegisterSuccess($customer);
		                $this->_successProcessRegistration($customer);
		                return;
		            } else {
		                $this->_addSessionError($errors);
		            }
		        } catch (Mage_Core_Exception $e) {
		            $session->setCustomerFormData($this->getRequest()->getPost());
		            if ($e->getCode() === Mage_Customer_Model_Customer::EXCEPTION_EMAIL_EXISTS) {
		                $url = $this->_getUrl('customer/account/forgotpassword');
		                $message = $this->__('There is already an account with this email address. If you are sure that it is your email address, <a href="%s">click here</a> to get your password and access your account.', $url);
		                $session->setEscapeMessages(false);
		            } else {
		                $message = $e->getMessage();
		            }
		            $session->addError($message);
		        } catch (Exception $e) {
		            $session->setCustomerFormData($this->getRequest()->getPost())
		                ->addException($e, $this->__('Cannot save the customer.'));
		        }
	        }
	    }
	    elseif($version_magento != '1.9'){
			if($isKeyHash){
				if ($this->getRequest()->isPost()) {
		            $errors = array();

		            if (!$customer = Mage::registry('current_customer')) {
		                $customer = Mage::getModel('customer/customer')->setId(null);
		            }

		            /* @var $customerForm Mage_Customer_Model_Form */
		            $customerForm = Mage::getModel('customer/form');
		            $customerForm->setFormCode('customer_account_create')
		                ->setEntity($customer);

		            $customerData = $customerForm->extractData($this->getRequest());

		            if ($this->getRequest()->getParam('is_subscribed', false)) {
		                $customer->setIsSubscribed(1);
		            }

		            /**
		             * Initialize customer group id
		             */
		            $customer->getGroupId();

		            if ($this->getRequest()->getPost('create_address')) {
		                /* @var $address Mage_Customer_Model_Address */
		                $address = Mage::getModel('customer/address');
		                /* @var $addressForm Mage_Customer_Model_Form */
		                $addressForm = Mage::getModel('customer/form');
		                $addressForm->setFormCode('customer_register_address')
		                    ->setEntity($address);

		                $addressData    = $addressForm->extractData($this->getRequest(), 'address', false);
		                $addressErrors  = $addressForm->validateData($addressData);
		                if ($addressErrors === true) {
		                    $address->setId(null)
		                        ->setIsDefaultBilling($this->getRequest()->getParam('default_billing', false))
		                        ->setIsDefaultShipping($this->getRequest()->getParam('default_shipping', false));
		                    $addressForm->compactData($addressData);
		                    $customer->addAddress($address);

		                    $addressErrors = $address->validate();
		                    if (is_array($addressErrors)) {
		                        $errors = array_merge($errors, $addressErrors);
		                    }
		                } else {
		                    $errors = array_merge($errors, $addressErrors);
		                }
		            }

		            try {
		                $customerErrors = $customerForm->validateData($customerData);
		                if ($customerErrors !== true) {
		                    $errors = array_merge($customerErrors, $errors);
		                } else {
		                    $customerForm->compactData($customerData);
		                    $customer->setPassword($this->getRequest()->getPost('password'));
		                    $customer->setConfirmation($this->getRequest()->getPost('confirmation'));
		                    $customerErrors = $customer->validate();
		                    if (is_array($customerErrors)) {
		                        $errors = array_merge($customerErrors, $errors);
		                    }
		                }

		                $validationResult = count($errors) == 0;
		                if (true === $validationResult) {
		                    $customer->save();

		                    Mage::dispatchEvent('customer_register_success',
		                        array('account_controller' => $this, 'customer' => $customer)
		                    );

		                    if ($customer->isConfirmationRequired()) {
		                        $customer->sendNewAccountEmail(
		                            'confirmation',
		                            $session->getBeforeAuthUrl(),
		                            Mage::app()->getStore()->getId()
		                        );
		                        $session->addSuccess($this->__('Account confirmation is required. Please, check your email for the confirmation link. To resend the confirmation email please <a href="%s">click here</a>.', Mage::helper('customer')->getEmailConfirmationUrl($customer->getEmail())));
		                        $this->_redirectSuccess(Mage::getUrl('*/*/index', array('_secure'=>true)));
		                        return;
		                    } else {
		                        $session->setCustomerAsLoggedIn($customer);
		                        $url = $this->_welcomeCustomer($customer);
		                        $this->_redirectSuccess($url);
		                        return;
		                    }
		                } else {
		                    $session->setCustomerFormData($this->getRequest()->getPost());
		                    if (is_array($errors)) {
		                        foreach ($errors as $errorMessage) {
		                            $session->addError($errorMessage);
		                        }
		                    } else {
		                        $session->addError($this->__('Invalid customer data'));
		                    }
		                }
		            } catch (Mage_Core_Exception $e) {
		                $session->setCustomerFormData($this->getRequest()->getPost());
		                if ($e->getCode() === Mage_Customer_Model_Customer::EXCEPTION_EMAIL_EXISTS) {
		                    $url = Mage::getUrl('customer/account/forgotpassword');
		                    $message = $this->__('There is already an account with this email address. If you are sure that it is your email address, <a href="%s">click here</a> to get your password and access your account.', $url);
		                    $session->setEscapeMessages(false);
		                } else {
		                    $message = $e->getMessage();
		                }
		                $session->addError($message);
		            } catch (Exception $e) {
		                $session->setCustomerFormData($this->getRequest()->getPost())
		                    ->addException($e, $this->__('Cannot save the customer.'));
		            }
		        }

		        $this->_redirectError(Mage::getUrl('*/*/create', array('_secure' => true)));
			}
		}
		$session->addError($this->__('Unable create account.'));
		$this->_redirect('*/*/create');
    }

    // public function postAction()
    // {
        // if ($data = Mage::getSingleton('review/session')->getFormData(true)) {
            // $rating = array();
            // if (isset($data['ratings']) && is_array($data['ratings'])) {
                // $rating = $data['ratings'];
            // }
        // } else {
            // $data   = $this->getRequest()->getPost();
            // $rating = $this->getRequest()->getParam('ratings', array());
        // }   

        // $isKeyHash = false;
		// $field_1 = Mage::helper("nomorespam")->getNmsField1();
		// $empty_text = Mage::helper("nomorespam")->getNmsField2();

        // if(isset($data[$field_1]) && isset($data[$empty_text])){
            // $isKeyHash = $this->checkHashKey($data[$field_1], $data[$empty_text]);
        // }
		// //else $isKeyHash = true; //
        // $enable_review = mage::getStoreConfig("no_more_spam/no_spam/enabled_review");
		// if(!$enable_review) $isKeyHash = true; // if this section is off in nomorespam config then don't check our fields

	    // $session    = Mage::getSingleton('core/session');
        // /* @var $session Mage_Core_Model_Session */
        // $review     = Mage::getModel('review/review')->setData($data);
        // /* @var $review Mage_Review_Model_Review */
        // if (($product = $this->_initProduct()) && !empty($data) && $isKeyHash) {
            // // $session    = Mage::getSingleton('core/session');
// //             /* @var $session Mage_Core_Model_Session */
// //             $review     = Mage::getModel('review/review')->setData($data);
// //             /* @var $review Mage_Review_Model_Review */

            // $validate = $review->validate();
            // if ($validate === true) {
		        // if($this->_checkManualSpam()) {	
		        	// $session->addError($this->__('Unable to add this review at the moment.'));
		        // }
				// else
				// {
					// if(count($rating) < 3){
						// $session->addError($this->__('Unable to add this review at the moment. Please choise ratings'));
					// }
					// else{
						// try {
							// $review->setEntityId($review->getEntityIdByCode(Mage_Review_Model_Review::ENTITY_PRODUCT_CODE))
								// ->setEntityPkValue($product->getId())
								// ->setStatusId(Mage_Review_Model_Review::STATUS_PENDING)
								// ->setCustomerId(Mage::getSingleton('customer/session')->getCustomerId())
								// ->setStoreId(Mage::app()->getStore()->getId())
								// ->setStores(array(Mage::app()->getStore()->getId()))
								// ->save();

							// foreach ($rating as $ratingId => $optionId) {
								// Mage::getModel('rating/rating')
									// ->setRatingId($ratingId)
									// ->setReviewId($review->getId())
									// ->setCustomerId(Mage::getSingleton('customer/session')->getCustomerId())
									// ->addOptionVote($optionId, $product->getId());
							// }

							// $review->aggregate();
							// $session->addSuccess($this->__('Your review has been accepted for moderation.'));
						// }
						// catch (Exception $e) {
							// $session->setFormData($data);
							// $session->addError($this->__('Unable to add the review.'));
						// }
					// }
				// }
            // }
            // else {
                // $session->setFormData($data);
                // if (is_array($validate)) {
                    // foreach ($validate as $errorMessage) {
                        // $session->addError($errorMessage);
                    // }
					// $session->addError($this->__('Sorry, unable to post the review.'));
                // }
                // else {
                    // $session->addError($this->__('Unable to post the review.'));
                // }
            // }
        // }else{
			// $session->setFormData($data);
            // $session->addError($this->__('Could\'t post the review, sorry.'));
        // }
		// //Mage::helper("nomorespam")->sendEmailNotify($review->getId());
        // if ($redirectUrl = Mage::getSingleton('review/session')->getRedirectUrl(true)) {
            // $this->_redirectUrl($redirectUrl);
            // return;
        // }
        // $this->_redirectReferer();
    // }
}

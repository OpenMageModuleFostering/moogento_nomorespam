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
* File        ProductController.php
* @category   Moogento
* @package    noMoreSpam
* @copyright  Copyright (c) 2014 Moogento <info@moogento.com> / All rights reserved.
* @license    http://www.moogento.com/License.html
*/ ?>
<?php
require_once 'Mage/Review/controllers/ProductController.php';
class Moogento_NoMoreSpam_Review_ProductController extends Mage_Review_ProductController
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
	
    public function postAction()
    {
    	
        if ($data = Mage::getSingleton('review/session')->getFormData(true)) {
            $rating = array();
            if (isset($data['ratings']) && is_array($data['ratings'])) {
                $rating = $data['ratings'];
            }
        } else {
            $data   = $this->getRequest()->getPost();
            $rating = $this->getRequest()->getParam('ratings', array());
        }   
        $enable_review = mage::getStoreConfig("no_more_spam/no_spam/enabled_review");
        $enable_review_rating = mage::getStoreConfig("no_more_spam/no_spam/enabled_review_rating");
        $isKeyHash = false;
        if($enable_review == 1){
			$field_1 = Mage::helper("nomorespam")->getNmsField1();
			$empty_text = Mage::helper("nomorespam")->getNmsField2();

	        if(isset($data[$field_1]) && isset($data[$empty_text])){
	            $isKeyHash = $this->checkHashKey($data[$field_1], $data[$empty_text]);
	        }
	    }
		//else $isKeyHash = true; //
        
		if(!$enable_review) $isKeyHash = true; // if this section is off in nomorespam config then don't check our fields

	    $session    = Mage::getSingleton('core/session');
        /* @var $session Mage_Core_Model_Session */
        $review     = Mage::getModel('review/review')->setData($data);
        /* @var $review Mage_Review_Model_Review */
        $rating_collection = mage::getModel("rating/rating")->getCollection();

        if (($product = $this->_initProduct()) && !empty($data) && $isKeyHash) {
            // $session    = Mage::getSingleton('core/session');
//             /* @var $session Mage_Core_Model_Session */
//             $review     = Mage::getModel('review/review')->setData($data);
//             /* @var $review Mage_Review_Model_Review */

            $validate = $review->validate();
            if ($validate === true) {
		        if($enable_review == 1 && $this->_checkManualSpam()) {	
		        	$session->addError($this->__('Unable to add this review at the moment.'));
		        }
				else
				{
					if($enable_review == 1 && count($rating) < count($rating_collection) && $enable_review_rating == 1){
						$session->addError($this->__('Unable to add this review at the moment. Please choise ratings'));
					}
					else{
						try {
							$review->setEntityId($review->getEntityIdByCode(Mage_Review_Model_Review::ENTITY_PRODUCT_CODE))
								->setEntityPkValue($product->getId())
								->setStatusId(Mage_Review_Model_Review::STATUS_PENDING)
								->setCustomerId(Mage::getSingleton('customer/session')->getCustomerId())
								->setStoreId(Mage::app()->getStore()->getId())
								->setStores(array(Mage::app()->getStore()->getId()))
								->save();

							foreach ($rating as $ratingId => $optionId) {
								Mage::getModel('rating/rating')
									->setRatingId($ratingId)
									->setReviewId($review->getId())
									->setCustomerId(Mage::getSingleton('customer/session')->getCustomerId())
									->addOptionVote($optionId, $product->getId());
							}

							$review->aggregate();
							$session->addSuccess($this->__('Your review has been accepted for moderation.'));
						}
						catch (Exception $e) {
							$session->setFormData($data);
							$session->addError($this->__('Unable to add the review.'));
						}
					}
				}
            }
            else {
                $session->setFormData($data);
                if (is_array($validate)) {
                    foreach ($validate as $errorMessage) {
                        $session->addError($errorMessage);
                    }
					$session->addError($this->__('Sorry, unable to post the review.'));
                }
                else {
                    $session->addError($this->__('Unable to post the review.'));
                }
            }
        }else{
			$session->setFormData($data);
            $session->addError($this->__('Couldn\'t post the review, sorry.'));
        }
		//Mage::helper("nomorespam")->sendEmailNotify($review->getId());
        if ($redirectUrl = Mage::getSingleton('review/session')->getRedirectUrl(true)) {
            $this->_redirectUrl($redirectUrl);
            return;
        }
        $this->_redirectReferer();
    }
}

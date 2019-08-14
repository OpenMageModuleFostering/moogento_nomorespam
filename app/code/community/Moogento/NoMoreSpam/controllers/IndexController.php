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
* File        IndexController.php
* @category   Moogento
* @package    noMoreSpam
* @copyright  Copyright (c) 2016 Moogento <info@moogento.com> / All rights reserved.
* @license    https://moogento.com/License.html
*/ ?>
<?php
require_once 'Mage/Contacts/controllers/IndexController.php';
class Moogento_NoMoreSpam_IndexController extends Mage_Contacts_IndexController
{
	private $_spam_link_words = array('a href', '[url', 'http', '://', '[link', 'www.');
	
	private function makeLinks($str) {
        $reg_exUrl = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";
        $urls = array();
        $urlsToReplace = array();
        if(preg_match_all($reg_exUrl, $str, $urls)) {
            $numOfMatches = count($urls[0]);
            $numOfUrlsToReplace = 0;
            for($i=0; $i<$numOfMatches; $i++) {
                $alreadyAdded = false;
                $numOfUrlsToReplace = count($urlsToReplace);
                for($j=0; $j<$numOfUrlsToReplace; $j++) {
                    if($urlsToReplace[$j] == $urls[0][$i]) {
                        $alreadyAdded = true;
                    }
                }
                if(!$alreadyAdded) {
                    array_push($urlsToReplace, $urls[0][$i]);
                }
            }
            $numOfUrlsToReplace = count($urlsToReplace);
            for($i=0; $i<$numOfUrlsToReplace; $i++) {
                $str = str_replace($urlsToReplace[$i], "<a href=\"".$urlsToReplace[$i]."\">".$urlsToReplace[$i]."</a> ", $str);
            }
            return $str;
        } else {
            return $str;
        }
    }
    
    private function _getHashKey(){
        $nms_field_1 = mage::getStoreConfig("no_more_spam/no_spam/field_1");
        $nms_field_2 = mage::getStoreConfig("no_more_spam/no_spam/field_2");
        $hash_result = hash("sha256", $nms_field_2 . $nms_field_1);
        return $hash_result;
    }
	
    private function _checkHashKey($key_form, $empty_key){
        $hash_key = $this->_getHashKey();
        $isKey = false;
        if(($key_form==$hash_key) && $empty_key==''){
            $isKey = true;
        }
        return $isKey;
    }
	
    private function _checkForLink($text){	
    
//     	$text2 = '<a href="abc.com">Heellooo 1122.</a>';
//     	$text3 = "The text you want to filter goes here. http://google.com";
//     	$regex = "((https?|ftp)\:\/\/)?"; // SCHEME
// 		$regex .= "([a-z0-9+!*(),;?&=\$_.-]+(\:[a-z0-9+!*(),;?&=\$_.-]+)?@)?"; // User and Pass
// 		$regex .= "([a-z0-9-.]*)\.([a-z]{2,4})"; // Host or IP
// 		$regex .= "(\:[0-9]{2,5})?"; // Port
// 		$regex .= "(\/([a-z0-9+\$_-]\.?)+)*\/?"; // Path
// 		$regex .= "(\?[a-z+&\$_.-][a-z0-9;:@&%=+\/\$_.-]*)?"; // GET Query
// 		$regex .= "(#[a-z_.-][a-z0-9+\$_.-]*)?"; // Anchor
// 		
// 		$url_reg = '/(ftp|https?):\/\/(\w+:?\w*@)?(\S+)(:[0-9]+)?(\/([\w#!:.?+=&%@!\/-])?)?/';
// 		
// 		$reg_exUrl = '/(http|https|ftp|ftps|www.|href)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/';
// 
// 		// The Text you want to filter for urls
// 		
// 
		// Check if there is a url in the text
		// if(preg_match($reg_exUrl, $text, $url)) {
// 
// 			   // make the urls hyper links
// 			   echo preg_replace($reg_exUrl, '<a href="{$url[0]}">{$url[0]}</a> ', $text);
// 
// 		} else {
// 
// 			   // if no urls in the text just return the text
// 			   echo 'Text 1: '.$text;
// 
// 		}
// 		
// 		echo '<br/>';
// 		if(preg_match($reg_exUrl, $text2, $url)) {
// 
// 			   // make the urls hyper links
// 			   echo preg_replace($reg_exUrl, '<a href="{$url[0]}">{$url[0]}</a> ', $text2);
// 
// 		} else {
// 
// 			   // if no urls in the text just return the text
// 			   echo 'Text 2: '.$text2;
// 
// 		}
// 		
// 		echo '<br/>';
// 		if(preg_match($reg_exUrl, $text3, $url)) {
// 
// 			   // make the urls hyper links
// 			   echo preg_replace($reg_exUrl, "<a href='{$url[0]}'>{$url[0]}</a>", $text3);
// 
// 		} else {
// 
// 			   // if no urls in the text just return the text
// 			   echo 'Text 3: '.$text3;
// 
// 		}		
//     	
		foreach($this->_spam_link_words as $a) {
			if (stripos($text,$a) !== FALSE) 
				return true;
				// echo '<br/>'.$text.' -- '.$a;
// 			if (stripos($text2,$a) !== FALSE) 
// 				echo '<br/>'.$text2.' -- '.$a;
// 			if (stripos($text3,$a) !== FALSE) 
// 				echo '<br/>'.$text3.' -- '.$a;
// 				return true;
		}
		return false;
    }
	
    private function _checkManualSpam(){
		$has_spam = FALSE;
		try {
			$check_name_yn = 1;
			$check_message_yn = 0;
	        $post = $this->getRequest()->getPost();
			$check_name_yn = mage::getStoreConfig('no_more_spam/no_spam/enabled_email_name_link');
			$check_message_yn = mage::getStoreConfig('no_more_spam/no_spam/enabled_email_message_link');
			if(($check_name_yn == 1) && (isset($post['name'])) && (trim($post['name'])!='') ) {
				if($this->_checkForLink($post['name']) === TRUE) {$has_spam = TRUE;}
			}
			
			if(($check_message_yn == 1) && (isset($post['comment'])) && (trim($post['comment'])!='') ) {
				if($this->_checkForLink($post['comment']) === TRUE) {$has_spam = TRUE;}
			}
		} catch (Exception $e) {
		echo  $e->getMessage(); exit;
//             $translate->setTranslateInline(true);
//             Mage::getSingleton('customer/session')->addError(Mage::helper('contacts')->__('Unable to send this message. Please, try again later'));
//             $this->_enable_reviewredirect('*/*/');
		}
        return $has_spam;
    }

    public function postAction()
    {
        $post = $this->getRequest()->getPost();
        $isKeyHash = false;
        $enable_email = mage::getStoreConfig("no_more_spam/no_spam/enabled_email");
        if($enable_email == 1){
    		$field_1 = Mage::helper("nomorespam")->getNmsField1();
    		$empty_text = Mage::helper("nomorespam")->getNmsField2();
            if(isset($post[$field_1]) && isset($post[$empty_text])){
                $isKeyHash = $this->_checkHashKey($post[$field_1], $post[$empty_text]);
            }
        }
        if(!$enable_email) $isKeyHash = true; // if this section is off in nomorespam config then don't check our fields
        if ( $post && $isKeyHash) {
            $translate = Mage::getSingleton('core/translate');
            /* @var $translate Mage_Core_Model_Translate */
            $translate->setTranslateInline(false);
            try {
                $postObject = new Varien_Object();
                $postObject->setData($post);

                $error = false;

                if (!Zend_Validate::is(trim($post['name']) , 'NotEmpty')) {
                    $error = true;
                }

                if (!Zend_Validate::is(trim($post['comment']) , 'NotEmpty')) {
                    $error = true;
                }

                if (!Zend_Validate::is(trim($post['email']), 'EmailAddress')) {
                    $error = true;
                }

                if (isset($post['hideit']) && Zend_Validate::is(trim($post['hideit']), 'NotEmpty')) {
                    $error = true;
                }
				
		        if($this->_checkManualSpam()) {					
		        	$error = true;
		        }

                if ($error) {
                    throw new Exception();
                }
                $mailTemplate = Mage::getModel('core/email_template');
                /* @var $mailTemplate Mage_Core_Model_Email_Template */
                $mailTemplate->setDesignConfig(array('area' => 'frontend'))
                    ->setReplyTo($post['email'])
                    ->sendTransactional(
                        Mage::getStoreConfig(self::XML_PATH_EMAIL_TEMPLATE),
                        Mage::getStoreConfig(self::XML_PATH_EMAIL_SENDER),
                        Mage::getStoreConfig(self::XML_PATH_EMAIL_RECIPIENT),
                        null,
                        array('data' => $postObject)
                    );

                if (!$mailTemplate->getSentSuccess()) {
                    throw new Exception();
                }

                $translate->setTranslateInline(true);
                Mage::getSingleton('customer/session')->addSuccess(Mage::helper('contacts')->__('Your inquiry was submitted and will be responded to as soon as possible. Thank you for contacting us.'));
                $this->_redirect('*/*/');

                return;
            } catch (Exception $e) {
                $translate->setTranslateInline(true);
                Mage::getSingleton('customer/session')->addError(Mage::helper('contacts')->__('Unable to send message. Please, try again later'));
                $this->_redirect('*/*/');
                return;
            }

        } else {
			 $translate = Mage::getSingleton('core/translate');
            $translate->setTranslateInline(true);
            Mage::getSingleton('customer/session')->addError(Mage::helper('contacts')->__('Unable to submit your request. Please, try again later'));
            $this->_redirect('*/*/');
        }
    }
}

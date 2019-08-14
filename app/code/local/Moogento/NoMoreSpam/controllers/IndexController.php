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
* File        IndexController.php
* @category   Moogento
* @package    noMoreSpam
* @copyright  Copyright (c) 2014 Moogento <info@moogento.com> / All rights reserved.
* @license    http://www.moogento.com/License.html
*/ ?>
<?php
require_once 'Mage/Contacts/controllers/IndexController.php';
class Moogento_NoMoreSpam_IndexController extends Mage_Contacts_IndexController
{
    private function _getHashKey(){
        $salt1 = mage::getStoreConfig("no_more_spam/no_spam/generate_text");
        $salt2 = mage::getStoreConfig("no_more_spam/no_spam/random_text");
        $hash_result = hash("sha256", $salt2 . $salt1);
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

    public function postAction()
    {
        $post = $this->getRequest()->getPost();
        $isKeyHash = false;
		$random_text = Mage::helper("nomorespam")->createNameInput1();
		$empty_text = Mage::helper("nomorespam")->createNameInput2();
        if(isset($post[$random_text]) && isset($post[$empty_text])){
            $isKeyHash = $this->_checkHashKey($post[$random_text], $post[$empty_text]);
        }
        $enable_email = mage::getStoreConfig("no_more_spam/no_spam/enabled_email");
        if ( $post && $isKeyHash && $enable_email) {
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

                if (Zend_Validate::is(trim($post['hideit']), 'NotEmpty')) {
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
                Mage::getSingleton('customer/session')->addError(Mage::helper('contacts')->__('Unable to submit your request. Please, try again later'));
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

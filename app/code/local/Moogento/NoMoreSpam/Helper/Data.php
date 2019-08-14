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
    private function _crypto_rand_secure($min, $max) {
        $range = $max - $min;
        if ($range < 0) return $min; // not so random...
        $log = log($range, 2);
        $bytes = (int) ($log / 8) + 1; // length in bytes
        $bits = (int) $log + 1; // length in bits
        $filter = (int) (1 << $bits) - 1; // set all lower bits to 1
        do {
            $rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
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
    private function _getGenerateText(){  //salt1
        $generate_text = mage::getStoreConfig("no_more_spam/no_spam/generate_text");
        if($generate_text==''){
            $generate_text = $this->_getToken(6);
            Mage::getModel('core/config')->saveConfig("no_more_spam/no_spam/generate_text", $generate_text );
        }
        return $generate_text;
    }
    private function _getRandomText(){ //salt2
        $random_text = mage::getStoreConfig("no_more_spam/no_spam/random_text");
        if($random_text==''){
            $random_text = $this->_getToken(6);
            Mage::getModel('core/config')->saveConfig("no_more_spam/no_spam/random_text", $random_text );
        }
        return $random_text;
    }
    public function createId(){
        $prefix = "nms";
        $salt1 = $this->_getGenerateText();
        $id = $prefix . "_" . $salt1;
        return $id;
    }
	public function createNameInput1(){
		$prefix = "nms_rd";
        $salt1 = $this->_getGenerateText();
        $name = $prefix . "_" . $salt1;
        return $name;
	}
	public function createNameInput2(){
		$prefix = "nms_em";
        $salt2 = $this->_getRandomText();
        $name = $prefix . "_" . $salt2;
        return $name;
	}
    public function createvalue(){
        $salt1 = $this->_getGenerateText();
        $salt2 = $this->_getRandomText();
        $hash_result = hash("sha256", $salt2 . $salt1);
        return $hash_result;
    }
}

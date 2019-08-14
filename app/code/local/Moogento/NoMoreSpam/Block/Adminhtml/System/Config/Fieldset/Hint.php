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
* File        Hint.php
* @category   Moogento
* @package    noMoreSpam
* @copyright  Copyright (c) 2014 Moogento <info@moogento.com> / All rights reserved.
* @license    http://www.moogento.com/License.html
*/ ?>
<?php

class Moogento_NoMoreSpam_Block_Adminhtml_System_Config_Fieldset_Hint
    extends Mage_Adminhtml_Block_Abstract
    implements Varien_Data_Form_Element_Renderer_Interface {

    protected $_template = 'moogento/nomorespam/system/config/fieldset/hint.phtml';
	const ROUTE = 'no_more_spam';
	const PATH = 'Moogento_NoMoreSpam';


    /**
     * Render fieldset html
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element) {
        return $this->toHtml();
    }

	public function checkMooLicense($input) {
		$key_return = "aHR0cDovL2JpdC5seS80a2I3N3Y=";
		return $key_return;
	}
	
	public function getMooVersion($e) {
		return (string)Mage::getConfig()->getNode('modules/'.$e.'/version');
	}

    public function getMooo() {
		$e = strtolower(substr(self::PATH, strpos(self::PATH, '_')+1,1)); 
		$l = trim(Mage::getModel('core/config_data')->load(self::ROUTE.'/mood'.'etails/'.'lic'.'ense','path')->getValue());		
		$s = '';
		if(isset($_SERVER['HTTP_HOST']))$s = $_SERVER['HTTP_HOST'];
		return '.z'.strtr(base64_encode("$l~$e~$s"), '+/=', '-_,');
    }
	
    public function getInfoHtml()
    {				
            try {
                $zend_cache_backend = new Zend_Cache_Backend();
                $cache = Zend_Cache::factory('Core', 'File', array('lifetime' => 86400), array('cache_dir' => $zend_cache_backend->getTmpDir()));
            } catch (Exception $e) {
                return null;
            }

            $dataModelName = @current($this->getGroup()->data_model);			
            $zend_cache_key = strtolower('moo_' . self::PATH . '_' . str_replace('.','',$this->getMooVersion(self::PATH)));			
            $cache_content = $cache->load($zend_cache_key);
$cache_content = false;		
            if ($cache_content !== false && $cache_content !== '') {
                $return_html = $cache_content;
           } else {
//return $zend_cache_key.':'.$dataModelName;	
$dataModelName = 'nomorespam/adminhtml_system_config_backend_import_name';
                try {
                    $dataModel = Mage::getSingleton($dataModelName);
                    $dataModel->afterLoad();
                    // Check for news updates
					// http://www.moogento.com/media/info/
                    $zend_client = new Zend_Http_Client('ht' . 'tps://w' . 'ww.' . 'mo' . 'oge' . 'nto' . '.' . 'co' . 'm/med' . 'ia' .'/in' .'fo/te'.'xt.t'.'xt', array('timeout' => 8));
                    $zend_client->setParameterGet('x', $dataModel->getValue());
                    $response = $zend_client->request('GET');
                    $return_html = $response->getBody();			
                    $cache->save($return_html, $zend_cache_key);
                } catch (Exception $e) {
                    return null;
                }
            }
			if(strpos($return_html,'has been an error') !== false) {
                return null;
            }
			
        return $return_html;
    }
}
?>

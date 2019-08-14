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
* File        Hint.php
* @category   Moogento
* @package    noMoreSpam
* @copyright  Copyright (c) 2016 Moogento <info@moogento.com> / All rights reserved.
* @license    https://moogento.com/License.html
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
	
	public function getMooVersion($e) {
		return (string)Mage::getConfig()->getNode('modules/'.$e.'/version');
	}

    public function getMooo() {
		return '.zn';
    }
	
    public function getInfoHtml()
    {				
		$return_html = '<ul class="ext_info" style="width: 32em;">
				<li style="line-height:26px;min-width:29em;padding:8px 6px 10px 11px;"><em></em>Thanks for installing <strong>NoMoreSpam!</strong><br />Check <a href="https://moogento.com" style="border-bottom:1px solid;">Moogento.com</a> for other great time-saving extensions!</li>
			</ul>';
			
        return $return_html;
    }
}
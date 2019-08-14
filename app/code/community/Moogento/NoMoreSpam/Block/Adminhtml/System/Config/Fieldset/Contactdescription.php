<?php 
/** 
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
* File        Packdescription.php
* @category   Moogento
* @package    pickPack
* @copyright  Copyright (c) 2016 Moogento <info@moogento.com> / All rights reserved.
* @license    https://moogento.com/License.html
*/ 


class Moogento_NoMoreSpam_Block_Adminhtml_System_Config_Fieldset_Contactdescription
    extends Mage_Adminhtml_Block_Abstract
    implements Varien_Data_Form_Element_Renderer_Interface {    
		//     Mage_Adminhtml_Block_System_Config_Form_Fieldset
    /**
     * Render fieldset html
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render() {
    	$html = '<div class="moo_config_info"><em></em>Include in CMS-page contact form by adding this code to the CMS content: 
		<span class="comment_code">
		<br />
		{{block type="nomorespam/nomorespam" name="nomorespam_contact" template="moogento/nomorespam/nomorespam_contact.phtml"}}
		</span></div>';
        return $html;
    }
}




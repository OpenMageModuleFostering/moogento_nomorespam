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
* File        Nomorespam.php
* @category   Moogento
* @package    noMoreSpam
* @copyright  Copyright (c) 2014 Moogento <info@moogento.com> / All rights reserved.
* @license    http://www.moogento.com/License.html
*/ ?>
<?php
class Moogento_NoMoreSpam_Block_Nomorespam extends Mage_Core_Block_Template
{
	public function _prepareLayout() {
		return parent::_prepareLayout();
	}

	public function getNoMoreSpam() { 
		if (!$this->hasData('nomorespam')) {
			$this->setData('nomorespam', Mage::registry('nomorespam'));
		}
		return $this->getData('nomorespam');
	}
	
}
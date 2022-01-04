<?php
/**
 * @since 1.0.0 Class para o tamplante de retorno da Provu
 * 
 * @author Felipe M A B Huinka
 * @copyright Milabs - M!labs 2021
 */

 namespace Milabs\Provu\Block;

class ReturnProvu extends \Magento\Framework\View\Element\Template
 {
    
    
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ){
        parent::__construct($context,$data);
    }

    public function getLogoProvu()
    {
        return $this->_assetRepo->getUrl("Milabs_Provu::images/logoProvu.png");
    }

 }
<?php
namespace Milabs\Provu\Api;

interface PostManagementInterface {


	/**
	 * Post for Post api
	 * @return string
	 * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedExceptio
	 */

	public function getPost();
}
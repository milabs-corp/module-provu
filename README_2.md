# Mage2 Module Milabs Provu

    ``zafarie/module-provu``

 - [Main Functionalities](#markdown-header-main-functionalities)
 - [Installation](#markdown-header-installation)
 - [Configuration](#markdown-header-configuration)
 - [Specifications](#markdown-header-specifications)
 - [Attributes](#markdown-header-attributes)


## Main Functionalities
Milabs Provu

## Installation
\* = in production please use the `--keep-generated` option

### Type 1: Zip file

 - Unzip the zip file in `app/code/Milabs`
 - Enable the module by running `php bin/magento module:enable Milabs_Provu`
 - Apply database updates by running `php bin/magento setup:upgrade`\*
 - Flush the cache by running `php bin/magento cache:flush`

### Type 2: Composer

 - Make the module available in a composer repository for example:
    - private repository `repo.magento.com`
    - public repository `packagist.org`
    - public github repository as vcs
 - Add the composer repository to the configuration by running `composer config repositories.repo.magento.com composer https://repo.magento.com/`
 - Install the module composer by running `composer require zafarie/module-provu`
 - enable the module by running `php bin/magento module:enable Milabs_Provu`
 - apply database updates by running `php bin/magento setup:upgrade`\*
 - Flush the cache by running `php bin/magento cache:flush`


## Configuration

 - Provu - payment/provu/*


## Specifications

 - Cronjob
	- zafarie_provu_orders

 - Observer
	- order_cancel_after > Milabs\Provu\Observer\Backend\Order\CancelAfter

 - Payment Method
	- Provu


## Attributes

 - Sales - provu_transaction_id (provu_transaction_id)

 - Sales - provu_transaction_parcells (Parcells)


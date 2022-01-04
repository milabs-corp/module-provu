<?php
/**
 * Copyright © Milabs All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Milabs\Provu\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\View\Asset\Source;
use Magento\Framework\Pricing\PriceCurrencyInterface;



/**
 * Class ConfigProvider
 */
final class ConfigProvider implements ConfigProviderInterface
{
    const CODE = 'provu';

    /** @var PriceCurrencyInterface $priceCurrency */
    protected $priceCurrency;

    /**
     * @var Repository
     */
    protected $assetRepo;
    /**
     * @var \Magento\Framework\View\Asset\Source
     */
    protected $assetSource;
    /**
     * @var ResolverInterface
     */
    private $localeResolver;
    /**
     * @var Config
     */
    private $config;

    protected $helper;

    /**
     * @var \Magento\Checkout\Model\Type\Onepage
     */
    protected $onepage;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $state;

     /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    protected $_assetRepo;


    /**
     * Constructor
     *
     * @param Repository $assetRepo
     * @param ResolverInterface $localeResolver
     * @param Source $assetSource
     * @param RequestInterface $request
     */
    public function __construct(
        Repository $assetRepo,
        ResolverInterface $localeResolver,
        Source $assetSource,
        RequestInterface $request,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Checkout\Model\Session $_checkoutSession,
        \Magento\Checkout\Model\Type\Onepage $onepage,
        \Milabs\Provu\Helper\Data $helper,
        \Magento\Framework\App\State $state
    )
    {
        $this->assetRepo = $assetRepo;
        $this->localeResolver = $localeResolver;
        $this->assetSource = $assetSource;
        $this->request = $request;
        $this->priceCurrency = $priceCurrency;
        $this->_checkoutSession = $_checkoutSession;
        $this->onepage = $onepage;
        $this->helper = $helper;
        $this->state = $state;
    }

    /**
     * Retrieve assoc array of checkout configuration
     * @return array
     */
    public function getConfig()
    {
       
        return [
            'payment' => [
                self::CODE => [
                    'isActive' => $this->helper->isEnabled(),
                    'logoIconProvu'=> $this->assetRepo->getUrl("Milabs_Provu::images/logoIconProvu.png"),
                    'instructions' => $this->helper->getInstructions(),
                    'infoProvu' => $this->assetRepo->getUrl("Milabs_Provu::images/infoProvu.svg"),
                    'title' => 'Provu',
                ]
            ]
        ];
    }



    /**
     * Create a file asset that's subject of fallback system
     *
     * @param string $fileId
     * @param array $params
     *
     * @return \Magento\Framework\View\Asset\File
     */
    public function createAsset($fileId, array $params = [])
    {
        $params = array_merge(['_secure' => $this->request->isSecure()], $params);
        return $this->assetRepo->createAsset($fileId, $params);
    }


    /**
     * Function getFormatedPrice
     *
     * @param float $price
     *
     * @return string
     */
    public function getFormatedPrice($amount)
    {
        return $this->priceCurrency->format($amount, false);
    }
}

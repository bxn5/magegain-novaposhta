<?php

namespace Magegain\Novaposhta\Model\Carrier;

use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Rate\Result;
use Magegain\Novaposhta\Api\WarhouseRepositoryInterface;
use Magegain\Novaposhta\Api\CityRepositoryInterface;
use Magento\Framework\Locale\Resolver;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;

class Newposhta extends \Magento\Shipping\Model\Carrier\AbstractCarrier implements
    \Magento\Shipping\Model\Carrier\CarrierInterface
{

    /**
     * @var string
     */
    protected $_code = 'newposhta';

    /**
     * @var CityRepositoryInterface
     */
    private $cityRepository;

    /**
     * @var Resolver
     */
    private $resolver;

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var \Magento\Customer\Model\Address
     */
    private  $modelAdress;

    /**
     * Newposhta constructor.
     * @param \Magento\Customer\Model\Address $modelAdress
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory
     * @param \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory
     * @param WarhouseRepositoryInterface $warhouseRepository
     * @param Resolver $resolver
     * @param FilterBuilder $filterBuilder
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param CityRepositoryInterface $cityRepository
     * @param \Magento\Framework\HTTP\ZendClientFactory $httpClientFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Customer\Model\Address $modelAdress,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        WarhouseRepositoryInterface $warhouseRepository,
        Resolver $resolver,
        FilterBuilder $filterBuilder,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        CityRepositoryInterface $cityRepository,
        \Magento\Framework\HTTP\ZendClientFactory $httpClientFactory,
        array $data = []
    ) {
        $this->modelAdress =  $modelAdress;
        $this->customerSession = $customerSession;
        $this->_rateResultFactory = $rateResultFactory;
        $this->_rateMethodFactory = $rateMethodFactory;
        $this->warhouseRepository = $warhouseRepository;
        $this->resolver = $resolver;
        $this->filterBuilder = $filterBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->cityRepository = $cityRepository;
        $this->scopeConfig = $scopeConfig;
        $this->_httpClientFactory = $httpClientFactory;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    /**
     * @return array
     */
    public function getAllowedMethods()
    {
        return ['newposhta' => $this->getConfigData('name')];
    }

    /**
     * @param RateRequest $request
     * @return bool|Result
     */
    public function collectRates(RateRequest $request)
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }
        $senderCity = $this->scopeConfig->getValue('carriers/newposhta/citylist', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $weightUnit = $this->scopeConfig->getValue('carriers/newposhta/weightunit', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $city = $request->getDestCity();
        if ($city == null) {
            $billingID =  $this->customerSession->getCustomer()->getDefaultBilling();
            $address = $this->modelAdress->load($billingID);
            $data = $address->getData();
            if (isset($data['city'])) {
                $city = $data['city'];
            }
            else {
               return false;
            }
        }
        $loc = $this->resolver->getLocale();
        $cityModel = $this->getCityByName($city, $loc);
        $senderCityModel = $this->getCityByName($senderCity, 'UA_ua');
        if (!$cityModel) {
            return false;
        }
        $shippingWeight = $request->getPackageWeight();
        $subtotal = $request->getBaseSubtotalInclTax();
        $amount = $this->_calculatePrice($senderCityModel, $cityModel, $shippingWeight, $weightUnit, $subtotal, 'WarehouseWarehouse');

        $result = $this->_rateResultFactory->create();
            $title = __('Доставка до отделения');
            $carrierData = ['carrierTitle' => __('Новая почта'), 'methodCode' => 'newposhta', 'methodTitle' => $title];
            $method = $this->setShipMethod($carrierData, $amount);
            $result->append($method);

        $toHomeAmount = $this->_calculatePrice($senderCityModel, $cityModel, $shippingWeight, $weightUnit, $subtotal, 'WarehouseDoors');
        $carrierData = ['carrierTitle' => __('Новая почта'), 'methodCode' => 'newposhtahome', 'methodTitle' => __('Доставка на дом')];
        $method = $this->setShipMethod($carrierData, $toHomeAmount);
        $result->append($method);
        return $result;
    }

    private function getCityByName($name, $loc)
    {
        $fied_name = ($loc == 'ru_RU') ? 'city_name_ru' : 'city_name';
        $filters = $this->filterBuilder
                ->setConditionType('eq')
                ->setField($fied_name)
                ->setValue($name)
                ->create();
        $this->searchCriteriaBuilder->addFilters([$filters]);
        $city = $this->cityRepository->getList(
            $this->searchCriteriaBuilder->create()
        )->getItems();
        if (count($city) > 0) {
            reset($city);
            $first_key = key($city);
            return $city[$first_key];
        } else {
            return false;
        }
    }

    /**
     * @var $carrierData array of carrier information
     * @param $price
     * @return \Magento\Quote\Model\Quote\Address\RateResult\Method
     */
    private function setShipMethod(array $carrierData, $price)
    {
        $method = $this->_rateMethodFactory->create();
        $method->setCarrier('newposhta');
        if ($price == 0) {
            $err = __('Помилка розрахунку вартостi');
        } else {
            $err = '';
        }
        $method->setCarrierTitle($carrierData['carrierTitle']. ' '. $err);
        $method->setMethod($carrierData['methodCode']);
        $method->setMethodTitle($carrierData['methodTitle']);
        $method->setPrice($price);
        $method->setCost($price);
        return $method;
    }

    private function _calculatePrice($senderCityModel, $cityModel, $shippingWeight, $weightUnit, $subtotal, $serviceType)
    {
        $apiKey = $this->scopeConfig->getValue('carriers/newposhta/apikey', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $weight = ($weightUnit == 'kg') ? $shippingWeight : $shippingWeight / 1000;
        $client = $this->_httpClientFactory->create();
        $client->setUri('http://testapi.novaposhta.ua/v2.0/en/getDocumentPrice/json/');
        $request = ['modelName' => 'InternetDocument', 'calledMethod' => 'getDocumentPrice', 'apiKey' => $apiKey, 'methodProperties' => ['CitySender' => $senderCityModel->getRef(), 'CityRecipient' => $cityModel->getRef(), 'Weight' => $weight, 'ServiceType' => $serviceType, 'Cost' => $subtotal]];
        $client->setConfig(['maxredirects' => 0, 'timeout' => 30]);
        $client->setRawData(utf8_encode(json_encode($request)));
        $response = json_decode($client->request(\Zend_Http_Client::POST)->getBody());
        if (!is_object($response)) {
            return false;
        }
        if ($response->success === true) {
            return $response->data[0]->Cost;
        } else {
            return 0;
        }
    }
}

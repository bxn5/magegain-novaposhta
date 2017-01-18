<?php

/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magegain\Novaposhta\Controller\Adminhtml\City;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magegain\Novaposhta\Api\CityRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

class Sync extends \Magento\Backend\App\Action {

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;
    private $cityRepository;
    private $cityFactory;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var \Magento\Framework\HTTP\ZendClientFactory
     */
    protected $_httpClientFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context, 
        PageFactory $resultPageFactory, 
        CityRepositoryInterface $cityRepository, 
        \Magegain\Novaposhta\Model\CityFactory $cityFactory, 
        \Magento\Framework\HTTP\ZendClientFactory $httpClientFactory, 
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder, 
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->cityRepository = $cityRepository;
        $this->cityFactory = $cityFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->_httpClientFactory = $httpClientFactory;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Check the permission to run it
     *
     * @return bool
     */
    protected function _isAllowed() {
        return $this->_authorization->isAllowed('Magento_Cms::page');
    }

    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute() {

        $citiesApiJson = $this->_getCitiesFromServer();
        $citiesApi = json_decode($citiesApiJson);
        if (property_exists($citiesApi, 'success') && $citiesApi->success === true) {
            $this->_syncWithDb($citiesApi->data);
            $this->messageManager->addSuccess(
                    __('Успешно синхронизировано'));
            $this->_redirect('novaposhta/city/index');
        } else {
            $this->messageManager->addError(
                    __('Новая почта не отвечет или отвечает не правльно')
            );
            $this->messageManager->addError($citiesApi->message);
            $this->_redirect('novaposhta/city/index');
        }
    }

    /**
     * Get cities from api
     *
     * 
     * @return json
     */
    protected function _getCitiesFromServer() {
        $apiKey = $this->scopeConfig->getValue('carriers/newposhta/apikey', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $client = $this->_httpClientFactory->create();
        $client->setUri('http://testapi.novaposhta.ua/v2.0/json/Address/getCities');
        $request = ['modelName' => 'Address', 'calledMethod' => 'getCities', 'apiKey' => $apiKey];
        $client->setConfig(['maxredirects' => 0, 'timeout' => 30]);
        $client->setRawData(utf8_encode(json_encode($request)));
        return $client->request(\Zend_Http_Client::POST)->getBody();
    }

    protected function _syncWithDb($citiesApi) {
        $currentCitiesIds = $this->_getCitiesIdArray();
        foreach ($citiesApi as $key => $cityApi) {
            $cityApiId = $cityApi->CityID;
            if (isset($currentCitiesIds[$cityApiId])) {
                continue;
            } else {
                $this->_addNewCity($cityApi);
            }
        }
    }

    private function _getCitiesIdArray() {
        $citiesCollection = $this->_getCitiesCollection();
        $idsArray = [];
        foreach ($citiesCollection as $key => $city_model) {
            $idsArray[$city_model->getCityId()] = '';
        }
        return $idsArray;
    }

    protected function _getCitiesCollection() {
        return $this->cityRepository->getList(
                        $this->searchCriteriaBuilder->create()
                )->getItems();
    }

    private function _addNewCity($cityApi) {
        $modelCity = $this->cityFactory->create();
        $modelCity->setCityId($cityApi->CityID);
        $modelCity->setCityName($cityApi->Description);
        $modelCity->setCityNameRu($cityApi->DescriptionRu);
        $modelCity->setRef($cityApi->Ref);
        $this->cityRepository->save($modelCity);
    }

}

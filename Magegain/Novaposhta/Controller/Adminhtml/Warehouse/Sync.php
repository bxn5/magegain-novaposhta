<?php

namespace Magegain\Novaposhta\Controller\Adminhtml\Warehouse;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magegain\Novaposhta\Api\WarehouseRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

class Sync extends \Magento\Backend\App\Action
{

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var WarehouseRepositoryInterface
     */
    private $warehouseRepository;

    /**
     * @var \Magegain\Novaposhta\Api\CityRepositoryInterface
     *
     */
    private $cityRepository;

    /**
     * @var \Magegain\Novaposhta\Model\WarehouseFactory
     */
    private $warehouseFactory;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var \Magento\Framework\HTTP\ZendClientFactory
     */
    private $_httpClientFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Sync constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param WarehouseRepositoryInterface $warehouseRepository
     * @param \Magegain\Novaposhta\Model\WarehouseFactory $warehouseFactory
     * @param \Magento\Framework\HTTP\ZendClientFactory $httpClientFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magegain\Novaposhta\Api\CityRepositoryInterface $cityRepository
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        WarehouseRepositoryInterface $warehouseRepository,
        \Magegain\Novaposhta\Model\WarehouseFactory $warehouseFactory,
        \Magento\Framework\HTTP\ZendClientFactory $httpClientFactory,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magegain\Novaposhta\Api\CityRepositoryInterface $cityRepository,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\ResourceConnection $resourceConnection
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->warehouseRepository = $warehouseRepository;
        $this->warehouseFactory = $warehouseFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->_httpClientFactory = $httpClientFactory;
        $this->cityRepository = $cityRepository;
        $this->scopeConfig = $scopeConfig;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Check the permission to run it
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Cms::page');
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        set_time_limit(0);
        $warehouseApiJson = $this->_getWarehousesFromServer();
        $warehouseApi = json_decode($warehouseApiJson);
        if (property_exists($warehouseApi, 'success') && $warehouseApi->success === true) {
            $this->_syncWithDb($warehouseApi->data);
            $this->messageManager->addSuccess(
                __('Успешно синхронизировано')
            );
            $this->_redirect('novaposhta/warehouse/index');
        } else {
            $this->messageManager->addError(
                __('Новая почта не отвечет или отвечает не правильно')
            );
            $this->messageManager->addError($warehouseApi->message);
            $this->_redirect('novaposhta/warehouse/index');
            return;
        }
        $this->_redirect('novaposhta/warehouse/index');
    }

    protected function _getWarehousesFromServer()
    {
        $apiKey = $this->scopeConfig->getValue('carriers/newposhta/apikey', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $client = $this->_httpClientFactory->create();
        $client->setUri('http://testapi.novaposhta.ua/v2.0/json/AddressGeneral/getWarehouses');
        $request = ['modelName' => 'AddressGeneral', 'calledMethod' => 'getWarehouses', 'apiKey' => $apiKey];
        $client->setConfig(['maxredirects' => 0, 'timeout' => 0]);
        $client->setRawData(utf8_encode(json_encode($request)));
        return $client->request(\Zend_Http_Client::POST)->getBody();
    }

    private function _syncWithDb($warehouseDataApi)
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('novaposhta_warehouse');
        $tableCityName = $this->resourceConnection->getTableName('novaposhta_cities');

        $citiesRefArray = $this->_getCitiesRefArray($connection, $tableCityName);
        $currentWarehouses = $this->_getWarehouseRefArray($connection, $tableName);
        foreach ($warehouseDataApi as $key => $warehouseItemApi) {
            if (isset($currentWarehouses[$warehouseItemApi->Ref])) {
                continue;
            } else {
                $cityID = $citiesRefArray[$warehouseItemApi->CityRef] ?? false;
                if ($cityID === false) {
                    $this->messageManager->addError(__('Для одного из отделений не найден город, пожалуйста синхронизируйте сперва города '));
                    continue;
                }
                $this->_addWarehouse($warehouseItemApi, $cityID, $connection, $tableName);
            }
        }
    }

    private function _getCitiesRefArray($connection, $tableName)
    {
        $select = $connection->select()
        ->from(
            ['ce' => $tableName],
            ['Ref','id']
        );
        $res_arr =  $connection->fetchAll($select);
        $result = [];
        for ($i=0; $i < count($res_arr); $i++) {
               $result[$res_arr[$i]['Ref']] = $res_arr[$i]['id'];
        }
        return $result;
    }

 

    private function _addWarehouse($warehouseItemApi, $cityID, $connection, $tableName)
    {

        $sql = "INSERT INTO $tableName (city_id, warehouse_name, warehouse_name_ru, ref) Values (:CITYID, :WHNAME, :WHNAMERU, :REF)";
        $binds = array(
        'CITYID' => (int)$cityID,
        'WHNAME' => $warehouseItemApi->Description,
        'WHNAMERU' => $warehouseItemApi->DescriptionRu,
        'REF' => $warehouseItemApi->Ref,
        );
        $connection->query($sql, $binds);


        return true;
    }

    private function _getWarehouseRefArray($connection, $tableName)
    {
        $select = $connection->select()
        ->from(
            ['ce' => $tableName],
            ['Ref']
        );
        $res_arr =  $connection->fetchAll($select);
        $result = [];
        for ($i=0; $i < count($res_arr); $i++) {
            $result[$res_arr[$i]['Ref']] = '';
        }
        return $result;
    }
}

<?php

namespace Magegain\Novaposhta\Controller\Adminhtml\Warhouse;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magegain\Novaposhta\Api\WarhouseRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

class Sync extends \Magento\Backend\App\Action {

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;
    private $warhouseRepository;
    private $cityRepository;
    private $warhouseFactory;
    private $resourceConnection;

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
        WarhouseRepositoryInterface $warhouseRepository,
        \Magegain\Novaposhta\Model\WarhouseFactory $warhouseFactory, 
        \Magento\Framework\HTTP\ZendClientFactory $httpClientFactory,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder, 
        \Magegain\Novaposhta\Api\CityRepositoryInterface $cityRepository, 
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\ResourceConnection $resourceConnection
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->warhouseRepository = $warhouseRepository;
        $this->warhouseFactory = $warhouseFactory;
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
    protected function _isAllowed() {
        return $this->_authorization->isAllowed('Magento_Cms::page');
    }

    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute() {
set_time_limit(0);
        $warhouseApiJson = $this->_getWarhousesFromServer();
        $warhouseApi = json_decode($warhouseApiJson);
        if (property_exists($warhouseApi, 'success') && $warhouseApi->success === true) {
            $this->_syncWithDb($warhouseApi->data);
           $this->messageManager->addSuccess(
                    __('Успешно синхронизировано'));
            $this->_redirect('novaposhta/warhouse/index');
        } else {
            $this->messageManager->addError(
                    __('Новая почта не отвечет или отвечает не правильно')
            );
            $this->messageManager->addError($warhouseApi->message);
            $this->_redirect('novaposhta/warhouse/index');
            return;
        }
        $this->_redirect('novaposhta/warhouse/index');
    }

    protected function _getWarhousesFromServer() {
        $apiKey = $this->scopeConfig->getValue('carriers/newposhta/apikey', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $client = $this->_httpClientFactory->create();
        $client->setUri('http://testapi.novaposhta.ua/v2.0/json/AddressGeneral/getWarehouses');
        $request = ['modelName' => 'AddressGeneral', 'calledMethod' => 'getWarehouses', 'apiKey' => $apiKey];
        $client->setConfig(['maxredirects' => 0, 'timeout' => 0]);
        $client->setRawData(utf8_encode(json_encode($request)));
        return $client->request(\Zend_Http_Client::POST)->getBody();
    }

    protected function _syncWithDb($warhouseDataApi) {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('novaposhta_warhouse'); //gives table name with prefix
         $tableCityName = $this->resourceConnection->getTableName('novaposhta_cities');
        $citiesRefArray = $this->_getCitiesRefArray($connection, $tableCityName);
        $currentWarhouses = $this->_getWarhouseRefArray($connection, $tableName);
        foreach ($warhouseDataApi as $key => $warhouseItemApi) {
            if (isset($currentWarhouses[$warhouseItemApi->Ref])) {
                continue;
            } else {
                $cityID = $citiesRefArray[$warhouseItemApi->CityRef] ?? false;
                if ($cityID === false) {
                    $this->messageManager->addError(__('Для одного из отделений не найден город, пожалуйста синхронизируйте сперва города '));
                    continue;
                }
                $this->_addWarhouse($warhouseItemApi, $cityID, $connection, $tableName);
            }
        }
    }

    private function _getCitiesRefArray($connection, $tableName) {

 $sql = "Select * FROM " . $tableName;
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

 

    private function _addWarhouse($warhouseItemApi, $cityID, $connection, $tableName) {
       /*$sql = "Insert Into " . $tableName . " (city_id, warhouse_name, warhouse_name_ru, ref) Values (".$cityID.", '".$warhouseItemApi->Description."', '".$warhouseItemApi->DescriptionRu."', '".$warhouseItemApi->Ref."')";
        $res = $connection->query($sql);*/

        $sql = "INSERT INTO $tableName (city_id, warhouse_name, warhouse_name_ru, ref) Values (:CITYID, :WHNAME, :WHNAMERU, :REF)";
    $binds = array(
        'CITYID' => (int)$cityID,
        'WHNAME' => $warhouseItemApi->Description,
        'WHNAMERU' => $warhouseItemApi->DescriptionRu,
        'REF' => $warhouseItemApi->Ref,
    );
    $connection->query($sql, $binds);


        return true;
    }

    private function _getWarhouseRefArray($connection, $tableName) {
        $sql = "Select * FROM " . $tableName;
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
       
        /*$warhouseCollection = $this->_getWarhouseCollection();
        $refArray = [];
        foreach ($warhouseCollection as $warhouse_model) {
            $refArray[$warhouse_model->getRef()] = $warhouse_model->getId();
        }
        return $refArray;*/
    }

}

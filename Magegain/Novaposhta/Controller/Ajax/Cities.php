<?php

/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magegain\Novaposhta\Controller\Ajax;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magegain\Novaposhta\Api\CityRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Locale\Resolver;
use Magento\Framework\Api\FilterBuilder;

class Cities extends \Magento\Framework\App\Action\Action
{

    /**
     * @var PageFactory
     */
    private $cityRepository;
    private $resultJsonFactory;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;
    private $resolver;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context, 
        CityRepositoryInterface $cityRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder, 
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory, 
        Resolver $resolver,
        FilterBuilder $filterBuilder
    )
    {
        parent::__construct($context);
        $this->cityRepository = $cityRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resolver = $resolver;
        $this->filterBuilder = $filterBuilder;
    }

    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {


        $cities = $this->_getCitiesCollection();
        $to_json = [];
        $loc = $this->resolver->getLocale();
        foreach ($cities as $key => $city) {

            $to_json[] = ($loc == 'ru_RU') ? $city->getCityNameRu() : $city->getCityName();
        }
        return $this->resultJsonFactory->create()->setData(json_encode($to_json));
    }

    protected function _getCitiesCollection()
    {
        return $this->cityRepository->getList(
            $this->searchCriteriaBuilder->create()
        )->getItems();
    }

}

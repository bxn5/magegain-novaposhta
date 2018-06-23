<?php

namespace Magegain\Novaposhta\Cron;

use Magegain\Novaposhta\Controller\Adminhtml\Warehouse\Sync as WarehouseSync;
use Magegain\Novaposhta\Controller\Adminhtml\City\Sync as CitySync;

class UpdateWarehouse
{
    /**
     * @var WarehouseSync
     */
    private $warehousesUpdater;
    /**
     * @var CitySync
     */
    private $citiesUpdater;

    /**
     * UpdateWarehouse constructor.
     * @param WarehouseSync $warehouseSync
     * @param CitySync $citySync
     */
    public function __construct(
        WarehouseSync $warehouseSync,
        CitySync $citySync
    ) {
        $this->citiesUpdater = $citySync;
        $this->warehousesUpdater = $warehouseSync;
    }

    /**
     * Run sync by schedule
     * @return void
     */
    public function execute() {
        $this->citiesUpdater->execute();
        $this->warehousesUpdater->execute();
    }
}

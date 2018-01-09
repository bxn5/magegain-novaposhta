<?php

namespace Magegain\Novaposhta\Cron;

use Magegain\Novaposhta\Controller\Adminhtml\Warhouse\Sync as WarhouseSync;
use Magegain\Novaposhta\Controller\Adminhtml\City\Sync as CitySync;

class UpdateWarhouse
{
    /**
     * @var WarhouseSync
     */
    private $warhousesUpdater;
    /**
     * @var CitySync
     */
    private $citiesUpdater;

    /**
     * UpdateWarhouse constructor.
     * @param WarhouseSync $warhouseSync
     * @param CitySync $citySync
     */
    public function __construct(
        WarhouseSync $warhouseSync,
        CitySync $citySync
    ) {
        $this->citiesUpdater = $citySync;
        $this->warhousesUpdater = $warhouseSync;
    }

    /**
     * Run sync by schedule
     * @return void
     */
    public function execute() {
        $this->citiesUpdater->execute();
        $this->warhousesUpdater->execute();
    }
}

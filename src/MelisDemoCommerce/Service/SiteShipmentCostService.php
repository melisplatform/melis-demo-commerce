<?php

/**
 * Melis Technology (http://www.melistechnology.com)
*
* @copyright Copyright (c) 2016 Melis Technology (http://www.melistechnology.com)
*
*/

namespace MelisDemoCommerce\Service;

use MelisCore\Service\MelisCoreGeneralService;

/**
 * SiteShipmentCost Services handle the Cost of the Shiiping
 */
class SiteShipmentCostService extends MelisCoreGeneralService
{
    public function computeShipmentCost($shipment)
    {
        // Shipping Total Amount
        $total = 0;
        // Shipping errors
        $errors = array();
        
        // Static Value for Shipping Cost, for testing
        $total = 100;
        
        //  Results initialization
        $shipment['costs']['shipment']['total'] = $total;
        if (!empty($errors))
        {
            $shipment['costs']['shipment']['errors'] = $errors;
        }
        
        return $shipment;
    }
}
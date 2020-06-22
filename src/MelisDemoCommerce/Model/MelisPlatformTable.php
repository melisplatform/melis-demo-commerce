<?php 

/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2015 Melis Technology (http://www.melistechnology.com)
 *
 */

namespace MelisDemoCommerce\Model;

use Laminas\Db\TableGateway\TableGateway;
use MelisEngine\Model\Tables\MelisGenericTable;

class MelisPlatformTable extends MelisGenericTable
{
	/**
     * Model table
     */
    const TABLE = 'melis_core_platform';

    /**
     * Table primary key
     */
    const PRIMARY_KEY = 'plf_id';

    public function __construct()
    {
        $this->idField = self::PRIMARY_KEY;
	}
}

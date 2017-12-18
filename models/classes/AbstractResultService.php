<?php
/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\taoResultServer\models\classes;

use oat\oatbox\service\ConfigurableService;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use \taoResultServer_models_classes_WritableResultStorage as WritableResultStorage;

abstract class AbstractResultService extends ConfigurableService implements ResultServerService
{

    /**
     * Starts or resume a taoResultServerStateFull session for results submission
     *
     * @param $compiledDelivery
     * @param $executionIdentifier
     * @param array $options additional result server options
     * @throws \common_Exception
     * @throws
     */
    public function initResultServer($compiledDelivery, $executionIdentifier, $options = [])
    {
        $storage = $this->getResultStorage($compiledDelivery);
        //$storage->spawnResult($executionIdentifier);

        //link test taker identifier with results
        $storage->storeRelatedTestTaker($executionIdentifier, \common_session_SessionManager::getSession()->getUserUri());

        //link delivery identifier with results
        $storage->storeRelatedDelivery($executionIdentifier, $compiledDelivery->getUri());
    }

    /**
     * @param string $serviceId
     * @return WritableResultStorage
     * @throws \common_exception_Error
     */
    public function instantiateResultStorage($serviceId)
    {
        $storage = null;
        if (class_exists($serviceId)) { //some old serialized session can has class name instead of service id
            $storage = new $serviceId();
        } elseif($this->getServiceManager()->has($serviceId)) {
            $storage = $this->getServiceManager()->get($serviceId);
        }

        if ($storage instanceof ServiceLocatorAwareInterface) {
            $storage->setServiceLocator($this->getServiceLocator());
        }

        if ($storage === null || !$storage instanceof WritableResultStorage) {
            throw new \common_exception_Error('Configured result storage is not writable.');
        }

        return $storage;
    }


    abstract public function getResultStorage($deliveryId);

}
<?php
/**
 * Passbolt ~ Open source password manager for teams
 * Copyright (c) Passbolt SARL (https://www.passbolt.com)
 *
 * Licensed under GNU Affero General Public License version 3 of the or any later version.
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Passbolt SARL (https://www.passbolt.com)
 * @license       https://opensource.org/licenses/AGPL-3.0 AGPL License
 * @link          https://www.passbolt.com Passbolt(tm)
 * @since         2.5.0
 */
namespace Passbolt\WebInstaller\Test\Lib;

use App\Test\Lib\AppIntegrationTestCase;
use Cake\Core\Configure\Engine\PhpConfig;
use Cake\Datasource\ConnectionManager;
use Cake\Http\Exception\InternalErrorException;

class WebInstallerIntegrationTestCase extends AppIntegrationTestCase
{
    use ConfigurationTrait;
    use DatabaseTrait;

    protected $_recover;

    public function setUp()
    {
        parent::setUp();
        $this->_recover = false;
    }

    public function tearDown()
    {
        parent::tearDown();
        if ($this->_recover) {
            ConnectionManager::setConfig('test', self::getTestDatasourceFromConfig());
        }
    }

    public function mockPassboltIsNotconfigured()
    {
        $this->_recover = true;
        ConnectionManager::drop('test');
    }

    public function getTestDatasourceFromConfig() {
        $engine = new PhpConfig();
        try {
            $appValues = $engine->read('app');
        } catch(\Exception $exception) {
            throw new InternalErrorException('config/app.php is missing an needed for this test.');
        }
        try {
            $passboltValues = $engine->read('passbolt');
        } catch(\Exception $exception) {
        }

        if (isset($passboltValues['Datasources']['test']) && $passboltValues['Datasources']['test']) {
            $config = array_merge($appValues['Datasources']['test'], $passboltValues['Datasources']['test']);
        } else {
            if (!isset($passboltValues['Datasources']['test']) && !isset($passboltValues['Datasources']['test'])) {
                throw new InternalErrorException('A test connection is missing in Datasources config.');
            }
            if(!isset($passboltValues['Datasources']['test'])) {
                $config = $passboltValues['Datasources']['test'];
            } else {
                $config = $appValues['Datasources']['test'];
            }
        }
        return $config;
    }

    public function initWebInstallerSession(array $options = [])
    {
        $session = ['initialized' => true] + $options;
        $this->session(['webinstaller' => $session]);
    }
}
<?php

/**
 * @author Matthew McNaney <mcnaneym@appstate.edu>
 * @license https://opensource.org/licenses/MIT
 */

namespace Dashboard\Setup\Factory;

use Canopy3\Template;
use Canopy3\HTTP\Request;
use Doctrine\DBAL\DriverManager;

class SetupFactory
{

    public static function createDBFile($values)
    {
        $username = $password = $dbname = $host = $port = $driver = null;
        extract($values);
        $dbContent[] = '<?php';
        $dbContent[] = '$connectionParams = [';
        $dbContent[] = "'user' => '$username',";
        $dbContent[] = "'password' => '$password',";
        $dbContent[] = "'dbname' => '$dbname',";
        if (empty($host)) {
            $host = 'localhost';
        }
        $dbContent[] = "'host' => '$host',";
        if (!empty($port)) {
            $dbContent[] = "'port' => '$port',";
        }
        $dbContent[] = "'driver' => '$driver',";
        $dbContent[] = "];";

        $fileContent = implode("\n", $dbContent);
        $filename = C3_DIR . 'config/db.php';
        return file_put_contents($filename, $fileContent);
    }

    /**
     * Creates the resourcesUrl.php file in the config/ directory.
     *
     * @param \Canopy3\HTTP\Request $request
     * @return bool
     */
    public static function createResourceUrl(Request $request): bool
    {
        $urlConfigFilePath = C3_DIR . 'config/resourcesUrl.php';
        $values['resourcesUrl'] = $request->POST->resourcesUrl;
        $template = new \Canopy3\Template(Template::dashboardDirectory('canopy3-dashboard-setup'));
        $content = "<?php\n" . $template->render('ResourcesUrl.txt', $values);
        $result = file_put_contents($urlConfigFilePath, $content);
        return (bool) $result;
    }

    public static function defaultResourceUrls()
    {
        $url = preg_replace('@public/$@', '',
            \Canopy3\HTTP\Server::getCurrentUri());

        define('C3_RESOURCES_URL', $url . 'resources/');
        define('C3_DASHBOARDS_URL', C3_RESOURCES_URL . 'dashboards/');
        define('C3_PLUGINS_URL', C3_RESOURCES_URL . 'plugins/');
        define('C3_THEMES_URL', C3_RESOURCES_URL . 'themes/');
    }

    public static function testDB(Request $request)
    {
        $connectionValues = [
            'user' => $request->GET->username,
            'password' => $request->GET->password,
            'dbname' => $request->GET->dbname,
            'host' => $request->GET->host ?? 'localhost',
            'port' => $request->GET->port,
            'driver' => $request->GET->driver
        ];
        if ($connectionValues['user'] == null) {
            $result['success'] = false;
            $result['error']['userNameEmpty'] = true;
        }
        if ($connectionValues['dbname'] == null) {
            $result['success'] = false;
            $result['error']['databaseNameEmpty'] = true;
        }

        try {
            $conn = \Doctrine\DBAL\DriverManager::getConnection($connectionValues);
            $conn->connect();
            $result = ['success' => true];
        } catch (\Exception $e) {
            $result['success'] = false;
            $result['error']['connection'] = $e->getMessage();
        }

        return $result;
    }

}

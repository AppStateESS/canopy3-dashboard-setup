<?php

declare(strict_types=1);

/**
 * @author Matthew McNaney <mcnaneym@appstate.edu>
 * @license https://opensource.org/licenses/MIT
 */

namespace Dashboard\Setup\Controller;

use Canopy3\Template;
use Canopy3\HTTP\Request;
use Canopy3\HTTP\Response;
use Dashboard\Setup\Factory\SetupFactory;
use Dashboard\Setup\View\SetupView;
use Canopy3\Exception\UnknownControllerCommand;

class Setup extends \Canopy3\Controller
{

    const setupFilePath = C3_DIR . 'config/setup.php';

    private SetupView $view;
    private bool $databaseConfigExists = false;
    private bool $resourcesConfigExists = false;
    private bool $setupFileExists = false;

    public function __construct()
    {
        parent::__construct();
        $this->setupFileExists = is_file(C3_DIR . 'config/setup.php');
        $this->resourcesConfigExists = is_file(C3_DIR . 'config/resourcesUrl.php');
        $this->databaseConfigExists = is_file(C3_DIR . 'config/db.php');
        if (!$this->resourceConfigExists) {
            SetupFactory::defaultResourceUrls();
        }
        $this->view = new SetupView;
    }

    public function get(string $command, bool $isAjax)
    {
        if ($isAjax) {
            switch ($command) {
                case 'dbTest':
                    return Response::json(SetupFactory::testDB($this->request));
            }
        } else {
            switch ($command) {
                default:
                    return Response::themed($this->displayStage());
            }
        }
        throw new UnknownControllerCommand('GET', $command);
    }

    public function post(string $command, bool $isAjax)
    {
        switch ($command) {
            case 'createResourceUrl':
                return $this->createResourceUrl();

            case 'createDB':
                return $this->createDBFile($this->request);
        }
        throw new UnknownControllerCommand('POST', $command);
    }

    private function createDBFile(Request $request)
    {
        $values = $request->POST->getValues();
        if (SetupFactory::createDBFile($values)) {
            return Response::redirect('./d/Setup/Setup/view');
        } else {
            return Response::redirect('./d/Setup/Setup/databaseFailed');
        }
    }

    private function createResourceUrl()
    {
        $result = SetupFactory::createResourceUrl($this->request);
        if ($result) {
            return Response::redirect('./d/Setup/Setup/view');
        } else {
            return Response::themedError($this->view->resourceFileError());
        }
    }

    private function displayStage()
    {
        switch (1) {
            case (!$this->setupAllowed()):
                return $this->view->setupFile(self::setupFilePath);

            case (!$this->resourcesConfigExists):
                return $this->view->createResourcesConfig();

            case (!$this->databaseConfigExists):
                return $this->view->createDatabaseConfig();

            case (!$this->tableCreated):
                return $this->view->createTables();
        }
        throw new \Exception('Setup stage could not continue');
    }

    private function setupAllowed()
    {
        $setupAllowed = false;
        if ($this->setupFileExists) {
            include self::setupFilePath;
            return $setupAllowed;
        } else {
            return false;
        }
    }

}

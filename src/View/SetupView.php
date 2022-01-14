<?php

/**
 * @author Matthew McNaney <mcnaneym@appstate.edu>
 * @license https://opensource.org/licenses/MIT
 */

namespace Dashboard\Setup\View;

use Canopy3\Template;
use Canopy3\HTTP\Header;

class SetupView
{

    const templateDir = C3_DASHBOARDS_DIR . 'canopy3-dashboard-setup/templates/';
    const javascriptUrl = C3_DASHBOARDS_URL . 'canopy3-dashboard-setup/javascript/';

    private Template $template;

    public function __construct()
    {
        $this->template = new Template(self::templateDir);
        Header::singleton()->setSiteTitle('Administration Setup');
    }

    public function createDatabaseConfig()
    {
        $header = Header::singleton()->addScript(self::javascriptUrl . 'databaseConfig.js',
            ['defer' => true]);
        $configDirectory = C3_DIR . 'config/';
        $values = [];
        $values['configDirectory'] = $configDirectory;
        $values['configWritable'] = is_writable($configDirectory);
        return $this->wrapper('Create Database Configuration File',
                $this->template->render('DatabaseConfig', $values));
    }

    public function createResourcesConfig()
    {
        $header = Header::singleton();
        $header->setPageTitle('Create Resources Config');
        $header->addScript(self::javascriptUrl . 'updateResource.js',
            ['defer' => true]);
        $header->addScriptValue('resourcesUrl', C3_SYSTEMS_URL);
        $values['resourcesUrl'] = C3_SYSTEMS_URL;
        $values['c3Dir'] = C3_DIR;
        $values['configWritable'] = is_writable(C3_DIR . 'config/');
        return $this->wrapper('Create System File',
                $this->template->render('CreateSystemConfig', $values));
    }

    public function createTables()
    {
        $header = Header::singleton();
        $header->setPageTitle('Create Required Tables');

        return $this->wrapper('Create tables', $this->template->render('CreateTables'));
    }

    public function resourceFileError()
    {
        return $this->wrapper('Resource config file failed',
                $this->template->render('ResourceFileError'));
    }

    public function setupFile($setupFilePath)
    {
        $values['configFile'] = $setupFilePath;
        $phpCode = <<<EOF
<&#63;php
\$setupAllowed = true;
EOF;
        $values['fileCode'] = $this->template->render('codeArea',
            ['code' => $phpCode]);

        $path = $setupFilePath;
        $consoleCode = <<<EOF
echo '<&#63;php \$setupAllowed = true;' > $path
EOF;
        $values['consoleCode'] = $this->template->render('codeArea',
            ['code' => $consoleCode]);
        return $this->wrapper('Welcome to Canopy 3!',
                $this->template->render('setupFileWarning', $values));
    }

    private function wrapper($title, $content)
    {
        $values['title'] = $title;
        $values['content'] = $content;
        return $this->template->render('Wrapper', $values);
    }

}

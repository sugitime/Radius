<?php namespace pineapple;
putenv('LD_LIBRARY_PATH='.getenv('LD_LIBRARY_PATH').':/sd/lib:/sd/usr/lib');
putenv('PATH='.getenv('PATH').':/sd/usr/bin:/sd/usr/sbin');

class Radius extends Module
{
    protected $logging = true;
    private $configFile = "/usr/local/etc/hostapd-wpe/hostapd-wpe-bgn.conf";
    public function route()
    {
        switch ($this->request->action) {
            case 'refreshInfo':
                $this->refreshInfo();
                break;
            case 'handleDependancies':
                $this->handleDependancies();
                break;
            case 'refreshStatus':
                $this->refreshStatus();
                break;
            case 'toggleradius':
                $this->toggleradius();
                break;
            case 'radiusStatus':
                $this->radiusStatus();
                break;
            case 'configFiles':
                $this->configFiles();
                break;
            case 'getConfigFileContent':
                $this->getConfigFileContent();
                break;
            case 'saveConfigFileContent':
                $this->saveConfigFileContent();
                break;
            case 'refreshOutput':
                $this->refreshOutput();
                break;
        }
    }

    protected function refreshInfo()
    {
        $moduleInfo = @json_decode(file_get_contents("/pineapple/modules/Radius/module.info"));
        $this->response = array('title' => $moduleInfo->title, 'version' => $moduleInfo->version);
    }

    protected function checkDependency($dependencyName)
    {
        return ((exec("which {$dependencyName}") == '' ? false : true) && ($this->uciGet("radius.module.installed")));
    }

    protected function getInterface($configFileName)
    {
        $search = "interface=";
        $file = file($configFileName);
        foreach($file as $line){
            $line = trim($line);
            if(strpos($line, $search) === 0) {
                $interface = $line;
                break;
            }
        }
        $i = explode("=", $interface);
        $j = explode("mon", $i[1]);
        $this->log($j);
        return $j[0];
    }

    protected function getLogName($configFileName)
    {
        $a = basename ($configFileName);
        $this->log($a);
        $i = explode('-', $a);
        $j = $i[1]."_".$i[2];
        $l = explode(".conf", $j);
        $s = $l[0].".log";
        $this->log($s);
        return $s;
    }

    protected function log($entry)
    {
        $fh = fopen('/tmp/radius.log', 'a+');
        fwrite($fh, date("[Y/m/d h:i:sa] ") . $entry . PHP_EOL);
        fclose($fh);
    }

    private function handleDependancies()  // This is the function that will be executed when you send the request "getContents".
    {
        if(!$this->checkDependency("hostapd-wpe"))
        {
            $this->execBackground("/pineapple/modules/Radius/scripts/dependencies.sh install ");
            $this->response = array('success' => true);
        }
    }

    private function handleDependenciesStatus()
    {
        if (!$this-checkRunning('hostapd-wpe'))
        {
            $this->response = array('success' => true);
        }
        else
        {
            $this->response = array('success' => false);
        }
    }

    private function refreshStatus()
    {
        if (!$this->checkDependency("hostapd-wpe"))
        {
            $installed = false;
            $install = "Not installed";
            $installLabel = "danger";
            $processing = false;

            $status = "Start";
            $statusLabel = "success";
        }
        else
        {
            $installed = true;
            $install = "Installed";
            $installLabel = "success";
            $processing = false;

            if ($this->checkRunning("hostapd-wpe"))
            {
                $status = "Stop";
                $statusLabel = "danger";
            }
            else
            {
                $status = "Start";
                $statusLabel = "success";
            }
        }

        $device = $this->getDevice();
        $sdAvailable = $this->isSDAvailable();

        $this->response = array("device" => $device, "sdAvailable" => $sdAvailable, "status" => $status, "statusLabel" => $statusLabel, "installed" => $installed, "install" => $install, "installLabel" => $installLabel, "processing" => $processing);
    }

    private function toggleradius()
    {
        $this->configFile = $this->uciGet('radius.module.configfile');
        $interface = $this->getInterface($this->configFile);
        $this->uciSet('radius.module.interface', $interface);
        if(!$this->checkRunning("hostapd-wpe"))
        {
            exec('/pineapple/modules/Radius/scripts/dependencies.sh start');
        }
        else
        {
            exec('/pineapple/modules/Radius/scripts/dependencies.sh stop');
        }
    }

    private function radiusStatus()
    {
        if (!$this->checkRunning("hostapd-wpe"))
        {
            $this->response = array('success' => true);
        }
        else
        {
            $this->response = array('success' => false);
        }
    }

    private function configFiles()
    {
        $files = array();
        foreach (glob("/usr/local/etc/hostapd-wpe/*.conf") as $file) {
            $files[]['name'] = $file;
        }
        $this->response = array('list' => json_encode($files));
    }

    private function getConfigFileContent()
    {
        $configFile = $this->request->data;
        $this->configFile = $configFile;
        $this->uciSet('radius.module.configfile', $configFile);
        $readHandler = fopen($configFile, "r");
        $content = fread($readHandler, filesize($configFile));
        fclose($readHandler);
        $this->response = array('confFileContent' => $content);

    }

    private function saveConfigFileContent() {

        $configFileContent = json_decode($this->request->data);
        exec('rm ' . $configFileContent[0]);
        $writeHandler = fopen($configFileContent[0], 'w+');
        fwrite($writeHandler, $configFileContent[1]);
        fclose($writeHandler);
    }

    private function refreshOutput() {
        if ($this->checkDependency("hostapd-wpe"))
        {
            $log = '/tmp/'.$this->getLogName($this->configFile);
            if ($this->checkRunning("hostapd-wpe") && file_exists($log))
            {
                $output = file_get_contents($log);
                if(!empty($output))
                    $this->response = $output;
                else
                    $this->response = "Empty log...";
            }
            else
            {
                $this->response = "hostapd-wpe is not running...";
            }
        }
        else
        {
            $this->response = "hostapd-wpe is not installed...";
        }
    }
}





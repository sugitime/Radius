<?php namespace pineapple;
putenv('LD_LIBRARY_PATH='.getenv('LD_LIBRARY_PATH').':/sd/lib:/sd/usr/lib');
putenv('PATH='.getenv('PATH').':/sd/usr/bin:/sd/usr/sbin');

class RadiusPineapple extends Module
{
    public function route()
    {
        switch ($this->request->action) {
            case 'refreshInfo':
                $this->refreshInfo();
                break;
            case 'handleDependancies':
                $this->handleDependancies();
                break;

        }
    }

    protected function refreshInfo()
    {
        $moduleInfo = @json_decode(file_get_contents("/pineapple/modules/RadiusPineapple/module.info"));
        $this->response = array('title' => $moduleInfo->title, 'version' => $moduleInfo->version);
    }

    protected function checkDependency($dependencyName)
    {
        return ($this->uciGet("radius.module.installed"));
    }

    private function handleDependancies()  // This is the function that will be executed when you send the request "getContents".
    {
        if(!$this->checkDependency("radius"))
        {
            $this->execBackground("/pineapple/modules/RadiusPineapple/scripts/dependencies.sh install ".$this->request->destination);
            $this->response = array('success' => true);
        }
    }
}





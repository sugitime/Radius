<?php namespace pineapple;


/* The class name must be the name of your module, without spaces. */
/* It must also extend the "Module" class. This gives your module access to API functions */
class RadiusPineapple extends Module
{
    public function route()
    {
        switch ($this->request->action) {
            case 'getContents':
                $this->getContents();
                break;
            case 'handleDependancies':
                $this->handleDependancies()
                break;

        }
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
        else
        {
            $this->execBackground("/pineapple/modules/RadiusPineapple/scripts/dependencies.sh remove");
            $this->response = array('success' => true);
        }
    }

    private function getContents()  // This is the function that will be executed when you send the request "getContents".
    {
        $this->response = array("success" => true,    // define $this->response. We can use an array to set multiple responses.
                                "greeting" => "Hey there!",
                                "content" => "This is the HTML template for your new module! The example shows you the basics of using HTML, AngularJS and PHP to seamlessly pass information to and from Javascript and PHP and output it to HTML.");
    }
}





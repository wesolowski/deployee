<?php

namespace Phizzl\Deployee\Plugins\Deploy;


use Phizzl\Deployee\Container;
use Phizzl\Deployee\Plugins\AbstractPlugin;
use Phizzl\Deployee\Plugins\Deploy\Subscriber\ApplicationSubscriber;

class DeployPlugin extends AbstractPlugin
{
    const PLUGIN_ID = "deploy";

    /**
     * @return string
     */
    public function getPluginId()
    {
        return self::PLUGIN_ID;
    }

    /**
     * @param Container $container
     */
    public function initialize(Container $container)
    {
        $this->validateConfiguration();

        $container->classLoader()->add("\\", $this->config['path']);
        $container->events()->addSubscriber(new ApplicationSubscriber($this));
    }

    /**
     * @throws \RuntimeException
     */
    private function validateConfiguration()
    {
        if(!isset($this->config['path'])
            || !($path = $this->config['path'])){
            throw new \RuntimeException("You must specify the path to store and read the deployment definitions from");
        }

        if(substr($path, 0, 1) !== '/'
            && substr($path, 1, 1) !== ':'){
            $path = getcwd() . DIRECTORY_SEPARATOR . $path;
        }

        if(!$realpath = realpath($path)){
            if(!mkdir($path)) {
                throw new \RuntimeException("Cannot create given path \"$path\"");
            }
            $realpath = realpath($path);
        }

        $this->config['path'] = $realpath;
    }
}
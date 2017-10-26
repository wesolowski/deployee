<?php

namespace Deployee\Kernel;

use Deployee\Kernel\Exceptions\ClassNotFoundException;
use Deployee\Kernel\Modules\FacadeInterface;
use Deployee\Kernel\Modules\FactoryInterface;
use Deployee\Kernel\Modules\Module;
use Deployee\Kernel\Modules\ModuleCollection;
use Deployee\Kernel\Modules\ModuleInterface;

class Locator
{
    /**
     * @var DependencyProviderContainerInterface
     */
    private $dependencyProviderContainer;

    /**
     * @var ModuleCollection
     */
    private $modules;

    /**
     * @var array
     */
    private $namespaces;

    /**
     * Locator constructor.
     * @param DependencyProviderContainerInterface $dependencyProvider
     * @param array $namespaces
     */
    public function __construct(DependencyProviderContainerInterface $dependencyProviderContainer, array $namespaces = [])
    {
        $this->dependencyProviderContainer = $dependencyProviderContainer;
        $this->modules = new ModuleCollection();
        $this->namespaces = $namespaces;
    }

    /**
     * @return DependencyProviderContainerInterface
     */
    public function getDependencyProviderContainer()
    {
        return $this->dependencyProviderContainer;
    }

    /**
     * @param string $namespace
     */
    public function registerNamespace($namespace)
    {
        array_unshift($this->namespaces, $namespace);
    }

    /**
     * @return array
     */
    public function getNamespaces()
    {
        return $this->namespaces;
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        if(!$this->modules->hasModule($name)){
            $module = $this->createModule($name);
            $this->modules->addModule($name, $module);
        }

        return $this->modules->getModule($name);
    }

    /**
     * @param string $name
     * @return ModuleInterface
     */
    private function createModule($name)
    {
        try {
            $moduleClassName = $this->locateClassName("{$name}\\{$name}Module");
        }
        catch (ClassNotFoundException $e){
            $moduleClassName = Module::class;
        }

        /* @var ModuleInterface $module */
        if(!($module = new $moduleClassName) instanceof ModuleInterface){
            throw new \RuntimeException("Invalid module class {$moduleClassName}");
        }

        $factoryClassName = $this->locateClassName("{$name}\\{$name}Factory");
        $facadeClassName = $this->locateClassName("{$name}\\{$name}Facade");

        /* @var FactoryInterface $factory */
        $factory = new $factoryClassName;
        $factory->setLocator($this);

        /* @var FacadeInterface $facade */
        $facade = new $facadeClassName;
        $facade->setFactory($factory);
        $factory->setLocator($this);

        $module->setFactory($factory);
        $module->setFacade($facade);
        $module->setLocator($this);

        $module->onLoad();

        return $module;
    }

    /**
     * @param string $className
     * @return string
     * @throws ClassNotFoundException
     */
    private function locateClassName($className)
    {
        foreach($this->namespaces as $namespace){
            if(class_exists($namespace . $className)){
                return $namespace . $className;
            }
        }

        throw new ClassNotFoundException("Could not locate \"{$className}\"");
    }
}
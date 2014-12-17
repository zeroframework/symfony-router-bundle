<?php

namespace Loader;

use interfaces\containerAwaireInterface;
use \Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\RouteCollection;

class ZFRoutingLoader extends Loader
{
    private $confLoader;
    private $directory;

    public function __construct($confLoader, $directory)
    {
        $this->confLoader = $confLoader;
        $this->directory = $directory;
    }

    /**
     * Loads a resource.
     *
     * @param mixed $resource The resource
     * @param string $type     The resource type
     */
    public function load($resource, $type = null)
    {
        $collection = new RouteCollection();

        $routes = $this->confLoader->loadConfigurationFile($resource, $this->directory);

        $buildRoute = function($routeparam)
        {
            return new \Symfony\Component\Routing\Route(
                $routeparam["path"],
                (!empty($routeparam["defaults"])) ? $routeparam["defaults"] : array(),
                (!empty($routeparam["requirements"])) ? $routeparam["requirements"] : array(), // Requirements,
                (!empty($routeparam["options"])) ? $routeparam["options"] : array(), // options
                (!empty($routeparam["host"])) ? $routeparam["host"] : '', // Host
                (!empty($routeparam["schemes"])) ? $routeparam["schemes"] : array(), // Schemes
                (!empty($routeparam["methods"])) ? $routeparam["methods"] : array(), // Methods
                (!empty($routeparam["condition"])) ? $routeparam["condition"] : null // Condition
            );
        };

        foreach($routes as $routename => $routeparam)
        {
            if(isset($routeparam["locales"]))
            {
                $collectionLocalesRoutes = new RouteCollection();

                foreach($routeparam["locales"] as $locale => $path)
                {
                    $collectionLocalesRoutes->add($routename.".".$locale, $buildRoute(array_merge($routeparam, array("path" => $path))));
                }

                $collection->addCollection($collectionLocalesRoutes);
            }
            else
            {
                $collection->add($routename, $buildRoute($routeparam));
            }
        }

        return $collection;
    }

    /**
     * Returns true if this class supports the given resource.
     *
     * @param mixed $resource A resource
     * @param string $type     The resource type
     *
     * @return Boolean true if this class supports the given resource, false otherwise
     */
    public function supports($resource, $type = null)
    {
        return $type === "advanced_extra";
    }

}

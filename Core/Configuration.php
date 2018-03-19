<?php

namespace TypeformBundle\Core;

use Symfony\Component\Routing\Router;
use Symfony\Component\Routing\RouterInterface;

/**
 * Class Configuration.
 */
class Configuration
{
    protected $base_url;

    protected $embed_url;

    protected $api_key;

    protected $router;

    protected $translator;

    /**
     * Configuration constructor.
     *
     * @param array               $configuration
     * @param RouterInterface     $router
     *
     * @throws \Exception
     */
    public function __construct(array $configuration, RouterInterface $router)
    {
        $this->embed_url = $configuration['embed_url'];
        $this->api_key = $configuration['api_key'];
        $this->router = $router;

        $router = $this->router;
        $routingContext = $router->getContext();
        $this->base_url = $routingContext->getScheme().'://'.$routingContext->getHost();
    }

    /**
     * @return string
     */
    public function getApiKey()
    {
        return $this->api_key;
    }

    /**
     * @return string
     */
    public function getBaseUrl(): string
    {
        return $this->base_url;
    }

    /**
     * @return string
     */
    public function getEmbedUrl()
    {
        return $this->embed_url;
    }

    /**
     * @return RouterInterface
     */
    public function getRouter(): RouterInterface
    {
        return $this->router;
    }
}

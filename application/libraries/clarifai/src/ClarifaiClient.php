<?php

namespace BE\Clarifai\Sdk;

use BE\Clarifai\Sdk\Exceptions\InvalidCredentialsException;
use BE\Clarifai\Sdk\Exceptions\NotImplementedMethodException;
use BE\Clarifai\Sdk\Responses\ResponseFactory;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 *
 * Client class, provides access to endpoint instances
 *
 * Class ClarifaiClient
 * @package BE\Clarifai\Sdk
 */
class ClarifaiClient
{
    /**
     * authenticate credential
     *
     * @var string
     */
    private $clientId;

    /**
     * authenticate credential
     *
     * @var string
     */
    private $clientSecret;

    /**
     * unique key to determine token in storage
     *
     * @var string
     */
    private $tokenKey;

    /**
     * @var \BE\Clarifai\Sdk\Contracts\TokenStorage
     */
    private $tokenStorage;


    public function __construct($clientId = null, $clientSecret = null)
    {
        require_once (APPPATH . '/libraries/clarifai/vendor/symfony/dependency-injection/ContainerBuilder.php');
        require_once (APPPATH . '/libraries/clarifai/vendor/symfony/dependency-injection/Reference.php');

        $this->container = new ContainerBuilder();

        $this->loadDependencies();

        if ($clientId && $clientSecret) {
            $this->setCredentials($clientId, $clientSecret);
            $this->processAuthentication();
        }
    }

    /**
     * @param $clientId
     * @param $clientSecret
     *
     * @return $this;
     */
    public function setCredentials($clientId, $clientSecret)
    {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->tokenKey = md5($clientId . $clientSecret);
        $this->container()->setParameter('tokenKey', $this->tokenKey);

        return $this;
    }


    /**
     * @var ContainerBuilder
     */
    private $container;

    /**
     * container provides opportunity to replace default interface implementation by clients realisation
     * @return ContainerBuilder
     */
    public function container()
    {
        return $this->container;
    }

    /**
     * @var \Be\Clarifai\Sdk\Contracts\Endpoints\Authenticate
     */
    private $authenticate;

    /**
     * authenticate endpoint
     * @see https://developer.clarifai.com/guide-v1/#authentication
     *
     * @return \Be\Clarifai\Sdk\Contracts\Endpoints\Authenticate
     */
    public function authenticate()
    {
        if (is_null($this->authenticate)) {
            $this->authenticate = $this->container()->get('clarifai.endpoints.authenticate');
        }

        return $this->authenticate;
    }

    /**
     * @var \BE\Clarifai\Sdk\Contracts\Endpoints\Tag
     */
    private $tag;

    /**
     * @return \BE\Clarifai\Sdk\Contracts\Endpoints\Tag
     */
    public function tag()
    {
        if (!$this->checkToken()) {
            $this->processAuthentication();
        }

        if (is_null($this->tag)) {
            $this->tag = $this->container()->get('clarifai.endpoints.tag');
        }

        return $this->tag;
    }

    /**
     * @var \BE\Clarifai\Sdk\Contracts\Endpoints\Color
     */
    private $color;

    public function color()
    {
        throw new NotImplementedMethodException();

        if (is_null($this->color)) {
            $this->color = $this->container()->get('clarifai.endpoints.color');
        }

        return $this->color;
    }

    /**
     * @var \BE\Clarifai\Sdk\Contracts\Endpoints\Feedback
     */
    private $feedback;

    /**
     * @return Contracts\Endpoints\Feedback
     */
    public function feedback()
    {
        if (is_null($this->feedback)) {
            $this->feedback = $this->container()->get('clarifai.endpoints.feedback');
        }

        return $this->feedback;
    }

    /**
     * @var \BE\Clarifai\Sdk\Contracts\Endpoints\Info
     */
    private $info;

    /**
     * @return Contracts\Endpoints\Info
     */
    public function info()
    {
        if (is_null($this->info)) {
            $this->info = $this->container()->get('clarifai.endpoints.info');
        }

        return $this->info;
    }

    /**
     * @var \BE\Clarifai\Sdk\Contracts\Endpoints\Language
     */
    private $language;

    /**
     * @return Contracts\Endpoints\Language
     */
    public function language()
    {
        if (is_null($this->language)) {
            $this->language = $this->container()->get('clarifai.endpoints.language');
        }

        return $this->language;
    }

    /**
     * @var \BE\Clarifai\Sdk\Contracts\Endpoints\Usage
     */
    private $usage;

    public function usage()
    {
        throw new NotImplementedMethodException();

        if (is_null($this->usage)) {
            $this->usage = $this->container()->get('clarifai.endpoints.usage');
        }

        return $this->usage;
    }

    /**
     * @return Contracts\TokenStorage
     */
    public function tokenStorage()
    {
        return $this->tokenStorage;
    }

    /**
     *
     * check token existence and not expired
     *
     * @return boolean
     */
    public function checkToken()
    {
        return $this->tokenStorage()
            && $this->tokenStorage()->exists($this->tokenKey)
            && !$this->tokenStorage()->get($this->tokenKey)->isExpired();
    }

    /**
     * @param $clientId
     * @param $clientSecret
     *
     * @return $this
     */
    public function processAuthentication()
    {
        // no credentials- no party
        if (!$this->tokenKey) {
            throw new InvalidCredentialsException("Credentials are not set");
        }

        // init storage on first call
        if (is_null($this->tokenStorage)) {
            $this->initTokenStorage();
        }

        // create new token if token not exists or token is expired
        if (!$this->tokenStorage()->exists($this->tokenKey)
            || $this->tokenStorage()->get($this->tokenKey)->isExpired()
        ) {
            $this->tokenStorage()->set(
                $this->tokenKey,
                $this->authenticate()->token($this->clientId, $this->clientSecret)
            );
        }
    }

    /**
     * init storage from container
     */
    private function initTokenStorage()
    {
        $this->tokenStorage = $this->container()->get('clarifai.token.storage');
    }


    /**
     * load dependencies in container
     */
    private function loadDependencies()
    {

        $this->container()->register('clarifai.token.storage', \BE\Clarifai\Sdk\Token\Storage\Filesystem::class)
            ->addArgument('%token.storage.filesystem.path%');


        $this->container()->register('clarifai.request.endpoint', \BE\Clarifai\Sdk\Request\Endpoint::class);
        $this->container()->register('clarifai.request.endpoint.authenticate', \BE\Clarifai\Sdk\Request\EndpointAuthenticate::class)
            ->addArgument(new Reference('clarifai.token.storage'))
            ->addArgument('%tokenKey%');

        $this->container()->register('clarifai.responses.factory', ResponseFactory::class);

        $this->container()
            ->register('clarifai.endpoints.authenticate', \BE\Clarifai\Sdk\Endpoints\Authenticate::class)
            ->addArgument(new Reference('clarifai.request.endpoint'))
            ->addArgument(new Reference('clarifai.responses.factory'));

        $this->container()
            ->register('clarifai.endpoints.tag', \BE\Clarifai\Sdk\Endpoints\Tag::class)
            ->addArgument(new Reference('clarifai.request.endpoint.authenticate'))
            ->addArgument(new Reference('clarifai.responses.factory'));

        $this->container()->register('clarifai.endpoints.color', \BE\Clarifai\Sdk\Endpoints\Color::class);

        $this->container()->register('clarifai.endpoints.feedback', \BE\Clarifai\Sdk\Endpoints\Feedback::class)
            ->addArgument(new Reference('clarifai.request.endpoint.authenticate'))
            ->addArgument(new Reference('clarifai.responses.factory'));;

        $this->container()->register('clarifai.endpoints.info', \BE\Clarifai\Sdk\Endpoints\Info::class)
            ->addArgument(new Reference('clarifai.request.endpoint.authenticate'))
            ->addArgument(new Reference('clarifai.responses.factory'));;

        $this->container()
            ->register('clarifai.endpoints.language', \BE\Clarifai\Sdk\Endpoints\Language::class)
            ->addArgument(new Reference('clarifai.request.endpoint.authenticate'))
            ->addArgument(new Reference('clarifai.responses.factory'));;

        $this->container()->register('clarifai.endpoints.usage', \BE\Clarifai\Sdk\Endpoints\Usage::class);

        $this->container()->setParameter('service_container', $this->container());
        $this->container()->setParameter(
            'token.storage.filesystem.path',
            __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'token.store'
        );

        $this->container()->setParameter(
            'tokenKey',
            'should be specified after credentials is set'
        );
    }
}
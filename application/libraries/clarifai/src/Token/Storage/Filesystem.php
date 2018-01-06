<?php


namespace BE\Clarifai\Sdk\Token\Storage;

require_once (APPPATH . '/libraries/clarifai/src/Contracts/TokenStorage.php');

use BE\Clarifai\Sdk\Contracts\AuthenticationToken;
use BE\Clarifai\Sdk\Contracts\TokenStorage;

/**
 * Class Filesystem
 * @package BE\Clarifai\Sdk\Token\Storage
 */
class Filesystem implements TokenStorage
{
    /**
     * path to file with token serialized
     *
     * @var string
     */
    private $path;

    /**
     * @var array
     */
    private $values;

    public function __construct($path)
    {
        $this->path = $path;

        $this->load();
    }

    /**
     * @param $key
     * @return AuthenticationToken
     */
    public function get($key)
    {
        return $this->exists($key) ? $this->values[$key] : null;
    }

    /**
     * @param $key
     * @param AuthenticationToken $token
     * @return mixed
     */
    public function set($key, AuthenticationToken $token)
    {
        $this->values[$key] = $token;
        $this->save();
    }

    /**
     * @param $key
     * @return boolean
     */
    public function exists($key)
    {
        return array_key_exists($key, $this->values);
    }

    private function load()
    {
        if (file_exists($this->path)) {
            $this->values = unserialize(file_get_contents($this->path));
        } else {
            $this->values = [];
        }

    }

    private function save()
    {
        file_put_contents($this->path, serialize($this->values));
    }
}
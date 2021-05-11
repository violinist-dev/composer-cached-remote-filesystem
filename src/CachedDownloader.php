<?php

namespace Violinist\CachedRemoteFilesystem;

use Composer\Config;
use Composer\IO\IOInterface;
use Composer\Util\HttpDownloader;
use React\Promise\Promise;
use Symfony\Component\Cache\Simple\FilesystemCache;

class CachedDownloader extends HttpDownloader
{
    private $io;

    public function __construct(IOInterface $io, Config $config, array $options = array(), $disableTls = false)
    {
        parent::__construct($io, $config, $options, $disableTls);
        $this->io = $io;
    }

    public function get($url, $options = array())
    {
        // Create a cache id.
        $cid = sha1(json_encode(func_get_args()));
        // Cache for 15 minutes.
        $ttl = (15 * 60);
        $cache = new FilesystemCache();
        if ($cache->has($cid)) {
            $data = $cache->get($cid);
            $this->io->writeError('Forcing disk cache for URL ' . $url, true, IOInterface::DEBUG);
            return $data['body'];
        }
        $data = parent::get($url, $options);
        $cache->set($cid, [
            'body' => $data,
        ], $ttl);
        return $data;
    }

    public function add($url, $options = array())
    {
        // Create a cache id.
        $cid = sha1(json_encode(func_get_args()));
        $cache = new FilesystemCache();
        if ($cache->has($cid)) {
            $data = $cache->get($cid);
            $promise = new Promise(function ($resolve, $reject) use ($url, $data) {
                $this->io->writeError('Forcing disk cache for URL ' . $url, true, IOInterface::DEBUG);
                $resolve($data);
            });
            return $promise;
        }
        $promise = parent::add($url, $options);
        $promise->then(function($value) use ($cache, $cid) {
            $cache->set($cid, $value, (15 * 60));
        });
        return $promise;
    }

}

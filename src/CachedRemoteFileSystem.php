<?php

namespace Violinist\CachedRemoteFilesystem;

use Composer\IO\IOInterface;
use Composer\Util\RemoteFilesystem;
use Symfony\Component\Cache\Simple\FilesystemCache;

class CachedRemoteFileSystem extends RemoteFilesystem
{
    private $lastCachedHeaders;
    private $outerIo;

    public function getOptions() {
      return $this->originalFileSystem->getOptions(); // TODO: Change the autogenerated stub
    }

    public function setOptions(array $options) {
      $this->originalFileSystem->setOptions($options); // TODO: Change the autogenerated stub
    }

    public function isTlsDisabled() {
      return $this->originalFileSystem->isTlsDisabled(); // TODO: Change the autogenerated stub
    }

    public function getLastHeaders() {
      if (isset($this->lastCachedHeaders)) {
        return $this->lastCachedHeaders;
      }
      return $this->originalFileSystem->getLastHeaders();
    }

    public function findHeaderValue(array $headers, $name) {
      return $this->originalFileSystem->findHeaderValue($headers, $name); // TODO: Change the autogenerated stub
    }

    public function findStatusCode(array $headers) {
      return $this->originalFileSystem->findStatusCode($headers); // TODO: Change the autogenerated stub
    }

    public function findStatusMessage(array $headers) {
      return $this->originalFileSystem->findStatusMessage($headers); // TODO: Change the autogenerated stub
    }

    protected function get($originUrl, $fileUrl, $additionalOptions = [], $fileName = NULL, $progress = TRUE) {
      return $this->originalFileSystem->get($originUrl, $fileUrl, $additionalOptions, $fileName, $progress); // TODO: Change the autogenerated stub
    }

    protected function callbackGet($notificationCode, $severity, $message, $messageCode, $bytesTransferred, $bytesMax) {
      $this->originalFileSystem->callbackGet($notificationCode, $severity, $message, $messageCode, $bytesTransferred, $bytesMax); // TODO: Change the autogenerated stub
    }

    protected function promptAuthAndRetry($httpStatus, $reason = NULL, $warning = NULL, $headers = array()) {
      return $this->originalFileSystem->promptAuthAndRetry($httpStatus, $reason, $warning, $headers);
    }

    protected function getOptionsForUrl($originUrl, $additionalOptions) {
      return $this->originalFileSystem->getOptionsForUrl($originUrl, $additionalOptions); // TODO: Change the autogenerated stub
    }

    protected $originalFileSystem;


    public function __construct(RemoteFilesystem $originalFileSystem, IOInterface $io) {
      $this->originalFileSystem = $originalFileSystem;
      $this->outerIo = $io;
    }

    public function copy($originUrl, $fileUrl, $fileName, $progress = TRUE, $options = []) {
      return $this->originalFileSystem->copy($originUrl, $fileUrl, $fileName, $progress, $options);
    }

    public function getContents($originUrl, $fileUrl, $progress = TRUE, $options = []) {
      // Create a cache id.
      $cid = sha1(json_encode(func_get_args()));
      // Cache for 15 minutes.
      $ttl = (15 * 60);
      $cache = new FilesystemCache();
      if ($cache->has($cid)) {
        $this->outerIo->writeError('Forcing disk cache for URL ' . $fileUrl, TRUE, IOInterface::DEBUG);
        $data = $cache->get($cid);
        if (is_array($data)) {
          $this->lastCachedHeaders = $data['headers'];
          return $data['body'];
        }
      }
      $data = $this->originalFileSystem->getContents($originUrl, $fileUrl, $progress, $options);
      $cache->set($cid, [
        'body' => $data,
        'headers' => $this->originalFileSystem->getLastHeaders(),
      ], $ttl);
      $this->lastCachedHeaders = $this->originalFileSystem->getLastHeaders();
      return $data;
    }

}

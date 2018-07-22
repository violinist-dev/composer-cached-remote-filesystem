<?php

namespace Violinist\CachedRemoteFilesystem;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginEvents;
use Composer\Plugin\PluginInterface;
use Composer\Plugin\PreFileDownloadEvent;

class EventSubscriber implements PluginInterface, EventSubscriberInterface
{

    /**
     * @var IOInterface
     */
    protected $io;

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            PluginEvents::PRE_FILE_DOWNLOAD => ['onPreFileDownload', -10],
        ];
    }

    public function onPreFileDownload(PreFileDownloadEvent $event)
    {
        $rfs = $event->getRemoteFilesystem();
        $crfs = new CachedRemoteFileSystem($rfs, $this->io);
        $event->setRemoteFilesystem($crfs);
    }

    /**
     * {@inheritdoc}
     */
    public function activate(Composer $composer, IOInterface $io)
    {
        $this->composer = $composer;
        $this->io = $io;
    }
}

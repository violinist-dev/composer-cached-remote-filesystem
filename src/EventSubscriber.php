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

    protected $composer;

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
        try {
            // Composer version 1, yes this is what we are using there.
            $rfs = $event->getRemoteFilesystem();
            $crfs = new CachedRemoteFileSystem($rfs, $this->io);
            $event->setRemoteFilesystem($crfs);
        }
        catch (\Throwable $e) {
            // Totally fine. We can do this differently on composer 2.
        }
    }

    /**
     * {@inheritdoc}
     */
    public function activate(Composer $composer, IOInterface $io)
    {
        $this->composer = $composer;
        $this->io = $io;
    }

    public function deactivate(Composer $composer, IOInterface $io)
    {
        // We do not need to do anything.
    }

    public function uninstall(Composer $composer, IOInterface $io)
    {
        // We do not need to do anything.
    }
}

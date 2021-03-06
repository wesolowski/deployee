<?php

namespace Deployee\Plugins\DeployHistory\Subscriber;


use Deployee\Events\BootstrapFinishedEvent;
use Deployee\Events\EventDispatcher;
use Deployee\Plugins\Deploy\Events\InstallEvent;
use Deployee\Plugins\Deploy\Events\PostRunDeployEvent;
use Deployee\Plugins\Deploy\Events\PreRunDeployEvent;
use Deployee\Plugins\DeployHistory\Events\PreAddToHistoryEvent;
use Deployee\Plugins\DeployHistory\Services\HistoryService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DeployHistorySubscriber implements EventSubscriberInterface
{
    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            PreRunDeployEvent::EVENT_NAME => "onPreDeploy",
            PostRunDeployEvent::EVENT_NAME => "onPostDeploy",
            BootstrapFinishedEvent::EVENT_NAME => "onBootstrapFinished",
            InstallEvent::EVENT_NAME => "onInstall"
        ];
    }

    /**
     * @param InstallEvent $event
     */
    public function onInstall(InstallEvent $event)
    {
        /* @var HistoryService $historyService */
        $historyService = $event->getContainer()[HistoryService::CONTAINER_ID];
        $event->getOutput()->writeln("Plugin DeployHistory >> Running setup");
        $historyService->setup();
        $event->getOutput()->writeln("Plugin DeployHistory >> Done!");
    }

    /**
     * @param PreRunDeployEvent $event
     */
    public function onPreDeploy(PreRunDeployEvent $event)
    {
        $unsetElements = [];
        /* @var HistoryService $historyService */
        $historyService = $event->getContainer()[HistoryService::CONTAINER_ID];
        $definitions = $event->getDefinitions();
        foreach($definitions as $offset => $definition){
            if($historyService->isStored($definition)){
                $event->getContainer()->logger()->debug("Already deployed " . get_class($definition));
                $unsetElements[] = $offset;
            }
        }

        foreach($unsetElements as $offset){
            $event->getDefinitions()->offsetUnset($offset);
        }
        $event->getDefinitions()->rewind();
    }

    /**
     * @param PostRunDeployEvent $event
     */
    public function onPostDeploy(PostRunDeployEvent $event)
    {
        /* @var HistoryService $historyService */
        $historyService = $event->getContainer()[HistoryService::CONTAINER_ID];
        $definition = $event->getDefinition();

        $event = new PreAddToHistoryEvent($event->getContainer(), $definition);
        $event->getContainer()[EventDispatcher::CONTAINER_ID]->dispatch(PreAddToHistoryEvent::EVENT_NAME, $event);

        if($event->canStore()) {
            $event->getContainer()->logger()->debug("Add to history " . get_class($definition));
            $historyService->store($definition);
        }
    }

    public function onBootstrapFinished(BootstrapFinishedEvent $event)
    {


    }
}
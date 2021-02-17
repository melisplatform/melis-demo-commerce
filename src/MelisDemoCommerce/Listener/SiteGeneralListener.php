<?php 

/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2016 Melis Technology (http://www.melistechnology.com)
 *
 */

namespace MelisDemoCommerce\Listener;

use Laminas\EventManager\EventInterface;
use Laminas\EventManager\EventManagerInterface;
use Laminas\EventManager\AbstractListenerAggregate;

/**
 * Melis General Listener implements detach
 * Other listener can extends this class and not
 * redefine those
 */
abstract class SiteGeneralListener extends AbstractListenerAggregate
{
    /**
     * Attach a listener to an event emitted by components with specific identifiers.
     *
     * @param  EventManagerInterface $events
     * @param  string $identifier Identifier for event emitting component
     * @param  string $eventName
     * @param  callable $listener Listener that will handle the event.
     * @param  int $priority Priority at which listener should execute
     *
     */
	public function attachEventListener(EventManagerInterface $events, $identifier, $eventName, callable $listener,  $priority = 1)
    {
        $sharedEvents = $events->getSharedManager();

        if (empty($eventName))
            return;

        if (!is_callable($listener))
            return;

        if (is_array($eventName)) {
            foreach ($eventName As $event)
                $this->listeners[] = $sharedEvents->attach($identifier, $event, $listener, $priority);
        }else{
            $this->listeners[] = $sharedEvents->attach($identifier, $eventName, $listener, $priority);
        }
    }
}
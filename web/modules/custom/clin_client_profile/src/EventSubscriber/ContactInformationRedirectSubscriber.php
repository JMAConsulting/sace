<?php
namespace Drupal\clin_client_profile\EventSubscriber;

use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

class ContactInformationRedirectSubscriber implements EventSubscriberInterface {

  public static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST][] = ['onKernelRequest', 10];
    return $events;
  }

  public function onKernelRequest(RequestEvent $event) {
    $request = $event->getRequest();
    $path = $request->getPathInfo();
    $matches = [];
    
    // Check if the path matches /contact-information/###
    if (preg_match('/^\/contact-information\/(\d+)$/', $path, $matches)) {
      $cid = $matches[1];
      $query = $request->query->all();
      
      // If 'cid' is not already set in the query parameters, add it
      if (!isset($query['cid'])) {
        $query['cid'] = $cid;
        $redirect_url = $request->getSchemeAndHttpHost() . $path . '?' . http_build_query($query);
        $event->setResponse(new RedirectResponse($redirect_url));
      }
    }
  }
}

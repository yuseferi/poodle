<?php

namespace Drupal\poodle\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\node\Entity\Node;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Returns responses for poodle routes.
 */
class LoginCallBackController extends ControllerBase {

  /**
   * Builds the response.
   */
  public function build() {

    $nid = $_COOKIE["poll"];
    $node = Node::load($nid);
    $alias = \Drupal::service('path_alias.manager')->getAliasByPath('/node/'.$nid);
    $redirect = new RedirectResponse($alias);
    $redirect->send();

  }

}

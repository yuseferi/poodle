<?php

namespace Drupal\poodle\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Returns responses for poodle routes.
 */
class PoodleController extends ControllerBase {

  /**
   * Builds the response.
   */
  public function build() {

    $build['content'] = [
      '#type' => 'item',
      '#markup' => $this->t('It works!'),
    ];

    return $build;
  }

}

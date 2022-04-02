<?php

namespace Drupal\poodle\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides an example block.
 *
 * @Block(
 *   id = "poodle_poll",
 *   admin_label = @Translation("Poll"),
 *   category = @Translation("poodle")
 * )
 */
class PollBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $form = \Drupal::formBuilder()->getForm('Drupal\poodle\Form\PollForm');
    return $form;
  }

}

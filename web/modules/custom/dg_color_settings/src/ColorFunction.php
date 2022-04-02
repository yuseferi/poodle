<?php

namespace Drupal\dg_color_settings;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\node\Entity\Node;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Custom twig functions.
 */
final class ColorFunction extends AbstractExtension {

  public function getFunctions() {
    return [
      new TwigFunction('get_colors', [$this, 'getColors']),
    ];
  }

  /**
   * Drupal\Core\Config\ConfigManagerInterface definition.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  public function __construct(ConfigFactoryInterface $configFactory) {
    $this->configFactory = $configFactory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('config.factory'));
  }


  public function getColors(Node $node) {
    $key = $node->field_guideline_ref->getValue() ? $node->field_guideline_ref->getValue()[0]["target_id"] : NULL;
    $colors = NULL;
    if ($key !== NULL) {
      $colorSetName = $this->configFactory->get('dg_color_settings.color_settings')
        ->get($key . '.colorset')["colorset"];
      $colors = $this->configFactory->get('dg_color_settings.color_options')
        ->get('global')[$colorSetName];
    }
    return $colors;
  }

}

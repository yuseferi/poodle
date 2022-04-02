<?php

namespace Drupal\dg_color_settings\Element;

use Drupal\Component\Utility\Html;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element\FormElement;

/**
 * Provide a color box form element.
 *
 * @FormElement("color_field_element_box")
 */
class ColorFieldElementBox extends FormElement {

  /**
   * {@inheritdoc}
   */
  public function getInfo(): array {
    $class = get_class($this);
    return [
      '#input' => TRUE,
      '#process' => [
        [$class, 'processColorFieldElementBox'],
      ],
    ];
  }

  /**
   * Create form element structure for color boxes provided by
   * color_field module.
   *
   * @param array $element
   *   The form element to process.
   * @param FormStateInterface $form_state
   *   The current state of the form.
   * @param array $form
   *   The complete form structure.
   *
   * @return array
   *   The form element.
   */
  public static function processColorFieldElementBox(array &$element, FormStateInterface $form_state, array &$form): array {
    $element['#uid'] = Html::getUniqueId($element['#name']);
    $element['element']['#type'] = 'container';

    $element['element']['color'] = [
      '#type' => 'textfield',
      '#maxlength' => 7,
      '#size' => 7,
      '#required' => $element['#required'],
      '#default_value' => $element['#default_value'],
    ];

    $element['element']['#attributes']['id'] = $element['#uid'];

    $element['element']['#attributes']['class'][] = 'js-color-field-widget-spectrum';
    $element['element']['color']['#attributes']['class'][] = 'js-color-field-widget-spectrum__color';
    $element['element']['#attached']['drupalSettings']['color_field']['color_field_widget_spectrum'][$element['#uid']] = [
      'show_input' => true,
      'show_palette' => true,
      'palette' => '',
      'show_buttons' => true,
      'allow_empty' => true,
      'show_palette_only' => false,
      'show_alpha' => true
    ];

    $element['#attached']['library'][] = 'color_field/color-field-widget-spectrum';
    return $element;
  }
}

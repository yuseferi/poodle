<?php

namespace Drupal\dg_color_settings\Form;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Component\Utility\Color;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class GuidelineColorSettingsForm.
 *
 * @package Drupal\dg_guideline_settings\Form
 */
class ColorChooseSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'color_choose_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['dg_color_settings.color_options'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('dg_color_settings.color_options');
    $global_colors = $config->get('global');

    $form['global_colors'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Global color field options'),
    ];
    $form['global_colors']['colors'] = [
      '#type' => 'table',
      '#title' => $this->t('Global colors'),
      '#header' => [
        $this->t('Name'),
        $this->t('Primary color'),
        $this->t('Secondary color'),
      ],
      '#empty' => t('There are no items yet.'),
    ];

    if ($global_colors) {
      foreach ($global_colors as $key => $value) {
        $form['global_colors']['colors'][$key]['name'] = [
          '#type' => 'textfield',
          '#title_display' => 'invisible',
          '#size' => 10,
          '#default_value' => $value['name'],
        ];
        $form['global_colors']['colors'][$key]['color1'] = [
          '#type' => 'color_field_element_box',
          '#title_display' => 'invisible',
          '#size' => 10,
          '#default_value' => $value['color1'],
        ];
        $form['global_colors']['colors'][$key]['color2'] = [
          '#type' => 'color_field_element_box',
          '#title_display' => 'invisible',
          '#size' => 10,
          '#default_value' => $value['color2'],
        ];
      }
    }

    $form['global_colors']['colors']['new_global_value']['name'] = [
      '#type' => 'textfield',
      '#title_display' => 'invisible',
      '#size' => 10,
      '#default_value' => NULL,
    ];
    $form['global_colors']['colors']['new_global_value']['color1'] = [
      '#type' => 'color_field_element_box',
      '#title_display' => 'invisible',
      '#size' => 10,
      '#default_value' => NULL,
    ];
    $form['global_colors']['colors']['new_global_value']['color2'] = [
      '#type' => 'color_field_element_box',
      '#title_display' => 'invisible',
      '#size' => 10,
      '#default_value' => NULL,
    ];

    return parent::buildForm($form, $form_state);
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
    $color_values = $form_state->getValue('colors');

    foreach ($color_values as $setting_key => $setting_value) {
      if (isset($setting_value['name']) && isset($setting_value['color']['element']['color1']) && !empty($setting_value['color']['element']['color1'])) {
        $is_valid = Color::validateHex($setting_value['color']['element']['color1']);
        if (!$is_valid) {
          $form_state->setError($form['colors'][$setting_key], $this->t("Primary color is not valid!"));
        }
      }
      if (isset($setting_value['name']) && isset($setting_value['color']['element']['color2']) && !empty($setting_value['color']['element']['color2'])) {
        $is_valid = Color::validateHex($setting_value['color']['element']['color2']);
        if (!$is_valid) {
          $form_state->setError($form['colors'][$setting_key], $this->t("Secondary color is not valid!"));
        }
      }
    }

    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('dg_color_settings.color_options');
    $color_values = $form_state->getValue('colors');


    foreach ($color_values as $key => $value) {
      $keyword = $key;
      if ($value['name'] && $value['color1']['element']['color'] && $value['color2']['element']['color']) {
        if ($key == 'new_global_value') {
          $name = $value['name'];
          $keyword = str_replace(' ', '_', strtolower($name));
        }
        else {
          $name = $value['name'];
        }
        $config->set('global.' . $keyword, [
          'name' => $name,
          'color1' => $value['color1']['element']['color'],
          'color2' => $value['color2']['element']['color'],
        ]);
      }
      else {
        if ($key != 'new_global_value') {
          $config->clear('global.' . $keyword);
        }
      }
    }
    $config->save();
  }

}

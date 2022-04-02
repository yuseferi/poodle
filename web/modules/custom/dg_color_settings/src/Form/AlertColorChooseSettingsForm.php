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
class AlertColorChooseSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'alert_color_choose_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['dg_color_settings.alter_color_options '];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('dg_color_settings.alter_color_options ');
    $global_colors = $config->get('global');
    $form['global_colors'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Alert color options'),
    ];
    $form['global_colors']['colors'] = [
      '#type' => 'table',
      '#title' => $this->t('Alert colors'),
      '#header' => [
        $this->t('Name'),
        $this->t('Text color'),
        $this->t('Background color'),
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
        $form['global_colors']['colors'][$key]['text_color'] = [
          '#type' => 'color_field_element_box',
          '#title_display' => 'invisible',
          '#size' => 10,
          '#default_value' => $value['text_color'],
        ];
        $form['global_colors']['colors'][$key]['background_color'] = [
          '#type' => 'color_field_element_box',
          '#title_display' => 'invisible',
          '#size' => 10,
          '#default_value' => $value['background_color'],
        ];
      }
    }

    $form['global_colors']['colors']['new_global_value']['name'] = [
      '#type' => 'textfield',
      '#title_display' => 'invisible',
      '#size' => 10,
      '#default_value' => NULL,
    ];
    $form['global_colors']['colors']['new_global_value']['text_color'] = [
      '#type' => 'color_field_element_box',
      '#title_display' => 'invisible',
      '#size' => 10,
      '#default_value' => NULL,
    ];
    $form['global_colors']['colors']['new_global_value']['background_color'] = [
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
      if (isset($setting_value['name']) && isset($setting_value['color']['element']['text_color']) && !empty($setting_value['color']['element']['text_color'])) {
        $is_valid = Color::validateHex($setting_value['color']['element']['text_color']);
        if (!$is_valid) {
          $form_state->setError($form['colors'][$setting_key], $this->t("Text color is not valid!"));
        }
      }
      if (isset($setting_value['name']) && isset($setting_value['color']['element']['background_color']) && !empty($setting_value['color']['element']['background_color'])) {
        $is_valid = Color::validateHex($setting_value['color']['element']['background_color']);
        if (!$is_valid) {
          $form_state->setError($form['colors'][$setting_key], $this->t("Background color is not valid!"));
        }
      }
    }

    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('dg_color_settings.alter_color_options ');
    $color_values = $form_state->getValue('colors');

    $styles = [];
    foreach ($color_values as $key => $value) {
      $keyword = $key;
      if ($value['name'] && $value['text_color']['element']['color'] && $value['background_color']['element']['color']) {
        if ($key == 'new_global_value') {
          $name = $value['name'];
          $keyword = str_replace(' ', '_', strtolower($name));
        }
        else {
          $name = $value['name'];
        }
        $config->set('global.' . $keyword, [
          'name' => $name,
          'text_color' => $value['text_color']['element']['color'],
          'background_color' => $value['background_color']['element']['color'],
        ]);
      }
      else {
        if ($key != 'new_global_value') {
          $config->clear('global.' . $keyword);
        }
      }
    }
    $config->save();
    $global_colors = $config->get('global');
    // Save it as configuration for sitewide alerts
    $alertConfig = \Drupal::configFactory()
      ->getEditable('sitewide_alert.settings');
    $value = "";
    $i = 1;
    foreach ($global_colors as $idx => $item) {
      if ($i != count($styles)) {
        $value .= sprintf("%s|%s\n", $idx, $item["name"]);
      }
      else {
        $value .= sprintf("%s|%s", $idx, $item);
      }
      $i++;
    }
    $alertConfig->set('alert_styles', $value)->save();
  }

}

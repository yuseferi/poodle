<?php

namespace Drupal\dg_color_settings\Form;

use Drupal\Component\Utility\Color;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\taxonomy\TermStorage;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class GuidelineColorSettingsForm.
 *
 * @package Drupal\dg_color_settings\Form
 */
class GuidelineColorSettingsForm extends ConfigFormBase {

  /**
   * Drupal\guideline\GuidelineLoader definition.
   *
   * @var \Drupal\Core\Config\Entity\ConfigEntityStorage
   */
  protected $guidelineStorage;

  /**
   * Guideline names
   *
   * @var array
   */
  protected $setting_keys;

  /**
   * GuidelineColorSettingsForm constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   * @param \Drupal\taxonomy\TermStorage $guidelineStorage
   */
  public function __construct(ConfigFactoryInterface $config_factory, TermStorage $guidelineStorage) {
    parent::__construct($config_factory);
    $this->guidelineStorage = $guidelineStorage;
  }

  /**
   * Create function return static guideline loader configuration.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   Load the ContainerInterface.
   *
   * @return \static
   *   return guideline loader configuration.
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('entity_type.manager')->getStorage('taxonomy_term')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'dg_color_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['dg_color_settings.color_settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('dg_color_settings.color_settings');
    $colorOptions = $this->config('dg_color_settings.color_options');
    $colorOptions = $colorOptions->get("global");
    $options = [];
    foreach ($colorOptions as $key => $colorName) {
      $form['#attached']['drupalSettings']['MSF']["guideline_color_set"][$key] = [
          "color1" => $colorName['color1'],
          "color2" => $colorName['color2'],
      ];
      $options[$key] = $colorName["name"];
    }
    $guidelines = $this->guidelineStorage->loadTree("guideline");

    $form['colors'] = [
      '#type' => 'table',
      '#title' => $this->t('Sample Table'),
      '#header' => [
        $this->t('name'),
        $this->t('Color set'),

      ],
      '#empty' => t('There are no items yet.'),
    ];

    foreach ($guidelines as  $guideline) {
      $key = $guideline->tid;
      $form['colors'][$key]['name'] = [
        '#markup' => $guideline->name,
      ];
      $form['colors'][$key]['colorset'] = [
        '#type' => 'select',
        '#title_display' => 'invisible',
        '#options' => $options,
        '#required' => TRUE,
        '#empty_option' => t('- Select a colour set -'),
        '#default_value' => $config->get($key . '.colorset'),
        "#attributes" => ["class" => ["inline"]],
        '#prefix' => "<span class='color-preview1'>&nbsp;</span><span class='color-preview2'>&nbsp;</span>",
      ];
    }

    $form['#attached']['library'][] = 'dg_color_settings/dg_color';


    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('dg_color_settings.color_settings');

    $color_values = $form_state->getValue('colors');
    foreach ($color_values as $key => $value) {
      $config->set($key . '.colorset', $value);
    }
    $config->save();
    parent::submitForm($form, $form_state);
  }
}

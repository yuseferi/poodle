<?php

namespace Drupal\poodle\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\user\Entity\User;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Cookie;


/**
 * Configure Pooddle settings for this site.
 */
class PollForm extends ConfigFormBase
{

  /**
   * {@inheritdoc}
   */
  public function getFormId()
  {
    return 'poodle_poll';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames()
  {
    return ['poodle.polls'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state)
  {

    $node = \Drupal::routeMatch()->getParameter('node');
    if (!$node) {
      return;
    }
    $user = User::load(\Drupal::currentUser()->id());
    $isPrivateForm = $node->field_private_poll->getValue()[0]["value"];
    $expireDate = $node->field_expired_on->getValue();
    $expired = false;
    if(isset($expireDate[0])) {
      $expireDate = $expireDate[0]['value'];
      if(strtotime($expireDate)< time()){
        $expired = true;
        \Drupal::messenger()->addWarning("This poll is expired and you are not able to vote on that anymore");
      }
    }
    if ($isPrivateForm && $user->id() == 0) {
      \Drupal::messenger()->addWarning("This poll is not public and you need to login into the website before vote");
      $redirect = new RedirectResponse(Url::fromRoute('user.login')->toString());
      $cookie = new  Cookie('poll', $node->id());
      $redirect->headers->setCookie($cookie);
      $redirect->send();
    }
    $isPollEditable = $node->field_is_editable->getValue()[0]["value"];
    $options = $node->field_options->getValue();
    $config = $this->config('poodle.polls');
    if (!$isPollEditable) {
      \Drupal::messenger()->addWarning("Notice: this poll is not editable you can not edit your vote after you vote on that!");
    }
    $data = $config->get($node->id());
    $previousData = $data ? unserialize($data) : null;
    $currenUserId = $user->id();
//    dump($previousData);
    $form['poll_table'] = [
      '#type' => 'fieldset',
      '#title' => "Poll : " . $node->getTitle(),
      '#attributes' => [
        'style' => 'width: 130%;'
      ],
    ];
    $headers = ["User"];
    foreach ($options as $option) {
      $headers[] = $option['value'];
    }
//    $headers[] = "Actions";
    $form['poll_table']['polls'] = [
      '#type' => 'table',
      '#title' => $this->t('Poll options'),
      '#header' => $headers,
      "#size" => 4,
      '#empty' => t('Nobody has voted yet'),
    ];
    $alreadyVoted = false;
    if ($previousData) {
      foreach ($previousData as $userId => $userData) {
        if (!$userData) {
          continue;
        }
        $disabledToEdit = true;
        if ($currenUserId == $userId) {
          $alreadyVoted = true;
          $disabledToEdit = false;
        }
        if ($isPollEditable == 0 || $expired) {
          $disabledToEdit = true;
        }
        $form['poll_table']['polls'][$userId]['poll_user'] = [
          '#type' => 'textfield',
          "#size" => 12,
          "#disabled" => $disabledToEdit,
          '#title_display' => 'invisible',
          "#required" => true,
          '#default_value' => $userData['poll_user'],
        ];

        foreach ($options as $option) {
          $key = "node" . $node->id() . "__" . self::createMachineName($option["value"]);
          $form['poll_table']['polls'][$userId][$key] = [
            '#type' => 'select',
            '#title_display' => 'invisible',
            "#required" => true,
            "#disabled" => $disabledToEdit,
            '#options' => [0 => "No", 1 => "yes", "0.5" => "Maybe"],
            '#default_value' => $userData[$key] ?? 0,
          ];
        }
      }
    }
    if (!$alreadyVoted && !$expired) {
      // new form
      $form['poll_table']['polls']['new_global_value']['poll_user'] = [
        '#type' => 'textfield',
        "#size" => 12,
        '#title_display' => 'invisible',
        "#required" => true,
        '#default_value' => $isPrivateForm ? $user->getAccountName() : "",
      ];

      foreach ($options as $option) {
        $key = "node" . $node->id() . "__" . self::createMachineName($option["value"]);
        $form['poll_table']['polls']['new_global_value'][$key] = [
          '#type' => 'select',
          '#title_display' => 'invisible',
          "#required" => true,
          '#options' => [0 => "No", 1 => "yes", "0.5" => "Maybe"],
          '#default_value' => 0,
        ];
      }

    }
    $form["#tree"] = true;
    hide($form["actions"]);
    if(!$isPollEditable == 0 && !$expired) {
      $form['poll_table']['submit'] = [
        '#type' => 'submit',
        '#value' => 'submit',
      ];
    }
    $config = $this->configFactory->getEditable('charts.settings');

    $chartSettings = $config->get('charts_default_settings');


    // Customize options here.
    $chartOptions = [
      'type' => $chartSettings['type'],
      'title' => '',
      'yaxis_title' => $this->t('Y-Axis'),
      'yaxis_min' => '',
      'yaxis_max' => '',
      'three_dimensional' => FALSE,
      'title_position' => 'out',
      'legend_position' => 'right',
      'data_labels' => $chartSettings['data_labels'],
      'tooltips' => $chartSettings['tooltips'],
      // 'grouping'   => TRUE,
      'colors' => $chartSettings['colors'],
      'min' => $chartSettings['min'],
      'max' => $chartSettings['max'],
      'yaxis_prefix' => $chartSettings['yaxis_prefix'],
      'yaxis_suffix' => $chartSettings['yaxis_suffix'],
      'data_markers' => $chartSettings['data_markers'],
      'red_from' => $chartSettings['red_from'],
      'red_to' => $chartSettings['red_to'],
      'yellow_from' => $chartSettings['yellow_from'],
      'yellow_to' => $chartSettings['yellow_to'],
      'green_from' => $chartSettings['green_from'],
      'green_to' => $chartSettings['green_to'],
    ];
    // Sample data format.
//    dump($previousData);
    $categories = [];
    foreach ($options as $option) {
      $categories[] = $option["value"];
    }


    // Creates a UUID for the chart ID.
    $chartId = 'chart-' . rand(3, 33333);
    if (isset($previousData) && $previousData) {
      $form['result'] = [
        '#type' => 'fieldset',
        '#title' => "Result : " . $node->getTitle(),
      ];
      $dataAgg = [];
      foreach ($previousData as $userId => $userData) {
        if (!$userData) {
          continue;
        }
        foreach ($userData as $key => $dataItem) {
          if ($key == "poll_user") {
            continue;
          }
          if (!isset($dataAgg[$key])) {
            $dataAgg[$key] = 0.0;
          }
          $dataAgg[$key] += $dataItem;
        }
      }
      $seriesData[] = [
        'name' => 'Series 1',
        'color' => '#0d233a',
        'type' => $chartSettings['type'],
        'data' => array_values($dataAgg),
      ];
      $form['result']["chart"] = [
        '#theme' => 'charts_api_example',
        '#library' => $chartSettings["library"],
        '#categories' => $categories,
        '#seriesData' => $seriesData,
        '#options' => $chartOptions,
        '#id' => $chartId,
        '#override' => [],
      ];
    }
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state)
  {
//    \Drupal::messenger()->addStatus(($form_state->getValue('poll_table')));
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    $node = \Drupal::routeMatch()->getParameter('node');
    $user = User::load(\Drupal::currentUser()->id());

    if (isset($form_state->getValue('poll_table')['polls'][$user->id()]) && $form_state->getValue('poll_table')['polls'][$user->id()] != null) {
      $values = $form_state->getValue('poll_table')['polls'][$user->id()];
    } else {
      $values = $form_state->getValue('poll_table')['polls']['new_global_value'];
    }
    $config = \Drupal::getContainer()->get('config.factory')->getEditable('poodle.polls');
    $previousValue = $config->get($node->id());
    $previousValueArray = $previousValue ? unserialize($previousValue) : null;
    $previousValueArray[$user->id()] = $values;
    $config->set($node->id(), serialize($previousValueArray));
    $config->save();
  }

  static private function createMachineName(string $value)
  {
    $new_value = strtolower($value);
    $new_value = preg_replace("/\s/", '_', $new_value);

    return preg_replace('/_+/', '_', $new_value);
  }
}


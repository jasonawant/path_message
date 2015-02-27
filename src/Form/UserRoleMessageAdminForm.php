<?php

/**
 * @file
 * Contains Drupal\user_role_message\Form\UserRoleMessageAdminForm
 */

namespace Drupal\user_role_message\Form;

use Drupal\Component\Plugin\Factory\FactoryInterface;
use Drupal\Component\Utility\String;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides and admin form for Path Message.
 */
class UserRoleMessageAdminForm extends ConfigFormBase {

  /**
   * The condition manager.
   *
   * @var \Drupal\Component\Plugin\Factory\FactoryInterface
   */
  protected $conditionManager;

  /**
   * The request path condition.
   *
   * @var \Drupal\user\Plugin\Condition\UserRole $condition
   */
  protected $condition;

  /**
   * Creates a new UserRoleMessageAdminForm.
   *
   * @param ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Component\Plugin\Factory\FactoryInterface $plugin_factory
   *   The condition plugin factory.
   */
  public function __construct(ConfigFactoryInterface $config_factory, FactoryInterface $plugin_factory) {
    parent::__construct($config_factory);
    $this->condition = $plugin_factory->createInstance('user_role');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('plugin.manager.condition')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'user_role_message_admin';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'user_role_message.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Load our default configuration.
    $config = $this->config('user_role_message.settings');
    $user_role_config = $config->get('user_roles');
    $user_role_config = (isset($user_role_config)) ? $user_role_config : array();
    // Set the default condition configuration.
    $this->condition->setConfiguration($user_role_config);

    $form['message'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Message'),
      '#description' => $this->t('Enter the message you want to appear'),
      '#default_value' => $config->get('message'),
    );

    // Build the configuration form.
    $form += $this->condition->buildConfigurationForm($form, $form_state);

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $this->condition->submitConfigurationForm($form, $form_state);
    $this->config('user_role_message.settings')
      ->set('message', String::checkPlain($form_state->getValue('message')))
      ->set('user_roles', $this->condition->getConfiguration())
      ->save();

    parent::submitForm($form, $form_state);
  }

}

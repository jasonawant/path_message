<?php

/**
 * @file
 * Contains Drupal\user_role_message\EventSubscriber\UserRoleMessageEventSubscriber
 */

namespace Drupal\user_role_message\EventSubscriber;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Plugin\Context\Context;
use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Executable\ExecutableManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Provides a response event subscriber for path messages.
 */
class UserRoleMessageEventSubscriber implements EventSubscriberInterface {

  /**
   * The path message config.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $config;

  /**
   * The condition manager.
   *
   * @var \Drupal\Core\Executable\ExecutableManagerInterface
   */
  protected $conditionManager;

  /**
   * Creates a new PathMessageEventSubscriber.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The current user.
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   *
   * @param \Drupal\Core\Executable\ExecutableManagerInterface $condition_manager
   *   The condition manager.
   */
  public function __construct(AccountInterface $account, EntityManagerInterface $entity_manager, ConfigFactoryInterface $config_factory, ExecutableManagerInterface $condition_manager) {
    $this->account = $account;
    $this->userStorage = $entity_manager->getStorage('user');
    $this->config = $config_factory->get('user_role_message.settings');
    $this->conditionManager = $condition_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::RESPONSE][] = array('setMessage', 1000);
    return $events;
  }

  /**
   * Sets a message for a matching path.
   */
  public function setMessage(FilterResponseEvent $event) {
    $user_role_config = $this->config->get('user_roles');
    $current_user = $this->userStorage->load($this->account->id());

    /* @var \Drupal\user\Plugin\Condition\UserRole $condition */
    $condition = $this->conditionManager->createInstance('user_role')
      ->setConfig('roles', $user_role_config['roles'])
      ->setConfig('negate',  $user_role_config['negate'])
      ->setContextValue('user', $current_user);

    if ($condition->isNegated()) {
      if (!$condition->evaluate()) {
        drupal_set_message($this->config->get('message'));
      }
    }
    else {
      if ($condition->evaluate()) {
        drupal_set_message($this->config->get('message'));
      }
    }

  }
}

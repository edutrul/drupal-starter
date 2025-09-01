<?php

namespace Drupal\server_og\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\node\NodeInterface;
use Drupal\og\Og;
use Symfony\Component\HttpFoundation\RedirectResponse;

final class JoinGroupController extends ControllerBase {

  public function join(NodeInterface $node): RedirectResponse {
    $account = $this->currentUser();

    // Must be logged in and node must be an OG group.
    if ($account->isAnonymous() || !Og::isGroup($node->getEntityTypeId(), $node->bundle())) {
      $this->messenger()->addError($this->t('Not allowed.'));
      return $this->redirect('<front>');
    }

    $membership_manager = \Drupal::service('og.membership_manager');
    $og_access = \Drupal::service('og.access');

    // Already a member?
    if ($membership_manager->isMember($node, $account)) {
      $this->messenger()->addStatus($this->t('You are already a member of this group.'));
      return $this->redirect('entity.node.canonical', ['node' => $node->id()]);
    }

    // Check OG permission to subscribe (keep simple).
    $allowed = $og_access->userAccess($node, 'subscribe', $account)->isAllowed()
      || $og_access->userAccess($node, 'create og membership', $account)->isAllowed();

    if (!$allowed) {
      $this->messenger()->addError($this->t('You are not allowed to subscribe to this group.'));
      return $this->redirect('entity.node.canonical', ['node' => $node->id()]);
    }

    // Load the full user entity (createMembership requires UserInterface).
    $user_storage = $this->entityTypeManager()->getStorage('user');
    $user = $user_storage->load($account->id());
    if (!$user) {
      $this->messenger()->addError($this->t('User not found.'));
      return $this->redirect('entity.node.canonical', ['node' => $node->id()]);
    }

    // Create active membership (fine for a test site).
    $membership = $membership_manager->createMembership($node, $user);
    $membership->save();

    $this->messenger()->addStatus($this->t('Welcome! You have subscribed to %label.', ['%label' => $node->label()]));

    // Redirect to destination or back to the group.
    $destination = $this->getRequest()->query->get('destination');
    if ($destination) {
      return $this->redirectUrl(Url::fromUserInput($destination));
    }
    return $this->redirect('entity.node.canonical', ['node' => $node->id()]);
  }

}

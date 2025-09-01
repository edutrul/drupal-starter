<?php

namespace Drupal\server_og\Plugin\EntityViewBuilder;

use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\node\NodeInterface;
use Drupal\og\Og;
use Drupal\pluggable_entity_view_builder\EntityViewBuilderPluginAbstract;
use Drupal\pluggable_entity_view_builder_example\ProcessedTextBuilderTrait;

/**
 * The "Node Group" plugin.
 *
 * @EntityViewBuilder(
 *   id = "node.group",
 *   label = @Translation("Node - Group"),
 *   description = "Node view builder for Group bundle."
 * )
 */
final class NodeGroup extends EntityViewBuilderPluginAbstract {

  use ProcessedTextBuilderTrait;

  /**
   * Build full view mode.
   */
  public function buildFull(array $build, NodeInterface $entity) {
    // Always render the processed body (soft gate).
    $build[] = $this->buildProcessedText($entity);

    // Only bother if this is an OG group and the user is logged in.
    $current_user = \Drupal::currentUser();
    if (!$current_user->isAuthenticated()) {
      return $build;
    }
    if (!Og::isGroup($entity->getEntityTypeId(), $entity->bundle())) {
      return $build;
    }

    // If already a member, nothing to show.
    $membership_manager = \Drupal::service('og.membership_manager');
    if ($membership_manager->isMember($entity, $current_user)) {
      return $build;
    }

    // Check OG access to subscribe/join.
    $og_access = \Drupal::service('og.access');
    $can_subscribe =
      $og_access->userAccess($entity, 'subscribe', $current_user)->isAllowed()
      || $og_access->userAccess($entity, 'create og membership', $current_user)->isAllowed();

    if (!$can_subscribe) {
      return $build;
    }

    // Build the "join" link with destination back to this page.
    $join_url = Url::fromRoute('server_og.group_join', [
      'node' => $entity->id(),
    ], [
      'query' => ['destination' => \Drupal::service('path.current')->getPath()],
    ]);

    // Prompt: "Hi {{ name }}, click here if you would like to subscribe to this group called {{ label }}."
    $build['og_subscribe_prompt'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['og-subscribe-prompt']],
      'greeting' => [
        '#markup' => $this->t('Hi @name, ', ['@name' => $current_user->getDisplayName()]),
      ],
      'link' => Link::fromTextAndUrl(
        $this->t('click here if you would like to subscribe to this group called @label', ['@label' => $entity->label()]),
        $join_url
      )->toRenderable(),
      '#weight' => -1000, // Show above body.
      '#cache' => [
        // Keep it simple: vary by user so the prompt hides after join.
        'contexts' => ['user'],
      ],
    ];

    return $build;
  }

}

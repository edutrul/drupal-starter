<?php

namespace Drupal\server_group\Plugin\EntityViewBuilder;

use Drupal\Core\Url;
use Drupal\node\NodeInterface;
use Drupal\og\Og;
use Drupal\pluggable_entity_view_builder\EntityViewBuilderPluginAbstract;

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

  /**
   * Build full view mode.
   */
  public function buildFull(array $build, NodeInterface $entity): array {

    $current_user = \Drupal::currentUser();

    // Only for OG groups.
    if (!Og::isGroup($entity->getEntityTypeId(), $entity->bundle())) {
      // Not a group: render normally.
      // @todo: implement methods for rendering the rest of the fields.
      $build[] = ['#markup' => $entity->label()];
      return $build;
    }

    // Anonymous: only invite to subscribe (no body).
    if ($current_user->isAnonymous()) {
      $build['server_group_subscribe_prompt'] = [
        '#markup' => $this->t('You must be an authenticated user and be in this group to view the content.'),
        '#cache' => [
          'contexts' => ['user'],
          'tags' => $entity->getCacheTags(),
        ],
      ];
      return $build;
    }

    $membership_manager = \Drupal::service('og.membership_manager');
    $is_member = $membership_manager->isMember($entity, $current_user);

    if (!$is_member) {
      // Build subscribe prompt.
      $join_url = Url::fromRoute('server_group.group_join', [
        'node' => $entity->id(),
      ], ['query' => ['destination' => \Drupal::service('path.current')->getPath()]]);

      $build['server_group_subscribe_prompt'] = [
        '#theme'  => 'server_group_subscribe_prompt',
        '#name'   => $current_user->getDisplayName(),
        '#label'  => $entity->label(),
        '#url'    => $join_url->toString(),
        '#cache'  => ['contexts' => ['user'], 'tags' => $entity->getCacheTags()],
      ];

    }
    return $build;
  }

}

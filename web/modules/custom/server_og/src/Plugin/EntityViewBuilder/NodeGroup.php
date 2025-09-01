<?php

namespace Drupal\server_og\Plugin\EntityViewBuilder;

use Drupal\media\MediaInterface;
use Drupal\node\NodeInterface;
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
class NodeGroup extends EntityViewBuilderPluginAbstract {

  /**
   * Build full view mode.
   *
   * @param array $build
   *   The existing build.
   * @param \Drupal\node\NodeInterface $entity
   *   The entity.
   *
   * @return array
   *   Render array.
   */
  public function buildFull(array $build, NodeInterface $entity) {

    $this->messenger()->addMessage('Add your Node News elements in \Drupal\server_general\Plugin\EntityViewBuilder\NodeNews');

    // The node's label.
    $node_type = $this->entityTypeManager->getStorage('node_type')->load($entity->bundle());
    $label = $node_type->label();

    // The hero responsive image.
    $medias = $entity->get('field_featured_image')->referencedEntities();
    $image = $this->buildEntities($medias, 'hero');

    $element = $this->buildElementNodeNews(
      $entity->label(),
      $label,
      $this->getFieldOrCreatedTimestamp($entity, 'field_publish_date'),
      $image,
      $this->buildProcessedText($entity),
      $this->buildTags($entity),
      $this->buildSocialShare($entity),
    );

    $build[] = $element;

    return $build;
  }

}
<?php

namespace Drupal\server_og\Plugin\EntityViewBuilder;

use Drupal\node\NodeInterface;
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
class NodeGroup extends EntityViewBuilderPluginAbstract {

  use ProcessedTextBuilderTrait;

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

    $this->messenger()->addMessage('Add your Node Organic Group elements in \Drupal\server_og\Plugin\EntityViewBuilder\NodeGroup');

    // Body.
    $build[] = $this->buildProcessedText($entity);

    return $build;
  }

}

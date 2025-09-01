<?php

namespace Drupal\server_person\Plugin\EntityViewBuilder;

use Drupal\paragraphs\ParagraphInterface;
use Drupal\pluggable_entity_view_builder\EntityViewBuilderPluginAbstract;
use Drupal\pluggable_entity_view_builder_example\ProcessedTextBuilderTrait;
use Drupal\pluggable_entity_view_builder_example\TagBuilderTrait;

/**
 * The Person Card paragraph plugin.
 *
 * @EntityViewBuilder(
 *   id = "paragraph.person_card",
 *   label = @Translation("Paragraph - Person Card"),
 *   description = "Paragraph view builder for 'Person Card' bundle."
 * )
 */
class ParagraphPerson extends EntityViewBuilderPluginAbstract {

  use ProcessedTextBuilderTrait;
  use TagBuilderTrait;

  /**
   * Build full view mode.
   *
   * @param array $build
   *   The existing build.
   * @param \Drupal\paragraphs\ParagraphInterface $entity
   *   The entity.
   *
   * @return array
   *   Render array.
   */
  public function buildFull(array $build, ParagraphInterface $entity): array {
    $element = [];
    $element['#theme'] = 'server_person_card';
    $element['#title'] = $this->getTextFieldValue($entity, 'field_title');
    $element['#subtitle'] = $this->getTextFieldValue($entity, 'field_subtitle');
    $element['#role'] = $this->getTextFieldValue($entity, 'field_role');
    $image = $this->getMediaImageAndAlt($entity, 'field_image', 'thumbnail');
    $element['#image'] = $image['url'];
    $element['#image_alt'] = $image['alt'];

    $build[] = $element;

    return $build;
  }

}

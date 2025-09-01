<?php

namespace Drupal\server_person\Plugin\EntityViewBuilder;

use Drupal\paragraphs\ParagraphInterface;
use Drupal\pluggable_entity_view_builder\EntityViewBuilderPluginAbstract;
use Drupal\server_general\ProcessedTextBuilderTrait;
use Drupal\server_general\ThemeTrait\ElementWrapThemeTrait;
use Drupal\server_general\ThemeTrait\PeopleTeasersThemeTrait;

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
  use ElementWrapThemeTrait;
  use PeopleTeasersThemeTrait;

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
    $name = $this->getTextFieldValue($entity, 'field_title');

    $image = $this->getMediaImageAndAlt($entity, 'field_image');
    $image_url = !empty($image['url']) ? $image['url'] : '';
    $image_alt = !empty($image['alt']) ? $image['alt'] : $name;

    $element = $this->buildElementPersonTeaser(
      $image_url,
      $image_alt,
      $name,
      $this->getTextFieldValue($entity, 'field_subtitle'),
    );

    $build[] = $element;

    return $build;
  }

}

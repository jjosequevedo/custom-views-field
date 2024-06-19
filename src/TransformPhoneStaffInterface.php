<?php

namespace Drupal\custom_views_field;

use Drupal\node\NodeInterface;

/**
 * Interface for transforming phone staff.
 */
interface TransformPhoneStaffInterface {

  /**
   * Displays the view of the phone staff entity.
   *
   * @param \Drupal\node\NodeInterface $entity
   *   The entity to display.
   * @param string $field_name
   *   The name of the field.
   *
   * @return array|string|null
   *   The transformed view as a string or NULL if empty.
   */
  public function view(NodeInterface $entity, string $field_name): array|string|null;

  /**
   * Retrieves the value of the phone staff entity.
   *
   * @param \Drupal\node\NodeInterface $entity
   *   The entity to retrieve the value from.
   * @param string $field_name
   *   The name of the field.
   * @param bool $secondary
   *   Flag indicating if it's a secondary value (default: FALSE).
   *
   * @return array|string
   *   The value of the phone staff entity.
   */
  public function getValue(NodeInterface $entity, string $field_name, bool $secondary = FALSE): array|string;

}

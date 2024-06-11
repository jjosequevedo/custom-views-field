<?php

namespace Drupal\chop_custom_views_field;

use Drupal\node\NodeInterface;
use Drupal\paragraphs\ParagraphInterface;

/**
 * Service to transform phone staff field.
 */
class TransformPhoneStaff implements TransformPhoneStaffInterface {

  /**
   * Transforms the phone staff.
   *
   * @param \Drupal\node\NodeInterface $entity
   *   The node entity.
   * @param string $field_name
   *   The name of the field.
   * @param string $action
   *   The action to perform.
   * @param bool $secondary
   *   Flag indicating if it's a secondary transformation.
   *
   * @return array|string
   *   The transformed entity or field value.
   */
  protected function transform(NodeInterface $entity, string $field_name, string $action, bool $secondary): array|string {
    if (
      $entity instanceof NodeInterface &&
      $entity->hasField($field_name) &&
      !$entity->get($field_name)->isEmpty()
    ) {
      $allowed_values = $entity->getFieldDefinition($field_name)
        ->getFieldStorageDefinition()
        ->getSetting('allowed_values');
      $value = $entity->get($field_name)->value;
      $key = array_key_exists($value, $allowed_values) ? $value : 'other';

      switch ($key) {
        case 'default':
          $exploded = explode(':', $allowed_values[$key]);
          if (array_key_exists(0, $exploded) && array_key_exists(1, $exploded)) {
            return $action == 'view' ? [
              '#type' => 'markup',
              '#markup' => "<div>{$exploded[0]}</div><div>{$exploded[1]}</div>",

            ] : $exploded[1];
          }
          return '';

        case 'other':
          return $action == 'view' ? $entity->get($field_name)->view(['label' => 'hidden']) : $entity->get($field_name)->value;

        case 'org':
          if (
            $entity->hasField('field_org_entity_position') &&
            !$entity->get('field_org_entity_position')->isEmpty()
          ) {
            // It returns an org_entity or a programs_initiatives content
            // type node.
            $referenced_entity = $entity
              ->get('field_org_entity_position')->entity
              ->get('field_org_entity')->entity;

            if ($referenced_entity instanceof NodeInterface) {
              return $this->getEntityPhone($referenced_entity, $action, $secondary);
            }
          }
          break;

        case 'pc':
          if (
            $entity->hasField('field_team_location') &&
            !$entity->get('field_team_location')->isEmpty()
          ) {
            $team_location = $entity->get('field_team_location')->entity;
            if (
              $team_location instanceof ParagraphInterface &&
              $team_location->hasField('field_location_team') &&
              !$team_location->get('field_location_team')->isEmpty()
            ) {
              // It returns a location content type node.
              $referenced_entity = $entity
                ->get('field_team_location')->entity
                ->get('field_location_team')->entity;

              if ($referenced_entity instanceof NodeInterface) {
                return $this->getEntityPhone($referenced_entity, $action, $secondary);
              }
            }
          }
          break;

        default:
          return '';
      }
    }
    return '';
  }

  /**
   * Get node entity's phone numbers data.
   */
  private function getEntityPhone(NodeInterface $entity, string $action, bool $secondary) {
    $bundle = $entity->bundle();

    switch ($bundle) {
      case 'org_entity':
        if (
          $entity->hasField('field_phone_contact') &&
          !$entity->get('field_phone_contact')->isEmpty()
        ) {
          $field_phone_contact = $entity->get('field_phone_contact');

          if ($action == 'view') {
            return $field_phone_contact->view('default');
          }

          $phone_contacts = $field_phone_contact->getValue();

          return count($phone_contacts) >= 2 && $secondary
            ? $phone_contacts[1]['value']
            : $field_phone_contact->value;
        }

        break;

      case 'location':
      case 'programs_initiatives':
        $has_contact_phone = $entity->hasField('field_contact_information')
          && !$entity->get('field_contact_information')->isEmpty();

        $has_secondary_phone = $entity->hasField('field_secondary_phone')
          && !$entity->get('field_secondary_phone')->isEmpty();

        if ($action == 'view') {
          $render = [];

          if ($has_contact_phone) {
            $render[] = $entity->get('field_contact_information')->view('meet_your_team_hero');
          }

          if ($has_secondary_phone) {
            $rendered_field = $entity->get('field_secondary_phone')->view('meet_your_team_hero');

            $render[] = $rendered_field;
          }

          return $render;
        }
        else {
          if ($has_contact_phone) {
            return $entity->get('field_contact_information')->value;
          }

          if ($secondary && $has_secondary_phone) {
            return $entity->get('field_secondary_phone')->value;
          }
        }

        break;
    }

    // Return an empty string if non of the above conditions are met.
    return '';
  }

  /**
   * {@inheritdoc}
   */
  public function view(NodeInterface $entity, string $field_name): array|string|null {
    $data = $this->transform($entity, $field_name, 'view', FALSE);
    return $data !== '' ? $data : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getValue(NodeInterface $entity, string $field_name, bool $secondary = FALSE): array|string {
    return $this->transform($entity, $field_name, 'value', $secondary);
  }

}

<?php

namespace Drupal\chop_custom_views_field\Plugin\views\field;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * A handler to provide a custom field.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("custom_field_conditional_phone")
 */
class ConditionalPhone extends FieldPluginBase {

  /**
   * The transform phone staff service.
   *
   * @var \Drupal\chop_custom_views_field\TransformPhoneStaffInterface
   */
  protected $transformPhoneStaff;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $instance->transformPhoneStaff = $container->get('chop_custom_views_field.transform_phone_staff');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['is_secondary'] = ['default' => FALSE];
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    $form['is_secondary'] = [
      '#title' => $this->t('Is secondary phone?'),
      '#description' => $this->t("Enable to display the secondary phone."),
      '#type' => 'checkbox',
      '#default_value' => !empty($this->options['is_secondary']),
    ];
    parent::buildOptionsForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    // This function can be left empty if no query alterations are needed.
  }

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    /** @var \Drupal\node\NodeInterface $entity */
    $entity = $this->getEntity($values);
    return $this->transformPhoneStaff->getValue($entity, 'field_staff_phone', !empty($this->options['is_secondary']));
  }

}

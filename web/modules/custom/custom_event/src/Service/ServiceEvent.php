<?php

namespace Drupal\custom_event\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Provide service of event.
 *
 * @package Drupal\custom_event
 */
class ServiceEvent {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritDoc}
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * Get events.
   *
   * @param string $type_id
   * @param string $date
   * @param int $range_length
   * @param boolean $notin
   * @param boolean $to_unpublish
   * @return array
   */
  public function getEvents ($type_id = NULL, $date = NULL, $range_length = NULL, $notin = FALSE, $to_unpublish = FALSE) {
    $query = $this->entityTypeManager->getStorage('node')->getQuery();
    $query->condition('type', 'event')
      ->accessCheck(TRUE)
      ->sort('field_date_range.value', 'ASC')
      ->condition('status', 1);

      if (NULL != $range_length) {
        $query->range(0, $range_length);
      }

      if (NULL != $type_id) {
        if ($notin) {
          $query->condition('field_event_type', $type_id, '<>');
        }
        else {
          $query->condition('field_event_type', $type_id);
        }
      }

      if (NULL != $date) {
        if ($to_unpublish) {
          $query->condition('field_date_range.end_value', $date, '<');
        }
        else {
          $query->condition('field_date_range.end_value', $date, '>');
        }

      }

      return $query->execute();
  }

}
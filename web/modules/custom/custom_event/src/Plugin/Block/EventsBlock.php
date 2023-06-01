<?php

namespace Drupal\custom_event\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\custom_event\Service\ServiceEvent;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;

/**
 * Provides a 'Events' Block.
 *
 * @Block(
 *   id = "events_block",
 *   admin_label = @Translation("Events block"),
 *   category = @Translation("Custom block"),
 * )
 */
class EventsBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $currentRouteMatch;

  /**
   * Path alias manager.
   *
   * @var \Drupal\custom_event\Service\ServiceEvent
   */
  protected $serviceEvent;

  /**
   * Constructs a Drupalist object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param \Drupal\Core\Routing\RouteMatchInterface $current_route_match
   *   The current route match service.
   * @param \Drupal\Core\Database\Connection $connection
   *   Database connection.
   * @param \Drupal\custom_event\Service\ServiceEvent $service_event
   *   Path alias manager.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    EntityTypeManagerInterface $entity_type_manager,
    RouteMatchInterface $current_route_match,
    ServiceEvent $service_event
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
    $this->currentRouteMatch = $current_route_match;
    $this->serviceEvent = $service_event;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('current_route_match'),
      $container->get('custom_event.service_event')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {

    $node = $this->currentRouteMatch->getParameter('node');
    if ($node instanceof \Drupal\node\NodeInterface) {
      $event_type_id = $node->field_event_type->referencedEntities()[0]->id();
      // Current date white datetime storage format.
      $now = new DrupalDateTime();
      $now->setTimezone(new \DateTimeZone(DateTimeItemInterface::STORAGE_TIMEZONE));
      $now = $now->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT);

      $events_id = $this->serviceEvent->getEvents($event_type_id, $now, 3);

      if (count($events_id) < 3) {
        $add_events_id = $this->serviceEvent->getEvents($event_type_id, $now, 3 - count($events_id), TRUE);
        $events_id = array_merge($events_id, $add_events_id);
      }
      $events = $this->entityTypeManager->getStorage('node')->loadMultiple($events_id);
      $view_builder = $this->entityTypeManager->getViewBuilder('node');

      foreach($events as $event) {
        $render_events[] = $view_builder->view($event, 'teaser');
      }
    }
    return [
      '#theme' => 'events_liste',
      '#events' => $render_events,
    ];
  }

  public function getCacheTags() {
    return Cache::mergeTags(parent::getCacheTags(), ['node_list:event']);
  }


}
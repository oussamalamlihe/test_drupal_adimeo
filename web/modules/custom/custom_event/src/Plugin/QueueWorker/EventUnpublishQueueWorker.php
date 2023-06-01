<?php
/**
 * @file
 * Contains Drupal\custom_event\Plugin\QueueWorker\EventUnpublishQueueWorker.php
 */

 namespace Drupal\custom_event\Plugin\QueueWorker;

 use Drupal\Core\Entity\EntityStorageInterface;
 use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
 use Drupal\Core\Queue\QueueWorkerBase;
 use Drupal\node\NodeInterface;
 use Symfony\Component\DependencyInjection\ContainerInterface;


 /**
  * Provides base functionality for the EventUnpublish Queue Workers.
  * @QueueWorker(
  *   id = "event_unpublish_queue",
  *   title = @Translation("Unpublish Envents"),
  *   cron = {"time" = 10}
  * )
  */
 class EventUnpublishQueueWorker extends QueueWorkerBase implements ContainerFactoryPluginInterface {

   /**
    * The node storage.
    *
    * @var \Drupal\Core\Entity\EntityStorageInterface
    */
   protected $nodeStorage;

   /**
    * Creates a new NodePublishBase object.
    *
    * @param \Drupal\Core\Entity\EntityStorageInterface $node_storage
    *   The node storage.
    */
   public function __construct(EntityStorageInterface $node_storage) {
     $this->nodeStorage = $node_storage;
   }

   /**
    * {@inheritdoc}
    */
   public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
     return new static(
       $container->get('entity_type.manager')->getStorage('node')
     );
   }

   /**
    * Unpublishes a node event.
    *
    * @param NodeInterface $node
    * @return int
    */
   protected function unpublishEven($node) {
     $node->setUnpublished();
     return $node->save();
   }

   /**
    * {@inheritdoc}
    */
   public function processItem($data) {
    if (isset($data) && is_string($data)){
        /** @var NodeInterface $node */
        $node = $this->nodeStorage->load($data);
        if ($node->isPublished() && $node instanceof NodeInterface) {
          return $this->unpublishEven($node);
        }
      }
    }

 }
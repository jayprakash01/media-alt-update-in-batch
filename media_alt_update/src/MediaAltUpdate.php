<?php

namespace Drupal\media_alt_update;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Manages media alt update.
 */
class MediaAltUpdate {

  use StringTranslationTrait;

  /**
   * The database service.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityManager;

  /**
   * The messenger.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Constructs a EntityReferenceRevisionsOrphanManager object.
   *
   * @param \Drupal\Core\Database\Connection $database
   *   The database service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_manager
   *   The entity manager service.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   */
  public function __construct(Connection $database, EntityTypeManagerInterface $entity_manager, MessengerInterface $messenger) {
    $this->database = $database;
    $this->entityManager = $entity_manager;
    $this->messenger = $messenger;
  }

  /**
   * Batch operation for updating alt text for media.
   *
   * @param array $items
   *   The result of media.
   * @param Iterable|array $context
   *   The context array.
   */
  public function batchUpdateMediaItem(array $items, &$context) {
    if (empty($items['mid'])) {
      return;
    }
    $media = $this->entityManager->getStorage('media')->load($items['mid']);
    $media->field_media_image = [
      'target_id' => $items['target_id'],
      'alt' => $items['name'],
      'title' => $items['name'],
    ];

    $media->save();

    $context['message'] = 'Updating Media Items...';
    $context['results'][] = $items;

  }

  /**
   * Batch dispatch submission finished callback.
   */
  public static function batchSubmitFinished($success, $results, $operations) {
    return \Drupal::service('media_alt_update.alt_update')->doBatchSubmitFinished($success, $results, $operations);
  }

  /**
   * Sets a batch for executing updating alt text of the media entity.
   */
  public function setBatch() {
    $query = $this->database->select('media_field_data', 'media');
    $query->innerJoin('media__field_media_image', 'media_image', 'media.mid = media_image.entity_id');
    $query->fields('media', ['mid', 'name']);
    $query->fields('media_image', ['field_media_image_target_id']);
    $query->condition('media.bundle', 'image');
    $query->condition('field_media_image_alt', '', '=');

    $results = $query->execute()->fetchAll();

    // Add the operations to update the media alt text.
    $operations = [];
    $items = [];
    foreach ($results as $result) {
      $items = [
        'mid' => $result->mid,
        'target_id' => $result->field_media_image_target_id,
        'name' => $result->name,
      ];

      $operations[] = ['_alt_media_batch_dispatcher',
        [
          'media_alt_update.alt_update:batchUpdateMediaItem',
          $items,
        ],
      ];
    }

    $batch = [
      'title' => $this->t('Updating alt Text of media Entity'),
      'operations' => $operations,
      'init_message' => $this->t('Updating...'),
      'progress_message' => $this->t('Processed @current out of @total.'),
      'error_message' => $this->t('An error occurred during processing'),
      'finished' => [MediaAltUpdate::class, 'batchSubmitFinished'],
    ];

    batch_set($batch);
  }

  /**
   * Finished callback for the batch process.
   *
   * @param bool $success
   *   Whether the batch completed successfully.
   * @param array $results
   *   The results array.
   * @param array $operations
   *   The operations array.
   */
  public function doBatchSubmitFinished($success, array $results, array $operations) {
    if ($success) {
      $message = \Drupal::translation()->formatPlural(
        count($results),
        'One item processed.', '@count items processed.'
      );
    }
    else {
      $message = $this->t('Finished with an error.');
    }
    $this->messenger->addStatus($message);
  }

}

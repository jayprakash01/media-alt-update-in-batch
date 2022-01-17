<?php

namespace Drupal\media_alt_update\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\media_alt_update\MediaAltUpdate;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a form that allows admin users to update alt text of media.
 */
class MediaAltUpdateForm extends FormBase {

  /**
   * The media alt update service.
   *
   * @var \Drupal\media_alt_update\MediaAltUpdate
   */
  protected $altUpdate;

  /**
   * MediaAltUpdateForm constructor.
   *
   * @param \Drupal\media_alt_update\MediaAltUpdate $alt_update
   *   The media alt update.
   */
  public function __construct(MediaAltUpdate $alt_update) {
    $this->altUpdate = $alt_update;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('media_alt_update.alt_update')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'media_alt_update_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['media_label'] = [
      '#type' => 'markup',
      '#markup' => $this->t('Please click on button to update the Alt text of image for the Media entity.
      This process updates all empty Alt text of the image by name.'),
      '#weight' => 1,
    ];
    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Update'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Validation if require.
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->altUpdate->setBatch();
  }

}

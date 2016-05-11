<?php

/**
* Provides a 'SignUp' Block
*
* @Block(
*   id = "cr_signup_block",
*   admin_label = @Translation("SignUp block"),
* )
*/

namespace Drupal\cr_signup\Plugin\Block;

use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Block\BlockBase;

class SignUpBlock extends BlockBase implements BlockPluginInterface
{
    /**
    * {@inheritdoc}
    */
    public function build()
    {
        $build = array();

        $build['#markup'] = '' . t('Sign Up') . '';
        $build['form'] = \Drupal::formBuilder()->getForm('Drupal\cr_signup\Form\SignUpForm');

        return $build;
    }
    /**
     * {@inheritdoc}
     */
    public function blockForm($form, FormStateInterface $form_state)
    {
        $form = parent::blockForm($form, $form_state);

        $config = $this->getConfiguration();

        $form['cr_signup_queue_name'] = array (
            '#type' => 'textfield',
            '#title' => $this->t('RabbitMQ Queue Name'),
            '#description' => $this->t('Which RabbitMQ Queue should receive the email address?'),
            '#default_value' => isset($config['name']) ? $config['name'] : ''
        );

        return $form;
    }
}
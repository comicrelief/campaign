<?php
/**
 * Drupal 8 Email SignUp Paragraph Form
 * @package Comic Relief
 * @file
 */
namespace Drupal\cr_paragraph_email_signup\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;

/**
 * Concrete implementation of paragraph email form
 *
 * @author Tom Staunton <t.staunton@comicrelief.com>
 */
class SignUpForm extends FormBase
{

    /**
     * Just return the unique form reference
     *
     * @return string
     */
    public function getFormId()
    {
        return "cr_paragraph_email_signup";
    }

    /**
     * Build up the form for display
     *
     * @param array              $form
     * @param FormStateInterface $form_state
     *
     * @return array
     */
    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $form['email_address'] = array(
            '#type' => 'email',
            '#title' => $this ->t('Your email address'),
        );

        $form['actions']['#type'] = 'actions';
        $form['actions']['submit'] = array(
            '#type' => 'submit',
            '#value' => $this ->t('Save'),
            '#button_type' => 'primary',
        );

        return $form;
    }

    /**
     * Not implemented/Just return.
     *
     * @param array              $form
     * @param FormStateInterface $form_state
     *
     * @return bool
     */
    public function validateForm(array &$form, FormStateInterface $form_state)
    {
        return true;
    }

    /**
     * Attempt to submit the Form/email address to
     * RabbitMQ Queue.
     *
     * @param array              $form
     * @param FormStateInterface $form_state
     * @return bool
     * @throws \Exception
     */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        $emailAddress = $form_state ->getValue(['email_address']);
        $queueName = $this
            ->config("cr_paragraph_email_signup.settings")
            ->get("queue_name");

        $queueHandle =\Drupal::service("queue") ->get($queueName);

        if (false === $queueHandle ->createItem($emailAddress)) {
            throw new \Exception("Unable to queue email address");
        }

        drupal_set_message(
            $this ->t(
                "Thank you. Your email address (@email) has been received",
                [
                    '@email' => $emailAddress,
                ]
            )
        );

        return true;
    }
}
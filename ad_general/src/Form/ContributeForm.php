<?php
/**
 * @file
 * Contains \Drupal\ad_general\Form\ContributeForm.
 */

namespace Drupal\ad_general\Form;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;


/**
 * Contribute form. Contains functions getFormId, buildForm, submitForm, validateForm, and simpleAjaxFormCallback.
 */
class ContributeForm extends FormBase {
    /**
     * {@inheritdoc}
     */

    public function getFormId() {
        return 'ad_general_block_form';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state) {
        // Don't cache this form
        // Create a containing element for the form items
        // This is where the magic happens.
        $form['submit_wrapper'] = [
            '#type' => 'container',
            '#attributes' => ['id' => 'simple-ajax-form-wrapper'],
        ];
        // The form elements are defined within the container
        // When the form is submitted the ajax submit handler will
        // replace the contents
        $form['submit_wrapper']['hello'] = array(
            '#type' => 'html_tag',
            '#tag' => 'p',
            '#value' => t('Send event to a friend'),
        );
        $form['submit_wrapper']['name'] = array(
            '#type' => 'textfield',
            '#title' => $this->t('Name'),
            '#description' => $this->t('Enter your name'),
            '#maxlength' => 64,
            '#size' => 64,
        );
        $form['submit_wrapper']['email'] = array(
            '#type' => 'email',
            '#title' => t("Friend's e-mail"),
            '#required' => TRUE,
            '#description' => $this->t('Enter your friends email'),
        );
        $form['submit_wrapper']['actions']['#type'] = 'actions';
        $form['submit_wrapper']['actions']['submit'] = array(
            '#type' => 'submit',
            '#value' => $this->t('Submit'),
            '#name' => 'ajax_submit',
            '#button_type' => 'primary',
            '#ajax' => [
                'callback' => '::simpleAjaxFormCallBack',
                'wrapper' => 'simple-ajax-form-wrapper',
                'method' => 'replace',
                'effect' => 'fade',
            ],
        );

        return $form;
    }
    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state) {
        $form['submit_wrapper']['actions']['submit']['#submit'][] = 'ad_general_mail'; //custom name

        // Remove the form elements that are no longer required.

        $userInput = $form_state->getUserInput();
        $keys = $form_state->getCleanValueKeys();

        $newInputArray = [];
        foreach ($keys as $key) {
            if ($key == "")  continue;
            $newInputArray[$key] = $userInput[$key];
        }

        $form_state->setUserInput($newInputArray);
        $form_state->setRebuild(true);
        drupal_set_message('Mail has been sent.', 'status');
    }

    public function validateForm(array &$form, FormStateInterface $form_state) {
        if(!\Drupal::service('email.validator')->isValid($form_state->getValue('email')) || empty($form_state->getValue('email')) || strpos(($form_state->getValue('email')),'no-reply'  )){
            $form_state->setErrorByName('email', t('Enter a valid email address.'));
        }
    }

    public function simpleAjaxFormCallback(array &$form, FormStateInterface $form_state) {
        $node = \Drupal::routeMatch()->getParameter('node'); //get node

        $nid = $node->nid->value; //get node id
        $title = $node->title->value; // get node title value

        $organizer_id = $node->field_organizer->getValue(); //get company name

        $query = \Drupal::database()->select('node_field_data', 'n');
        $query->condition('n.nid', $organizer_id[0]['target_id']);
        $query->addField('n', 'title');
        $result = $query->execute()->fetchField();

        $userEmail = $form_state->getValue('email');
        $params['email'] = $userEmail;
        $params['name'] =  $form_state->getValue('name');
        $params['organizer'] = $result;
        $params['event_name'] = $title;

        $current_path = \Drupal::service('path.current')->getPath();
        $host = \Drupal::request()->getHost();
        $result = \Drupal::service('path.alias_manager')->getAliasByPath($current_path);

        $params['event_link'] = $this->t($host.$result);

        $newMail = \Drupal::service('plugin.manager.mail');
        $newMail->mail('ad_general', 'registerMail', $userEmail, 'en', $params, $reply = NULL, $send = TRUE);

        return $form;
    }

}

?>
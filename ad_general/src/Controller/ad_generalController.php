<?php

/**
 * @file
 * @author Your Name
 * Contains \Drupal\ad_general\Controller\ad_generalController.
 */

namespace Drupal\ad_general\Controller;

/**
 * Provides route responses for the Test module.
 */
class ad_generalController {

    /**
     * Returns a simple page.
     *
     * @return array
     *   A simple renderable array.
     */
    public function content() {
        $build = array(
            '#type' => 'markup',
            '#markup' => t('Hello World!'),
        );
        return $build;
    }
}


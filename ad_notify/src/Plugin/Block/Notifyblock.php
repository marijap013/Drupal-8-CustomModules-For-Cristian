<?php

namespace Drupal\ad_notify\Plugin\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Url;
use Drupal\Core\GeneratedLink;


/**
 * Provides a 'Custom' Block
 *
 * @Block(
 *   id = "notify_block",
 *   admin_label = @Translation("Notify Block"),
 * )
 */

class NotifyBlock extends BlockBase{
    /**
     * {@inheritdoc}
     */
    public function build() {
        return array(
            '#markup' => $this->get_notifications(),
            '#theme' => 'notify_block',
        );
    }
    function get_notifications(){

        $account = \Drupal::currentUser();
        $query = \Drupal::database()->select('user__field_event_type', 'ufet' );
        $query->condition('ufet.entity_id', $account->id(), '=' );
        $query->innerJoin('node__field_event_type', 'nfet',
            'ufet.field_event_type_target_id = nfet.field_event_type_target_id');
        $query->innerJoin('node_field_data', 'n',
            'n.nid = nfet.entity_id');
        $query->addField('n', 'nid' );
        $query->addField('n', 'title' );
        $nodes = $query->execute()->fetchAll();

//        $links = array();
        $links = '';
        foreach ($nodes as $n) {
            // Each $n is an object.
            $url = Url::fromRoute('entity.node.canonical',['node' => $n->nid]);
            $generatedLink = \Drupal::l(t($n->title), $url);
            $links.='<span>'.$generatedLink->getGeneratedLink().'</span>';
        }
        return $links;
    }

}


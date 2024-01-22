<?php

namespace ImportWP\Pro\Importer;

use ImportWP\Pro\Importer\Template\AttachmentTemplate;
use ImportWP\Pro\Importer\Template\CommentTemplate;
use ImportWP\Pro\Importer\Template\CustomPostTypeTemplate;
use ImportWP\Pro\Importer\Template\PageTemplate;
use ImportWP\Pro\Importer\Template\PostTemplate;
use ImportWP\Pro\Importer\Template\TermTemplate;
use ImportWP\Pro\Importer\Template\UserTemplate;

class ImporterManager extends \ImportWP\Common\Importer\ImporterManager
{
    /**
     * Inject Pro Templates
     *
     * @return void
     */
    public function get_templates()
    {
        $templates = parent::get_templates();
        $templates = array_merge($templates, [
            'post' => PostTemplate::class,
            'page' => PageTemplate::class,
            'user' => UserTemplate::class,
            'term' => TermTemplate::class,
            'custom-post-type' => CustomPostTypeTemplate::class,
        ]);

        if (isset($templates['attachment'])) {
            $templates['attachment'] = AttachmentTemplate::class;
        }

        if (isset($templates['comment'])) {
            $templates['comment'] = CommentTemplate::class;
        }

        $templates = apply_filters('iwp/templates/register', $templates);
        return $templates;
    }
}

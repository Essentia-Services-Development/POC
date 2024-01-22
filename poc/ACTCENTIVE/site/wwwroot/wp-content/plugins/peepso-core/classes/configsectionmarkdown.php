<?php

class PeepSoConfigSectionMarkdown extends PeepSoConfigSectionAbstract
{
	// Builds the groups array
	public function register_config_groups()
	{
		$this->context='left';
		$this->left();

        if(class_exists('PeepSoGroupsPlugin')) {
            $this->groups();
        }

		$this->context='right';
        if(class_exists('PeepSoMessages')) {
            #$this->chat();
        }



        $this->readme();
	}


	/**
	 * General Settings Box
	 */
	private function left()
	{
        $this->set_field(
            'md_post',
            __('Enable in posts','peepso-core'),
            'yesno_switch'
        );

        $this->set_field(
            'md_comment',
            __('Enable in comments','peepso-core'),
            'yesno_switch'
        );


        $this->args('default',1);
        $this->args('descript', __('ON: users can use syntax generating &lt;h&gt; tags', 'peepso-core'));
        $this->set_field(
            'md_headers',
            __('Allow headers','peepso-core'),
            'yesno_switch'
        );


        $this->args('default', 1);
        $this->args('descript', __('ON: replace the default MarkDown &lt;p&gt; tag rendering with &lt;br&gt; tags', 'peepso-core'));
        $this->set_field(
            'md_no_paragraph',
            __('Use regular linebreaks', 'peepso-core'),
            'yesno_switch'
        );

		$this->set_group(
			'peepso_md_general',
			__('General', 'peepsommd')
		);
	}

	public function chat() {
        $this->set_field(
            'md_chat',
            __('Enable in messages','peepso-core'),
            'yesno_switch'
        );

        $this->set_group(
            'peepso_md_chat',
            __('Chat', 'peepsommd')
        );
    }

    public function groups()
    {
        $this->set_field(
            'md_groups_about',
            __('Enable in group descriptions','peepso-core'),
            'yesno_switch'
        );


//        $this->set_field(
//            'md_groups_rules',
//            __('Enable in group rules','peepso-core'),
//            'yesno_switch'
//        );

        $this->set_group(
            'md_groups',
            __('Groups', 'peepso-core')
        );
    }

    public function readme() {

        $this->set_field(
            'md_readme',
            __('Markdown is a lightweight markup language with plain text formatting syntax. It is designed so that it can be converted to HTML and many other formats', 'peepsomd') . ' ' .
            '(<a href="https://en.wikipedia.org/wiki/Markdown" target="_blank">Wikipedia</a>)<br/><br/>' .
            '# headers for <b>headers (h1, h2, h3 etc)</b><br/>' .
            '**bold text** for <b>bold text</b><br/>' .
            '*italic text* for <i>italic text</i><br/>' .
            '`inline code` for <code>inline code</code><br/>' .
            '~~strikethrough~~` for <del>strikethrough</del><br/>' .
            '[link](https://PeepSo.com)` for <a href="https://PeepSo.com">link</a><br/>' .
            ''
            ,
            'message'
        );

        $this->set_group(
            'peepso_md_groups',
            __('About Markdown', 'peepso-core')
        );
    }
}
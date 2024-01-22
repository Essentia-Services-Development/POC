<?php

class PeepSoReactionsAjax extends PeepSoAjaxCallback
{
	private $_model;

	public function __construct()
	{
		parent::__construct();
		$this->_model 		= new PeepSoReactionsModel();
		$this->_model->init($this->_input->int('act_id'));
	}

	public function react(PeepSoAjaxResponse $resp)
	{
		$reaction = $this->_model->reaction($this->_input->int('react_id'));

		$this->_model->user_reaction_set($reaction->id);

		// increase like ranking on activity
		$rank = new PeepSoActivityRanking();
		$rank->add_like_count($this->_input->int('act_id'));

		$resp->success(TRUE);
		$resp->set('reaction_mine_id', 		$reaction->id);
		$resp->set('reaction_mine_label', 	$reaction->title);
		$resp->set('reaction_mine_class', 	$reaction->class);
		$resp->set('html_reactions', $this->_model->html_reactions());
	}

	public function react_delete(PeepSoAjaxResponse $resp)
	{
        $default_reaction_id = array_keys($this->_model->reactions_published)[0];
		$reaction = $this->_model->reaction($default_reaction_id);

		// remove like + all reactions for this content and this user
		$this->_model->user_reaction_reset(true);

		// decrease like ranking on activity
		$rank = new PeepSoActivityRanking();
		$rank->remove_like_count($this->_input->int('act_id'));

		$resp->success(TRUE);
		$resp->set('reaction_mine_id', false);
		$resp->set('reaction_mine_label', $reaction->title);
		$resp->set('reaction_mine_class', $reaction->class);
        $resp->set('default_reaction_id', $default_reaction_id);
		$resp->set('html_reactions', $this->_model->html_reactions());
	}

	public function html_reactions(PeepSoAjaxResponse $resp)
	{
		$resp->set('html_reactions', $this->_model->html_reactions());
		$resp->success(TRUE);
	}

	public function html_reactions_details(PeepSoAjaxResponse $resp)
	{
		$resp->set('html_reactions', $this->_model->html_reactions_details());
		$resp->success(TRUE);
	}
}

// EOF
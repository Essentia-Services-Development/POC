<?php

namespace FSPoster\App\Pages\Accounts\Controllers;

use FSPoster\App\Providers\Pages;
use FSPoster\App\Providers\Helper;
use FSPoster\App\Providers\Request;

class Main
{
	private function load_assets ()
	{
		wp_register_script( 'fsp-accounts', Pages::asset( 'Accounts', 'js/fsp-accounts.js' ), [
			'jquery',
			'fsp'
		], NULL );
		wp_enqueue_script( 'fsp-accounts' );

		wp_enqueue_style( 'fsp-accounts', Pages::asset( 'Accounts', 'css/fsp-accounts.css' ), [ 'fsp-ui' ], NULL );
	}

	public function index ()
	{
		$this->load_assets();

		$filter             = Request::get( 'filter_by', '', [
			'all',
			'active',
			'inactive',
			'visible',
			'hidden',
			'failed'
		] );
		$fsp_accounts_count = Pages::action( 'Accounts', 'get_counts' );

		if ( ! empty( $filter ) )
		{
			Helper::setOption( 'accounts_default_filter_' . get_current_user_id(), $filter );
		}
		else
		{
			$filter = Helper::getOption( 'accounts_default_filter_' . get_current_user_id(), 'visible' );
		}

		$data = [
			'accounts_count' => $fsp_accounts_count,
			'filter'         => $filter
		];

		$view = Request::get( 'view', 'accounts', [ 'accounts', 'groups' ] );

		if ( $view === 'accounts' )
		{
			$activeTab = Request::get( 'tab', 'fb', 'string' );

			if ( $activeTab === 'telegram' )
			{
				$button_text = fsp__( 'ADD A BOT' );
			}
			else if ( $activeTab === 'wordpress' )
			{
				$button_text = fsp__( 'ADD A SITE' );
			}
            else if ( $activeTab === 'webhook' )
            {
                $button_text = fsp__( 'ADD A WEBHOOK' );
            }
			else
			{
				$button_text = fsp__( 'ADD AN ACCOUNT' );
			}

			$data[ 'button_text' ] = $button_text;
			$data[ 'active_tab' ]  = $activeTab;
			$data[ 'show_accounts' ] = TRUE;
		}
		else
		{
			$data[ 'button_text' ] = 'CREATE A GROUP';
			$data[ 'show_accounts' ] = FALSE;

			$groups           = Pages::action( 'Accounts', 'get_groups' );
			$data[ 'groups' ] = $groups;

			$activeGroup = Request::get( 'group', '', 'num' );

			$activeGroup = empty( $activeGroup ) && ! empty( $groups ) ? ( isset( $groups[ 0 ][ 'id' ] ) ? $groups[ 0 ][ 'id' ] : $activeGroup ) : $activeGroup;

			$data['active_group'] = $activeGroup;

		}

		Pages::view( 'Accounts', 'index', $data );
	}
}
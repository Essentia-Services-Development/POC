<?php

namespace FSPoster\App\Pages\Apps\Controllers;

use FSPoster\App\Providers\DB;
use FSPoster\App\Providers\Helper;
use FSPoster\App\Providers\Request;
use FSPoster\App\Libraries\fb\Facebook;
use FSPoster\App\Libraries\instagram\InstagramAppMethod;

trait Ajax
{
	public function delete_app ()
	{
		$id = Request::post( 'id', '0', 'int' );

		if ( ! $id )
		{
			exit();
		}

		$check_app = DB::fetch( 'apps', $id );

		if ( ! $check_app )
		{
			Helper::response( FALSE, fsp__( 'There isn\'t an App!' ) );
		}
		else if ( $check_app[ 'user_id' ] != get_current_user_id() )
		{
			Helper::response( FALSE, fsp__( 'You don\'t have permission to delete the App!' ) );
		}
		else if ( ! empty( $check_app[ 'slug' ] ) )
		{
			Helper::response( FALSE, fsp__( 'You can\'t delete the App!' ) );
		}

		DB::DB()->delete( DB::table( 'apps' ), [ 'id' => $id ] );

		Helper::response( TRUE );
	}

	public function add_new_app ()
	{
		$driver = Request::postMust( 'driver', 'string' );

		$params = apply_filters( 'fsp_add_new_app_' . $driver, [] );

		if ( empty( $params ) )
		{
			Helper::response( FALSE );
		}

		$params[ 'user_id' ] = get_current_user_id();
		$params[ 'driver' ]  = $driver;

		DB::DB()->insert( DB::table( 'apps' ), $params );

		Helper::response( TRUE, [
			'id'      => DB::DB()->insert_id,
			'name'    => esc_html( $params[ 'name' ] ),
			'message' => fsp__( 'App has been added successfully!' )
		] );
	}
}
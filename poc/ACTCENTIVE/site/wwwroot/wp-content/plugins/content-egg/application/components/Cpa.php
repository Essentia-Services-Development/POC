<?php

namespace ContentEgg\application\components;

defined( '\ABSPATH' ) || exit;

/**
 * Cpa class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2017 keywordrush.com
 */
class Cpa {

	const CPA_ADMITAD = 'admitad';
	const CPA_GDESLON = 'gdeslon';
	const CPA_ACTIONPAY = 'actionpay';
	const CPA_CITYADS = 'cityads';
	const CPA_SHOPOZZ = 'shopozz';
	const CPA_SHOPOTAM = 'shopotam';
	const CPA_EPNBZ = 'epnbz';
	const CPA_SALESDOUBLER = 'salesdoubler';
	const CPA_RAKUTEN_LINKSHARE = 'rakuten';

	private static $cpa = array(
		Cpa::CPA_ADMITAD           => array(
			'uri'  => 'https://www.admitad.com/ru/promo/?ref=770f943d83',
			'ico'  => 'https://www.google.com/s2/favicons?domain=admitad.ru',
			'name' => 'Admitad'
		),
		Cpa::CPA_GDESLON           => array(
			'uri'  => 'http://gdeslon.ru?welcome_token=TPWB4d6UbMDVFJ2u',
			'ico'  => 'https://www.google.com/s2/favicons?domain=gdeslon.ru',
			'name' => 'Где Слон?'
		),
		Cpa::CPA_ACTIONPAY         => array(
			'uri'  => 'http://actionpay.ru/ref:NzI2MzEzOTA2Nzcz',
			'ico'  => 'https://www.google.com/s2/favicons?domain=actionpay.ru',
			'name' => 'Actionpay'
		),
		Cpa::CPA_CITYADS           => array(
			'uri'  => 'http://cityads.ru/?ref=db07c7e1',
			'ico'  => 'https://www.google.com/s2/favicons?domain=cityads.ru',
			'name' => 'CityAds'
		),
		Cpa::CPA_SHOPOZZ           => array(
			'uri'  => 'http://shopozz.ru/affiliate/?src=e77c7b588569860fddcbe6e3d528295d',
			'ico'  => 'https://www.google.com/s2/favicons?domain=shopozz.ru',
			'name' => 'Shopozz'
		),
		Cpa::CPA_SHOPOTAM          => array(
			'uri'  => 'https://shopotam.ru/?puebtdid=866987',
			'ico'  => 'https://www.google.com/s2/favicons?domain=shopotam.ru',
			'name' => 'Shopotam'
		),
		Cpa::CPA_EPNBZ             => array(
			'uri'  => 'https://epn.bz/?i=6cb6d',
			'ico'  => 'https://www.google.com/s2/favicons?domain=epn.bz',
			'name' => 'Epn.bz'
		),
		Cpa::CPA_SALESDOUBLER      => array(
			'uri'  => 'https://www.salesdoubler.com.ua/affiliate/signup/?ref=30170',
			'ico'  => 'https://www.google.com/s2/favicons?domain=salesdoubler.com.ua',
			'name' => 'Salesdoubler'
		),
		Cpa::CPA_RAKUTEN_LINKSHARE => array(
			'uri'  => 'https://signup.linkshare.com/publishers/registration/landing',
			'ico'  => 'https://www.google.com/s2/favicons?domain=salesdoubler.com.ua',
			'name' => 'Rakuten Linkshare'
		),
	);

	static public function deeplinkPrepare( $deeplink ) {
		// multiple deeplink
		if ( strstr( $deeplink, ';' ) ) {
			return $deeplink;
		}

		$cpa = array(
			'ad.admitad.com' => 'ulp',
			'modato.ru'      => 'ulp', // lamoda admitad?
			'f.gdeslon.ru'   => 'goto',
			'cityadspix.com' => 'url',
			'www.cityads.ru' => 'url',
			'epnclick.ru'    => 'to',
			'alipromo.com'   => 'to', //epn.bz
			//'click.linksynergy.com' => 'murl',
			//'click.linksynergy.com' => 'RD_PARM1',
		);

		$p = parse_url( $deeplink );

		if ( $p === false || empty( $p['host'] ) ) {
			return $deeplink;
		}

		$host = $p['host'];

		if ( $host == 'n.actionpay.ru' ) {
			return str_replace( 'url=example.com', 'url=', $deeplink );
		}

		if ( array_key_exists( $host, $cpa ) ) {
			$param = $cpa[ $host ];
			if ( ! empty( $p['query'] ) ) {
				parse_str( $p['query'], $query );
			} else {
				$query = array();
			}
			if ( isset( $query[ $param ] ) ) {
				unset( $query[ $param ] );
			}
			$url = $p['scheme'] . '://' . $p['host'] . $p['path'] . '?';
			if ( $query ) {
				$url .= http_build_query( $query ) . '&';
			}
			$url .= $param . '=';

			return $url;
		}

		return $deeplink;
	}

	static public function getCpaString( $shop_id ) {
		$shop = ShopManager::getInstance()->getItem( $shop_id );
		if ( empty( $shop->cpa ) ) {
			return '';
		}
		$str = '';
		foreach ( $shop->cpa as $cpa ) {
			$str .= '<a target="_blank" href="' . self::getCpaLink( $cpa ) . '">';
			$str .= '<img src="' . self::getCpaIco( $cpa ) . '" title="' . self::getCpaName( $cpa ) . '" />';
			$str .= '</a> ';
		}

		return $str;
	}

	static public function getCpaLink( $cpa ) {
		if ( ! empty( self::$cpa[ $cpa ] ) ) {
			return self::$cpa[ $cpa ]['uri'];
		} else {
			return false;
		}
	}

	static public function getCpaIco( $cpa ) {
		if ( ! empty( self::$cpa[ $cpa ] ) ) {
			return self::$cpa[ $cpa ]['ico'];
		} else {
			return false;
		}
	}

	static public function getCpaName( $cpa ) {
		if ( ! empty( self::$cpa[ $cpa ] ) ) {
			return self::$cpa[ $cpa ]['name'];
		} else {
			return false;
		}
	}

	static public function deeplinkSetSubid( $deeplink, $subid, $priority = 0 ) {

		$cpa = array(
			'ad.admitad.com'        => 'subid',
			'modato.ru'             => 'subid', // lamoda admitad?
			'f.gdeslon.ru'          => 'sub_id',
			'cityadspix.com'        => 'sa',
			'www.cityads.ru'        => 'sa',
			'epnclick.ru'           => 'sub',
			'click.linksynergy.com' => 'subid',
		);

		$p = parse_url( $deeplink );
		if ( $p === false || ! isset( $p['host'] ) ) {
			return $deeplink;
		}

		$host = $p['host'];

		//actionpay передает subid через path, остальные через query
		if ( $host == 'n.actionpay.ru' ) {
			return str_replace( '/subaccount', '/' . $subid, $deeplink );
		}


		if ( array_key_exists( $host, $cpa ) ) {
			$param = $cpa[ $host ];
			if ( ! empty( $p['query'] ) ) {
				parse_str( $p['query'], $query );
			} else {
				$query = array();
			}

			$url = $p['scheme'] . '://' . $p['host'] . $p['path'] . '?';

			if ( ! isset( $query[ $param ] ) || $query[ $param ] == '' || $priority == 1 ) {
				unset( $query[ $param ] );
				$url .= $param . '=' . $subid . '&';
			}

			if ( $query ) {
				$url .= http_build_query( $query );
			}

			return $url;
		}

		return $deeplink;
	}

	public static function getCpaIds() {
		return array_keys( self::$cpa );
	}

}

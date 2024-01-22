<?php

use \DantSu\OpenStreetMapStaticAPI\OpenStreetMap;
use \DantSu\OpenStreetMapStaticAPI\LatLng;
use \DantSu\OpenStreetMapStaticAPI\Polygon;
use \DantSu\OpenStreetMapStaticAPI\Markers;
use \DantSu\OpenStreetMapStaticAPI\TileLayer;

class MailsterStaticMap {

	protected $openStreetMap;
	protected $file;
	protected $exist;

	public function __construct( $args ) {


		$coords = array();
		if ( isset( $args['coords'] ) ) {
			foreach ( $args['coords'] as $c ) {
				if ( is_string( $c ) ) {
					$c        = explode( ',', $c );
					$coords[] = new LatLng( (float) $c[0], (float) $c[1] );
				}
			}
		}

		if ( isset( $args['lat'] ) && isset( $args['lon'] ) ) {
			$coords[] = new LatLng( (float) $args['lat'], (float) $args['lon'] );
		}

		$center = $this->getCenter( $coords );

		$zoom        = (int) $args['zoom'];
		$imageWidth  = (int) $args['width'];
		$imageHeight = (int) $args['height'];
		//$zoom        = $this->getZoom( $coords, $imageWidth, $imageHeight, (int) $args['zoom'] );
		$tileServer  = new TileLayer( 'https://tile.openstreetmap.org/{z}/{x}/{y}.png', '© OpenStreetMap' );

		$this->openStreetMap = new OpenStreetMap( $center, $zoom, $imageWidth, $imageHeight, $tileServer );

		foreach ( $coords as $markerpos ) {
			$this->openStreetMap->addMarkers(
				( new Markers( MAILSTER_DIR . 'assets/img/osm/markers/marker.png' ) )
				->setAnchor( Markers::ANCHOR_CENTER, Markers::ANCHOR_BOTTOM )
				->addMarker( $markerpos )
			);
		}
	}

	private function getCenter( $coords ) {

		if ( ! is_array( $coords ) ) {
			return false;
		}

		$num_coords = count( $coords );

		if ( $num_coords == 1 ) {
			return $coords[0];
		}

		$X = 0.0;
		$Y = 0.0;
		$Z = 0.0;

		foreach ( $coords as $coord ) {
			$lat = $coord->getLat() * pi() / 180;
			$lon = $coord->getLng() * pi() / 180;

			$a = cos( $lat ) * cos( $lon );
			$b = cos( $lat ) * sin( $lon );
			$c = sin( $lat );

			$X += $a;
			$Y += $b;
			$Z += $c;
		}

		$X /= $num_coords;
		$Y /= $num_coords;
		$Z /= $num_coords;

		$lon = atan2( $Y, $X );
		$hyp = sqrt( $X * $X + $Y * $Y );
		$lat = atan2( $Z, $hyp );

		return new LatLng( $lat * 180 / pi(), $lon * 180 / pi() );
	}


	public function getDir() {
		return $this->file;
	}

	public function getUrl() {
		$dir = $this->getDir();
		return str_replace( MAILSTER_UPLOAD_DIR, MAILSTER_UPLOAD_URI, $dir );
	}

	public function getFile() {

	}

	public function displayPNG() {
		$this->openStreetMap->getImage()->displayPNG();
	}



}

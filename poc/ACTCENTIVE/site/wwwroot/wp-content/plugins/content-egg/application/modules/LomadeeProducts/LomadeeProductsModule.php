<?php

namespace ContentEgg\application\modules\LomadeeProducts;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\components\AffiliateParserModule;
use ContentEgg\application\components\ContentProduct;
use ContentEgg\application\admin\PluginAdmin;
use ContentEgg\application\helpers\TextHelper;
use ContentEgg\application\libs\lomadee\LomadeeApi;

/**
 * LomadeeProductsModule class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2017 keywordrush.com
 */
class LomadeeProductsModule extends AffiliateParserModule {

	private $api_client = null;

	public function info() {
		return array(
			'name'        => 'Lomadee Products',
			'description' => __( 'Lomadee.com affiliate network.', 'content-egg' ),
		);
	}

	public function releaseVersion() {
		return '4.3.0';
	}

	public function getParserType() {
		return self::PARSER_TYPE_PRODUCT;
	}

	public function defaultTemplateName() {
		return 'grid';
	}

	public function isItemsUpdateAvailable() {
		return true;
	}

	public function doRequest( $keyword, $query_params = array(), $is_autoupdate = false ) {
		$options = array();

		if ( $is_autoupdate ) {
			$limit = $this->config( 'entries_per_page_update' );
		} else {
			$limit = $this->config( 'entries_per_page' );
		}

		$options['size'] = $limit;

		$params = array(
			'categoryId',
			'storeId',
			'sort',
		);

		foreach ( $params as $param ) {
			$value = $this->config( $param );
			if ( $value !== '' ) {
				$options[ $param ] = $value;
			}
		}

		if ( ! empty( $query_params['storeId'] ) ) {
			$options['storeId'] = (int) $query_params['storeId'];
		}

		$results = $this->getApiClient()->offers( $keyword, $options );
		if ( ! isset( $results['offers'] ) || ! is_array( $results['offers'] ) ) {
			return array();
		}

		return $this->prepareResults( $results['offers'] );
	}

	private function prepareResults( $results ) {
		$data = array();
		//$orig_urls = array();
		foreach ( $results as $key => $r ) {
			$content = new ContentProduct;

			$content->unique_id    = $r['id'] . '-' . $r['store']['id']; // offer id
			$content->stock_status = ContentProduct::STOCK_STATUS_IN_STOCK;
			$content->title        = $r['name'];
			$content->url          = $r['link'];
			$content->img          = $r['thumbnail'];
			$content->price        = $r['price'];
			$content->currencyCode = 'BRL';
			$content->merchant     = trim( $r['store']['name'] );

			if ( $domain = self::getMerchantDomain( $content->merchant ) ) {
				$content->domain = $domain;
			} else {
				$merchant = strtolower( $r['store']['name'] );
				if ( TextHelper::isValidDomainName( $merchant ) ) {
					$content->domain = $merchant;
				}
			}
			$content->extra = new ExtraDataLomadeeProducts();
			ExtraDataLomadeeProducts::fillAttributes( $content->extra, $r );

			$data[] = $content;
		}

		return $data;
	}

	public function doRequestItems( array $items ) {
		// assign new data
		foreach ( $items as $unique_id => $item ) {
			$parts    = explode( '-', $unique_id );
			$offer_id = $parts[0];
			$store_id = $parts[1];

			try {
				$result = $this->getApiClient()->offer( $offer_id, $store_id );
			} catch ( \Exception $e ) {
				/**
				 * Offer IDs were changed after updating API to v3
				 * So no way to track stock status and price for existing products?
				 */
				if ( $e->getCode() == 404 ) {
					$items[ $unique_id ]['stock_status'] = ContentProduct::STOCK_STATUS_OUT_OF_STOCK;
				}
				//$items[$unique_id]['stock_status'] = ContentProduct::STOCK_STATUS_UNKNOWN;
				continue;
			}
			if ( ! $result || ! isset( $result['offers'] ) ) {
				$items[ $unique_id ]['stock_status'] = ContentProduct::STOCK_STATUS_OUT_OF_STOCK;
				continue;
			}
			$items[ $unique_id ]['price']        = $result['offers'][0]['price'];
			$items[ $unique_id ]['stock_status'] = ContentProduct::STOCK_STATUS_IN_STOCK;
			$items[ $unique_id ]['url']          = $result['offers'][0]['link'];
		}

		return $items;
	}

	private function getApiClient() {
		if ( $this->api_client === null ) {
			$this->api_client = new LomadeeApi( '15071999399311f734bd1', $this->config( 'sourceId' ) );
		}

		return $this->api_client;
	}

	public function renderResults() {
		PluginAdmin::render( '_metabox_results', array( 'module_id' => $this->getId() ) );
	}

	public function renderSearchResults() {
		PluginAdmin::render( '_metabox_search_results', array( 'module_id' => $this->getId() ) );
	}

	public function renderSearchPanel() {
		$this->render( 'search_panel', array( 'module_id' => $this->getId() ) );
	}

	public static function getMerchantDomains() {
		return array(
			'2 AM Gaming '                    => '2amgaming.com',
			'About Home'                      => 'abouthome.com.br',
			'Alfaparf Milano'                 => 'loja.alfaparfmilano.com.br',
			'Almundo'                         => 'almundo.com.br',
			'Alto Giro'                       => 'altogiro.net',
			'Amazon'                          => 'amazon.com.br',
			'Amêndoas Confeitadas'            => 'amendoasconfeitadas.com.br',
			'Americanas.com'                  => 'americanas.com.br',
			'Americanas'                      => 'americanas.com.br',
			'Anhanguera Vestibular'           => 'vestibulares.com.br',
			'Anker'                           => 'ankeroficial.com.br',
			'April Seguro Viagens'            => 'aprilbrasil.com.br',
			'Aramis'                          => 'aramis.com.br',
			'Arena Warrior'                   => 'arenawarrior.com.br',
			'Assine Abril'                    => 'assine-abril.com',
			'Asus'                            => 'loja.asus.com.br',
			'Atrio Esportes'                  => 'atrioesportes.com.br',
			'Aurea Beauty'                    => 'aureanutrition.com.br',
			'Baitashop'                       => 'baitashop.com.br',
			'Banggood'                        => 'banggood.com',
			'Barceló'                         => 'barcelo.com',
			'BC Suplementos'                  => 'bcsuplementos.com.br',
			'Beline'                          => 'beline.com.br',
			'Betec'                           => 'loja.betec.com.br',
			'Blow Back'                       => 'lojablowback.com.br',
			'Box Viva'                        => 'boxviva.com.br',
			'BRA Lingerie'                    => 'bralingerie.com.br',
			'Brastemp'                        => 'loja.brastemp.com.br',
			'BugaLupa'                        => 'bugalupa.com.br',
			'BugShop'                         => 'bugshop.com.br',
			'Byoushop'                        => 'byoushop.com.br',
			'Cabana Magazine'                 => 'cabanamagazine.com.br',
			'Camisaria Colombo'               => 'camisariacolombo.com.br',
			'Cartão Animal'                   => 'loja.cartaoanimal.com.br',
			'Carter\'s'                       => 'cartersoshkosh.com.br',
			'Catho Educação'                  => 'catho.com.br',
			'Cavuca.com'                      => 'cavuca.com.br',
			'Centauro'                        => 'centauro.com.br',
			'Center Bike'                     => 'centerbikemaringa.com.br',
			'Central Ar'                      => 'centralar.com.br',
			'Cereja sem Bolo'                 => 'cerejasembolo.com.br',
			'ChicBest'                        => 'chicbest.com',
			'Chico Rei'                       => 'chicorei.com',
			'Ciclic'                          => 'ciclic.com.br',
			'Classic Tennis'                  => 'classictennis.com.br',
			'Clique Bem Estar'                => 'cliquebemestar.com.br',
			'Clube Marisol'                   => 'clubemarisol.com.br',
			'Clube Paladar'                   => 'clubepaladar.com.br',
			'Color Brinque'                   => 'colorbrinque.com.br',
			'Comix'                           => 'comix.com.br',
			'Conoy Cosmetics'                 => 'conoycosmetics.com',
			'Consul'                          => 'loja.consul.com.br',
			'CPM Office'                      => 'cpmoffice.com.br',
			'DarkSide Books'                  => 'darksidebooks.com.br',
			'Deans'                           => 'deans.com.br',
			'Dermage'                         => 'dermage.com.br',
			'Descomplica'                     => 'descomplica.com.br',
			'Despachante DOK'                 => 'despachantedok.com.br',
			'Doce Erva'                       => 'doceerva.com.br',
			'Dona Coelha'                     => 'donacoelha.com',
			'Dresslily'                       => 'dresslily.com',
			'Drogaria Pacheco'                => 'drogariaspacheco.com.br',
			'Drogaria São Paulo'              => 'drogariasaopaulo.com.br',
			'E-cota Auto'                     => 'e-cota.com',
			'Editora Foco'                    => 'editorafoco.com.br',
			'Elare'                           => 'elare.com.br',
			'Electrolux'                      => 'loja.electrolux.com.br',
			'EletroAlmeida'                   => 'eletroalmeida.com.br',
			'Eneba'                           => 'eneba.com.br',
			'Escala Verde'                    => 'escalaverde.com.br',
			'Etna'                            => 'etna.com.br',
			'Fata'                            => 'fata.com.br',
			'Ferramentas Kennedy'             => 'ferramentaskennedy.com.br',
			'Foreo'                           => 'foreo.com',
			'Found IT'                        => 'foundit.com.br',
			'Frio Peças'                      => 'friopecas.com.br',
			'Gear Best'                       => 'gearbest.com',
			'Girafa'                          => 'girafa.com.br',
			'Go Read'                         => 'goreadassineabril.com',
			'Gourmetzinho'                    => 'gourmetzinhocomidinhas.com.br',
			'Grupo A'                         => 'grupoa.com.br',
			'Guitarpedia'                     => 'guitarpedia.com.br',
			'Gym Chef Comida Fit'             => 'gymchef.com.br',
			'Hang Loose'                      => 'hangloose.com.br',
			'Happy Hair'                      => 'happyhair.com.br',
			'Hermoso Compadre'                => 'hermosocompadre.com.br',
			'HFBrazil'                        => 'hfbrazil.com.br',
			'Hipervarejo'                     => 'hipervarejo.com.br',
			'Home is...'                      => 'homeis.com.br',
			'Hostinger'                       => 'hostinger.com.br',
			'Impacta'                         => 'impacta.com.br',
			'Inbox Shoes'                     => 'inboxshoes.com.br',
			'IORANE'                          => 'shoponline.iorane.com.br',
			'Ipemig'                          => 'estude-ipemig.com',
			'Iridium labs '                   => 'iridiumlabs.com.br',
			'iRobot'                          => 'irobotloja.com.br',
			'Jocar'                           => 'jocar.com.br',
			'Joico'                           => 'joico.com.br',
			'K9 Fashion'                      => 'k9fashion.com.br',
			'Kit Led'                         => 'kitled.com.br',
			'KitchenAid'                      => 'kitchenaid.com.br',
			'Klin'                            => 'klin.com.br',
			'Koralle'                         => 'koralle.com.br',
			'Latifundio'                      => 'latifundio.com.br',
			'Lenovo Brasil'                   => 'lenovo.com',
			'Leveros'                         => 'leveros.com.br',
			'Levok'                           => 'levok.com.br',
			'Livraria Cultura'                => 'livrariacultura.com.br',
			'Livraria da Travessa'            => 'travessa.com.br',
			'Livraria Florence'               => 'livrariaflorence.com.br',
			'Loja do Café'                    => 'lojadocafe.com.br',
			'Loja do Mecânico'                => 'lojadomecanico.com.br',
			'Lomadee'                         => 'lomadee.com',
			'Lovoo'                           => 'pt.lovoo.com',
			'LTPerformance'                   => 'ltperformance.com.br',
			'Magazine Pag Menos'              => 'magazinepagmenos.com.br',
			'Magrela Shop'                    => 'magrelashop.com.br',
			'Mais Barato Store '              => 'maisbaratostore.com.br',
			'Malwee'                          => 'malwee.com.br',
			'Mamorena'                        => 'mamorena.com.br',
			'Mania Pop'                       => 'maniapop.com.br',
			'Max Milhas'                      => 'maxmilhas.com.br',
			'Megaton Suplementos'             => 'megatonsuplementos.com.br',
			'Milla Cabelos'                   => 'millacabelos.com.br',
			'Mirage'                          => 'mirage.com.br',
			'MMPlace'                         => 'mmplace.com.br',
			'Mobiliata'                       => 'mobiliata.com.br',
			'Mobly'                           => 'mobly.com.br',
			'Mr Deal'                         => 'mrdeal.com.br',
			'Multikids'                       => 'lojamultikids.com.br',
			'Multikids baby'                  => 'multikidsbaby.com.br',
			'MultiLaser'                      => 'lojamultilaser.com.br',
			'Mundo Infantil Store'            => 'mundoinfantilstore.com.br',
			'Nathus Brasil'                   => 'nathusbrasil.com.br',
			'Natue'                           => 'natue.com.br',
			'Netshoes'                        => 'netshoes.com.br',
			'newbeach'                        => 'newbeach.com.br',
			'Newchic'                         => 'pt.newchic.com',
			'Nextel'                          => 'nextel.com.br',
			'Nike'                            => 'nike.com.br',
			'NordVPN'                         => 'nordvpn.com',
			'NutriBullet'                     => 'nutribulletbrasil.com.br',
			'Nuuvem'                          => 'nuuvem.com',
			'Ordenato'                        => 'ordenato.com.br',
			'Organomix'                       => 'organomix.com.br',
			'Ouro e Prata'                    => 'viacaoouroeprata.com.br',
			'PagSeguro'                       => 'pagseguro.uol.com.br',
			'Par Perfeito'                    => 'parperfeito.com.br',
			'Passagens Aéreas '               => 'passagensaereas.com.br',
			'PB Kids'                         => 'pbkids.com.br',
			'Pierre Cardin - Feminina'        => 'pierrecardin.com.br',
			'Pierre Cardin Store - Masculina' => 'store.pierrecardin.com.br',
			'Pipocaweb'                       => 'pipocaweb.com.br',
			'Plantei'                         => 'plantei.com.br',
			'Pneu Store'                      => 'pneustore.com.br',
			'Pollia'                          => 'pollia.com.br',
			'Portal Pós'                      => 'portalpos.com.br',
			'Porto de Letras'                 => 'portodeletras.com.br',
			'Porto Faz'                       => 'portosegurofaz.com.br',
			'Positivo'                        => 'loja.meupositivo.com.br',
			'Positivo Casa Inteligente'       => 'positivocasainteligente.com.br',
			'Prime Home Decor'                => 'primehomedecor.com.br',
			'Pulse Sound'                     => 'pulsesound.com.br',
			'Q48 Superfoods'                  => 'q48superfoods.com.br',
			'Quantum'                         => 'meuquantum.com.br',
			'QUINTA DELL\'ARTE'               => 'quintadellarte.com.br',
			'Recco'                           => 'recco.com.br',
			'Repassa'                         => 'repassa.com.br',
			'Reppara'                         => 'reppara.com.br',
			'ReservaCool'                     => 'reservacool.com.br',
			'Resultados Digitais'             => 'materiais.resultadosdigitais.com.br',
			'Ri Happy'                        => 'rihappy.com.br',
			'Ribsol Energia Solar'            => 'ribsolenergiasolar.com.br',
			'RZM Shop'                        => 'rzmshop.com.br',
			'Sam\'s Club Premium'             => 'seja-socio-samsclub.com',
			'Samsonite'                       => 'samsonite.com.br',
			'Santori'                         => 'santorioficial.com.br',
			'Sapatilhas BH'                   => 'sapatilhasbh.com.br',
			'Segundo Ciclo'                   => 'segundociclo.com.br',
			'Seja-Lev'                        => 'seja-lev.com',
			'Sem Parar'                       => 'semparar.com.br%2Fassine-promo',
			'Seu Bebê Precisa'                => 'seubebeprecisa.com.br',
			'Shop RVX'                        => 'shoprvx.com.br',
			'Shoptime'                        => 'shoptime.com.br',
			'Sieno Perfumes'                  => 'sieno.com.br',
			'Sosô Lingerie'                   => 'sosolingerie.com.br',
			'StarTech'                        => 'startech.com.br',
			'Stoodi'                          => 'stoodi.com.br',
			'Submarino'                       => 'submarino.com.br',
			'Suplementos mais baratos'        => 'suplementosmaisbaratos.com',
			'Swift'                           => 'swift.com.br',
			'Tá Barato Tô Levando'            => 'tabaratotolevando.com.br',
			'TAP Air Portugal'                => 'flytap.com',
			'TNG'                             => 'tng.com.br',
			'Track&Field'                     => 'tf.com.br',
			'Travel Mobile '                  => 'travelmobile.biz',
			'Uitech'                          => 'uitech.com.br',
			'UltraBikes '                     => 'ultrabikes.com.br',
			'UltraSports'                     => 'ultrasports.com.br',
			'Uniflores'                       => 'uniflores.com.br',
			'unifórmula online'               => 'uniformulaonline.com.br',
			'Universo Kids'                   => 'lojauniversokids.com',
			'Unopar EAD Vestibular'           => 'vestibular.unoparead.com.br',
			'Usaflex'                         => 'usaflex.com.br',
			'Use Camisetas'                   => 'usecamisetas.com',
			'Vaio'                            => 'loja.br.vaio.com',
			'Valejet'                         => 'valejet.com',
			'Veni perfumaria'                 => 'veni.com.br',
			'Vhita'                           => 'vhita.com.br',
			'Vibra Comigo'                    => 'vibracomigo.com.br',
			'Viggore'                         => 'viggore.com.br',
			'Vissla'                          => 'vissla.com.br',
			'Vital Atman'                     => 'vitalatman.com.br',
			'Vitrine Casual'                  => 'vitrinecasual.com.br',
			'Volcom'                          => 'volcom.com.br',
			'Weego'                           => 'lojaweego.com.br',
			'Yori Cosméticos'                 => 'yori.store',
			'Zaful'                           => 'br.zaful.com',
			'Zait Jeans'                      => 'zait.com.br',
			'Zalika Cosméticos'               => 'zalikacosmeticos.com.br',
			'Zandara Store'                   => 'zandarastore.com.br',
			'Zattini'                         => 'zattini.com.br',
			'Zona Cerealista'                 => 'zonacerealista.com.br',
		);
	}

	public static function getMerchantDomain( $merchant ) {
		$list = self::getMerchantDomains();
		if ( isset( $list[ $merchant ] ) ) {
			return $list[ $merchant ];
		} else {
			return false;
		}
	}

}

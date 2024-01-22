<?php

defined( 'ABSPATH' ) OR exit;

register_block_pattern_category(
    'rehubaff',
    array( 'label' => __( 'Rehub Profitable', 'rehub-framework' ) )
);

register_block_pattern_category(
    'rehubcontent',
    array( 'label' => __( 'Content Helpers', 'rehub-framework' ) )
);

register_block_pattern(
    'rehubtheme/versusblock',
    array(
        'title'       => __( 'Versus Block', 'rehub-framework' ),
        'categories' => array('rehubaff'),
        'content'     => '<!-- wp:rehub/versus-table {"heading":"Mavic 2 vs Mavic 1","subheading":"Epic battle","bg":"#f0f0f0","firstColumn":{"type":"image","isGrey":false,"content":"Value 1","image":"https://remag.wpsoul.net/wp-content/uploads/2018/12/95224951074647_small10.jpeg","imageId":4256},"secondColumn":{"type":"image","isGrey":false,"content":"Value 2","image":"https://remag.wpsoul.net/wp-content/uploads/2018/12/EVERGREEN-Best-drones-to-buy-in-2018-Top-picks-including-DJI-Parrot-and-Xiro.jpg","imageId":4266}} /-->
<!-- wp:rehub/versus-table {"heading":"HD Video","subheading":"4k and 8k","firstColumn":{"type":"tick","isGrey":false,"content":"Value 1","image":"","imageId":""},"secondColumn":{"type":"times","isGrey":false,"content":"Value 2","image":"","imageId":""}} /--><!-- wp:rehub/versus-table {"heading":"SmartFocus","subheading":"Focus and tracking","firstColumn":{"type":"tick","isGrey":false,"content":"Value 1","image":"","imageId":""},"secondColumn":{"type":"times","isGrey":false,"content":"Value 2","image":"","imageId":""}} /--><!-- wp:rehub/versus-table {"heading":"Quick Shots","subheading":"Functional blocks","firstColumn":{"type":"tick","isGrey":false,"content":"Value 1","image":"","imageId":""},"secondColumn":{"type":"tick","isGrey":false,"content":"Value 2","image":"","imageId":""}} /--><!-- wp:rehub/versus-table {"heading":"Flight Time","subheading":"Average flight time","firstColumn":{"type":"text","isGrey":false,"content":"40min","image":"","imageId":""},"secondColumn":{"type":"text","isGrey":true,"content":"20min","image":"","imageId":""}} /--><!-- wp:rehub/versus-table {"heading":"FPS","subheading":"Frame rate per second","firstColumn":{"type":"text","isGrey":false,"content":"240","image":"","imageId":""},"secondColumn":{"type":"text","isGrey":true,"content":"120","image":"","imageId":""}} /--><!-- wp:spacer {"height":33} --><div style="height:33px" aria-hidden="true" class="wp-block-spacer"></div><!-- /wp:spacer -->',
    )
);

register_block_pattern(
    'rehubtheme/ctablockheading',
    array(
        'title'       => __( 'Heading and CTA block', 'rehub-framework' ),
        'categories' => array('rehubaff'),
        'content'     => '<!-- wp:rehub/review-heading {"title":"My custom heading","subtitle":"My custom subheading"} /-->

<!-- wp:image {"sizeSlug":"large"} -->
<figure class="wp-block-image size-large"><img src="https://dummyimage.com/600x250/000/fff" alt=""/></figure>
<!-- /wp:image -->

<!-- wp:rehub/promo-box {"title":"\u003cstrong\u003eCheck Prices of Product\u003c/strong\u003e","content":"and find best price for your needs","showHighlightBorder":true,"highlightColor":"#7c03fc","showButton":true,"buttonText":"Find Best Price Now","buttonLink":"#"} /-->

<!-- wp:paragraph -->
<p></p>
<!-- /wp:paragraph -->',
    )
);

register_block_pattern(
    'rehubtheme/toptitleblock',
    array(
        'title'       => __( 'Top Title block for Full width page', 'rehub-framework' ),
        'categories' => array('rehubaff'),
        'description' => _x( 'Title block Special for Customizable Full width post layout', 'Block pattern description', 'rehub-framework' ),
        'content'     => '<!-- wp:cover {"minHeight":259,"minHeightUnit":"px","gradient":"midnight","contentPosition":"center center","isDark":false,"align":"full"} -->
        <div class="wp-block-cover alignfull is-light" style="min-height:259px"><span aria-hidden="true" class="wp-block-cover__background has-background-dim-100 has-background-dim has-background-gradient has-midnight-gradient-background"></span><div class="wp-block-cover__inner-container"><!-- wp:paragraph {"align":"center","style":{"typography":{"fontSize":24,"lineHeight":"1"}},"textColor":"luminous-vivid-amber"} -->
        <p class="has-text-align-center has-luminous-vivid-amber-color has-text-color" style="font-size:24px;line-height:1"><strong>Subtitle</strong></p>
        <!-- /wp:paragraph -->
        
        <!-- wp:heading {"textAlign":"center","level":1,"style":{"typography":{"lineHeight":"1.1","fontSize":41}},"textColor":"white"} -->
        <h1 class="has-text-align-center has-white-color has-text-color" id="main-title-for-customizable-layout-1" style="font-size:41px;line-height:1.1">Main title for customizable layout </h1>
        <!-- /wp:heading -->
        
        <!-- wp:paragraph {"align":"center","style":{"typography":{"lineHeight":"1.2"},"color":{"text":"#cbdbea"}}} -->
        <p class="has-text-align-center has-text-color" style="color:#cbdbea;line-height:1.2">Date of posting</p>
        <!-- /wp:paragraph --></div></div>
        <!-- /wp:cover -->
        
        <!-- wp:spacer {"height":"15px"} -->
        <div style="height:15px" aria-hidden="true" class="wp-block-spacer"></div>
        <!-- /wp:spacer -->',
    )
);

register_block_pattern(
    'rehubtheme/ordertitleblock',
    array(
        'title'       => __( 'Order page title block', 'rehub-framework' ),
        'categories' => array('rehubaff'),
        'description' => _x( 'Title block for orders or other system pages with steps', 'Block pattern description', 'rehub-framework' ),
        'content'     => '<!-- wp:columns {"className":"mb0 pt30 itinerary-order"} -->
        <div class="wp-block-columns mb0 pt30 itinerary-order"><!-- wp:column {"verticalAlignment":"top","className":"hideontablet rh-flex-justify-center rh-flex-columns ml0 mr0"} -->
        <div class="wp-block-column is-vertically-aligned-top hideontablet rh-flex-justify-center rh-flex-columns ml0 mr0"><!-- wp:rehub/itinerary {"items":[{"icon":"rhicon rhi-check","color":"#4caf50","content":"\u003ca href=\u0022/cart\u0022 data-type=\u0022URL\u0022 data-id=\u0022/cart\u0022\u003eSHOPPING CART\u003c/a\u003e"}]} /--></div>
        <!-- /wp:column -->
        
        <!-- wp:column {"verticalAlignment":"top","className":"tabletblockdisplay rh-flex-justify-center rh-flex-columns ml0 mr0"} -->
        <div class="wp-block-column is-vertically-aligned-top tabletblockdisplay rh-flex-justify-center rh-flex-columns ml0 mr0"><!-- wp:rehub/itinerary {"items":[{"icon":"rhicon rhi-pencil","color":"#f46b09","content":"\u003cstrong\u003eORDER DETAILS\u003c/strong\u003e"}]} /--></div>
        <!-- /wp:column -->
        
        <!-- wp:column {"className":"hideontablet rh-flex-justify-center rh-flex-columns ml0 mr0"} -->
        <div class="wp-block-column hideontablet rh-flex-justify-center rh-flex-columns ml0 mr0"><!-- wp:rehub/itinerary {"items":[{"icon":"rhicon rhi-circle-solid","color":"#dbdbdb","content":"ORDER COMPLETE"}]} /--></div>
        <!-- /wp:column --></div>
        <!-- /wp:columns -->
        
        <!-- wp:spacer {"height":15} -->
        <div style="height:15px" aria-hidden="true" class="wp-block-spacer"></div>
        <!-- /wp:spacer -->',
    )
);

register_block_pattern(
    'rehubtheme/imagecitate',
    array(
        'title'       => __( 'Quote with image', 'rehub-framework' ),
        'categories' => array('rehubcontent'),
        'content'     => '<!-- wp:group -->
<div class="wp-block-group"><div class="wp-block-group__inner-container"><!-- wp:image {"align":"center","width":164,"height":164,"sizeSlug":"large","className":"is-style-rounded"} -->
<div class="wp-block-image is-style-rounded"><figure class="aligncenter size-large is-resized"><img src="https://s.w.org/images/core/5.5/don-quixote-03.jpg" alt="Pencil drawing of Don Quixote" width="164" height="164"/></figure></div>
<!-- /wp:image -->

<!-- wp:quote {"align":"center","className":"is-style-large"} -->
<blockquote class="wp-block-quote has-text-align-center is-style-large"><p>"Do you see over yonder, friend Sancho, thirty or forty hulking giants? I intend to do battle with them and slay them."</p><cite>— Don Quixote</cite></blockquote>
<!-- /wp:quote -->

<!-- wp:separator {"className":"is-style-dots"} -->
<hr class="wp-block-separator is-style-dots"/>
<!-- /wp:separator --></div></div>
<!-- /wp:group -->',
    )
);

register_block_pattern(
    'rehubtheme/tablecontent',
    array(
        'title'       => __( 'Table of Content Shortcode', 'rehub-framework' ),
        'categories' => array('rehubcontent'),
        'content'     => '<!-- wp:rehub/titlebox {"title":"Table of content","text":"[contents h2 h3]"} /-->',
    )
);

register_block_pattern(
    'rehubtheme/toplistshortcode',
    array(
        'title'       => __( 'Top list shortcode', 'rehub-framework' ),
        'categories' => array('rehubaff'),
        'content'     => '<!-- wp:shortcode -->[wpsm_toplist]<!-- /wp:shortcode -->',
    )
);

register_block_pattern(
    'rehubtheme/stickycontentshortcode',
    array(
        'title'       => __( 'Sticky panel Contents shortcode', 'rehub-framework' ),
        'categories' => array('rehubcontent'),
        'content'     => '<!-- wp:shortcode -->[wpsm_stickypanel][contents h2][/wpsm_stickypanel]<!-- /wp:shortcode -->',
    )
);
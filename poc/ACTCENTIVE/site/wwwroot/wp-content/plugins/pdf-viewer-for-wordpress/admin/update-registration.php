<div class="wrap">
    <div id="poststuff">
        <div id="post-body">

            <div class="tnc-pdf-column-left">
                <div class="postbox">
                    <h3>Register TNC FlipBook - PDF viewer for WordPress</h3>
                    <div class="inside">
                    <?php tnc_pvfw_site_registered_status( true ); ?>
                    	<?php if (isset($_POST['purchase_code']) && !empty($_POST['purchase_code'])) {
                    		$update_server = "https://updates.themencode.com/pvfw/register-purchase.php";
                    		$purchase_code = htmlspecialchars($_POST['purchase_code']);
                    		$site_url 	   = site_url();

                    		$response = wp_remote_post( $update_server, array(
                                'sslverify'   => false,
							    'method'      => 'POST',
							    'timeout'     => 45,
							    'redirection' => 5,
							    'httpversion' => '1.0',
							    'blocking'    => true,
							    'headers'     => array(),
							    'body'        => array(
							        'site' => $site_url,
							        'purchase_code' => $purchase_code
							    ),
							    'cookies'     => array()
							    )
							);
							 
							if ( is_wp_error( $response ) ) {
							    $error_message = $response->get_error_message();
							    $display_result_message = "<span style='color: red;'>Something went wrong: $error_message </span>";
							} else {
							    if($response['response']['code'] == "200"){
							    	$decoded_resp = json_decode($response['body']);
							    	if($decoded_resp->status == "success"){
							    		update_option( 'tnc_pvfw_sitekey', $decoded_resp->sitekey, $autoload = null );
							    		update_option( 'tnc_pvfw_purchase_code', $decoded_resp->purchase_code, $autoload = null );
							    		$display_result_message = '<span style="color: green;">'.$decoded_resp->message.'</span>';
							    	} else {
							    		$display_result_message = '<span style="color: red;">'.$decoded_resp->message.'</span>';
							    	}
							    	
							    }
							}
                    	}
                    	$get_sitekey = get_option("tnc_pvfw_sitekey");
                        $get_reg_status = get_transient( 'themencode-pdf-viewer-for-wordpress-registration' );
                    	if(!empty($get_sitekey) && $get_reg_status == 'active' ){
                    		echo "<h3 style='color:green;font-weight: bold;text-align: center;'>Congratulations! Your copy of TNC FlipBook - PDF viewer for WordPress is registered.</h3>";

                    		echo "<p><a id='reregister' href='#'>Click here</a> if you need to change the purchase code or register again</p>";

                    		$reg_style = "display: none;";
                    	} elseif ( $get_reg_status == 'inactive' ) {
                    		$reg_style = "display: block;";
                    	} else {
                            $reg_style = "display: block;";
                        }
                    	?>
                        <div class="register-pvfw" style="<?php echo $reg_style; ?>" id="register-pvfw">
                        	<p>
                            Please enter your purchase key below to register your copy of TNC FlipBook - PDF viewer for WordPress to get automatic updates. If you don't have a purchase code yet, you can get that by clicking <a href="https://codecanyon.net/item/pdf-viewer-for-wordpress/8182815" target="_blank">here</a>. <br><br /><strong>Please note that, Every license/purchase key of TNC FlipBook - PDF viewer for WordPress is valid for only one site.</strong> <br /></p>
                        

	                        <div class="form">
	                        	<form action="" method="POST">
	                        		<input type="text" size="60" name="purchase_code" placeholder="Enter your envato purchase key" required /> <br><br />
	                        		<a href="https://codecanyon.net/item/pdf-viewer-for-wordpress/8182815/support" target="_blank">Click here</a> to go to codecanyon product page to get purchase code <br><br />

	                        		<input type="submit" value="Register TNC FlipBook -  PDF viewer for WordPress" class="button button-primary" />
	                        	</form>
	                        </div>
	                    </div>

	                    <h3 style="text-align: center;"><?php if(!empty($display_result_message)){echo $display_result_message; } ?></h3>
                    </div><!--/.inside--> 
                </div><!--/.postbox-->
                
                <?php
                $get_site_key = get_option( 'tnc_pvfw_sitekey' );
                if(!empty($get_site_key) && $get_reg_status == 'active' ){ ?>
                <div class="postbox">
                    <h3>De-register This Site</h3>
                    <div class="inside">
                        <?php if (isset($_POST['deregister_site']) && !empty($_POST['deregister_site'])) {
                            $deregister_server = "https://updates.themencode.com/pvfw/deregister-purchase.php";

                            $dereg_response = wp_remote_post( $deregister_server, array(
                                'sslverify'   => false,
                                'method'      => 'POST',
                                'timeout'     => 45,
                                'redirection' => 5,
                                'httpversion' => '1.0',
                                'blocking'    => true,
                                'headers'     => array(),
                                'body'        => array(
                                    'site' => site_url(),
                                    'sitekey' => $get_site_key,
                                ),
                                'cookies'     => array()
                                )
                            );
                             
                            if ( is_wp_error( $dereg_response ) ) {
                                $dereg_error_message = $dereg_response->get_error_message();
                                $display_dereg_result_message = "<span style='color: red;'>Something went wrong: $dereg_error_message </span>";
                            } else {
                                if($dereg_response['response']['code'] == "200"){
                                    $decoded_dereg_resp = json_decode($dereg_response['body']);
                                    if($decoded_dereg_resp->status == "success"){
                                        update_option( 'tnc_pvfw_sitekey', '' );
                                        update_option( 'tnc_pvfw_purchase_code', '' );
                                        $display_dereg_result_message = '<span style="color: red;">'.$decoded_dereg_resp->message.'</span>';
                                    } else {
                                        update_option( 'tnc_pvfw_sitekey', '' );
                                        update_option( 'tnc_pvfw_purchase_code', '' );
                                        
                                        $display_dereg_result_message = '<span style="color: red;">Successfully Deregistered.</span>';
                                    }
                                    
                                }
                            }
                        }
                        
                        ?>
                        <div class="deregister-pvfw" id="deregister-pvfw">
                            <p>
                            Please click on the button below to de-register this site from TNC FlipBook - PDF viewer for WordPress Updates.<strong>Every license of TNC FlipBook - PDF viewer for WordPress is valid for only one site.</strong> If you need additional license keys for TNC FlipBook - PDF viewer for WordPress, you can get that by clicking <a href="https://codecanyon.net/item/pdf-viewer-for-wordpress/8182815" target="_blank">here</a> <br> <br />
                            <strong style="color: red;">Please note, You won't receive one click updates anymore after deregistering this site.</strong>
                        </p>
                        

                            <div class="form">
                                <form action="" method="POST">
                                    <input type="hidden" name="deregister_site" value="yes" />

                                    <input type="submit" value="De-Register TNC FlipBook - PDF viewer for WordPress" class="button button-warning" style="background: red;color: #fff;" />
                                </form>
                            </div>
                        </div>

                        <h3 style="text-align: center;"><?php if(!empty($display_dereg_result_message)){echo $display_dereg_result_message; } ?></h3>
                    </div><!--/.inside--> 
                </div><!--/.postbox-->
                <?php } ?>
            </div> <!-- column left -->
            <div class="tnc-pdf-column-right">
                <div class="postbox">
                    <h3>Useful Resources</h3>
                    <div class="inside">
                        <ul>
                            <li><a href="https://codecanyon.net/item/pdf-viewer-for-wordpress/8182815/">Codecanyon Plugin Page</a></li>
                            <li><a href="https://themencode.com/live-preview/pdf-viewer-for-wordpress/">Plugin Live Demo</a></li>
                            <li><a href="https://themencode.com/docs/pdf-viewer-for-wordpress/">Plugin Documentation</a></li>
                            <li><a href="http://youtube.com/channel/UC0mkhMK6fTx1BCovV6M_E4w">Video Documentations</a></li>
                            <li><a href="https://themencode.support-hub.io/">HelpDesk</a></li>
                        </ul>
                    </div><!--/inside--> 
                </div><!--/.postbox-->

                <div class="postbox">
                    <h3>Latest updates from ThemeNcode</h3>
                    <div class="inside">
                    	
                    	<?php echo themencode_news_updates(); ?>
                        
                    </div><!--/.inside--> 
                </div>
                <!--/.postbox other_plugins -->
                <!-- Subscribe -->
                <div class="postbox">
                    <h3>Stay Updated with Latest Products and News from ThemeNcode</h3>
                    <div class="inside">
                        <div class="tnp tnp-subscription">
                            <iframe src="https://eepurl.com/hx1A6H" width="100%" height="550"></iframe>
                        </div><!--/.newsletter--> 
                    </div><!--/.inside --> 
                </div><!-- /.postbox Subscribe End -->
            </div> <!-- tnc-pdf-column-right -->
        </div> <!-- postbody -->
    </div><!--poststuff-->
</div><!--/.wrap-->
<style type="text/css">
    a{
        text-decoration: none;
    }
    #poststuff h3{
        border-bottom: 1px solid #f4f4f4;
    }
    .tnp-field {
        display: block;
        clear: both;
        margin: 10px 0;
    }
    .tnp-field input[type="text"], .tnp-field input[type="email"]{
        margin-left: 0;
        width: 100%;
        display: block;
        margin: 10px 0;
    }
</style>
<script type="text/javascript">
    jQuery("#reregister").on('click', function(e){
    	e.preventDefault();
    	jQuery("#register-pvfw").toggle();
    });
</script>
<?php

namespace FSPoster\App\Providers;

abstract class SocialNetwork
{
	abstract public static function callbackURL ();

	public static function error ( $message = '', $esc_html = TRUE )
	{
        //fix esc_html
		if ( empty( $message ) )
		{
			$message = fsp__( 'An error occurred while processing your request! Please close the window and try again!' );
		}

        $message = $esc_html === TRUE ? esc_html( $message ) : $message;

		echo '<div>' . $message . '</div>';

		?>
		<script type="application/javascript">
			if ( typeof window.opener.accountAdded === 'function' )
			{
				window.opener.FSPoster.alert( "<?php echo addslashes($message); ?>" );
				window.close();
			}
		</script>
		<?php

		exit();
	}

	public static function closeWindow ()
	{
		echo '<div>' . fsp__( 'Loading...' ) . '</div>';

		?>
		<script type="application/javascript">
			if ( typeof window.opener.accountAdded === 'function' )
			{
				window.opener.accountAdded();
				window.close();
			}
		</script>
		<?php

		exit;
	}
}
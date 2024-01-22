(function( $ ) {

var $aws = $('#photos_enable_aws_s3');
var $awsId = $('#field_photos_aws_access_key_id');
var $awsKey = $('#field_photos_aws_secret_access_key');
var $awsBucket = $('#field_photos_aws_s3_bucket');
var $awsBucketLocation = $('#field_photos_aws_bucket_location');
var $awsRemoveLocalCopy = $('#field_photos_aws_s3_not_keep');

$aws.on('change', function() {
	if ( this.checked ) {
		$awsId.show();
		$awsKey.show();
		$awsBucket.show();
		$awsBucketLocation.show();
		$awsRemoveLocalCopy.show();
	} else {
		$awsId.hide();
		$awsKey.hide();
		$awsBucket.hide();
		$awsBucketLocation.hide();
		$awsRemoveLocalCopy.hide();
	}
}).triggerHandler('change');

})( jQuery );

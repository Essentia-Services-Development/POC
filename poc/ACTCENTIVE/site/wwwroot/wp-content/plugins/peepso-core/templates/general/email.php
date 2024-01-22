<!-- email sent to new users upon registration -->

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta name="viewport" content="width=device-width"/>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
		<title></title>

		<style type="text/css">
			a {
				color: #00b0ff;
				text-decoration: none;
			}
			a:hover,
			a:focus {
				color: #0092D4;
			}
		</style>
	</head>

	<body bgcolor="#ebedf0">
		<div style="background-color: #ebedf0;">
			<center style="background-color: #ebedf0;">
				<table width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width: 750px; font-family: Arial, Helvetica, sans-serif; margin: 0; padding-top: 50px; padding-bottom: 50px;">
					<tr>
						<td style="padding:30px; background: #fff; border-bottom:1px solid #eee;">
							<table width="100%" cellpadding="0" cellspacing="0" border="0" style="">
								<tr>
									<td style="font-size: 18px; text-align:center;">
										<!-- HEADER CONTENT -->
										<?php PeepSoTemplate::exec_template('general','email-header');?>
									</td>
								</tr>
							</table>
						</td>
					</tr>
					<tr>
						<td style="background-color: #fff; border-bottom: 1px solid #eee; border-top: 0; margin: 0;">
							<!-- CONTENT -->
							<div style="font-size: 14px; line-height: 20px;color: #333;padding:30px;">
								{email_contents}
							</div>
						</td>
					</tr>
					<tr>
						<td style="background-color: #333538; border-top: 0; padding: 30px; margin: 0; text-align:center;">
							<!-- FOOTER CONTENT -->
							<?php PeepSoTemplate::exec_template('general','email-footer',['user_id'=>$user_id]);?>
							<?php
							if($powered_by = PeepSo3_Helper_Addons::maybe_powered_by_peepso()) {
								echo '<div style="font-size: 12px; line-height: 20px;color: #999;margin-top:10px;">' . $powered_by . '</div>';
							}
							?>
						</td>
					</tr>
				</table>
			</center>
		</div>
	</body>
</html>

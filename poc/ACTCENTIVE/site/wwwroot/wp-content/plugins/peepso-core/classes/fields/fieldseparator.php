<?php

class PeepSoFieldSeparator extends PeepSoFieldText
{
    public static $order = 400;
	public static $admin_label='Separator';

	public static $user_disable_edit = 1;
	public static $user_disable_stats =1;
	public static $user_hide_title = 1;

	public function __construct($post, $user_id)
	{
		parent::__construct($post, $user_id);

		$this->admin_disable_validation = 1;
		$this->admin_disable_appearance = 1;
		$this->admin_disable_privacy = 1;

		// Separators don't have privacy, default to visible
		$this->acc = PeepSo::ACCESS_PUBLIC ;
		$this->can_acc = TRUE;
	}

	protected function _render()
	{
		ob_start();
		?>

		<div class="ps-form__separator ps-js-profile-separator"><?php echo $this->title;?></div>

		<?php
		return ob_get_clean();
	}

}

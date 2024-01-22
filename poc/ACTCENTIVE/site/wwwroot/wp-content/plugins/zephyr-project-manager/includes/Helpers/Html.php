<?php

/**
 * @package ZephyrProjectManager
 */

namespace ZephyrProjectManager\Helpers;

if (!defined('ABSPATH')) die;

use \DateTime;
use ZephyrProjectManager\Core\Members;
use ZephyrProjectManager\Core\Projects;
use ZephyrProjectManager\ZephyrProjectManager;

class Html {

	public static function inputField($label = '', $value = '', $id = '', $classes = '', $atts = []) {
		ob_start();
		?>

		<div class="zpm-form__group">
			<input type="text" name="<?php echo esc_attr($id); ?>" id="<?php echo esc_attr($id); ?>" class="zpm-form__field" placeholder="<?php echo wp_kses_post($label); ?>" value="<?php echo esc_attr($value); ?>" <?php foreach($atts as $key => $att) { echo esc_attr($key) . '="' . esc_attr($att) . '"'; } ?> />
			<label for="<?php echo esc_attr($id); ?>" class="zpm-form__label"><?php echo wp_kses_post($label); ?></label>
		</div>

		<?php
		$html = ob_get_clean();
		return $html;
	}

	public static function textarea($label = '', $value = '', $id = '', $classes = '', $atts = []) {
		ob_start();
		?>

		<div class="zpm-form__group">
			<textarea type="text" name="<?php echo esc_attr($id); ?>" id="<?php echo esc_attr($id); ?>" class="zpm-form__field" placeholder="<?php echo esc_attr($label); ?>" <?php foreach($atts as $key => $att) { echo esc_attr($key) . '="' . esc_attr($att) . '"'; } ?>><?php echo esc_textarea($value); ?></textarea>
			<label for="<?php echo esc_attr($id); ?>" class="zpm-form__label"><?php echo wp_kses_post($label); ?></label>
		</div>

		<?php
		$html = ob_get_clean();
		return $html;
	}

	public static function hiddenField($value = '', $id = '', $classes = '', $atts = []) {
		ob_start();
		?>

		<div class="zpm-form__group">
			<input type="hidden" name="<?php echo esc_attr($id); ?>" id="<?php echo esc_attr($id); ?>" value="<?php echo esc_attr($value); ?>" <?php foreach($atts as $key => $att) { echo esc_attr($key) . '="' . esc_attr($att) . '"'; } ?> />
		</div>

		<?php
		$html = ob_get_clean();
		return $html;
	}

	public static function selectField($label = '', $val = '', $multiple = false, $options = [], $atts = [], $id = '', $classes = '') {
		ob_start();
		?>

		<div class="zpm-select__container">
			<label class="zpm_label" for="<?php echo esc_attr($id); ?>"><?php echo wp_kses_post($label); ?></label>
			<select id="<?php echo esc_attr($id); ?>" <?php echo $multiple ? 'multiple' : ''; ?> data-placeholder="<?php echo esc_attr($label); ?>" class="zpm-select <?php echo esc_attr($classes); ?> <?php echo $multiple ? 'zpm-multi-select' : ''; ?>" <?php foreach($atts as $key => $att) { echo esc_attr($key) . '="' . esc_attr($att) . '"'; } ?>>
				<?php foreach ($options as $key => $value) : ?>
					<option value="<?php echo esc_attr($key); ?>" <?php echo $key == $val ? 'selected' : ''; ?> <?php echo is_array($val) && in_array($key, $val) ? 'selected' : ''; ?> ><?php echo wp_kses_post($value); ?></option>
				<?php endforeach; ?>
			</select>
		</div>

		<?php
		$html = ob_get_clean();
		return $html;
	}

	public static function label($value = '', $for = '') {
		ob_start();
		?>
		<label class="zpm_label" for="<?php echo esc_attr($for); ?>"><?php echo wp_kses_post($value); ?></label>
		<?php
		$html = ob_get_clean();
		return $html;
	}

	public static function memberSelectField( $args = [] ) {
		$defaults = [
			'id' => 'zpm-member-select',
			'name' => 'member-select',
			'title' => 'Assignee',
			'value' => []
		];
		$args = wp_parse_args( $args, $defaults );
		ob_start();
		?>
		<?php
			$members = Members::get_zephyr_members();
			$options = [];

			foreach ($members as $member) {
				$options[$member['id']] = $member['name'];
			}

			$atts = [
				'name' => $args['name']
			];
		?>
		<?php echo Html::selectField( $args['title'], $args['value'], true, $options, $atts, $args['id'] ); ?>

		<?php
		$html = ob_get_clean();
		return $html;
	}

	public static function projectSelectField( $args = [] ) {
		$defaults = [
			'id' => 'zpm-member-select',
			'name' => 'member-select',
			'title' => 'Project',
			'value' => ''
		];

		$args = wp_parse_args( $args, $defaults );
		ob_start();
		?>

		<?php
			$manager = ZephyrProjectManager::get_instance();
			$projects = $manager::get_projects();
			$options = [];
			$options['-1'] = __( 'None', 'zephyr-project-manager' );

			foreach ($projects as $project) {
				if (Projects::has_project_access($project)) {
					$options[$project->id] = esc_html($project->name);
				}
			}

			$atts = [
				'name' => $args['name']
			];
		?>
		<?php echo Html::selectField( $args['title'], $args['value'], false, $options, $atts, $args['id'] ); ?>

		<?php
		$html = ob_get_clean();
		return $html;
	}

	public static function teamSelectField( $args = [] ) {
		$defaults = [
			'id' => 'zpm-team-select',
			'name' => 'team-select',
			'title' => 'Team',
			'value' => ''
		];

		$args = wp_parse_args( $args, $defaults );
		ob_start();
		?>
		<?php
			$teams = Members::get_teams();
			$options = [];
			$options['-1'] = __( 'None', 'zephyr-project-manager' );

			foreach ($teams as $team) {
				$options[$team['id']] = $team['name'];
			}

			$atts = [
				'name' => $args['name']
			];
		?>
		<?php echo Html::selectField( $args['title'], $args['value'], false, $options, $atts, $args['id'] ); ?>

		<?php
		$html = ob_get_clean();
		return $html;
	}
}
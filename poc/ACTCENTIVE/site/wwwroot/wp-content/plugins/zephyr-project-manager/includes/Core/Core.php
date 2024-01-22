<?php

/**
* @package ZephyrProjectManager
*/

namespace ZephyrProjectManager\Core;

if ( !defined( 'ABSPATH' ) ) {
	die;
}

use ZephyrProjectManager\Api\Emails;
use ZephyrProjectManager\Core\Projects;

class Core {

	public function register( ) {
		if (zpmIsProjectsPage()) {
			add_filter('zpm_after_quickmenu', [$this, 'projectsPageMenuOptions']);
		}

		if (zpmIsTasksPage()) {
			add_filter('zpm_after_quickmenu', [$this, 'tasksPageMenuOptions']);
		}

		if (zpmIsCalendarPage()) {
			add_filter('zpm_after_quickmenu', [$this, 'calendarPageMenuOptions']);
		}

		add_filter('zpm_category_projects', [$this, 'filterCategoryProjects']);
		add_action('zpm_project_completed', [$this, 'projectCompleted']);
		add_action('zpm_project_assigned', [$this, 'projectAssigned'], 10, 2);
		add_action('zpm_new_task_settings', [$this, 'newTaskFields']);
		add_action('set_user_role', [$this, 'onUserRoleChanged'], 10, 3);
	}

	public function onUserRoleChanged($userID, $role, $oldRoles)  {
		if (in_array($role, Utillities::getCustomRoles())) {
			Utillities::update_user_access($userID, true);
		}
	}

	public function newTaskFields() {
		?>
		<!-- <input type="hidden" data-ajax-name="milestone" value="" data-default="" /> -->
		<?php
	}

	public function projectCompleted( $project ) {
		$managers = Members::getManagers();

		$header = __( 'Project Completed', 'zephyr-project-manager' );
		$subject = __( 'Project Completed', 'zephyr-project-manager' );
		$body = sprintf( __( 'Project "%s" has been completed.', 'zephyr-project-manager' ), esc_html($project->name) );
		$footer = '';

		$html = Emails::email_template( $header, $body, $footer );

		foreach ($managers as $manager) {
			Emails::send_email( $manager['email'], $subject, $html );
		}
	}

	public function projectAssigned( $project, $assignees ) {
		$managers = Members::getManagers();

		$header = __( 'Project Assigned to You', 'zephyr-project-manager' );
		$subject = __( 'Project Assigned to You', 'zephyr-project-manager' );
		$body = sprintf( __( 'Project "%s" has been assigned to you.', 'zephyr-project-manager' ), esc_html($project->name) );
		$footer = '';
		$html = Emails::email_template( $header, $body, $footer );
		$sent = [];

		foreach ($assignees as $assignee) {
			if (isset($assignee['email'])) {
				if (in_array($assignee['email'], $sent)) continue;

				Emails::send_email( $assignee['email'], $subject, $html );
				$sent[] = $assignee['email'];
			}
		}
	}

	public function filterCategoryProjects($projects) {
		$results = [];

		foreach ($projects as $project) {
			if (Projects::has_project_access($project)) {
				$results[] = $project;
			}
		}

		return $results;
	}

	public function projectsPageMenuOptions( $content ) {
		$content .= "<li class='zpm_fancy_item zpm-export-projects__btn'>" . __( 'Export Projects', 'zephyr-project-manager' ) . "</li>";
		$content .= "<li class='zpm_fancy_item zpm-import-projects__btn'>" . __( 'Import Projects', 'zephyr-project-manager' ) . "</li>";
      	return $content;
	}

	public function tasksPageMenuOptions( $content ) {
		$content .= "<li class='zpm_fancy_item zpm-export-tasks__btn'>" . __( 'Export Tasks', 'zephyr-project-manager' ) . "</li>";
		$content .= "<li class='zpm_fancy_item zpm-import-tasks__btn'>" . __( 'Import Tasks', 'zephyr-project-manager' ) . "</li>";
      	return $content;
	}

	public function calendarPageMenuOptions($content) {
		$content .= "<li class='zpm_fancy_item' data-import-ical-button>" . __( 'Import iCal File', 'zephyr-project-manager' ) . "</li>";
      	return $content;
	}
}

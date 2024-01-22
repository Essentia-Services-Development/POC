jQuery(document).ready(function () {

	if (ZephyrProjects.isProjectsPage()) {
		$ = jQuery;
		var selector = 'zpm-task-list__project';

		if (!ZephyrProjects.isAdmin()) {
			selector = 'zpm-project-task-list';
		}

		var drake = dragula({
			copySortSource: true
		});

		var container = document.getElementById(selector);
		drake.containers.push(container);
		var projectId = jQuery('#zpm-project-id').val();

		drake.on('dragend', function (one, two, three) {
			var taskIdsOrder = [];
			var parentList = jQuery('#' + selector);

			parentList.find('.zpm_task_list_row').each(function () {
				var taskId = $(this).data('task-id');
				taskIdsOrder.push(taskId);
			});

			ZephyrProjects.ajax({
				action: 'zpm_updateProjectSetting',
				project_id: projectId,
				key: 'task_order',
				value: taskIdsOrder
			}, function (res) {

			});
		});
	}

	jQuery('body').on('click', '[data-upload-task-file-button]', function () {
		let zpm_file_uploader = null;
		ZephyrProjects.upload_file(zpm_file_uploader, function (res) {
			ZephyrProjects.notification(zpm_localized.strings.uploading_files);
			console.log('zpm/files', res);

			var taskId = $('body').find('#zpm-task-id').val();
			var attachments = res;

			for (var i = 0; i < attachments.length; ++i) {
				var atts = attachments[i].attributes;

				ZephyrProjects.upload_attachment(atts.url, 'task', taskId, function (res) {
					console.log('zpm/upload', res);
					jQuery('body').find('[data-no-files-message]').remove();
					jQuery('body').find('.zpm-files__container').append(res.html);
					jQuery('body').find('[data-no-files-message]').remove();
				});
				// zpm-kanban-attachments
				if (ZephyrProjects.is_image(atts.url) || ZephyrProjects.is_pdf(atts.url)) {
					var html = '<span class="zpm-kanban-attachment zpm-dropdown-item" data-attachment="' + atts.url + '" target="_blank"><span class="zpm-kanban-attachment__url">' + atts.filename + '</span><span class="zpm-kanban-attachment__action">' + zpm_localized.strings.view_attachment + '</span></span>';
					jQuery('.zpm_kanban_item[data-task-id="' + taskId + '"]').find('.zpm-kanban-attachments').append(html);
				} else {
					var html = '<a class="zpm-kanban-attachment zpm-dropdown-item" href="' + atts.url + '" download target="_blank"><span class="zpm-kanban-attachment__url">' + atts.filename + '</span><span class="zpm-kanban-attachment__action">' + zpm_localized.strings.download_file + '</span></a>';
					jQuery('.zpm_kanban_item[data-task-id="' + taskId + '"]').find('.zpm-kanban-attachments').append(html);
				}
			}
		}, true);
	});

	jQuery('body').on('click', '[data-archive-task]', function () {
		const id = jQuery(this).data('task-id');
		const archived = jQuery(this).attr('data-archived');
		let isArchived = false;

		if (archived == 'true' && archived !== false) {
			isArchived = true;
		}

		if (isArchived) {
			jQuery(this).attr('data-archived', 'false');
			jQuery(this).text(zpm_localized.strings.archive);
		} else {
			jQuery(this).attr('data-archived', 'true');
			jQuery(this).text(zpm_localized.strings.unarchive);
		}

		ZephyrProjects.archiveTask(id, !isArchived, function () {
		});
	});

	jQuery('body').on('click', '.zpm-remove-parent', function () {
		jQuery(this).parent().remove();
	});

	jQuery('body').on('change', '[data-task-duration]', function () {
		const taskID = jQuery(this).data('task-duration');
		const duration = jQuery(this).val();

		if (typeof taskID !== 'undefined') {
			ZephyrProjects.updateTaskMeta(taskID, 'duration', duration);
		}
	});

	jQuery('body').on('click', '[data-print-project-pdf-button]', function() {
		const id = jQuery(this).data('print-project-pdf-button');
		const $btn = jQuery(this);
		$btn.text($btn.data('loading-text'));
		const $loader = ZephyrProjects.loaderNotification(zpm_localized.strings.downloading);

		ZephyrProjects.ajax({
			action: 'zpm_getProjectOverview',
			id
		}, function(res) {
			jQuery('body').append(res.data.html);
			const $el = jQuery('body').find('[data-project-overview="' + id + '"]');
			const name = $el.find('[data-project-overview-name]').text();
			$btn.text($btn.data('text'));
			// $el.hide();
			$el.css('display', 'flex');
			ZephyrProjects.createPDF($el[0], name, function(res) {
				$loader.remove();
				console.log('zpm/pdf/done', $el);
				$el.remove();
			});
		});
	});

	// Remove project from dashboard
	jQuery('body').on('click', '#zpm-remove-from-dashboard', function (e) {
		e.stopPropagation();
		e.preventDefault();
		var parent = jQuery(this).closest('.zpm_project_item');
		var projectId = parent.data('project-id');
		parent.closest('.zpm_project_grid_cell').remove();
		ZephyrProjects.ajax({
			action: 'zpm_removeProjectFromDashboard',
			id: projectId
		}, function () { });
	});

	let zpmIsBulkEditing = false;

	jQuery('body').on('click', '[data-toggle-task-bulk-selection]', function () {
		zpmIsBulkEditing = !zpmIsBulkEditing;

		if (!zpmIsBulkEditing) {
			jQuery('body').find('[data-task-bulk-selector]').removeAttr('checked');
			jQuery(this).text(zpm_localized.strings.select_multiple);
		} else {
			jQuery(this).text(zpm_localized.strings.deselect_all);
		}

		jQuery('body').toggleClass('zpm-task-list--bulk-edit');
	});

	jQuery('body').on('click', '[data-bulk-delete-button]', function () {
		const selectedTasks = zpmGetSelectedTasks();

		if (selectedTasks.length == 0) {
			ZephyrProjects.notification(zpm_localized.strings.nothing_selected);
		} else {
			// Perform here.
			if (confirm('Are you sure that you would like to permanently delete the ' + selectedTasks.length + ' selected tasks?')) {
				ZephyrProjects.ajax({
					action: 'zpm_bulkDeleteTasks',
					tasks: selectedTasks
				}, function (res) {

				});
				jQuery.each(selectedTasks, function (key, id) {
					jQuery('body').find('.zpm_task_list_row[data-task-id="' + id + '"]').remove();
				});
				jQuery('body').find('[data-toggle-task-bulk-selection]').click();
			}
		}
	});

	jQuery('body').on('click', '[data-bulk-archive-button]', function () {
		const selectedTasks = zpmGetSelectedTasks();

		if (selectedTasks.length == 0) {
			ZephyrProjects.notification(zpm_localized.strings.nothing_selected);
		} else {
			// Perform here.
			if (confirm('Are you sure that you would like to archive the ' + selectedTasks.length + ' selected tasks?')) {
				ZephyrProjects.ajax({
					action: 'zpm_bulkArchiveTasks',
					tasks: selectedTasks
				}, function (res) {

				});
				jQuery.each(selectedTasks, function (key, id) {
					jQuery('body').find('.zpm_task_list_row[data-task-id="' + id + '"]').remove();
				});
				jQuery('body').find('[data-toggle-task-bulk-selection]').click();
			}
		}
	});

	jQuery('body').on('click', '[data-bulk-edit-button]', function () {
		const selectedTasks = zpmGetSelectedTasks();

		if (selectedTasks.length == 0) {
			ZephyrProjects.notification(zpm_localized.strings.nothing_selected);
		} else {
			// Perform here.
			ZephyrProjects.open_modal('zpm-edit-multiple-tasks-modal');
		}
	});

	jQuery('body').on('click', '[data-create-task-list]', function () {
		const items = [];

		jQuery('body').find('[data-task-list]').find('[data-task-list-item]').each(function () {
			items.push(jQuery(this).val());
		});

		ZephyrProjects.close_modal('#zpm-new-task-list-modal');

		const $list = jQuery('body').find('#zpm-new-task-list-modal [data-task-list]');
		$list.html('');
		const itemHtml = jQuery('body').find('[data-zpm-template="taskListItem"]').html();
		$list.append(itemHtml);

		const $assignees = jQuery('body').find('#zpm-task-list-assignees');
		const $project = jQuery('body').find('#zpm-task-list-project');

		ZephyrProjects.ajax({
			action: 'zpm_createTaskList',
			list: items,
			assignees: $assignees.val(),
			project: $project.val()
		}, function (res) {
			jQuery('body').find('#zpm-task-list__tasks').prepend(res.data.html);
			$assignees.val('').trigger('chosen:updated');
			$project.val('-1').trigger('chosen:updated');
		});
	});

	jQuery('body').on('click', '[data-update-multiple-tasks-button]', function () {
		const selectedTasks = zpmGetSelectedTasks();
		const values = {};
		const $modal = jQuery(this).closest('.zpm-modal');
		const $selectedFields = $modal.find('[data-edit-multiple-task-container].active');

		$selectedFields.each(function () {
			const $field = jQuery(this).find('[data-edit-multiple-task-input]');
			const key = $field.data('edit-multiple-task-input');
			values[key] = $field.val();
		});

		// ZephyrProjects.close_modal('#zpm-edit-multiple-tasks-modal');
		jQuery(this).text(zpm_localized.strings.saving_changes);

		ZephyrProjects.ajax({
			action: 'zpm_bulkUpdateTasks',
			tasks: selectedTasks,
			values: values
		}, function (res) {
			window.location.reload();
		});
	});

	jQuery('body').on('keydown', '[data-task-list-item]', function (e) {
		const $taskList = jQuery('body').find('[data-task-list]');
		const $lastItem = $taskList.find('[data-task-list-item]').last();
		const isLast = jQuery(this).is($lastItem);
		const $container = jQuery(this).closest('.zpm-form__group');

		// Add New Item
		if ((e.keyCode == 10 || e.keyCode == 13) && !e.ctrlKey) {
			const itemHtml = jQuery('body').find('[data-zpm-template="taskListItem"]').html();
			$container.after(itemHtml);
			const $newElement = $container.next().find('[data-task-list-item]');

			setTimeout(function () {
				$newElement.focus();
			}, 100);
		}

		// Delete Item
		if (e.which == 46) {
			jQuery(this).closest('.zpm-form__group').remove();

			setTimeout(function () {
				$taskList.find('[data-task-list-item]').last().focus();
			}, 100);
		}
	});

	jQuery('body').on('click', '[data-add-task-list-button]', function (e) {
		const project = jQuery(this).data('project');
		ZephyrProjects.open_modal('zpm-new-task-list-modal');
		const $taskList = jQuery('body').find('[data-task-list]');
		$taskList.find('[data-task-list-item]').first().focus();

		if (typeof project !== 'undefined') {
			jQuery('body').find('#zpm-task-list-project').val(project).trigger('chosen:updated');
		}
	});

	jQuery('body').on('click', '[data-edit-multiple-task-toggle]', function () {
		const $container = jQuery(this).closest('[data-edit-multiple-task-container]');
		$container.toggleClass('active');
	});

	jQuery('body').on('click', '[data-import-ical-button]', function () {
		ZephyrProjects.upload_file(null, function (res) {
			ZephyrProjects.ajax({
				action: 'zpm_importIcal',
				url: res.url
			}, function (res) {
				console.log('imported', res.data.tasks);
				ZephyrProjects.notification('Successfully imported ' + res.data.tasks.length + ' tasks.');
				window.location.reload();
			});
		});
	});

	// jQuery('body').on('change', '[data-filter-tasks-status]', function () {
	// 	const status = jQuery(this).val();
	// 	const currentFilter = jQuery('body').find('.zpm_selection_option.zpm_nav_item_selected').data('zpm-filter');
	// 	const data = {
	// 		action: 'zpm_filter_tasks_by',
	// 		filter: 'status',
	// 		status
	// 	};

	// 	if (typeof currentFilter !== 'undefined') {
	// 		data.current_filter = currentFilter;
	// 	}

	// 	ZephyrProjects.ajax(data, function (res) {
	// 		console.log('zpm/status/tasks', res);
	// 		jQuery('body').find('#zpm-task-list__tasks').html(res.html);
	// 	});
	// });

	jQuery('#zpm-task-preview__info').on('change', 'input, select, textarea', function () {
		ZephyrProjects.saveTaskPanel();
		ZephyrProjects.notification('Changes Saved');
	});

	jQuery('#zpm-project-preview__info').on('change', 'input, select, textarea', function () {
		if (!jQuery(this).hasClass('zpm-panel__no-update')) {
			ZephyrProjects.saveProjectPanel();
			ZephyrProjects.notification('Changes Saved');
		}
	});

	jQuery('body').on('click', function (e) {
		var target = jQuery(e.target);
		if (target.closest('#zpm-project-preview__bar').length <= 0 && target.closest('.zpm_project_item').length <= 0) {
			ZephyrProjects.closeProjectPanel();
		}

		if (target.closest('#zpm-task-preview__bar').length <= 0 && target.closest('.zpm_task_list_row').length <= 0) {
			ZephyrProjects.closeTaskPanel();
		}
	});

	const $zpmSubtaskContainers = jQuery('body').find('.zpm-subtasks-container');

	if ($zpmSubtaskContainers.length > 0) {
		const zpmSubtaskDragger = dragula([document.querySelector('.zpm-subtasks-container')], {
			drag: function(el, source) {

			},
			drop: function(el, source) {

			}
		});

		zpmSubtaskDragger.on('dragend', function(el){
			const $subtaskList = jQuery(el).closest('#zpm-subtask-list');
			const $subtasks = $subtaskList.find('.zpm_subtask_item:not(.zpm-subtask-item--sub)');
			const positions = {};
			const taskID = $subtaskList.data('task-id');
			$subtaskList.find('#zpm-no-subtasks').remove();

			$subtasks.each(function() {
				const id = jQuery(this).data('zpm-subtask');
				positions[jQuery(this).index()] = id;
			});
			console.log('zpm/subtasks/dragend', el, $subtaskList, $subtasks, positions);
			ZephyrProjects.ajax({
				action: 'zpm_updateSubtaskOrder',
				taskID,
				positions,
			}, function(res) {

			});
		});
	}

	jQuery('body').on('input', '#zpm-edit-task--duration', function() {
		let duration = jQuery(this).val();
		const start = jQuery('body').find('#zpm_edit_task_start_date').val();

		if (start !== '') {
			if (duration == 0) duration = 1;
			const newDate = moment(start).add(duration - 1, 'days').format('YYYY-MM-DD');
			console.log('zpm/duration/new_due_date', newDate);
			jQuery('body').find('#zpm_edit_task_due_date').val(newDate).trigger('change');
		}

	  console.log('zpm/duration/changed', duration, start);
	});

	jQuery('body').on('change', '#zpm_edit_task_due_date', function() {
		const $due = jQuery(this);
		const $start = jQuery('body').find('#zpm_edit_task_start_date');
		let due = jQuery(this).val();
		let start = $start.val();
		const $duration = jQuery('body').find('#zpm-edit-task--duration');

		if (due !== '') {
			start = moment(start);
			due = moment(due);
			let duration = due.diff(start, 'days');
			duration += 1;

			if (duration <= 0) {
				if ($duration.val() !== '') {
					duration = $duration.val();

					if (isNaN(duration)) duration = 0;

					$start.val(due.subtract(duration, 'days').format('YYYY-MM-DD'));
				}
			}

			if (isNaN(duration)) duration = 0;

			$duration.val(duration);
			// $start.val(due.add(duration, 'days').format('YYYY-MM-DD'));
			console.log('zpm/due_date/changed', duration, start);
		}
	});

	jQuery('body').on('change', '#zpm_edit_task_start_date', function () {
		const $start = jQuery(this);
		const $due = jQuery('body').find('#zpm_edit_task_due_date');
		let due = $due.val();
		let start = jQuery(this).val();
		const $duration = jQuery('body').find('#zpm-edit-task--duration');

		if (start !== '') {
			start = moment(start);
			due = moment(due);
			let duration = due.diff(start, 'days');
			duration += 1;

			if (duration <= 0) {
				if ($duration.val() !== '') {
					duration = $duration.val();

					if (isNaN(duration)) duration = 0;

					$due.val(start.add(duration, 'days').format('YYYY-MM-DD'));
				}
			}

			if (isNaN(duration)) duration = 0;

			$duration.val(duration);

			console.log('zpm/due_date/changed', duration, start);
		}
	});

	jQuery('body').on('change', '#zpm_edit_task_due_date', function() {
		const today = moment();
		const val = jQuery(this).val();
		const $estimated = jQuery('body').find('[data-task-estimated-end]');

		if (val === '' || $estimated.length === 0) return;

		const date = moment(val);

		if (date.isBefore(today)) {
			$estimated.addClass('zpm-overdue-text');
		} else {
			$estimated.removeClass('zpm-overdue-text');
		}

		$estimated.text(date.format('D MMM YYYY'));
	});

	jQuery('body').on('input', '#zpm-new-task-duration', function () {
		let duration = jQuery(this).val();
		const start = jQuery('body').find('#zpm_new_task_start_date').val();

		if (start !== '') {
			if (duration == 0) duration = 1;
			const newDate = moment(start).add(duration - 1, 'days').format('YYYY-MM-DD');
			console.log('zpm/duration/new_due_date', newDate);
			jQuery('body').find('#zpm_new_task_due_date').val(newDate).trigger('change');
		}

		console.log('zpm/duration/changed', duration, start);
	});

	jQuery('body').on('change', '#zpm_new_task_due_date', function () {
		let due = jQuery(this).val();
		let start = jQuery('body').find('#zpm_new_task_start_date').val();

		if (due !== '') {
			start = moment(start);
			due = moment(due);
			let duration = due.diff(start, 'days');
			duration += 1;

			if (duration < 0) return;

			if (isNaN(duration)) duration = 0;

			jQuery('body').find('#zpm-new-task-duration').val(duration).trigger('change');
			console.log('zpm/due_date/changed', duration, start);
		}
	});

	jQuery('body').on('change', '#zpm_new_task_start_date', function () {
		let due = jQuery('body').find('#zpm_edit_task_due_date').val();
		let start = jQuery(this).val();

		if (due !== '') {
			start = moment(start);
			due = moment(due);
			let duration = due.diff(start, 'days');
			duration += 1;

			if (duration < 0) return;

			if (isNaN(duration)) duration = 0;

			jQuery('body').find('#zpm-new-task-duration').val(duration).trigger('change');
			console.log('zpm/due_date/changed', duration, start);
		}
	});

	jQuery('body').on('change', '[data-ajax-name="blockingTasks"]', function () {
		const $select = jQuery(this);
		const dates = [];
		const selectVal = $select.val();

		$select.find('[data-due]').each(function () {
			const val = jQuery(this).val();
			const due = jQuery(this).data('due');
			const isSelected = jQuery.inArray(val, selectVal) !== -1;

			if (isSelected && due !== '') {
				dates.push(moment(due));
			}
		});

		const latest = moment.max(dates);

		if (dates.length !== 0 && typeof latest !== 'undefined' && latest !== null) {
			console.log('zpm/blocking/changed', {
				latest, dates
			});
			const $startDate = jQuery('body').find('#zpm_edit_task_start_date');
			let startDate = $startDate.val();

			if (startDate !== '') {
				startDate = moment(startDate);

				if (latest.isBefore(startDate)) {
					console.log('zpm/blocking', 'Start date is already later than the latest blocking task');
					return;
				}
			}

			$startDate.val(latest.add(1, 'days').format('YYYY-MM-DD'));
			jQuery('body').find('#zpm-edit-task--duration').trigger('input').trigger('keydown');
		}
	});

	jQuery('body').on('change', '#zpm-edit-task__status', function() {
		const $status = jQuery(this);
		const original = $status.attr('data-original');
		const $start = jQuery('body').find('#zpm_edit_task_start_date');
		const originalStart = $start.data('original');
		const today = moment().format('YYYY-MM-DD');

		if (typeof original == 'undefined') return;
		if (typeof originalStart == 'undefined') return;

		console.log('zpm/status', original, originalStart, today);

		if (original == 'not_started' && today !== originalStart) {
			if (confirm(zpm_localized.strings.automatically_update_dates)) {
				$start.val(today).trigger('change');
				$status.attr('data-original', $status.val()).prop('data-original', $status.val());
			}
		}
	});

	const $autoResizeTextareas = jQuery('body').find('textarea.zpm-auto-resize');

	$autoResizeTextareas.each(function() {
		const textarea = jQuery(this)[0];
		textarea.style.height = 'auto';
		textarea.style.height = textarea.scrollHeight + 'px';
	});

	jQuery('body').on('click', '[data-submit-task-export-button]', function() {
		const $options = jQuery('body').find('[data-task-export-options]');
		const data = {};
		data.headers = [];
		data.from = $options.find('[data-from]').val();
		data.to = $options.find('[data-to]').val();
		data.exportNames = $options.find('[data-export-names]').is(':checked');

		$options.find('[data-header]').each(function() {
			if (jQuery(this).is(':checked')) {
				data.headers.push(jQuery(this).data('header'))
			}
		});

		console.log('zpm/task/export/options', data);


		const $btn = jQuery(this);
		$btn.text(zpm_localized.strings.loading);

		ZephyrProjects.ajax({
			action: 'zpm_exportTasksToCSV',
			options: data
		}, function (response) {
			ZephyrProjects.remove_modal('task-export-modal');
			ZephyrProjects.close_modal();
			var link = document.createElement('a');
			document.body.appendChild(link);
			link.download = 'ZPM Tasks.csv';
			link.href = response;
			link.click();
		});
	});

	jQuery('body').on('input', 'textarea.zpm-auto-resize', function() {
		const textarea = jQuery(this)[0];
		textarea.style.height = 'auto';
		textarea.style.height = textarea.scrollHeight + 'px';
	});

	jQuery('body').on('change', '#zpm-edit-project__status', function() {
		const $status = jQuery(this);
		const original = $status.attr('data-original');
		const $start = jQuery('body').find('#zpm_edit_project_start_date');
		const originalStart = $start.data('original');
		const today = moment().format('YYYY-MM-DD');

		if (typeof original == 'undefined') return;
		if (typeof originalStart == 'undefined') return;

		console.log('zpm/status', original, originalStart, today);

		if (original == 'not_started' && today !== originalStart) {
			if (confirm(zpm_localized.strings.automatically_update_dates)) {
				$start.val(today).trigger('change');
				$status.attr('data-original', $status.val()).prop('data-original', $status.val());
			}
		}
	});

	/* Text Editor */
	jQuery('.zpm_editor_toolbar a').click(function (e) {
		e.preventDefault();
		e.stopPropagation();

		var command = $(this).data('command');

		if (command == 'h1' || command == 'h2' || command == 'p') {
			document.execCommand('formatBlock', false, command);
		}

		if (command == 'forecolor' || command == 'backcolor') {
			document.execCommand(jQuery(this).data('command'), false, $(this).data('value'));
		}

		if (command == 'createlink' || command == 'insertimage') {
			url = prompt('Enter the link here: ', 'http:\/\/');
			document.execCommand(jQuery(this).data('command'), false, url);
		}

		if (command == 'addCode') {
			document.execCommand("insertHTML", false, "<code class='cca_code_snippet' style='display: block;'>" + document.getSelection() + "</code>");
		} else {
			document.execCommand(jQuery(this).data('command'), false, null);
		}
	});

	jQuery('body').on('change', '[data-zpm-project-sorting]', function () {
		// const sortingMethod = jQuery(this).val();
		const page = jQuery('body').find('.zpm-pagination__current-page').data('page');
		ZephyrProjects.paginateProjects(page, zpm_localized.settings.projects_per_page, zpm_localized.is_admin == '');

		// ZephyrProjects.ajax({
		// 	action: 'zpm_sortProjects',
		// 	sortingMethod: sortingMethod,
		// 	page: page
		// }, function() {

		// });
	});

	jQuery('body').find('[data-zpm-tooltip]').each(function() {
		jQuery(this).append('<div class="zpm-tooltip">' + jQuery(this).data('zpm-tooltip') + '</div>');
		jQuery(this).addClass('zpm-tooltip-container');
	});

	jQuery(document).on('keydown', function (event) {
		const $activeModal = jQuery('body').find('.zpm-modal.active');
		const $saveTaskBtn = jQuery('body').find('#zpm_save_changes_task');
		const hasActiveModal = $activeModal.length > 0;
		const isTaskPage = $saveTaskBtn.length > 0;

		// ESCAPE shortcut
		if (event.key == "Escape") {
			if (hasActiveModal) {
				ZephyrProjects.close_modal();
			} else {
				if (isTaskPage) {
					window.history.back();
				}
			}
			// if (ZephyrProjects.isModalOpen('zpm_create_task')) {
			// 	ZephyrProjects.close_modal('#zpm_create_task');
			// }

			// if (ZephyrProjects.isModalOpen('zpm_project_modal')) {
			// 	ZephyrProjects.close_modal('#zpm_project_modal');
			// }
		}

		// CTRL + ENTER Shortcut
		if ((event.keyCode == 10 || event.keyCode == 13) && event.ctrlKey) {
			if (hasActiveModal) {
				$activeModal.find('button[type="submit"]').last().click();
			} else {
				if (isTaskPage) {
					$saveTaskBtn.click();
				}
			}
		}
	});
});

function zpmGetSelectedTasks() {
	const selectedTasks = jQuery('body').find('[data-task-bulk-selector]:checked');
	const selectedIDs = [];

	selectedTasks.each(function () {
		selectedIDs.push(jQuery(this).data('task-bulk-selector'));
	});

	return selectedIDs;
}

jQuery(document).on('heartbeat-send', function (event, data) {
	data.zpm_comment_task_id = jQuery('body').find('#zpm-task-id').val();
	data.zpm_comment_project_id = jQuery('body').find('#zpm-project-id').val();
});

jQuery(document).on('heartbeat-tick', function (event, data) {
	if (data.zpm_comment_task_id) {
		jQuery('body').find('.zpm_task_comments[data-task-id="' + data.zpm_comment_task_id + '"]').html(data.comments_html);
	}
	
	if (data.zpm_comment_project_id) {
		jQuery('body').find('.zpm_task_comments[data-project-id="' + data.zpm_comment_project_id + '"]').html(data.comments_html);
	}
});
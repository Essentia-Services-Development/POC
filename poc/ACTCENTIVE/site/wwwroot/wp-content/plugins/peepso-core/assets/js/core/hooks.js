function createHooks() {
	/**
	 * Callbacks table.
	 *
	 * @type {Object.<string, Array>}
	 */
	let hooks = {};

	/**
	 * Adds a callback for a hook type.
	 *
	 * @param {string} hookName
	 * @param {string} callbackName
	 * @param {Function} callback
	 * @param {?number} priority
	 */
	function addHook(hookName, callbackName, callback, priority = 10) {
		if ('function' !== typeof callback) {
			throw new Error('The callback parameter must be a function.');
		}

		if ('number' !== typeof priority) {
			throw new Error('The priority parameter must be a number.');
		}

		let callbacks = hooks[hookName];
		if (!callbacks) {
			callbacks = hooks[hookName] = [];
		}

		// Finds the correct index to put the callback in the callback list.
		let index = callbacks.length;
		for (; index > 0; index--) {
			if (priority >= callbacks[index - 1].priority) break;
		}

		// Inserts callback in the callback list.
		callbacks.splice(index, 0, { name: callbackName, callback, priority });
	}

	/**
	 * Removes all callbacks for a hook type. If second parameter is provided,
	 * only remove callbacks with the same name.
	 *
	 * @param {string} hookName
	 * @param {?string} callbackName
	 */
	function removeHooks(hookName, callbackName) {
		if ('undefined' === typeof callbackName) {
			delete hooks[hookName];
			return;
		}

		let callbacks = hooks[hookName];
		if (callbacks) {
			for (let i = callbacks.length - 1; i >= 0; i--) {
				if (callbacks[i].name === callbackName) {
					callbacks.splice(i, 1);
				}
			}

			// Removes empty callback list.
			if (!callbacks.length) {
				delete hooks[hookName];
			}
		}
	}

	/**
	 * Executes all registered callbacks for a hook type.
	 *
	 * @param {string} hookName
	 * @param {boolean} returnFirstArg
	 * @param  {...*} args
	 * @returns {*}
	 */
	function runHooks(hookName, returnFirstArg, ...args) {
		let callbacks = hooks[hookName],
			result;

		if (returnFirstArg) {
			result = args[0];
		}

		if (callbacks) {
			callbacks.forEach(item => {
				try {
					let callbackResult = item.callback.apply(null, args);
					if (returnFirstArg) {
						result = args[0] = callbackResult;
					}
				} catch (e) {}
			});
		}

		return result;
	}

	return {
		addFilter(filterName, ...args) {
			return addHook(`filter/${filterName}`, ...args);
		},

		removeFilter(filterName, ...args) {
			return removeHooks(`filter/${filterName}`, ...args);
		},

		applyFilters(filterName, ...args) {
			return runHooks(`filter/${filterName}`, true, ...args);
		},

		addAction(actionName, ...args) {
			return addHook(`action/${actionName}`, ...args);
		},

		removeAction(actionName, ...args) {
			return removeHooks(`action/${actionName}`, ...args);
		},

		doAction(actionName, ...args) {
			return runHooks(`action/${actionName}`, false, ...args);
		}
	};
}

export default createHooks();

const ls = window.localStorage;
const inMemoryStorage = {};

/**
 * Local storage management with an in-memory storage if feature is not available.
 */
const localStorage = {
	/**
	 * Adds or updates a localStorage data.
	 *
	 * @param {string} key
	 * @param {string} value
	 */
	set(key, value = null) {
		try {
			// Save to the in-memory storage cache for faster access next time.
			inMemoryStorage[key] = value;

			ls.setItem(`peepso_${key}`, value);
		} catch (e) {}
	},

	/**
	 * Gets a localStorage data.
	 *
	 * @param {string} key
	 * @returns {string|null}
	 */
	get(key) {
		let value = null;

		try {
			if (inMemoryStorage[key]) {
				value = inMemoryStorage[key];
			} else {
				value = ls.getItem(`peepso_${key}`);
				inMemoryStorage[key] = value;
			}
		} catch (e) {}

		return value;
	},

	/**
	 * Removes a localStorage data.
	 *
	 * @param {string} key
	 */
	remove(key) {
		try {
			delete inMemoryStorage[key];

			ls.removeItem(`peepso_${key}`);
		} catch (e) {}
	}
};

export default localStorage;

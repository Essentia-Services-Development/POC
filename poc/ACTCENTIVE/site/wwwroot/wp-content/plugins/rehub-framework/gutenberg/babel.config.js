module.exports = api => {
	api.cache(false)

	return {
		presets: [
			'@babel/preset-env',
			'@babel/preset-react',
		],
		env: {
			test: {
				plugins: [
					['@babel/plugin-transform-modules-commonjs'],
					['@babel/plugin-proposal-class-properties'],
					["@babel/plugin-proposal-decorators", {"legacy": true}],
					["@babel/plugin-proposal-class-properties", {"loose": true}],
					["@babel/plugin-proposal-private-methods", {"loose": true}],
					["@babel/plugin-transform-classes", {"loose": true}],
					["transform-function-bind"]
				],
			},
		},
	}
};

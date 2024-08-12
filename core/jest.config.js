module.exports = {
  transform: {
    '^.+\\.[tj]sx?$': 'babel-jest',
  },
  testEnvironment: 'jsdom',
  testPathIgnorePatterns: [
    '<rootDir>/modules/views/tests/modules/views_test_data/views_cache.test.js',
  ]
};

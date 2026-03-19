import { defineConfig } from 'cypress';
import dotenv from 'dotenv';
dotenv.config();

export default defineConfig({
  fixturesFolder: 'tests/cypress/fixtures',
  videosFolder: 'tests/cypress/output/videos',
  screenshotsFolder: 'tests/cypress/output/screenshots',
  viewportHeight: 1000,
  viewportWidth: 1280,
  e2e: {
    baseUrl: process.env.CYPRESS_BASE_URL || 'http://localhost:8080',
    specPattern: [
      'tests/cypress/integration/**/*.cy.{js,ts}'
    ],
    supportFile: 'tests/cypress/support/index.js',
    scrollBehavior: 'center'
  },
  expose: {
    username: process.env.CYPRESS_ADMIN_USER || '',
    password: process.env.CYPRESS_ADMIN_PASSWORD || ''
  }
});

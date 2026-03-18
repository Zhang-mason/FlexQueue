import('./commands.mjs');
import('joomla-cypress');

afterEach(() => {
  cy.checkForPhpNoticesOrWarnings();
  Cypress.session.clearAllSavedSessions();
});

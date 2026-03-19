const openFlexQueuePluginEditor = () => {
  cy.visit('/administrator/index.php?option=com_plugins&view=plugins');
  cy.get('#filter_search').clear().type('flexqueue');
  cy.get('#adminForm').submit();
  cy.contains('a', /flexqueue/i, { timeout: 10000 }).first().click();
  cy.location('search').should('include', 'option=com_plugins');
  cy.location('search').should('include', 'view=plugin');
  cy.get('select[name="jform[params][driver]"]').should('exist');
  cy.get('select[name="jform[params][driver]"] option[value="database"]').should('exist');
  cy.get('select[name="jform[params][driver]"] option[value="redis"]').should('exist');
};

const clickApply = () => {
  cy.get('joomla-toolbar-button .button-apply, .button-apply').first().click({ force: true });
};

const settingRedis = () => {
  cy.get('button[aria-controls="attrib-redis"], button[data-bs-target="#attrib-redis"], a[href="#attrib-redis"]')
    .first()
    .click({ force: true });
  cy.get('input[name="jform[params][redis_host]"]').clear({ force: true }).type('127.0.0.1', { force: true });
  cy.get('input[name="jform[params][redis_port]"]').clear({ force: true }).type('6379', { force: true });
  cy.get('input[name="jform[params][redis_database]"]').clear({ force: true }).type('0', { force: true });
};

describe('System - FlexQueue plugin tests', () => {
  beforeEach(() => {
    cy.loginAdministrator(
      Cypress.expose("username"),
      Cypress.expose("password"),
    );
  });
  it('setting queue driver to database', () => {
    openFlexQueuePluginEditor();
    cy.get('select[name="jform[params][driver]"]').select('database');
    clickApply();
    cy.reload();
    cy.get('select[name="jform[params][driver]"]').should('have.value', 'database');
  });
  it('setting queue driver to redis', () => {
    openFlexQueuePluginEditor();
    cy.get('select[name="jform[params][driver]"]').select('redis');
    settingRedis();
    clickApply();
    cy.reload();
    cy.get('select[name="jform[params][driver]"]').should('have.value', 'redis');
    cy.get('button[aria-controls="attrib-redis"], button[data-bs-target="#attrib-redis"], a[href="#attrib-redis"]')
      .first()
      .click({ force: true });
    cy.get('input[name="jform[params][redis_host]"]').should('have.value', '127.0.0.1');
    cy.get('input[name="jform[params][redis_port]"]').should('have.value', '6379');
    cy.get('input[name="jform[params][redis_database]"]').should('have.value', '0');
  });
});

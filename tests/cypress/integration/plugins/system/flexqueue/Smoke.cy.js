describe('System - FlexQueue plugin smoke tests', () => {
  it('can open plugin manager and find flexqueue', () => {
    cy.loginAdministrator(Cypress.env('username'), Cypress.env('password'));
    cy.visit('/administrator/index.php?option=com_plugins&view=plugins&filter_search=flexqueue');
    cy.get('body').should('contain.text', 'flexqueue');
  });

  it('com_ajax endpoint should not return 500', () => {
    cy.request({
      method: 'GET',
      url: '/index.php?option=com_ajax&plugin=flexQueue&group=system&format=json',
      failOnStatusCode: false,
    }).then((response) => {
      expect(response.status).to.not.equal(500);
    });
  });
});

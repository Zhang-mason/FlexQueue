describe("System - FlexQueue plugin smoke tests", () => {
  it("can open plugin manager and find flexqueue", () => {
    cy.loginAdministrator(
      Cypress.expose("username"),
      Cypress.expose("password"),
    );
    cy.visit("/administrator/index.php?option=com_installer&view=manage");

    cy.get("#filter_search").type("FlexQueue");
    cy.get("#adminForm").submit();

    cy.contains("pkg_FlexQueue").should("exist");
  });
});

Cypress.on("uncaught:exception", (err, runnable) => {
  if (err.message.includes("$(...).once is not a function")) {
    return false; // Prevents Cypress from failing the test
  }
});

describe('Youth presentation', () => {
  before(() => {
    cy.login();
  });
  
  it('Submits proposal', () => {
    cy.visit('https://staging.sace.jmaconsulting.biz/youth-presentation-booking-request');
    cy.general_info();
    cy.organization_info();
    cy.online_presentation();
    cy.youth_presentation();
    cy.submit_form();
  });
});
Cypress.on("uncaught:exception", (err, runnable) => {
    if (err.message.includes("$(...).once is not a function")) {
      return false; // Prevents Cypress from failing the test
    }
  });
  
  describe('Institutional Support', () => {
    before(() => {
      cy.login();
    });
    
    it('Submits proposal', () => {
      cy.visit('https://staging.sace.jmaconsulting.biz/institutional-support-services-request');
      cy.general_info();
      cy.organization_info();
      cy.online_presentation();
      cy.institutional_support();
      cy.submit_form();
    });
  });
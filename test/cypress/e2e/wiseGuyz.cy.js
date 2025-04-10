Cypress.on("uncaught:exception", (err, runnable) => {
    if (err.message.includes("$(...).once is not a function")) {
      return false; // Prevents Cypress from failing the test
    }
  });
  
  describe('WiseGuyz', () => {
    before(() => {
      cy.login();
    });
    
    it('Submits proposal', () => {
      cy.visit('https://staging.sace.jmaconsulting.biz/wiseguyz-booking-request');
      cy.general_info();
      cy.organization_info();
      cy.in_person_presentation();
      cy.wise_guyz();
      cy.submit_form();
    });
  });
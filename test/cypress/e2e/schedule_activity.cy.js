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
      cy.visit('https://staging.sace.jmaconsulting.biz/schedule-an-activity');
      cy.organization_info3();
      cy.date_time();
      cy.online_activity();
      cy.attendees();
      cy.activity_details();
      cy.submit_form2();
    });
  });
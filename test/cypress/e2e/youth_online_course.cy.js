Cypress.on("uncaught:exception", (err, runnable) => {
    if (err.message.includes("$(...).once is not a function")) {
      return false; // Prevents Cypress from failing the test
    }
  });
  
  describe('Youth online course', () => {
    before(() => {
      cy.login();
    });
    
    it('Submits proposal', () => {
      cy.visit('https://staging.sace.jmaconsulting.biz/youth-online-course-booking-request');
      cy.general_info();
      cy.organization_info2();
      cy.youth_online_courses();
      cy.submit_form();
    });
  });
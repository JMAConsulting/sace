Cypress.on("uncaught:exception", (err, runnable) => {
    if (err.message.includes("$(...).once is not a function")) {
      return false; // Prevents Cypress from failing the test
    }
  });
  
  describe('Community Engagement', () => {
    before(() => {
      cy.login();
    });
    
    it('Submits proposal', () => {
      cy.visit('https://staging.sace.jmaconsulting.biz/community-engagement-services-request');
      cy.general_info();
      cy.organization_info();
      cy.online_presentation();
      cy.community_engagement();
      cy.submit_form();
    });
  });
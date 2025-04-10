// ***********************************************
// This example commands.js shows you how to
// create various custom commands and overwrite
// existing commands.
//
// For more comprehensive examples of custom
// commands please read more here:
// https://on.cypress.io/custom-commands
// ***********************************************
//
//
// -- This is a parent command --
// Cypress.Commands.add('login', (email, password) => { ... })
//
//
// -- This is a child command --
// Cypress.Commands.add('drag', { prevSubject: 'element'}, (subject, options) => { ... })
//
//
// -- This is a dual command --
// Cypress.Commands.add('dismiss', { prevSubject: 'optional'}, (subject, options) => { ... })
//
//
// -- This will overwrite an existing command --
// Cypress.Commands.overwrite('visit', (originalFn, url, options) => { ... })

import { auth, url, grantApiUrl, username, password, apiKey } from './user';

Cypress.Commands.add('login', () => {
    cy.visit('staging.sace.jmaconsulting.biz');
    cy.contains(
      'Sexual Assault Centre of Edmonton (SACE)'
    ).should('be.visible');
    cy.get('#edit-name').type(username);
    cy.get('input[id="edit-pass"]').type(password);
    cy.contains('Log In').click();
});

Cypress.Commands.add('general_info', () => {
  cy.get('input[id="edit-civicrm-1-contact-1-contact-first-name"]').type('test');
  cy.get('input[id="edit-civicrm-1-contact-1-contact-last-name"]').type('user');
  cy.get('input[id="edit-civicrm-1-contact-1-email-email"]').type('testuser@jmaconsulting.biz');
  cy.get('input[id="edit-civicrm-1-contact-1-phone-phone"]').type('111 111 1111');
  cy.get('input[id="edit-civicrm-1-contact-1-cg5-custom-89"]').type('test/user');
  cy.get('select[id="edit-civicrm-1-contact-1-contact-preferred-communication-method"]').select('Email');
});

Cypress.Commands.add('organization_info', () => {
  /*
  cy.get('input[id="token-input-edit-civicrm-3-contact-1-contact-existing"]')
    .should('exist')
    .should('be.visible')
    .click({ force: true })
    .clear() 
    .type('Adult Counselling', { force: true }) 
    .wait(2000);
  cy.get('.token-input-dropdown')
    .should('be.visible')
    .contains('Adult Counselling')
    .click({ force: true });
  */
  cy.get('input[id="token-input-edit-civicrm-3-contact-1-contact-existing"]').type('JMA Consulting');
  cy.get('input[id="edit-civicrm-3-contact-1-address-street-address"]').type('340 King St. E');
  cy.get('input[id="edit-civicrm-3-contact-1-address-city"]').type('Toronto');
  cy.get('input[id="edit-civicrm-3-contact-1-address-postal-code"]').type('AAA 111');
  cy.get('select[id="edit-civicrm-3-contact-1-address-state-province-id"]')
    .select('Ontario', { force: true });
  cy.get('input[id="edit-civicrm-3-contact-1-phone-phone"]').type('111 111 1111');
  cy.get('input[id="edit-civicrm-3-contact-1-email-email"]').type('testuser@jmaconsulting.biz');
  cy.get('select[id="edit-civicrm-1-activity-1-cg2-custom-657"]')
    .select('Yes', { force: true });
  cy.get('input[id="edit-civicrm-1-activity-1-cg2-custom-658"]').type('testuser group');
});

Cypress.Commands.add('organization_info2', () => {
  cy.get('a#new-org').click({ force: true });
  cy.get('input[id="edit-civicrm-2-contact-1-contact-organization-name"]').type('JMA Consulting');
  cy.get('input[id="edit-civicrm-2-contact-1-address-street-address"]').type('340 King St. E');
  cy.get('input[id="edit-civicrm-2-contact-1-address-city"]').type('Toronto');
  cy.get('input[id="edit-civicrm-2-contact-1-address-postal-code"]').type('AAA 111');
  cy.get('select[id="edit-civicrm-2-contact-1-address-state-province-id"]')
    .select('Ontario', { force: true });
  cy.get('input[id="edit-civicrm-2-contact-1-phone-phone"]').type('111 111 1111');
  cy.get('input[id="edit-civicrm-2-contact-1-email-email"]').type('testuser@jmaconsulting.biz');
  cy.get('select[id="edit-civicrm-1-activity-1-cg2-custom-657"]')
    .select('Yes', { force: true });
    cy.get('input[id="edit-civicrm-1-activity-1-cg2-custom-658"]').type('testuser group');
});

Cypress.Commands.add('online_presentation', () => {
  cy.get('#edit-civicrm-1-activity-1-cg2-custom-88')
    .select('Online Presentation', { force: true });
  cy.get('#edit-civicrm-1-activity-1-cg2-custom-88')
    .select('Online Presentation', { force: true });
});

Cypress.Commands.add('online_presentation_form', () => {
  cy.get('#edit-civicrm-1-activity-1-cg2-custom-119')
    .select('Other', { force: true });
  cy.get('#edit-civicrm-1-activity-1-cg2-custom-120')
    .select('Home', { force: true });
  cy.get('#edit-civicrm-1-activity-1-cg2-custom-124')
    .select('TBD', { force: true });
  cy.get('#edit-civicrm-1-activity-1-cg2-custom-121')
    .select('Yes', { force: true });
  cy.get('#edit-civicrm-1-activity-1-cg2-custom-123')
    .select('Yes', { force: true });
  cy.get('#edit-civicrm-1-activity-1-cg2-custom-122')
    .select('Yes', { force: true });
  cy.get('#edit-civicrm-1-activity-1-cg2-custom-125')
    .select('Yes', { force: true });
});

Cypress.Commands.add('in_person_presentation', () => {
  cy.get('#edit-civicrm-1-activity-1-cg2-custom-88')
    .select('In-Person Presentation', { force: true });
  cy.get('#edit-civicrm-1-activity-1-cg2-custom-88')
    .select('In-Person Presentation', { force: true });
});

Cypress.Commands.add('in_person_presentation_form', () => {
  cy.get('input[id="edit-civicrm-1-activity-1-cg2-custom-37"]').type('Test Instructions');
  cy.get('input[id="edit-civicrm-1-activity-1-cg58-custom-1266"]').type('Test Details');
  cy.get('#edit-civicrm-1-activity-1-cg2-custom-34')
    .select('Yes', { force: true });
  cy.get('#edit-civicrm-1-activity-1-cg2-custom-35')
    .select('Yes', { force: true });
});

Cypress.Commands.add('grades_ages', () => {
  cy.get('#edit-civicrm-1-activity-1-cg2-custom-25-grade-7-ages-12-13').check();
  cy.get('#edit-civicrm-1-activity-1-cg2-custom-25-grade-8-ages-13-14').check();
  cy.get('#edit-civicrm-1-activity-1-cg2-custom-25-grade-9-ages-14-15').check();
  cy.get('#edit-civicrm-1-activity-1-cg2-custom-25-high-school-ages-15-18').check();
});

Cypress.Commands.add('common_info', () => {
  cy.get('input[id="edit-civicrm-1-activity-1-cg2-custom-815"]').type('100');
  cy.get('input[id="edit-civicrm-1-activity-1-cg2-custom-127"]').type('April 8th, 2025');
  cy.get('textarea[id="edit-civicrm-1-activity-1-cg2-custom-126"]').type('N/A');
});

Cypress.Commands.add('start_end', () => {
  cy.get('input[id="edit-civicrm-1-activity-1-cg2-custom-129"]').type('morning');
});

Cypress.Commands.add('presentation_number', () => {
  cy.get('input[id="edit-civicrm-1-activity-1-cg2-custom-128"]').type('5');
});

Cypress.Commands.add('population_focus', () => {
  cy.get('#edit-civicrm-1-activity-1-cg2-custom-333-adults').check();
});

Cypress.Commands.add('classes_number', () => {
  cy.get('input[id="edit-civicrm-1-activity-1-cg2-custom-656"]').type('5');
});

Cypress.Commands.add('youth_presentation', () => {
  cy.get('#edit-civicrm-1-activity-1-cg2-custom-40-saceservices').check();
  cy.grades_ages();
  cy.common_info();
  cy.presentation_number();
  cy.start_end();
  cy.online_presentation_form();
  cy.population_focus();
});

Cypress.Commands.add('adult_presentation', () => {
  cy.get('#edit-civicrm-1-activity-1-cg2-custom-40-bystanderintervention').check();
  cy.common_info();
  cy.presentation_number();
  cy.start_end();
  cy.online_presentation_form();
  cy.population_focus();
});

Cypress.Commands.add('youth_online_courses', () => {
  cy.get('select[id="edit-civicrm-1-activity-1-cg2-custom-697"]').then(($select) => {
    cy.wrap($select).select('Ask First: Sexual Harassment\u00A0', { force: true }).trigger('change');
  }); 
  cy.get('#edit-civicrm-1-activity-1-cg2-custom-662-live-qa-with-a-sace-educator').check();
  cy.get('#edit-civicrm-1-activity-1-cg2-custom-662-non-consensual-photo-sharing-add-on-module-high-school-only').check();
  cy.get('#edit-civicrm-1-activity-1-cg2-custom-662-2slgbtq-community-sexual-violence').check();
  cy.get('#edit-civicrm-1-activity-1-cg2-custom-662-sexual-harassment-add-on-module-high-school-only').check();
  cy.get('#edit-civicrm-1-activity-1-cg2-custom-662-racism-sexual-violence').check();
  cy.common_info();
  cy.classes_number();
});

Cypress.Commands.add('institutional_support', () => {
  cy.get('select[id="edit-civicrm-1-activity-1-cg2-custom-1232"]')
    .select('CreatingSaferSpaces', { force: true });
  cy.get('select[id="edit-civicrm-1-activity-1-cg2-custom-1232"]')
    .select('CreatingSaferSpaces', { force: true });
  cy.wait(1000);
  cy.common_info();
  cy.start_end();
});

Cypress.Commands.add('community_engagement', () => {
  cy.get('select[id="edit-civicrm-1-activity-1-cg2-custom-1233"]')
    .select('Discussion Panel', { force: true });
  cy.get('select[id="edit-civicrm-1-activity-1-cg2-custom-1233"]')
    .select('Discussion Panel', { force: true });
  cy.get('textarea[id="edit-civicrm-1-activity-1-cg2-custom-1234"]').type('Test Event');
  cy.common_info();
  cy.start_end();
});

Cypress.Commands.add('wise_guyz', () => {
  cy.grades_ages();
  cy.common_info();
  cy.start_end();
  cy.in_person_presentation_form();
});

Cypress.Commands.add('submit_form', () => {
  cy.get('#edit-actions-submit').click();
});

/*
Cypress.Commands.add('verify_details', () => {
  cy.request({
    method: 'POST',
    url: grantApiUrl,
    auth,
    form: true,
    headers: {
      'X-Civi-Auth': `Bearer ${apiKey}`,
    },
  }).then((response) => {
    expect(response.status).to.eq(200);
    cy.log('API Response:', JSON.stringify(response.body, null, 2));

    // Ensure response has expected structure
    expect(response.body).to.have.property('values');
    expect(response.body.values).to.be.an('array').and.not.be.empty;
  });
});
*/
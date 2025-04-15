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

import { auth, url, ApiUrl, username, password, apiKey } from './user';

Cypress.Commands.add('login', () => {
  cy.visit('https://staging.sace.jmaconsulting.biz/user/login');
  
  // If no cookies exist for the session, log in and store the cookies
  cy.getCookie('your_session_cookie_name').then((cookie) => {
    if (!cookie) {
      // Log in if the cookie is not found
      cy.get('input[id="edit-name"]').type(Cypress.env('username'));
      cy.get('input[id="edit-pass"]').type(Cypress.env('password'));
      cy.get('input[id="edit-submit"]').click();
      
      // After successful login, save cookies
      cy.getCookies().then((cookies) => {
        cookies.forEach((cookie) => {
          cy.setCookie(cookie.name, cookie.value);
        });
      });
    }
  });
});

// Save session cookies after successful login
Cypress.Commands.add('saveCookies', () => {
  cy.getCookies().then((cookies) => {
    cookies.forEach((cookie) => {
      cy.setCookie(cookie.name, cookie.value);
    });
  });
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
  cy.get('a#new-org-block').click({ force: true });
  cy.get('input[id="edit-civicrm-3-contact-1-contact-organization-name"]').type('JMA Consulting');
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

Cypress.Commands.add('organization_info3', () => {
  cy.get('a#existing-org').click({ force: true });
  cy.get('#existing-org').click({ force: true });
  cy.get('input[id="edit-civicrm-1-contact-1-contact-organization-name"]').type('JMA Consulting');
  cy.get('input[id="edit-civicrm-1-contact-1-address-street-address"]').type('340 King St. E');
  cy.get('input[id="edit-civicrm-1-contact-1-address-city"]').type('Toronto');
  cy.get('input[id="edit-civicrm-1-contact-1-address-postal-code"]').type('AAA 111');
  cy.get('select[id="edit-civicrm-1-contact-1-address-state-province-id"]')
    .select('Ontario', { force: true });
  cy.get('input[id="edit-civicrm-1-contact-1-phone-phone"]').type('111 111 1111');
  cy.get('input[id="edit-civicrm-1-contact-1-email-email"]').type('testuser@jmaconsulting.biz');
});

Cypress.Commands.add('organization_info4', () => {
  cy.get('input[id="edit-civicrm-2-contact-1-contact-organization-name"]').type('(NEW)');
  cy.get('input[id="edit-civicrm-2-contact-1-address-street-address"]').type('(NEW)');
  cy.get('input[id="edit-civicrm-2-contact-1-address-city"]').type('(NEW)');
  cy.get('input[id="edit-civicrm-2-contact-1-address-postal-code"]').type('(NEW)');
  cy.get('select[id="edit-civicrm-2-contact-1-address-state-province-id"]')
    .select('Ontario', { force: true });
});

Cypress.Commands.add('wise_guyz_update', () => {
  cy.get('#edit-civicrm-2-contact-1-other-tag-44').check();
  cy.get('#edit-civicrm-2-contact-1-other-tag-48').check();
  cy.get('#edit-civicrm-2-contact-1-other-tag-32').check();
  cy.get('#edit-civicrm-1-activity-1-cg2-custom-657')
    .select('Yes', { force: true });
  cy.get('input[id="edit-civicrm-1-activity-1-activity-subject"]').type('Test Calendar Title');
  cy.get('#edit-civicrm-1-activity-1-activity-status-id')
    .select('Completed', { force: true });
  cy.get('#edit-civicrm-1-activity-1-cg2-custom-88')
    .select('Phone Activity', { force: true });
  cy.get('#edit-civicrm-1-activity-1-cg2-custom-331')
    .select('No', { force: true });
  cy.get('#edit-civicrm-1-activity-1-cg2-custom-330')
    .select('No', { force: true });
  cy.get('#edit-civicrm-1-activity-1-cg2-custom-334')
    .select('Yes', { force: true });
  cy.get('#edit-civicrm-1-activity-1-cg2-custom-1261')
    .select('Yes', { force: true });
  cy.get('input[id="edit-civicrm-1-activity-1-cg2-custom-815"]').type('100');
  cy.get('input[id="edit-civicrm-1-activity-1-cg2-custom-128"]').type('5');
  cy.grades_ages();
  cy.get('textarea[id="edit-civicrm-1-activity-1-cg2-custom-126"]').type('Test');
  cy.get('textarea[id="edit-civicrm-1-activity-1-activity-details-value"]').type('Test');
  cy.get('#edit-civicrm-1-activity-1-activity-details-value')
  .type('This is my message', { force: true });
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

Cypress.Commands.add('date_time', () => {
  cy.get('input[name="civicrm_1_activity_1_activity_activity_date_time[date]"]')
    .clear()
    .type('2025-04-14');
  cy.get('input[name="civicrm_1_activity_1_activity_activity_date_time[time]"]')
    .clear()
    .type('14:30');
  cy.get('input#edit-civicrm-1-activity-1-cg2-custom-661-date')
    .clear()
    .type('2025-04-14');
  cy.get('div.form-item-civicrm-1-activity-1-cg2-custom-661-time input[type="time"]')
    .clear()
    .type('16:30');
});

Cypress.Commands.add('online_activity', () => {
  cy.get('#edit-civicrm-1-activity-1-cg2-custom-88')
    .select('Online Activity', { force: true });
  cy.get('#edit-civicrm-1-activity-1-cg2-custom-88')
    .select('Online Activity', { force: true });
  cy.get('#edit-civicrm-1-activity-1-cg2-custom-119')
    .select('Other', { force: true });
  cy.get('input[id="edit-civicrm-1-activity-1-cg58-custom-1266"]').type('Test Building');
  cy.get('input[id="edit-civicrm-1-activity-1-cg58-custom-1267"]').type('Test Link');
  cy.get('input[id="edit-civicrm-1-activity-1-cg2-custom-37"]').type('Test Instructions');
});

Cypress.Commands.add('attendees', () => {
  cy.get('#edit-civicrm-1-activity-1-cg2-custom-332-2slgbtq-inclusion-committee').check();
  cy.get('#edit-staff').should('exist');
  cy.get('.select2-container').should('exist').eq(0).click();
  cy.get('.select2-results__option').contains('Sara Cameron').click();
  cy.get('#edit-user-team').should('exist');
  cy.get('.select2-container').should('exist').eq(1).click();
  cy.get('.select2-results__option').contains('Finance').click();
  cy.get('#edit-organisation-select').should('exist');
  cy.get('.select2-container').should('exist').eq(2).click();
  cy.get('.select2-results__option').contains('McKee School').click();
});

Cypress.Commands.add('activity_details', () => {
  cy.get('#edit-civicrm-1-activity-1-activity-activity-type-id')
    .next('.select2')
    .click();
  cy.get('.select2-dropdown input.select2-search__field')
    .type('AD - Best/ Promising Practices Research');
  cy.get('.select2-results__option')
    .contains('AD - Best/ Promising Practices Research')
    .click();
  cy.get('input[id="edit-civicrm-1-activity-1-activity-subject"]').type('Test Calendar Title');
  cy.get('#edit-civicrm-1-activity-1-cg2-custom-332-communityagencydevelopment--2').check();
  cy.get('iframe.cke_wysiwyg_frame')
    .its('0.contentDocument.body').should('not.be.empty')
    .then(cy.wrap)
    .click()
    .type('Test Outputs');
  cy.get('#edit-civicrm-1-activity-1-cg58-custom-1265-format--2')
    .select('html', { force: true });
  cy.get('label')
    .contains('Activity Notes')
    .invoke('attr', 'for')
    .then((id) => {
      cy.get(`div#cke_${id}`).find('iframe.cke_wysiwyg_frame').then(($iframe) => {
        const body = $iframe.contents().find('body');
        cy.wrap(body).click().clear().type('Test Notes');
      });
    });
  cy.get('#edit-civicrm-1-activity-1-activity-details-format--2')
    .select('html', { force: true });
  cy.get('#edit-civicrm-1-activity-1-cg2-custom-333-2slgbtq').check();
  cy.get('#edit-civicrm-1-activity-1-cg2-custom-333-child-3-12').check();
  cy.get('#edit-civicrm-1-activity-1-cg2-custom-333-guardians').check();
  cy.get('#edit-civicrm-1-activity-1-cg2-custom-40-2slgbtq').check();
  cy.get('#edit-civicrm-1-activity-1-cg2-custom-40-childsexualabuse').check();
  cy.get('#edit-civicrm-1-activity-1-cg2-custom-40-creatingsaferspacesandrespondingtosexualviolence').check();
  cy.get('#edit-civicrm-1-activity-1-cg2-custom-341-2slgbtq').check();
  cy.get('#edit-civicrm-1-activity-1-cg2-custom-341-community-group').check();
  cy.get('#edit-civicrm-1-activity-1-cg2-custom-341-englishlanguagelearners').check();
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

Cypress.Commands.add('submit_form2', () => {
  cy.get('#edit-submit').click();
});

Cypress.Commands.add('select_update', () => {
  cy.contains('a.fc-timegrid-event', 'JMA CONSULTING - WiseGuyz')
    .scrollIntoView()
    .click({ force: true });
  cy.get('.meeting-buttons a')
    .contains('Update Booking')
    .scrollIntoView()
    .should('be.visible')
    .click();
});

Cypress.Commands.add('verify_details', () => {
  cy.request({
    method: 'POST',
    url: 'https://staging.sace.jmaconsulting.biz/civicrm/ajax/api4/Activity/get',
    headers: {
      "X-Civi-Auth": "Bearer gTChgzxxgtidJd7LHCo7",  // Ensure this token is valid
      "user-agent": "Mozilla/5.0 ...",  // Use the correct user agent
      "accept": "*/*",
      "content-type": "application/x-www-form-urlencoded",
    },
    cookies: {
      SSESS79d31a8082f640107d5508347bb4f699: 'ISCac1utPD6qT0WeByF4WmxRyRJSfy0-aZMpliSqU8KBdX1N',
    },
    failOnStatusCode: false,  // Set to false to prevent test failure due to 403
  }).then((response) => {
    // Log the entire response to inspect it
    cy.log('Full Response:', JSON.stringify(response, null, 2));

    // Check if response exists and has the status property
    if (response && response.status) {
      cy.log(response.status);  // Log response status for debugging
      expect(response.status).to.eq(200);  // Ensure status is 200
      cy.log('API Response:', JSON.stringify(response.body, null, 2));

      // Ensure response has expected structure
      expect(response.body).to.have.property('values');
      expect(response.body.values).to.be.an('array').and.not.be.empty;

      // Sort the values by activity_date_time in descending order to get the most recent one
      const mostRecentActivity = response.body.values.sort((a, b) => {
        const dateA = new Date(a.activity_date_time);
        const dateB = new Date(b.activity_date_time);
        return dateB - dateA;  // Sort in descending order
      })[0];  // Get the most recent activity

      cy.log('Most Recent Activity:', mostRecentActivity);

      // Validate specific fields of the most recent activity
      expect(mostRecentActivity).to.have.property('id');
      expect(mostRecentActivity).to.have.property('subject').and.not.be.empty;
      expect(mostRecentActivity).to.have.property('activity_date_time').and.not.be.null;
      expect(mostRecentActivity).to.have.property('details').and.not.be.null;
    } else {
      cy.log('Response is null or missing status.');
    }
  });
});
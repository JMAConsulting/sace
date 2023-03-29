// page loader
let pageLoader = document.createElement('div');
pageLoader.classList.add('page-loader');
const main = document.querySelector('main');
main.append(pageLoader);

/**
 * Change Banner Image
 */

const bannerImage = document.querySelector('.region--hero .block__content .media img')
bannerImage.src = '/sites/default/files/2023-01/MovingCentre-logo-banner.png';
bannerImage.dataset.src = '/sites/default/files/2023-01/MovingCentre-logo-banner.png';


/**
 * Change Labels for Pledge Field in Form 7
 */
const pledgeField = document.querySelector('label[for="CIVICRM_QFID_1_is_pledge"');
if(pledgeField) {
    pledgeField.innerText += ' month';
}

const pledgeInstallments = document.querySelector('#pledge_installments_num');
if(pledgeInstallments) {
    pledgeInstallments.innerHTML = `
    &nbsp;for&nbsp;<input size="3" aria-label="Installments" name="pledge_installments" type="text" id="pledge_installments" class="crm-form-text">&nbsp;months.
    `;
}

const introSection = document.querySelector('#intro_text');
const blockContent = document.querySelector('main .block__content');
const crmContainer = document.querySelector('#crm-container');
const blockContainer = document.querySelector('#block-mainpagecontent');
const pageTitle = document.querySelector('.page-title').innerText.trim();
const contributionContainer = document.querySelector('.crm-block.crm-contribution-main-form-block');
const onBehalfOfOrg = document.querySelector('#onBehalfOfOrg');
const submitBtnDiv = document.querySelector('#crm-submit-buttons');
const wholeContentContainer = document.querySelector('.main-content__container.container');

const campaigns = [
    {
        title: 'Community Active Bystanders',
        content: 'Donate $500 – $10,000 to be one of our 75 Community Active Bystanders',
        link: '/civicrm/contribute/transact?reset=1&id=7' ,
        image: '/sites/default/files/2023-01/Active%20bystander%201.png'          
    },
    {
        title: 'Safer Space Makers',
        content: 'Sign up to be a monthly donor helping SACE to offer consistent accessibility supports',
        link: 'civicrm/contribute/transact?reset=1&id=9',
        image: '/sites/default/files/2023-01/Space%20Makers%202.png'                  
    },
    {
        title: 'Supporters',
        content: 'Make a donation and share your message of support',
        link: 'civicrm/contribute/transact?reset=1&id=8',
        image: '/sites/default/files/2023-01/Supporters%201.png'                  
    },
]

const filteredCampaigns =  campaigns.filter(campaign => {
    if(campaign.title !== pageTitle) {
        return campaign;
    }
})

let removeSpace = document.querySelector(' #onBehalfOfOrg .is_for_organization-section');
let removeSpaceContent = removeSpace.innerHTML;
removeSpace.innerHTML = removeSpaceContent.replace(/&nbsp;/g, '');



// Move Intro Section
blockContainer.prepend(introSection);

// Add Sidebar
let sidebar = document.createElement('aside');
sidebar.classList.add('sidebar');
wholeContentContainer.insertBefore(sidebar, main);

// Add Content to Sidebar
let sidebarContainer = document.createElement('div');
sidebarContainer.classList.add('sidebar-container');
sidebar.append(sidebarContainer);

let addCampaigns = filteredCampaigns.map(campaign => {
    return (
        `
        <div class="campaign-box">
            <img src="${campaign.image}">
            <h3>${campaign.title}</h3>
            <p>${campaign.content}</p>
            <a class="sace-btn" href="${campaign.link}">Donate Now</a>
        </div>
        `
    )
});

let sidebarTop = `
<div>
<p><a href="http://www.sace.ca/CentreSACE" target="_blank" >Learn more about Moving to the Centre: a New Home for SACE</a> </p>
<h3>Other ways you can support:</h3>
</div>
`;

sidebarContainer.innerHTML = sidebarTop;
sidebarContainer.innerHTML += addCampaigns.join('');



// sidebarContainer.prepend(sidebarTop);

/**
 * On Behalf Section
 */

// Create Parent Div
let donationType = document.createElement('div');
donationType.classList.add('donation-type-container');
contributionContainer.insertBefore(donationType, onBehalfOfOrg);
donationType.append(onBehalfOfOrg.querySelector('.content'))
let personalDonation = document.createElement('button');
personalDonation.classList.add('sace-box');
personalDonation.setAttribute('id', 'personal_donation');
personalDonation.innerHTML = 'Personal Donation';
donationType.append(personalDonation)


/**
 * Move Post Profile Section
 */
const postProfileSection = document.querySelector('.crm-group.custom_post_profile-group');
const nameAndAddSection = document.querySelector('.crm-group.custom_pre_profile-group');
contributionContainer.insertBefore(postProfileSection, nameAndAddSection);

/**
 * Move Newsletter section
 */
const helpRow = document.querySelector('.helprow-group-section');
const helpRowSibling = document.querySelector('.helprow-group-section + div');
contributionContainer.insertBefore(helpRow, submitBtnDiv);
contributionContainer.insertBefore(helpRowSibling, submitBtnDiv);


/**
 * Donation Type Actions
 */
const orgDonation = document.getElementById('is_for_organization');
const orgDonationLabel = document.querySelector('#is_for_organization + label');
const personalDonationBtn = document.getElementById('personal_donation')

orgDonation.addEventListener('change', e => {
    if(orgDonation.checked) {
        orgDonationLabel.classList.add('active-look');
        personalDonationBtn.classList.remove('active-look');
    } else {
        orgDonationLabel.classList.remove('active-look');
    }
});

personalDonationBtn.addEventListener('click', e => {
    e.preventDefault();
    personalDonationBtn.classList.add('active-look');
    if(orgDonationLabel.classList.contains('active-look')) {
        orgDonationLabel.classList.remove('active-look');
        orgDonation.click();
    }
    
});

/**
 * Pledge Actions Styling
 */
const pledges = document.querySelectorAll(' input[name="is_pledge"] ');
const pledgeLabels = document.querySelectorAll(' input[name="is_pledge"] + label ');

pledges.forEach((pledge, index) => {
    pledge.addEventListener('change', e => {
        pledgeLabels.forEach(pledgeLabel => {
            pledgeLabel.classList.remove('active-look');
        });
        if(pledge.checked) {
            pledgeLabels[index].classList.add('active-look');
        }   
    });
});

/**
 * In Tribute Actions Styling
 */
const tributes = document.querySelectorAll(' input[name="soft_credit_type_id"] ');
const tributeLabels = document.querySelectorAll(' input[name="soft_credit_type_id"] + label ');
const clearLink = document.querySelector('.crm-hover-button.crm-clear-link');

tributes.forEach((tribute, index) => {
    tribute.addEventListener('change', e => {
        tributeLabels.forEach(tributeLabel => {
            tributeLabel.classList.remove('active-look');
        });
        if(tribute.checked) {
            tributeLabels[index].classList.add('active-look');
        }   
    });
});

clearLink.addEventListener('click' , e => {
    tributeLabels.forEach(tributeLabel => {
        tributeLabel.classList.remove('active-look');
    });
});

setTimeout(() => {
    pageLoader.classList.add('remove');
}, 2000);
// Define Constants and Variables
const introText = document.getElementById("intro_text");
const footerText = document.getElementById("footer_text");
const pricesetContainer = document.getElementById("priceset-div");
const pricesets = document.getElementById("priceset");
const oneTimeSection = document.querySelector(".one_time-section");
const monthlySection = document.querySelector(".monthly-section");
const otherAmountSection = document.querySelector(".other_amount-section");
const otherAmountInput = otherAmountSection.querySelector(".other_amount-content input");
const contributionAmountSection = document.querySelector(".contribution_amount-section");
const pricesetTotal = document.getElementById("pricesetTotal");
const isRecurSection = document.querySelector(".is_recur-section");
const onBehalfOfOrgSection = document.getElementById("onBehalfOfOrg");
const onBehalf = document.querySelector("#on-behalf-block fieldset");
const postProfileSection = document.querySelector(".custom_post_profile-group");
const preProfileSection = document.querySelector(".custom_pre_profile-group");
const crmBottomButtons = document.querySelector(".crm-submit-buttons");

// console.log(preProfileSection)

// Change position of Name and Address Section
if (preProfileSection) {
	const group = preProfileSection.querySelector("fieldset");
	if (group.classList.contains("crm-profile-name-name_and_address")) {
		const parent = preProfileSection.parentNode;
		parent.insertBefore(postProfileSection, preProfileSection);
	} else {
		// Remove labels if field type is checkbox
		const group = preProfileSection.querySelector("fieldset");
		const sections = group.querySelectorAll(".crm-section");
		sections.forEach((section) => {
			const label = section.querySelector(".label");
			const input = section.querySelector(".content input:last-of-type");
			const content = section.querySelector(".content");

			if (input) {
				if (input.type == "checkbox") {
					label.style.display = "none";
					content.style.display = "flex";
				}
			}
		});
	}
}

// Move fields around from post profile section
if (postProfileSection) {
	const group = postProfileSection.querySelector("fieldset");
	const groupLegend = group.querySelector(" legend");

	if (groupLegend.innerText == "Additional Information") {
		// Move What Inspired You Optional Field
		const whatInspiredYouField = group.querySelector(".crm-section:first-of-type");
		const pricesetContainerParent = pricesetContainer.parentNode;
		pricesetContainerParent.insertBefore(whatInspiredYouField, pricesetContainer.nextSibling);

		// Move Newsletter Field
		const newsletterField = group.querySelector(".editrow_group-section:last-of-type");
		const newsletterHelpField = group.querySelector(".helprow-group-section:nth-last-of-type(2)");

		if (newsletterField && newsletterHelpField) {
			newsletterField.classList.add("newsletter");
			newsletterHelpField.classList.add("newsletter-helper");
			const footerTextParent = footerText.parentNode;
			const crmBottomButtonsParent = crmBottomButtons.parentNode;
			crmBottomButtonsParent.insertBefore(newsletterField, crmBottomButtons);
			crmBottomButtonsParent.insertBefore(newsletterHelpField, crmBottomButtons);
		}

		// Remove labels if field type is checkbox
		const sections = group.querySelectorAll(".crm-section");
		sections.forEach((section) => {
			const label = section.querySelector(".label");
			const input = section.querySelector(".content input:last-of-type");
			const content = section.querySelector(".content");

			if (input) {
				if (input.type == "checkbox") {
					label.style.display = "none";
					content.style.display = "flex";
				}
			}
		});
	} else {
		// Remove labels if field type is checkbox
		const sections = group.querySelectorAll(".crm-section");
		sections.forEach((section) => {
			const label = section.querySelector(".label");
			const input = section.querySelector(".content input:last-of-type");
			const content = section.querySelector(".content");

			if (input) {
				if (input.type == "checkbox") {
					label.style.display = "none";
					content.style.display = "flex";
				}
			}
		});
	}
}

// If only has contribution amount section
if (contributionAmountSection) {
	const lastAmount = contributionAmountSection.querySelector(".contribution_amount-content .price-set-row:last-of-type input");
	otherAmountInput.addEventListener("click", (e) => {
		e.preventDefault();
		lastAmount.click();
	});
}

// Add Monthly/One Time toggle buttons
const durationLegend = document.createElement("div");
durationLegend.className = "duration-legend contribution_amount-section";
durationLegend.innerHTML = `
<div class="label"><label>Your Donation</label></div>
`;
const durationButtonSection = document.createElement("div");
durationButtonSection.className = "duration-button-section";
durationButtonSection.innerHTML = `
<button id="toggleMonthly" class="toggle-btn active">Monthly</button>
<button id="toggleOneTime" class="toggle-btn">One-Time</button>
`;
const pricesetParent = pricesetContainer.parentNode;
if (monthlySection) {
	pricesetParent.insertBefore(durationLegend, pricesetContainer);
	pricesetParent.insertBefore(durationButtonSection, pricesetContainer);
}

// Actions for monthly/One Time toggle buttons
const hasToggleButtons = document.querySelector(".duration-button-section");
if (hasToggleButtons) {
	monthlySection.classList.add("active");
	let isRecurInput;
	if (isRecurSection) {
		isRecurInput = isRecurSection.querySelector(".content input");
		// isRecurInput.click()
		isRecurInput.checked = true;
	}

	const toggleMonthly = document.getElementById("toggleMonthly");
	const toggleOneTime = document.getElementById("toggleOneTime");
	const noneMonthly = monthlySection.querySelector(".monthly-content .price-set-row:last-of-type input");
	const noneOneTime = oneTimeSection.querySelector(".one_time-content .price-set-row:last-of-type input");
	// console.log(noneOneTime)
	toggleMonthly.addEventListener("click", (e) => {
		e.preventDefault();
		console.log("monthly");
		toggleMonthly.classList.add("active");
		monthlySection.classList.add("active");
		toggleOneTime.classList.remove("active");
		oneTimeSection.classList.remove("active");
		noneOneTime.click();
		if (isRecurSection) {
			// isRecurInput.click()
			isRecurInput.checked = true;
		}
	});

	toggleOneTime.addEventListener("click", (e) => {
		e.preventDefault();
		console.log("One Time");
		toggleOneTime.classList.add("active");
		oneTimeSection.classList.add("active");
		toggleMonthly.classList.remove("active");
		monthlySection.classList.remove("active");
		noneMonthly.click();
		if (isRecurSection) {
			// isRecurInput.click()
			isRecurInput.checked = false;
		}
	});

	otherAmountInput.addEventListener("click", (e) => {
		e.preventDefault();
		noneOneTime.click();
		noneMonthly.click();
	});
}

// If on Behalf of Org Section Exists
if (onBehalf) {
	const onBehalfOfOrgInput = document.getElementById("is_for_organization");
	const onBehalfBlock = document.getElementById("on-behalf-block");
	// add toggle button
	const onbehalfOfButtonSection = document.createElement("div");
	onbehalfOfButtonSection.className = "onbehalf-button-section";
	onbehalfOfButtonSection.innerHTML = `
<button id="togglePersonal" class="toggle-btn active">Personal Donation</button>
<button id="toggleOrganization" class="toggle-btn">Organizational Donation</button>
`;
	onBehalfOfOrgSection.prepend(onbehalfOfButtonSection);

	const togglePersonal = document.getElementById("togglePersonal");
	const toggleOrganization = document.getElementById("toggleOrganization");

	togglePersonal.addEventListener("click", (e) => {
		e.preventDefault();
		onBehalfOfOrgInput.checked = false;
		onBehalfBlock.style.display = "none";
		togglePersonal.classList.add("active");
		toggleOrganization.classList.remove("active");
	});

	toggleOrganization.addEventListener("click", (e) => {
		e.preventDefault();
		onBehalfOfOrgInput.checked = true;
		onBehalfBlock.style.display = "block";
		toggleOrganization.classList.add("active");
		togglePersonal.classList.remove("active");
	});
}

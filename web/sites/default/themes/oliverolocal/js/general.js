const userContactSection = document.querySelector(".jma-user-contact .view-filters");
if (userContactSection) {
	userContactSection.setAttribute("aria-expanded", "false");
	const legendBtn = userContactSection.querySelector(" .fieldset__legend ");
	const wrapper = userContactSection.querySelector(" .fieldset__wrapper ");
	legendBtn.addEventListener("click", (e) => {
		userContactSection.classList.toggle("jma-expanded");

		if (userContactSection.classList.contains("jma-expanded")) {
			userContactSection.setAttribute("aria-expanded", "true");
		} else {
			userContactSection.setAttribute("aria-expanded", "false");
		}
	});
}

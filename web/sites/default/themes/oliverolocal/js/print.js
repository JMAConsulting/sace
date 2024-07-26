const pageTitleBlock = document.getElementById("block-pagetitle");
const firstFormRow = document.querySelector(".js-printable-form .form-wrapper");
let bookingID;
if (firstFormRow) {
	const fieldBoxes = firstFormRow.querySelectorAll("div > div > div ");
	fieldBoxes.forEach((field, index) => {
		const label = field.querySelector("label") ? field.querySelector("label").innerText.toLowerCase() : null;
		const input = field.querySelector("input");

		if (label == "Booking Reference ID".toLowerCase()) {
			bookingID = input.value;
			// console.log(bookingID);
		}
	});

	const bookingIDField = document.createElement("div");
	bookingIDField.innerHTML = `
	<span class="booking-id">
	Booking Reference ID: ${bookingID}
	</span>
	`;
  if (pageTitleBlock) {
    pageTitleBlock.classList.add("flex-title");
    pageTitleBlock.appendChild(bookingIDField);
  }
}
